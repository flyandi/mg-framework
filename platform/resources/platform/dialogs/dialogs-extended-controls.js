/**
  * (mg)framework Version 5.0
  *	Copyright (c) 1999-2013 eikonlexis LLC. All rights reserved.
  *
  * This program is protected by copyright laws and international treaties.
  * Unauthorized reproduction or distribution of this program, or any 
  * portion thereof, may result in serious civil and criminal penalties.
  *
  * Module 		Dialog Extended Controls
  */
 
/** (domclone) */
mgDialogFieldControls.domclone = function(controller, item, params, index) {
	var params = $.extend({}, {ref: false, classname: false}, params),
		target = $(sprintf("[storage-name=%s]", params.ref)),
		item =  target.clone().addClass("domcloned").addClass(params.classname?params.classname:"");
	// assign
	target.find("input,select,textarea").each(function(index) {
		var control = $(this);
		item.find(control.prop("tagName")).val($(this).val()).bind({
			change: function() {	
				control.val($(this).val()).trigger("inputchange");
			}
		});
	});
	// return
	return item;
};


