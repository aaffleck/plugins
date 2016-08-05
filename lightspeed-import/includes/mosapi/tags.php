<?php


function lightspeed_tag_cloud($atts){

// include our handy API wrapper that makes it easy to call the API, it also depends on MOScURL to make the cURL call
	require_once("MOSAPICall.class.php");
	
	extract( shortcode_atts( array(
		'orderby'	=> 'name', 
	    'order'		=> 'ASC',
    	'hide_empty'=> false, 
	), $atts ) );
	
	ob_start();

	$mosapi = new MOSAPICall("992e498dfa5ab5245f5bd5afee4ee1ce6ac6e0a1ee7d11e36480694a9b5282e7","83442");

	$emitter = 'https://api.merchantos.com/API/Account/83442/Tag';
	
	$xml_query_string = 'limit=100&orderby='.$orderby.'&orderby_asc=1';
	
	$terms = $mosapi->makeAPICall("Account.Tag","Read",null,null,$emitter, $xml_query_string);

	?>
	<div id="tag_cloud-3" class="widget widget_tag_cloud">
		<h3 class="widget-title">Tags</h3>
		<div class="tagcloud">
			<?php
			foreach ($terms as $term) {
				$slug = sanitize_title($term->name);
				$tag = $term->name;
				?>
				<a href="http://borealpaddle.lightspeedwebstore.com/<?php echo $slug; ?>" target="_black"><?php echo $tag; ?></a>
				<?php
			} ?>
		</div>
	</div>

	<?php
	return ob_get_clean();
}