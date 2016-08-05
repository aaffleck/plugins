<?php
	require('../../../wp-config.php');
	$pid = $_POST['postID'];
	$question = get_post_meta($pid, '_HYHQuestion', true);
	$answer = get_post_meta($pid, '_HYHAnswer', true);
	if($question == '' || $answer == ''){
		$options = get_option(Hold_Your_Horses_Comments::OPTIONNAME);
		$answer = $options['defaultAnswer'];
		$answer = ($answer=='')? '3' : $answer;
	}
	echo $answer;
?>