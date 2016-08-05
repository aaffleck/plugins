<?php
/**
 * WC_Product_Brand_Dropdown_Walker class.
 *
 * @extends 	Walker
 * @class 		WC_Product_Cat_Dropdown_Walker
 * @version		1.6.4
 * @package		WooCommerce/Classes/Walkers
 * @author 		WooThemes
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class WC_Product_Brand_Dropdown_Walker extends Walker {

	var $tree_type = 'brand';
	var $db_fields = array ('parent' => 'parent', 'id' => 'term_id', 'slug' => 'slug' );

	/**
	 * @see Walker::start_el()
	 * @since 2.1.0
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param object $brand brand data object.
	 * @param int $depth Depth of brand in reference to parents.
	 * @param integer $current_object_id
	 */
	function start_el( &$output, $brand, $depth = 0, $args = array(), $current_object_id = 0 ) {

		if ( ! empty( $args['hierarchical'] ) )
			$pad = str_repeat('&nbsp;', $depth * 3);
		else
			$pad = '';

		$brand_name = apply_filters( 'list_product_cats', $brand->name, $brand );

		$value = isset( $args['value'] ) && $args['value'] == 'id' ? $brand->term_id : $brand->slug;

		$output .= "\t<option class=\"level-$depth\" value=\"" . $value . "\"";

		if ( $value == $args['selected'] || ( is_array( $args['selected'] ) && in_array( $value, $args['selected'] ) ) )
			$output .= ' selected="selected"';

		$output .= '>';

		$output .= $pad . __( $brand_name, 'woocommerce' );

		if ( ! empty( $args['show_count'] ) )
			$output .= '&nbsp;(' . $brand->count . ')';

		$output .= "</option>\n";
	}
}