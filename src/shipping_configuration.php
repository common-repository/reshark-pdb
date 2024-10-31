<?php if(!defined('ABSPATH')) exit; // Exit if accessed directly ?>

<form id="reshark_shipping_conf" name="reshark_shipping_conf" action="options.php" method="POST">
	<h2><?php echo __('Configurazioni spedizioni','reshark_pdb'); ?></h2>
	<p><?php echo __('Per ogni metodo di spedizione è possibile associare una configurazione corriere precedentemente impostata su Reshark.<br>Nel caso in cui non sia stata ancora impostata alcuna configurazione corriere su Reshark, è possibile farlo direttamente dalla pagina di <a href="'.RESHARK_SHIPMENT_CONFIGURATION_URL.'" target="_blank">configurazione trasporti</a>','reshark_pdb'); ?></p>
	<?php
		$array_shipping_methods = array();
		$html = '';
		foreach(rpdb_get_all_shipping_zones() as $zone){
			$zone_id = $zone->get_id();
			$zone_name = $zone->get_zone_name();
			$zone_order = $zone->get_zone_order();
			$zone_locations = $zone->get_zone_locations();
			$zone_formatted_location = $zone->get_formatted_location();
			$zone_shipping_methods = $zone->get_shipping_methods();

			$html .= '<table><thead><th colspan="2"><strong>'.$zone_name.'</strong> ('.esc_html($zone_formatted_location).')</th></thead><tbody>';
			foreach($zone_shipping_methods as $index => $method){
				$method_is_taxable = $method->is_taxable();
				$method_is_enabled = $method->is_enabled();
				$method_instance_id = $method->get_instance_id();
				$method_title = $method->get_method_title(); // e.g. "Flat Rate"
				$method_user_title = $method->get_title(); // e.g. whatever you renamed "Flat Rate" into
				$method_rate_id = $method->get_rate_id(); // e.g. "flat_rate:18"

				$array_shipping_methods[] = $method_instance_id;

				$option_conf = get_option(RESHARK_SLUG.'_conf_shipping_'.$method_instance_id);

				$html .= '<tr><td>'.esc_html($method_user_title).'</td><td>'.rpdb_selectConf($method_instance_id).'</td></tr>';
			}
			$html .= '<tr><td colspan="2"></td></tr></tbody></table><hr>';
		}
	
		echo rpdb_wp_kses($html);
	?>
	<button type="submit" class="button button-primary">Salva le modifiche</button>
</form>
<script>
	jQuery(function($){
		$(document).on('submit','#reshark_shipping_conf',function(e){
			e.preventDefault();
			var obj_shipping_conf = {};
			<?php foreach($array_shipping_methods as $sm): ?>
			obj_shipping_conf[<?php echo esc_attr($sm); ?>] = $('#shipping_conf_<?php echo esc_attr($sm); ?>').val();
			<?php endforeach; ?>
			var array_shipping_conf = Object.entries(obj_shipping_conf);

			$.ajax({
				type: 'POST',
				url: ajax.url,
				data: {
					'nonce': ajax.nonce,
					'action':'rpdb_set_shipping_conf',
					'array_shipping_conf' : array_shipping_conf,
				},
				success: function (result) {
					window.location.href = window.location.href;
				},
			});
		});
	});
</script>