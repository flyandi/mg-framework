/**
  * Manager Module Script
  * @Id: manager.personality
  */

var mgResourceScript = {
	/** (run) */
	process: function(action) {
		var that = this;
		// initialize
		this.parameters = this.call('parameters');
		// action
		switch(action) {
			default:
				this.returncontent([
					{type:'label', header:true, text:'{%ManagerModuleHeader}'},
					{type:'grid', controlbar: false, columns: this.call('getgrid'), alwayssortable: true, events: {
						onvalues: function(page, itemsperpage, sort, sortmode, search) {
							return that.call('data', {requestpage: page, itemsperpage: itemsperpage, sort: sort, sortmode: sortmode, search: search});
						},
						onrowdoubleclick: function(item, grid) {
							that.dialog(item, function(uitem) {
								grid.trigger("update");
							});
						},
						oncellformat: function(column, value, item) {
							switch(column.field) {
								case "active": item.css("color", value==1?"green":"red"); break;
							}
						},
						onadd: function(grid) {
							that.dialogadd(function() {								
								grid.trigger("update");
							});
						},
						ondelete: function(items, grid) {
							// parse id's
							var deleteids = [];
							$.each(items, function(index, params){deleteids.push(params.idstring)});
							that.call('registrydeletevalue', {items: deleteids});
							grid.trigger("update");
						}
					}}
				]);
		}
		return true;		
	},
	
	/** dialogadd */
	dialogadd: function(callback) {
		var wnd = mgCreateButtonDialog({
			blocker: true,
			height: 320,
			width: 280,
			title:'Add User',
			fields: [
				{type:'input', label:'Username', storage:'idusername'},
				{type:'combo', label:'Enabled', options:['No', 'Yes'], select:1, storage:'active', size: 20},
				{type:'divider'},
				{type:'combo', label:'Type', options:this.parameters.types, storage:'idtype', size: 20},
				{type:'combo', label:'Role', options:this.parameters.groups, storage:'role', size: 20},
				{type:'combo', label:'Language', options:this.parameters.languages, storage:'idlocalized', size: 20},
				{type:'divider'},
				{type:'checkbox', label:'Issue Temporary Password'}
			],
			buttons: {
				'{%ButtonCancel}': false,
				'{%ButtonAdd}': true,
			},
			events: {
				onsuccess: function(v) {
					if(typeof(successcallback)=="function") {
					successcallback(v.values);
					}
				}
			}
		});
		wnd.show();	
	},
	
	/** ----------------------------------------------------------------------------------------------
	  * (dialog) for personality 
	  */
	  
	dialog: function(item, callback) {
		// initialize
		var that = this;
		// set items
		var templates = mgIsObject(item.templates)?item.templates:[];
		var requests = mgIsObject(item.requests)?item.requests:[];
		var contents = mgIsObject(item.contents)?item.contents:[];
		var snippets = mgIsObject(item.snippets)?item.snippets:[];
		var emails = mgIsObject(item.emails)?item.emails:[];
		var languages = false;
				
		
		//requestes = [];
		// get real item
		var wnd = mgCreateActionPageDialog({
			values: item,
			width: 900,
			height: 450,
			events: {
				onsuccess: function(values) {
					// assign to values
					values.templates = templates;
					values.requests = requests;
					values.contents = contents;
					values.snippets = snippets;
					values.emails = emails;
					// send request
					var result = that.call('set', values);
					// process result
					if(!result) {
						alert('Can not update Personality');
						return false;
					} else {
						if(typeof(callback)=="function"){
							callback(values);
						}
						return true;
					}
				},
			},
			allowsave: true,
			simpletabs: false,
			tabs: [
				{name:'Overview', fields: [
					{type:'input', label:'Name', style:{bold:true}, storage:'description', margins: {top:10}},
					{type:'input', label:'Domain', storage:'domain'},
					{type:'divider'},
					{type:'combo', label:'Enabled', options:['No', 'Yes'], storage:'active', size: 20},
					{type:'divider'},
					{type:'combo', label:'Inherited', options:[], storage:'default-personality', size: 20},
				]},
				{name:'Configuration', fullarea: true, fields: [
					{type:'collection', fitalign: true, autodetect: true, storage:'configuration', events: {
						oncollection: function(values, resultcallback) {
							mgCreateCollectionDialog(values, resultcallback);
						}
					}}
				]},
				{name:'Templates', scrollbar: false, fields: [
					{type:'grid', height: 14, columns: this.call('getgrid', {name:'templates'}), controlactions: {add: true, remove: true}, alwayssortable: true, events: {
						onvalues: function(page, itemsperpage, sort, sortmode) {
							return mgSortObjectArray(templates, sort, sortmode=="a"?true:false);
						},
						onrowdoubleclick: function(item, grid) {
							that.dialogtemplate(item, function(uitem) {
								var index = $.inJSON(templates, ['name', item.name], false, true);
								if(index!=-1) {
									templates[index] = uitem;
									grid.trigger("update");
								}
							});
						},
						onadd: function(grid) {
							that.dialogtemplate(false, function(uitem) {								
								templates.push(uitem);
								grid.trigger("update");
							});
						},
						ondelete: function(items, grid) {
							$.each(items, function(index, item) {
								var index = $.inJSON(templates, ['name', item.name], false, true);
								if(index!=-1) {
									templates.splice(index, 1);
								}
							});
							grid.trigger("update");
						}
					}}
				]},
				{name:'Localizations', scrollbar: false, fields: [
					{type:'tabs', tabs: {
						'Requests': [
							{type:'grid', intabs: true, columns: this.call('getgrid', {name:'requests'}), controlactions: {add: true, remove: true}, alwayssortable: true, events: {
								onvalues: function(page, itemsperpage, sort, sortmode) {
									return mgSortObjectArray(requests, sort, sortmode=="a"?true:false);
								},
								onrowdoubleclick: function(item, grid) {
									that.dialogrequest(item, function(uitem) {
										var index = $.inJSON(requests, ['request', item.request], false, true);
										if(index!=-1) {
											requests[index] = uitem;
											grid.trigger("update");
										}
									}, requests, templates);
								},
								oncellformat: function(column, value, item) {
									switch(column.field) {
										case "enabled": item.css("color", value==1?"green":"red"); break;
									}
								},
								onadd: function(grid) {
									that.dialogrequest(false, function(uitem) {								
										requests.push(uitem);
										grid.trigger("update");
									}, requests, templates);
								},
								ondelete: function(items, grid) {
									$.each(items, function(index, item) {
										var index = $.inJSON(requests, ['request', item.request], false, true);
										if(index!=-1) {
											requests.splice(index, 1);
										}
									});
									grid.trigger("update");
								}
							}},
							{type:'divider'},
							{type:'combo', size: 20, label:'Default Request', options:['Application Call', 'Sign-In', 'Error'], storage:'defaultrequest'}
						],
						'Content': [
							{type:'grid', intabs: true, columns: this.call('getgrid', {name:'contents'}), controlactions: {add: true, remove: true}, alwayssortable: true, events: {
								onvalues: function(page, itemsperpage, sort, sortmode) {
									return mgSortObjectArray(contents, sort, sortmode=="a"?true:false);
								},
								onrowdoubleclick: function(item, grid) {
									that.dialogcontent(item, function(uitem) {
										var index = $.inJSON(contents, ['name', item.name], false, true);
										if(index!=-1) {
											contents[index] = uitem;
											grid.trigger("update");
										}
									}, contents, templates);
								},
								oncellformat: function(column, value, item) {
									switch(column.field) {
										case "enabled": item.css("color", value==1?"green":"red"); break;
									}
								},
								onadd: function(grid) {
									that.dialogcontent(false, function(uitem) {	
										contents.push(uitem);
										grid.trigger("update");
									}, contents, templates);
								},
								ondelete: function(items, grid) {
									$.each(items, function(index, item) {
										var index = $.inJSON(contents, ['name', item.name], false, true);
										if(index!=-1) {
											contents.splice(index, 1);
										}
									});
									grid.trigger("update");
								}
							}},							
						],
						'Snippets': [
							{type:'grid', intabs: true, columns: this.call('getgrid', {name:'snippets'}), controlactions: {add: true, remove: true}, alwayssortable: true, events: {
								onvalues: function(page, itemsperpage, sort, sortmode) {
									return mgSortObjectArray(snippets, sort, sortmode=="a"?true:false);
								},
								onrowdoubleclick: function(item, grid) {
									that.dialogsnippet(item, function(uitem) {
										var index = $.inJSON(snippets, ['name', item.name], false, true);
										if(index!=-1) {
											snippets[index] = uitem;
											grid.trigger("update");
										}
									}, snippets);
								},
								oncellformat: function(column, value, item) {
									switch(column.field) {
										case "enabled": item.css("color", value==1?"green":"red"); break;
									}
								},
								onadd: function(grid) {
									that.dialogsnippet(false, function(uitem) {								
										snippets.push(uitem);
										grid.trigger("update");
									}, snippets);
								},
								ondelete: function(items, grid) {
									$.each(items, function(index, item) {
										var index = $.inJSON(snippets, ['name', item.name], false, true);
										if(index!=-1) {
											snippets.splice(index, 1);
										}
									});
									grid.trigger("update");
								}
							}},	
						
						],
						'E-Mails': [
							{type:'grid', intabs: true, columns: this.call('getgrid', {name:'emails'}), controlactions: {add: true, remove: true}, alwayssortable: true, events: {
								onvalues: function(page, itemsperpage, sort, sortmode) {
									return mgSortObjectArray(emails, sort, sortmode=="a"?true:false);
								},
								onrowdoubleclick: function(item, grid) {
									that.dialogemail(item, function(uitem) {
										var index = $.inJSON(emails, ['name', item.name], false, true);
										if(index!=-1) {
											emails[index] = uitem;
											grid.trigger("update");
										}
									}, emails, templates);
								},
								oncellformat: function(column, value, item) {
									switch(column.field) {
										case "enabled": item.css("color", value==1?"green":"red"); break;
									}
								},
								onadd: function(grid) {
									that.dialogemail(false, function(uitem) {	
										emails.push(uitem);
										grid.trigger("update");
									}, emails, templates);
								},
								ondelete: function(items, grid) {
									$.each(items, function(index, item) {
										var index = $.inJSON(emails, ['name', item.name], false, true);
										if(index!=-1) {
											emails.splice(index, 1);
										}
									});
									grid.trigger("update");
								}
							}},	
						],
						'Languages': [
							{type:'grid', intabs: true, columns: this.call('getgrid', {name:'language'}), controlactions: {add: true, remove: true}, alwayssortable: true, events: {
								onvalues: function(page, itemsperpage, sort, sortmode) {
									if(!languages) {
										languages = that.call('language', {idstring: item.idstring});
									}
									return mgSortObjectArray(languages, sort, sortmode=="a"?true:false);
								},
								onrowdoubleclick: function(item, grid) {
									that.dialoglanguage(item, function(uitem) {
										var index = $.inJSON(languages, ['id', item.id], false, true);
										if(index!=-1) {
											languages[index] = uitem;
											grid.trigger("update");
										}
									}, languages);
								},
								onadd: function(grid) {
									that.dialoglanguage(false, function(uitem) {								
										languages.push(uitem);
										grid.trigger("update");
									}, languages);
								},
								ondelete: function(items, grid) {
									$.each(items, function(index, item) {
										var index = $.inJSON(languages, ['id', item.id], false, true);
										if(index!=-1) {
											languages.splice(index, 1);
										}
									});
									grid.trigger("update");
								}
							}}
						]
					}}
				]},
			]
		});
		
		wnd.show();
	},
	
	
	/** ----------------------------------------------------------------------------------------------
	  * (dialogtemplate)
	  */
	  
	dialogtemplate: function(item, callback) {
		// initialize
		var that = this;
		// convert old format
		if(item.source&&!mgIsObject(item.source)) {
			item.source = {"0":{source:item.source}};
		}
		// get real item
		var wnd = mgCreateActionPageDialog({
			values: item,
			events: {
				onsuccess: function(values) {
					if(typeof(callback)=="function") {
						callback(values);
					}
				},
			},
			simpletabs: false,
			tabs: [
				{name:'Overview', fields: [
					{type:'input', label:'Name', style:{bold:true}, storage:'name', margins: {top:10}},
					{type:'input', label:'Reference', storage:'reference'},
					{type:'divider'},
					{type:'combo', label:'Type', storage:'type', options: that.parameters.templatetypenames, size: 20},
					{type:'combo', label:'Format', storage:'format', options: that.parameters.templateformatnames, size: 20},
					{type:'divider'},
					{type:'input', label:'Location', storage:'location'},
				]},
				{name:'Source', scrollbar: false, fields: [
					{type:'switcher', tabs: ['Default', 'Mobile'], storage:'source', fields: [
						{type:'codeeditor', height: 355, mode:'combined', theme:'default', allowfullscreen: true, storage:'source'}
					]}
				]}
				/*
				{name:'Source', fullarea: true, fields: [
					{type:'codeeditor', mode:'combined', theme:'default', fitalign: true, allowfullscreen: true, storage:'source'} 
				]},*/
			]
		});
		
		wnd.show();
	},
	
	/** ----------------------------------------------------------------------------------------------
	  * (dialogrequest)
	  */
	dialogrequest: function(item, callback, requests, templates) {
		// initialize
		var that = this;
		// set localizations
		var v = !item?{localrequests:mgRemoveValuesFromObject(this.parameters.languages)}:item;
		// get real item
		var wnd = mgCreateActionPageDialog({
			values: v,
			events: {
				onsuccess: function(v) {
					// format data
					if(v.request.substr(0, 1)!="/") {v.request="/"+v.request}
					// run callback
					if(typeof(callback)=="function") {
						callback(v);
					}
				},
				onvalidation: function(v) {
					if(v.request.length==0) {
						mgMessageDialog('Warning!', 'Please enter a valid name for the request.');
						return false;
					} else {
						// format data
						if(v.request.substr(0, 1)!="/") {v.request="/"+v.request}
						// verify request only if added
						if(!item) {
							if($.inJSON(requests, ['request', v.request], true)) {
								mgMessageDialog('Error!', 'The entered request already exists.');
								return false;
							}
						}
					}
					return true;
				}
			},
			simpletabs: false,
			tabs: [
				{name:'Overview', fields: [
					{type:'input', readonly:!item?false:true, label:'Request', style:{bold:true}, storage:'request', margins: {top:10}},
					{type:'divider'},
					{type:'combo', label:'Condition', storage:'condition', options: that.parameters.requestsconditionnames, size: 25},
					{type:'combo', label:'Type', storage:'type', options: that.parameters.requeststypenames, size: 25},
					{type:'text', label:'Value', storage:'value'},
					{type:'divider'},
					{type:'combo', label:'Enabled', options:['No', 'Yes'], select:1, storage:'enabled', size: 20},
					
				]},
				{name:'Advance', fields: [
					{type:'text', label:'Includes', storage:'includes', margins:{top:10}},
					{type:'divider'},
					{type:'combo', label:'Template', select:'index', storage:'template', options: mgFormatObjectArray(templates, {name:function(v){return mgUCFirst(v.name)}}), size: 20},
					{type:'combo', label:'User Group', select:'index', storage:'usergroup', options: that.parameters.usergroupnames, size: 20, margins:{top:10}},
					{type:'divider'},
					{type:'collection', label:'Localizations', storage:'localrequests'}
				]}
			]
		});
		
		wnd.show();
	},	
	
	/** ----------------------------------------------------------------------------------------------
	  * (dialogcontent)
	  */
	  
	dialogcontent: function(item, callback, contents, templates) {
		// initialize
		var that = this;
		// set localizations
		var item = !item?{languages:['default'].concat(mgCreateArrayFromObject(this.parameters.languages))}:item,
			v = item;		
		// get real item
		var wnd = mgCreateActionPageDialog({
			values: item,
			events: {
				onsuccess: function(values) {
					if(typeof(callback)=="function") {
						callback(values);
					}
				},
				onvalidation: function(v) {
					if(v.name.length==0) {
						mgMessageDialog('Warning!', 'Please enter a valid name for the content.');
						return false;
					} else {
						// verify request only if added
						if(!item) {
							if($.inJSON(contents, ['name', v.name], true)) {
								mgMessageDialog('Error!', 'The entered content already exists.');
								return false;
							}
						}
					}
					return true;	
				}
			},
			simpletabs: false,
			tabs: [
				{name:'Overview', fields: [
					{type:'input', label:'Name', style:{bold:true}, storage:'name', margins: {top:10}},
					{type:'input', label:'Reference', storage:'reference'},
					{type:'combo', label:'Template', select:'index', storage:'template', options: mgFormatObjectArray(templates, {name:function(v){return mgUCFirst(v.name)}}), size: 20},
					{type:'divider'},
					{type:'input', label:'Location', storage:'location'},
				]},
				{name:'Source', scrollbar: false, fields: [
					{type:'switcher', tabs: mgCreateObjectArray(v.languages, function(v){return mgUCFirst(v)}), storage:'source', fields: [
						{type:'codeeditor', height: 355, mode:'combined', theme:'default', allowfullscreen: true, storage:'source'}
					]}
				]},
				{name:'Meta', scrollbar: false, fields: [
					{type:'switcher', tabs: mgCreateObjectArray(v.languages, function(v){return mgUCFirst(v)}), storage:'meta', fields: [
						{type:'collection', defaultvalues: that.parameters.metadefaultfields, intabs: true, widthname: 120, height: 12, autodetect: true, storage:'metadata'}
					]}
				]},
			]
		});
		
		wnd.show();
	},
	
	/** ----------------------------------------------------------------------------------------------
	  * (dialogsnippet)
	  */
	dialogsnippet: function(item, callback, snippets) {
		// initialize
		var that = this;
		// create item
		var v = !item?{languages:['default'].concat(mgCreateArrayFromObject(this.parameters.languages))}:item;
		// create dialog
		var wnd = mgCreateButtonDialog({
			blocker: true,
			height: 480,
			width: 500,
			values: v,
			fields: [
				{type:'input', readonly:!item?false:true, label:'Name', style:{bold:true}, storage:'name', margins: {top:10}, size: 18},
				{type:'divider'},
				{type:'switcher', tabs: mgCreateObjectArray(v.languages, function(v){return mgUCFirst(v)}), storage:'source', fields: [
					{type:'codeeditor', height: 290, mode:'combined', theme:'default', allowfullscreen: true, storage:'source'}
				]}
			],
			buttons: $.extend({}, {'{%ButtonCancel}':false}, !item?{'{%ButtonAdd}':true}:{'{%ButtonSave}':true}),
			events: {
				onsuccess: function(v) {
					// run callback
					if(typeof(callback)=="function") {
						callback(v);
					}
				},
				onvalidation: function(v) {
					if(v.name.length==0) {
						mgMessageDialog('Warning!', 'Please enter a valid name for the snippet.');
						return false;
					} else {
						// verify request only if added
						if(!item) {
							if($.inJSON(snippets, ['name', v.name], true)) {
								mgMessageDialog('Error!', 'The entered snippet already exists.');
								return false;
							}
						}
					}
					return true;
				}
			}
		});
		wnd.show();	
	},
	
	/** ---------------------------------------------------------------------------------------------
	  * (dialoglanguage)
	  */
	dialoglanguage: function(item, callback, languages) {
		// initialize
		var that = this;
		// create item
		if(!item) {
			item = {id:false};
			$.each(mgCreateArrayFromObject(this.parameters.languages), function(index, l) {
				item[l] = "";
			});
		}
		// create dialog
		var wnd = mgCreateButtonDialog({
			blocker: true,
			height: 480,
			width: 500,
			values: item,
			fields: [
				{type:'input', readonly:!item.id?false:true, label:'Id', style:{bold:true}, storage:'id', margins: {top:10}},
				{type:'input', label:'Related', storage:'related'},
				{type:'combo', label:'Literal', options:{false:'False', true:'True'}, storage:'literal'},
				{type:'divider'},
				{type:'switcher', tabs: mgCreateObjectArray(this.parameters.languages, function(v){return mgUCFirst(v)}), storage:'source', fields: [
					{type:'codeeditor', height: 290, mode:'combined', theme:'default', allowfullscreen: true, storage:'source'}
				]}
			],
			buttons: $.extend({}, {'{%ButtonCancel}':false}, !item?{'{%ButtonAdd}':true}:{'{%ButtonSave}':true}),
			events: {
				onsuccess: function(v) {
					// run callback
					if(typeof(callback)=="function") {
						callback(v);
					}
				},
				onvalidation: function(v) {
					if(v.id.length==0) {
						mgMessageDialog('Warning!', 'Please enter a valid id.');
						return false;
					} else {
						// verify request only if added
						if(!item) {
							if($.inJSON(languages, ['id', v.id], true)) {
								mgMessageDialog('Error!', 'The entered snippet already exists.');
								return false;
							}
						}
					}
					return true;
				}
			}
		});
		wnd.show();	
	},
	
	/** ----------------------------------------------------------------------------------------------
	  * (dialogemail)
	  */
	  
	dialogemail: function(item, callback, contents, templates) {
		// initialize
		var that = this;
		// set localizations
		var item = !item?{languages:['default'].concat(mgCreateArrayFromObject(this.parameters.languages))}:item,
			v = item;		
		// get real item
		var wnd = mgCreateActionPageDialog({
			values: item,
			events: {
				onsuccess: function(values) {
					if(typeof(callback)=="function") {
						callback(values);
					}
				},
				onvalidation: function(v) {
					if(v.name.length==0) {
						mgMessageDialog('Warning!', 'Please enter a valid name for the content.');
						return false;
					} else {
						// verify request only if added
						if(!item) {
							if($.inJSON(contents, ['name', v.name], true)) {
								mgMessageDialog('Error!', 'The entered content already exists.');
								return false;
							}
						}
					}
					return true;	
				}
			},
			simpletabs: false,
			tabs: [
				{name:'Overview', fields: [
					{type:'input', label:'Name', style:{bold:true}, storage:'name', margins: {top:10}},
					{type:'divider'},
					{type:'combo', label:'Type', storage:'type', options: ['HTML', 'Text'], size: 30},
					{type:'divider'},
					{type:'combo', label:'Template', select:'index', storage:'template', options: mgFormatObjectArray(templates, {name:function(v){return mgUCFirst(v.name)}}), size: 30},
					{type:'combo', label:'Sender', storage:'sender', options: that.parameters.emailsenders, size: 30},
					
				]},
				{name:'Subject', scrollbar: false, fields: [
					{type:'switcher', tabs: mgCreateObjectArray(v.languages, function(v){return mgUCFirst(v)}), storage:'subject', fields: [
						{type:'input', label:'Subject', storage:'source', margins:{top:10}}
					]}
					
				]},
				{name:'Content',  fields: [
					{type:'switcher', tabs: mgCreateObjectArray(v.languages, function(v){return mgUCFirst(v)}), storage:'content', fields: [
						{type:'codeeditor', height: 355, mode:'combined', theme:'default', allowfullscreen: true, storage:'source'}
					]}
				]},
			]
		});
		
		wnd.show();
	},	
};