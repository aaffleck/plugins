<?php global $post, $product, $woocommerce; ?>

<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php
$prodImgs = array();
$i=0;
foreach($product as $prod){
	//$prod = $prod[0];
	//print_r($prod);
	$images = $prod['Images'][0]['Image'];
	if(!empty($images) && count($images) > 0):
		
		foreach($images as $key => $image):
			$image_base = $image['baseImageURL'];
			$image_id = $image['publicID'];
			$image_url = $image_base .'c_pad,h_400,q_75,w_400/'. $image_id;
			$thumb_url = $image_base .'c_fill,h_220,w_220/'. $image_id;

			$prodImgs[$i]['slideId'] = '-0-';
			$prodImgs[$i]['imgSrc'] = $image_url;
			$prodImgs[$i]['imgThumbSrc'] = $thumb_url;
			$i++;
		endforeach;
	endif;
}

$html = '';	
if(!empty($prodImgs)):
	$i = 0; foreach($prodImgs as $imgId => $imgData):
		if($i == 0):
			$html .= '<img src="'.$imgData['imgSrc'].'" data-'.$this->slug.'="'.$imgData['slideId'].'" class="'.$this->slug.'_image" data-rsTmb="'.$imgData['imgThumbSrc'].'">';
		else:
			$html .= '<a href="'.$imgData['imgSrc'].'" data-'.$this->slug.'="'.$imgData['slideId'].'" class="'.$this->slug.'_image rsImg" data-rsTmb="'.$imgData['imgThumbSrc'].'"></a>';
		endif;
	$i++; endforeach;
endif;
?>
<div id="<?php echo $this->slug.'_images_wrap'; ?>">
	<div id="<?php echo $this->slug.'_images'; ?>" class="royalSlider rsMinW">
	
		<?php echo $html; ?>
	
	</div>
</div>