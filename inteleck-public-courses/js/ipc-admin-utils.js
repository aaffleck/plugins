jQuery(document).ready(function() {
	
	jQuery('#add-new-date').click(function(e){
		var i = jQuery("#_IPCDateCount").val();
		i++;
		e.preventDefault();
		
		jQuery("#dates-container").append(
			'<div id="dla'+i+'" class="course-dates-block">'+
				'<label for="_IPCDate'+i+'" class="_IPCDateLabel">Date: <input type="text" name="_IPCDate'+i+'" id="_IPCDate'+i+'" class="dateinput" value="" /></label>'+
				'<label for="_IPCLocation'+i+'" class="_IPCLocationLabel">Location: <input type="text" name="_IPCLocation'+i+'" id="_IPCLocation'+i+'" value="" /></label>'+
				'<label for="_IPCAudience'+i+'" class="_IPCAudienceLabel">Audience: <input type="text" name="_IPCAudience'+i+'" id="_IPCAudience'+i+'" value="" /></label>'+
				'<label for="_IPCLanguage'+i+'" class="_IPCLanguageLabel">Language: <select name="_IPCLanguage'+i+'" id="_IPCLanguage'+i+'"><option value="">Choose...</option><option value="English">English</option><option value="Arabic">Arabic</option></select></label>'+
				'<button id="delete-date" class="delete-date-entry"><img src="/wp-content/plugins/inteleck-public-courses/images/minus.png" alt="delete" title="delete" /></button>'+
				'<div class="clear"></div>'+
			'</div>'
		);
		
		jQuery(".dateinput").each(function(index, element){			
			if(typeof jQuery(element).attr("id") !== 'undefined' && jQuery(element).attr("id").indexOf("Date")+1){
				jQuery(element).data('dateinput', null);
				jQuery(element).dateinput({ format: 'yyyy-mm-dd' });
			}
		});
		
		jQuery("#_IPCDateCount").val(i);
		
	});
	
	jQuery(".dateinput").dateinput({
		format: 'yyyy-mm-dd'
	});
	
	jQuery(".delete-date-entry").click(function(e){
		e.preventDefault();
		var entry = jQuery(this).parent().attr("id");
		var postID = jQuery("#post_ID").val();
		var id = entry.substr(3,entry.length);
		var answer = confirm("Are you sure you want to delete this date?")
		if (answer){
			jQuery.getJSON('/wp-content/plugins/inteleck-public-courses/views/delete-date.php', {ID:id, post_id:postID}, function(success){
				if(success){
					jQuery("#"+entry).remove();
					jQuery("#_IPCDateCount").val(jQuery("#_IPCDateCount").val()-1);
				}			
			});
		}
	});
});