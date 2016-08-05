<?php
	
function new_products_lightspeed($atts){
	/**
	 * Featured Products shortcode
	 *
	 * @param array $atts
	 * @return string
	 */
	
	global $woocommerce_loop, $products, $product;

	// include our handy API wrapper that makes it easy to call the API, it also depends on MOScURL to make the cURL call
	require_once("MOSAPICall.class.php");
	
	extract( shortcode_atts( array(
		'per_page' 	=> '12',
		'columns' 	=> '4',
		'orderby' 	=> 'date',
		'order' 	=> 'desc'
	), $atts ) );
	
	$woocommerce_loop['columns'] = $columns;
	
	

	ob_start();

	$mosapi = new MOSAPICall("992e498dfa5ab5245f5bd5afee4ee1ce6ac6e0a1ee7d11e36480694a9b5282e7","83442");

	$emitter = 'https://api.merchantos.com/API/Account/83442/ItemMatrix';
	
	$xml_query_string = 'limit=100&orderby=timeStamp&orderby_desc=1&load_relations=["ItemECommerce","Tags","Images"]';
	
	$products = $mosapi->makeAPICall("Account.ItemMatrix","Read",null,null,$emitter, $xml_query_string);

	$wp_session = WP_Session::get_instance();
	
	$products = xml2array($products);
	
	$wp_session['products'] = $products;
	
	//var_dump($wp_session['products']);
	
	$i=0;

	//if ( $products->children() ) : ?>

		<?php woocommerce_product_loop_start(); ?>

			<?php foreach($products as $prod) :

				foreach($prod as $product) :
				
					wc_get_template_part( 'content', 'lightspeedproduct' );
					
				endforeach;
				
			endforeach; // end of the loop. ?>

		<?php woocommerce_product_loop_end(); ?>

	<?php //endif;

	return '<div class="woocommerce columns-' . $columns . '">' . ob_get_clean() . '</div>';
}

add_shortcode( 'new_products_lightspeed', 'new_products_lightspeed' ); ?>