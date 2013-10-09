/**  * (mg)framework Version 5.0  *	Copyright (c) 1999-2011 eikonlexis LLC. All rights reserved.  *  * This program is protected by copyright laws and international treaties.  * Unauthorized reproduction or distribution of this program, or any   * portion thereof, may result in serious civil and criminal penalties.  *  * Module 		Dialog Class  */  /**  * (constants)  */  var DIALOG_BUTTON_PREVIOUS = 0,	DIALOG_BUTTON_NEXT = 1,	DIALOG_BUTTON_FINISH = 2;  /**  * (mgCreateDialogContent)  * Creates a content block  */var mgCreateDialogContent = function(d, b, c, v) {	var cs = jQuery("<div></div>").addClass(b+"-content").append(v?v:"");	d.append(jQuery("<div></div>").addClass(b).css(c?c:{}).append(cs));	return cs;};  /**  * (mgCreateDialogWindow)  * Creates basic layout and window manager for window  */ var mgCreateDialogWindow = (function(){	function mgCreateDialogWindow(settings) {		this.settings = settings;		this.__initialize();	}	mgCreateDialogWindow.prototype = {		storagechanged: false,		blocker: false,		__initialize: function() {			this.__options = $.extend({}, {waitcursor: true, focusfirst: true, values: {}, error: false, blocker: false, cancelkey: false, width: 200, height: 120, margin: 20, moveable: false, events: false, title: false, position: 'centered', offset: false}, this.settings);			this.__class = jQuery("<div></div>").addClass("-dialog -dialog-defaultfont").addClass(this.__options.error?"-dialog-error":"").addClass(this.__options.title?"-dialog-hastitle":"-dialog-notitle").css({width: this.__options.width, height: this.__options.height});			if(this.__options.blocker) {				this.blocker = jQuery("<div></div>").addClass("-dialog-blocker").appendTo('body');			}						this.setposition(this.__options.position);			if(this.__options.moveable) {				this.__class.draggable({cancel:'.-dialog-inner,.-dialog-area', opacity: 0.5, stack: '.-dialog-window-stack'});			}			if(this.__options.title) {				this.__class.append(jQuery("<div></div>").addClass("-dialog-title -corner-top").append(this.__options.title).disableSelection());			}			this.__main = jQuery("<div></div>").addClass("-dialog-inner").appendTo(this.__class);			this.__class.appendTo('body').bind(typeof(this.__options.events)=="object"?this.__options.events:{});			this.storages = $.extend({}, this.__options.values);			this.__class.triggerHandler('oncreate');		},		setposition: function(p) {			var mt = -1*(this.__options.height/2),				ml = -1*(this.__options.width/2),				os = $.extend({}, {top: 0, left: 0, right: 0, bottom:0}, this.__options.offset);			this.__class.removeCSS("top left right bottom margin-top margin-left");			switch(p) {				case "topright": var pcss = {left:20+os.left, top:'50%', 'margin-top':mt}; break;				case "topleft": var pcss = {left:20+os.left, top:20+os.top}; break;				case "topcenter": var pcss = {top:40+os.top, left:'50%', 'margin-left':ml}; break;				case "topright": var pcss = {right:20+os.right, top:'50%', 'margin-top':mt}; break;				case "topright": var pcss = {right:20+os.left, top:20+os.top}; break;				case "bottomright": var pcss = {right:20+os.right, bottom:20+os.bottom}; break;				case "rightcenter": var pcss = {right:20+os.right, top:'50%', 'margin-top':mt}; break;				default: var pcss = {top:'50%', left:'50%', 'margin-top':mt, 'margin-left':ml}; break;			}			this.__class.css(pcss).appendTo('body');					},		clear: function() {			this.__class.triggerHandlers('destroycontrol').html("");		},		destroy: function() {			if(this.__options.blocker){				jQuery("#-page-blocker").css("opacity", 1);				this.blocker.remove();			}			this.__class.triggerHandlers('destroycontrol');			this.__class.triggerHandlers('destroy').trigger('ondestroy');			this.__class.remove();			if(this.__options.cancelkey) {jQuery(document).unbind('keyup');}			this.__class = null;		},		show: function() {			var that = this;			if(this.__options.blocker){				jQuery("#-page-blocker").css("opacity", 0.4);				this.blocker.show();			}			this.__class.show();			this.__class.trigger('onshow');			if(this.__options.focusfirst){				this.__class.find("input:first").focus();			}			if(this.__options.cancelkey) {				jQuery(document).keyup(function(e) {					if (e.keyCode == 27) {that.cancel();}				});			}			this.__main.triggerHandlers('after');		},		hide: function() {			this.__class.hide();			if(this.__options.blocker){				jQuery("#-page-blocker").css("opacity", 0.4);				this.blocker.hide();			}			this.__class.trigger('onhide');		},		success: function(c) {			var result = true;			if(typeof(this.__options.events.onvalidation)=="function") {				result = this.__options.events.onvalidation(this.values());				result = result==false?false:true;			}			if(result) {				if(typeof(this.__options.events.onsuccess)=="function") {					result = this.__options.events.onsuccess(this.values(), this);					result = result==false?false:true;					if(result&&!c) {this.destroy();}				}			}			return result;		},		cancel: function() {			this.__class.trigger('oncancel');			this.destroy();		},		values: function() {			return this.storages;		}	}		return mgCreateDialogWindow;})();/**  * (mgCreateWizardDialog)  * Basic function to create a wizard dialog  */var mgCreateWizardDialog = function(settings) {	var settings = $.extend({}, {pageindex: 0, width: 550, height: 350, pages: [], buttons: false}, settings);	var wnd = mgCreateButtonDialog($.extend({}, settings, {fields: false, buttons: false}));	wnd.__pageindex = settings.pageindex;	wnd.__pagecount = settings.pages.length;	wnd.__buttonaction = function(action) {		var index = this.__pageindex,			count = this.__pagecount - 1,			page = $.extend({}, {events: {}}, settings.pages[index]),			n = -1;		switch(action) {			case DIALOG_BUTTON_NEXT: 				n = index+1; 				if(n>count){n=count;}				if(typeof(page.events.onnext)=="function"){var c = page.events.onnext(index, n);if(c){n=c;}}				break;			case DIALOG_BUTTON_PREVIOUS:				n = index-1;				if(n<=0){n=0;}				if(typeof(page.events.onprevious)=="function"){var c = page.events.onprevious(index, n);if(c){n=c;}}				break;		}		if(n>=0){			this.__updatepage(n);		}	};	wnd.__updatepage = function(index) {		var that = this,				page = $.extend({}, {title: false, subtitle: false, icon: false, buttons: false, fields: [], events: {}}, settings.pages[index]), 			prev = (index>0),			next = (index>=0&&index<(this.__pagecount-1)),			finish = index==(this.__pagecount-1),			buttons = $.extend({}, {cancel: true, previous: prev, next: next, finish: finish}, page.buttons),			bntdata = {};		if(buttons.finish) {bntdata['{%ButtonFinish}']=function(w){return true}}		if(buttons.next) {bntdata['{%ButtonNext}']=function(w){w.__buttonaction(DIALOG_BUTTON_NEXT);}}		if(buttons.previous) {bntdata['{%ButtonPrevious}']=function(w){w.__buttonaction(DIALOG_BUTTON_PREVIOUS);}}		if(buttons.cancel) {bntdata['{%ButtonCancel}']=false}		this.__updatebuttons(bntdata);		var fields = [];		if(page.title) {			fields.push({type:'wizardtitle', title: page.title, subtitle: page.subtitle, icon: page.icon});		}		this.__updatefields(fields.concat(page.fields));		this.__pageindex = index;			};	wnd.__updatepage(wnd.__pageindex);	return wnd;};/**  * (mgCreateButtonDialog)  * Basic function to create a button dialog  */var mgCreateButtonDialog = function(settings) {	var wnd = new mgCreateDialogWindow($.extend({}, {padding: 10, scrollable: false, fields: false, width: 450, height: 250, buttons: {Cancel: 0}}, settings));	wnd.__top = mgCreateDialogContent(wnd.__main, "-dialog-top", {height: wnd.__options.height - 40});	wnd.__updatefields = function(flddata) {		var fields = mgCreateDialogFields(flddata, {			scrollable: wnd.__options.scrollable, 			parentwidth: wnd.__options.width, 			parentheight: wnd.__options.height - 40,			padding: settings.fullarea?0:settings.padding,			onwritestorage: function(name, value) {				wnd.storages[name] = value;			},			onreadstorage: function(name) {				return wnd.storages[name]?wnd.storages[name]:"";			}		})		this.__top.triggerHandlers('destroycontrol').html("").append(fields);	};	wnd.__updatefields(wnd.__options.fields);	if(!settings.nobottom) {		wnd.__bottom = mgCreateDialogContent(wnd.__main, "-dialog-bottom");		wnd.__updatebuttons = function(btndata) {			var that = this;			var buttons = jQuery("<ul></ul>");			$.each(btndata, function(name, id) {				jQuery("<li></li>").append(name).data("mg-buttonid", id).disableSelection('pointer').bind({					'click touchend': function(ev) {						ev.stopPropagation();						var f = jQuery(this).data("mg-buttonid");						if(typeof(f)=="function") {							var r = f(that, that.__top);							if(r) {								that.success();							}						} else {							if(f==true) {								that.success();							} else if(f==false) {								that.cancel();							} else {								that.__class.trigger('onbuttonclick', f);							}						}					}				}).appendTo(buttons);			});				this.__bottom.triggerHandlers('destroycontrol').html("").append(buttons).append(mgClear());		}		wnd.__updatebuttons(wnd.__options.buttons);	}	return wnd;};/**  * (mgCreatePageDialog)  * Basic function to create a page dialog  */var mgCreatePageDialog = function(settings) {	var wnd = new mgCreateDialogWindow($.extend({}, {simpletabs: false, fullarea: false, scrollable: true, pages: [], width: 650, height: 400, buttons: {Cancel: 0}}, settings));	wnd.__side = mgCreateDialogContent(wnd.__main, "-dialog-side");	wnd.__content = mgCreateDialogContent(wnd.__main, "-dialog-area");	wnd.__main.append(mgClear());	// attach dialog specific functions	wnd.rendertabfields = function(data, p) {		var that = this;		// clear and set content		this.__content.triggerHandlers('destroycontrol').html("").append(mgCreateDialogFields(			data.fields, {				scrollable: that.__options.scrollable, 				parentwidth: that.__options.width - (p.fullarea?175:195), 				parentwidthadjusted: 20,				parentheight: that.__options.height + (p.fullarea?19:0),				onwritestorage: function(name, value) {					wnd.storages[name] = value;				},				onreadstorage: function(name) {					return wnd.storages[name]?wnd.storages[name]:"";				}			}		));				this.__content.triggerHandlers('after');	}	// create page dialogs	$.each(wnd.__options.pages, function(index, page) {		// initialize page		var page = $.extend({}, {name: false, tabs: {}, isaction: false, showname: true}, page);		// create name		if(page.showname) {			wnd.__side.append(jQuery("<h3></h3>").append(page.name)).disableSelection();		}		// create tabs		var tabs = jQuery("<ul></ul>").appendTo(wnd.__side);		// create tab links		if(page.isaction) {			$.each(page.buttons, function(name, id) {				jQuery("<li></li>").addClass("action").append(name).data("mg-buttonid", id).disableSelection('pointer').bind({					'click touchend': function(ev) {						ev.stopPropagation() ;						var f = jQuery(this).data("mg-buttonid");						if(typeof(f)=="function") {							var r = f(wnd);							if(r) {								wnd.success();							}						} else {							if(f==true) {								wnd.success();							} else if(f==false) {								wnd.cancel();							} else {								wnd.__class.trigger('onbuttonclick', f);							}						}					}				}).appendTo(tabs);			});				} else {			$.each(page.tabs, function(name, fields) {				var pr = false;				if(!settings.simpletabs) {					var pr = $.extend({}, {fullarea:false, action: false, scrollbar: true}, fields), name = pr.name, fields = pr.fields;				}				var tab = jQuery("<li></li>").append(name).addClass("tab").disableSelection('pointer');				// assign parameters				// assign fields to tab				tab.data({					fields: fields,					name: name,					parameters: pr,				});				// bind events				tab.bind({					'click': function() {						jQuery(this).parent().parent().find(".tab.selected").removeClass("selected");						jQuery(this).addClass("selected");						var pr = jQuery(this).data("parameters"), 							fields = jQuery(this).data();						if(typeof(pr.action)=="function") {fields = pr.action();}						wnd.rendertabfields(fields, pr?pr:{});						if(pr) {							if(pr.fullarea) {								wnd.__content.find(".-dialog-control").css({padding: 0, "overflow-y":"hidden"});							}							if(!pr.scrollbar) {								wnd.__content.find(".-dialog-control").css({"overflow-y":"hidden"});							}						}							if(typeof(wnd.__options.events.ontabchange)=="function") {							wnd.__options.events.ontabchange();						}											}				});				// add to tab				tab.appendTo(tabs);			});		}		// select first		wnd.__side.find(".tab:first").click();	});	return wnd;};/**  * (mgCreateActionPageDialog)  * Macro to PageDialog  */var mgCreateActionPageDialog = function(settings) {	var settings = $.extend({}, {buttons:false, blocker: true, allowsave: false, simpletabs: true, tabs: {}}, settings);	var p = [];	p.push({name:'{%DialogPageActions}', isaction: true, buttons: $.extend({}, settings.allowsave?{		'{%ButtonApply}': function(wnd) {			wnd.success(true);			return false;		}}:{}, {		'{%ButtonApplyClose}': true,		'{%ButtonCancel}': false	}, settings.buttons)});	p.push({name:'{%DialogPageTabs}', tabs: settings.tabs});	delete settings.tabs;	return mgCreatePageDialog($.extend({}, {pages: p}, settings));	}/**  * (mgCreateMessageDialog)  * Main function to create a message dialog  */var mgCreateMessageDialog = function(settings) {	if(typeof(settings)=="string") {settings = {message: settings};}	var options = $.extend({}, {width: 400, height: 150, fields: false, addfields: true, buttons:{'{%ButtonClose}': false}, error: false, offset: false, position:false, blocker: true, message: false, icon: false, header: false, type: 'simple'}, settings),		mask = [];	if(options.header) {		mask.push({type:'label', header: true, text: options.header});	}	mask.push({type:'label', icon: options.icon, text: options.message});	if(options.fields) {		mask = !options.addfields?options.fields:mask.concat(options.fields);	}	var wnd = new mgCreateButtonDialog({		fields: mask,		height: options.height,		width: options.width,		offset: options.offset,		blocker: options.blocker,		error: options.error,		position: options.position,		buttons: options.buttons,		events: options.events	});  	wnd.show();	return wnd;};/**  * (mgMessageDialog)  * Simple Message Dialog that displays a title and message along a ok button  * Does not have any return values  */var mgMessageDialog = function(title, message, buttons, callback, settings) {	return mgCreateMessageDialog($.extend({}, {		header:title,		position:'topcenter',		offset: {top: 10},		message:message, 		buttons:typeof(buttons)=="object"?buttons:{'{%ButtonClose}': typeof(callback)=="function"},		events: $.extend({}, typeof(callback)=="function"?{onsuccess:callback}:{})	}, settings));}/**  * (mgCreateDebugDialog)  * Main function to create a debug dialog  */var mgCreateDebugDialog = function(msg) {	var wnd = new mgCreateButtonDialog({		fields: [			{type:'label', header: true, text: 'Debug Message'},			{type:'label', outline:true, height: 150, scrolling: true, text:"<b>("+typeof(msg)+") length="+msg.length+"</b>\n\n"+(typeof(msg)=="object"?JSON.stringify(msg):msg)},		],		height: 250,		width: 400,		buttons: {			'Close': false		}	});  	wnd.show();	return wnd;};/**  * (mgCreateCollectionDialog)  * Main function to create a debug dialog  */var mgCreateCollectionDialog = function(values, successcallback, options) {	var options = $.extend({}, {controlbar: true}, options), wnd = mgCreateButtonDialog({		values: {values: values},		fullarea: true,		blocker: true,		fields: [			{type:'collection', controlbar: options.controlbar, fitalign: true, autodetect: true, storage:'values'}		],		buttons: {			'{%ButtonCancel}': false,			'{%ButtonSave}': true,		},		events: {			onsuccess: function(v) {				if(typeof(successcallback)=="function") {					successcallback(v.values);				}			}		}	});	// show window	wnd.show();}/**  * (mgCreateGridPickerDialog)  * Main function to create a delete confirmation dialog  */var mgCreateGridPickerDialog = function(settings) {	if(typeof(settings)=="string") {settings = {message: settings};}	var options = $.extend({}, {gridheader: false, success: false, columns: false, buttons:{'{%ButtonCancel}': false}, data: false, position:false, blocker: true, message: false, icon: false, header: false, type: 'simple'}, settings),		columns = [];	// process columns	$.each(options.columns, function(index, field) {		columns.push({label: index, field: field});	});	// create mask	var wnd = new mgCreateButtonDialog({		fields: [			{type:'label', header: true, text: options.header},			{type:'grid', header: options.gridheader, height: 7, rowselect: false, controlbar: false, alwayssortable: true, columns: columns, events: {				onvalues: function(page, itemsperpage, sort, sortmode) {					return mgSortObjectArray(options.data, sort, sortmode=="a"?true:false)				},				onrowclick: function(item, grid) {					if(typeof(options.success)=="function") {						options.success(item);					}					wnd.destroy();					return false;				},				oncellformat: function(column, value, item) {					switch(column.field) {						case "filesize": return mgBytesToSize(parseInt(value)); break;					}				}			}}		],		height: 300,		width: 450,		offset: options.offset,		blocker: options.blocker,		error: options.error,		position: options.position,		buttons: options.buttons,	});  	wnd.show();	return wnd;};/**  * (mgCreateDeleteDialog)  * Main function to create a delete confirmation dialog  */  var mgCreateDeleteDialog = function(values, successcallback, settings) {	// test values	if(mgEmptyObject(values)) return false;	// settings	var settings = $.extend({}, {height: false, text: false}, settings);	// initialize	var fields = [{type:'label', header: true, text:'{%DialogDeleteTitle}'}];	if(settings.text) {		fields.push({type:'label', text: settings.text});	}	$.each(values, function(name, value) {		fields.push({type:'checkbox', checked: true, label: value, storage: name});	});	var wnd = mgCreateButtonDialog({		values: {values: values},		blocker: true,		fields: fields,		height: settings.height?settings.height:350,		buttons: {			'{%ButtonCancel}': false,			'{%ButtonOk}': true,		},		events: {			onsuccess: function(v) {				if(typeof(successcallback)=="function") {					successcallback(v.values);				}			}		}	});	// show window	wnd.show();}/**  * (mgCreateErrorDialog)  * Main function to create a debug dialog  */var mgCreateErrorDialog = function(e, settings) {	var settings = $.extend({}, {showstack: true}, settings),		wnd = new mgCreateButtonDialog({		blocker: true,		fields: [			{type:'label', header: true, text: 'An error occured!'},			{type:'label', icon:'exclamation', text: sprintf("%s\n\n%s:%s (%s)", e.message, e.fileName, e.lineNumber)},			{type:'text', margins:{left:20}, readonly: true, scrollbar: true, text: e.stack, rows:5, size: 43},		],		height: 250,		width: 400,		buttons: {			'Close': false		}	});  	wnd.show();	return wnd;};/**  * (mgCreateAssetDialog)  * Main function to create a asset dialog  */var mgCreateAssetDialog = function(o, callback) {	// create settings	var settings = $.extend({picker: false, user: false, isuser: true, crop: false, assets: false, showall: true, hideall: false, images: true, videos: true, music: true, archives: true, other: true, documents: true}, {}, o);	// adjust settings	if(settings.hideall){		settings.showall = false;		var settings = $.extend({}, settings, {hideall: false, images: false, videos: false, music: false, archives: false, other: false, documents: false}, o);	}		// create tabs and window	var tabs = {}, wnd = false, dlg = false;	// get assets	var getassets = function() {		var result = false;		if(settings.user) {			if(typeof(settings.user)=="function") {				result = settings.user();			} else {				var r = ASSET.user(settings.user);				if(r.result) {					result = r.result;				}			}		} else if(settings.assets) {			result = settings.assets;		}		if(result) {			result = mgAssetsByCategory(result);		}					return result;	};	// assign refresh	var refreshdialog = function() {		// refresh dialog		wnd.__content.find(".assetlist").trigger('updateassets', [getassets()]);	}	// assign upload	var upload = function() {		// create upload control		var upl = jQuery("<div></div>").selectfile({			maxsize: ASSET_MAXSIZE,			success: function(filedata) {				var r = ASSET.upload(settings.user, filedata);										// refresh list or grid				refreshdialog();			},									failed: function(error, filedata) {				switch(error) {					case "mime": var msg = sprintf('{%FileUploadErrorMime}', filedata.type); break;					case "size": var msg = sprintf('{%FileUploadErrorSize}', mgBytesToSize(params.maxsize), mgBytesToSize(filedata.size)); break;					default: var msg="{%FileUploadError}"; break;				}				mgMessageDialog("{%FileUploadErrorTitle}", msg);			}		});		upl.find("input").click();	};	// create events	var events = {		onadd: function() {			upload();					},		ondelete: function(items) {			mgCreateDeleteDialog(items, function(d) {				try {					ASSET.remove(d);					if(dlg) {dlg.cancel();}					refreshdialog();				} catch(e) {					alert(e);				}			});		},		ondoubleclick: function(item, grid) {			// initialize			var isimage = (item.filetype == "image");			// create values			var values = {				Type: item.filetypename,				Mime: item.filemime,				Size: mgBytesToSize(parseInt(item.filesize)),				Filename: item.filename,				'Link (SEO)': item.seourl,				'Link': item.url,				ID: item.idstring,				SID: item.idrelated,			};			// add extra meta			if(isimage) {				values['Dimensions'] = sprintf("%sx%s px", item.width, item.height)			}			// create fields			var fields = [				!isimage?{}:{type:'html', source: function() {					var s = jQuery("<div></div>").css({position:'relative', height:165, padding:5}).attr("align", "center");					// add image					var i = jQuery("<img/>").addClass("-corner-all").css({cursor:'pointer'}).attr("src", sprintf("%s?crop=165x165", item.url)).appendTo(s);					// open in new window					i.bind('click touchend', function() {						window.open(item.url);					});					return s;				}},				{type:'collection', values:values, readonly: true, height: isimage?6:10, widthname: 100}			];			// create delete			var d = {};			d[item.idstring] = item.filename;			// show dialog			dlg = mgCreateButtonDialog({				blocker: true,				position:'topcenter',				width: 450,				height: isimage?410:335,				values: item,				fields: fields,									scrollbar: true,				buttons: $.extend({}, settings.picker?{'{%ButtonCancel}': false, '{%ButtonDelete}':function(){events.ondelete(d)}, '{%ButtonUse}': true}:{'{%ButtonClose}': false, '{%ButtonDelete}':function(){events.ondelete(d)}}),				events: {					ondestroy: function() {						dlg = false;					},					onsuccess: function(values) {						if(settings.picker&&typeof(callback)=="function") {							callback(item);							wnd.cancel();						} 						return true;					}				}			});			dlg.show();					}	};	// check	if(settings.showall) {tabs['{%AssetLabelAll}'] = [{type:'assetlist', events: events, assets: getassets(), category:false}];}	if(settings.showall||settings.images) {tabs['{%AssetLabelImages}'] = [{type:'assetlist', events: events, assets: getassets(), imagebrowser: true, category:'images'}];}	if(settings.showall||settings.videos) {tabs['{%AssetLabelVideos}'] = [{type:'assetlist', events: events, assets: getassets(), category:'archives'}];}	if(settings.showall||settings.music) {tabs['{%AssetLabelMusic}'] = [{type:'assetlist', events: events, assets: getassets(), category:'music'}];}	if(settings.showall||settings.media) {tabs['{%AssetLabelMedia}'] = [{type:'assetlist', events: events, assets: getassets(), category:'media'}];}	if(settings.showall||settings.documents) {tabs['{%AssetLabelDocuments}'] = [{type:'assetlist', events: events, assets: getassets(), category:'documents'}];}	if(settings.showall||settings.archives) {tabs['{%AssetLabelArchives}'] = [{type:'assetlist', events: events, assets: getassets(), category:'archives'}];}	if(settings.showall||settings.other) {tabs['{%AssetLabelOther}'] = [{type:'assetlist', events: events, assets: getassets(), category:'unknown'}];}		// has buttons	var hasbuttons = {};	if (settings.user) {		var hasbuttons = {			'{%ButtonUpload}': upload		};	};	// create dialog	wnd = mgCreateActionPageDialog({		width: 800,		height: 450,		events: {			onsuccess: function(values) {				if(typeof(callback)=="function"){					callback(values);				}			},		},		scrollable: true,		buttons: hasbuttons,		simpletabs: true,		fullarea: true,		tabs: tabs	});	wnd.show();	refreshdialog();	return wnd;};/**  * (mgCreateHistoryItemDialog)  * Main function to create a asset dialog  */  var mgCreateHistoryDialog = function(history, settings) {	var settings = $.extend({}, {values: false, name: 'History Overview', blocker: true, width: 650, height: 432}, settings),		wnd = new mgCreateButtonDialog({		blocker: settings.blocker,		padding: 0,		values: settings.values?settings.values:{},		fields: [			{type:'crmheader', subheader:'History', header:settings.name, icon:'book'},			{type:'history', fixed: true, height:10, automargin: false, list: history, fixalign: true}		],		height: settings.height,		width: settings.width,		buttons: {			'Close': false		}	});  	wnd.show();	return wnd;	};/**  * (mgCreateHistoryItemDialog)  * Main function to create a asset dialog  */  var mgCreateHistoryItemDialog = function(item, settings) {	var settings = $.extend({}, {blocker: true, width: 400, height: 460}, settings),		wnd = new mgCreateButtonDialog({		values: item,		blocker: settings.blocker,		padding: 0,		fields: [			{type:'crmheader', subheader:'History Item', header:'%name%', icon:'book'},			{type:'crmsections', automargin: false, fixed: true, height: 350, collapsable: true, sections: {				'Overview': [										{type:'input', readonly:true, label:'Name', storage:'name'},					{type:'text', readonly:true, label:'Description', storage:'description'},					{type:'input', readonly:true, label:'Date', storage:'date'},					{type:'input', readonly:true, label:'Time', storage:'time'},				],				'Additional': [					{type:'collection', values:item.data?item.data:[], readonly: true}				]			}}		],		height: settings.height,		width: settings.width,		buttons: {			'Close': false		}	});  	wnd.show();	return wnd;	};/**  * (mgCreateDialogFields)  * Main function to create dialog fields    */var mgCreateDialogFields = function(fields, settings) {	// initialize result	var result = jQuery("<div></div>").addClass("-dialog-control");	// initialize options	var options = $.extend({}, {onwritestorage: false, onreadstorage: false, padding: 10, parentwidth: false, parentwidthadjusted: 0, parentheight: false, scrollable: false}, settings);	// transform options	options.parentwidth -= (options.padding * 2);	options.parentheight -= options.padding * 2;	// initialize css	result.css($.extend({}, {padding: options.padding, width: options.parentwidth, height: options.parentheight}, options.scrollable?{'overflow-y':'scroll'}:(options.parentheight=="auto"?{}:{'overflow':'hidden'})));	// adjust width	options.parentwidth -= options.parentwidthadjusted;	// initialize control 	var fieldcontroller = new mgDialogFieldController(options);	// check fields	if(typeof(fields)=="function") {		fields = fields();	}	// create fields	$.each(fields, function(index, params) {		// check if function		if(typeof(params)=="function") {			params = params();		}		// initialize field item and set parameters		var params = $.extend({}, {storage: false, classes: false, type: false, automargin: true}, params);		// create control		if(params.type) {			// call control			var item = fieldcontroller.callcontrol(params.type, params, index);		}		// add item on success		if(item) {			var l = result.find(":last");			if(!l.hasClass("divider")&&!l.hasClass("rwlb")&&!item.hasClass("divider")&&result.find("*").length!=0){				if(params.automargin) {					item.css("margin-top", 10);				}			}			item.data("params", params).addClass(params.classes);			result.append(item);				}	});	return result;};/**  * Supporting functions for mgDialogFieldControl functions  */jQuery.fn.mgDialogFontStyle = function(styles) {	styles = $.extend({color: false, bold: false, italic: false, underline: false}, styles);	if(styles.bold) {jQuery(this).css('font-weight', 'bold')}	if(styles.italic) {jQuery(this).css('font-style', 'italic')}	if(styles.underline) {jQuery(this).css('text-decoration', 'underline')}	if(styles.color) {jQuery(this).css('color', styles.color)}	return jQuery(this);};