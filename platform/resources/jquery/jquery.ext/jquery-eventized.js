/**
  * (mg)framework Version 5.0
  *	Copyright (c) 1999-2011 eikonlexis LLC. All rights reserved.
  *
  * This program is protected by copyright laws and international treaties.
  * Unauthorized reproduction or distribution of this program, or any 
  * portion thereof, may result in serious civil and criminal penalties.
  *
  * Module 		JQuery Eventized 
  */
  
$(function() {
	
	/** show */
	var _jQueryShow = $.fn.show; $.fn.show = function(s, cb) {
		return $(this).each(function(){
			var obj = $(this);
			_jQueryShow.apply(obj, [s, cb]);
			obj.trigger('onshow');
		});
	}
	
	/** hide */
	var _jQueryHide = $.fn.hide; $.fn.hide = function(s, cb) {
		return $(this).each(function(){
			var obj = $(this);
			_jQueryHide.apply(obj, [s, cb]);
			obj.trigger('onhide');
		});
	}
	
	/** remove */
	var _jQueryRemove = $.fn.remove; $.fn.remove = function(a, b) {
		return $(this).each(function(){
			var obj = $(this);
			_jQueryRemove.apply(obj, [a, b]);
			obj.trigger('onremove');
		});
	}
});