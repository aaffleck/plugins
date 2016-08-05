<?php
/*
Plugin Name: Raysolar Custom Modifications
Plugin URI: http://www.inteleck.com/
Description: This plugin adds custom functionality to the Raysolar website.
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
if ( !class_exists( 'Raysolar_Custom_Mods' ) ) {
	class Raysolar_Custom_Mods {
		
		const VERSION = '1.0';
		
		public $plugin_dir;
		public $plugin_url;
		public $plugin_domain = 'raysolar-custom-mods';

		
		
		function __construct(){
			$this->pluginDir = basename(dirname(__FILE__));
			$this->pluginUrl = WP_CONTENT_URL.'/plugins/'.plugin_basename(dirname(__FILE__));
			
			//Definitions
			define("SOAP_CLIENT_BASEDIR", dirname(__FILE__)."/forceToolkit/soapclient");
			define("USERNAME", "info@raysolar.ca");
			define("PASSWORD", "Goose123");
			define("SECURITY_TOKEN", "77nXkxyv3UMyEBDsBlZvU2qTc");
			define("salesforce_ID", '00D70000000MyNR');
			
			//Hooks
			register_activation_hook(__FILE__, array($this, 'activate_rcm'));
			register_deactivation_hook(__FILE__, array($this, 'deactivate_rcm'));
			
			//Actions
			//add_action( 'init', 'create_product_taxonomies', 10 );
			//add_action('admin_head', array( $this, 'add_post_meta_box' ));
			//add_action('save_post', array($this, 'add_post_meta'), 15);
			//add_action('publish_post', array($this, 'add_post_meta'), 15);
			add_action('admin_head', array($this, 'set_admin_resources'));
			//add_action('init', array($this, 'set_frontend_resources'));
			//add_action('admin_init', array($this, 'get_picture_file_from_local_server'));
			//add_action( 'init', array($this, 'rs_custom_taxonomies'));
			//add_action( 'init', array($this, 'rs_register_widgets'));
			add_action('wpcf7_before_send_mail', array($this, 'rcm_send_partner_data_salesforce'), 10, 2);
			//add_action("woocommerce_checkout_order_processed", array($this, 'rcm_send_cutomer_data_salesforce'), 10, 2);

			
			// Add columns
			//add_filter( 'manage_edit-product_brand_columns', array( $this, 'product_brand_columns' ) );
			//add_filter( 'manage_product_brand_custom_column', array( $this, 'product_brand_column' ), 10, 3 );
			
			//Filters
			//add_filter('the_content', array($this, 'moodys_tax_tables'));
			//add_filter('user_contactmethods', array($this, 'hide_old_and_update_fields'),10,1);
			add_filter('woocommerce_get_availability', array($this, 'availability_filter_func'));
			add_filter('woocommerce_free_price_html', array($this,'rsChangeFreePrice'));
			
			//remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering');
			
			add_filter( 'woocommerce_subcategory_count_html', array($this,'rs_remove_counts'));
			

		}
		
		
		/*public function rcm_send_cutomer_data_salesforce(&$order_id, &$checkout){
		
			$checkout = $woocommerce->checkout();
			$order = new WC_Order( $order_id );
			
			extract($WPCF7_ContactForm->posted_data, EXTR_PREFIX_SAME, '_rs');
			
			$exists = 0;
				
			require_once (SOAP_CLIENT_BASEDIR.'/SforceEnterpriseClient.php');
			require_once (SOAP_CLIENT_BASEDIR.'/SforceHeaderOptions.php');

			try {
				$mySforceConnection = new SforceEnterpriseClient();
				$mySforceConnection->createConnection(SOAP_CLIENT_BASEDIR."/enterprise.wsdl.xml");
				$mySforceConnection->login(USERNAME, PASSWORD.SECURITY_TOKEN);
			
				//test Data
				/$_POST['first-name'] = "Aaron";
				$_POST['last-name'] = "Affleck";
				$_POST['phone'] = "8074075426";
				$_POST['company'] = "The Inteleck Dev";
				$_POST['email'] = "sup@inteleck.com";
				
				//Get record if exists
				$query = "SELECT Id from Lead where FirstName = '".$first_name."' and LastName = '".$last_name."' and Company = '".$company."'";
				$response = $mySforceConnection->query($query);
				$recs = $response->records;
				
				//error_log(count($rps));
				

				//Partner_Record_Type
				if(count($recs)>0)
					$exists = 1;

				If(!$exists){
					$records = array();

					$records[0] = new stdclass();
					$records[0]->FirstName = $first_name;
					$records[0]->LastName = $last_name;
					$records[0]->Company = $company;
					$records[0]->Phone = $phone;
					$records[0]->Email = $email;
					$records[0]->LeadSource = 'Web';
					$records[0]->Lead_Type__c = 'B2B';
					$records[0]->RecordTypeId = $formToLead[$_wpcf7_unit_tag]['recordTypeId'];

					$response = $mySforceConnection->create($records, 'Lead');
					
					$ids = array();
					foreach ($response as $i => $result) {
						echo $records[$i]->FirstName . " " . $records[$i]->LastName . " "
								. $records[$i]->Phone . " created with id " . $result->id
								. "<br/>\n";
						array_push($ids, $result->id);
					}
					error_log(serialize($response));
				}
				return $WPCF7_ContactForm;
			} catch(Exception $e) {
				echo $mySforceConnection->getLastRequest();
				echo $e->faultstring;
			}
		}*/
		
		
		
		public function rcm_send_partner_data_salesforce(&$WPCF7_ContactForm, &$order_id, &$checkout){
			
			if(!isset($WPCF7_ContactForm))
				return false;
			
			extract($WPCF7_ContactForm->posted_data, EXTR_PREFIX_SAME, '_rs');
				
			$formToLead = array(
				'wpcf7-f8106-p8101-o1' => array(
					'type' => 'Lead',
					'recordTypeId' => '01270000000E5TF',
					'query' => "SELECT Id from Lead where FirstName = '".$first_name."' and LastName = '".$last_name."' and Company = '".$company."'"
				),
				'wpcf7-f6561-p6562-o1' => array(
					'type' => 'Lead',
					'recordTypeId' => '01270000000E5TF',
					'query' => "SELECT Id from Lead where FirstName = '".$first_name."' and LastName = '".$last_name."' and Company = '".$company."'"
				)
			);
			
			$submittedFormID = $WPCF7_ContactForm->id;
			
			$exists = 0;
				
			require_once (SOAP_CLIENT_BASEDIR.'/SforceEnterpriseClient.php');
			require_once (SOAP_CLIENT_BASEDIR.'/SforceHeaderOptions.php');

			try {
				$mySforceConnection = new SforceEnterpriseClient();
				$mySforceConnection->createConnection(SOAP_CLIENT_BASEDIR."/enterprise.wsdl.xml");
				$mySforceConnection->login(USERNAME, PASSWORD.SECURITY_TOKEN);
			
				//test Data
				/*$_POST['first-name'] = "Aaron";
				$_POST['last-name'] = "Affleck";
				$_POST['phone'] = "8074075426";
				$_POST['company'] = "The Inteleck Dev";
				$_POST['email'] = "sup@inteleck.com";*/
				
				//Get record if exists
				$query = $formToLead[$_wpcf7_unit_tag]['query'];
				$response = $mySforceConnection->query($query);
				$recs = $response->records;
				
				//error_log(count($rps));
				

				//Partner_Record_Type
				if(count($recs)>0)
					$exists = 1;

				If(!$exists){
					$records = array();

					$records[0] = new stdclass();
					$records[0]->FirstName = $first_name;
					$records[0]->LastName = $last_name;
					$records[0]->Company = $company;
					$records[0]->Phone = $phone;
					$records[0]->Email = $email;
					$records[0]->LeadSource = 'Web';
					$records[0]->Lead_Type__c = 'B2B';
					$records[0]->RecordTypeId = $formToLead[$_wpcf7_unit_tag]['recordTypeId'];

					$response = $mySforceConnection->create($records, 'Lead');
					
					/*$ids = array();
					foreach ($response as $i => $result) {
						echo $records[$i]->FirstName . " " . $records[$i]->LastName . " "
								. $records[$i]->Phone . " created with id " . $result->id
								. "<br/>\n";
						array_push($ids, $result->id);
					}*/
					error_log(serialize($response));
				}
				return $WPCF7_ContactForm;
			} catch(Exception $e) {
				echo $mySforceConnection->getLastRequest();
				echo $e->faultstring;
			}
		}
		
		/**
		 * brand thumbnail fields.
		 *
		 * @access public
		 * @return void
		 */
		public function add_brand_fields() {
			?>
			<div class="form-field">
				<label for="display_type"><?php _e( 'Display type', 'woocommerce' ); ?></label>
				<select id="display_type" name="display_type" class="postform">
					<option value=""><?php _e( 'Default', 'woocommerce' ); ?></option>
					<option value="products"><?php _e( 'Products', 'woocommerce' ); ?></option>
					<option value="subbrands"><?php _e( 'Subbrands', 'woocommerce' ); ?></option>
					<option value="both"><?php _e( 'Both', 'woocommerce' ); ?></option>
				</select>
			</div>
			<div class="form-field">
				<label><?php _e( 'Thumbnail', 'woocommerce' ); ?></label>
				<div id="product_brand_thumbnail" style="float:left;margin-right:10px;"><img src="<?php echo wc_placeholder_img_src(); ?>" width="60px" height="60px" /></div>
				<div style="line-height:60px;">
					<input type="hidden" id="product_brand_thumbnail_id" name="product_brand_thumbnail_id" />
					<button type="button" class="upload_image_button button"><?php _e( 'Upload/Add image', 'woocommerce' ); ?></button>
					<button type="button" class="remove_image_button button"><?php _e( 'Remove image', 'woocommerce' ); ?></button>
				</div>
				<script type="text/javascript">

					 // Only show the "remove image" button when needed
					 if ( ! jQuery('#product_brand_thumbnail_id').val() )
						 jQuery('.remove_image_button').hide();

					// Uploading files
					var file_frame;

					jQuery(document).on( 'click', '.upload_image_button', function( event ){

						event.preventDefault();

						// If the media frame already exists, reopen it.
						if ( file_frame ) {
							file_frame.open();
							return;
						}

						// Create the media frame.
						file_frame = wp.media.frames.downloadable_file = wp.media({
							title: '<?php _e( 'Choose an image', 'woocommerce' ); ?>',
							button: {
								text: '<?php _e( 'Use image', 'woocommerce' ); ?>',
							},
							multiple: false
						});

						// When an image is selected, run a callback.
						file_frame.on( 'select', function() {
							attachment = file_frame.state().get('selection').first().toJSON();

							jQuery('#product_brand_thumbnail_id').val( attachment.id );
							jQuery('#product_brand_thumbnail img').attr('src', attachment.url );
							jQuery('.remove_image_button').show();
						});

						// Finally, open the modal.
						file_frame.open();
					});

					jQuery(document).on( 'click', '.remove_image_button', function( event ){
						jQuery('#product_brand_thumbnail img').attr('src', '<?php echo wc_placeholder_img_src(); ?>');
						jQuery('#product_brand_thumbnail_id').val('');
						jQuery('.remove_image_button').hide();
						return false;
					});

				</script>
				<div class="clear"></div>
			</div>
			<?php
		}

		/**
		 * Edit brand thumbnail field.
		 *
		 * @access public
		 * @param mixed $term Term (brand) being edited
		 * @param mixed $taxonomy Taxonomy of the term being edited
		 */
		public function edit_brand_fields( $term, $taxonomy ) {

			$display_type	= get_woocommerce_term_meta( $term->term_id, 'display_type', true );
			$image 			= '';
			$thumbnail_id 	= absint( get_woocommerce_term_meta( $term->term_id, 'thumbnail_id', true ) );
			if ( $thumbnail_id )
				$image = wp_get_attachment_thumb_url( $thumbnail_id );
			else
				$image = wc_placeholder_img_src();
			?>
			<tr class="form-field">
				<th scope="row" valign="top"><label><?php _e( 'Display type', 'woocommerce' ); ?></label></th>
				<td>
					<select id="display_type" name="display_type" class="postform">
						<option value="" <?php selected( '', $display_type ); ?>><?php _e( 'Default', 'woocommerce' ); ?></option>
						<option value="products" <?php selected( 'products', $display_type ); ?>><?php _e( 'Products', 'woocommerce' ); ?></option>
						<option value="subbrands" <?php selected( 'subbrands', $display_type ); ?>><?php _e( 'Subbrands', 'woocommerce' ); ?></option>
						<option value="both" <?php selected( 'both', $display_type ); ?>><?php _e( 'Both', 'woocommerce' ); ?></option>
					</select>
				</td>
			</tr>
			<tr class="form-field">
				<th scope="row" valign="top"><label><?php _e( 'Thumbnail', 'woocommerce' ); ?></label></th>
				<td>
					<div id="product_brand_thumbnail" style="float:left;margin-right:10px;"><img src="<?php echo $image; ?>" width="60px" height="60px" /></div>
					<div style="line-height:60px;">
						<input type="hidden" id="product_brand_thumbnail_id" name="product_brand_thumbnail_id" value="<?php echo $thumbnail_id; ?>" />
						<button type="submit" class="upload_image_button button"><?php _e( 'Upload/Add image', 'woocommerce' ); ?></button>
						<button type="submit" class="remove_image_button button"><?php _e( 'Remove image', 'woocommerce' ); ?></button>
					</div>
					<script type="text/javascript">

						// Uploading files
						var file_frame;

						jQuery(document).on( 'click', '.upload_image_button', function( event ){

							event.preventDefault();

							// If the media frame already exists, reopen it.
							if ( file_frame ) {
								file_frame.open();
								return;
							}

							// Create the media frame.
							file_frame = wp.media.frames.downloadable_file = wp.media({
								title: '<?php _e( 'Choose an image', 'woocommerce' ); ?>',
								button: {
									text: '<?php _e( 'Use image', 'woocommerce' ); ?>',
								},
								multiple: false
							});

							// When an image is selected, run a callback.
							file_frame.on( 'select', function() {
								attachment = file_frame.state().get('selection').first().toJSON();

								jQuery('#product_brand_thumbnail_id').val( attachment.id );
								jQuery('#product_brand_thumbnail img').attr('src', attachment.url );
								jQuery('.remove_image_button').show();
							});

							// Finally, open the modal.
							file_frame.open();
						});

						jQuery(document).on( 'click', '.remove_image_button', function( event ){
							jQuery('#product_brand_thumbnail img').attr('src', '<?php echo wc_placeholder_img_src(); ?>');
							jQuery('#product_brand_thumbnail_id').val('');
							jQuery('.remove_image_button').hide();
							return false;
						});

					</script>
					<div class="clear"></div>
				</td>
			</tr>
			<?php
		}

		/**
		 * save_brand_fields function.
		 *
		 * @access public
		 * @param mixed $term_id Term ID being saved
		 * @param mixed $tt_id
		 * @param mixed $taxonomy Taxonomy of the term being saved
		 * @return void
		 */
		public function save_brand_fields( $term_id, $tt_id, $taxonomy ) {
			if ( isset( $_POST['display_type'] ) )
				update_woocommerce_term_meta( $term_id, 'display_type', esc_attr( $_POST['display_type'] ) );

			if ( isset( $_POST['product_brand_thumbnail_id'] ) )
				update_woocommerce_term_meta( $term_id, 'thumbnail_id', absint( $_POST['product_brand_thumbnail_id'] ) );

			delete_transient( 'wc_term_counts' );
		}

		/**
		 * Description for product_brand page to aid users.
		 *
		 * @access public
		 * @return void
		 */
		public function product_brand_description() {
			echo wpautop( __( 'Product brands for your store can be managed here. To change the order of brands on the front-end you can drag and drop to sort them. To see more brands listed click the "screen options" link at the top of the page.', 'woocommerce' ) );
		}

		/**
		 * Description for shipping class page to aid users.
		 *
		 * @access public
		 * @return void
		 */
		public function shipping_class_description() {
			echo wpautop( __( 'Shipping classes can be used to group products of similar type. These groups can then be used by certain shipping methods to provide different rates to different products.', 'woocommerce' ) );
		}

		/**
		 * Thumbnail column added to brand admin.
		 *
		 * @access public
		 * @param mixed $columns
		 * @return array
		 */
		public function product_brand_columns( $columns ) {
			$new_columns          = array();
			$new_columns['cb']    = $columns['cb'];
			$new_columns['thumb'] = __( 'Image', 'woocommerce' );

			unset( $columns['cb'] );

			return array_merge( $new_columns, $columns );
		}

		/**
		 * Thumbnail column value added to brand admin.
		 *
		 * @access public
		 * @param mixed $columns
		 * @param mixed $column
		 * @param mixed $id
		 * @return array
		 */
		public function product_brand_column( $columns, $column, $id ) {

			if ( $column == 'thumb' ) {

				$image 			= '';
				$thumbnail_id 	= get_woocommerce_term_meta( $id, 'thumbnail_id', true );

				if ($thumbnail_id)
					$image = wp_get_attachment_thumb_url( $thumbnail_id );
				else
					$image = wc_placeholder_img_src();

				// Prevent esc_url from breaking spaces in urls for image embeds
				// Ref: http://core.trac.wordpress.org/ticket/23605
				$image = str_replace( ' ', '%20', $image );

				$columns .= '<img src="' . esc_url( $image ) . '" alt="Thumbnail" class="wp-post-image" height="48" width="48" />';

			}

			return $columns;
		}
		
		function availability_filter_func($availability)
		{
		$availability['availability'] = str_ireplace('Out of stock', 'Call', $availability['availability']);
		return $availability;
		}
		
		
 
		function rsChangeFreePrice($price) {
			return '<span class="amount">Call for price</span>';
		}
		
		function rs_remove_counts($html){
			return '';
		}
		
		function rs_register_widgets(){
			include_once( 'classes/widgets/class-rs-widget-product-brands.php' );
		}
		
		
				
		//create two taxonomies, genres and writers for the post type "book"
		function rs_custom_taxonomies()
		{
		  // Add new taxonomy, make it hierarchical (like brands)
		  $labels = array(
			'name' => _x( 'Brands', 'taxonomy general name' ),
			'singular_name' => _x( 'Brand', 'taxonomy singular name' ),
			'search_items' =>  __( 'Search Brands' ),
			'all_items' => __( 'All Brands' ),
			'parent_item' => __( 'Parent Brand' ),
			'parent_item_colon' => __( 'Parent Brands:' ),
			'edit_item' => __( 'Edit Brands' ),
			'update_item' => __( 'Update Brands' ),
			'add_new_item' => __( 'Add New Brand' ),
			'new_item_name' => __( 'New Brand Name' ),
			'menu_name' => __( 'Brands' ),
		  );     

		  register_taxonomy('product_brand',array('product'), array(
			'hierarchical' => true,
			'show_in_nav_menus' => true,
			'labels' => $labels,
			'show_ui' => true,
			'query_var' => true,
			//'rewrite' => true,
			'rewrite' => array( 'slug' => 'product-brand', 'with_front' => true ),
		  ));
		  
		  register_taxonomy_for_object_type( 'product_brand', 'product' );
		}

		
        function set_admin_resources(){
        	//wp_enqueue_style('mcmStyles', $this->pluginUrl .'/css/mcm-styles.css', array(), Moodys_Custom_Mods::VERSION, 'screen');
        	//wp_enqueue_script('mcmTools', $this->pluginUrl .'/js/jquery.tools.min.js', array('jquery'));
        	//wp_enqueue_script('mcmAdminUtils', $this->pluginUrl .'/js/mcm-admin-utils.js', array('jquery'));
        	wp_enqueue_media();
        }
        
        function set_frontend_resources(){
        	wp_enqueue_script('faqScript', get_bloginfo('stylesheet_directory').'/assets/js/show-hide.js', array('jquery'));        	
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
		function activate_rcm(){
			if(!load_plugin_textdomain($this->plugin_domain,'/wp-content/languages/'))
				load_plugin_textdomain($this->plugin_domain,basename(dirname(__FILE__)) . '/languages/');
		}//End Activation
		
		/**
		 * Nothing to deactivate
		 * @return void
		 */
		function deactivate_rcm(){
		
		}//End Deactivation
		
	}//End Class
	
	$rcm = new Raysolar_Custom_Mods();
	
} //End If Class exists
?>
