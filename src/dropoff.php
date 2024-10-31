<?php if(!defined('ABSPATH')) exit; // Exit if accessed directly ?>

<form id="form_dropoff" method="POST" action="options.php">
	<h2><?php echo __('Drop-off','reshark_pdb'); ?></h2>
	<p><?php echo __('Grazie all´opzione "Drop-off" offerta da Poste Italiane, è possibile consentire ai propri clienti di selezionare un punto di drop-off come indirizzo di consegna dell´ordine.<br>Attivando questa opzione, i clienti potranno scegliere il punto di drop-off Poste Italiane più comodo per loro, semplificando così la procedura di consegna dell´ordine.','reshark_pdb'); ?></p>
	
	<?php
		$key = RESHARK_SLUG.'_dropoff';
		$active = get_option($key);
		$min = get_option($key.'_min');
		$cost = get_option($key.'_cost');
		$cost_type = get_option($key.'_cost_type');
	?>
	<div>
		<table class="form-table" role="presentation">
			<tbody>
				<tr><th scope="row"><?php _e('Attiva','reshark_pdb'); ?></th><td><input type="checkbox" id="<?php echo esc_attr($key); ?>" name="<?php echo esc_attr($key); ?>" value="1" <?php echo $active ? 'checked' : ''; ?>></td></tr>
				<tr><th scope="row"><?php _e('Valore minimo carrello','reshark_pdb'); ?></th><td><input type="number" id="<?php echo esc_attr($key); ?>_min" name="<?php echo esc_attr($key); ?>_min" value="<?php echo esc_attr($min); ?>"> <?php _e('€','reshark_pdb'); ?></td></tr>
				<tr><th scope="row"><?php _e('Costo','reshark_pdb'); ?></th><td><input type="number" id="<?php echo esc_attr($key); ?>_cost" name="<?php echo esc_attr($key); ?>_cost" value="<?php echo esc_attr($cost); ?>"><select id="<?php echo esc_attr($key); ?>_cost_type" name="<?php echo esc_attr($key); ?>_cost_type"><option value="fix" <?php echo $cost_type == 'fix' ? 'selected' : ''; ?>><?php _e('€','reshark_pdb'); ?></option><option value="perc" <?php echo $cost_type == 'perc' ? 'selected' : ''; ?>>%</option></select><br><br><small for="fix" style="display:<?php echo $cost_type == 'fix' ? 'block' : 'none'; ?>;"><?php _e('importo fisso da aggiungere al carrello (può essere anche negativo)','reshark_pdb'); ?>;</small><small for="perc" style="display:<?php echo $cost_type == 'perc' ? 'block' : 'none'; ?>;"><?php _e('percentuale applicata sul valore totale del carrello (può essere anche negativa)','reshark_pdb'); ?>;</small></td></tr>
			</tbody>
		</table>
	</div>
	<hr>
	<button type="submit" class="button button-primary">Salva le modifiche</button>
</form>

<script>
	jQuery(function($){
		$(document).on('submit','#form_dropoff',function(e){
			e.preventDefault();
			var obj_dropoff = {};
				obj_dropoff['<?php echo esc_attr($key); ?>'] = $('#<?php echo esc_attr($key); ?>').is(':checked') ? 1 : 0;
				obj_dropoff['<?php echo esc_attr($key); ?>_min'] = $('#<?php echo esc_attr($key); ?>_min').val();
				obj_dropoff['<?php echo esc_attr($key); ?>_cost'] = $('#<?php echo esc_attr($key); ?>_cost').val();
				obj_dropoff['<?php echo esc_attr($key); ?>_cost_type'] = $('#<?php echo esc_attr($key); ?>_cost_type').val();
			var array_dropoff = Object.entries(obj_dropoff);
			$.ajax({
				type: 'POST',
				url: ajax.url,
				data: {
					'nonce': ajax.nonce,
					'action':'rpdb_set_dropoff_options',
					'array_dropoff' : array_dropoff,
				},
				success: function (result) {
					window.location.href = window.location.href;
				},
			});
		});
	});
</script>