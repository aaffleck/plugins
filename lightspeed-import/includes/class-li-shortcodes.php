<?php
/**
 * LI_Shortcodes class.
 *
 * @class 		LI_Shortcodes
 * @version		1.0
 * @package		LightSpeed Inteleck/Classes
 * @category	Class
 * @author 		Inteleck
 */
class LI_Shortcodes {

	

	/**
	 * Init shortcodes
	 */
	public static function init() {
		// Define shortcodes
		$shortcodes = array(
			'lightspeed_products'                   => __CLASS__ . '::lightspeed_products',
			'lightspeed_product_tags'				 => __CLASS__ .	'::lightspeed_product_tags',
			'lightspeed_product_brands'				 => __CLASS__ .	'::lightspeed_product_brands',
			'lightspeed_product_categories'         => __CLASS__ . '::lightspeed_product_categories',
			'lightspeed_recent_products'            => __CLASS__ . '::lightspeed_recent_products',
			'lightspeed_sale_products'              => __CLASS__ . '::lightspeed_sale_products',
			'lightspeed_best_selling_products'      => __CLASS__ . '::lightspeed_best_selling_products',
			'lightspeed_top_rated_products'         => __CLASS__ . '::lightspeed_top_rated_products',
			'lightspeed_featured_products'          => __CLASS__ . '::lightspeed_featured_products',
		);

		foreach ( $shortcodes as $shortcode => $function ) {
			add_shortcode( apply_filters( "{$shortcode}_shortcode_tag", $shortcode ), $function );
		}
		
		
		
	}

	/**
	 * Shortcode Wrapper
	 *
	 * @param mixed $function
	 * @param array $atts (default: array())
	 * @return string
	 */
	public static function shortcode_wrapper(
		$function,
		$atts    = array(),
		$wrapper = array(
			'class'  => 'woocommerce',
			'before' => null,
			'after'  => null
		)
	) {
		ob_start();

		$before 	= empty( $wrapper['before'] ) ? '<div class="' . esc_attr( $wrapper['class'] ) . '">' : $wrapper['before'];
		$after 		= empty( $wrapper['after'] ) ? '</div>' : $wrapper['after'];

		echo $before;
		call_user_func( $function, $atts );
		echo $after;

		return ob_get_clean();
	}
	
	
	public static function lightspeed_product_tags($atts=array()){
	
		extract( shortcode_atts( array(
			'orderby'	=> 'name', 
			'order'		=> 'ASC',
			'hide_empty'=> false, 
		), $atts ) );

		ob_start();
		
		$limit = 100;
		$output = 25;
		$tags = array();
		
		$wp_session = WP_Session::get_instance();
		
		//LI()->LI_cache->set("lightspeed_product_tags",null);	
		
		$tags = LI()->LI_cache->get("lightspeed_product_tags");
		
		$t = $wp_session['total_tags'];
		
		if($tags == null){
		
			$emitter = 'https://api.merchantos.com/API/Account/'.LI()->API_account.'/Tag';

			$xml_query_string = 'limit='.$limit;

			$terms = LI()->api->makeAPICall("Account.Tag","Read",null,null,$emitter, $xml_query_string);
		
			$totalrecords = $terms->attributes()->count;
		
			$loop_size = $totalrecords / $limit;
			$feeds = array();

			for ( $i = 0; $i <= $loop_size; $i++ ) {
				$offset = $limit * $i;
				$feeds[] = ( $i === 0 ) ? $terms : LI()->api->makeAPICall("Account.Tag","Read",null,null,$emitter, $xml_query_string."&offset=$offset");
				
			}

			// For each feed, store the results as an array
			$grouped_results = array();
			foreach ( $feeds as $feed ) {
				$xml = $feed;
				if( !$xml ) return false;
				$json = json_encode($xml);
				$grouped_results[] = json_decode($json, TRUE);
			}			
			
			foreach ( $grouped_results as $v ) {
				$tags = array_merge( (array) $tags, (array) $v['Tag'] );
			}
			
			LI()->LI_cache->set("lightspeed_product_tags",$tags);
			$wp_session['total_tags'] = (int)$totalrecords;
		}
		
		
		//get a random number out of total records
		$r = rand(0,$t);
		
		//slice 25 out of the array
		$tags = array_slice($tags, $r, 25);
		
		echo '<div class="tagcloud">';
		foreach ( $tags as $tag ) {
			$slug = sanitize_title($tag['name']);
			$t = $tag['name'];
			echo '<a href="http://borealpaddle.lightspeedwebstore.com/'.$slug.'" target="_black">'.$t.'</a>';
		}
		echo '</div>';
		
		ob_end_flush();
	}
	
	
	public static function lightspeed_product_brands($atts=array()){
	
		extract( shortcode_atts( array(
			'orderby'	=> 'name', 
			'order'		=> 'ASC',
			'hide_empty'=> false, 
		), $atts ) );

		ob_start();
		
		$limit = 100;
		$output = 25;
		$tags = array();
		
		$wp_session = WP_Session::get_instance();
		
		//LI()->LI_cache->set("lightspeed_product_tags",null);	
		
		$brands = LI()->LI_cache->get("lightspeed_product_tags");
		
		$t = $wp_session['total_tags'];
		
		if($tags == null){
		
			$emitter = 'https://api.merchantos.com/API/Account/'.LI()->API_account.'/Tag';

			$xml_query_string = 'limit='.$limit;

			$terms = LI()->api->makeAPICall("Account.Tag","Read",null,null,$emitter, $xml_query_string);
		
			$totalrecords = $terms->attributes()->count;
		
			$loop_size = $totalrecords / $limit;
			$feeds = array();

			for ( $i = 0; $i <= $loop_size; $i++ ) {
				$offset = $limit * $i;
				$feeds[] = ( $i === 0 ) ? $terms : LI()->api->makeAPICall("Account.Tag","Read",null,null,$emitter, $xml_query_string."&offset=$offset");
				
			}

			// For each feed, store the results as an array
			$grouped_results = array();
			foreach ( $feeds as $feed ) {
				$xml = $feed;
				if( !$xml ) return false;
				$json = json_encode($xml);
				$grouped_results[] = json_decode($json, TRUE);
			}			
			
			foreach ( $grouped_results as $v ) {
				$tags = array_merge( (array) $tags, (array) $v['Tag'] );
			}
			
			LI()->LI_cache->set("lightspeed_product_tags",$tags);
			$wp_session['total_tags'] = (int)$totalrecords;
		}
		
		
		//get a random number out of total records
		$r = rand(0,$t);
		
		//slice 25 out of the array
		$tags = array_slice($tags, $r, 25);
		
		echo '<div class="tagcloud">';
		foreach ( $tags as $tag ) {
			$slug = sanitize_title($tag['name']);
			$t = $tag['name'];
			echo '<a href="http://borealpaddle.lightspeedwebstore.com/'.$slug.'" target="_black">'.$t.'</a>';
		}
		echo '</div>';
		
		ob_end_flush();
	}


	/**
	 * List all (or limited) product categories
	 *
	 * @access public
	 * @param array $atts
	 * @return string
	 */
	public static function lightspeed_product_categories( $atts ) {
		global $woocommerce_loop;

		extract( shortcode_atts( array(
			'number'     => null,
			'orderby'    => 'name',
			'order'      => 'ASC',
			'columns' 	 => '4',
			'hide_empty' => 1,
			'parent'     => ''
		), $atts ) );

		if ( isset( $atts[ 'ids' ] ) ) {
			$ids = explode( ',', $atts[ 'ids' ] );
			$ids = array_map( 'trim', $ids );
		} else {
			$ids = array();
		}

		$hide_empty = ( $hide_empty == true || $hide_empty == 1 ) ? 1 : 0;

		// get terms and workaround WP bug with parents/pad counts
		$args = array(
			'orderby'    => $orderby,
			'order'      => $order,
			'hide_empty' => $hide_empty,
			'include'    => $ids,
			'pad_counts' => true,
			'child_of'   => $parent
		);

		$product_categories = get_terms( 'product_cat', $args );

		if ( $parent !== "" ) {
			$product_categories = wp_list_filter( $product_categories, array( 'parent' => $parent ) );
		}

		if ( $hide_empty ) {
			foreach ( $product_categories as $key => $category ) {
				if ( $category->count == 0 ) {
					unset( $product_categories[ $key ] );
				}
			}
		}

		if ( $number ) {
			$product_categories = array_slice( $product_categories, 0, $number );
		}

		$woocommerce_loop['columns'] = $columns;

		ob_start();

		// Reset loop/columns globals when starting a new loop
		$woocommerce_loop['loop'] = $woocommerce_loop['column'] = '';

		if ( $product_categories ) {

			woocommerce_product_loop_start();

			foreach ( $product_categories as $category ) {

				wc_get_template( 'content-product_cat.php', array(
					'category' => $category
				) );

			}

			woocommerce_product_loop_end();

		}

		woocommerce_reset_loop();

		return '<div class="woocommerce columns-' . $columns . '">' . ob_get_clean() . '</div>';
	}

	/**
	 * Recent Products shortcode
	 *
	 * @access public
	 * @param array $atts
	 * @return string
	 */
	public static function lightspeed_recent_products( $atts ) {
		global $woocommerce_loop, $products, $product;

		extract( shortcode_atts( array(
			'tag'		=> '',
			'per_page' 	=> '12',
			'columns' 	=> '4',
			'orderby' 	=> 'date',
			'order' 	=> 'desc'
		), $atts ) );

		$woocommerce_loop['columns'] = $columns;
		
		$products = LI()->LI_cache->get("lightspeed_recent_products");

		ob_start();
		
		if($products==null){

			$emitter = 'https://api.merchantos.com/API/Account/'.LI()->API_account.'/ItemMatrix';

			if(!empty($tag))
				$xml_query_string = 'tag='.$tag.'&limit=100&load_relations=["ItemECommerce","Tags","Images"]';
			else
				$xml_query_string = 'limit=100&orderby=timeStamp&orderby_desc=1&load_relations=["ItemECommerce","Tags","Images"]';

			$products = LI()->api->makeAPICall("Account.ItemMatrix","Read",null,null,$emitter, $xml_query_string);

			//$wp_session = WP_Session::get_instance();

			$products = xml2array($products);

			LI()->LI_cache->set("lightspeed_recent_products",$products,36000);
		}
		
		

		$i=0;
		
		woocommerce_product_loop_start();

		foreach($products as $prod) :

			foreach($prod as $product) :
			
				wc_get_template_part( 'content', 'lightspeedproduct' );
				
			endforeach;
			
		endforeach; // end of the loop.

		woocommerce_product_loop_end();

	
		return '<div class="woocommerce columns-' . $columns . '">' . ob_get_clean() . '</div>';
	}


	/**
	 * List multiple products shortcode
	 *
	 * @access public
	 * @param array $atts
	 * @return string
	 */
	public static function lightspeed_products( $atts ) {
		global $woocommerce_loop, $products, $product;

		extract( shortcode_atts( array(
			'tag'		=> '',
			'per_page' 	=> '12',
			'columns' 	=> '4',
			'orderby' 	=> 'timeStamp',
			'order' 	=> 'desc'
		), $atts ) );

		$woocommerce_loop['columns'] = $columns;

		ob_start();

		$emitter = 'https://api.merchantos.com/API/Account/'.$this->API_account.'/ItemMatrix';

		if(!empty($tag))
			$xml_query_string = 'tag='.$tag.'&limit=100&load_relations=["ItemECommerce","Tags","Images"]';
		else
			$xml_query_string = 'limit=100&orderby='.$orderby.'&orderby_desc=1&load_relations=["ItemECommerce","Tags","Images"]';

		$products = $this->api->makeAPICall("Account.ItemMatrix","Read",null,null,$emitter, $xml_query_string);

		//$wp_session = WP_Session::get_instance();

		$products = xml2array($products);

		$wp_session['products'] = $products;

		//var_dump($wp_session['products']);

		$i=0;
		
		woocommerce_product_loop_start();

		foreach($products as $prod) :

			foreach($prod as $product) :
			
				wc_get_template_part( 'content', 'lightspeedproduct' );
				
			endforeach;
			
		endforeach; // end of the loop.

		woocommerce_product_loop_end();

	
		return '<div class="woocommerce columns-' . $columns . '">' . ob_get_clean() . '</div>';
	}


	/**
	 * List all products on sale
	 *
	 * @access public
	 * @param array $atts
	 * @return string
	 */
	public static function lightspeed_sale_products( $atts ) {
		global $woocommerce_loop;

		extract( shortcode_atts( array(
			'per_page'      => '12',
			'columns'       => '4',
			'orderby'       => 'title',
			'order'         => 'asc'
		), $atts ) );

		// Get products on sale
		$product_ids_on_sale = wc_get_product_ids_on_sale();

		$meta_query   = array();
		$meta_query[] = WC()->query->visibility_meta_query();
		$meta_query[] = WC()->query->stock_status_meta_query();
		$meta_query   = array_filter( $meta_query );

		$args = array(
			'posts_per_page'	=> $per_page,
			'orderby' 			=> $orderby,
			'order' 			=> $order,
			'no_found_rows' 	=> 1,
			'post_status' 		=> 'publish',
			'post_type' 		=> 'product',
			'meta_query' 		=> $meta_query,
			'post__in'			=> array_merge( array( 0 ), $product_ids_on_sale )
		);

		ob_start();

		$products = new WP_Query( apply_filters( 'woocommerce_shortcode_products_query', $args, $atts ) );

		$woocommerce_loop['columns'] = $columns;

		if ( $products->have_posts() ) : ?>

			<?php woocommerce_product_loop_start(); ?>

				<?php while ( $products->have_posts() ) : $products->the_post(); ?>

					<?php wc_get_template_part( 'content', 'product' ); ?>

				<?php endwhile; // end of the loop. ?>

			<?php woocommerce_product_loop_end(); ?>

		<?php endif;

		wp_reset_postdata();

		return '<div class="woocommerce columns-' . $columns . '">' . ob_get_clean() . '</div>';
	}

	/**
	 * List best selling products on sale
	 *
	 * @access public
	 * @param array $atts
	 * @return string
	 */
	public static function lightspeed_best_selling_products( $atts ) {
		global $woocommerce_loop;

		extract( shortcode_atts( array(
			'per_page'      => '12',
			'columns'       => '4'
		), $atts ) );

		$args = array(
			'post_type' 			=> 'product',
			'post_status' 			=> 'publish',
			'ignore_sticky_posts'   => 1,
			'posts_per_page'		=> $per_page,
			'meta_key' 		 		=> 'total_sales',
			'orderby' 		 		=> 'meta_value_num',
			'meta_query' 			=> array(
				array(
					'key' 		=> '_visibility',
					'value' 	=> array( 'catalog', 'visible' ),
					'compare' 	=> 'IN'
				)
			)
		);

		ob_start();

		$products = new WP_Query( apply_filters( 'woocommerce_shortcode_products_query', $args, $atts ) );

		$woocommerce_loop['columns'] = $columns;

		if ( $products->have_posts() ) : ?>

			<?php woocommerce_product_loop_start(); ?>

				<?php while ( $products->have_posts() ) : $products->the_post(); ?>

					<?php wc_get_template_part( 'content', 'product' ); ?>

				<?php endwhile; // end of the loop. ?>

			<?php woocommerce_product_loop_end(); ?>

		<?php endif;

		wp_reset_postdata();

		return '<div class="woocommerce columns-' . $columns . '">' . ob_get_clean() . '</div>';
	}

	/**
	 * List top rated products on sale
	 *
	 * @access public
	 * @param array $atts
	 * @return string
	 */
	public static function lightspeed_top_rated_products( $atts ) {
		global $woocommerce_loop;

		extract( shortcode_atts( array(
			'per_page'      => '12',
			'columns'       => '4',
			'orderby'       => 'title',
			'order'         => 'asc'
			), $atts ) );

		$args = array(
			'post_type' 			=> 'product',
			'post_status' 			=> 'publish',
			'ignore_sticky_posts'   => 1,
			'orderby' 				=> $orderby,
			'order'					=> $order,
			'posts_per_page' 		=> $per_page,
			'meta_query' 			=> array(
				array(
					'key' 			=> '_visibility',
					'value' 		=> array('catalog', 'visible'),
					'compare' 		=> 'IN'
				)
			)
		);

		ob_start();

		add_filter( 'posts_clauses', array( __CLASS__, 'order_by_rating_post_clauses' ) );

		$products = new WP_Query( apply_filters( 'woocommerce_shortcode_products_query', $args, $atts ) );

		remove_filter( 'posts_clauses', array( __CLASS__, 'order_by_rating_post_clauses' ) );

		$woocommerce_loop['columns'] = $columns;

		if ( $products->have_posts() ) : ?>

			<?php woocommerce_product_loop_start(); ?>

				<?php while ( $products->have_posts() ) : $products->the_post(); ?>

					<?php wc_get_template_part( 'content', 'product' ); ?>

				<?php endwhile; // end of the loop. ?>

			<?php woocommerce_product_loop_end(); ?>

		<?php endif;

		wp_reset_postdata();

		return '<div class="woocommerce columns-' . $columns . '">' . ob_get_clean() . '</div>';
	}

	/**
	 * Output featured products
	 *
	 * @access public
	 * @param array $atts
	 * @return string
	 */
	public static function lightspeed_featured_products( $atts ) {
		global $woocommerce_loop, $products, $product;

		extract( shortcode_atts( array(
			'per_page' 	=> '12',
			'columns' 	=> '4',
			'orderby' 	=> 'date',
			'order' 	=> 'desc'
		), $atts ) );

		$woocommerce_loop['columns'] = $columns;
		
		$products = LI()->LI_cache->get("lightspeed_featured_products");

		ob_start();
		
		if($products==null){

			echo "getting from API";
			$emitter = 'https://api.merchantos.com/API/Account/'.LI()->API_account.'/ItemMatrix';

			if(!empty($tag) || $filter == "new")
				$xml_query_string = 'limit=100&orderby=timeStamp&orderby_desc=1&load_relations=["ItemECommerce","Tags","Images"]';
			else
				$xml_query_string = 'tag=beyondyoga&limit=100&load_relations=["ItemECommerce","Tags","Images"]';

			$products = LI()->api->makeAPICall("Account.ItemMatrix","Read",null,null,$emitter, $xml_query_string);

			//$wp_session = WP_Session::get_instance();

			$products = xml2array($products);

			LI()->LI_cache->set("lightspeed_featured_products",$products, 36000);
		}
		
		woocommerce_product_loop_start();

		foreach($products as $prod) :

			foreach($prod as $product) :
			
				wc_get_template_part( 'content', 'lightspeedproduct' );
				
			endforeach;
			
		endforeach; // end of the loop.

		woocommerce_product_loop_end();

	
		return '<div class="woocommerce columns-' . $columns . '">' . ob_get_clean() . '</div>';
	}	
}
