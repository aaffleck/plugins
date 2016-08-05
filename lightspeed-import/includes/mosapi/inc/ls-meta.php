<?php global $item; ?>

<div class="product_meta" style="width:auto !important;margin-top:15px;">

	<?php do_action( 'woocommerce_product_meta_start' ); ?>

	
	<?php //echo $item->get_categories( ', ', '<span class="posted_in">' . _n( 'Category:', 'Categories:', sizeof( get_the_terms( $post->ID, 'product_cat' ) ), 'woocommerce' ) . ' ', '.</span>' ); ?>

	<?php
	$tags = $item->ItemMatrix->Tags->tag;
	if(!empty($tags)&&count($tags)>0){
		echo '<span class="tagged_as">'._n( 'Tag: ', 'Tags: ', sizeof( $tags ) );
		foreach($tags as $tag){
			echo '<a href="http://borealpaddle.lightspeedwebstore.com/search/results?q='.$tag.'">'.$tag.'</a>, ';
		}
		echo '</span>';
	}
	?>

	<?php do_action( 'woocommerce_product_meta_end' ); ?>

</div>