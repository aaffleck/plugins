<?php
	$options = get_option(Hold_Your_Horses_Comments::OPTIONNAME);
	if(!isset($options) || $options == '')
		update_option(Hold_Your_Horses_Comments::OPTIONNAME, array('defaultText'=>'Average article reading time is ','defaultQuestion'=>'What color do we wear on St. Patrick\'s Day?','defaultAnswer'=>'Green','defaultHint'=>'It\'s the same color as grass','progressBarColor'=>'#003399','displayProgressBar'=>'yes'));
	$options = get_option(Hold_Your_Horses_Comments::OPTIONNAME);
	
	$sel_bar_yes = ($options['displayProgressBar']=='yes') ? 'selected="selected"' : '';
	$sel_bar_no  = ($options['displayProgressBar']=='no')  ? 'selected="selected"' : '';
	?>
	<div class="wrap" id="hold-your-horses-options-wrap">
		<h2><?php _e('Hold Your Horses Comment Settings',$this->plugin_domain); ?></h2>
		<form name="hold-your-horses-options" id="hold-your-horses-options" method="post" action="<?php $_SERVER['PHP_SELF']; ?>">
		
		<div class="helplink">
			<label for="defaultText" id="defaultTextLabel"><?php _e("Default Text", '$this->plugin_domain' ); ?>: <input type="text" name="defaultText" id="defaultText" value="<?php echo stripslashes($options['defaultText']); ?>" /></label>
			<a href="javascript:void(0);" class="helpme">
				<img src="<?php echo $this->pluginUrl; ?>/images/question-mark.gif" />
				<span class="description" class="description"><?php _e("Use as a the default text to display to the reader, e.g. 'Average article reading time is '", '$this->plugin_domain' ); ?></span>
			</a>
		</div>
		
		<div class="helplink">
			<label for="defaultQuestion" id="defaultQuestionLabel"><?php _e("Default question", '$this->plugin_domain' ); ?>: <input type="text" name="defaultQuestion" id="defaultQuestion" value="<?php echo stripslashes($options['defaultQuestion']); ?>" /></label>
			<a href="javascript:void(0);" class="helpme">
				<img src="<?php echo $this->pluginUrl; ?>/images/question-mark.gif" />
				<span class="description"><?php _e("Use for the default challenge question to by pass the wait time, e.g. 'What color do we wear on St. Patrick's Day?'", '$this->plugin_domain' ); ?></span>
			</a>
		</div>
		
		<div class="helplink">
			<label for="defaultAnswer" id="defaultAnswerLabel"><?php _e("Default answer", '$this->plugin_domain' ); ?>: <input type="text" name="defaultAnswer" id="defaultAnswer" value="<?php echo stripslashes($options['defaultAnswer']); ?>" /></label>
			<a href="javascript:void(0);" class="helpme">
				<img src="<?php echo $this->pluginUrl; ?>/images/question-mark.gif" />
				<span class="description"><?php _e("Use for the default answer to the question above, e.g. 'Green'", '$this->plugin_domain' ); ?></span>
			</a>
		</div>
		
		<div class="helplink">
			<label for="defaultHint" id="defaultHintLabel"><?php _e("Default hint", '$this->plugin_domain' ); ?>: <input type="text" name="defaultHint" id="defaultHint" value="<?php echo stripslashes($options['defaultHint']); ?>" /></label>
			<a href="javascript:void(0);" class="helpme">
				<img src="<?php echo $this->pluginUrl; ?>/images/question-mark.gif" />
				<span class="description"><?php _e("Use for the default hint to the question above, e.g. 'It's the same color as grass'", '$this->plugin_domain' ); ?></span>
			</a>
		</div>
		
		<div class="helplink">
			<label for="progressBarColor" id="progressBarColorLabel"><?php _e("Progress bar color", '$this->plugin_domain' ); ?>: <input type="text" name="progressBarColor" id="progressBarColor" value="<?php echo $options['progressBarColor']; ?>" /></label>
			<a href="javascript:void(0);" class="helpme">
				<img src="<?php echo $this->pluginUrl; ?>/images/question-mark.gif" />
				<span class="description"><?php _e("E.g. 'blue', '#006699'", '$this->plugin_domain' ); ?></span>
			</a>
		</div>
		
		<div class="helplink">
			<label for="displayProgressBar" id="displayProgressBarLabel"><?php _e("Display progress bar?", '$this->plugin_domain' ); ?>: <select name="displayProgressBar" id="displayProgressBar">
				<option value="yes" <?php echo $sel_bar_yes; ?> > <?php _e('yes', '$this->plugin_domain' ); ?></option>
				<option value="no"  <?php echo $sel_bar_no; ?> > <?php _e('no',  '$this->plugin_domain' ); ?></option>
				</select>
			</label>
			<a href="javascript:void(0);" class="helpme">
				<img src="<?php echo $this->pluginUrl; ?>/images/question-mark.gif" />
				<span class="description"><?php _e("Whether or not to display the progress bar while waiting.", '$this->plugin_domain' ); ?></span>
			</a>
		</div>
		
		<p class="submit">
			<input type="submit" name="saveHoldYourHorsesOptions" id="saveHoldYourHorsesOptions" class="button-primary" value="<?php _e('Save Settings', '$this->plugin_domain') ?>" />
		</p>
	
		</form>
	</div>