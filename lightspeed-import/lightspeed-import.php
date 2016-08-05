<?php
/**
 * Plugin Name: Lightspeed Import
 * Plugin URI:  http://inteleck.com/wordpress/plugins
 * Description: Lightspeed Retail Import for WordPress.
 * Version:     1.0
 * Author:      Aaron Affleck
 * Author URI:  http://inteleck.com
 * License:     GPLv2+
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 *  Lightspeed Import CONTAINER CLASS
 */
if(!class_exists("LightspeedImport")) :
	class LightspeedImport {
	
		/**
		 * @var LightspeedImport The single instance of the class
		 * @since 1.0
		 */
		protected static $_instance = null;
	
		/**
		 * @var $API_key
		 */
		public $API_key = null;
		
		/**
		 * @var $API_account
		 */
		public $API_account = null;
		
		/**
		 * @var $LI_cache
		 */
		public $LI_cache = null;
		
		/**
		 * @var $XML_dir
		 */
		 public $XML_dir_matrices = null;
		 
		 /**
		 * @var $XML_dir
		 */
		 public $XML_dir_vendors = null;
		 
		 /**
		 * @var $XML_dir
		 */
		 public $XML_dir_manufacturers = null;
		 
		 /**
		 * @var $XML_dir
		 */
		 public $XML_dir_items = null;

		/**
		* Main Lightspeed Import Instance
		*
		* Ensures only one instance of Lightspeed Import is loaded or can be loaded.
		*
		* @since 1.0
		* @static
		* @see LI()
		* @return Lightspeed Import - Main instance
		*/
		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
		}
			return self::$_instance;
		}
		
		/**
		 * Cloning is forbidden.
		 *
		 * @since 2.1
		*/
		public function __clone() {
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'lightspeedimport' ), '1.0' );
		}

		/**
		* Unserializing instances of this class is forbidden.
		*
		* @since 2.1
		*/
		public function __wakeup() {
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'lightspeedimport' ), '1.0' );
		}

		/**
		 * Constructor.
		 * @access public
		 *
		 */

		public function __construct() {

			// Auto-load classes on demand
			if ( function_exists( "__autoload" ) ) {
				spl_autoload_register( "__autoload" );
			}

			spl_autoload_register( array( $this, 'autoload' ) );

			// Define constants
			$this->define_constants();

			// Include required files
			$this->includes();
			
			$this->API_key = "";
			
			$this->API_account = "";

			// Init API
			$this->api = new LI_API($this->API_key,$this->API_account);
			
			// set xml directory
			$this->XML_dir_matrices = $this->plugin_path() . '/xml/matrices/';
			
			// set xml directory
			$this->XML_dir_items = $this->plugin_path() . '/xml/items/';
			
			// set xml directory
			$this->XML_dir_vendors = $this->plugin_path() . '/xml/vendors/';
			
			$this->XML_dir_manufacturers = $this->plugin_path() . '/xml/manufacturers/';
			
			
			
			register_activation_hook( __FILE__, array($this, 'lightspeed_import_activation') );
			
			register_deactivation_hook( __FILE__, array($this, 'lightspeed_import_deactivation') );
			
			add_action( 'lightspeed_hourly_product_import', array($this, 'lightspeed_import_items') );
			
			add_action( 'lightspeed_hourly_matrices_import', array($this, 'lightspeed_import_matrices') );
			
			add_action( 'lightspeed_hourly_vendors_import', array($this, 'lightspeed_import_vendors') );
			
			add_action( 'lightspeed_hourly_manufacturers_import', array($this, 'lightspeed_import_manufacturers') );
			
			
			
		}
		
		
		
		
		/**
		 * On activation, set a time, frequency and name of an action hook to be scheduled.
		 */
		function lightspeed_import_activation() {
			wp_schedule_event( time(), 'hourly', 'lightspeed_hourly_product_import' );
			
			wp_schedule_event( time(), 'hourly', 'lightspeed_hourly_matrices_import' );
			
			wp_schedule_event( time(), 'hourly', 'lightspeed_hourly_vendors_import' );
			
			wp_schedule_event( time(), 'hourly', 'lightspeed_hourly_manufacturers_import' );
		}
		
		
		/**
		* On deactivation, remove all functions from the scheduled action hook.
		*/
		function lightspeed_import_deactivation() {
			wp_clear_scheduled_hook( 'lightspeed_hourly_product_import' );
			
			wp_clear_scheduled_hook( 'lightspeed_hourly_matrices_import' );
			
			wp_clear_scheduled_hook( 'lightspeed_hourly_vendors_import' );
			
			wp_clear_scheduled_hook( 'lightspeed_hourly_manufacturers_import' );
		}
		
		
		
		/**
		 * On the scheduled action hook, run the function.
		 */
		function lightspeed_import_items() {
			// do import every hour
			
			//empty the directory to build new xml files
			$files = glob($this->XML_dir_items.'*'); // get all file names
			foreach($files as $file){ // iterate files
				if(is_file($file))
					unlink($file); // delete file
			}
			
			$emitter = 'https://api.merchantos.com/API/Account/'.LI()->API_account.'/Item';

			$offset = 0;
			$limit = 100;
			$c=$j=0;
			$feeds = array();
			$grouped_results = null;
			
			$xml_query_string = 'tag=webstore&limit='.$limit.'&offset='.$offset;
			$products = LI()->api->makeAPICall("Account.Item","Read",null,null,$emitter, $xml_query_string);
			$c = $products->attributes()->count;
			
			syslog (LOG_DEBUG, "Products Count=".$c);
			
			
			$loop_size = ceil($c / $limit);
			for ( $i = 0; $i < $loop_size; $i++ ) {
				$offset = $limit * $i;
				$feeds[] = 'tag=webstore&limit='.$limit.'&offset='.$offset.'&load_relations=all';
			}
			
			// For each feed, store the results as an array
			foreach ( $feeds as $feed ) {
				$products = LI()->api->makeAPICall("Account.Item","Read",null,null,$emitter, $feed);
				if(!empty($products)){
					if($j==0){
						$grouped_results = $products;
					}
					else{
						$dom_grouped_results = dom_import_simplexml($grouped_results);
						$dom_products = dom_import_simplexml($products);
						foreach($dom_products->childNodes as $node){
							$dom_product = $dom_grouped_results->ownerDocument->importNode($node, TRUE);
							$dom_grouped_results->appendChild($dom_product);
						}
					}
				}
				$j++;
			}
			$grouped_results->asXML($this->XML_dir_items.'lightspeed-webstore-products.xml');
			
			syslog (LOG_DEBUG, "Products Finished");
		}
		
		
		/**
		 * On the scheduled action hook, run the function.
		 */
		function lightspeed_import_matrices() {
			// do import every hour
			
			//empty the directory to build new xml files
			$files = glob($this->XML_dir_matrices.'*'); // get all file names
			foreach($files as $file){ // iterate files
				if(is_file($file))
					unlink($file); // delete file
			}
			
			$emitter = 'https://api.merchantos.com/API/Account/'.LI()->API_account.'/ItemMatrix';

			$offset = 0;
			$limit = 100;
			$c=$j=0;
			$feeds = array();
			$grouped_results = null;
			
			$xml_query_string = 'tag=webstore&limit='.$limit.'&offset='.$offset;
			$products = LI()->api->makeAPICall("Account.ItemMatrix","Read",null,null,$emitter, $xml_query_string);
			$c = $products->attributes()->count;
			
			syslog (LOG_DEBUG, "Matrices Count=".$c);
			
			
			if($c>0){
				$loop_size = ceil($c / $limit);
				for ( $i = 0; $i < $loop_size; $i++ ) {
					$offset = $limit * $i;
					$feeds[] = 'tag=webstore&limit='.$limit.'&offset='.$offset.'&load_relations=all';
				}
			
				// For each feed, store the results as an array
				foreach ( $feeds as $feed ) {
					$products = LI()->api->makeAPICall("Account.ItemMatrix","Read",null,null,$emitter, $feed);
					if(!empty($products)){
						if($j==0){
							$grouped_results = $products;
						}
						else{
							$dom_grouped_results = dom_import_simplexml($grouped_results);
							$dom_products = dom_import_simplexml($products);
							foreach($dom_products->childNodes as $node){
								$dom_product = $dom_grouped_results->ownerDocument->importNode($node, TRUE);
								$dom_grouped_results->appendChild($dom_product);
							}
						}
					}
					$j++;
				}
				$grouped_results->asXML($this->XML_dir_matrices.'lightspeed-webstore-product_matrices.xml');
			}
			
			
		}
		
		
		/**
		 * On the scheduled action hook, run the function.
		 */
		function lightspeed_import_vendors() {
			// do import every hour
			
			//empty the directory to build new xml files
			$files = glob($this->XML_dir_vendors.'*'); // get all file names
			foreach($files as $file){ // iterate files
				if(is_file($file))
					unlink($file); // delete file
			}
			
			$emitter = 'https://api.merchantos.com/API/Account/'.LI()->API_account.'/Vendor';

			$offset = 0;
			$limit = 100;
			$c=$j=0;
			$feeds = array();
			$grouped_results = null;
			
			$xml_query_string = 'limit='.$limit.'&offset='.$offset;
			$vendors = LI()->api->makeAPICall("Account.Vendor","Read",null,null,$emitter, $xml_query_string);
			$c = $vendors->attributes()->count;
			
			syslog (LOG_DEBUG, "Vendor Count=".$c);
			
			
			$loop_size = ceil($c / $limit);
			for ( $i = 0; $i < $loop_size; $i++ ) {
				$offset = $limit * $i;
				$feeds[] = '&limit='.$limit.'&offset='.$offset;
			}
			
			// For each feed, store the results as an array
			foreach ( $feeds as $feed ) {
				$vendors = LI()->api->makeAPICall("Account.Vendor","Read",null,null,$emitter, $feed);
				if(!empty($vendors)){
					if($j==0){
						$grouped_results = $vendors;
					}
					else{
						$dom_grouped_results = dom_import_simplexml($grouped_results);
						$dom_vendors = dom_import_simplexml($vendors);
						foreach($dom_vendors->childNodes as $node){
							$dom_vendor = $dom_grouped_results->ownerDocument->importNode($node, TRUE);
							$dom_grouped_results->appendChild($dom_vendor);
						}
					}
				}
				$j++;
			}
			$grouped_results->asXML($this->XML_dir_vendors.'lightspeed-webstore-vendors.xml');
			
			
		}
		
		/**
		 * On the scheduled action hook, run the function.
		 */
		function lightspeed_import_manufacturers() {
			// do import every hour
			
			//empty the directory to build new xml files
			$files = glob($this->XML_dir_manufacturers.'*'); // get all file names
			foreach($files as $file){ // iterate files
				if(is_file($file))
					unlink($file); // delete file
			}
			
			$emitter = 'https://api.merchantos.com/API/Account/'.LI()->API_account.'/Manufacturer';

			$offset = 0;
			$limit = 100;
			$c=$j=0;
			$feeds = array();
			$grouped_results = null;
			
			$xml_query_string = 'limit='.$limit.'&offset='.$offset;
			$manufacturers = LI()->api->makeAPICall("Account.Manufacturer","Read",null,null,$emitter, $xml_query_string);
			$c = $manufacturers->attributes()->count;
			
			syslog (LOG_DEBUG, "Manufacturer Count=".$c);
			
			
			$loop_size = ceil($c / $limit);
			for ( $i = 0; $i < $loop_size; $i++ ) {
				$offset = $limit * $i;
				$feeds[] = '&limit='.$limit.'&offset='.$offset;
			}
			
			// For each feed, store the results as an array
			foreach ( $feeds as $feed ) {
				$manufacturers = LI()->api->makeAPICall("Account.Manufacturer","Read",null,null,$emitter, $feed);
				if(!empty($manufacturers)){
					if($j==0){
						$grouped_results = $manufacturers;
					}
					else{
						$dom_grouped_results = dom_import_simplexml($grouped_results);
						$dom_manufacturers = dom_import_simplexml($manufacturers);
						foreach($dom_manufacturers->childNodes as $node){
							$dom_manufacturer = $dom_grouped_results->ownerDocument->importNode($node, TRUE);
							$dom_grouped_results->appendChild($dom_manufacturer);
						}
					}
				}
				$j++;
			}
			$grouped_results->asXML($this->XML_dir_manufacturers.'lightspeed-webstore-manufacturers.xml');
			
			
		}
		
		
		
		
 
		
		
		/**
		 * Auto-load LI classes on demand to reduce memory consumption.
		 *
		 * @param mixed $class
		 */
		public function autoload( $class ) {
			$path  = null;
			$class = strtolower( $class );
			$file = 'class-' . str_replace( '_', '-', $class ) . '.php';

			if ( strpos( $class, 'li_widget_' ) === 0 ) {
				$path = $this->plugin_path() . '/includes/widgets/';
			}
			elseif ( strpos( $class, 'li_shortcode_' ) === 0 ) {
				$path = $this->plugin_path() . '/includes/shortcodes/';
			}

			if ( $path && is_readable( $path . $file ) ) {
				include_once( $path . $file );
				return;
			}

			// Fallback
			if ( strpos( $class, 'li_' ) === 0 ) {
				$path = $this->plugin_path() . '/includes/';
			}

			if ( $path && is_readable( $path . $file ) ) {
				include_once( $path . $file );
				return;
			}
		}
		
		/**
		 * Define LSI Constants
		 */
		
		private function define_constants(){
			 /* CONSTANTS */
			if(!defined('LSI_MIN_WP_VERSION')) {
				define('LSI_MIN_WP_VERSION', '3.1');
			}

			if(!defined('LSI_PLUGIN_NAME')) {
				define('LSI_PLUGIN_NAME', 'Lightspeed Import');
			}

			if(!defined('LSI_PLUGIN_SLUG')) {
				define('LSI_PLUGIN_SLUG', 'lightspeed-import');
			}

			if(!defined('LSI_DIR_PATH')) {
				define('LSI_DIR_PATH', plugin_dir_path(__FILE__));
			}

			if(!defined('LSI_DIR_URL')) {
				define('LSI_DIR_URL', plugin_dir_url(__FILE__));
			}
		}
		
		/**
		 * Include required core files used in admin and on the frontend.
		 */
		private function includes() {

			/*if ( is_admin() ) {
				include_once( 'includes/admin/class-li-admin.php' );
			}*/
			
			/*if ( ! is_admin() || defined( 'DOING_AJAX' ) ) {
				$this->frontend_includes();
			}*/
			
			
			// include our handy API wrapper that makes it easy to call the API, it also depends on MOScURL to make the cURL call
			require_once('includes/mosapi/class-li-curl.php');
			
			include_once("includes/mosapi/class-li-api.php");
			
			include_once("includes/mosapi/mosapi-functions.php");

						
		}

		/**
		 * Function used to Init Lightspeed Import Template Functions - This makes them pluggable by plugins and themes.
		 */
		public function include_template_functions() {
			if ( ! is_admin() || defined( 'DOING_AJAX' ) ) {
				include_once( 'includes/li-template-functions.php' );
			}
		}
		
		
		//****** Helper Funtions *******//
		
		/**
		 * Get the plugin url.
		 *
		 * @return string
		 */
		public function plugin_url() {
			return untrailingslashit( plugins_url( '/', __FILE__ ) );
		}

		/**
		 * Get the plugin path.
		 *
		 * @return string
		 */
		public function plugin_path() {
			return untrailingslashit( plugin_dir_path( __FILE__ ) );
		}

		
		/**
		 * Get Ajax URL.
		 *
		 * @return string
		 */
		public function ajax_url() {
			return admin_url( 'admin-ajax.php', 'relative' );
		}
				
	}
endif;

/**
 * Returns the main instance of LI to prevent the need to use globals.
 *
 * @since  1.0
 * @return LightspeedImport
 */
function LI() {
	return LightspeedImport::instance();
}

// Global for backwards compatibility.
$GLOBALS['lightspeedimport'] = LI();