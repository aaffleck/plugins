<?php
/**
 * Plugin Name: Custom Related Products By Tag
 * Description: Customize the "Related Products" list or your single product page for WooCommerce.
 * Version: 1.0
 * Author: Aaron Affleck
 * Author URI: http://inteleck.com
 */

if ( !function_exists('get_related') ) :
	/**
	* Get and return related products.
	*
	* @access public
	* @param int $id
	* @param int $limit (default: 5)
	* @return array Array of post IDs
	*/
	function get_related( $product, $limit = 5 ) {
		global $wpdb;
		
		$related_ids = false;

		$post_ids = array();
		// get tag ids belonging to $post_id
		$tag_ids = wp_get_object_terms( $product->id, 'product_tag', array( 'fields' => 'ids' ) );
	
		if ( $tag_ids ) {
			// get all posts that have the same tags
			$tag_posts = get_posts(
				array(
					'post_type' => 'product',
					'posts_per_page' => -1, // return all posts 
					'no_found_rows'  => true, // no need for pagination
					'fields'         => 'ids', // only return ids
					'post__not_in'   => array( $product->id ), // exclude $post_id from results
					'tax_query'      => array(
						array(
							'taxonomy' => 'product_tag',
							'field'    => 'id',
							'terms'    => $tag_ids,
							'operator' => 'IN'
						)
					)
				)
			);

			// loop through posts with the same tags
			if ( $tag_posts ) {
				$score = array();
				$i = 0;
				foreach ( $tag_posts as $tag_post ) {
					// get tags for related post
					$terms = wp_get_object_terms( $tag_post, 'product_tag', array( 'fields' => 'ids' ) );
					$total_score = 0;

					foreach ( $terms as $term ) {
						if ( in_array( $term, $tag_ids ) ) {
							++$total_score;
						}
					}

					if ( $total_score > 0 ) {
						$score[$i]['ID'] = $tag_post;
						// add number $i for sorting 
						$score[$i]['score'] = array( $total_score, $i );
					}
					++$i;
				}

				// sort the related posts from high score to low score
				uasort( $score, 'sort_tag_score' );
				// get sorted related post ids
				$related_ids = wp_list_pluck( $score, 'ID' );
				// limit ids
				//$related_ids = array_slice( $related_ids, 0, (int) $number );
			}
		}
		return $related_ids;
	}
	
	function sort_tag_score( $item1, $item2 ) {
		if ( $item1['score'][0] != $item2['score'][0] ) {
			return $item1['score'][0] < $item2['score'][0] ? 1 : -1;
		} else {
			return $item1['score'][1] < $item2['score'][1] ? -1 : 1; // ASC
		}
	}
endif;
?>