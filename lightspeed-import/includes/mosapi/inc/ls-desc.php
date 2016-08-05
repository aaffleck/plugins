<?php global $product; ?>

<div id="<?php echo $this->slug; ?>_desc">
	<p><?php echo substr($product[0]['ItemECommerce'][0]['longDescription'],0,strpos((string)$product[0]['ItemECommerce'][0]['longDescription'],'.',strpos((string)$item->ItemMatrix->ItemECommerce->longDescription,'.')+1)+1); ?></p>
	<?php echo $product[0]['ItemECommerce'][0]['shortDescription']; ?>
</div>