/**
  * (mg)framework Version 5.0
  *	Copyright (c) 1999-2011 eikonlexis LLC. All rights reserved.
  *
  * This program is protected by copyright laws and international treaties.
  * Unauthorized reproduction or distribution of this program, or any 
  * portion thereof, may result in serious civil and criminal penalties.
  *
  * Module 		JQuery Extensions
  */
  
jQuery.fn.disableSelection = function(c){return this.attr("unselectable","on").css({"cursor":c?c:"default","MozUserSelect":"none", "-webkit-user-select":"none", "-o-user-select":"none", "-moz-user-select":"none", "user-select":"none", "-khtml-user-select":"none"})};
jQuery.fn.outerHTML = function () {return $('<div>').append(this.eq(0).clone()).html();};
jQuery.fn.disableTextSelect = function() {return this.each(function(){$(this).disableSelection()})};
jQuery.fn.cssPath = function(){var currentObject = $(this).get(0); cssResult = ""; while (currentObject.parentNode) {if(currentObject.id) {cssResult = currentObject.nodeName + '#' + currentObject.id + " " + cssResult; break;} else if(currentObject.className) {cssResult = currentObject.nodeName + '.' + currentObject.className + " " + cssResult;} else {cssResult = currentObject.nodeName + " " + cssResult;}currentObject = currentObject.parentNode;}return cssResult.toLowerCase();};
jQuery.fn.tagPath=function(){var element=$(this), path=[];while(element.parent().attr('tagName')){path.push(element);element=element.parent();}return path;};
jQuery.fn.rootElement=function(){var element=$(this); while(element.parent().attr('tagName')&&(element.parent().attr('tagName')).toLowerCase()!='body') {element = element.parent();} return element;};

jQuery.fn.reverse=[].reverse;
jQuery.fn.listAttributes = function(prefix) {var list = []; $(this).each(function(){var attributes = []; for(var key in this.attributes) {if(!isNaN(key)) {if(!prefix || this.attributes[key].name.substr(0,prefix.length) == prefix) { attributes.push(this.attributes[key].name); }} }list.push(attributes); }); return (list.length > 1 ? list : list[0]);};
jQuery.fn.mapAttributes = function(prefix) { var maps = []; $(this).each(function() { var map = {}; for(var key in this.attributes) { if(!isNaN(key)) { if(!prefix || this.attributes[key].name.substr(0,prefix.length) == prefix) { map[this.attributes[key].name] = this.attributes[key].value; } } } maps.push(map); }); return (maps.length > 1 ? maps : maps[0]); };
jQuery.fn.disableGroup=function(disable){var grp = $(this).find(":input");if(disable){grp.attr('disabled', 'disabled')}else{grp.removeAttr('disabled')}};
jQuery.fn.padding = function(margin) {margin=margin?margin:false;var marginTop = this.outerHeight(margin) - this.outerHeight(); var marginLeft = this.outerWidth(margin) - this.outerWidth();return {top: marginTop,left: marginLeft}};
jQuery.fn.cssvalue = function(name){return parseInt((this.css(name)).replace("px", ""));};
jQuery.fn.numeric = function(decimal, callback) { decimal = decimal || "."; callback = typeof callback == "function" ? callback : function(){}; this.keypress( function(e) { var key = e.charCode ? e.charCode : e.keyCode ? e.keyCode : 0; if(key == 13 && this.nodeName.toLowerCase() == "input") { return true; } else if(key == 13) { return false; } var allow = false; if((e.ctrlKey && key == 97 /* firefox */) || (e.ctrlKey && key == 65) /* opera */) return true; if((e.ctrlKey && key == 120 /* firefox */) || (e.ctrlKey && key == 88) /* opera */) return true; if((e.ctrlKey && key == 99 /* firefox */) || (e.ctrlKey && key == 67) /* opera */) return true; if((e.ctrlKey && key == 122 /* firefox */) || (e.ctrlKey && key == 90) /* opera */) return true; if((e.ctrlKey && key == 118 /* firefox */) || (e.ctrlKey && key == 86) /* opera */ || (e.shiftKey && key == 45)) return true; if(key < 48 || key > 57) { /* '-' only allowed at start */ if(key == 45 && this.value.length == 0) return true; /* only one decimal separator allowed */ if(key == decimal.charCodeAt(0) && this.value.indexOf(decimal) != -1) { allow = false; } if( key != 8 /* backspace */ && key != 9 /* tab */ && key != 13 /* enter */ && key != 35 /* end */ && key != 36 /* home */ && key != 37 /* left */ && key != 39 /* right */ && key != 46 /* del */ ) { allow = false; } else { if(typeof e.charCode != "undefined") {if(e.keyCode == e.which && e.which != 0) { allow = true; } else if(e.keyCode != 0 && e.charCode == 0 && e.which == 0) { allow = true; } } } if(key == decimal.charCodeAt(0) && this.value.indexOf(decimal) == -1) { allow = true; } } else { allow = true; } return allow; } ) .blur( function() { var val = jQuery(this).val(); if(val != "") { var re = new RegExp("^\\d+$|\\d*" + decimal + "\\d+"); if(!re.exec(val)) { callback.apply(this); } } } ); return this; };
jQuery.fn.moveUp = function() {var before = $(this).prev();$(this).insertBefore(before); return $(this);};
jQuery.fn.moveDown = function() {var after = $(this).next(); $(this).insertAfter(after); return $(this);};
jQuery.fn.removeCSS = function(cssName) {return this.each(function() {var curDom = $(this);jQuery.grep(cssName.split(","),function(cssToBeRemoved) {curDom.css(cssToBeRemoved, '');});return curDom;});};
jQuery.fn.flash = function(settings) {
	var settings = $.extend({width:320, height:240, wmode:'window', scale:'default', src:false, background:'#000000'}, settings);
	if(settings.src!=false) {
		var f = $("<embed></embed>").attr({scale: settings.scale, bgcolor: settings.background, wmode: settings.wmode, type:'application/x-shockwave-flash', pluginspage:'http://www.adobe.com/go/getflashplayer', src:settings.src, width: settings.width, height: settings.height});
		$(this).append(f);
	}
	return this;
};
jQuery.fn.selectall = function() { 
	var obj = this[0];
    if ($.browser.msie) {
        var range = obj.offsetParent.createTextRange();
        range.moveToElementText(obj);
        range.select();
    } else if ($.browser.mozilla || $.browser.opera) {
        var selection = obj.ownerDocument.defaultView.getSelection();
        var range = obj.ownerDocument.createRange();
        range.selectNodeContents(obj);
        selection.removeAllRanges();
        selection.addRange(range);
    } else if ($.browser.safari) {
        var selection = obj.ownerDocument.defaultView.getSelection();
        selection.setBaseAndExtent(obj, 0, obj, 1);
    }
    return this;
}
jQuery.fn.removeUntil=function(c){
	return this.each(function(){
		var element=$(this); 
		while(element.attr('tagName')) {
			if(element.is(c)) {
				element.remove(); return true;
			}
			element = element.parent();		
		}
		return $(this);
	});
};
	
/* snippetvalues */
jQuery.fn.snippetvalues = function(values) {
	var that = $(this);
	$.each(values, function(name, params) {
		var field = that.find(name);
		$.each(params, function(n, v) {
			switch(n) {
				case "html": field.html(v); break;
				case "css": field.css(v); break;
				default: field.attr(n, v); break;
			}
		});
	});
	return $(this);
};


/* expression extensions */
jQuery.extend(jQuery.expr[':'], {
  focus: function(e){try{ return e == document.activeElement; }catch(err){ return false; }}
});

/* Extensions */
$.extend($, {
	inJSON: function(json, key, returnBool, returnIndex){
        var hits = 0, ix = -1;
        $.each(json, function(index, params){
			var b = false;
			switch(typeof(key)) {
				case "object": b = (params[key[0]]==key[1]);b=key[2]?(b!=""?true:false):b; break;
				case "string": b = (params[key]); break;
			}
            if (b){hits+=1;if(returnIndex){ix = index; return;}}
        });
        return returnIndex?ix:(returnBool?(hits!=0):hits);
    }
});

/* monitor functions */
jQuery.fn.monitorheight=function(e){
	var target=$(this), cheight = target.height();
	if(arguments[1]){
		if(arguments[1]!=target.height()&&$(this).is(":focus")){
			var end = e(target.height()); 
		}
	}
	$(this).data("monitorHeightTimer", setTimeout(function(){target.monitorheight(e, cheight)}, 500));
};

/* base64 encoder/decoder */ 
jQuery.base64=(function($){var _PADCHAR="=",_ALPHA="ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/",_VERSION="1.0";function _getbyte64(s,i){var idx=_ALPHA.indexOf(s.charAt(i));if(idx===-1){throw"Cannot decode base64"}return idx}function _decode(s){var pads=0,i,b10,imax=s.length,x=[];s=String(s);if(imax===0){return s}if(imax%4!==0){throw"Cannot decode base64"}if(s.charAt(imax-1)===_PADCHAR){pads=1;if(s.charAt(imax-2)===_PADCHAR){pads=2}imax-=4}for(i=0;i<imax;i+=4){b10=(_getbyte64(s,i)<<18)|(_getbyte64(s,i+1)<<12)|(_getbyte64(s,i+2)<<6)|_getbyte64(s,i+3);x.push(String.fromCharCode(b10>>16,(b10>>8)&255,b10&255))}switch(pads){case 1:b10=(_getbyte64(s,i)<<18)|(_getbyte64(s,i+1)<<12)|(_getbyte64(s,i+2)<<6);x.push(String.fromCharCode(b10>>16,(b10>>8)&255));break;case 2:b10=(_getbyte64(s,i)<<18)|(_getbyte64(s,i+1)<<12);x.push(String.fromCharCode(b10>>16));break}return x.join("")}function _getbyte(s,i){var x=s.charCodeAt(i);if(x>255){throw"INVALID_CHARACTER_ERR: DOM Exception 5"}return x}function _encode(s){if(arguments.length!==1){throw"SyntaxError: exactly one argument required"}s=String(s);var i,b10,x=[],imax=s.length-s.length%3;if(s.length===0){return s}for(i=0;i<imax;i+=3){b10=(_getbyte(s,i)<<16)|(_getbyte(s,i+1)<<8)|_getbyte(s,i+2);x.push(_ALPHA.charAt(b10>>18));x.push(_ALPHA.charAt((b10>>12)&63));x.push(_ALPHA.charAt((b10>>6)&63));x.push(_ALPHA.charAt(b10&63))}switch(s.length-imax){case 1:b10=_getbyte(s,i)<<16;x.push(_ALPHA.charAt(b10>>18)+_ALPHA.charAt((b10>>12)&63)+_PADCHAR+_PADCHAR);break;case 2:b10=(_getbyte(s,i)<<16)|(_getbyte(s,i+1)<<8);x.push(_ALPHA.charAt(b10>>18)+_ALPHA.charAt((b10>>12)&63)+_ALPHA.charAt((b10>>6)&63)+_PADCHAR);break}return x.join("")}return{decode:_decode,encode:_encode,VERSION:_VERSION}}(jQuery));
/* sortby extension */ 
var sortby = function(field, reverse, primer){reverse = (reverse) ? -1 : 1;return function(a,b){a = a[field]; b = b[field]; if (typeof(primer) != 'undefined'){ a = primer(a); b = primer(b);}if (a<b) return reverse * -1; if (a>b) return reverse * 1; return 0;}};


/** (triggerhandlers */
jQuery.fn.triggerHandlers = function(type, data) {
	return this.each(function(){
		//alert($(this).attr("class"));
		$(this).triggerHandler(type, data);
		$(this).children().triggerHandlers(type, data);
	});
}

/** (sortElements) */
jQuery.fn.sortElements = (function(){ 
    var sort = [].sort;
    return function(comparator, getSortable) {
        getSortable = getSortable || function(){return this;};
        var placements = this.map(function(){
            var sortElement = getSortable.call(this),
                parentNode = sortElement.parentNode,
                // Since the element itself will change position, we have
                // to have some way of storing its original position in
                // the DOM. The easiest way is to have a 'flag' node:
                nextSibling = parentNode.insertBefore(
                    document.createTextNode(''),
                    sortElement.nextSibling
                );
 
            return function() {
                if (parentNode === this) {
                    throw new Error(
                        "You can't sort elements if any one is a descendant of another."
                    );
                }
                // Insert before flag:
                parentNode.insertBefore(this, nextSibling);
                // Remove flag:
                parentNode.removeChild(nextSibling);
 
            };
        });
        return sort.call(this, comparator).each(function(i){
            placements[i].call(getSortable.call(this));
        });
    };
})();