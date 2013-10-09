/**  * (mg)framework Version 5.0  *	Copyright (c) 1999-2011 eikonlexis LLC. All rights reserved.  *  * This program is protected by copyright laws and international treaties.  * Unauthorized reproduction or distribution of this program, or any   * portion thereof, may result in serious civil and criminal penalties.  *  * Module 		Dialog Extensions  */    /**  * (mgCreateImageCropDialog)  * Creates a dialog that accepts an image to crop  */ var mgCreateImageCropDialog = function(url, settings, callback) {	var settings = $.extend({}, {nocache: false, values: false, blocker: true, width: 400, height: 400, centerimage: true, maxzoom: 2, fit: false, lockzoom: true, fixedsize: false, actionbar: true, actions: {fitalign: true, zoom: true}}, settings);	var wh = settings.height + (settings.actionbar?27:0), controlid = "c_"+mgUniqueId(), cropresults = false;	settings.height -= 40;	var wnd = mgCreateButtonDialog({		width: settings.width,		height: wh,		blocker: settings.blocker,		events: {			onsuccess: function() {				if(typeof(callback) == "function") {					callback(cropresults);				}			},		},		buttons: {			'{%ButtonCancel}': false,			'{%ButtonSave}': true,		},		fullarea: true,		fields: [			{type:'html', automargin: false, itemcontext: true, source: function(item) {				item.addClass("input croparea").disableSelection().attr("id", controlid).css({width:settings.width, height: settings.height});				var croparea = jQuery("<div></div>").addClass("cropareaimg").appendTo(item), isfitalign = false, cropimg = false, crop = false, imgwidth, imgheight, zoom = 1;				item.bind({					centerimage: function() {						croparea.css({top: (item.height() - cropimg.height()) / 2, left: (item.width() - cropimg.width()) / 2});					},					action: function(ev, action, noupdate) {						if(!noupdate) item.trigger("disablecrop");						var zl = false;						switch(action) {							case "fitalign":								var p = mgCalcBestWidthHeight(imgwidth, imgheight, item.width(), item.height());								cropimg.css({height:p.height, width: p.width});								zoom = mgCalcZoomLevel(imgwidth, imgheight, p.width, p.height);								isfitalign = true;								break;							case "zoomreset":								zoom = 1;								cropimg.css({width: imgwidth, height: imgheight});								break;							case "zoomin":								zl  = zoom + 0.1;  							case "zoomout":								if(!zl) zl = zoom - 0.1; 								item.trigger("zoom", [zl]);								break;						}						if(!noupdate) {							if(settings.centerimage) {								item.trigger('centerimage');							}							item.trigger("enablecrop");						}					},										zoom: function(ev, zl) {						isfitalign = false;						var p = false;						if(settings.lockzoom&&settings.fixedsize) {							p = imgwidth*zl<settings.fixedsize.width || imgheight*zl<settings.fixedsize.height;						}						if(zl > settings.maxzoom) zl = settings.maxzoom; // max zoom						if(!p) {							cropimg.css({width:imgwidth*zl, height: imgheight*zl});							zoom = zl;						}					},									enablecrop: function(ev, first) {						var sw = croparea.width(), sh = croparea.height()						var select = false;						if(first) {							if(settings.values.crop) {								var select = {									setSelect: [										settings.values.crop.x,										settings.values.crop.y,										settings.values.crop.w,										settings.values.crop.h									]								}							}						}						var size = false;						if(settings.fixedsize) {							var size = {								minSize: [settings.fixedsize.width, settings.fixedsize.height],								maxSize: [settings.fixedsize.width, settings.fixedsize.height],								}							if(!select) {								var select = {									setSelect: [										(sw - settings.fixedsize.width) / 2,										(sh - settings.fixedsize.height) / 2,										settings.fixedsize.width, 										settings.fixedsize.height									]								}							} else {								select.setSelect[2] = settings.fixedsize.width;								select.setSelect[3] = settings.fixedsize.height;							}						}						cropimg.Jcrop($.extend({}, size, select, {							onSelect: function(c) {								if(settings.fixedsize) {									c.w = settings.fixedsize.width; 									c.h = settings.fixedsize.height;								}								var rect = [c.x, c.y, c.w, c.h, cropimg.width(), cropimg.height()];								cropresults = {									crop:c,									zoom:zoom,									zw: cropimg.width(),									zh: cropimg.height(),									ow: imgwidth,									oh: imgheight,									fa: isfitalign,									rect: rect.join("x")								};							}						}), function() {							crop = this;							if(settings.fixedsize) {								item.find(".jcrop-handle").hide();							}						});											},										disablecrop: function() {						if(crop) {							crop.destroy();							crop = false;						};					},										init: function() {						APILOADER.create();						croparea.html("");						cropimg = jQuery("<img/>").disableSelection().attr({src:url}).appendTo(item).load(function() {							// make sure it's loaded							imgwidth = jQuery(this).width();							imgheight = jQuery(this).height();							if(settings.fit) {								item.trigger('action', ['fitalign', true]);							}							if(settings.values) {								if(settings.values.zoom&&!settings.values.fa) {									item.trigger("zoom", [settings.values.zoom]);								}							}							if(settings.centerimage) {								item.trigger('centerimage');							}							item.trigger("enablecrop", [true]);							APILOADER.remove();						}).appendTo(croparea);											},									});				setTimeout(function() {					item.trigger("init");				}, 500);							}},			{type:'html', automargin: false, itemcontext: true, source: function(item) {				if(settings.actionbar) {					item.addClass("input cropactionbar");					var actions = [];					if(settings.actions.fitalign) {						actions.push({icon:'fitalign', action:'fitalign'});					}					if(settings.actions.zoom) {						actions.push({icon:'fitbox', action:'zoomreset'});						actions.push({icon:'add', action:'zoomin'});						actions.push({icon:'delete', action:'zoomout'});					}					$.each(actions, function(x, p) {						jQuery("<div></div>").addClass("actionbutton -corner-all-small").css("background-image", sprintf("url(/resources/dialogs/images/dialog-icon-%s.png)", p.icon)).click(function(){jQuery("#"+controlid).trigger('action', [p.action])}).appendTo(item);					});					item.append(mgClear());				}			}},					]	});	wnd.show();	return wnd;};/**  * (mgCreateCodeDialog)  * Creates a dialog that enables to display and edit code  */ var mgCreateCodeDialog = function(settings, callback) {	var settings = $.extend({}, {value: '', blocker: true, width: 650, height: 400}, settings);	var wnd = mgCreateButtonDialog({		width: settings.width,		height: settings.height,		blocker: settings.blocker,		values: {source: settings.value},		events: {			onsuccess: function(v) {				if(typeof(callback) == "function") {					return callback(v.source);				}			}		},		buttons: {			'{%ButtonCancel}': false,			'{%ButtonSave}': true,		},		fullarea: true,		padding: 0,		fields: [			{type:'codeeditor', mode:'combined', theme:'default', automargin: false, fitalign: true, allowfullscreen: true, storage:'source'} 		]	});	wnd.show();};/**  * (mgCreateExchangeDialog)  * Creates a dialog that enables to exchange snippets  */ var mgCreateExchangeDialog = function(value, callback, settings) {	// transform value	var v = value;	switch(typeof(v)) {		case "object": 			v = JSON.stringify(v, null, 4);			break;		case "boolean":			v = v===true?"true":"false";			break;	}	// create dialog	mgCreateCodeDialog($.extend({}, settings, {value: v}), function(nv) {		// parse		switch(typeof(value)) {			case "object": 				try {					nv = JSON.parse(nv);				} catch(e) {					alert(e);					return true;				}				break;			case "boolean":				nv = nv.toLowerCase()=="true"||nv.toLowerCase()=="1"?true:false;				break;		};				if(typeof(callback)=="function") {			callback(nv);		}	});};