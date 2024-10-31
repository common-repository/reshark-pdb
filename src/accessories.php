<?php if(!defined('ABSPATH')) exit; // Exit if accessed directly ?>

<form id="reshark_accessories" name="reshark_accessories" action="options.php" method="POST">
	<h2><?php echo __('Servizi accessori','reshark_pdb'); ?></h2>
	<p><?php echo __('Seleziona i servizi accessori per le tue spedizioni.','reshark_pdb'); ?><br><small><?php echo __('I seguenti servizi sono disponibili per le spedizioni senza il drop-off','reshark_pdb'); ?></small></p>
	<?php
		//Tempo definito - 9 | 10 | 12 [NO CONSEGNA APPUNTAMENTO]
		//Consegna su appuntamento | YYYY/M/D [NO TEMPO DEFINITO]
	
		//Consegna al piano | con ascensore / senza ascensore
		//Consegna al vicino | Nominativo vicino
	
		//Reverso a domicilio [NO CONSEGNA APPUNTAMENTO]
		//Andata e ritorno
	
		$additionalServices_static_vars = array();
		$additionalServices_static_vars['TimeDefinite'] = ['label' => __('Tempo definito','reshark_pdb'), 'description' => __("Possibilità di scegliere l'orario di consegna (9.00 | 10.00 | 12.00)",'reshark_pdb')];
		$additionalServices_static_vars['ScheduledDelivery'] = ['label' => __('Consegna su appuntamento','reshark_pdb'), 'description' => __("Possibilità di scegliere il giorno di consegna",'reshark_pdb')];
		$additionalServices_static_vars['DeliveryAtFloor'] = ['label' => __('Consegna al piano','reshark_pdb'), 'description' => __("Possibilità di scegliere la consegna al piano",'reshark_pdb')];
		$additionalServices_static_vars['DeliveryToNeighbor'] = ['label' => __('Consegna al vicino','reshark_pdb'), 'description' => __("Possibilità di consegna al vicino",'reshark_pdb')];
		$additionalServices_static_vars['ReverseAtHome'] = ['label' => __('Reverse a domicilio','reshark_pdb'), 'description' => __("DESCRIZIONE Reverse a domicilio",'reshark_pdb')];
		$additionalServices_static_vars['Roundtrip'] = ['label' => __('Andata e ritorno','reshark_pdb'), 'description' => __("DESCRIZIONE Andata e ritorno",'reshark_pdb')];
	
		$additionalService_excluded = array('CashOnDelivery','FullInsurance','InternationalLowInsurance','InternationalHighInsurance');
		$additionalService_withoutOptions = RESHARK_STATIC_ACCESSORIES_ARRAY;
		$additionalService_availableValues_frontend = array('DeliveryAtFloor');
	
		$array_additionalServices = array();
	
		$additionalServices = get_option(RESHARK_SLUG.'_additionalServices');
		$json_additionalServices = json_decode($additionalServices);
		foreach($json_additionalServices as $service){
			$name = $service->name;
			$type = $service->type;
			$availableValues = $service->availableValues; // array
			$mutualExclusion = $service->mutualExclusion; // array
			
			if(!in_array($name,$additionalService_excluded)){
				$active = get_option(RESHARK_SLUG.'_'.$name);
				$frontend_label = get_option(RESHARK_SLUG.'_'.$name.'_frontend_label');
				$frontend_description = get_option(RESHARK_SLUG.'_'.$name.'_frontend_description');
				$min = get_option(RESHARK_SLUG.'_'.$name.'_min');
				$cost = get_option(RESHARK_SLUG.'_'.$name.'_cost');
				$cost_type = get_option(RESHARK_SLUG.'_'.$name.'_cost_type');

				if(in_array($name,$additionalService_withoutOptions)){
					$array_additionalServices[$name] = ['active' => $active, 'frontend_label' => $frontend_label, 'frontend_description' => $frontend_description];
				}else{
					$array_additionalServices[$name] = ['active' => $active, 'frontend_label' => $frontend_label, 'frontend_description' => $frontend_description, 'min' => $min, 'cost' => $cost, 'cost_type' => $cost_type];
				}
				
				if(in_array($name,$additionalService_availableValues_frontend)) $array_additionalServices[$name]['availableValues'] = $availableValues;	
			}
		}
	?>
	<div id="accordion_accessories">
		<?php foreach($array_additionalServices as $key => $data): ?>
			<?php echo $data['active'] ? '<h3 for="'.esc_attr($key).'" class="active"><span class="dashicons dashicons-yes-alt"></span>' : '<h3 for="'.esc_attr($key).'"><span class="dashicons dashicons-dismiss"></span>'; ?> <?php echo array_key_exists('label',$additionalServices_static_vars[$key]) ? $additionalServices_static_vars[$key]['label'] : esc_attr($key); ?></h3>
			<div for="<?php echo $key; ?>">
				<?php if(array_key_exists('description',$additionalServices_static_vars[$key])): ?><p><?php echo esc_html($additionalServices_static_vars[$key]['description']); ?></p><?php endif; ?>
				<table class="form-table" role="presentation">
					<tbody>
						<tr><th scope="row"><?php _e('Attiva','reshark_pdb'); ?></th><td><input type="checkbox" id="<?php echo esc_attr($key); ?>" name="<?php echo esc_attr($key); ?>" value="1" <?php echo $data['active'] ? 'checked' : ''; ?>></td></tr>
						<?php if(array_key_exists('frontend_label',$data)): ?><tr><th scope="row"><?php _e('Testo per il cliente','reshark_pdb'); ?><br><small><?php _e('Visualizzato nel checkout','reshark_pdb'); ?></small></th><td><input type="text" id="<?php echo esc_attr($key); ?>_frontend_label" name="<?php echo esc_attr($key); ?>_frontend_label" value="<?php echo esc_attr($data['frontend_label']); ?>"></td></tr><?php endif; ?>
						<?php if(array_key_exists('frontend_description',$data)): ?><tr><th scope="row"><?php _e('Descrizione per il cliente','reshark_pdb'); ?><br><small><?php _e('Visualizzato nel checkout','reshark_pdb'); ?></small></th><td><input type="text" id="<?php echo esc_attr($key); ?>_frontend_description" name="<?php echo esc_attr($key); ?>_frontend_description" value="<?php echo esc_attr($data['frontend_description']); ?>"></td></tr><?php endif; ?>
						
						<?php if(array_key_exists('availableValues',$data)): ?><tr><th scope="row"><?php _e('Scelte possibili per il cliente','reshark_pdb'); ?><br><small><?php _e('Visualizzato nel checkout','reshark_pdb'); ?></small><td>
							
							<?php foreach($data['availableValues'] as $value): ?>
								<?php $availableValue_frontend_label = get_option(RESHARK_SLUG.'_'.$key.'_'.$value.'_frontend_label'); ?>
								<input type="text" id="<?php echo esc_attr($key); ?>_<?php echo esc_attr($value); ?>_frontend_label" name="<?php echo esc_attr($key); ?>_<?php echo esc_attr($value); ?>_frontend_label" value="<?php echo esc_attr($availableValue_frontend_label); ?>" placeholder="<?php echo esc_attr($value); ?>"> <small>(<?php echo esc_html($value); ?>)</small><br>
							<?php endforeach; ?>
							
						</td></th><?php endif; ?>
						
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
			$("#accordion_accessories").accordion({
				heightStyle: "content"
			});
		});
		$(document).on('submit','#reshark_accessories',function(e){
			e.preventDefault();
			var obj_accessories = {};
			<?php foreach($array_additionalServices as $key => $data): ?>
				obj_accessories['<?php echo esc_attr($key); ?>'] = $('#<?php echo esc_attr($key); ?>').is(':checked') ? 1 : 0;
				<?php if(array_key_exists('frontend_label',$data)): ?>obj_accessories['<?php echo esc_attr($key); ?>_frontend_label'] = $('#<?php echo esc_attr($key); ?>_frontend_label').val();<?php endif; ?>
				<?php if(array_key_exists('frontend_description',$data)): ?>obj_accessories['<?php echo esc_attr($key); ?>_frontend_description'] = $('#<?php echo esc_attr($key); ?>_frontend_description').val();<?php endif; ?>
				<?php if(array_key_exists('min',$data)): ?>obj_accessories['<?php echo esc_attr($key); ?>_min'] = $('#<?php echo esc_attr($key); ?>_min').val();<?php endif; ?>
				<?php if(array_key_exists('cost',$data)): ?>obj_accessories['<?php echo esc_attr($key); ?>_cost'] = $('#<?php echo esc_attr($key); ?>_cost').val();<?php endif; ?>
				<?php if(array_key_exists('cost_type',$data)): ?>obj_accessories['<?php echo esc_attr($key); ?>_cost_type'] = $('#<?php echo esc_attr($key); ?>_cost_type').val();<?php endif; ?>
			
				<?php if(array_key_exists('availableValues',$data)): foreach($data['availableValues'] as $value): ?>
					obj_accessories['<?php echo esc_attr($key); ?>_<?php echo esc_attr($value); ?>_frontend_label'] = $('#<?php echo esc_attr($key); ?>_<?php echo esc_attr($value); ?>_frontend_label').val();
				<?php endforeach; endif; ?>
				
			<?php endforeach; ?>
			var array_accessories = Object.entries(obj_accessories);
			$.ajax({
				type: 'POST',
				url: ajax.url,
				data: {
					'nonce': ajax.nonce,
					'action':'rpdb_set_accessories',
					'array_accessories' : array_accessories,
				},
				success: function (result) {
					window.location.href = window.location.href;
				},
			});
		});
	});
</script>