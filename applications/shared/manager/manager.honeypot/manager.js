/**
  * Manager Module Script
  * @Id: manager.honeypot
  */

var mgResourceScript = {
	/** (definition) */
	REQUEST_BLOCKED: 'blocked',
	REQUEST_TRAFFIC: 'traffic',
	REQUEST_TRAPS: 'traps',
	REQUEST_RULES: 'rules',
	
	/** (run) */
	process: function(action) {
		// initialize API
		API.showerror = true;
		var that = this;
		switch(action) {
			default:
				this.returncontent([
					{type:'html', source: that.readfile("snippets/header.snippet")},
					{type:'tabs', id:'datagrids', callback: function(x) {
						that.__itemtype = that.__requests[x];
						that.currenttab = x;
						that.updatefilters();
					}, tabs: {
						'Blocked': [
							{type:'grid', sortasc: true, cookie:'cmdg', height: 12, columns: this.call('getgrid', {name: that.REQUEST_BLOCKED}), alwayssortable: true, events: {
								onvalues: function(page, itemsperpage, sort, sortmode, search) {
									return that.call('data', {itemtype: that.REQUEST_BLOCKED, requestpage: page, itemsperpage: itemsperpage, sort: sort, sortmode: sortmode, search: search});
								},
								onrowdoubleclick: function(item, grid) {
									that.openitem(that.REQUEST_BLOCKED, item, grid);
								},
								oncellformat: function(column, value, item, row) {
									return that.formatitems(column, value, item, row);
								},
								onadd: function(grid) {
									that.additem(that.REQUEST_BLOCKED, grid);
								},
								ondelete: function(items, grid) {
									that.removeitems(that.REQUEST_BLOCKED, items, grid);
								}
							}}
						],
						'Traffic': [
							{type:'grid', sortasc: true,cookie:'cmdg', height: 12, columns: this.call('getgrid', {name: that.REQUEST_TRAFFIC}), alwayssortable: true, events: {
								onvalues: function(page, itemsperpage, sort, sortmode, search) {
									return that.call('data', {itemtype: that.REQUEST_TRAFFIC, requestpage: page, itemsperpage: itemsperpage, sort: sort, sortmode: sortmode, search: search});
								},
								onrowdoubleclick: function(item, grid) {
									that.openitem(that.REQUEST_TRAFFIC, item, grid);
								},
								oncellformat: function(column, value, item, row) {
									return that.formatitems(column, value, item, row);
								},
								onadd: function(grid) {
									that.additem(that.REQUEST_TRAFFIC, grid);
								},
								ondelete: function(items, grid) {
									that.removeitems(that.REQUEST_TRAFFIC, items, grid);
								}
							}}	
						],
						'Traps': [
							{type:'grid', sortasc: true,cookie:'cmdg', height: 12, columns: this.call('getgrid', {name: that.REQUEST_TRAPS}), alwayssortable: true, events: {
								onvalues: function(page, itemsperpage, sort, sortmode, search) {
									return that.call('data', {itemtype: that.REQUEST_TRAPS, requestpage: page, itemsperpage: itemsperpage, sort: sort, sortmode: sortmode, search: search});
								},
								onrowdoubleclick: function(item, grid) {
									that.openitem(that.REQUEST_TRAPS, item, grid);
								},
								oncellformat: function(column, value, item, row) {
									return that.formatitems(column, value, item, row);
								},
								onadd: function(grid) {
									that.additem(that.REQUEST_TRAPS, grid);
								},
								ondelete: function(items, grid) {
									that.removeitems(that.REQUEST_TRAPS, items, grid);
								}
							}}
						],
						'Rules': [
							{type:'grid', sortasc: true,cookie:'cmdg', height: 12, columns: this.call('getgrid', {name: that.REQUEST_RULES}), alwayssortable: true, events: {
								onvalues: function(page, itemsperpage, sort, sortmode, search) {
									return that.call('data', {itemtype: that.REQUEST_RULES, requestpage: page, itemsperpage: itemsperpage, sort: sort, sortmode: sortmode, search: search});
								},
								onrowdoubleclick: function(item, grid) {
									that.openitem(that.REQUEST_RULES, item, grid);
								},
								oncellformat: function(column, value, item, row) {
									return that.formatitems(column, value, item, row);
								},
								onadd: function(grid) {
									that.additem(that.REQUEST_RULES, grid);
								},
								ondelete: function(items, grid) {
									that.removeitems(that.REQUEST_RULES, items, grid);
								}
							}}
						],
					}}
				], false, function() {
					$("[action=clearcache]").click(function() {
						mgMessageDialog("Clear Cache", false, false, false, {
							position:false,
							height: 470,
							buttons: {
								'{%ButtonCancel}':false,
								'{%ButtonDelete}':true,
							},
							events: {
								onsuccess: function(v) {
									var v = $.extend({}, {expblocks: "true", exptraffic: "true", exptraps: "true"}, v);
									var r = that.call('clear', {cache: v});
									$(".grid").trigger("update");
								}
							},
							scrollbar: true,
							fields: [
								{type:'checkbox', storage:'expblocked', label:'Expired blocked IPs', checked: true},
								{type:'checkbox', storage:'exptraffic', label:'Expired traffic', checked: true},
								{type:'checkbox', storage:'exptraps', label:'Expired traps', checked: true},
								{type:'divider', text:'Temporary'},
								{type:'checkbox', storage:'tmpblocked', label:'Temporary active blocked IPs'},
								{type:'checkbox', storage:'tmptraffic', label:'Temporary active traffic IPs'},
								{type:'checkbox', storage:'tmptraps', label:'Temporary active traps'},
								{type:'divider', text:'Permanent'},
								{type:'label', outline:'warn', text:'Removing Permanent IPs is not recommended.'},
								{type:'checkbox', storage:'pmtblocked', label:'Permanent blocked IPs'},
								{type:'checkbox', storage:'pmttraps', label:'Permanent traps'}
							]
						});
					});
				});
		}
		return true;		
	},
	
	additem: function(type, grid) {
		var that = this;
	  	that.dialog(type, false, function(item) {								
			grid.trigger("update");
		}, true);
	},
	
	openitem: function(type, item, grid) {
		var that = this;
		that.dialog(type, item, function(uitem) {
			grid.trigger("update");
		}, false);
	},	

	removeitems: function(type, items, grid) {
		var that = this, deleteids = {};
		$.each(items, function(index, params){
			deleteids[params.idstring] = sprintf("%s", params.idaddress);
		});
		mgCreateDeleteDialog(deleteids, function(values) {
			that.call('remove', {itemtype: type, items: deleteids});
			grid.trigger("update");
		});
	},	
	
	formatitems: function(column, value, item, row) {
		var that = this;
		switch(column.field) {
			case "stampcreated":
			case "stamptimeout":item.css("color", "#444"); break;
			case "idaddress": item.css({"font-weight":"bold"}); break;
		}
	},	
	
	dialog: function(itemtype, item, callback, add) {
		// initialize
		var that = this;
		// create dialog
		var wnd = mgCreateButtonDialog({
			blocker: true,
			height: 450,
			width: 800,
			padding: 0,
			position:'topcenter',
			values: item?item:{},
			fields: [
				{type:'crmheader', subheader: itemtype, header:'%idaddress%', icon:'world'},
				{type:'columns', distance: 0, columns: [
					{shortlabel: true, fields: [
						{type:'crmsections', automargin: false, collapsable: true, sections: {
							'General': [
								{type:'logic', conditions: [
									{condition: itemtype==that.REQUEST_RULES, isfinal: true, fields: [
										{type:'input', label:'Created', storage:'stampcreated', disabled: true},
									]},
									{condition: true, fields: [
										{type:'input', label:'IP Address', storage:'idaddress', readonly: !add},
										{type:'input', label:'Created', storage:'stampcreated', disabled: true},
										{type:'input', label:'Timeout', storage:'stamptimeout', disabled: true},
										{type:'input', label:'Timeout Seconds', storage:'timeoutseconds'}
									]}
								]}								
							],
							'Other': [
								{type:'logic', conditions: [
									{condition:itemtype==that.REQUEST_RULES, fields: [
										{type:'combo', label:'Type', storage:'idtype', options:['IP', 'User Agent', 'Referer', 'Request']},
										{type:'combo', label:'Rule', storage:'rule', options:['Block', 'Allow']},
										{type:'text', label:'Value', storage:'value'},
									]},									
									{condition:itemtype==that.REQUEST_BLOCKED, fields: [
										{type:'combo', label:'Type', storage:'idtype', options:['Temporary', 'Permanent']},
										{type:'combo', label:'Reason', storage:'reason', options:['Manual', 'User Agent', 'Trap', 'Spammer']},
									]},
									{condition:itemtype==that.REQUEST_TRAFFIC, fields: [
										{type:'input', label:'Referer', storage:'referer'},
										{type:'input', label:'UA', storage:'ua'}
									]},
									{condition:itemtype==that.REQUEST_TRAPS, fields: [
										{type:'combo', label:'Type', storage:'idtype', options:['Request', 'Permanent']},
										{type:'input', label:'Value', storage:'value'}
									]}
								]}
							]
						}}
					]},
					{shortlabel: true, fields: [
						{type:'crmsections', automargin: false, collapsable: true, sections: {
							'Meta': [
								{type:'collection', storage:'meta'}
							]
						}}
					]}
				]}
			],
			buttons: $.extend({}, {'{%ButtonCancel}': false}, add?{'{%ButtonAdd}': true}:{'{%ButtonOk}': true}),
			events: {
				onsuccess: function(v) {
					// set
					var f = that.call('set', {itemtype: itemtype, data: v}, false, true);
					// result
					if(f.result==true) {
						if(typeof(callback)=="function") {
							callback(f.item);
						}
						return true;
					} else {
						mgMessageDialog('Error!', 'The item could not be updated.');
						return false;
					}
				},
				onvalidation: function(v) {
					if(v.idaddress.length==0) {
						mgMessageDialog('Warning!', 'Please enter a valid IP address.');
						return false;
					}
					return true;
				}
			}
		});
		wnd.show();	
		// create		
		wnd.show();
	},	

};