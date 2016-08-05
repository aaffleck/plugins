<?php
/*
Plugin Name: Directory Sync
Plugin URI: http://inteleck.com
Description: Sync the virtual tour folder on the server with Wordpress and create or remove posts/pages based on the directory changes.
Version: 1.0
Author: Aaron Affleck
Author URI: http://inteleck.com
*/

/*
Copyright (C) 2011 Inteleck, inteleck.com
*/
class Directory_sync {
	
	var $path;
	var $er_dir_tree;
	var $parent_page = "6";
	var $version = "1.0";
	var $db_version = "1.0";
	var $existing_db_version = "1.0";
 	var $upgrade_filename = 'temp.zip';
 	var $upgrade_folder;
 	var $upgrade_error;
 	var $upgrade_url = 'http://www.inteleck.com/plugins/directory-sync.zip';
 	var $log_file;
 	var $do_log;
 	var $wp_version;
 	var $temp_dir_size;
 	var $temp_dir_tree;
 	var $set_dir_size;
 	var $set_dir_tree;
 	var $vtour_cat;
 	var $vtour_new_cat;
 	var $vtour_up_cat;
 	var $page_template;
 	var $thumbnail_meta_key;
 	var $seo_keywords_meta_key;
 	var $seo_description_meta_key;
 	var $seo_title_meta_key;
 	var $seo_title_attribute_meta_key;
 	var $seo_menu_label_meta_key;
 	var $header_custom_meta_key;
 	var $header_vr_ID_meta_key;
 	var $permalinkcatkey;
	
	function init(){
		global $wp_version, $wpdb;		
		$this->wp_version = $wp_version;
		$this->path = $_SERVER['DOCUMENT_ROOT']."/wp-content/uploads/virtual_tours/";
		$this->vtour_cat = 16;
		$this->vtour_new_cat = 17;
		$this->vtour_up_cat = 18;
		$this->page_template = 'template-virtual-tour-right-sidebar.php';
		$this->thumbnail_meta_key = '_thumbnail_id';
		$this->seo_keywords_meta_key = '_aioseop_keywords';
		$this->seo_description_meta_key = '_aioseop_description';
		$this->seo_title_meta_key = '_aioseop_title';
		$this->header_custom_meta_key = '_header_custom';
		$this->header_vr_ID_meta_key = '_header_custom_vr';
		$this->permalinkcatkey = '_category_permalink';
		$this->page_template_meta_key = '_wp_page_template';
		$this->upgrade_filename = dirname(__FILE__) . '/' . $this->upgrade_filename;
		$this->upgrade_folder = dirname(__FILE__);
		if (function_exists('load_plugin_textdomain')) {
			load_plugin_textdomain('directory-sync', 'wp-content/plugins/directory-sync');
		}
		$this->er_options_init();
	}
	
	function er_options_init(){
		$this->existing_db_version = get_option('erds_db_version');
//		$this->temp_dir_size = get_option('er_vr_dir_size');
//		$this->temp_dir_tree = get_option('er_dir_tree');
		$options = get_option('optionsDirSync');
		if(empty($this->existing_db_version)) $this->existing_db_version = $this->db_version;
//		if(empty($this->temp_dir_size)) $this->set_dir_size = $this->er_dir_size();
//		if(empty($this->temp_dir_tree)) $this->set_dir_tree = $this->er_dir_tree();
		if(!is_array($options)) $options = array();
		if (!isset($options['accessLevel'])) $options['accessLevel'] = 'level_10';
		if(!isset($options)) update_option('optionsDirSync', $options);
		update_option('erds_db_version', $this->db_version);
	}
	
	function get_base() {
   		 return '/'.end(explode('/', str_replace(array('\\','/directory-sync.php'),array('/',''),__FILE__)));
	}
	
	function log($message) {
		if ($this->do_log) {
			error_log(date('Y-m-d H:i:s') . " " . $message . "\n", 3, $this->log_file);
		}
	}

	function download_newest_version() {
		$success = true;
	    $file_content = $this->get_url($this->upgrade_url);
	    if ($file_content === false) {
	    	$this->upgrade_error = sprintf(__("Could not download distribution (%s)"), $this->upgrade_url);
			$success = false;
	    } else if (strlen($file_content) < 100) {
	    	$this->upgrade_error = sprintf(__("Could not download distribution (%s): %s"), $this->upgrade_url, $file_content);
			$success = false;
	    } else {
	    	$this->log(sprintf("filesize of download ZIP: %d", strlen($file_content)));
		    $fh = @fopen($this->upgrade_filename, 'w');
		    $this->log("fh is $fh");
		    if (!$fh) {
		    	$this->upgrade_error = sprintf(__("Could not open %s for writing"), $this->upgrade_filename);
		    	$this->upgrade_error .= "<br />";
		    	$this->upgrade_error .= sprintf(__("Please make sure %s is writable"), $this->upgrade_folder);
		    	$success = false;
		    } else {
		    	$bytes_written = @fwrite($fh, $file_content);
			    $this->log("wrote $bytes_written bytes");
		    	if (!$bytes_written) {
			    	$this->upgrade_error = sprintf(__("Could not write to %s"), $this->upgrade_filename);
			    	$success = false;
		    	}
		    }
		    if ($success) {
		    	fclose($fh);
		    }
	    }
	    return $success;
	}

	function install_newest_version() {
		$success = $this->download_newest_version();
	    if ($success) {
		    $success = $this->extract_plugin();
		    unlink($this->upgrade_filename);
	    }
	    return $success;
	}

	function extract_plugin() {
	    if (!class_exists('PclZip')) {
	        require_once ('pclzip.lib.php');
	    }
	    $archive = new PclZip($this->upgrade_filename);
	    $files = $archive->extract(PCLZIP_OPT_STOP_ON_ERROR, PCLZIP_OPT_REPLACE_NEWER, PCLZIP_OPT_REMOVE_ALL_PATH, PCLZIP_OPT_PATH, $this->upgrade_folder);
	    $this->log("files is $files");
	    if (is_array($files)) {
	    	$num_extracted = sizeof($files);
		    $this->log("extracted $num_extracted files to $this->upgrade_folder");
		    $this->log(print_r($files, true));
	    	return true;
	    } else {
	    	$this->upgrade_error = $archive->errorInfo();
	    	return false;
	    }
	}	
	
	function is_admin() {
		return current_user_can('level_8');
	}
	
	function is_directory_writable($directory) {
		$filename = $directory . '/' . 'tmp_file_' . time();
		$fh = @fopen($filename, 'w');
		if (!$fh) {
			return false;
		}
		
		$written = fwrite($fh, "test");
		fclose($fh);
		unlink($filename);
		if ($written) {
			return true;
		} else {
			return false;
		}
	}

	function is_upgrade_directory_writable() {
		//return $this->is_directory_writable($this->upgrade_folder);
		// let's assume it is
		return true;
	}
	
	function er_dir_size(){
		$bytestotal=0;
		if(is_dir($this->path)){
			$ite=new RecursiveDirectoryIterator($this->path);
			foreach (new RecursiveIteratorIterator($ite) as $filename=>$cur) {
				$filesize=$cur->getSize();
				$bytestotal+=$filesize;
			}
			return $bytestotal;
		}
		return 0;
	}
	
	function er_create_dir_tree(){
		$files = array();
		if(is_dir($this->path)){
			$ite=new RecursiveDirectoryIterator($this->path);
			foreach (new RecursiveIteratorIterator($ite) as $filename => $info) {
				$files[] = $filename;
			}
			
		}
		return $files;
	}
	
	function er_dir_changes(){
		$dir_size = $this->er_dir_size();
		if($dir_size){
			//echo "Dir Size = ".$dir_size."<br />";
			$existing_dir_size = get_option('er_vr_dir_size');
			//echo "Existing Dir Size = ".$existing_dir_size."<br />";
			if($dir_size != $existing_dir_size){
				return true;
			}
			//**Future: Could add what dirs have been removed and display them for maintenance sake.
		}
		return false;
	}
	
	function er_admin_options(){
		global $shortname, $options;
		screen_icon('options-general');
		?>
		<h2 class="webtreats-page-title">Directory Sync</h2>
		<?php
		if (isset($_REQUEST['action']) && 'save' == $_REQUEST['action'] ) {
			$this->er_sync_the_directory();
		}
		?>
		<form method="post" action="">
		<div class="submit webtreats-submit">
			<input class="button-primary" type="submit" name="save" value="Sync Directory" id="directory-sync-control" /><input type="hidden" name="action" value="save" />
		</div>
		</form>
		<?php
	}
	
	function er_sync_the_directory(){
		$changes = $this->er_dir_changes();
		if($changes){
			$this->er_sync();
			$this->er_update_options();
		}
		else{
			echo '<div id="message" class="updated"><p><strong>DIrectory is fully Synced. There are no updates to make at this time.</strong></p></div>';
			//$this->er_update_options();
		}
	}
	
	function er_update_options(){
		//Update DB with new entries
		$dir_size = $this->er_dir_size();
		$er_dir_tree = $this->er_dir_tree;
		update_option('er_vr_dir_size', $dir_size);
		update_option('er_dir_tree', $er_dir_tree);
	}
	
	//Do the Magic
	function er_sync(){
		$creation_success = false;
		$remove_success = false;
		$last_folder_name_add = '';
		$last_folder_name_rem = '';
		//Find out what has changed, so we only have to work with that, instead of all of the files
		$what_changes  = $this->er_what_changes();
		$add_folders = array();
		$rem_folders = array();
		if(is_array($what_changes) && count($what_changes)>0){
			$adds = $what_changes[0];
			$subs = $what_changes[1];
			//print_r($adds);
			if(is_array($adds) && count($adds)>0){
				foreach($adds as $change){
					//echo "add: ".$change."<br />";
					$substr = substr($change,strpos($change, 'virtual_tours')+14);
					$folder_name = substr($substr,0,strpos($substr,'/'));
					if($folder_name != $last_folder_name_add){
						array_push($add_folders, $folder_name);
						$last_folder_name_add = $folder_name;
					}
				}
				//Let's create some posts
				//$results = array($pages_created,$pages_updated,$page_titles_add,$page_titles_update,$posts_added,$posts_updated,$creation_errors);
				$creations = $this->create_posts($add_folders);
				if(is_array($creations)&&count($creations)>0 && !$creations[6]){
					//Display confirmation that posts were created
					$this->er_display_creation_results($creations);
				}
				else if(is_array($creations)&&count($creations)>0 && $creations[6]){
					echo '<div id="message" class="updated"><p><strong>Error: There was an error while processing the addition of your new posts. Please try again.</strong></p></div>';
				}
				else{
					//Display failure message
					echo '<div id="message" class="updated"><p><strong>Message: Nothing was added at this time.</strong></p></div>';
				}
			}
			if(is_array($subs) && count($subs)>0){
				//print_r($subs);
				foreach($subs as $change){
					//echo "rem: ".$change."<br />";
					$substr = substr($change,strpos($change, 'virtual_tours')+14);
					$folder_name = substr($substr,0,strpos($substr,'/'));
					if($folder_name != $last_folder_name_rem){
						array_push($rem_folders, $folder_name);
						$last_folder_name_rem = $folder_name;
					}
				}
				// Let's unpublish the page and posts that were removed from the directory
				//$results = array($pages_unpublished, $posts_unpublished, $page_titles, $errors);
				$removes = $this->unpublish_posts($rem_folders);
				if(is_array($removes)&&count($removes)>0 && $removes[3]===false){
					//Display confirmation that posts were unpublished					
					$this->er_display_remove_results($removes);
				}
				else if(is_array($removes)&&count($removes)>0 && $removes[3]){
					echo '<div id="message" class="updated"><p><strong>Error: There was an error while processing the removal of your posts. Please try again.</strong></p></div>';
				}
				else{
					//Display failure message
					echo '<div id="message" class="updated"><p><strong>Message: Nothing was unpublished at this time.</strong></p></div>';
				}
			}
			echo '<div id="message" class="updated"><p><strong>DIrectory sync complete.</strong></p></div>';
		}
	}
	
	function er_display_creation_results($creations){
		if($creations[0]>0){
		?>
			<h3>Pages Created</h3>
			<p>Count = <?php echo $creations[0]; ?></p>
			<h4>Created Page Titles</h4>
			<ul>
				<?php
				if(is_array($creations[2])&&count($creations[2])>0){
					foreach($creations[2] as $create){
					?>
					<li><?php echo $create ?></li>
					<?php
					}
				}
				?>
			</ul>					
			<hr />
		<?php } ?>
		<?php if($creations[1]>0){ ?>
			<h3>Pages Updated</h3>
			<p>Count = <?php echo $creations[1]; ?></p>
			<h4>Updated Page Titles</h4>
			<ul>
				<?php
				if(is_array($creations[3])&&count($creations[3])>0){
					foreach($creations[3] as $create){
					?>
					<li><?php echo $create ?></li>
					<?php
					}
				}
				?>
			</ul>					
			<hr />
		<?php } ?>
		<?php if($creations[4]>0){ ?>
			<h3>Posts Created</h3>
			<p>Count = <?php echo $creations[4]; ?></p>
			<hr />
			<?php } ?>
			<?php if($creations[5]>0){ ?>
			<h3>Posts Updated</h3>
			<p>Count = <?php echo $creations[5]; ?></p>
	<?php
		}
	}
	
	function er_display_remove_results($removes){
	?>
		<h3>Pages Unpublished</h3>
		<p>Count = <?php echo $removes[0]; ?></p>
		<h4>Page Titles</h4>
		<ul>
			<?php
			if(is_array($removes[2])&&count($removes[2])>0){
				foreach($removes[2] as $rem){
				?>
				<li><?php echo $rem; ?></li>
				<?php
				}
			}
			?>
		</ul>
		<hr />
		<h3>Posts Unpublished</h3>
		<p>Count = <?php echo $removes[1]; ?></p>
	<?php
	}
	
	function er_what_changes(){
		$er_ex_dir_tree = get_option('er_dir_tree');
		if(is_array($er_ex_dir_tree)===false)
			$er_ex_dir_tree = array();
		$this->er_dir_tree = $this->er_create_dir_tree();
		$additions = array_diff($this->er_dir_tree, $er_ex_dir_tree);
		$substractions = array_diff($er_ex_dir_tree, $this->er_dir_tree);
		return array($additions,$substractions);
	}
	
	function regExpFile($regExp, $dir, $regType='P', $case='') {
		# Two parameters accepted by $regType are E for ereg* functions
		# and P for preg* functions
		$func = ( $regType == 'P' ) ? 'preg_match' : 'ereg' . $case;
		
		# Note, technically anything other than P will use ereg* functions;
		# however, you can specify whether to use ereg or eregi by
		# declaring $case as "i" to use eregi rather than ereg
		
		$open = opendir($dir);
		while( ($file = readdir($open)) !== false ) {
			if ( $func($regExp, $file) ) {
				return $file;
			}
		} // End while
		return false;
	} // End function

	
	function create_posts($folders){
		global $wpdb;
		$errors = false;
		$pages_created = 0;
		$pages_updated = 0;
		$posts_added = 0;
		$posts_not_added = array();
		$page_exists = false;
		$post_changes = false;
		$posts_updated = 0;
		$page_titles_add = array();
		$page_titles_update = array();
		$pages_not_updated = array();
		$posts_not_updated = array();
		$seo_keywords = '';
		$existing_page_id = 0;
		
		require_once(ABSPATH . "wp-admin" . '/includes/image.php');
		if(is_array($folders)&&count($folders)>0){
			//print_r($folders);
			foreach($folders as $folder){
				//Set the permissions to full on the folder
				chmod($this->path.$folder, 0777);

				//New Title
				$new_page_title = ucwords(str_replace(array('-','_')," ",$folder));
				$tour_name = $folder;
				$page_name = $folder;
				$standardized_name = str_replace("-", "%", $page_name);
				//echo $standardized_name."<br />";
				//create category and get cat ID if it doesn't already exist;
				$category_name = $new_page_title;
				if(!term_exists($category_name))
					$catid = wp_create_category($category_name, $this->vtour_cat);
				else
					$catid = get_cat_ID($category_name);
				//create post object for new page if it doesn't exist
				$google_page_title = $new_page_title." Virtual Tour by Inteleck";
				//Tags and SEO
				//$tags = explode(" ", $new_page_title);
				$seo_keywords = $new_page_title;
				$seo_description = $new_page_title;
				//END Tags and SEO				
				$existing_page_id = $wpdb->get_var( $wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE post_name LIKE %s and post_status not in('trash', 'inherit', 'auto-draft')", $standardized_name) );
				if($existing_page_id){
					//echo "PAGE EXISTS";
					$page_args = array('ID' => $existing_page_id, 'post_status' => 'draft');
					$page_update = wp_update_post($page_args);
					if($page_update){
						$pages_updated++;
						array_push($page_titles_update, $new_page_title);
					}
					else{
						array_push($pages_not_updated, $new_page_title);
						exit();
					}
					
					//Page exists. Let's check if there are any changes to the directory, shall we?
					foreach (new DirectoryIterator($this->path.$folder) as $fileInfo) {
						if($fileInfo->isDot()) continue;
						$name = $fileInfo->getFilename();
						//get one of the subfolders and iterate through it to create the posts
						if($name=="low"){
							foreach (new DirectoryIterator($this->path.$folder.'/low') as $files) {
								if($files->isDot()) continue;
								$posttitle = $new_page_title;
								$filenumber = str_replace(array("image_","image-"),"",$files->getFilename());
								$posttitle .= " ".$filenumber;
								$postname = $tour_name."-".$filenumber;
								$standardized_postname = str_replace("-", "%", $postname);
								//check if this post exists already
								$post_id = 0;
								$status = 'draft';
								$post_id = $wpdb->get_var( $wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE post_name LIKE %s and post_status not in('trash', 'inherit', 'auto-draft')", $standardized_postname) );
								if($post_id && $post_id!=''){
									//If it does, then update it's categories
									$post_args = array('ID' => $post_id, 'post_category' => array($catid,$this->vtour_cat));
									$update = wp_update_post($post_args);
									
									if($update){
										$posts_updated++;
										update_post_meta($post_id, $this->permalinkcatkey, $catid);
										//Attach the post thumbnail if it exists just to make sure it is current
										if(file_exists($this->path.$folder.'/thumbnails/'.$files->getFilename().'.jpg'))
											$filename = $this->path.$folder.'/thumbnails/'.$files->getFilename().'.jpg';
										else if($postThumbnailFile = $this->regExpFile('/('.$files->getFilename().')-([0-9\.\-]).jpg/', $this->path.$folder.'/thumbnails/'))
											$filename = $this->path.$folder.'/thumbnails/'.$postThumbnailFile;
										if($filename != ''){	
											$wp_filetype = wp_check_filetype(basename($filename), null );
											$attachment = array(
											'post_mime_type' => $wp_filetype['type'],
											'post_title' => preg_replace('/\.[^.]+$/', '', basename($filename)),
											'post_content' => '',
											'post_status' => 'inherit'
											);
											$meta_key = $this->thumbnail_meta_key;
											$existing_attachment_id = $wpdb->get_var( $wpdb->prepare("SELECT meta_value FROM $wpdb->postmeta WHERE post_id = %d and meta_key = %s", $post_id, $meta_key) );
											$existing_image = wp_get_attachment_image_src($existing_attachment_id, 'full');
											$existing_image_data = getimagesize($existing_image[0]);
											$new_image_data = getimagesize($filename);
											//Check to see if images are different
											if($existing_image_data['bits'] != $new_image_data['bits']){
												$attach_id = wp_insert_attachment( $attachment, $filename, $post_id );
												wp_delete_attachment($existing_attachment_id);
												$attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
												wp_update_attachment_metadata( $attach_id,  $attach_data );
												update_post_meta($post_id, $this->thumbnail_meta_key, $attach_id);
												$post_image_args = array('ID' => $attach_id, 'post_title' => $posttitle);
												wp_update_post($post_image_args);
											}
										}//close if file exists
									}
									else{
										array_push($posts_not_updated, $posttitle);
										$errors = true;
									}
								}// close if post exists
								else {
									//If it doesn't, go ahead and create it
									$post_changes = true;
									//setup new post array
									$new_post = array(
									'post_title' => $posttitle,
									'post_content' => '',
									'post_status' => 'draft',
									'post_category' => array($catid,$this->vtour_cat),
									'post_name' => $postname,
									'post_author' => $user_ID,
									'post_type' => 'post');
									//Add the new post and get the ID
									$postID = wp_insert_post($new_post, false);
									
									if($postID){
										$posts_added++;
										add_post_meta($post_id, $this->permalinkcatkey, $catid, true);
										//Attach the post thumbnail if it exists
										if(file_exists($this->path.$folder.'/thumbnails/'.$files->getFilename().'.jpg'))
											$filename = $this->path.$folder.'/thumbnails/'.$files->getFilename().'.jpg';
										else if($postThumbnailFile = $this->regExpFile('/('.$files->getFilename().')-([0-9\.\-]).jpg/', $this->path.$folder.'/thumbnails/'))
											$filename = $this->path.$folder.'/thumbnails/'.$postThumbnailFile;
										if($filename != ''){	
											$filename = $this->path.$folder.'/thumbnails/'.$files->getFilename().'.jpg';
											$wp_filetype = wp_check_filetype(basename($filename), null );
											$attachment = array(
											'post_mime_type' => $wp_filetype['type'],
											'post_title' => preg_replace('/\.[^.]+$/', '', basename($filename)),
											'post_content' => '',
											'post_status' => 'inherit'
											);
											$attach_id = wp_insert_attachment( $attachment, $filename, $postID );
											$attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
											wp_update_attachment_metadata( $attach_id,  $attach_data );
											add_post_meta($postID, $this->thumbnail_meta_key, $attach_id);
											$post_image_args = array('ID' => $attach_id, 'post_title' => $posttitle);
											wp_update_post($post_image_args);
										}// file exists
									}
									else{
										array_push($posts_not_added, $posttitle);
										$errors = true;
									}
								}// else post exists
							}// for each
						}// if name = high						
					}//foreach
					
					//get the thumbnail (big-portfolio.jpg) and set it as the featured image for the parent page
					if(file_exists($this->path.$folder.'/big-portfolio.jpg'))
						$filename = $this->path.$folder.'/big-portfolio.jpg';
					else if($postThumbnailFile = $this->regExpFile('/(big-portfolio)-([0-9\.\-]).jpg/', $this->path.$folder))
						$filename = $this->path.$folder.'/'.$postThumbnailFile;
					if($filename != ''){	
						$wp_filetype = wp_check_filetype(basename($filename), null );
						$attachment = array(
						'post_mime_type' => $wp_filetype['type'],
						'post_title' => preg_replace('/\.[^.]+$/', '', basename($filename)),
						'post_content' => '',
						'post_status' => 'inherit'
						);
						$meta_key = $this->thumbnail_meta_key;
						$existing_attachment_id = $wpdb->get_var( $wpdb->prepare("SELECT meta_value FROM $wpdb->postmeta WHERE post_id = %d and meta_key = %s", $pageID, $meta_key) );
						$existing_featured = wp_get_attachment_image_src($existing_attachment_id, 'full');
						$existing_featured_data = getimagesize($existing_featured[0]);
						$new_featured_data = getimagesize($filename);
						if($existing_featured_data['bits'] != $new_featured_data['bits']){
							$attach_id = wp_insert_attachment( $attachment, $filename, $existing_page_id );
							wp_delete_attachment($existing_attachment_id);
							$attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
							wp_update_attachment_metadata( $attach_id,  $attach_data );
							update_post_meta($existing_page_id, $this->thumbnail_meta_key, $attach_id);
							$post_image_args = array('ID' => $attach_id, 'post_title' => $new_page_title);
							$update = wp_update_post($post_image_args);
						}
					}// if name = thumb
					
					//Add SEO keywords & Description
					//VARS
					$ht = "vr";
					$new_page_name = $folder;
					$new_page_name = str_replace("-", "%", $new_page_name);
					$new_page_name .= "-001";
					
					
					//Check for existing Tour already set
					$existing_header_vr_id = get_post_meta($existing_page_id, $this->header_vr_ID_meta_key, true);					
					if(empty($existing_header_vr_id)){
						$first_tour_id = $wpdb->get_var($wpdb->prepare("select ID from $wpdb->posts where post_name LIKE %s", $new_page_name));
						update_post_meta($existing_page_id, $this->header_custom_meta_key, $ht);
						update_post_meta($existing_page_id, $this->header_vr_ID_meta_key, $first_tour_id);
					}
					
					//check for SEO Description already set
					$SEO_description = get_post_meta($existing_page_id, $this->seo_description_meta_key, true);
					if(empty($SEO_description))
						update_post_meta($existing_page_id, $this->seo_description_meta_key, $seo_description);
						
					//check for SEO Keywords already set
					$SEO_keywords = get_post_meta($existing_page_id, $this->seo_keywords_meta_key, true);
					if(empty($SEO_keywords))
						update_post_meta($existing_page_id, $this->seo_keywords_meta_key, $seo_keywords);
					
					//check for SEO Title already set
					$SEO_title = get_post_meta($existing_page_id, $this->seo_title_meta_key, true);
					if(empty($SEO_title))
						update_post_meta($existing_page_id, $this->seo_title_meta_key, $google_page_title);
					
					//check for SEO Title Attribute already set
					$SEO_title_attribute = get_post_meta($existing_page_id, $this->seo_title_attribute_meta_key, true);
					if(empty($SEO_title_attribute))
						add_post_meta($existing_page_id, $this->seo_title_attribute_meta_key, $seo_keywords);
					
					//Check for SEO Menu Label already set
					$SEO_menu_label = get_post_meta($existing_page_id, $this->seo_menu_label_meta_key, true);
					if(empty($SEO_menu_label))
						add_post_meta($existing_page_id, $this->seo_menu_label_meta_key, $seo_keywords);
					
					//Set Virtual Tour Template
					update_post_meta($existing_page_id, $this->page_template_meta_key, $this->page_template);
				}//If Page Exists
				else{
					//echo "PAGE DOESN't EXIST";
					//New Page object
					$new_page = array(
					'post_title' => $new_page_title,
					'post_content' => '',
					'post_status' => 'draft',
					'post_author' => $user_ID,
					'post_type' => 'page',
					'post_name' => $page_name,
					'post_parent' => $this->parent_page,
					'tags_input' => $seo_keywords);
					//Create new page
					$pageID = wp_insert_post($new_page, false );
					//$pageID=1;
					//Check if page was created successfuly
					if($pageID){
						$pages_created++;
						array_push($page_titles_add, $new_page_title);
						//Get the file listing for each VR Folder to create the posts and set the page thumbnail.
						foreach (new DirectoryIterator($this->path.$folder) as $fileInfo) {
							if($fileInfo->isDot()) continue;
							$name = $fileInfo->getFilename();
							//get one of the subfolders and iterate through it to create the posts
							if($name=="high"){
								foreach (new DirectoryIterator($this->path.$folder.'/high') as $files) {
									if($files->isDot()) continue;
									$posttitle = $new_page_title;
									$filenumber = str_replace(array("image_","image-"),"",$files->getFilename());
									$posttitle .= " ".$filenumber;
									$postname = $tour_name."-".$filenumber;
									//setup new post array
									$new_post = array(
									'post_title' => $posttitle,
									'post_content' => '',
									'post_status' => 'draft',
									'post_name' => $postname,
									'post_category' => array($catid,$this->vtour_cat),
									'post_author' => $user_ID,
									'post_type' => 'post');
									//Add the new post and get the ID
									$postID = wp_insert_post($new_post, false);
									if($postID){
										$posts_added++;
										add_post_meta($postID, $this->permalinkcatkey, $catid, true);//Add the meta field for the Hikari Category Permalink plugin
										//Attach the post thumbnail if it exists
										if(file_exists($this->path.$folder.'/thumbnails/'.$files->getFilename().'.jpg')){
											$filename = $this->path.$folder.'/thumbnails/'.$files->getFilename().'.jpg';
											$wp_filetype = wp_check_filetype(basename($filename), null );
											$attachment = array(
											'post_mime_type' => $wp_filetype['type'],
											'post_title' => preg_replace('/\.[^.]+$/', '', basename($filename)),
											'post_content' => '',
											'post_status' => 'inherit'
											);
											$attach_id = wp_insert_attachment( $attachment, $filename, $postID );
											$attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
											wp_update_attachment_metadata( $attach_id,  $attach_data );
											add_post_meta($postID, $this->thumbnail_meta_key, $attach_id);
											$post_image_args = array('ID' => $attach_id, 'post_title' => $posttitle);
											$update = wp_update_post($post_image_args);
										}//If Thumbnails
									}//if postID for new post
								}//Foreach
							}//If name=high
						}//foreach
						
						//get the thumbnail (big-portfolio.jpg) and set it as the featured image for the parent page
						if(file_exists($this->path.$folder.'/big-portfolio.jpg'))
							$filename = $this->path.$folder.'/big-portfolio.jpg';
						else if($postThumbnailFile = $this->regExpFile('/(big-portfolio)-([0-9\.\-]).jpg/', $this->path.$folder))
							$filename = $this->path.$folder.'/'.$postThumbnailFile;
						if($filename != ''){	
							$wp_filetype = wp_check_filetype(basename($filename), null );
							$attachment = array(
							'post_mime_type' => $wp_filetype['type'],
							'post_title' => preg_replace('/\.[^.]+$/', '', basename($filename)),
							'post_content' => '',
							'post_status' => 'inherit'
							);
							$attach_id = wp_insert_attachment( $attachment, $filename, $pageID );
							$attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
							wp_update_attachment_metadata( $attach_id,  $attach_data );
							add_post_meta($pageID, $this->thumbnail_meta_key, $attach_id);
							$post_image_args = array('ID' => $attach_id, 'post_title' => $new_page_title);
							$update = wp_update_post($post_image_args);
						}//if Thumb
						
						//Add SEO keywords & Description
						$first_tour = get_page_by_title($new_page_title." 001", "OBJECT", 'post');
						add_post_meta($pageID, $this->seo_description_meta_key, $seo_description);
						add_post_meta($pageID, $this->seo_keywords_meta_key, $seo_keywords);
						add_post_meta($pageID, $this->seo_title_meta_key, $google_page_title);
						add_post_meta($pageID, $this->seo_title_attribute_meta_key, $seo_keywords);
						add_post_meta($pageID, $this->seo_menu_label_meta_key, $seo_keywords);
						add_post_meta($pageID, $this->header_custom_meta_key, 'vr');
						add_post_meta($pageID, $this->header_vr_ID_meta_key, $first_tour->ID);
						add_post_meta($pageID, $this->page_template_meta_key, $this->page_template);
					}//If PageID
					else {
						$errors = true;
					}
				}// foreach
			}// else page exists
		}
		$results = array($pages_created,$pages_updated,$page_titles_add,$page_titles_update,$posts_added,$posts_updated, $errors);
		return $results;
	}
	
	function unpublish_posts($folders){
		global $wpdb;
		$errors = false;
		$posts_unpublished = 0;
		$pages_unpublished = 0;
		$page_titles = array();
		if(is_array($folders)&&count($folders)>0){
			foreach($folders as $folder){
				//get the main category and get cat ID;
				$category_name = ucwords(str_replace(array('-','_')," ",$folder));
				$catid = get_cat_ID($category_name);
				//get post object for existing page
				//Page Title
				$page_title = ucwords(str_replace(array('-','_')," ",$folder));
				$page_name = str_replace('-',"_",$folder);
				$standardized_page_name = str_replace("-", "%", $folder);
				$pageID = $wpdb->get_var( $wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE post_name LIKE %s and post_status not in('trash', 'inherit', 'auto-draft')", $standardized_page_name) );
				//Check if page exists
				if($pageID){
					//if it does, unpublish it.
					$page_args= array('ID' => $pageID, 'post_status' => 'draft');
					$success = wp_update_post($page_args);
					if($success) {
						//remove the attachments associated with the page
						$attachment_id = $wpdb->get_var($wpdb->prepare("SELECT ID from $wpdb->posts where post_parent = %d and post_type = %s", $pageID, 'attachment'));
						if($attachment_id){
							//$wpdb->query($wpdb->prepare("DELETE from $wpdb->posts where ID = %d and post_type = %s", $attachment_id, 'attachment'));
							//$wpdb->query($wpdb->prepare("DELETE from $wpdb->postmeta where post_id = %d and post_type = %s", $attachment_id));
							wp_delete_attachment($attachment_id);
						}
						$pages_unpublished++;
						array_push($page_titles, $page_title);
					}
					else {
						$errors = true;
					}
					//get all posts that are assigned to the parent category
					$args = array('category' => $catid, 'numberposts' => -1, 'post_status' => 'publish,draft');
					$posts = get_posts($args);
					if(is_array($posts)&&count($posts)>0){
						foreach($posts as $post){
							$post_args = array('ID' => $post->ID, 'post_status' => 'draft', 'post_category' => array($catid,$this->vtour_cat,$this->vtour_up_cat));
							$post_success = wp_update_post($post_args);
							if($post_success){
								$attachment_id = $wpdb->get_var($wpdb->prepare("SELECT ID from $wpdb->posts where post_parent = %d and post_type = %s", $post->ID, 'attachment'));
								if($attachment_id){
									//$wpdb->query($wpdb->prepare("DELETE from $wpdb->posts where ID = %d and post_type = %s", $attachment_id, 'attachment'));
									//$wpdb->query($wpdb->prepare("DELETE from $wpdb->postmeta where post_id = %d and post_type = %s", $attachment_id));
									wp_delete_attachment($attachment_id);
								}
								$posts_unpublished++;
							}
							else {
								$errors = true;
							}
						}//foreach
					}//is_array
				}// if pageID
				else {
					$errors = true;
				}
			}//foreach
		}//is_array
		$results = array($pages_unpublished, $posts_unpublished, $page_titles, $errors);
		return $results;
	}
	
	function admin_menu() {
		$file = __FILE__;		
		add_menu_page('Directory Sync', 'Directory Sync', 8, 'directory-sync', array($this, 'er_admin_options'));
	}
	
	function array_diff_values($tab1, $tab2){
	    $result = array();
    	foreach($tab1 as $values){
    		//echo $values."<br />";
    		if(! in_array($values, $tab2))
    			$result[] = $values;
    	}	
   		return $result;
   	}
   	
   	function multidimensional_array_diff($a1,$a2){
		$r = array();	 
		foreach ($a2 as $key => $second){
			foreach ($a1 as $key => $first){
				if (isset($a2[$key])){
					foreach ($first as $first_value){
						foreach ($second as $second_value){
							if ($first_value == $second_value){
								$true = true;
								break;
							}
						}
						if (!isset($true)){
							$r[$key][] = $first_value;
						}
						unset($true);
					}
				}
				else{
					$r[$key] = $first;
				}
			}
		}
		return $r;
	} 
	
}//END CLASS

//Instanciate Class Object
$erds = new Directory_sync();

//Actions
add_action('init', array($erds, 'init'),10);
add_action('admin_menu', array($erds, 'admin_menu'));
	
//Filters
?>