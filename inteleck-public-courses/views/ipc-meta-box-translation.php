<div id="ipc-meta-box-translation">
	<label for="_IPCName_a" id="_IPCName_aLabel"><?php _e("Title", '$this->plugin_domain' ); ?>: <input type="text" name="_IPCName_a" id="_IPCName_a" value="<?php echo $_IPCName_a; ?>" /></label>
	<label for="_IPCDuration_a" id="_IPCDuration_aLabel"><?php _e("Duration", '$this->plugin_domain' ); ?>: <input type="text" name="_IPCDuration_a" id="_IPCDuration_a" value="<?php echo $_IPCDuration_a; ?>" /></label>
	<label for="_IPCCost_a" id="_IPCCost_aLabel"><?php _e("Cost", '$this->plugin_domain' ); ?>: <input type="text" name="_IPCCost_a" id="_IPCCost_a" value="<?php echo $_IPCCost_a; ?>" /></label><br />
	<label for="_IPCContent_a" id="_IPCContent_aLabel"><?php _e("Description", '$this->plugin_domain' ); ?>:</label></br />
	<?php
	$content = $_IPCContent_a;
	$ed_id = "_IPCContent_a";
	$settings = array("wpautop"=>false);
	wp_editor($content, $ed_id, $settings);
	?>
	<label for="_IPCBrief_a" id="_IPCBrief_aLabel"><?php _e("Brief", '$this->plugin_domain' ); ?>:</label></br />
	<?php
	$content = $_IPCBrief_a;
	$ed_id = "_IPCBrief_a";
	$settings = array("wpautop"=>false);
	wp_editor($content, $ed_id, $settings);
	?>
</div>