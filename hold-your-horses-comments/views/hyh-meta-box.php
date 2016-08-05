<div id="hyh-meta-box">
	<label for="_HYHLimit" id="_HYHLimitLabel"><input type="checkbox" name="_HYHLimit" id="_HYHLimit"<?php echo $enabled; ?> /> Limit commenting by requiring the reader to wait the average reading time of your article (<span id="update-article-reading-time"><?php echo $avg_time; ?></span>)?</label>
	<label for="_HYHEnableQuestion" id="_HYHEnableQuestionLabel"><input type="checkbox" name="_HYHEnableQuestion" id="_HYHEnableQuestion"<?php echo $question_enabled; ?> /> Allow the reader to bypass the average reading time of your article by answering a question?</label>
	<label for="_HYHQuestion" id="_HYHQuestionLabel"><?php _e("Question", '$this->plugin_domain' ); ?>: <input type="text" name="_HYHQuestion" id="_HYHQuestion" value="<?php echo $_HYHQuestion; ?>" /></label><br />
	<label for="_HYHAnswer" id="_HYHAnswerLabel"><?php _e("Answer", '$this->plugin_domain' ); ?>: <input type="text" name="_HYHAnswer" id="_HYHAnswer" value="<?php echo $_HYHAnswer; ?>" /></label><br />
	<label for="_HYHHint" id="_HYHHintLabel"><?php _e("Hint", '$this->plugin_domain' ); ?>: <input type="text" name="_HYHHint" id="_HYHHint" value="<?php echo $_HYHHint; ?>" /></label>
</div>