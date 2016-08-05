jQuery(document).ready(function(){

	
	
		
	//set default tab
  var vtab=0;
//remove the activated tab
  var areatext = $("ul.resp-tabs-list").find(".resp-tab-active").first().attr('aria-controls');
//activate the target tab
  $("#horizontalTab h2[aria-controls='tab_item-" + vtab + "']").addClass("resp-tab-active");
  $("#horizontalTab .resp-tabs-container div[aria-labelledby='tab_item-" + vtab + "']").addClass("resp-tab-content-active").attr("style", "display: block;");
});