jQuery(function() {
	
    if(jQuery('#the-list tr').length > 1){
		var the_list = new Array();
	
		//Gather the initial order and meta IDs of the fields to use later
		jQuery('#the-list tr').each(function(){
			var id = jQuery(this).attr("id");
			var meta_id = id.substr(5);
			the_list.push(meta_id);
			console.log(meta_id+"\n");
		});
	
		//Make the list Sortable using jQuery UI Sortable
		jQuery( "#the-list" ).sortable({ placeholder: "sortable-placeholder", axis : "y", cursor: "move", delay: 150, forceHelperSize: true, forcePlaceholderSize: true, helper: "clone",
			start: function(ev, ui) {
				//Nothing to start
			},
			update: function( ev, ui ) {
				//console.log(dump(the_list));
				//Get the post ID and initialize the meta arrays
				var post_id = getParameterByName('post');
				var meta_values = new Array();
				var meta_ids = new Array();
			
				//Loop through the list items and populate the meta arrays
				jQuery('#the-list tr').each(function(){
					var id = jQuery(this).attr("id");
					var meta_id = id.substr(5);
					var meta_key = jQuery('#meta\\['+meta_id+'\\]\\[key\\]').val();
					var meta_val = jQuery('#meta\\['+meta_id+'\\]\\[value\\]').map(function(){return jQuery(this).val();}).get();
					meta_values.push(meta_val);
					meta_ids.push(meta_id);
				});
			
				//Send ajax request to update the DB records
				// 2014-03-28, Not performing this at this time. Using Post Update to perform the update.
				/*jQuery.ajax('/wp-content/plugins/moodys-custom-mods/libs/sort-custom-fields.php', { data: {
					'post_id' : post_id,
					'meta_values' : meta_values,
					'meta_ids' : meta_ids
					},
					type: 'POST',
					success: function (data){
						//console.log(data);
					
					}
				});*/
			},
			stop: function(ev, ui) {
			
				// When the sorting has stopped Loop through the list again and update all the attributes for each item so that in the event of a Post Update,
				// the items will be updated accordingly and not set back to their original values.
				var i = 0;
				jQuery('#the-list tr').each(function(){
					jQuery(this).attr("id", "meta-"+the_list[i]);
					jQuery(this).find("input[name*='key']").attr("name", "meta["+the_list[i]+"][key]");
					jQuery(this).find("input[name*='key']").attr("id", "meta["+the_list[i]+"][key]");
					jQuery(this).find("input[name*='deletemeta']").attr("name", "deletemeta["+the_list[i]+"]");
					jQuery(this).find("input[name*='deletemeta']").attr("id", "deletemeta["+the_list[i]+"]");
					jQuery(this).find("input[name*='submit']").attr("name", "meta-"+the_list[i]+"-submit");
					jQuery(this).find("input[name*='submit']").attr("id", "meta-"+the_list[i]+"-submit");
					jQuery(this).find("textarea").attr("name", "meta["+the_list[i]+"][value]");
					i++;
				});
			
				// Create a new array with the updated values to be used on Update again.
				the_list = new Array();
				jQuery('#the-list tr').each(function(){
					var id = jQuery(this).attr("id");
					var meta_id = id.substr(5);
					the_list.push(meta_id);
					console.log(meta_id+"\n");
				});
			}
		});
	}
});


function dump(obj) {
    var out = '';
    for (var i in obj) {
        out += i + ": " + obj[i] + "\n";
    }

    alert(out);

    // or, if you wanted to avoid alerts...

    var pre = document.createElement('pre');
    pre.innerHTML = out;
    document.body.appendChild(pre)
}


function getParameterByName(name) {
    name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
    var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
        results = regex.exec(location.search);
    return results == null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
}