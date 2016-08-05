//Hold your horses comment plugin JS
//helper funciton for the help icons.
jQuery(document).ready(function() {	
	jQuery('.helpme').click(function(){
		jQuery('.helpme span').hide('fast');
		s = jQuery(this).find('span');
		if(s.css('display')=='none'){
			o = jQuery(this).offset();
			h = s.height()+15;
			nh = o.top-h;
			nl = o.left+15;
			s.show('normal').offset({top:nh,left:nl});
		}
	});
	if(!jQuery('#_HYHEnableQuestion').attr('checked')&&!jQuery('#_HYHLimit').attr('checked'))
		jQuery('#_HYHEnableQuestion').attr("disabled", "disabled");
	jQuery('#_HYHLimit').click(function(){
		if(jQuery(this).attr('checked')){
			jQuery('#_HYHEnableQuestion').removeAttr("disabled");
		}
		else{
			jQuery('#_HYHEnableQuestion').attr("disabled", "disabled");
			jQuery('#_HYHEnableQuestion').removeAttr("checked");
		}
	});
});