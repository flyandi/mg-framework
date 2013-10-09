/**
  * (mg)framework Interface Script
  * Copyright (c) 2012 eikonlexis LLC
  * @About: Script for Framework Interface
  */
  
/** Constants */

/** Initialize */
$(function(){
	// initialize manager
	var manager = new mgCreateManager({
		target:'.-content',
		width: 950,
		modules: typeof(bnManagerModules)=="object"?bnManagerModules:false
	});
	// finalize manager
	$(".-content").addClass("manager -corner-all");
});