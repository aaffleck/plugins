<div id="ipc-meta-box-dates">
	<div id="dates-container">
		<?php
		$n='';
		$dates_array = array();
		$locations_array = array();
		$languages_array = array();
		$audiences_array = array();
		if(!empty($_IPCDateCount)){
			$sql = "select * from $wpdb->postmeta where post_id = $post_id and (meta_key like '_IPCDate%' or meta_key like '_IPCLocation%' or meta_key like '_IPCAudience%' or meta_key like '_IPCLanguage%') order by meta_id";
			$dates = $wpdb->get_results($sql, ARRAY_A);
			if(is_array($dates)&&count($dates)>0){
				foreach($dates as $date){
					if(preg_match('/_IPCDate.*/', $date['meta_key']) && $date['meta_key'] != '_IPCDateCount'){
						$n = substr($date['meta_key'], strpos($date['meta_key'], "Date")+4);
						$language = get_post_meta($post_id, '_IPCLanguage'.$n, true);
					?>
					<div id="dla<?php echo $n; ?>" class="course-dates-block">
					<?php					
						$title = str_replace("_IPC", "", $key);
						?>
						<label for="_IPCDate<?php echo $n; ?>" class="IPCDateLabel"><?php _e('Date', '$this->plugin_domain' ); ?>: 
						<input type="text" name="_IPCDate<?php echo $n; ?>" id="_IPCDate<?php echo $n; ?>" class="dateinput" value="<?php echo $date['meta_value'] ?>" />
						</label>
						<label for="_IPCLocation<?php echo $n; ?>" class="IPCLocationLabel"><?php _e('Location', '$this->plugin_domain' ); ?>: 
						<input type="text" name="_IPCLocation<?php echo $n; ?>" id="_IPCLocation<?php echo $n; ?>" value="<?php echo get_post_meta($post_id, '_IPCLocation'.$n, true); ?>" />
						</label>
						<label for="_IPCAudience<?php echo $n; ?>" class="IPCAudienceLabel"><?php _e('Audience', '$this->plugin_domain' ); ?>: 
						<input type="text" name="_IPCAudience<?php echo $n; ?>" id="_IPCAudience<?php echo $n; ?>" value="<?php echo get_post_meta($post_id, '_IPCAudience'.$n, true); ?>" />
						</label>
						<label for="_IPCLanguage<?php echo $n; ?>" class="IPCLanguageLabel"><?php _e('Language', '$this->plugin_domain' ); ?>: 
						<select name="_IPCLanguage<?php echo $n; ?>" id="_IPCLanguage<?php echo $n; ?>"><option value="">Choose...</option><option value="English"<?php if($language=='English') echo ' selected'; ?>>English</option><option value="Arabic"<?php if($language=='Arabic') echo ' selected'; ?>>Arabic</option></select>
						</label>						
						<button id="delete-date<?php echo $n; ?>" class="delete-date-entry"><img src="<?php echo $this->pluginUrl.'/images/minus.png'; ?>" alt="delete" title="delete" /></button>
						<div class="clear"></div>
					</div>
				<?php
					}
				}
			}
		}
		else {
		?>
		<div id="dla" class="course-dates-block">
			<label for="_IPCDate" class="_IPCDateLabel"><?php _e("Date", '$this->plugin_domain' ); ?>: <input type="text" name="_IPCDate" id="_IPCDate" class="dateinput" value="<?php echo $_IPCDate; ?>" /></label>
			<label for="_IPCLocation" class="_IPCLocationLabel"><?php _e("Location", '$this->plugin_domain' ); ?>: <input type="text" name="_IPCLocation" id="_IPCLocation" value="<?php echo $_IPCLocation; ?>" /></label>
			<label for="_IPCAudience" class="_IPCAudienceLabel"><?php _e("Audience", '$this->plugin_domain' ); ?>: <input type="text" name="_IPCAudience" id="_IPCAudience" value="<?php echo $_IPCAudience; ?>" /></label>
			<label for="_IPCLanguage" class="_IPCLanguageLabel"><?php _e("Language", '$this->plugin_domain' ); ?>: <select name="_IPCLanguage" id="_IPCLanguage"><option value="">Choose...</option><option value="English"<?php if($_IPCLanguage=='English') echo ' selected'; ?>></option><option value="Arabic"<?php if($_IPCLanguage=='Arabic') echo ' selected'; ?>>Arabic</option></select></label>
			<button id="delete-date" class="delete-date-entry"><img src="<?php echo $this->pluginUrl.'/images/minus.png'; ?>" alt="delete" title="delete" /></button>
			<div class="clear"></div>
		</div>
		<?php
		}
		?>
	</div>
	<input type="hidden" name="_IPCDateCount" id="_IPCDateCount" value="<?php echo $_IPCDateCount; ?>" />
	<button id="add-new-date">+ Add New</button>
</div>