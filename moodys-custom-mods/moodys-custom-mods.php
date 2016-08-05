<?php
/*
Plugin Name: Moodys Custom Modifications
Plugin URI: http://www.inteleck.com/
Description: This plugin adds custom meta boxes and widgets for the Tax Tables and Profiles.
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
if ( !class_exists( 'Moodys_Custom_Mods' ) ) {
	class Moodys_Custom_Mods {
		
		const VERSION = '1.0';
		
		public $plugin_dir;
		public $plugin_url;
		public $year;
		public $plugin_domain = 'moodys-custom-mods';
		public $province_meta_keys = array('_alberta', '_british_columbia', '_saskatchewan', '_manitoba', '_ontario', '_quebec', '_new_brunswick', '_nova_scotia', '_prince_edward_island', '_new_foundland', '_northwest_territories', '_nunavut', '_yukon');
		public $taxTablePages = array(531,535,537,539,4539);//4512,4514,4516
		public $contact_meta_keys = array('phone', 'linkedIn', 'twitter', 'Title');
		
		function __construct(){
			$this->pluginDir = basename(dirname(__FILE__));
			$this->pluginUrl = WP_CONTENT_URL.'/plugins/'.plugin_basename(dirname(__FILE__));
			$this->year = 2014;
			
			//Hooks
			register_activation_hook(__FILE__, array($this, 'activate_mtt'));
			register_deactivation_hook(__FILE__, array($this, 'deactivate_mtt'));
			
			//Actions
			add_action('wp_head', array($this, 'set_frontend_resources'));
			add_action('admin_head', array( $this, 'add_post_meta_box' ));
			add_action('save_post', array($this, 'add_post_meta'), 15);
			add_action('publish_post', array($this, 'add_post_meta'), 15);
			add_action('admin_init', array($this, 'set_admin_resources'));
			add_action("login_head", array($this,"change_my_wp_login_image"));
			
			//Filters
			add_filter('the_content', array($this, 'moodys_tax_tables'));
			add_filter('user_contactmethods', array($this, 'hide_old_and_update_fields'),10,1);
			
			add_shortcode('create_tax_table', array($this, 'moodys_tax_tables'));
		}
		
		
		function change_my_wp_login_image() {
			echo "
			<style>
			body.login #login h1 a {
			background: url('/wp-content/themes/whiteboard/images/moodys-header-iphone.png') center center no-repeat transparent;
			background-size:contain;
			width:320px;
			height:140px;
			margin-left:0;}
			</style>
			";
		}


		
		/**
		 *
		 *
		 */
		function moodys_tax_tables($content){
			global $post;
			$post_id = $post->ID;
			
			if(in_array($post_id, $this->taxTablePages)){				
				ob_start();
				include(dirname(__FILE__).'/views/mtt-front-end-provinces.php');
				if($post_id == 539)
					include(dirname(__FILE__).'/views/mtt-front-end-provinces-ned.php');
				$out = ob_get_contents();				
				ob_end_clean();
			}
			return $content.$out;
		}
		
		
		
		/**
		 * Adds options to the edit/write post screen
		 *
		 * @return void
		 */
		function setup_meta_provinces($post, $bool) {
			$post_id = $post->ID;
			$thename = "_".$post->post_name."_highlights";
			$_highlights = get_post_meta($post_id, $thename, true);
			if(in_array($post_id, $this->taxTablePages)){
				include(dirname(__FILE__).'/views/mtt-meta-box-provinces.php');
			}
		}
		
		/**
		 * Callback for adding the Meta box to edit/write post screen
		 * @return void
		 */
		function add_post_meta_box() {
			global $post;
			
			if(!in_array($post->ID, $this->taxTablePages))
				return;
				
			$name = 'Tax Tables';
			$slug = 'Tax-Tables';
			if($post->ID==539){
				$name = 'Tax Tables - Eligible Dividends';
				$slug = 'Tax-Tables-Eligible-Dividends';
			}
			add_meta_box($slug, __( $name, $this->plugin_domain), array($this, 'setup_meta_provinces'), 'page', 'normal', 'high', array('bool'=>0));
			if($post->ID==539){
				$name2 = 'Tax Tables - Non-Eligible Dividends';
				$slug2 = 'Tax-Tables-Non-Eligible-Dividends';
				add_meta_box($slug2, __( $name2, $this->plugin_domain), array($this, 'setup_meta_provinces'), 'page', 'normal', 'high', array('bool'=>1));
			}
		}
		
		/**
		 * Adds/removes the meta data.
		 *
		 * @param string $post_id 
		 * @return void
		 */
		function add_post_meta($post_id) {
			global $wpdb,$post;
			$year = get_post_meta($post_id,'_tax-table-year',true);
			$tax_bracket_year = get_post_meta($post->ID,'_tax-bracket-year',true);
			if(empty($year))
				$year = $this->year;
			if(empty($tax_bracket_year))
				$tax_bracket_year = $this->year;
			if(isset($_POST['original_publish'])||isset($_POST['save'])){
				update_post_meta($post_id, "_".$post->post_name."_highlights", $_POST["_".$post->post_name."_highlights"]);
				update_post_meta($post_id, "_tax-table-year", $_POST["_tax-table-year"]);
				update_post_meta($post_id, "_tax-bracket-year", $_POST["_tax-bracket-year"]);
				$ed = '';
				if($post_id==539)
					$ed = 'ed_';
				foreach($this->province_meta_keys as $key){
					for($x=$year-4;$x<=$year;$x++){
						$n = $key.'_'.$ed.$x;
						update_post_meta($post_id, $n, $_POST[$n]);
					}
					if($post_id==535){
						$sbln = $key.'_sbl';
						update_post_meta($post_id, $sbln, $_POST[$sbln]);
					}
					if($post_id==537 || $post_id==539 || $post_id==4539){
						$htbn = $key.'_htb';
						update_post_meta($post_id, $htbn, $_POST[$htbn]);
					}
				}				
				if($post_id==535){
					for($x=$year-4;$x<=$year;$x++){
							$fsbln = '_federal_only_'.$x;
							update_post_meta($post_id, $fsbln, $_POST[$fsbln]);
					}
					$fsbl = '_federal_small_business_limit';
					update_post_meta($post_id, $fsbl, $_POST[$fsbl]);
				}
			
				if($post_id==539){
					$ed = 'ned_';
					foreach($this->province_meta_keys as $key){
						for($x=$year-4;$x<=$year;$x++){
							$n = $key.'_'.$ed.$x;
							update_post_meta($post_id, $n, $_POST[$n]);
						}
						$htbn = $key.'_htb';
						update_post_meta($post_id, $htbn, $_POST[$htbn]);
					}
				}
			}
		}
		
		function set_frontend_resources(){
			wp_enqueue_style('mcmFrontEndStyles', $this->pluginUrl .'/css/tax-table.css', array(), Moodys_Custom_Mods::VERSION, 'screen');
			wp_enqueue_script('mcm-cookie-js', $this->pluginUrl.'/js/plugins/jquery.cookie.min.js');
			//wp_enqueue_script('mcm-active-js', $this->pluginUrl.'/js/set_active.js');
		}
				        
        function set_admin_resources(){
        	wp_enqueue_style('mcmStyles', $this->pluginUrl .'/css/mcm-styles.css', array(), Moodys_Custom_Mods::VERSION, 'screen');
        	wp_enqueue_script('jquery-ui-sortable', array('jquery'));
			wp_enqueue_script('customScript', $this->pluginUrl .'/js/custom_script.js', array('jquery'));
        	//wp_enqueue_script('mcmTools', $this->pluginUrl .'/js/jquery.tools.min.js', array('jquery'));
        }
       
		//Hide the unused profile fields and add new ones
		function hide_old_and_update_fields( $contactmethods ) {
			unset($contactmethods['aim']);
			unset($contactmethods['yim']);
			unset($contactmethods['jabber']);
			foreach($this->contact_meta_keys as $key){
				$contactmethods[$key] = ucfirst($key);
			}
			return $contactmethods;
		}
        
		/**
		 * Sets internationalization on activation. Mo PO files not created yet 03/23/2011.
		 * @return void
		 */
		function activate_mcm(){
			if(!load_plugin_textdomain($this->plugin_domain,'/wp-content/languages/'))
				load_plugin_textdomain($this->plugin_domain,basename(dirname(__FILE__)) . '/languages/');
		}//End Activation
		
		/**
		 * Nothing to deactivate
		 * @return void
		 */
		function deactivate_mtt(){
		
		}//End Deactivation
		
	}//End Class
	
	$mcm = new Moodys_Custom_Mods();
	
} //End If Class exists
?>
