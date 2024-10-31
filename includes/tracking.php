<?php
if(!defined('ABSPATH')) exit; // Exit if accessed directly

/* TRACKING */
if(!function_exists('rpdb_tracking_shipping_fields') && !function_exists('rpdb_tracking_add_meta_boxes'))
{
    function rpdb_tracking_shipping_fields()
    {
        global $post;
		$order_id = $post->ID;
		$brand = get_option(RESHARK_SLUG.'_brand');
		
		if($brand){
			$tracking_url = RESHARK_TRACKING_BASE_URL.$brand.'/'.RESHARK_TRACKING_SHOP.$order_id;
			
			$html = '<table class="woocommerce_order_items" cellspacing="0" cellpadding="0"><tbody><tr><td><a href="'.$tracking_url.'" target="_blank">'.$tracking_url.'</a></td></tr></tbody></table>';
			echo rpdb_wp_kses($html);
		}
    }
	
	add_action('add_meta_boxes','rpdb_tracking_add_meta_boxes');
	function rpdb_tracking_add_meta_boxes()
    {
        add_meta_box('reshark_tracking_fields',__('Tracking url','reshark_pdb'), 'rpdb_tracking_shipping_fields', 'shop_order', 'normal', 'core' );
    }
}

/* FRONTEND VIEW ORDER TRACKING */
if(!function_exists('rpdb_tracking'))
{
	add_action('woocommerce_view_order','rpdb_tracking');
	function rpdb_tracking($order_id){

		$order = wc_get_order($order_id);
		$brand = get_option(RESHARK_SLUG.'_brand');

		if($brand){
			$tracking_url = RESHARK_TRACKING_BASE_URL.$brand.'/'.RESHARK_TRACKING_SHOP.$order_id;
			$html = '<section class="woocommerce-order-details"><h2 class="woocommerce-order-details__title"'.__('Tracking','reshark_pdb').'</h2><table class="woocommerce-table woocommerce-table--order-details shop_table order_details"><tbody><tr><th>'.__('Tracking url','reshark_pdb').'</th><td><a href="'.esc_url($tracking_url).'" target="_blank">'.esc_url($tracking_url).'</a></td><tr></tbody></table></section>';
			
			echo rpdb_wp_kses($html);
		}
	}
}
?>