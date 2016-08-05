<?php global $product; ?>

<?php echo '<p class="price">$'. number_format((float)$product[0]['Prices'][0]['ItemPrice'][0]['amount'], 2) .'</p>'; ?>