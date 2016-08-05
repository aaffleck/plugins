<div class="tax-table" id="<?php echo $post->post_name; ?>">
	<table cellspacing="0">
	<tr>
		<th>&nbsp;</th>		
	<?php
	$year = get_post_meta($post->ID,'_tax-table-year',true);
	$tax_bracket_year = get_post_meta($post->ID,'_tax-bracket-year',true);
	if(empty($year))
		$year = $this->year;
	if(empty($tax_bracket_year))
		$tax_bracket_year = $this->year;
	for($x=$year-4;$x<=$year;$x++){
		?>
		<th><?php echo $x; ?></th>
		<?php
	}
	if($post->ID==535){
	?>
		<th class="sbl">Small Business Limit</th>
	<?php
	}
	if($post->ID==537 || $post->ID==539 || $post->ID==4539){
	?>
		<th class="sbl">Highest tax Bracket (<?php echo $tax_bracket_year; ?>)</th>
	<?php
	}
	?>
	</tr>
	<?php
	if($post->ID==535){
		?>
		<tr>
			<td class="province-name">Federal Only</td>
		<?php
		for($i=$year-4;$i<=$year;$i++){
			$name = '_federal_only_'.$i;
			$value = get_post_meta($post->ID,$name,true);
			?>
			<td><?php echo $value; ?></td>
			<?php			
		}
		$fsbl = get_post_meta($post->ID,'_federal_small_business_limit',true);
		?>
		<td class="sbl"><?php echo $fsbl; ?></td>
		</tr>
		<tr><td colspan="6">&nbsp;</td></tr>
		<?php
	}
	foreach($this->province_meta_keys as $v){
		$n=$v;
		if($v == '_new_foundland')
			$n = '_newfoundland_and_labrador';
		?>
		<tr>
			<td class="province-name"><?php _e(ucwords(str_replace('_', ' ',substr($n,1))), '$this->plugin_domain' ); ?></td>
		<?php
		$ed = '';
		if($post->ID==539){
			$ed = 'ed_';
		}
		for($i=$year-4;$i<=$year;$i++){
			$name = $v.'_'.$ed.$i;
			$value = get_post_meta($post->ID,$name,true);
			?>
			<td><?php echo $value; ?></td>			
			<?php			
		}
		if($post->ID==535){
			$sbln = $v.'_sbl';
			$sblv = get_post_meta($post->ID,$sbln,true);
		?>
			<td class="sbl"><?php echo $sblv; ?></td>
		<?php
		}
		if($post->ID==537 || $post->ID==539 || $post->ID==4539){
			$htbn = $v.'_htb';
			$htbv = get_post_meta($post->ID,$htbn,true);
		?>
			<td class="sbl"><?php echo $htbv; ?></td>
		<?php
		}
		?>
		</tr>
		<?php
	}
	?>
	</table>
</div>