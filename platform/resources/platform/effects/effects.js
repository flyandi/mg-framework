/**
  * (mg)framework Version 5.0
  *	Copyright (c) 1999-2011 eikonlexis LLC. All rights reserved.
  *
  * This program is protected by copyright laws and international treaties.
  * Unauthorized reproduction or distribution of this program, or any 
  * portion thereof, may result in serious civil and criminal penalties.
  *
  * Module 		Effects Controller
  */
  
/**
  * mgEffects, manages the effects
  */
var mgEffects = {
	/** supported, checks against the current browser */
	supported: function() {
		return true;
	}
};  

/**
  * (effect) blur
  * @About: blur's any object
  */
mgEffects.blur = function(target) {	
	// make sure this is supported
	
	// make sure this is a real target
	if(typeof(target)=="string") { target = $(target);}
	// initialize css fields
	var cssbp = [], cssbi = [];
	// cycle
	for(var x=0;x<7;x++) {
		// add css position
		for(var y=0;y<4;y++) {cssbp.push(x+"px "+y+"px");}
		for(var y=1;y<4;y++) {cssbp.push(x+"px -"+y+"px");}
		// add css image
		for(var i=0;i<7;i++) {cssbi.push("-moz-element(#-page-blocker)");}
	}
	// assign to target
	target.css({'background-repeat':'no-repeat', 'background-image':cssbi.join(","), 'background-position':cssbp.join(",")});
	// assign events
	target.bind({
		'onshow': function() {
			alert('show');
			$("#-page-blocker").css({opacity: 0.4});
		},
		'onremove': function() {
			$("#-page-blocker").css({opacity: 1});
		}
	});
};