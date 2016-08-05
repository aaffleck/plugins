<p>&nbsp;</p>
<p><strong>Non - Eligible Dividends</strong></p>
<div class="tax-table" id="<?php echo $post->post_name; ?>">
	<table cellspacing="0">
	<tr>
		<th>&nbsp;</th>		
	<?php
	$year = get_post_meta($post->ID,'_tax-table-year',true);
	if(empty($year))
		$year = $this->year;
	for($x=$year-4;$x<=$year;$x++){
		?>
		<th><?php echo $x; ?></th>
		<?php
	}
	?>
		<th class="sbl">Highest tax Bracket (<?php echo $year-1; ?>)</th>
	</tr>
	<?php
	foreach($this->province_meta_keys as $v){
		$n=$v;
		if($v == '_new_foundland')
			$n = '_newfoundland_and_labrador';
		?>
		<tr>
			<td class="province-name"><?php _e(ucwords(str_replace('_', ' ',substr($n,1))), '$this->plugin_domain' ); ?></td>
		<?php
		$ed = 'ned_';
		for($i=$year-4;$i<=$year;$i++){
			$name = $v.'_'.$ed.$i;
			$value = get_post_meta($post->ID,$name,true);
			?>
			<td><?php echo $value; ?></td>			
			<?php			
		}
		$htbn = $v.'_htb';
		$htbv = get_post_meta($post->ID,$htbn,true);
		?>
			<td class="sbl"><?php echo $htbv; ?></td>
		</tr>
		<?php
	}
	?>
	</table>
</div>