<?php if(!defined('ABSPATH')) exit; // Exit if accessed directly ?>

<form id="reshark_insures" name="reshark_insures" action="options.php" method="POST">
	<h2><?php echo __('Assicurazione','reshark_pdb'); ?></h2>
	<p><?php echo __("Seleziona l'assicurazione per le tue spedizioni",'reshark_pdb'); ?></p>
	<?php
	
		$countries = new WC_Countries();
		$allowed_countries = $countries->get_allowed_countries();
		$is_national = array_key_exists('IT',$allowed_countries); //NAZIONALE
		$is_international = (count($allowed_countries) > 1 || (count($allowed_countries) == 1 && !array_key_exists('IT',$allowed_countries))); //INTERNAZIONALE
	
		//FullInsurance
		//InternationalLowInsurance
		//InternationalHighInsurance
	
		$insure_static_vars = array();
		$insure_static_vars['FullInsurance'] = ['label' => __('Assicurazione nazionale','reshark_pdb'), 'description' => __("Le spedizioni nazionali (IT) vengono assicurate",'reshark_pdb'), 'type' => 'national'];
		$insure_static_vars['InternationalLowInsurance'] = ['label' => __('Assicurazione internazionale (fino a 1500 €)','reshark_pdb'), 'description' => __("Le spedizioni internazionali vengono assicurate fino a un valore di 1500 €",'reshark_pdb'), 'type' => 'international'];
		$insure_static_vars['InternationalHighInsurance'] = ['label' => __('Assicurazione internazionale (fino a 50000 €)','reshark_pdb'), 'description' => __("Le spedizioni internazionali vengono assicurate fino a un valore di 50000 €",'reshark_pdb'), 'type' => 'international', 'limit' => 1500];
	
		$array_insureServices = array();
	
		$additionalServices = get_option(RESHARK_SLUG.'_additionalServices');
		$json_additionalServices = json_decode($additionalServices);
		foreach($json_additionalServices as $service){
			$name = $service->name;
			$type = $service->type;
			$availableValues = $service->availableValues; // array
			$mutualExclusion = $service->mutualExclusion; // array
			
			if(array_key_exists($name,$insure_static_vars) && ($insure_static_vars[$name]['type'] == 'national' && $is_national || $insure_static_vars[$name]['type'] == 'international' && $is_international)){
				$active = get_option(RESHARK_SLUG.'_'.$name);
				$area = $insure_static_vars[$name]['type'];
				$limit = array_key_exists('limit',$insure_static_vars[$name]) ? $insure_static_vars[$name]['limit'] : NULL;
				$apply = get_option(RESHARK_SLUG.'_'.$name.'_apply');
				$frontend_label = get_option(RESHARK_SLUG.'_'.$name.'_frontend_label');
				$frontend_description = get_option(RESHARK_SLUG.'_'.$name.'_frontend_description');
				$min = get_option(RESHARK_SLUG.'_'.$name.'_min');
				$cost = get_option(RESHARK_SLUG.'_'.$name.'_cost');
				$cost_type = get_option(RESHARK_SLUG.'_'.$name.'_cost_type');

				$array_insureServices[$name] = ['active' => $active, 'area' => $area, 'limit' => $limit, 'apply' => $apply, 'frontend_label' => $frontend_label, 'frontend_description' => $frontend_description, 'min' => $min, 'cost' => $cost, 'cost_type' => $cost_type];
			}
		}
	?>
	<div id="accordion_insures">
		<?php foreach($array_insureServices as $key => $data): ?>
			<?php echo $data['active'] ? '<h3 for="'.esc_attr($key).'" class="active"><span class="dashicons dashicons-yes-alt"></span>' : '<h3 for="'.esc_attr($key).'"><span class="dashicons dashicons-dismiss"></span>'; ?> <?php echo array_key_exists('label',$insure_static_vars[$key]) ? esc_html($insure_static_vars[$key]['label']) : esc_html($key); ?></h3>
			<div for="<?php echo esc_attr($key); ?>">
				<?php if(array_key_exists('description',$insure_static_vars[$key])): ?><p><?php echo esc_html($insure_static_vars[$key]['description']); ?></p><?php endif; ?>
				<table class="form-table" role="presentation">
					<tbody>
						<tr><th scope="row"><?php _e('Attiva','reshark_pdb'); ?></th>
							<td>
								<input type="checkbox" id="<?php echo esc_attr($key); ?>" name="<?php echo esc_attr($key); ?>" value="1" <?php echo $data['active'] ? 'checked' : ''; ?>>
								<input type="hidden" id="<?php echo esc_attr($key); ?>_area" name="<?php echo esc_attr($key); ?>_area" value="<?php echo $data['area']; ?>">
								<?php if(isset($data['limit'])): ?><input type="hidden" id="<?php echo esc_attr($key); ?>_limit" name="<?php echo esc_attr($key); ?>_limit" value="<?php echo esc_attr($data['limit']); ?>"><?php endif; ?>
							</td>
						</tr>
						<tr><th scope="row"><?php _e('Applica','reshark_pdb'); ?><br><small><?php _e("se obbligatoria, il cliente non potrà deselezionare",'reshark_pdb'); ?></small></th><td><select id="<?php echo esc_attr($key); ?>_apply" name="<?php echo esc_attr($key); ?>_apply"><option value="required" <?php echo $data['apply'] == 'required' ? 'selected' : ''; ?>><?php _e('Obbligatoria','reshark_pdb'); ?></option><option value="optional" <?php echo $data['apply'] == 'optional' ? 'selected' : ''; ?>><?php _e('Opzionale','reshark_pdb'); ?></option></select></td></tr>
						<?php if(array_key_exists('frontend_label',$data)): ?><tr><th scope="row"><?php _e('Testo per il cliente','reshark_pdb'); ?><br><small><?php _e('Visualizzato nel checkout','reshark_pdb'); ?></small></th><td><input type="text" id="<?php echo esc_attr($key); ?>_frontend_label" name="<?php echo esc_attr($key); ?>_frontend_label" value="<?php echo esc_attr($data['frontend_label']); ?>"></td></tr><?php endif; ?>
						<?php if(array_key_exists('frontend_description',$data)): ?><tr><th scope="row"><?php _e('Descrizione per il cliente','reshark_pdb'); ?><br><small><?php _e('Visualizzato nel checkout','reshark_pdb'); ?></small></th><td><input type="text" id="<?php echo esc_attr($key); ?>_frontend_description" name="<?php echo esc_attr($key); ?>_frontend_description" value="<?php echo esc_attr($data['frontend_description']); ?>"></td></tr><?php endif; ?>
						<?php if(array_key_exists('min',$data)): ?><tr><th scope="row"><?php _e('Valore minimo carrello','reshark_pdb'); ?></th><td><input type="number" id="<?php echo esc_attr($key); ?>_min" name="<?php echo esc_attr($key); ?>_min" value="<?php echo esc_attr($data['min']); ?>"> <?php _e('€','reshark_pdb'); ?></td></tr><?php endif; ?>
						<?php if(array_key_exists('cost',$data)): ?><tr><th scope="row"><?php _e('Costo','reshark_pdb'); ?></th><td><input type="number" id="<?php echo esc_attr($key); ?>_cost" name="<?php echo esc_attr($key); ?>_cost" value="<?php echo esc_attr($data['cost']); ?>"><select id="<?php echo esc_attr($key); ?>_cost_type" name="<?php echo esc_attr($key); ?>_cost_type"><option value="fix" <?php echo $data['cost_type'] == 'fix' ? 'selected' : ''; ?>><?php _e('€','reshark_pdb'); ?></option><option value="perc" <?php echo $data['cost_type'] == 'perc' ? 'selected' : ''; ?>>%</option></select><br><br><small for="fix" style="display:<?php echo $data['cost_type'] == 'fix' ? 'block' : 'none'; ?>;"><?php _e('importo fisso da aggiungere al carrello (può essere anche negativo)','reshark_pdb'); ?>;</small><small for="perc" style="display:<?php echo $data['cost_type'] == 'perc' ? 'block' : 'none'; ?>;"><?php _e('percentuale applicata sul valore totale del carrello (può essere anche negativa)','reshark_pdb'); ?>;</small></td></tr><?php endif; ?>
					</tbody>
				</table>
			</div>
		<?php endforeach; ?>
	</div>
	<hr>
	<button type="submit" class="button button-primary">Salva le modifiche</button>
</form>

<script>
	jQuery(function($){
		$(function(){
			$("#accordion_insures").accordion({
				heightStyle: "content"
			});
		});
		$(document).on('submit','#reshark_insures',function(e){
			e.preventDefault();
			var obj_insures = {};
			<?php foreach($array_insureServices as $key => $data): ?>
				obj_insures['<?php echo esc_attr($key); ?>'] = $('#<?php echo esc_attr($key); ?>').is(':checked') ? 1 : 0;
				<?php if(array_key_exists('area',$data)): ?>obj_insures['<?php echo esc_attr($key); ?>_area'] = $('#<?php echo esc_attr($key); ?>_area').val();<?php endif; ?>
				<?php if(isset($data['limit'])): ?>obj_insures['<?php echo esc_attr($key); ?>_limit'] = $('#<?php echo esc_attr($key); ?>_limit').val();<?php endif; ?>
				<?php if(array_key_exists('apply',$data)): ?>obj_insures['<?php echo esc_attr($key); ?>_apply'] = $('#<?php echo esc_attr($key); ?>_apply').val();<?php endif; ?>
				<?php if(array_key_exists('frontend_label',$data)): ?>obj_insures['<?php echo esc_attr($key); ?>_frontend_label'] = $('#<?php echo esc_attr($key); ?>_frontend_label').val();<?php endif; ?>
				<?php if(array_key_exists('frontend_description',$data)): ?>obj_insures['<?php echo esc_attr($key); ?>_frontend_description'] = $('#<?php echo esc_attr($key); ?>_frontend_description').val();<?php endif; ?>
				<?php if(array_key_exists('min',$data)): ?>obj_insures['<?php echo esc_attr($key); ?>_min'] = $('#<?php echo esc_attr($key); ?>_min').val();<?php endif; ?>
				<?php if(array_key_exists('cost',$data)): ?>obj_insures['<?php echo esc_attr($key); ?>_cost'] = $('#<?php echo esc_attr($key); ?>_cost').val();<?php endif; ?>
				<?php if(array_key_exists('cost_type',$data)): ?>obj_insures['<?php echo esc_attr($key); ?>_cost_type'] = $('#<?php echo esc_attr($key); ?>_cost_type').val();<?php endif; ?>
			<?php endforeach; ?>
			var array_insures = Object.entries(obj_insures);
			$.ajax({
				type: 'POST',
				url: ajax.url,
				data: {
					'nonce': ajax.nonce,
					'action':'rpdb_set_insures',
					'array_insures' : array_insures,
				},
				success: function (result) {
					window.location.href = window.location.href;
				},
			});
		});
	});
</script>