<?php
error_reporting(E_ALL);

ini_set('display_errors', 1);

require_once('../../../../wp-config.php');

$post_id = $_REQUEST['post_id'];
$meta_values = $_REQUEST['meta_values'];
$meta_ids = $_REQUEST['meta_ids'];
//$meta_values = implode(",",$meta_values);
$ids = implode(",",$meta_ids);
//parse_str($meta_fields, $values);

//var_dump($meta_fields);


function moveElement(&$array, $a, $b) {
    $out = array_splice($array, $a, 1);
    array_splice($array, $b, 0, $out);
}

global $wpdb;

$rows = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."postmeta WHERE post_id = $post_id and meta_id IN($ids) order by meta_key,meta_id");

$stay = $rows;
$i=0;

//moveElement($rows, $org_index, $index);

//var_dump($rows);
$j=0;
foreach($rows as $u){
	$wpdb->query("update ".$wpdb->prefix."postmeta set meta_value='".$meta_values[$j][0]."' where meta_id = ".$u->meta_id);
	//echo "update ".$wpdb->prefix."postmeta set meta_value='".$meta_values[$j][0]."' where meta_id = ".$u->meta_id."<br />";
	$j++;
}

//$wpdb->query("update ".$wpdb->prefix."postmeta set meta_value='$meta_val2' where meta_id = $meta_id1");