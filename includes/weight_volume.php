<?php
if(!defined('ABSPATH')) exit; // Exit if accessed directly

/* DIMENSIONS AND WEIGHT */
if(!function_exists('rpdb_get_cart_dimensions'))
{
	function rpdb_get_cart_dimensions($items)
	{
		$total_volume = 0;
		$return = 0;
		$perc_delta = 5;
		foreach($items as $item => $values){
			$_product = wc_get_product($values['data']->get_id());
			$_product_length = $_product->get_length();
			$_product_width = $_product->get_width();
			$_product_height = $_product->get_height();
			if($_product_length && $_product_width && $_product_height){
				$_product_volume = $_product_length*$_product_width*$_product_height;
				$quantity = $values['quantity'];
				$total_volume += $_product_volume*$quantity;
			}
		}

		if($total_volume > 0){
			$delta = pow($total_volume,1/3)/100*$perc_delta;
			$return = round(pow($total_volume,1/3)+$delta);
		}

		return $return;
	}
}

if(!function_exists('rpdb_get_cart_volume'))
{
	function rpdb_get_cart_volume($items)
	{
		$total_volume = 0;
		$return = 0;
		foreach($items as $item => $values){
			$_product = wc_get_product($values['data']->get_id());
			$_product_length = $_product->get_length();
			$_product_width = $_product->get_width();
			$_product_height = $_product->get_height();
			if($_product_length && $_product_width && $_product_height){
				$_product_volume = $_product_length*$_product_width*$_product_height;
				$quantity = $values['quantity'];
				$total_volume += $_product_volume*$quantity;
			}
		}

		if($total_volume > 0) $return = $total_volume;

		return $return;
	}
}

if(!function_exists('rpdb_get_cart_max_side'))
{
	function rpdb_get_cart_max_side($items)
	{
		$max_side = 0;
		$return = 0;
		foreach($items as $item => $values){
			$_product = wc_get_product($values['data']->get_id());
			$_product_length = $_product->get_length();
			$_product_width = $_product->get_width();
			$_product_height = $_product->get_height();

			$max_side = max(array($_product_length,$_product_width,$_product_height));
			if($max_side > $return) $return = $max_side;
		}

		return $return;
	}
}

/* ORDER META_DATA */
if(!function_exists('rpdb_before_checkout_create_order'))
{
	add_action('woocommerce_checkout_create_order','rpdb_before_checkout_create_order',20,2);
	function rpdb_before_checkout_create_order($order,$data)
	{
		$order_weight = WC()->cart->get_cart_contents_weight();
		$order_dimensions = rpdb_get_cart_dimensions(WC()->cart->get_cart());
		if($order_weight) $order->update_meta_data('_order_weight',$order_weight);
		if($order_dimensions) $order->update_meta_data('_order_dimensions',$order_dimensions);
	}
}

/* ORDER META_DATA WP-ADMIN */
if(!function_exists('rpdb_order_add_meta_boxes') && !function_exists('rpdb_order_shipping_fields'))
{
	function rpdb_order_shipping_fields()
    {
        global $post;
		
		$order = wc_get_order($post->ID);

        $order_weight = get_post_meta($post->ID,'_order_weight',true) ? get_post_meta($post->ID,'_order_weight',true) : '';
        $order_dimensions = get_post_meta($post->ID,'_order_dimensions',true) ? get_post_meta($post->ID,'_order_dimensions',true) : '';
		$shipping_method = $order->get_shipping_method();

		$html = '';
		
        $html .= '<table class="woocommerce_order_items" cellspacing="0" cellpadding="0"><tbody>';
			if($order_weight) $html .= '<tr><td>'.__('Peso','reshark_pdb').'</td><td>'.$order_weight.'</td></tr>';
			if($order_dimensions) $html .= '<tr><td>'.__('Dimensioni','reshark_pdb').'</td><td>'.$order_dimensions.'</td></tr>';
			if($shipping_method) $html .= '<tr><td>'.__('Spedizione','reshark_pdb').'</td><td>'.$shipping_method.'</td></tr>';
		$html .= '</tbody></table>';
		
		echo rpdb_wp_kses($html);
    }
	
	add_action('add_meta_boxes','rpdb_order_add_meta_boxes');
    function rpdb_order_add_meta_boxes()
    {
        add_meta_box('reshark_order_fields', __('Dettagli spedizione','reshark_pdb'), 'rpdb_order_shipping_fields', 'shop_order', 'normal', 'core' );
    }
}
?>