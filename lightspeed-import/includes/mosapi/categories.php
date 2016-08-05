<?php

// include our handy API wrapper that makes it easy to call the API, it also depends on MOScURL to make the cURL call
require_once("MOSAPICall.class.php");

// setup our credentials
// this key is to our demo data and allows full access to just /Account/797/Item control
$mosapi = new MOSAPICall("992e498dfa5ab5245f5bd5afee4ee1ce6ac6e0a1ee7d11e36480694a9b5282e7","83442");

// get the itemID out of the response XML
// $item_id = $item_response_xml->itemID;
// $matrix_id = $item_response_xml->itemMatrixID;
// $tag_id = $item_response_xml->tagID;

//$emitter = "https://api.merchantos.com/API/Account/$account_id/ItemMatrix?limit=200\&load_relations=all\&tag=beyondyoga";
$emitter = 'https://api.merchantos.com/API/Account/83442/ItemMatrix?limit=500\&load_relations=["ItemECommerce","Tags","Images"]';
//$xml_query_string = "nodeDepth=0&categoryID=";

// make another API call to Account.Item, this time with Update method and our changed Item XML.
//$get_items = $mosapi->makeAPICall("Account.Item","Read",$item_id,null,$emitter, $xml_query_string);
//$get_matrix = $mosapi->makeAPICall("Account.ItemMatrix","Read",$matrix_id,null);
$matrixs = $mosapi->makeAPICall("Account.ItemMatrix","Read",null,null,$emitter);
//$get_tags = $mosapi->makeAPICall("Account.Tag","Read",$tag_id,null);

//print_r($get_items);
//print_r($matrixs);
//print_r($get_tags);


// output everything

function api_call() {
	global $matrixs, $mosapi;
	
	foreach($matrixs as $item) { 
		
 		print_r($item);
// 		$id = $item->itemMatrixID;
 		$name =	$item->description;
// 		$tax = $item->tax;
// 		$itemECommerceID = $item->ItemECommerce->itemECommerceID;
// 		$longDescription = $item->ItemECommerce->longDescription;
// 		$shortDescription = $item->ItemECommerce->shortDescription;
// 		$weight = $item->ItemECommerce->weight;
// 		$width = $item->ItemECommerce->width;
// 		$height = $item->ItemECommerce->height;
// 		$length = $item->ItemECommerce->length;
		
// 		$imageID_1 = $item->Images->Image[0]->imageID;
// 		$description_1 = $item->Images->Image[0]->description;
// 		$filename_1 = $item->Images->Image[0]->filename;
// 		$ordering_1 = $item->Images->Image[0]->ordering;
// 		$publicID_1 = $item->Images->Image[0]->publicID;
// 		$baseImageURL_1 = $item->Images->Image[0]->baseImageURL;
// 		$itemID_1 = $item->Images->Image[0]->itemID;
// 		$itemMatrixID_1 = $item->Images->Image[0]->itemMatrixID;

// 		$imageID_2 = $item->Images->Image[1]->imageID;
// 		$description_2 = $item->Images->Image[1]->description;
// 		$filename_2 = $item->Images->Image[1]->filename;
// 		$ordering_2 = $item->Images->Image[1]->ordering;
// 		$publicID_2 = $item->Images->Image[1]->publicID;
// 		$baseImageURL_2 = $item->Images->Image[1]->baseImageURL;
// 		$itemID_2 = $item->Images->Image[1]->itemID;
// 		$itemMatrixID_2 = $item->Images->Image[1]->itemMatrixID;

// 		$imageID_3 = $item->Images->Image[2]->imageID;
// 		$description_3 = $item->Images->Image[2]->description;
// 		$filename_3 = $item->Images->Image[2]->filename;
// 		$ordering_3 = $item->Images->Image[2]->ordering;
// 		$publicID_3 = $item->Images->Image[2]->publicID;
// 		$baseImageURL_3 = $item->Images->Image[2]->baseImageURL;
// 		$itemID_3 = $item->Images->Image[2]->itemID;
// 		$itemMatrixID_3 = $item->Images->Image[2]->itemMatrixID;
		
 		$tags = $item->Tags->tag;

 if($tags=='new'){
// 		echo '<h1>'. $id .':</h1>';
 		echo '<h2>'. $name .':</h2>';
// 		echo '<strong>Long Description</stong>'. $longDescription;
// 		echo '<strong>Short Description</stong>'. $shortDescription;

// 		echo 'Image 1: <img src="'. $baseImageURL_1 .'c_pad,h_400,q_75,w_400/'. $publicID_1 .'"/>';
// 		echo 'Image 2: <img src="'. $baseImageURL_2 .'c_pad,h_400,q_75,w_400/'. $publicID_2 .'"/>';
// 		echo 'Image 3: <img src="'. $baseImageURL_3 .'c_pad,h_400,q_75,w_400/'. $publicID_3 .'"/>';
		



 		echo '<hr>';
 }
		
	 }

}

add_shortcode( 'web_store', 'api_call' );

?>