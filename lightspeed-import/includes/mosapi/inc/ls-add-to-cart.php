<?php
global $product;

$lightspeed_id = '1000000000' + $product[0]['itemMatrixID'];
$slug = sanitize_title($product[0]['description']);
$link = "http://borealpaddle.lightspeedwebstore.com/". $slug ."/dp/". $lightspeed_id ;
?>
<a href="<?php echo $link;?>" class="single_add_to_cart_button button alt">View Item</a>
<div class="clear"></div>