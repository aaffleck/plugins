<?php
/*
Plugin Name: Moodys Advanced Search
Plugin URI: http://moodystax.com
Description: Advanced AJAX search and filter utility.
Version: 1.0
Author: Inteleck - Aaron Affleck
Author URI: http://inteleck.com/
*/

/**
 *
 * **********************************************************************
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * **********************************************************************
 *
 */

/* CONSTANTS */
if(!defined('MAS_MIN_WP_VERSION')) {
	define('MAS_MIN_WP_VERSION', '3.1');
}

if(!defined('MAS_PLUGIN_NAME')) {
	define('MAS_PLUGIN_NAME', 'Moodys Avanced Search');
}

if(!defined('MAS_PLUGIN_SLUG')) {
	define('MAS_PLUGIN_SLUG', 'moodys-advanced-search');
}

if(!defined('MAS_DIR_PATH')) {
	define('MAS_DIR_PATH', plugin_dir_path(__FILE__));
}

if(!defined('MAS_DIR_URL')) {
	define('MAS_DIR_URL', plugin_dir_url(__FILE__));
}

// check WordPress version
global $wp_version;
if(version_compare($wp_version, MAS_MIN_WP_VERSION, "<")) {
	exit(MAS_PLUGIN_NAME.' requires WordPress '.MAS_MIN_WP_VERSION.' or newer.');
}

// deny direct access
if(!function_exists('add_action')) {
	header('Status: 403 Forbidden');
	header('HTTP/1.1 403 Forbidden');
	exit();
}

/**
 *  Moodys Advanced Search CONTAINER CLASS
 */
if(!class_exists("MoodysAdvancedSearch")) :
	class MoodysAdvancedSearch {

		public $options, $is_active;

		function __construct() {

			$this->options = get_option('mas_options');

			if(is_admin()) {
				require_once(MAS_DIR_PATH.'views/mas-options.php'); // include options file
				$options_page = new MoodysAdvancedSearchOptions();
				add_action('admin_menu', array($options_page, 'add_pages')); // adds page to menu
				add_action('admin_init', array($options_page, 'register_settings'));
			}

			add_action('init', array($this, 'init'));

			// REGISTER AJAX FUNCTIONS WITH ADMIN-AJAX
			add_action('wp_ajax_mas_search', array($this, 'get_results'));
			add_action('wp_ajax_nopriv_mas_search', array($this, 'get_results')); // need this to serve non logged in users
			add_action('wp_ajax_mas_getvalues', array($this, 'get_values'));
			add_action('wp_ajax_nopriv_mas_getvalues', array($this, 'get_values')); // need this to serve non logged in users

			// REGISTER SHORTCODES
			add_shortcode(MAS_PLUGIN_SLUG."-bar", array($this, 'search_form'));
			add_shortcode(MAS_PLUGIN_SLUG."-results", array($this, 'search_results'));

			add_action('widgets_init', array($this, 'mas_register_widgets')); // REGISTER WIDGET

			register_activation_hook(__FILE__, array($this, 'activation_hook')); // on plugin activation, create search results page
		}
		
		/**
		 * mas_register_widgets
		 *
		 */
		function mas_register_widgets() {
			require_once(MAS_DIR_PATH.'views/mas-widget.php'); // include widget file
			register_widget('MoodysAdvancedSearchWidget');
		}

		function init() {
			if($this->options['override_default']) {
				add_filter('get_search_form', array($this, 'search_form'));
			}
		}

		/**
		 *
		 * Create search results page
		 *
		 * When the plugin is first activated, create a /search/ page with the results shortcode.
		 *
		 */
		public function activation_hook() {
			$pages = get_pages();
			foreach($pages as $page) {
				if($page->post_name == "search-results") {
					return;
				}
			} // if search page already exists, exit
			$results_page = array(
				'post_title'     => 'Search Results',
				'post_content'   => '['.MAS_PLUGIN_SLUG.'-bar]<br />['.MAS_PLUGIN_SLUG.'-results]',
				'post_status'    => 'publish',
				'post_type'      => 'page',
				'post_name'      => 'search-results',
				'comment_status' => 'closed'
			);
			wp_insert_post($results_page);
		}

		/**
		 *  PRIVATE FUNCTIONS
		 */

		/**
		 *
		 * Highlight search terms
		 *
		 *
		 * Takes a block of text and an array of keywords, returns the text with
		 * keywords wrapped in a "highlight" class.
		 *
		 * @param $text
		 * @param $keywords
		 *
		 * @return mixed
		 */
		private function highlightsearchterms($text, $keywords) {
			return preg_replace('/('.implode('|', $keywords).')/i', '<strong class="mas-highlight">$0</strong>', $text);
		}

		/**
		 *
		 * Convert a string to an array of keywords
		 *
		 *
		 * Separate a comma-separated string of keywords into an array, preserving quotation marks
		 *
		 * @param $search
		 *
		 * @return mixed
		 */
		protected function string_to_keywords($search) {
			preg_match_all('/(?<!")\b\w+\b|(?<=")\b[^"]+/', $search, $keywords);
			for($i = 0; $i < count($keywords[0]); $i++) {
				$keywords[0][$i] = stripslashes($keywords[0][$i]);
			}
			return $keywords[0];
		}

		/**
		 *
		 * Modified version of wp_strip_all_tags
		 *
		 *
		 * Strips all HTML etc. tags from a given input, converts line breaks to spaces, and
		 * removes any trailing tags that got clipped by the excerpt process
		 *
		 * @param      $string
		 * @param bool $remove_breaks
		 *
		 * @return string
		 */
		private function mas_strip_tags($string, $remove_breaks = FALSE) {
			$string = preg_replace('/[\r\n\t ]+/', ' ', $string);

			$string = preg_replace('@<(script|style)[^>]*?>.*?</\\1>@si', '', $string);

			$string = preg_replace('@ *</?\s*(P|UL|OL|DL|BLOCKQUOTE)\b[^>]*?> *@si', "\n\n", $string);
			$string = preg_replace('@ *<(BR|DIV|LI|DT|DD|TR|TD|H\d)\b[^>]*?> *@si', "\n", $string);
			$string = preg_replace("@\n\n\n+@si", "\n\n", $string);

			$string = strip_tags($string);

			if($remove_breaks) {
				$string = preg_replace('/[\r\n\t ]+/', ' ', $string);
			}

			// ...since we're pulling excerpts from the DB, some of the excerpts contain truncated HTML tags
			// that won't be picked up by strip_tags(). This removes any trailing HTML from the beginning
			// and end of the excerpt:
			$string = preg_replace('/.*>|<.*/', ' ', $string);

			return trim($string);
		}

		/**
		 *
		 * Ajax response
		 *
		 *
		 * Similar to wp_localize_script, but wp_localize_script can only be called on plugin load / on
		 * page load. This function can be called during execution of the AJAX call & response process
		 * to update the main.js file with new variables.
		 *
		 * @param $parameter
		 * @param $response
		 */
		private function ajax_response($parameter, $response) {
			echo '
				<script type="text/javascript">
				    /* <![CDATA[ */
				    var mass_response = {
				            "'.$parameter.'":"'.$response.'"
				    };
				    /* ]]> */
				    </script>';
		}

		/**
		 *
		 * Print results
		 *
		 *
		 * If there are results, load the appropriate results template and output
		 * the search results. Send Analytics tracking beacon if enabled.
		 *
		 * @param $results
		 * @param $keywords
		 *
		 * @internal param $resultsarray
		 */
		protected function render_results($results, $keywords) {
			global $post;
			
			if(!empty($results)) {
			
				ob_start();

				if(file_exists(TEMPLATEPATH.'/mas-results-template.php')) {
					require(TEMPLATEPATH.'/mas-results-template.php');
				} else {
					require(MAS_DIR_PATH.'views/mas-results-template.php');
				}
				// if we're tracking searches as analytics events, pass the number of search results back to main.js
				if($this->options['track_events']) {
					$this->ajax_response('numresults', count($results));
				}

				echo ob_get_clean();
				/* @todo add an option to switch to non post_object based results output (just title and excerpt) 

				if(is_array($keywords)) {
					echo $this->highlightsearchterms($output, $keywords);
				} else {
				echo $output;
				}*/
			}
		}

		/**
		 *  PUBLIC FUNCTIONS
		 */

		/**
		 * register_scripts
		 *
		 */
		public function register_scripts() {
			$options = $this->options;
			
			// ENQUEUE VISUALSEARCH SCRIPTS
			//			wp_enqueue_script('underscore', MAS_DIR_URL.'js/underscore-min.js');
			//			wp_enqueue_script('backbone', MAS_DIR_URL.'js/backbone-min.js', array('underscore'));
			wp_enqueue_script('underscore');
			wp_enqueue_script('backbone');
			wp_enqueue_script(
				'visualsearch',
				MAS_DIR_URL.'js/visualsearch.js',
				array(
					 'jquery',
					 'jquery-ui-core',
					 'jquery-ui-datepicker',
					 'jquery-ui-widget',
					 'jquery-ui-position',
					 'jquery-ui-autocomplete',
					 'backbone',
					 'underscore'
				)
			);
			
			// ENQUEUE AND LOCALIZE MAIN JS FILE
			wp_enqueue_script('mas-script', MAS_DIR_URL.'js/main.js');

			


			// ENQUEUE STYLES
			wp_enqueue_style('mas-bar', MAS_DIR_URL.'css/visualsearch.css');
			wp_enqueue_style('mas-calendar', 'http://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css');
		}

		/**
		 * search_form
		 *
		 * @return string
		 */
		public function search_form() {
			$this->register_scripts();
			// RENDER SEARCH FORM
			return '<div id="search_box_container"><div id="search"><div class="VS-search">
			  <div class="VS-search-box-wrapper VS-search-box">
			    <div class="VS-icon VS-icon-search"></div>
			    <div class="VS-icon VS-icon-cancel VS-cancel-search-box" title="clear search"></div>
			  </div>
			</div></div></div>';
		}

		/**
		 * search_form_template_tag
		 *
		 */
		public function search_form_template_tag() {
			
			if(!is_home() && !is_single() && !is_page(2883) && !is_archive() && !is_category())
				return;
			
			$this->register_scripts();
			
			$q = str_replace("+", " ", $_GET['q']);
			$auth = $_GET['auth'];
			$cat = $_GET['cat'];
			$date_from = $_GET['date_from'];
			$date_to = $_GET['date_to'];
			
			
			$cats = get_categories();

			echo '
			<form name="moodys-advanced-search-form" id="moodys-advanced-search-form" action="/search-results/" method="get">
			<div id="moodys-advanced-search">	
				<a href="#" id="search-heading"><span class="cue-open"><span>+</span></span><span class="cue-close" style="display: none;"><span>-</span> Advanced</span> Search</a>
				<div id="search-container">
					<label for="q">Keywords<br /><input type="text" name="q" class="text" id="q" value="'.$q.'" placeholder="Keyword Search" /></label>
					<div id="advanced-fields" style="display:none;">
						<label for="auth">Author<br /><input type="text" name="auth" class="text" id="auth" value="'.$auth.'" placeholder="Author" /></label>
						<label for="cat">Category<br /><select name="cat" class="select" id="cat">
							<option value="">Choose Category</option>';
							foreach($cats as $category){
								$selected = '';
								if(isset($cat) && $cat == $category->term_id)
									$selected = " selected";
								echo '<option value="'.$category->term_id.'"'.$selected.'>'.$category->name.'</option>';
							}
						echo '</select></label>
						<label for="date_from" class="blocklabel">From Date<input type="text" class="date" name="date_from" id="date_from" value="'.$date_from.'" placeholder="From Date" /></label>
						<label for="date_to" class="blocklabel to-date">To Date<input type="text" class="date" name="date_to" id="date_to" value="'.$date_to.'" placeholder="To Date" /></label>
					</div>
				</div>
				<input type="submit" id="search-submit" value="" />
				<!--div id="search_box_container">
					<div id="search"><div class="VS-search">
						<div class="VS-search-box-wrapper VS-search-box">
							<div class="VS-icon VS-icon-search"></div>
							<div class="VS-icon VS-icon-cancel VS-cancel-search-box" title="clear search"></div>
						</div>
					</div>
				</div-->
			</div>
			</form>';
		}

		/**
		 * search_results
		 *
		 * @return string
		 */
		public function search_results() {
			$options = $this->options;
			$keywords = array();
			global $wp_query, $post, $paged, $query_string, $paged, $date_from, $date_to;

			//Get Values
			$q = $_GET['q'];
			$auth = $_GET['auth'];
			$cat = $_GET['cat'];
			$date_from = $_GET['date_from'];
			$date_to = $_GET['date_to'];
			
			//Check to see if something was passed
			if(!isset($q)) {
				exit;
			}
			// if nothing's been set, we can exit
			
			//Set up Args Array
			$args = array( // parameters for the term query
				'post_type' => 'post',
				'orderby' => 'date',
				'order'   => 'DESC',
				'posts_per_page' => 6,
				'paged' => $paged
			);
			
			if(!empty($q)){
				$args['s'] = $q;
				/*if(strpos($q, '+')){
					$keywords = explode('+', $q);
				}*/
			}

			//Specific Category
			if(isset($cat) && !empty($cat)) {
				$args['cat'] = $cat;
			}
			
			//Author Specific
			if(isset($auth) && !empty($auth)){
				global $wpdb;
				$auth = str_replace(" ", "%", $auth);
				$authors = $wpdb->get_results("Select * from ".$wpdb->users." where display_name like '%".$auth."%'");
				$auth_ids = array();
				if(is_array($authors) && !empty($authors)){
					foreach($authors as $author){
						$auth_ids[] = $author->ID;
					}
					$auth_ids = implode(',',$auth_ids);
					$args['author'] = $auth_ids;
				}
				else{
					$args['post_type'] = 'null';
				}
				
			}
			
			//Specific Date Range
			if(isset($date_from) && !empty($date_from)){
				add_filter( 'posts_where', array($this, 'filter_where') );
			}

			//$posts = get_posts($args);

			$posts = new WP_Query($args);
			//print_r($posts);
			//remove_filter( 'posts_where', array($this, 'filter_where') );

			// RENDER SEARCH RESULTS AREA
			$this->render_results($posts, $keywords);
		}
		
		function filter_where( $where = '' ) {
			global $date_from, $date_to;

			$where .= " AND wp_posts.post_date >= '$date_from' AND wp_posts.post_date <= '$date_to'";
			return $where;
		}

		/**
		 * search_results_template_tag
		 *
		 */
		public function search_results_template_tag() {
			echo '<div id="mas_response"></div>';
		}

		

		/**
		 *
		 * Get results
		 *
		 *
		 * This is called by main.js when the mas_search action is triggered. Gets
		 * the query from the UI, reconstructs it into an array, builds and executes the
		 * database query, and calls the function to output the results.
		 *
		 */
		public function get_results() {

			if(!isset($_GET['masquery'])) {
				die(); // if no data has been entered, quit
			} else {
				$searcharray = $_GET['masquery'];
			}

			$nonce = $_GET['searchNonce'];
			if(!wp_verify_nonce($nonce, 'search-nonce')) // make sure the search nonce matches the nonce generated earlier
			{
				die ('Busted!');
			}

			$this->execute_query_basic($searcharray);
		}

		/**
		 * @param $searcharray
		 */
		public function execute_query_basic($searcharray) {

			global $wpdb; // load the database wrapper

			foreach($searcharray as $index) { // iterate through the search query array and separate the taxonomies into their own array
				foreach($index as $facet => $data) {
					$facet = $wpdb->escape($facet);

					//		$data = $wpdb->escape($data); //	@todo find an escape method that doesn't break strings encased in quotes. not a huge deal since we're breaking all
					//	strings apart anyway (so sql injection is impossible)
					$type = $this->determine_facet_type($facet); // determine if we're dealing with a taxonomy or a metafield

					switch($type) {
						case "text" :
							$keywords = $this->string_to_keywords($data);
							break;
						case "taxonomy" :
							$data = preg_replace('/_/', " ", $data); // in case there are underscores in the value (from a permalink), remove them
							if(!isset($taxonomies[$facet])) {
								$taxonomies[$facet] = "'".$data."'"; // if it's the first parameter, don't prefix with a comma
							} else {
								$taxonomies[$facet] .= ", '".$data."'"; // prefix subsequent parameters with ", "
							}
							break;
						case "metafield" :
							// Do nothing
							break;
					}
				}
			}
			// @todo would be nice if we could somehow iterate through to find the first matching keyword instead of just checking $keywords[0]
			$querystring = "
			SELECT *,
			substring(post_content, ";
			if(isset($keywords)) { // if there are keywords, locate them and return a 200 character excerpt beginning 80 characters before the keyword
				$keywords = $wpdb->escape($keywords); // Sanitize the keywords parameters to prevent sql injection attacks
				$querystring .= "
					case 
						 when locate('$keywords[0]', lower(post_content)) <= 80 then 1
			             else locate('$keywords[0]', lower(post_content)) - 80
			        end,";
			} else { // if there aren't any keywords, just return the first 200 characters of the post
				$querystring .= "1,";
			}
			$querystring .= "200)
			AS excerpt
			FROM $wpdb->posts ";
			if(isset($taxonomies)) {
				for($i = 0; $i < count($taxonomies); $i++) { // for each taxonomy (categories, tags, etc.) do some joins so we can check each post against taxonomy[i] and term[i]
					$querystring .= "
					LEFT JOIN $wpdb->term_relationships AS rel".$i." ON($wpdb->posts.ID = rel".$i.".object_id)
					LEFT JOIN $wpdb->term_taxonomy AS tax".$i." ON(rel".$i.".term_taxonomy_id = tax".$i.".term_taxonomy_id)
					LEFT JOIN $wpdb->terms AS term".$i." ON(tax".$i.".term_id = term".$i.".term_id) ";
				}
			}
			$querystring .= "WHERE "; // the SELECT part of the query told us *what* to grab, the WHERE part tells us which posts to grab it from
			// if there are keywords, select posts where any of the keywords appear in either the title or post body
			if(isset($keywords)) {
				for($i = 0; $i < count($keywords); $i++) {
					$querystring .= "(lower(post_content) LIKE '%{$keywords[$i]}%' ";
					$querystring .= "OR lower(post_title) LIKE '%{$keywords[$i]}%') ";
					if($i < count($keywords) - 1) {
						$querystring .= "AND ";
					}
				}
			}
			if(isset($keywords) && isset($taxonomies)) {
				$querystring .= "AND ";
			} // if there were keywords, and there are taxonomies, insert an AND between the two sections
			$i = 0;
			if(isset($taxonomies)) {
				foreach($taxonomies as $taxonomy => $taxstring) { // for each taxonomy, check to see if there are any matches from within the comma-separated list of terms
					if($i > 0) {
						$querystring .= "AND ";
					}
					$querystring .= "(term".$i.".name IN (".$taxstring.") ";
					$querystring .= "AND tax".$i.".taxonomy = '".$taxonomy."') ";
					$i++;
				}
			}
			if((isset($keywords) || isset($taxonomies)) && isset($metafields)) {
				$querystring .= "AND ";
			}
			$querystring .= "
			AND $wpdb->posts.post_status = 'publish'"; // exclude drafts, scheduled posts, etc

			//echo $querystring; $wpdb->show_errors(); 		// for debugging, you can echo the completed query string and enable error reporting before it's executed

			if(!isset($keywords)) {
				$keywords = NULL;
			}

			$this->print_results($wpdb->get_results($querystring, OBJECT), $keywords); // format and output the search results

			die(); // wordpress may print out a spurious zero without this - can be particularly bad if using json
		}
	}
endif;

/**
 *  GLOBAL FUNCTIONS AND TEMPLATE TAGS
 */
if(class_exists("MoodysAdvancedSearch")) {

	$moodys_advanced_search = new MoodysAdvancedSearch();

	/**
	 * moodys_advanced_search_results
	 *
	 */
	function moodys_advanced_search_results() {
		global $moodys_advanced_search;
		$moodys_advanced_search->search_results_template_tag();
	}

	/**
	 * moodys_advanced_search_bar
	 *
	 */
	function moodys_advanced_search_bar() {
		global $moodys_advanced_search;
		$moodys_advanced_search->search_form_template_tag();
	}
}
