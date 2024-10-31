<?php
if(!defined('ABSPATH')) exit; // Exit if accessed directly

/* ACCESSORIES - SHIPPING OPTIONS */
if(!function_exists('rpdb_set_insures'))
{
	add_action('wp_ajax_rpdb_set_insures','rpdb_set_insures');
	add_action('wp_ajax_nopriv_rpdb_set_insures','rpdb_set_insures');
	function rpdb_set_insures()
	{

		if(!wp_verify_nonce($_POST['nonce'],RESHARK_SLUG.'_ajax_nonce')){ 
			die('Permesso negato'); 
		}

		foreach($_POST['array_insures'] as $ins){
			$ins_key = sanitize_text_field(esc_attr($ins[0]));
			$ins_value = sanitize_text_field(strip_tags(str_replace('\\','',esc_attr($ins[1]))));

			if($ins_value){
				if(!update_option(RESHARK_SLUG.'_'.$ins_key,$ins_value)) add_option(RESHARK_SLUG.'_'.$ins_key,$ins_value);
			}else{
				delete_option(RESHARK_SLUG.'_'.$ins_key);
			}
		}

		die();
	}
}

if(!function_exists('rpdb_allInsures'))
{
	function rpdb_allInsures()
	{
		$all_insures = array();

		$additionalServices = get_option(RESHARK_SLUG.'_additionalServices');

		$json_additionalServices = json_decode($additionalServices);
		foreach($json_additionalServices as $service){
			$name = $service->name;
			if(in_array($name,RESHARK_INSURES_ARRAY)){
				$apply = get_option(RESHARK_SLUG.'_'.$name.'_apply');
				$frontend_label = get_option(RESHARK_SLUG.'_'.$name.'_frontend_label');
				$frontend_description = get_option(RESHARK_SLUG.'_'.$name.'_frontend_description');

				$all_insures[$name] = ['label' => $frontend_label, 'description' => $frontend_description, 'apply' => $apply];
			}
		}

		return $all_insures;
	}
}

if(!function_exists('rpdb_availableFrontendInsures'))
{
	function rpdb_availableFrontendInsures()
	{
		$active_insures = array();

		$shipping_country = WC()->customer->get_shipping_country();
		$subtotal = WC()->cart->subtotal;
		$additionalServices = get_option(RESHARK_SLUG.'_additionalServices');
		$json_additionalServices = json_decode($additionalServices);
		foreach($json_additionalServices as $service){
			$name = $service->name;
			if(in_array($name,RESHARK_INSURES_ARRAY)){
				$active = get_option(RESHARK_SLUG.'_'.$name);
				$area = get_option(RESHARK_SLUG.'_'.$name.'_area');
				$limit = get_option(RESHARK_SLUG.'_'.$name.'_limit');
				$apply = get_option(RESHARK_SLUG.'_'.$name.'_apply');
				$frontend_label = get_option(RESHARK_SLUG.'_'.$name.'_frontend_label');
				$frontend_description = get_option(RESHARK_SLUG.'_'.$name.'_frontend_description');
				$min = get_option(RESHARK_SLUG.'_'.$name.'_min');
				if($active && (!$min || $subtotal >= $min) && ($shipping_country == 'IT' && $area == 'national') || ($shipping_country != 'IT' && $area == 'international') && (!$limit || $limit<=$subtotal)){
					$active_insures[$name] = ['label' => $frontend_label, 'description' => $frontend_description, 'apply' => $apply];
				}else{
					//remove session acc
					WC()->session->__unset(RESHARK_SLUG.'_'.$name);
					WC()->session->set(RESHARK_SLUG.'_'.$name,null);
				}
			}
		}
		return $active_insures;
	}
}

// ############################################################################################################## FRONTEND
if(!function_exists('rpdb_htmlCheckInsures'))
{
	function rpdb_htmlCheckInsures($label,$description,$ins,$session_ins)
	{
		$html = '';
		$html .= '<tr><th>'.$label.''.($description ? '<br><small>'.$description.'</small>' : '').'</th><td>';
		$html .= '<div>';
		$html .= '<input type="checkbox" class="check_ins" name="'.$ins.'" id="'.$ins.'" '.($session_ins ? 'checked' : '').'>';
		$html .= '</div>';
		$html .= '</td></tr>';

		return $html;
	}
}

if(!function_exists('rpdb_insures_woocommerce_review_order_after_shipping'))
{
	add_action('woocommerce_review_order_after_shipping','rpdb_insures_woocommerce_review_order_after_shipping',1);
	function rpdb_insures_woocommerce_review_order_after_shipping(){

		$html = '';
		$active_insures = rpdb_availableFrontendInsures();
		$required_insure = NULL;

		foreach($active_insures as $ins => $texts){
			if($texts['apply'] == 'required'){
				if(isset($required_insure)){
					WC()->session->__unset(RESHARK_SLUG.'_'.$required_insure);
					WC()->session->set(RESHARK_SLUG.'_'.$required_insure,null);
				}
				$required_insure = $ins;
				WC()->session->set(RESHARK_SLUG.'_'.$ins,1);
			}else{
				$session_ins = WC()->session->get(RESHARK_SLUG.'_'.$ins);
				$html .= rpdb_htmlCheckInsures($texts['label'],$texts['description'],$ins,$session_ins);
			}
		}
		
		echo rpdb_wp_kses($html);
	}
}

// SESSION reshark_insure
if(!function_exists('rpdb_set_insure'))
{
	add_action('wp_ajax_rpdb_set_insure','rpdb_set_insure');
	add_action('wp_ajax_nopriv_rpdb_set_insure','rpdb_set_insure');
	function rpdb_set_insure(){

		if(!wp_verify_nonce($_POST['nonce'],RESHARK_SLUG.'_ajax_nonce')){ 
			die('Permesso negato'); 
		}

		$ins = sanitize_text_field($_POST['ins']);
		$value = sanitize_text_field($_POST['value']) == 'true' ? 1 : 0;
		$required = '';

		$active_insures = rpdb_availableFrontendInsures();
		foreach($active_insures as $i => $texts){
			if($texts['apply'] == 'required') $required = $i;
			WC()->session->__unset(RESHARK_SLUG.'_'.$i);
			WC()->session->set(RESHARK_SLUG.'_'.$i,null);
		}

		if($value){
			WC()->session->set(RESHARK_SLUG.'_'.$ins,$value);
		}else{
			WC()->session->__unset(RESHARK_SLUG.'_'.$ins);
			WC()->session->set(RESHARK_SLUG.'_'.$ins,null);
			if($required != '') WC()->session->set(RESHARK_SLUG.'_'.$required,1);
		}
		die();
	}
}

// FEE
if(!function_exists('rpdb_set_insures_fee'))
{
	add_action('woocommerce_cart_calculate_fees','rpdb_set_insures_fee');
	function rpdb_set_insures_fee(){

		$active_insures = rpdb_availableFrontendInsures();
		$subtotal = WC()->cart->subtotal;

		foreach($active_insures as $ins => $texts){
			$session_ins = WC()->session->get(RESHARK_SLUG.'_'.$ins);
			if($session_ins){
				//get options values
				$ins_cost = get_option(RESHARK_SLUG.'_'.$ins.'_cost');
				$ins_cost_type = get_option(RESHARK_SLUG.'_'.$ins.'_cost_type');
				if($ins_cost != 0){
					$ins_fee = $ins_cost_type == 'perc' ? $subtotal/100*$ins_cost : $ins_cost;
					WC()->cart->add_fee($texts['label'].($texts['description'] ? ' ('.$texts['description'].')' : ''),$ins_fee);
				}else{
					rpdb_removeFee($texts['label'].($texts['description'] ? ' ('.$texts['description'].')' : ''));
				}
			}else{
				rpdb_removeFee($texts['label'].($texts['description'] ? ' ('.$texts['description'].')' : ''));
			}
		}
	}
}

//PROCESSING ORDER
if(!function_exists('rpdb_insures_save_checkout_field'))
{
	add_action('woocommerce_checkout_update_order_meta','rpdb_insures_save_checkout_field');
	function rpdb_insures_save_checkout_field($order_id)
	{
		$order = wc_get_order($order_id);
		$fees = $order->get_items('fee');

		$array_insures_fee_name = array();

		$all_insures = rpdb_allInsures();
		foreach($all_insures as $ins => $texts){
			$ins_fees_name = $texts['label'].($texts['description'] ? ' ('.$texts['description'].')' : '');
			$array_insures_fee_name[$ins] = $ins_fees_name;
		}

		foreach($fees as $fee_key => $item){
			$fee_name = $item->get_name();
			$fee_total = $item->get_total();

			$ins_name = array_search($fee_name,$array_insures_fee_name);
			if($ins_name){
				if(!add_post_meta($order_id,RESHARK_ACCESSORIES_META_PREFIX.$ins_name,$fee_total,true)) update_post_meta($order_id,RESHARK_ACCESSORIES_META_PREFIX.$ins_name,$fee_total);
			}
		}
	}
}
?>