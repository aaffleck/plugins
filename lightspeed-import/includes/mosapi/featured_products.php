<?php

require_once("mosapi-functions.php");
	
function featured_products_lightspeed($atts){
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
	
	$xml_query_string = 'tag=beyondyoga&limit=100&load_relations=["ItemECommerce","Tags","Images"]';
	
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
	

/*function featured_products_lightspeed( $atts ) {
	global $products, $mosapi;
	//print_r($items);
	$html = '<div class="woocommerce columns-4">';
		$html .= '<ul class="products grid-layout">';

		foreach($items->children() as $item) { 
			$slug = sanitize_title($item->description);
			$id = $item->itemMatrixID;
			$lightspeed_id = '1000000000' + $item->itemMatrixID;
			$link = "http://borealpaddle.lightspeedwebstore.com/". $slug ."/dp/". $lightspeed_id ;
			$has_image = (!empty($item->Images))? true : false;
			$images = $item->Images->Image;
			$image_base = $item->Images->Image[0]->baseImageURL;
			$image_id = $item->Images->Image[0]->publicID;
			$image_url = $image_base .'c_pad,h_400,q_75,w_400/'. $image_id;
			$price = $item->Prices->ItemPrice[0]->amount;		

			if($has_image){
			
				$html .= '<li class="product type-product has-post-thumbnail">';
					$html .= '<div class="product-wrapper fading-controls">';
						$html .= '<div class="animation-section" data-product-link="'. $link .'">';
							$html .= '<div class="product-img-wrapper flip-container">';
								$html .= '<div class="flipper">';
									$html .= '<div class="front img-wrap">';
										$html .= '<img src="'. $image_url .'" alt="'. $item->Images->Image[0]->description .'" />';					
									$html .= '</div>';
								$html .= '</div>';
							$html .= '</div>';
							$html .= '<div class="product-controls-wrapper" data-product-link="'. $link .'">';
								$html .= '<div class="buttons-wrapper">';
									//$html .= '<span data-mfp-src="#popup-'. $id .'" class="lightspeed-popup jckqvBtn"><i class="fa fa-search"></i> Quickview</span>';
									$html .= '<span data-jckqvpid="'.$id.'" class="jckqvBtn"><i class="jckqv-icon-eye"></i> Quickview</span>';
									$html .= '<div class="clear"></div>';
									$html .= '<span class="product-tooltip"></span>';
								$html .= '</div>';
								$html .= '<div class="vertical-helper"></div>';
							$html .= '</div>';
						$html .= '</div>';
						$html .= '<div class="product-description-wrapper">';
							$html .= '<a class="product-title" href="'. $link .'" title="Click to learn more about '. $item->description .'">';
								$html .= '<h3>'. $item->description .'</h3>';
							$html .= '</a>';
							if ( $item->shortDescription ) :
								$html .= '<div itemprop="description" class="entry-content">';
									$html .= $item->shortDescription;
								$html .= '</div>';
							endif;
							$html .= '<div class="product-price-wrapper">';
								$html .= '<span class="price"><span class="amount">&#36; '. $price .'.00</span></span>';
							$html .= '</div>';
						$html .= '</div>';
					$html .= '</div>';
					/*$html .= '<div id="popup-'. $id .'" class="white-popup">';
						$html .= '<div id="slider-'.$id.'" class="slider">';
							$html .= '<a href="#" class="fa fa-angle-right control_next"></a>';
							$html .= '<a href="#" class="fa fa-angle-left control_prev"></a>';
							$html .= '<ul>';
								for($i=0;$i<count($images);$i++){
									$imagebase = $images->image->baseImageURL;
									$imageid = $images->image->publicID;
									$imageurl = $imagebase .'c_pad,h_400,q_75,w_400/'. $imageid;
									$html .= '<li><img src="'. $imageurl .'" alt="'. $images->image->description .'" /></li>';
								}
							$html .= '</ul>';
						$html .= '</div>';
						$html .= '<div id="jckqv_summary">';
							$html .= '<h1>'. $item->description .'</h1>';
							$html .= '<p class="price"><span class="amount">&#36; '. $price .'.00</span></p>';
							$html .= '<div id="jckqv_desc">';
								$html .= $item->shortDescription;
							$html .= '</div>';
						$html .= '</div>';
						$html .= '<button title="Close (Esc)" type="button" class="mfp-close">×</button>';
					$html .= '</div>';
				$html .= '</li>';
			}
		}

		$html .= '</ul>';
	$html .= '</div>';

return $html;
}
*/
add_shortcode( 'lightspeed_products', 'lightspeed_products' ); ?>