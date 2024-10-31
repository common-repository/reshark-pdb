<?php
if(!defined('ABSPATH')) exit; // Exit if accessed directly

/* API KEY */
if(!function_exists('rpdb_apikey_settings_init'))
{
	add_action('admin_init','rpdb_apikey_settings_init');
	function rpdb_apikey_settings_init()
	{
		add_settings_section(
			RESHARK_SLUG.'_apikey_setting_section',
			__('API Key','reshark_pdb'),
			'rpdb_apikey_section_callback_function',
			RESHARK_SLUG.'_apikey'
		);

			add_settings_field(
			   RESHARK_SLUG.'_apikey',
			   __('API Key','reshark_pdb'),
			   'rpdb_apikey_setting_markup',
			   RESHARK_SLUG.'_apikey',
			   RESHARK_SLUG.'_apikey_setting_section'
			);

			$args = array('type' => 'string','sanitize_callback' => 'reshark_apikey_callback','default' => NULL);
			register_setting(RESHARK_SLUG.'_apikey',RESHARK_SLUG.'_apikey',$args);
	}
}

if(!function_exists('rpdb_apikey_section_callback_function'))
{
	function rpdb_apikey_section_callback_function() {
		
		$html_info_plugin = '<p>'.__('Per poter utilizzare il plugin, è necessario disporre di un abbonamento attivo su <a href="'.RESHARK_URL.'" target="_blank">reshark.eu</a> e successivamente recuperare l´API KEY nella propria pagina <a href="'.RESHARK_PROFILE_URL.'">profilo</a></p>','reshark_pdb');		
		echo rpdb_wp_kses($html_info_plugin);

		$apikey = get_option(RESHARK_SLUG.'_apikey');
		$conf = get_option(RESHARK_SLUG.'_conf');

		if($apikey){
			if($conf){
				$class = 'reshark_message reshark_success';
				$message = __('API Key valida','reshark_pdb');
			}else{
				$class = 'reshark_message reshark_error';
				//$message = __('API Key non valida!','reshark_pdb');
				$message = __('API Key non valida. Verificala sul portale <a href="'.RESHARK_URL.'" target="_blank">Reahark</a>','reshark_pdb');
			}
		}else{
			$class = 'reshark_message reshark_warning';
			$message = __('Inserisci la API KEY che trovi nel tuo Profilo di Reshark','reshark_pdb');
		}

		$html_input = '<input type="hidden" id="check_api_key" name="check_api_key" value="'.esc_html($class).'">';
		echo rpdb_wp_kses($html_input);
		
		if($class == 'reshark_message reshark_error'){
			$html_message = '<div class="reshark_message"><p>'.$message.'</p></div>';
			echo rpdb_wp_kses($html_message);
		}
	}
}

if(!function_exists('reshark_apikey_callback'))
{
	function reshark_apikey_callback($input)
	{
		if($input != ''){

			//UserCourierConfigurations
			$conf_id = false;
			$data = ['Courier'=>31]; //POSTEBUSINESS
			$res = resharkAPI($input,'UserCourierConfigurations',$data);
			$response = json_decode($res);
			if(json_last_error() === JSON_ERROR_NONE){
				if(!update_option(RESHARK_SLUG.'_conf',$res)) add_option(RESHARK_SLUG.'_conf',$res);
				$conf_id = json_decode($res)[0]->id;
			}else{
				delete_option(RESHARK_SLUG.'_conf');
			}

			//GetBrand
			$res = resharkAPI($input,'GetBrand',null,'GET');
			if($res){
				if(!update_option(RESHARK_SLUG.'_brand',$res)) add_option(RESHARK_SLUG.'_brand',$res);
			}else{
				delete_option(RESHARK_SLUG.'_brand');
			}

			//CourierAdditionalServices
			if($conf_id){
				$data = ['Courier'=>31,'CourierConfigurationId'=>$conf_id]; //POSTEBUSINESS & conf_id (from UserCourierConfigurations)
				$res = resharkAPI($input,'CourierAdditionalServices',$data);
				if($res){
					if(!update_option(RESHARK_SLUG.'_additionalServices',$res)) add_option(RESHARK_SLUG.'_additionalServices',$res);
				}else{
					delete_option(RESHARK_SLUG.'_additionalServices');
				}
			}else{
				delete_option(RESHARK_SLUG.'_additionalServices');
			}

		}else{
			delete_option(RESHARK_SLUG.'_conf');
			delete_option(RESHARK_SLUG.'_brand');
			delete_option(RESHARK_SLUG.'_additionalServices');
		}
		return $input;
	}
}

if(!function_exists('rpdb_apikey_setting_markup'))
{
	function rpdb_apikey_setting_markup() {
		$apikey = get_option(RESHARK_SLUG.'_apikey');
		?>
		<input type="text" id="<?php echo esc_attr(RESHARK_SLUG); ?>_apikey" name="<?php echo esc_attr(RESHARK_SLUG); ?>_apikey" value="<?php echo esc_html($apikey); ?>">
		<?php
	}
}

if(!function_exists('resharkAPI'))
{
	function resharkAPI($key,$api,$data,$type = 'POST')
	{
		$url = RESHARK_API_URL.$api;
		$setup_array = array('headers' => array('Authorization' => 'Bearer '.$key));
		if(isset($data)) $setup_array['body'] = json_encode($data);

		if($type == 'POST'){
			try{			
				$response = wp_remote_post($url,$setup_array);
				$response_code = wp_remote_retrieve_response_code($response);
				$response_body = wp_remote_retrieve_body($response);
				$response = $response_code == 200 ? $response_body : false;
			}catch (Exception $e) {
				return false;
			}
		}elseif($type == 'GET'){
			try{			
				$response = wp_remote_get($url,$setup_array);
				$response_code = wp_remote_retrieve_response_code($response);
				$response_body = wp_remote_retrieve_body($response);
				$response = $response_code == 200 ? $response_body : false;
			}catch (Exception $e) {
				return false;
			}
		}

		return $response;
	}
}

if(!function_exists('rpdb_isValidApiKey'))
{
	function rpdb_isValidApiKey()
	{
		$apikey = get_option(RESHARK_SLUG.'_apikey');
		$conf = get_option(RESHARK_SLUG.'_conf');

		return (($apikey && $conf) ? true : false);
	}
}

if(!function_exists('rpdb_selectConf'))
{
	function rpdb_selectConf($shipping_method_id)
	{
		$conf = get_option(RESHARK_SLUG.'_conf');
		$option_conf = get_option(RESHARK_SLUG.'_conf_shipping_'.$shipping_method_id);

		$array_conf = json_decode($conf);

		$html_select = '';
		$html_select .= '<select id="shipping_conf_'.$shipping_method_id.'" name="shipping_conf_'.$shipping_method_id.'"><option value="">Nessuna</option>';
		foreach($array_conf as $con){
			$html_select .= '<option value="'.$con->id.'" '.($option_conf == $con->id ? 'selected' : '').'>'.$con->name.'</option>';
		}
		$html_select .= '</select>';

		return $html_select;
	}
}

if(!function_exists('rpdb_get_all_shipping_zones'))
{
	function rpdb_get_all_shipping_zones() {
		$data_store = WC_Data_Store::load('shipping-zone');
		$raw_zones = $data_store->get_zones();
		foreach ($raw_zones as $raw_zone){
		  $zones[] = new WC_Shipping_Zone($raw_zone);
		}
		$zones[] = new WC_Shipping_Zone(0);
		return $zones;
	}
}

if(!function_exists('rpdb_set_shipping_conf'))
{
	add_action('wp_ajax_rpdb_set_shipping_conf','rpdb_set_shipping_conf');
	add_action('wp_ajax_nopriv_reshark_set_shipping_conf','rpdb_set_shipping_conf');
	function rpdb_set_shipping_conf(){

		if(!wp_verify_nonce($_POST['nonce'],RESHARK_SLUG.'_ajax_nonce')){ 
			die('Permesso negato'); 
		}

		$array_shipping_conf = $_POST['array_shipping_conf'];
		foreach($array_shipping_conf as $shipping_conf){
			$id_shipping_method = sanitize_text_field($shipping_conf[0]);
			$id_conf = sanitize_text_field($shipping_conf[1]);

			if($id_conf){
				if(!update_option(RESHARK_SLUG.'_conf_shipping_'.$id_shipping_method,$id_conf)) add_option(RESHARK_SLUG.'_conf_shipping_'.$id_shipping_method,$id_conf);
			}else{
				delete_option(RESHARK_SLUG.'_conf_shipping_'.$id_shipping_method);
			}
		}

		die();
	}
}
?>