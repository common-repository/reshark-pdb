<?php
if(!defined('ABSPATH')) exit; // Exit if accessed directly

/* DROPOFF */

/* ACCESSORIES - SHIPPING OPTIONS */
if(!function_exists('rpdb_set_dropoff_options'))
{
	add_action('wp_ajax_rpdb_set_dropoff_options','rpdb_set_dropoff_options');
	add_action('wp_ajax_nopriv_rpdb_set_dropoff_options','rpdb_set_dropoff_options');
	function rpdb_set_dropoff_options(){

		if(!wp_verify_nonce($_POST['nonce'],RESHARK_SLUG.'_ajax_nonce')){ 
			die('Permesso negato'); 
		}

		foreach($_POST['array_dropoff'] as $acc){
			$acc_key = sanitize_text_field(esc_attr($acc[0]));
			$acc_value = sanitize_text_field(strip_tags(str_replace('\\','',esc_attr($acc[1]))));

			if($acc_value){
				if(!update_option($acc_key,$acc_value)) add_option($acc_key,$acc_value);
			}else{
				delete_option($acc_key);
			}
		}

		die();
	}
}

// ############################################################################################################## ADMIN
if(!function_exists('rpdb_getDropoffLocations'))
{
	function rpdb_getDropoffLocations($url,$apikey,$conf_id,$shipping_postcode,$type)
	{	
		$postdata = ["CourierConfigurationId"=>$conf_id,"Courier"=>"POSTEBUSINESS","CountryCode"=>"IT","Zip"=>$shipping_postcode,"ServiceTypology"=>$type];
		$setup_array = array(
			'headers' => array('Authorization' => 'Bearer '.$apikey),
			'body' => json_encode($postdata),
		);

		try{			
			$response = wp_remote_post($url,$setup_array);
			$response_code = wp_remote_retrieve_response_code($response);
			$response_body = wp_remote_retrieve_body($response);
			$response = $response_code == 200 ? json_decode($response_body) : false;
		}catch (Exception $e) {
			return false;
		}

		return $response;

	}
}

// ############################################################################################################## FRONTEND
if(!function_exists('rpdb_ajaxdropOffLocations'))
{
	add_action('wp_ajax_rpdb_refresh_dropoff_map','rpdb_ajaxdropOffLocations');
	add_action('wp_ajax_nopriv_rpdb_refresh_dropoff_map','rpdb_ajaxdropOffLocations');
	function rpdb_ajaxdropOffLocations()
	{
		if(!wp_verify_nonce($_POST['nonce'],RESHARK_SLUG.'_ajax_nonce')){ 
			die('Permesso negato'); 
		}

		$apikey = get_option(RESHARK_SLUG.'_apikey');
		$conf = get_option(RESHARK_SLUG.'_conf');

		$dropoff = get_option(RESHARK_SLUG.'_dropoff');
		$dropoff_min = get_option(RESHARK_SLUG.'_dropoff_min');

		$shipping_postcode = WC()->cart->get_customer()->get_shipping_postcode();
		$shipping_method = wc_get_chosen_shipping_method_ids()[0];
		$subtotal = WC()->cart->subtotal;

		$order_weight = WC()->cart->get_cart_contents_weight();
		$order_volume = rpdb_get_cart_volume(WC()->cart->get_cart());
		$order_max_side = rpdb_get_cart_max_side(WC()->cart->get_cart());

		$active_servizi_accessori = rpdb_availableFrontendAccessories();
		$array_session_accessories = array();
		foreach($active_servizi_accessori as $acc => $label){
			$session_acc = WC()->session->get(RESHARK_SLUG.'_'.$acc);
			if($session_acc) $array_session_accessories[] = $acc;
		}

		$return = false;
		if($apikey && $conf && $dropoff && (!$dropoff_min || ($subtotal>=$dropoff_min)) && $shipping_postcode && $shipping_method && count($array_session_accessories) == 0 && ($order_weight <= 15 && $order_volume <= 74592 && $order_max_side <= 56)){

			$conf_id = json_decode($conf)[0]->id;
			$url = RESHARK_API_URL.'DropoffLocation';

			$dropoff_locations_apt = rpdb_getDropoffLocations($url,$apikey,$conf_id,$shipping_postcode,"APT"); // APT = punti poste locker
			$dropoff_locations_rtz = rpdb_getDropoffLocations($url,$apikey,$conf_id,$shipping_postcode,"RTZ"); // RTZ = punto poste

			//add marker src
			if(is_array($dropoff_locations_apt)) foreach($dropoff_locations_apt as $loc) $loc->marker = RESHARK_PLUGIN_URL.'assets/images/marker_yellow.png';
			if(is_array($dropoff_locations_rtz)) foreach($dropoff_locations_rtz as $loc) $loc->marker = RESHARK_PLUGIN_URL.'assets/images/marker_blue.png';

			$return = ((!$dropoff_locations_apt && !$dropoff_locations_rtz) ? false : (is_array($dropoff_locations_apt) && is_array($dropoff_locations_rtz) ? array_merge($dropoff_locations_apt,$dropoff_locations_rtz) : (is_array($dropoff_locations_apt) ? $dropoff_locations_apt : $dropoff_locations_rtz)));

		}

		header('Content-Type: application/json');
		echo json_encode($return);
		die();
	}
}

if(!function_exists('rpdb_dropOffLocations'))
{
	function rpdb_dropOffLocations()
	{	
		$apikey = get_option(RESHARK_SLUG.'_apikey');
		$conf = get_option(RESHARK_SLUG.'_conf');

		$dropoff = get_option(RESHARK_SLUG.'_dropoff');
		$dropoff_min = get_option(RESHARK_SLUG.'_dropoff_min');

		$shipping_postcode = WC()->cart->get_customer()->get_shipping_postcode();
		$shipping_method = wc_get_chosen_shipping_method_ids()[0];
		$subtotal = WC()->cart->subtotal;

		$order_weight = WC()->cart->get_cart_contents_weight();
		$order_volume = rpdb_get_cart_volume(WC()->cart->get_cart());
		$order_max_side = rpdb_get_cart_max_side(WC()->cart->get_cart());

		$active_servizi_accessori = rpdb_availableFrontendAccessories();
		$array_session_accessories = array();
		foreach($active_servizi_accessori as $acc => $label){
			$session_acc = WC()->session->get(RESHARK_SLUG.'_'.$acc);
			if($session_acc) $array_session_accessories[] = $acc;
		}

		$return = false;
		if($apikey && $conf && $dropoff && (!$dropoff_min || ($subtotal>=$dropoff_min)) && $shipping_postcode && $shipping_method && count($array_session_accessories) == 0 && ($order_weight <= 15 && $order_volume <= 74592 && $order_max_side <= 56)){

			$conf_id = json_decode($conf)[0]->id;
			$url = RESHARK_API_URL.'DropoffLocation';

			$dropoff_locations_apt = rpdb_getDropoffLocations($url,$apikey,$conf_id,$shipping_postcode,"APT"); // APT = punti poste locker
			$dropoff_locations_rtz = rpdb_getDropoffLocations($url,$apikey,$conf_id,$shipping_postcode,"RTZ"); // RTZ = punto poste

			//add marker src
			if(is_array($dropoff_locations_apt)) foreach($dropoff_locations_apt as $loc) $loc->marker = plugin_dir_url(__FILE__).'assets/images/marker_yellow.png';
			if(is_array($dropoff_locations_rtz)) foreach($dropoff_locations_rtz as $loc) $loc->marker = plugin_dir_url(__FILE__).'assets/images/marker_blue.png';

			$return = ((!$dropoff_locations_apt && !$dropoff_locations_rtz) ? false : (is_array($dropoff_locations_apt) && is_array($dropoff_locations_rtz) ? array_merge($dropoff_locations_apt,$dropoff_locations_rtz) : (is_array($dropoff_locations_apt) ? $dropoff_locations_apt : $dropoff_locations_rtz)));

		}
		return $return;
	}
}

// SESSION reshark_dropoff 
if(!function_exists('rpdb_set_dropoff'))
{
	add_action('wp_ajax_rpdb_set_dropoff','rpdb_set_dropoff');
	add_action('wp_ajax_nopriv_rpdb_set_dropoff','rpdb_set_dropoff');
	function rpdb_set_dropoff(){

		if(!wp_verify_nonce($_POST['nonce'],RESHARK_SLUG.'_ajax_nonce')){ 
			die('Permesso negato'); 
		}

		$value = sanitize_text_field($_POST['value']) == 'true' ? 1 : 0;

		if($value){
			WC()->session->set(RESHARK_SLUG.'_dropoff',$value);
		}else{
			WC()->session->__unset(RESHARK_SLUG.'_dropoff');
			//UNSET SESSION - locations and choosed point
			WC()->session->__unset(RESHARK_SLUG.'_dropoff_locations');
			WC()->session->__unset(RESHARK_SLUG.'_dropoff_point');
		}
		die();
	}
}


// CHECKBOX DROPOFF - if enabled && zipcode is insert
if(!function_exists('rpdb_dropoff_woocommerce_cart_totals_after_shipping_action'))
{
	add_action('woocommerce_review_order_after_shipping','rpdb_dropoff_woocommerce_cart_totals_after_shipping_action',1);
	function rpdb_dropoff_woocommerce_cart_totals_after_shipping_action(){

		$html = '';
		$show_dropoff = rpdb_dropOffLocations();

		if($show_dropoff){
			$session_dropoff = WC()->session->get(RESHARK_SLUG.'_dropoff');
			$session_dropoff_point = WC()->session->get(RESHARK_SLUG.'_dropoff_point');
			$session_dropoff_point_company = WC()->session->get(RESHARK_SLUG.'_dropoff_point_company');
			$session_dropoff_point_address_1 = WC()->session->get(RESHARK_SLUG.'_dropoff_point_address_1');
			$session_dropoff_point_city = WC()->session->get(RESHARK_SLUG.'_dropoff_point_city');
			$session_dropoff_point_state = WC()->session->get(RESHARK_SLUG.'_dropoff_point_state');
			$session_dropoff_point_postcode = WC()->session->get(RESHARK_SLUG.'_dropoff_point_postcode');
			$session_dropoff_point_country = WC()->session->get(RESHARK_SLUG.'_dropoff_point_country');

			$html .= '<tr><th>'.__('Drop off','reshark_pdb').'</th><td>';
			$html .= '<div>';
			$html .= '<input type="checkbox" name="dropoff" id="dropoff" value="1" '.($session_dropoff ? 'checked' : '').'>';
			$html .= '<label for="dropoff"><small>'.__('Spedisci il mio ordine in un punto di drop-off','reshark_pdb').'</small></label>';
			$html .= '</div>';
			$html .= '</td></tr>';

			if($session_dropoff) $html .= '<tr><td colspan="2"><a href="#" id="btn_dropoff_popup" class="btn_dropoff_popup">'.__('Seleziona il punto di drop-off','reshark_pdb').'</a></td></tr>';
			if($session_dropoff_point) $html .= '<tr><td><p>'.__('Punto di drop-off','reshark_pdb').':</p></td><td class="dropoff_point"><p><strong>'.$session_dropoff_point_company.'</strong><br>'.$session_dropoff_point_address_1.'<br>'.$session_dropoff_point_city.' '.$session_dropoff_point_postcode.' '.$session_dropoff_point_state.'</p></td></tr>';
		}else{
			WC()->session->__unset(RESHARK_SLUG.'_dropoff');
			WC()->session->set(RESHARK_SLUG.'_dropoff',null);
			//UNSET SESSION - locations and choosed point
			WC()->session->__unset(RESHARK_SLUG.'_dropoff_locations');
			WC()->session->__unset(RESHARK_SLUG.'_dropoff_point');
			WC()->session->set(RESHARK_SLUG.'_dropoff_locations',null);
			WC()->session->set(RESHARK_SLUG.'_dropoff_point',null);
		}

		echo rpdb_wp_kses($html);
	}
}


// FEE - Cost or discount
if(!function_exists('rpdb_set_dropoff_fee'))
{
	add_action('woocommerce_cart_calculate_fees','rpdb_set_dropoff_fee');
	function rpdb_set_dropoff_fee(){

		$dropoff_cost = get_option(RESHARK_SLUG.'_dropoff_cost');
		$dropoff_cost_type = get_option(RESHARK_SLUG.'_dropoff_cost_type');

		$session_dropoff = WC()->session->get(RESHARK_SLUG.'_dropoff');
		$shipping_total = WC()->cart->get_shipping_total();

		if($session_dropoff && $dropoff_cost != 0){
			$dropoff_total = $dropoff_cost_type == 'perc' ? $shipping_total/100*$dropoff_cost : $dropoff_cost;
			WC()->cart->add_fee(__('Drop off','reshark_pdb'),$dropoff_total);
		}else{
			rpdb_removeFee(__('Drop off','reshark_pdb'));
		}
	}
}

if(!function_exists('rpdb_dropoff_popup_woocommerce_after_cart_action'))
{
	//add_action('woocommerce_review_order_before_payment','rpdb_dropoff_popup_woocommerce_after_cart_action',3);
	function rpdb_dropoff_popup_woocommerce_after_cart_action()
	{
		$session_dropoff = WC()->session->get(RESHARK_SLUG.'_dropoff');
		$html_dropoff_popup = '<div id="dropoff_popup" class="dropoff_popup"><div id="map"></div><div id="popup" class="ol-popup"><a href="#" id="popup-closer" class="ol-popup-closer"></a><div id="popup-content"></div></div></div>';
		
		echo $session_dropoff ? rpdb_wp_kses($html_dropoff_popup) : rpdb_wp_kses('');
	}
}

if(!function_exists('rpdb_footer_dropoff_popup'))
{
	add_action('wp_footer','rpdb_footer_dropoff_popup');
	function rpdb_footer_dropoff_popup()
	{
		// Only on Checkout
		if(is_checkout()){
			$html_checkout = '<div id="dropoff_popup" class="dropoff_popup woocommerce"><div id="map"></div><div id="popup" class="ol-popup"><a href="#" id="popup-closer" class="ol-popup-closer"></a><div id="popup-content"></div></div></div>';
			
			echo rpdb_wp_kses($html_checkout);
		}
	}
}

if(!function_exists('rpdb_set_dropoff_point'))
{
	add_action('wp_ajax_rpdb_set_dropoff_point','rpdb_set_dropoff_point');
	add_action('wp_ajax_nopriv_rpdb_set_dropoff_point','rpdb_set_dropoff_point');
	function rpdb_set_dropoff_point(){

		if(!wp_verify_nonce($_POST['nonce'],RESHARK_SLUG.'_ajax_nonce')){ 
			die('Permesso negato'); 
		}

		$code = sanitize_text_field($_POST['code']);
		$title = sanitize_text_field($_POST['title']);
		$address = sanitize_text_field($_POST['address']);
		$city = sanitize_text_field($_POST['city']);
		$zip = sanitize_text_field($_POST['zip']);
		$province = sanitize_text_field($_POST['province']);
		$country = 'IT';

		if($code){
			WC()->session->set(RESHARK_SLUG.'_dropoff_point',wc_clean($code));
			WC()->session->set(RESHARK_SLUG.'_dropoff_point_company',wc_clean($title));
			WC()->session->set(RESHARK_SLUG.'_dropoff_point_address_1',wc_clean($address));
			WC()->session->set(RESHARK_SLUG.'_dropoff_point_city',wc_clean($city));
			WC()->session->set(RESHARK_SLUG.'_dropoff_point_state',wc_clean($province));
			WC()->session->set(RESHARK_SLUG.'_dropoff_point_postcode',wc_clean($zip));
			WC()->session->set(RESHARK_SLUG.'_dropoff_point_country',wc_clean($country));
		}else{
			WC()->session->__unset(RESHARK_SLUG.'_dropoff_point');
			WC()->session->__unset(RESHARK_SLUG.'_dropoff_point_company');
			WC()->session->__unset(RESHARK_SLUG.'_dropoff_point_address_1');
			WC()->session->__unset(RESHARK_SLUG.'_dropoff_point_city');
			WC()->session->__unset(RESHARK_SLUG.'_dropoff_point_state');
			WC()->session->__unset(RESHARK_SLUG.'_dropoff_point_postcode');
			WC()->session->__unset(RESHARK_SLUG.'_dropoff_point_country');
			WC()->session->set(RESHARK_SLUG.'_dropoff_point',null);
			WC()->session->set(RESHARK_SLUG.'_dropoff_point_company',null);
			WC()->session->set(RESHARK_SLUG.'_dropoff_point_address_1',null);
			WC()->session->set(RESHARK_SLUG.'_dropoff_point_city',null);
			WC()->session->set(RESHARK_SLUG.'_dropoff_point_state',null);
			WC()->session->set(RESHARK_SLUG.'_dropoff_point_postcode',null);
			WC()->session->set(RESHARK_SLUG.'_dropoff_point_country',null);
		}
		die();
	}
}


/* NOTICE MESSAGE */
if(!function_exists('rpdb_check_dropoff'))
{
	add_action('woocommerce_before_checkout_form','rpdb_check_dropoff');
	function rpdb_check_dropoff()
	{
		$session_dropoff = WC()->session->get(RESHARK_SLUG.'_dropoff');
		$session_dropoff_point = WC()->session->get(RESHARK_SLUG.'_dropoff_point');
		if($session_dropoff && !$session_dropoff_point) wc_add_notice( sprintf( __('È necessario indicare il punto di drop-off prima di proseguire','reshark_pdb')), 'error' );
	}
}

/* DISABLE PLACE ORDER */
if(!function_exists('rpdb_replace_order_button_html'))
{
	add_filter('woocommerce_order_button_html','rpdb_replace_order_button_html',10,2);
	function rpdb_replace_order_button_html($order_button)
	{
		$session_dropoff = WC()->session->get(RESHARK_SLUG.'_dropoff');
		$session_dropoff_point = WC()->session->get(RESHARK_SLUG.'_dropoff_point');

		if($session_dropoff && !$session_dropoff_point){

		}else{
			return $order_button;
		}
	}
}

// CHECKOUT
if(!function_exists('rpdb_dropoff_point_woocommerce_before_checkout_form_action'))
{
	add_action('woocommerce_before_checkout_form','rpdb_dropoff_point_woocommerce_before_checkout_form_action');
	function rpdb_dropoff_point_woocommerce_before_checkout_form_action()
	{
		$session_dropoff = WC()->session->get(RESHARK_SLUG.'_dropoff');
		$session_dropoff_point = WC()->session->get(RESHARK_SLUG.'_dropoff_point');
		$session_dropoff_point_company = WC()->session->get(RESHARK_SLUG.'_dropoff_point_company');
		$session_dropoff_point_address_1 = WC()->session->get(RESHARK_SLUG.'_dropoff_point_address_1');
		$session_dropoff_point_city = WC()->session->get(RESHARK_SLUG.'_dropoff_point_city');
		$session_dropoff_point_state = WC()->session->get(RESHARK_SLUG.'_dropoff_point_state');
		$session_dropoff_point_postcode = WC()->session->get(RESHARK_SLUG.'_dropoff_point_postcode');
		$session_dropoff_point_country = WC()->session->get(RESHARK_SLUG.'_dropoff_point_country');

		if($session_dropoff && $session_dropoff_point){
			if($session_dropoff_point_company) WC()->customer->set_shipping_company(wc_clean($session_dropoff_point_company));
			if($session_dropoff_point_address_1) WC()->customer->set_shipping_address(wc_clean($session_dropoff_point_address_1));
			if($session_dropoff_point_address_1) WC()->customer->set_shipping_address_1(wc_clean($session_dropoff_point_address_1));
			//WC()->customer->set_shipping_address_2(wc_clean($shipping_address_2)); 
			if($session_dropoff_point_city) WC()->customer->set_shipping_city(wc_clean($session_dropoff_point_city)); 
			if($session_dropoff_point_state) WC()->customer->set_shipping_state(wc_clean($session_dropoff_point_state)); 
			if($session_dropoff_point_postcode) WC()->customer->set_shipping_postcode(wc_clean($session_dropoff_point_postcode)); 
			if($session_dropoff_point_country) WC()->customer->set_shipping_country(wc_clean($session_dropoff_point_country));

			$script = '<script>jQuery(function($){
					$("#shipping_company").val($("#'.RESHARK_SLUG.'_dropoff_point_company").val());
					$("#shipping_address_1").val($("#'.RESHARK_SLUG.'_dropoff_point_address_1").val());
					$("#shipping_city").val($("#'.RESHARK_SLUG.'_dropoff_point_city").val());
					$("#shipping_postcode").val($("#'.RESHARK_SLUG.'_dropoff_point_postcode").val());
					$("#shipping_country").val($("#'.RESHARK_SLUG.'_dropoff_point_country").val());
					$("#shipping_state").val($("#'.RESHARK_SLUG.'_dropoff_point_state").val());
					$("#ship-to-different-address-checkbox").prop("checked",true);
				  });</script>';
			
			echo rpdb_wp_kses($script);
		}
	}
}

if(!function_exists('rpdb_checkout_hidden_field'))
{
	add_action('woocommerce_after_order_notes','rpdb_checkout_hidden_field');
	function rpdb_checkout_hidden_field($checkout)
	{
		$session_dropoff = WC()->session->get(RESHARK_SLUG.'_dropoff');
		$session_dropoff_point = WC()->session->get(RESHARK_SLUG.'_dropoff_point');
		$session_dropoff_point_company = WC()->session->get(RESHARK_SLUG.'_dropoff_point_company');
		$session_dropoff_point_address_1 = WC()->session->get(RESHARK_SLUG.'_dropoff_point_address_1');
		$session_dropoff_point_city = WC()->session->get(RESHARK_SLUG.'_dropoff_point_city');
		$session_dropoff_point_state = WC()->session->get(RESHARK_SLUG.'_dropoff_point_state');
		$session_dropoff_point_postcode = WC()->session->get(RESHARK_SLUG.'_dropoff_point_postcode');
		$session_dropoff_point_country = WC()->session->get(RESHARK_SLUG.'_dropoff_point_country');

		if($session_dropoff && $session_dropoff_point){
			$html = '<input type="hidden" id="'.esc_attr(RESHARK_SLUG).'_dropoff_point" name="'.esc_attr(RESHARK_SLUG).'_dropoff_point" value="'.esc_attr($session_dropoff_point).'"><input type="hidden" id="'.esc_attr(RESHARK_SLUG).'_dropoff_point_company" name="'.esc_attr(RESHARK_SLUG).'_dropoff_point_company" value="'.esc_attr($session_dropoff_point_company).'"><input type="hidden" id="'.esc_attr(RESHARK_SLUG).'_dropoff_point_address_1" name="'.esc_attr(RESHARK_SLUG).'_dropoff_point_address_1" value="'.esc_attr($session_dropoff_point_address_1).'"><input type="hidden" id="'.esc_attr(RESHARK_SLUG).'_dropoff_point_city" name="'.esc_attr(RESHARK_SLUG).'_dropoff_point_city" value="'.esc_attr($session_dropoff_point_city).'"><input type="hidden" id="'.esc_attr(RESHARK_SLUG).'_dropoff_point_state" name="'.esc_attr(RESHARK_SLUG).'_dropoff_point_state" value="'.esc_attr($session_dropoff_point_state).'"><input type="hidden" id="'.esc_attr(RESHARK_SLUG).'_dropoff_point_postcode" name="'.esc_attr(RESHARK_SLUG).'_dropoff_point_postcode" value="'.esc_attr($session_dropoff_point_postcode).'"><input type="hidden" id="'.esc_attr(RESHARK_SLUG).'_dropoff_point_country" name="'.esc_attr(RESHARK_SLUG).'_dropoff_point_country" value="'.esc_attr($session_dropoff_point_country).'">';
			
			echo rpdb_wp_kses($html);
		}
	}
}

if(!function_exists('rpdb_dropoff_point_woocommerce_review_order_after_shipping'))
{
	//add_action('woocommerce_review_order_after_shipping','rpdb_dropoff_point_woocommerce_review_order_after_shipping',2);
	function rpdb_dropoff_point_woocommerce_review_order_after_shipping()
	{
		$session_dropoff = WC()->session->get(RESHARK_SLUG.'_dropoff');
		$session_dropoff_point = WC()->session->get(RESHARK_SLUG.'_dropoff_point');
		$session_dropoff_point_company = WC()->session->get(RESHARK_SLUG.'_dropoff_point_company');
		$session_dropoff_point_address_1 = WC()->session->get(RESHARK_SLUG.'_dropoff_point_address_1');
		$session_dropoff_point_city = WC()->session->get(RESHARK_SLUG.'_dropoff_point_city');
		$session_dropoff_point_state = WC()->session->get(RESHARK_SLUG.'_dropoff_point_state');
		$session_dropoff_point_postcode = WC()->session->get(RESHARK_SLUG.'_dropoff_point_postcode');
		$session_dropoff_point_country = WC()->session->get(RESHARK_SLUG.'_dropoff_point_country');
		
		$html_dropoff = '<tr><td colspan="2"><a href="#" id="btn_dropoff_popup" class="btn_dropoff_popup">'.esc_html(__('Seleziona il punto di drop-off','reshark_pdb')).'</a></td></tr>';
		$html_dropoff_point = '<tr><td><p>Drop-off:</p></td><td class="dropoff_point"><p><strong>'.$session_dropoff_point_company.'</strong><br>'.$session_dropoff_point_address_1.'<br>'.$session_dropoff_point_city.' '.$session_dropoff_point_postcode.' '.$session_dropoff_point_state.'</p></td></tr>';

		echo $session_dropoff ? rpdb_wp_kses($html_dropoff) : rpdb_wp_kses('');
		echo $session_dropoff_point ? rpdb_wp_kses($html_dropoff_point) : rpdb_wp_kses('');
		
	}
}

//PROCESSING ORDER
if(!function_exists('rpdb_dropoff_save_custom_checkout_hidden_field'))
{
	add_action('woocommerce_checkout_update_order_meta','rpdb_dropoff_save_custom_checkout_hidden_field');
	function rpdb_dropoff_save_custom_checkout_hidden_field($order_id)
	{
		//DROPOFF POINT
		if(!empty($_POST[RESHARK_SLUG.'_dropoff_point'])){
			if(!add_post_meta($order_id,'reshark_dropoffCode',sanitize_text_field($_POST[RESHARK_SLUG.'_dropoff_point']),true)) update_post_meta($order_id,'reshark_dropoffCode',sanitize_text_field($_POST[RESHARK_SLUG.'_dropoff_point']));
		}

		// RESHARK CONF ID
		$order = wc_get_order($order_id);
		$shipping_method = $order->get_shipping_methods();
		foreach($shipping_method as $shipping_method){
			$shipping_method_instance_id = $shipping_method->get_instance_id();
		}
		$option_conf = get_option(RESHARK_SLUG.'_conf_shipping_'.$shipping_method_instance_id);
		if($option_conf){
			if(!add_post_meta($order_id,'reshark_courierConfiguration',sanitize_text_field($option_conf),true)) update_post_meta($order_id,'reshark_courierConfiguration',sanitize_text_field($option_conf));
		}
	}
}
?>