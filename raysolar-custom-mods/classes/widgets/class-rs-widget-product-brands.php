<?php
/**
 * Product brands Widget
 *
 * @author 		WooThemes
 * @category 	Widgets
 * @package 	WooCommerce/Widgets
 * @version 	2.1.0
 * @extends 	WC_Widget
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class WC_Widget_Product_brands extends WC_Widget { 

	public $brand_ancestors;
	public $current_brand;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->widget_cssclass    = 'woocommerce widget_product_brands';
		$this->widget_description = __( 'A list or dropdown of product brands.', 'woocommerce' );
		$this->widget_id          = 'woocommerce_product_brands';
		$this->widget_name        = __( 'WooCommerce Product brands', 'woocommerce' );
		$this->settings           = array(
			'title'  => array(
				'type'  => 'text',
				'std'   => __( 'Product brands', 'woocommerce' ),
				'label' => __( 'Title', 'woocommerce' )
			),
			'orderby' => array(
				'type'  => 'select',
				'std'   => 'name',
				'label' => __( 'Order by', 'woocommerce' ),
				'options' => array(
					'order' => __( 'Brand Order', 'woocommerce' ),
					'name'  => __( 'Name', 'woocommerce' )
				)
			),
			'dropdown' => array(
				'type'  => 'checkbox',
				'std'   => 0,
				'label' => __( 'Show as dropdown', 'woocommerce' )
			),
			'count' => array(
				'type'  => 'checkbox',
				'std'   => 0,
				'label' => __( 'Show post counts', 'woocommerce' )
			),
			'hierarchical' => array(
				'type'  => 'checkbox',
				'std'   => 1,
				'label' => __( 'Show hierarchy', 'woocommerce' )
			),
			'show_children_only' => array(
				'type'  => 'checkbox',
				'std'   => 0,
				'label' => __( 'Only show children for the current brand', 'woocommerce' )
			)
		);
		parent::__construct();
	}

	/**
	 * widget function.
	 *
	 * @see WP_Widget
	 * @access public
	 * @param array $args
	 * @param array $instance
	 * @return void
	 */
	public function widget( $args, $instance ) {
		extract( $args );

		global $wp_query, $post, $woocommerce;

		$title = apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base );
		$c     = ( isset( $instance['count'] ) && $instance['count'] ) ? '1' : '0';
		$h     = $instance['hierarchical'] ? true : false;
		$s     = ( isset( $instance['show_children_only'] ) && $instance['show_children_only'] ) ? '1' : '0';
		$d     = ( isset( $instance['dropdown'] ) && $instance['dropdown'] ) ? '1' : '0';
		$o     = $instance['orderby'] ? $instance['orderby'] : 'order';

		echo $before_widget;

		if ( $title )
			echo $before_title . $title . $after_title;
		
		$dropdown_args = array();
		$brand_args = array( 'show_count' => $c, 'hierarchical' => $h, 'taxonomy' => 'product_brand' );

		// Menu Order
		$brand_args['menu_order'] = false;
		if ( $o == 'order' ) {
			$brand_args['menu_order'] = 'asc';
		} else {
			$brand_args['orderby'] = 'title';
		}
		
		// Setup Current brand
		$this->current_brand = false;
		$this->brand_ancestors = array();

		if ( is_tax('product_brand') ) {

			$this->current_brand = $wp_query->queried_object;
			$this->brand_ancestors = get_ancestors( $this->current_brand->term_id, 'product_brand' );

		} elseif ( is_singular('product') ) {

			$product_brand = wc_get_product_terms( $post->ID, 'product_brand', array( 'orderby' => 'parent' ) );

			if ( $product_brand ) {
				$this->current_brand   = end( $product_brand );
				$this->brand_ancestors = get_ancestors( $this->current_brand->term_id, 'product_brand' );
			}

		}
		
		// Show Siblings and Children Only
		if ( $s && $this->current_brand ) {
		
			if ( $this->current_brand->parent == 0 ) {
				$brand_children = $this->current_brand->term_id;
			} else {
				$brand_children = $this->current_brand->parent;
			}
			
			$current_brand_children = get_term_children( $brand_children, 'product_brand' );
			
			if ( $current_brand_children ) {
				$current_brand_children = implode ( ", ", $current_brand_children );
				$dropdown_args['include'] = $current_brand_children;
				$brand_args['include'] = $current_brand_children;
			}
			
		}

		// Dropdown
		if ( $d ) {

			$dropdown_defaults = array(
				'show_counts'        => $c,
				'hierarchical'       => $h,
				'show_uncategorized' => 0,
				'orderby'            => $o
			);
			$dropdown_args = wp_parse_args( $dropdown_args, $dropdown_defaults );

			// Stuck with this until a fix for http://core.trac.wordpress.org/ticket/13258
			wc_product_dropdown_brands( $dropdown_args );
			?>
			<script type='text/javascript'>
			/* <![CDATA[ */
				var product_brand_dropdown = document.getElementById("dropdown_product_brand");
				function onProductBrandChange() {
					if ( product_brand_dropdown.options[product_brand_dropdown.selectedIndex].value !=='' ) {
						location.href = "<?php echo home_url(); ?>/?product_brand="+product_brand_dropdown.options[product_brand_dropdown.selectedIndex].value;
					}
				}
				product_brand_dropdown.onchange = onProductBrandChange;
			/* ]]> */
			</script>
			<?php

		// List
		} else {

			include_once( '../walkers/class-product-brand-list-walker.php' );

			$brand_args['walker'] 			= new WC_Product_Brand_List_Walker;
			$brand_args['title_li'] 			= '';
			$brand_args['pad_counts'] 		= 1;
			$brand_args['show_option_none'] 	= __('No product brands exist.', 'woocommerce' );
			$brand_args['current_brand']	= ( $this->current_brand ) ? $this->current_brand->term_id : '';
			$brand_args['current_brand_ancestors']	= $this->brand_ancestors;

			echo '<ul class="product-brands">';

			wp_list_brands( apply_filters( 'woocommerce_product_brands_widget_args', $brand_args ) );

			echo '</ul>';
		}

		echo $after_widget;
	}
}

register_widget( 'WC_Widget_Product_Brands' );