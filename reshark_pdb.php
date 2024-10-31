<?php
/**
* Plugin Name: Reshark PDB
* Plugin URI: https://reshark.eu/
* Description: Un’unica piattaforma per l’automazione della tua attività di e-commerce. Nel mare magnum delle vendite online, con ReShark PDB tieni tutto dentro una sola rete!
* Version: 0.1
* Author: Métaxy
* Author URI: https://metaxy.eu/
**/

defined( 'ABSPATH' ) || exit;

if(!defined('RESHARK_ABSPATH')) define('RESHARK_ABSPATH',dirname( __FILE__ ).'/');
if(!defined('RESHARK_PLUGIN_URL')) define('RESHARK_PLUGIN_URL',plugin_dir_url(__FILE__));
if(!defined('RESHARK_NAME')) define('RESHARK_NAME','Reshark PDB');
if(!defined('RESHARK_SLUG')) define('RESHARK_SLUG','reshark_pdb');
if(!defined('RESHARK_URL')) define('RESHARK_URL','https://reshark.eu/');
if(!defined('RESHARK_PROFILE_URL')) define('RESHARK_PROFILE_URL','https://app.reshark.eu/profile/');
if(!defined('RESHARK_SHIPMENT_CONFIGURATION_URL')) define('RESHARK_SHIPMENT_CONFIGURATION_URL','https://app.reshark.eu/shipment/configuration/');
if(!defined('RESHARK_API_URL')) define('RESHARK_API_URL','https://api.reshark.eu/api/');
if(!defined('RESHARK_TRACKING_BASE_URL')) define('RESHARK_TRACKING_BASE_URL','https://deliverynote.eu/');
if(!defined('RESHARK_TRACKING_SHOP')) define('RESHARK_TRACKING_SHOP','WOC');
if(!defined('RESHARK_ACCESSORIES_META_PREFIX')) define('RESHARK_ACCESSORIES_META_PREFIX','reshark_additionalservices_');
if(!defined('RESHARK_STATIC_ACCESSORIES_ARRAY')) define('RESHARK_STATIC_ACCESSORIES_ARRAY',array()); //array('ReverseAtHome','Roundtrip')
if(!defined('RESHARK_INSURES_ARRAY')) define('RESHARK_INSURES_ARRAY',array('FullInsurance','InternationalLowInsurance','InternationalHighInsurance'));

/* Admin Notice */
if(!function_exists('reshark_admin_notice_warning'))
{
	function reshark_admin_notice_warning() {
		$html = '<div class="notice notice-warning is-dismissible"><p>'.esc_html(RESHARK_NAME).' richiede la presenza di Woocommerce, per questo motivo è stato disattivato.</p></div>';
		echo rpdb_wp_kses($html);
	}
}

/* Check plugin */
if(!function_exists('reshark_check_plugin_dependency'))
{
	add_action('admin_init','reshark_check_plugin_dependency');
	function reshark_check_plugin_dependency() {
		/* PLUGIN FUNZIONANTE CON WooCommerce */
		if(!is_plugin_active('woocommerce/woocommerce.php')){
			deactivate_plugins('reshark_pdb/reshark_pdb.php');
			add_action('admin_notices','reshark_admin_notice_warning');
		}
	}
}


// API KEY
include_once RESHARK_ABSPATH . 'includes/api_key.php';

// DIMENSIONS AND WEIGHT
include_once RESHARK_ABSPATH . 'includes/weight_volume.php';

// DROPOFF
include_once RESHARK_ABSPATH . 'includes/dropoff.php';

// ACCESSORIES
include_once RESHARK_ABSPATH . 'includes/accessories.php';

// INSURES
include_once RESHARK_ABSPATH . 'includes/insures.php';

// TRACKING
include_once RESHARK_ABSPATH . 'includes/tracking.php';


/*

// DROP OFF
include_once RESHARK_ABSPATH . 'includes/pickup.php';

// INSURE
include_once RESHARK_ABSPATH . 'includes/insure.php';

// SHIPPING OPTIONS
include_once RESHARK_ABSPATH . 'includes/shipping_options.php';
*/


// ADMIN PAGE
if(!function_exists('reshark_admin_menu'))
{
	add_action('admin_menu','reshark_admin_menu');
	function reshark_admin_menu(){
		add_menu_page(__(RESHARK_NAME,'reshark_pdb'),__(RESHARK_NAME,'reshark_pdb'),'manage_woocommerce',RESHARK_SLUG,'reshark_page_contents','none',58);
		add_submenu_page(RESHARK_SLUG,__(RESHARK_NAME,'reshark_pdb'),__(RESHARK_NAME,'reshark_pdb'),'manage_woocommerce',RESHARK_SLUG,'reshark_page_contents',1);
		add_submenu_page(RESHARK_SLUG,__("Connessione",'reshark_pdb'),__("Connessione",'reshark_pdb'),'manage_woocommerce',RESHARK_SLUG.'&tab=conn','reshark_page_contents',2);
		add_submenu_page(RESHARK_SLUG,__('Configurazioni spedizioni','reshark_pdb'),__('Configurazioni spedizioni','reshark_pdb'),'manage_woocommerce',RESHARK_SLUG.'&tab=shipping_configuration','reshark_page_contents',3);
		add_submenu_page(RESHARK_SLUG,__('Drop-off','reshark_pdb'),__('Drop-off','reshark_pdb'),'manage_woocommerce',RESHARK_SLUG.'&tab=dropoff','reshark_page_contents',4);
		add_submenu_page(RESHARK_SLUG,__('Servizi accessori','reshark_pdb'),__('Servizi accessori','reshark_pdb'),'manage_woocommerce',RESHARK_SLUG.'&tab=accessories','reshark_page_contents',5);
		add_submenu_page(RESHARK_SLUG,__('Assicurazione','reshark_pdb'),__('Assicurazione','reshark_pdb'),'manage_woocommerce',RESHARK_SLUG.'&tab=insure','reshark_page_contents',6);
	}
}

if(!function_exists('reshark_page_contents'))
{
	function reshark_page_contents()
	{
	?>	
		<?php
			$default_tab = null;
			$tab = isset($_GET['tab']) ? esc_attr($_GET['tab']) : $default_tab;
			$rpdb_isValidApiKey = rpdb_isValidApiKey();
		?>
		<div class="wrap">
			<input type="hidden" id="check_input" name="check_input" value="0">
			<h1><?php echo esc_html(RESHARK_NAME); ?></h1>
			<nav class="nav-tab-wrapper">
				<a href="?page=<?php echo RESHARK_SLUG; ?>" class="nav-tab <?php if($tab===null):?>nav-tab-active<?php endif; ?>"><?php echo esc_html(__(RESHARK_NAME,'reshark_pdb')); ?></a>
				<a href="?page=<?php echo RESHARK_SLUG; ?>&tab=conn" class="nav-tab <?php if($tab==='conn'):?>nav-tab-active<?php endif; ?>"><?php echo esc_html(__("Connessione",'reshark_pdb')); ?></a>
				<?php if($rpdb_isValidApiKey): ?>
				<a href="?page=<?php echo RESHARK_SLUG; ?>&tab=shipping_configuration" class="nav-tab <?php if($tab==='shipping_configuration'):?>nav-tab-active<?php endif; ?>"><?php echo esc_html(__('Configurazioni spedizioni','reshark_pdb')); ?></a>
				<a href="?page=<?php echo RESHARK_SLUG; ?>&tab=dropoff" class="nav-tab <?php if($tab==='dropoff'):?>nav-tab-active<?php endif; ?>"><?php echo esc_html(__('Drop-off','reshark_pdb')); ?></a>
				<a href="?page=<?php echo RESHARK_SLUG; ?>&tab=accessories" class="nav-tab <?php if($tab==='accessories'):?>nav-tab-active<?php endif; ?>"><?php echo esc_html(__('Servizi accessori','reshark_pdb')); ?></a>
				<a href="?page=<?php echo RESHARK_SLUG; ?>&tab=insure" class="nav-tab <?php if($tab==='insure'):?>nav-tab-active<?php endif; ?>"><?php echo esc_html(__('Assicurazione','reshark_pdb')); ?></a>
				<?php endif; ?>
			</nav>
			<div class="tab-content">
				<?php 
					switch($tab):
						case 'shipping_configuration': ?>
							<?php include_once RESHARK_ABSPATH.'/src/shipping_configuration.php'; ?>
						<?php break;
						case 'conn': ?>
							<form id="form_apikey" method="POST" action="options.php">
								<?php
									settings_fields(RESHARK_SLUG.'_apikey');
									do_settings_sections(RESHARK_SLUG.'_apikey');
									submit_button();
								?>
							</form>
						<?php break;
						case 'dropoff': ?>
							<?php include_once RESHARK_ABSPATH.'/src/dropoff.php'; ?>
						<?php break;
						case 'insure': ?>
							<?php include_once RESHARK_ABSPATH.'/src/insures.php'; ?>
						<?php break;
						case 'accessories': ?>
							<?php include_once RESHARK_ABSPATH.'/src/accessories.php'; ?>
						<?php break;
						default: ?>
							<h2><?php echo __("Cos'è?",'reshark_pdb'); ?></h2>
							<p>Il plugin Reshark PDB è uno strumento di supporto per i proprietari di negozi online che utilizzano la piattaforma di e-commerce Woocommerce e hanno un contratto di spedizione con Poste Delivery Business di Poste Italiane. Questo plugin semplifica la gestione dei servizi accessori legati alle spedizioni degli ordini, offrendo una vasta gamma di servizi accessori come assicurazione, consegna al piano, consegna su appuntamento, consegna presso un punto di drop-off, etc...</p>
							<p>Per utilizzare il plugin, è necessario attivare un account <a href="https://reshark.eu" target="_blank">Reshark</a>. Una volta attivato, potrai configurare facilmente i servizi accessori offerti dal contratto Poste Delivery Business, in modo da offrire ai tuoi clienti la scelta del servizio di spedizione desiderato durante le operazioni di checkout.</p>
							<p>Grazie al plugin Reshark PDB, potrai offrire ai tuoi clienti un'esperienza di acquisto migliorata, aumentando la soddisfazione del cliente e riducendo il tempo e la fatica necessari per gestire le spedizioni degli ordini. Inoltre, il plugin offre un'interfaccia intuitiva e facile da usare, che ti consentirà di gestire facilmente i servizi accessori che vuoi offrire ai tuoi clienti.</p>
						<?php break;
					endswitch; 
				?>
			</div>
		</div>
	<?php
	}
}


if(!function_exists('rpdb_wp_kses'))
{
	function rpdb_wp_kses($html)
	{
		$arr = array(
			'input' => array('type' => array(),'id' => array(),'class' => array(),'name' => array(),'value' => array(),'checked' => array()),
			'br' => array(),
			'p' => array('id' => array(),'class' => array()),
			'h2' => array('id' => array(),'class' => array()),
			'strong' => array('id' => array(),'class' => array()),
			'small' => array('id' => array(),'class' => array()),
			'table' => array('id' => array(),'class' => array(),'cellspacing' => array(),'cellpadding' => array()),
			'tbody' => array(),
			'thead' => array(),
			'tr' => array('id' => array(),'class' => array()),
			'th' => array('id' => array(),'class' => array(),'colspan' => array()),
			'td' => array('id' => array(),'class' => array(),'colspan' => array()),
			'div' => array('id' => array(),'class' => array()),
			'section' => array('id' => array(),'class' => array()),
			'a' => array('href' => array(),'id' => array(),'class' => array(),'target' => array()),
			'label' => array('for' => array()),
			'select' => array('id' => array(),'name' => array()),
			'option' => array('value' => array(),'selected' => array()),
			'script' => array('type' => array()),
		);
		return wp_kses($html,$arr);
	}
}

if(!function_exists('rpdb_removeFee'))
{
	function rpdb_removeFee($fee_name)
	{
		$fees = WC()->cart->get_fees();
		foreach ($fees as $key => $fee) {
			if($fees[$key]->name === $fee_name) {
				unset($fees[$key]);
			}
		}
		WC()->cart->fees_api()->set_fees($fees);
	}
}

if(!function_exists('rpdb_register_admin_scripts'))
{
	add_action( 'admin_enqueue_scripts', 'rpdb_register_admin_scripts' );
	function rpdb_register_admin_scripts()
	{
		wp_register_style(RESHARK_SLUG.'_admin', plugins_url(RESHARK_SLUG.'/assets/css/admin.css'));	
		wp_register_script(RESHARK_SLUG.'_admin', plugins_url(RESHARK_SLUG.'/assets/js/admin.js'));
	}
}

if(!function_exists('rpdb_admin_load_admin_scripts'))
{
	add_action('admin_enqueue_scripts','rpdb_admin_load_admin_scripts');
	function rpdb_admin_load_admin_scripts($hook)
	{
		// Load only on ?page=reshark_pdb
		//if($hook != 'toplevel_page_'.'reshark_pdb') return;

		// Load style & scripts.
		wp_localize_script('jquery', 'ajax', array(
			'url' => admin_url('admin-ajax.php'),
			'nonce' => wp_create_nonce(RESHARK_SLUG.'_ajax_nonce'),
		));

		wp_enqueue_style(RESHARK_SLUG.'_admin');
		wp_enqueue_script(RESHARK_SLUG.'_admin');
		wp_enqueue_script('jquery-ui-accordion');
	}
}

if(!function_exists('rpdb_style_script'))
{
	add_action('wp_enqueue_scripts', 'rpdb_style_script');
	function rpdb_style_script($hook) {

		wp_localize_script('jquery', 'ajax', array(
			'url' => admin_url('admin-ajax.php'),
			'nonce' => wp_create_nonce(RESHARK_SLUG.'_ajax_nonce'),
		));

		wp_register_style(RESHARK_SLUG.'_ol', plugin_dir_url(__FILE__).'assets/css/ol.css',array(),false,'all');
		wp_register_style(RESHARK_SLUG.'_dropoff', plugin_dir_url(__FILE__).'assets/css/dropoff.css',array(),false,'all');
		wp_register_style(RESHARK_SLUG.'_checkout', plugin_dir_url(__FILE__).'assets/css/checkout.css',array(),false,'all');

		wp_register_script(RESHARK_SLUG.'_ol', plugin_dir_url(__FILE__).'assets/js/ol.js',array(),false,true);
		wp_register_script(RESHARK_SLUG.'_dropoff', plugin_dir_url(__FILE__).'assets/js/dropoff.js',array(),false,true);
		wp_register_script(RESHARK_SLUG.'_accessories', plugin_dir_url(__FILE__).'assets/js/accessories.js',array(),false,true);
		wp_register_script(RESHARK_SLUG.'_insures', plugin_dir_url(__FILE__).'assets/js/insures.js',array(),false,true);

		if(is_checkout()){
			wp_enqueue_style(RESHARK_SLUG.'_ol');
			wp_enqueue_style(RESHARK_SLUG.'_dropoff');
			wp_enqueue_style(RESHARK_SLUG.'_checkout');
			wp_enqueue_script(RESHARK_SLUG.'_ol');
			wp_enqueue_script(RESHARK_SLUG.'_dropoff');
			wp_enqueue_script(RESHARK_SLUG.'_accessories');
			wp_enqueue_script(RESHARK_SLUG.'_insures');
		}
	}
}
