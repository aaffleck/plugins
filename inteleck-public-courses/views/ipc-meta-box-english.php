<div id="ipc-meta-box-english">
	<label for="_IPCDuration" id="_IPCDurationLabel"><?php _e("Duration", '$this->plugin_domain' ); ?>: <input type="text" name="_IPCDuration" id="_IPCDuration" value="<?php echo $_IPCDuration; ?>" /></label>
	<label for="_IPCCost" id="IPCCostLabel"><?php _e("Cost", '$this->plugin_domain' ); ?>: <input type="text" name="_IPCCost" id="_IPCCost" value="<?php echo $_IPCCost; ?>" /></label><br />
	<label for="_IPCBrief" id="_IPCBriefLabel"><?php _e("Brief", '$this->plugin_domain' ); ?>:</label></br />
	<?php
	$content = $_IPCBrief;
	$ed_id = "_IPCBrief";
	$settings = array("wpautop"=>false);
	wp_editor($content, $ed_id, $settings);
	?>
</div>