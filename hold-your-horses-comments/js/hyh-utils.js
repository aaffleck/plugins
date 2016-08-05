jQuery(document).ready(function(){
	
	jQuery('.answer-challenge-submit').click(function(){
		var pid = jQuery(this).attr("id").substr(jQuery(this).attr("id").lastIndexOf('-')+1);
		
		jQuery.post('/wp-content/plugins/hold-your-horses-comments/get-answer.php', { postID: pid }, function(answer){
			var lastChar = ".";
			var given_answer = jQuery.trim(jQuery("#challenge-answer-"+pid).val().toLowerCase());
			var the_answer = jQuery.trim(answer.toLowerCase());
			var gal = given_answer.length;
			var tal = the_answer.length;

			//remove trailing period for consitency
			if(given_answer.charAt(gal-1)==lastChar)
				given_answer = given_answer.slice(0,gal-1);

			//remove trailing period for consitency
			if(the_answer.charAt(tal-1)==lastChar)
				the_answer = the_answer.slice(0,tal-1);
			
			if( given_answer == the_answer){
				jQuery("#commentform #submit").removeAttr("disabled").val("Post Comment");
				jQuery("#comment").removeAttr("disabled");
				jQuery("#countdown").countdown('destroy');
				jQuery(".avg_time_border").hide();
				jQuery("#comment-in").text('Great, go ahead and comment!');
				msg = "Correct!";
				color = "#009900";
				jQuery(".hold-your-horses").delay(2000).slideUp();
			}
			else {
				msg = "Sorry. Please try again.";
				color = "#990000";
			}
			jQuery("#msg").html(msg).css({"display" : "block", 'color' : color});
		});
	});
	
});