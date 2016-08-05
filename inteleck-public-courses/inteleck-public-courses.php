<?php
/*
Plugin Name: Public Courses
Plugin URI: http://www.inteleck.com/
Description: This plugin adds custom meta boxes and widgets for the Public Courses Post Type.
Version: 1.0
Author: Inteleck - Aaron Affleck
Author URI: http://www.inteleck.com/
License: GPL2
*/

/*  
    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
if ( !class_exists( 'Inteleck_Public_Courses' ) ) {
	class Inteleck_Public_Courses {
		
		const VERSION = '1.0';
		
		public $plugin_dir;
		public $plugin_url;
		public $plugin_domain = 'inteleck-public-courses';
		public $dates_meta_keys = array('_IPCDate','_IPCLocation','_IPCAudience','_IPCLanguage');
		public $english_meta_keys = array('_IPCDuration','_IPCCost','_IPCBrief');
		public $arabic_meta_keys = array('_IPCName_a','_IPCDuration_a','_IPCCost_a','_IPCContent_a','_IPCBrief_a');
		
		
		function __construct(){
			$this->pluginDir = basename(dirname(__FILE__));
			$this->pluginUrl = WP_CONTENT_URL.'/plugins/'.plugin_basename(dirname(__FILE__));
			
			//Hooks
			register_activation_hook(__FILE__, array($ipc, 'activate_ipc'));
			register_deactivation_hook(__FILE__, array($ipc, 'deactivate_ipc'));
			
			//Actions
			add_action('admin_menu', array( $this, 'add_post_meta_box' ));
			add_action('save_post', array($this, 'add_post_meta'), 15);
			add_action('publish_post', array($this, 'add_post_meta'), 15);
			add_action('init', array($this, 'set_admin_resources'));
			add_action('loop_start', array($this, 'inteleck_public_course'));
			//add_action('init', array($this, 'set_user_resources'));
			
			//Filters
			add_filter('the_content', array($this, 'inteleck_public_courses'));
		}
		
		/**
		 *
		 *
		 */
		function inteleck_public_courses($content){
			global $wpdb, $post;
			
			//Get Defaults
			$post_id = get_the_ID();
			
			if(!is_page(639) || strpos($content, "Contact form"))
				return $content;
			else
				$content .= get_the_content($post_id);
			ob_start();
			$query = "select * from $wpdb->posts";
			
			$query2 = '';
			$query3 = '';
			$query4 = '';
			$results = array();
			$results2 = array();
			
			if(isset($_GET)&& (!empty($_GET['date-sort']) || !empty($_GET['course-sort']) || !empty($_GET['location-sort']))){
				if(isset($_GET['date-sort']) && $_GET['date-sort']!=''){
				 	$d = $_GET['date-sort'];
				 	$m = substr($d, 0, strpos($d, ','));
				 	$y = substr($d, strpos($d, ',')+2);
				 	$query2 = " inner join $wpdb->postmeta on ($wpdb->posts.ID = $wpdb->postmeta.post_id) where $wpdb->postmeta.meta_key like '_IPCDate%'  and $wpdb->postmeta.meta_key != '_IPCDateCount' and $wpdb->postmeta.meta_value REGEXP '^$y-$m-[0-9]*$'";
				 	$results = $wpdb->get_results($query.$query2, ARRAY_A);
				 	//echo "Errors=".mysql_error();
				}
				if(isset($_GET['location-sort']) && $_GET['location-sort']!=''){
					$location = $_GET['location-sort'];
					if(!empty($query2)) {
						$query3 = " and $wpdb->postmeta.meta_key like '_IPCLocation%' and $wpdb->postmeta.meta_value = '$location'";
						$results2 = $wpdb->get_results($query.$query2.$query3, ARRAY_A);
						$results = array_merge($results, $results2);
						$results = array_unique($results);
					}
					else {
						$query3 = " inner join $wpdb->postmeta on ($wpdb->posts.ID = $wpdb->postmeta.post_id) where $wpdb->postmeta.meta_key like '_IPCLocation%' and $wpdb->postmeta.meta_value = '$location'";
						$results = $wpdb->get_results($query.$query3, ARRAY_A);
					}
				}
				if(isset($_GET['course-sort']) && $_GET['course-sort']!=''){
					$course = $_GET['course-sort'];
					if(!empty($query2) && !empty($query3)){
						$query4 = " and $wpdb->posts.post_title = '$course'";
						$results = $wpdb->get_results($query.$query2.$query3.$query4, ARRAY_A);
					}
					else if(empty($query2) && !empty($query3)){
						$query4 = " and $wpdb->posts.post_title = '$course'";
						$results = $wpdb->get_results($query.$query3.$query4, ARRAY_A);
					}
					else if(!empty($query2) && empty($query3)){
						$query4 = " and $wpdb->posts.post_title = '$course'";
						$results = $wpdb->get_results($query.$query2.$query4, ARRAY_A);
					}
					else {
						$query4 = " where $wpdb->posts.post_title = '$course'";
						$results = $wpdb->get_results($query.$query4, ARRAY_A);
					}
					if(!empty($query2)) {
						
					}
					else {
						$query3 = " where $wpdb->posts.post_title = '$course'";
						$results = $wpdb->get_results($query.$query3, ARRAY_A);
					}
					//echo "Errors1=".mysql_error();
				}
			}
			else {
				$query .= " where $wpdb->posts.post_type = 'public-courses' and $wpdb->posts.post_status = 'publish'";
				$results = $wpdb->get_results($query, ARRAY_A);
			}

			//Get Posts
			$posts = $results;
			$courses = array();
			$locations = array();
			$locales = array();
			$languages = array();
			$dates = array();
			$titles = array();
			$titles_a = array();
			$partners = array();
			$permalinks = array();
			$durations = array();
			$out = '';

			if(is_array($posts)&&!empty($posts)){
				
				$out .= '<div id="filter-public-courses">Sort By: ';
				$out .= '<form name="sort-table-form" id="sort-table-form" method="get" action="">';
				$out .= '<select name="date-sort" id="date-sort"><option value="">All Dates</option>';
				$currentMonth = (int)date('m');
				for($x = $currentMonth; $x < $currentMonth+13; $x++) {
					$out .= '<option value="'.date('m, Y', mktime(0, 0, 0, $x, 1)).'">'.date('F, Y', mktime(0, 0, 0, $x, 1)).'</option>';
				}
				$out .= '</select>';
				$out .= '<select name="course-sort" id="course-sort"><option value="">All Courses</option>';
				$course_query = new WP_Query('post_type=public-courses&posts_per_page=-1');
				while ($course_query->have_posts()) : $course_query->the_post();
					$out .= '<option value="'.get_the_title().'">'.get_the_title().'</option>';
				endwhile;
				$out .= '</select>';
				
				foreach($posts as $post){
					setup_postdata($post);
					
					$customs = get_post_custom($post['ID']);
					//check if there is more than one date for this course
					$z=0;
					foreach ( $customs as $key => $value ) {
						if ( preg_match( '/_IPCDate.*/', $key ) && $key != '_IPCDateCount') {
							array_push($dates, $value[0]);
							$title = get_the_title($post['ID']);
							$dnum = substr($key,strpos($key,"Date")+4);
							array_push($titles, $title);
							array_push($titles_a, get_post_meta($post['ID'], '_IPCName_a', true));
							array_push($partners, get_the_post_thumbnail($post['ID'], array(70,100)));
							array_push($permalinks, get_permalink($post['ID']));
							array_push($durations, get_post_meta($post['ID'], '_IPCDuration', true));
							array_push($locations, get_post_meta($post['ID'], '_IPCLocation'.$dnum, true));
							array_push($languages, get_post_meta($post['ID'], '_IPCLanguage'.$dnum, true));
						}
					}
				}
				
				$c=0;
				foreach($dates as $date){
					if($date >= date('Y-m-d')){
						if((isset($d) && preg_match("/^$y-$m-\d{2}$/", $date)) && !isset($course) && !isset($location) ){
							$e = array('title'=>$titles[$c], 'date'=>$date, 'duration'=>$durations[$c], 'location'=>$locations[$c], 'language'=>$languages[$c], 'title_a'=>$titles_a[$c], 'partner'=>$partners[$c], 'permalink'=>$permalinks[$c]);
							$courses[$date] = $e;
						}
						else if((isset($d) && preg_match("/^$y-$m-\d{2}$/", $date)) && (isset($course) && $course == $titles[$c]) && !isset($location)){
							$e = array('title'=>$titles[$c], 'date'=>$date, 'duration'=>$durations[$c], 'location'=>$locations[$c], 'language'=>$languages[$c], 'title_a'=>$titles_a[$c], 'partner'=>$partners[$c], 'permalink'=>$permalinks[$c]);
							$courses[$date] = $e;
						}
						else if((isset($d) && preg_match("/^$y-$m-\d{2}$/", $date)) && (isset($course) && $course == $titles[$c]) && (isset($location) && $location==$locations[$c])){
							$e = array('title'=>$titles[$c], 'date'=>$date, 'duration'=>$durations[$c], 'location'=>$locations[$c], 'language'=>$languages[$c], 'title_a'=>$titles_a[$c], 'partner'=>$partners[$c], 'permalink'=>$permalinks[$c]);
							$courses[$date] = $e;
						}
						else if((isset($d) && preg_match("/^$y-$m-\d{2}$/", $date)) && !isset($course) && (isset($location) && $location==$locations[$c])){
							$e = array('title'=>$titles[$c], 'date'=>$date, 'duration'=>$durations[$c], 'location'=>$locations[$c], 'language'=>$languages[$c], 'title_a'=>$titles_a[$c], 'partner'=>$partners[$c], 'permalink'=>$permalinks[$c]);
							$courses[$date] = $e;
						}
						else if(!isset($d) && (isset($course) && $course == $titles[$c]) && !isset($location)){
							$e = array('title'=>$titles[$c], 'date'=>$date, 'duration'=>$durations[$c], 'location'=>$locations[$c], 'language'=>$languages[$c], 'title_a'=>$titles_a[$c], 'partner'=>$partners[$c], 'permalink'=>$permalinks[$c]);
							$courses[$date] = $e;
						}
						else if(!isset($d) && !isset($course) && (isset($location) && $location==$locations[$c])){
							$e = array('title'=>$titles[$c], 'date'=>$date, 'duration'=>$durations[$c], 'location'=>$locations[$c], 'language'=>$languages[$c], 'title_a'=>$titles_a[$c], 'partner'=>$partners[$c], 'permalink'=>$permalinks[$c]);
							$courses[$date] = $e;
						}
						else if(!isset($d) && (isset($course) && $course == $titles[$c]) && (isset($location) && $location==$locations[$c])){
							$e = array('title'=>$titles[$c], 'date'=>$date, 'duration'=>$durations[$c], 'location'=>$locations[$c], 'language'=>$languages[$c], 'title_a'=>$titles_a[$c], 'partner'=>$partners[$c], 'permalink'=>$permalinks[$c]);
							$courses[$date] = $e;
						}
						else if(!isset($d) && !isset($course) && !isset($location)){
							$e = array('title'=>$titles[$c], 'date'=>$date, 'duration'=>$durations[$c], 'location'=>$locations[$c], 'language'=>$languages[$c], 'title_a'=>$titles_a[$c], 'partner'=>$partners[$c], 'permalink'=>$permalinks[$c]);
							$courses[$date."-".$c] = $e;
						}
					}
					$c++;
				}
				
				$out .= '<select name="location-sort" id="location-sort"><option value="">All Locations</option>';
				while ($course_query->have_posts()) : $course_query->the_post();
					$cs = get_post_custom($post->ID);
					foreach ( $cs as $key => $value ) {
						if ( preg_match( '/_IPCLocation.*/', $key ) ) {
							array_push($locales, $value[0]);
						}
					}
				endwhile;
				sort($locales);
				$location_options = array_unique($locales);
				foreach($location_options as $location_option){
					$out .= '<option value="'.$location_option.'">'.$location_option.'</option>';
				}
				$out .= '</select>';
				$out .= '<input type="submit" id="sort-button" class="fancy_button" value="Filter" />';
				$out .= '</form>';
				$out .= '</div>';
				
				ksort($courses);
				$j=1;
				if(is_array($courses) && !empty($courses)){
					$out .= '<table cellspacing="0" id="public-courses-table">'."\r";
					$out .= '<tr><th>Date</th><th>Duration</th><th>Course</th><th>Location</th><th>Language</th><th>Partner</th><th>&nbsp;</th></tr>'."\r";
					foreach($courses as $course){
						$thedateparts = explode("-", $course['date']);
						$day = $thedateparts[2];
						$month = $thedateparts[1];
						$year = $thedateparts[0];
						$out .= '<form name="course-listing-'.$j.'" id="course-listing-form-'.$j.'" class="listingForm" method="post" action="'.get_permalink(704).'"><tr>
							<td>'.date('F d, Y', mktime(0,0,0,$month,$day,$year)).'</td>
							<td>'.$course['duration'].'</td>
							<td width="40%;"><a href="'.$course['permalink'].'">'.$course['title'].'<br />'.$course['title_a'].'</a></td>
							<td>'.$course['location'].'</td>
							<td>'.$course['language'].'</td>
							<td>'.$course['partner'].'</td>
							<td>
								<input type="hidden" name="thedate" value="'.$course['date'].'" />
								<input type="hidden" name="thetitle" value="'.$course['title'].'" />
								<input type="hidden" name="thelocation" value="'.$course['location'].'" />
								<input type="hidden" name="thelanguage" id="setlanguage-'.$j.'" value="'.$course['language'].'" />
								<input type="submit" name="register" class="register-button" id="register-button-'.$j.'" value="Register &raquo;" />
							</td>
						</tr></form>'."\r";
						$j++;
					}
					$out .= '</table>';
				}
				else {
					$out .= '<h2 class="sorry">Sorry. No courses were found for that search.</h2>';
				}
			}
			else {
				$out .= '<div id="filter-public-courses">Sort By: ';
				$out .= '<form name="sort-table-form" id="sort-table-form" method="get" action="">';
				$out .= '<select name="date-sort" id="date-sort"><option value="">All Dates</option>';
				$currentMonth = (int)date('m');
				for($x = $currentMonth; $x < $currentMonth+13; $x++) {
					$out .= '<option value="'.date('m, Y', mktime(0, 0, 0, $x, 1)).'">'.date('F, Y', mktime(0, 0, 0, $x, 1)).'</option>';
				}
				$out .= '</select>';
				$out .= '<select name="course-sort" id="course-sort"><option value="">All Courses</option>';
				$course_query = new WP_Query('post_type=public-courses&posts_per_page=-1');
				while ($course_query->have_posts()) : $course_query->the_post();
					$out .= '<option value="'.get_the_title().'">'.get_the_title().'</option>';
				endwhile;
				$out .= '</select>';
				$out .= '<select name="location-sort" id="location-sort"><option value="">All Locations</option>';
				while ($course_query->have_posts()) : $course_query->the_post();
					$cs = get_post_custom($post->ID);
					foreach ( $cs as $key => $value ) {
						if ( preg_match( '/_IPCLocation.*/', $key ) ) {
							array_push($locales, $value[0]);
						}
					}
				endwhile;
				sort($locales);
				$location_options = array_unique($locales);
				foreach($location_options as $location_option){
					$out .= '<option value="'.$location_option.'">'.$location_option.'</option>';
				}				
				$out .= '</select>';
				$out .= '<input type="submit" id="sort-button" class="fancy_button" value="Filter" />';
				$out .= '</form>';
				$out .= '</div>';
				$out .= '<h2 class="sorry">Sorry. No courses were found</h2>';
			}
			
			//Get Post Options
			
			
			
			
			return $content.$out;
			ob_end_clean();
		}
		
		
		/**
		 *
		 *
		 */
		function inteleck_public_course(){
			global $wpdb;
			
			if (!is_single())
				return;
						
			//Get Defaults
			$post_id = get_the_ID();
			$content = get_the_content($post_id);
			
			//Get Post Options
			$date = get_post_meta($post_id, '_IPCDate', true);
			$location = get_post_meta($post_id, '_IPCLocation', true);
			$language = get_post_meta($post->ID, '_IPCLanguage', true);
			$brief = get_post_meta($post_id, '_IPCBrief', true);
			$audience = get_post_meta($post_id, '_IPCAudience', true);
			$duration = get_post_meta($post_id, '_IPCDuration', true);
			$cost = get_post_meta($post_id, 'IPCCost', true);
			
			//Get Post Options
			$title_a = get_post_meta($post_id, '_IPCName_a', true);
			$content_a = get_post_meta($post_id, '_IPCContent_a', true);
			$brief = get_post_meta($post_id, '_IPCBrief_a', true);
				
			$out = '';
			
		}
		
		/**
		 * Adds options to the edit/write post screen
		 *
		 * @return void
		 */
		function setup_meta_translation() {
			global $post, $wpdb;
			$post_id = $post->ID;
			
			foreach($this->arabic_meta_keys as $key ) {
				if ($post_id){
					$$key = get_post_meta($post_id, $key, true);
				}
				else{
					$$key = '';
				}
			}
			
			$the_post = get_post($post_id);
			
			include(dirname(__FILE__).'/views/ipc-meta-box-translation.php');
		}
		
		/**
		 * Adds options to the edit/write post screen
		 *
		 * @return void
		 */
		function setup_meta_dates() {
			global $post, $wpdb;
			$post_id = $post->ID;
			$_IPCDateCount = get_post_meta($post_id, "_IPCDateCount", true);
			if(empty($_IPCDateCount))
				$_IPCDateCount = 1;
						
			$the_post = get_post($post_id);
			
			include(dirname(__FILE__).'/views/ipc-meta-box-meta-data.php');
		}
		
		/**
		 * Adds options to the edit/write post screen
		 *
		 * @return void
		 */
		function setup_meta_english() {
			global $post, $wpdb;
			$post_id = $post->ID;
			
			foreach($this->english_meta_keys as $key ) {
				if ($post_id){
					$$key = get_post_meta($post_id, $key, true);
				}
				else{
					$$key = '';
				}
			}
			
			include(dirname(__FILE__).'/views/ipc-meta-box-english.php');
		}
		
		/**
		 * Callback for adding the Meta box to edit/write post screen
		 * @return void
		 */
		function add_post_meta_box( ) {
			add_meta_box('Course-Information', __( 'Course Information', $this->plugin_domain), array($this, 'setup_meta_english'), 'public-courses', 'normal', 'high');
			add_meta_box('Arabic-Translations', __( 'Arabic Translations', $this->plugin_domain), array($this, 'setup_meta_translation'), 'public-courses', 'normal', 'high');
			add_meta_box('Course-Dates', __( 'Course Dates', $this->plugin_domain), array($this, 'setup_meta_dates'), 'public-courses', 'normal', 'high');
		}
		
		/**
		 * Adds/removes the meta data.
		 *
		 * @param string $post_id 
		 * @return void
		 */
		function add_post_meta($post_id) {
			global $wpdb;
			if(isset($_POST['original_publish'])||isset($_POST['save'])){
				foreach($this->english_meta_keys as $key){
					update_post_meta($post_id, $key, $_POST[$key]);
				}
				foreach($this->arabic_meta_keys as $key2){
					update_post_meta($post_id, $key2, $_POST[$key2]);
				}
				
				foreach($_POST as $key => $value){
					if(preg_match('/_IPCDate.*/', $key) && $key != '_IPCDateCount'){
						$n = substr($key, strpos($key, "Date")+4);
					
						foreach($this->dates_meta_keys as $key3){
							$k = $key3.$n;
							update_post_meta($post_id, $k, $_POST[$k]);
						}
					}
				}

				update_post_meta($post_id, '_IPCDateCount', $_POST['_IPCDateCount']);
			}
		}
				        
        function set_admin_resources(){
        	wp_enqueue_style('ipcDateStyles', $this->pluginUrl .'/css/dateinput.css', array(), Inteleck_Public_Courses::VERSION, 'screen');
        	wp_enqueue_script('ipcTools', $this->pluginUrl .'/js/jquery.tools.min.js', array('jquery'));
        	wp_enqueue_script('ipcAdminUtils', $this->pluginUrl .'/js/ipc-admin-utils.js', array('jquery'));
        }
        
        /**
		 * Helper funciton to format the time string
		 * @return $time
		 */
        function format_time($secs) {
		   $times = array(3600, 60, 1);
		   $time = '';
		   $tmp = '';
		   for($i = 1; $i < 3; $i++) {
			  $tmp = floor($secs / $times[$i]);
			  if($tmp < 1) {
				 $tmp = '00';
			  }
			  elseif($tmp < 10) {
				 $tmp = '0' . $tmp;
			  }
			  $time .= $tmp;
			  if($i < 2) {
				 $time .= ':';
			  }
			  $secs = $secs % $times[$i];
		   }
		   return $time;
		}
		
		/**
		 * Sets internationalization on activation. Mo PO files not created yet 03/23/2011.
		 * @return void
		 */
		function activate_ipc(){
			if(!load_plugin_textdomain($this->plugin_domain,'/wp-content/languages/'))
				load_plugin_textdomain($this->plugin_domain,basename(dirname(__FILE__)) . '/languages/');
		}//End Activation
		
		/**
		 * Nothing to deactivate
		 * @return void
		 */
		function deactivate_ipc(){
		
		}//End Deactivation
		
	}//End Class
	
	$ipc = new Inteleck_Public_Courses();
	
} //End If Class exists
?>
