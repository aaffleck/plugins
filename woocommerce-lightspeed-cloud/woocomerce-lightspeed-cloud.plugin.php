<?php
/**
 * Plugin Name: WooCommerce LightSpeed Cloud
 * Plugin URI: http://inteleck.com
 * Description: LightSpeed Cloud (http://lightspeedretail.com) Integration with WooCommerce
 * Version: 1.0
 * Author: Aaron Affleck c/o Inteleck
 * Author URI: http://www.inteleck.com
 * License: GNU GPL v2.0
 */

define( 'WCLSC_VERSION', '1.0' );
define( 'WCLSC_OPT_PREFIX', 'wclsc_' );
define( 'WCLSC_META_PREFIX', '_wclsc_' );
define( 'WCLSC_PATH', plugin_dir_path( __FILE__ ) );
define( 'WCLSC_DIR', plugin_dir_url( __FILE__ ) );

require_once( WCLSC_PATH . '/lib/MOSAPI/MOScURL.class.php' );
require_once( WCLSC_PATH . '/lib/MOSAPI/MOSAPICall.class.php' );
require_once( WCLSC_PATH . '/classes/lightspeed-cloud.api.php' );
require_once( WCLSC_PATH . '/classes/wc-lightspeed-cloud.class.php' );

global $wc_lightspeed_cloud;
$wc_lightspeed_cloud = new WC_Lightspeed_Cloud();
