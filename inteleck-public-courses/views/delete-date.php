<?php
require_once("../../../../wp-config.php");

$go=false;
if(isset($_GET['post_id'])&&$_GET['post_id']!=''){
	$ID = $_GET['ID'];
	$post_id = $_GET['post_id'];
	$go = true;
}

if($go){
	global $wpdb;
	$meta_keys = array('_IPCDate','_IPCLocation','_IPCAudience','_IPCLanguage');
	foreach($meta_keys as $key){
		delete_post_meta($post_id, $key.$ID);
	}
	echo 1;
}
else {
	echo 0;
}
