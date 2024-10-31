<?php
if(!defined('ABSPATH')) exit; // Exit if accessed directly

/* ACCESSORIES - SHIPPING OPTIONS */

if(!function_exists('rpdb_set_accessories'))
{
	add_action('wp_ajax_rpdb_set_accessories','rpdb_set_accessories');
	add_action('wp_ajax_nopriv_rpdb_set_accessories','rpdb_set_accessories');
	function rpdb_set_accessories(){

		if(!wp_verify_nonce($_POST['nonce'],RESHARK_SLUG.'_ajax_nonce')){ 
			die('Permesso negato'); 
		}

		foreach($_POST['array_accessories'] as $acc){
			$acc_key = sanitize_text_field(esc_attr($acc[0]));
			$acc_value = sanitize_text_field(strip_tags(str_replace('\\','',esc_attr($acc[1]))));

			if($acc_value){
				if(!update_option(RESHARK_SLUG.'_'.$acc_key,$acc_value)) add_option(RESHARK_SLUG.'_'.$acc_key,$acc_value);
			}else{
				delete_option(RESHARK_SLUG.'_'.$acc_key);
			}
		}

		die();
	}
}

if(!function_exists('rpdb_availableFrontendAccessories'))
{
	function rpdb_availableFrontendAccessories()
	{
		$insures = RESHARK_INSURES_ARRAY;
		$active_servizi_accessori = array();

		$subtotal = WC()->cart->subtotal;
		$additionalServices = get_option(RESHARK_SLUG.'_additionalServices');
		$json_additionalServices = json_decode($additionalServices);
		foreach($json_additionalServices as $service){
			$name = $service->name;
			$type = $service->type;
			if(!in_array($name,RESHARK_INSURES_ARRAY) && !in_array($name,RESHARK_STATIC_ACCESSORIES_ARRAY)){
				$active = get_option(RESHARK_SLUG.'_'.$name);
				$frontend_label = get_option(RESHARK_SLUG.'_'.$name.'_frontend_label');
				$frontend_description = get_option(RESHARK_SLUG.'_'.$name.'_frontend_description');
				$min = get_option(RESHARK_SLUG.'_'.$name.'_min');
				if($active && (!$min || $subtotal >= $min)){
					$active_servizi_accessori[$name] = ['label' => $frontend_label, 'description' => $frontend_description, 'type' => $type];
				}else{
					//remove session acc
					WC()->session->__unset(RESHARK_SLUG.'_'.$name);
					WC()->session->set(RESHARK_SLUG.'_'.$name,null);
				}
			}

		}
		return $active_servizi_accessori;
	}
}

if(!function_exists('rpdb_availableStaticAccessories'))
{
	function rpdb_availableStaticAccessories()
	{
		$staticAccessories = RESHARK_STATIC_ACCESSORIES_ARRAY;
		$active_static_servizi_accessori = array();

		foreach($staticAccessories as $service){
			$name = $service;
			$active = get_option(RESHARK_SLUG.'_'.$name);
			$frontend_label = get_option(RESHARK_SLUG.'_'.$name.'_frontend_label');
			$frontend_description = get_option(RESHARK_SLUG.'_'.$name.'_frontend_description');
			if($active) $active_static_servizi_accessori[$name] = ['label' => $frontend_label, 'description' => $frontend_description];		
		}
		return $active_static_servizi_accessori;
	}
}

if(!function_exists('rpdb_activeSessionAccessories'))
{
	function rpdb_activeSessionAccessories($activeAccessories)
	{
		$active_session_servizi_accessori = array();
		foreach($activeAccessories as $key => $acc){
			$session_acc = WC()->session->get(RESHARK_SLUG.'_'.$key);
			if($session_acc) $active_session_servizi_accessori[] = $key;
		}
		return $active_session_servizi_accessori;
	}
}

if(!function_exists('rpdb_accessoryTypeValuesExclusion'))
{
	function rpdb_accessoryTypeValuesExclusion($acc)
	{
		$return = array();

		//FROM DB
		$additionalServices = get_option(RESHARK_SLUG.'_additionalServices');
		$json_additionalServices = json_decode($additionalServices);
		foreach($json_additionalServices as $service){
			if($service->name == $acc){
				$return['type'] = $service->type;
				$return['availableValues'] = $service->availableValues;
				$return['mutualExclusion'] = $service->mutualExclusion;

				if(isset($service->availableValues)){
					foreach($service->availableValues as $value){
						$availableValue_frontend_label = get_option(RESHARK_SLUG.'_'.$acc.'_'.$value.'_frontend_label');
						if($availableValue_frontend_label) $return['availableValues_frontend_label'][$value] = $availableValue_frontend_label;
					}
				}
			}
		}
		return $return;
	}
}

//HTML CHECKOUT
if(!function_exists('rpdb_htmlCheckAccessories'))
{
	function rpdb_htmlCheckAccessories($label,$description,$acc,$session_acc)
	{
		$html = '';
		$html .= '<tr><th>'.$label.''.($description ? '<br><small>'.$description.'</small>' : '').'</th><td>';
		$html .= '<div>';
		$html .= '<input type="checkbox" class="check_acc" name="'.$acc.'" id="'.$acc.'" '.($session_acc ? 'checked' : '').'>';
		$html .= '</div>';
		$html .= '</td></tr>';

		return $html;
	}
}

if(!function_exists('rpdb_htmlInputAccessories'))
{
	function rpdb_htmlInputAccessories($acc,$type,$availableValues,$availableValues_frontend_label,$mutualExclusion)
	{
		//$html = print_r($mutualExclusion).'';
		$html = '';
		switch($type){
			case 'select':
				$html .= '<select class="'.esc_attr($acc).'_type" id="'.esc_attr($acc).'_type" name="'.esc_attr($acc).'_type"><option value="">'.__("Seleziona",'reshark_pdb').'</option>';
				foreach($availableValues as $av_val){
					$html .= '<option value="'.$av_val.'">'.(array_key_exists($av_val,$availableValues_frontend_label) ? $availableValues_frontend_label[$av_val] : $av_val).'</option>';
				}
				$html .= '</select>';
				break;
			case 'date':
				$date_time = new \DateTime();
				$date_time->setTimezone(new DateTimeZone('Europe/Rome'));
				date_add($date_time, date_interval_create_from_date_string('3 weekdays'));
				$start = $date_time->format('Y-m-d');
				date_add($date_time, date_interval_create_from_date_string('5 weekdays'));
				$end = $date_time->format('Y-m-d');

				$html .= '<input class="'.esc_attr($acc).'_type" id="'.esc_attr($acc).'_type" name="'.esc_attr($acc).'_type" type="date" min="'.$start.'" max="'.$end.'">';

				break;
			case 'string':
				$html .= '<input class="'.esc_attr($acc).'_type" id="'.esc_attr($acc).'_type" name="'.esc_attr($acc).'_type" type="text">';
				break;
		}

		return $html;
	}
}

if(!function_exists('rpdb_htmlStaticAccessories'))
{
	function rpdb_htmlStaticAccessories($label,$description)
	{
		$html = '';
		$html .= '<tr><th>'.esc_html($label).'</th><td>';
		$html .= '<div>';
		$html .= ''.($description ? '<small>'.esc_html($description).'</small>' : '').'';
		$html .= '</div>';
		$html .= '</td></tr>';

		return $html;
	}
}

// ############################################################################################################## FRONTEND
if(!function_exists('rpdb_accessories_woocommerce_review_order_after_shipping'))
{
	add_action('woocommerce_review_order_after_shipping','rpdb_accessories_woocommerce_review_order_after_shipping',1);
	function rpdb_accessories_woocommerce_review_order_after_shipping(){

		$html = '';
		$session_dropoff = WC()->session->get(RESHARK_SLUG.'_dropoff');
		$active_servizi_accessori = rpdb_availableFrontendAccessories();
		$active_session_servizi_accessori = rpdb_activeSessionAccessories($active_servizi_accessori);

		$active_static_servizi_accessori = rpdb_availableStaticAccessories();

		if(!$session_dropoff){
			foreach($active_servizi_accessori as $acc => $texts){
				$session_acc = WC()->session->get(RESHARK_SLUG.'_'.$acc);
				$type_values_exclusion = rpdb_accessoryTypeValuesExclusion($acc);

				if($session_acc){
					$html .= rpdb_htmlCheckAccessories($texts['label'],$texts['description'],$acc,$session_acc);
					if($type_values_exclusion['type'] != 'checkbox') $html .= '<tr><td></td><td><div>'.rpdb_htmlInputAccessories($acc,$type_values_exclusion['type'],$type_values_exclusion['availableValues'],(array_key_exists('availableValues_frontend_label',$type_values_exclusion) ? $type_values_exclusion['availableValues_frontend_label'] : array()),$type_values_exclusion['mutualExclusion']).'</div></td></tr>';
				}else{
					$mutualExclusion = $type_values_exclusion['mutualExclusion'];
					if(!isset($mutualExclusion) || count(array_intersect($active_session_servizi_accessori,$mutualExclusion)) == 0) $html .= rpdb_htmlCheckAccessories($texts['label'],$texts['description'],$acc,$session_acc,$mutualExclusion);
				}
			}
			foreach($active_static_servizi_accessori as $acc => $texts){
				$html .= rpdb_htmlStaticAccessories($texts['label'],$texts['description']);
			}
		}else{
			//if active dropoff - remove accessories session 
			foreach($active_servizi_accessori as $acc => $texts){
				WC()->session->__unset(RESHARK_SLUG.'_'.$acc);
				WC()->session->__unset(RESHARK_SLUG.'_'.$acc.'_type'); //UNSET SESSION - acc type
				WC()->session->set(RESHARK_SLUG.'_'.$acc,null);
				WC()->session->set(RESHARK_SLUG.'_'.$acc.'_type',null);
			}
		}
		echo rpdb_wp_kses($html);
	}
}

if(!function_exists('rpdb_set_accessory'))
{
	// SESSION reshark_accessories
	add_action('wp_ajax_rpdb_set_accessory','rpdb_set_accessory');
	add_action('wp_ajax_nopriv_rpdb_set_accessory','rpdb_set_accessory');
	function rpdb_set_accessory(){

		if(!wp_verify_nonce($_POST['nonce'],RESHARK_SLUG.'_ajax_nonce')){ 
			die('Permesso negato'); 
		}

		$acc = sanitize_text_field($_POST['acc']);
		$value = sanitize_text_field($_POST['value']) == 'true' ? 1 : 0;

		if($value){
			WC()->session->set(RESHARK_SLUG.'_'.$acc,$value);
		}else{
			WC()->session->__unset(RESHARK_SLUG.'_'.$acc);
			WC()->session->__unset(RESHARK_SLUG.'_'.$acc.'_type'); //UNSET SESSION - acc type
			WC()->session->set(RESHARK_SLUG.'_'.$acc,null);
			WC()->session->set(RESHARK_SLUG.'_'.$acc.'_type',null);
		}
		die();
	}
}

// FEE
if(!function_exists('rpdb_set_accessories_fee'))
{
	add_action('woocommerce_cart_calculate_fees','rpdb_set_accessories_fee');
	function rpdb_set_accessories_fee(){

		$active_servizi_accessori = rpdb_availableFrontendAccessories();
		$shipping_total = WC()->cart->get_shipping_total();

		foreach($active_servizi_accessori as $acc => $texts){
			$session_acc = WC()->session->get(RESHARK_SLUG.'_'.$acc);
			if($session_acc){
				//get options values
				$acc_cost = get_option(RESHARK_SLUG.'_'.$acc.'_cost');
				$acc_cost_type = get_option(RESHARK_SLUG.'_'.$acc.'_cost_type');
				if($acc_cost != 0){
					$acc_fee = $acc_cost_type == 'perc' ? $shipping_total/100*$acc_cost : $acc_cost;
					WC()->cart->add_fee($texts['label'],$acc_fee);
				}else{
					rpdb_removeFee($texts['label']);
				}
			}else{
				rpdb_removeFee($texts['label']);
			}
		}
	}
}

//VALIDATE CHECKOUT
if(!function_exists('rpdb_process_accessories_fields_checkbox'))
{
	add_action('woocommerce_checkout_process', 'rpdb_process_accessories_fields_checkbox');
	function rpdb_process_accessories_fields_checkbox()
	{
		$active_servizi_accessori = rpdb_availableFrontendAccessories();

		foreach($active_servizi_accessori as $acc => $texts){
			$session_acc = WC()->session->get(RESHARK_SLUG.'_'.$acc);
			if($session_acc && !$_POST[$acc.'_type'] && $texts['type'] != 'checkbox'){
				wc_add_notice( sprintf( __('È necessario indicare '.$texts['label'],'reshark_pdb')), 'error' );
			}
		}		
	}
}

//PROCESSING ORDER
if(!function_exists('rpdb_accessories_save_checkout_field'))
{
	add_action('woocommerce_checkout_update_order_meta', 'rpdb_accessories_save_checkout_field');
	function rpdb_accessories_save_checkout_field($order_id)
	{
		$order = wc_get_order($order_id);

		$active_servizi_accessori = rpdb_availableFrontendAccessories();
		$active_static_servizi_accessori = rpdb_availableStaticAccessories();
		$session_dropoff = WC()->session->get(RESHARK_SLUG.'_dropoff');

		foreach($active_servizi_accessori as $acc => $texts){
			$session_acc = WC()->session->get(RESHARK_SLUG.'_'.$acc);
			if($session_acc && ($_POST[$acc.'_type'] || $texts['type'] == 'checkbox')){
				if($texts['type'] == 'checkbox'){
					if(!add_post_meta($order_id,RESHARK_ACCESSORIES_META_PREFIX.$acc,1,true)) update_post_meta($order_id,RESHARK_ACCESSORIES_META_PREFIX.$acc,1);
				}else{
					if(!add_post_meta($order_id,RESHARK_ACCESSORIES_META_PREFIX.$acc,sanitize_text_field($_POST[$acc.'_type']),true)) update_post_meta($order_id,RESHARK_ACCESSORIES_META_PREFIX.$acc,sanitize_text_field($_POST[$acc.'_type']));
				}

			}
		}
		if(!$session_dropoff){
			foreach($active_static_servizi_accessori as $acc => $texts){
				if(!add_post_meta($order_id,RESHARK_ACCESSORIES_META_PREFIX.$acc,1,true)) update_post_meta($order_id,RESHARK_ACCESSORIES_META_PREFIX.$acc,1);
			}
		}
	}
}

//THANK YOU PAGE
if(!function_exists('rpdb_thankyou_accessories'))
{
	add_action('woocommerce_thankyou','rpdb_thankyou_accessories',5);
	function rpdb_thankyou_accessories($order_id){

		$html = '';

		global $wpdb;
		$results = $wpdb->get_results($wpdb->prepare("SELECT meta_key,meta_value FROM {$wpdb->prefix}postmeta WHERE post_id = %d AND meta_key LIKE %s",$order_id,RESHARK_ACCESSORIES_META_PREFIX.'%'));
		foreach($results as $res){
			$meta_key = $res->meta_key;
			$meta_value = $res->meta_value;
			$name = str_replace(RESHARK_ACCESSORIES_META_PREFIX,'',$meta_key);

			if(!in_array($name,RESHARK_INSURES_ARRAY)){
				$frontend_label = get_option(RESHARK_SLUG.'_'.$name.'_frontend_label');

				$html .= '<tr class="woocommerce-table__line-item order_item"><th>'.($frontend_label ? $frontend_label : $name).'</th><td>'.(!in_array($name,RESHARK_STATIC_ACCESSORIES_ARRAY) ? $meta_value : '').'</td></tr>';
			}

		}

		if($html != '') $html = '<table class="woocommerce-table woocommerce-table--order-details shop_table order_details"><tbody>'.$html.'</tbody></table>';
		echo rpdb_wp_kses($html);
	}
}
?>