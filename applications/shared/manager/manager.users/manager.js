/**
  * Manager Module Script
  * @Id: manager.user
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
					{type:'grid', columns: this.call('getgrid'), alwayssortable: true, events: {
						onvalues: function(page, itemsperpage, sort, sortmode, search) {
							return that.call('getusers', {requestpage: page, itemsperpage: itemsperpage, sort: sort, sortmode: sortmode, search: search});
						},
						onrowdoubleclick: function(item, grid) {
							that.dialog(item, function(uitem) {
								that.call('setuser', {user: uitem});
								grid.trigger("update");
							});
						},
						oncellformat: function(column, value, item) {
							switch(column.field) {
								case "active": item.css("color", value==1?"green":"red"); break;
							}
						},
						onadd: function(grid) {
							that.dialog(false, function(uitem) {
								var r = that.call('setuser', {user: uitem});
								grid.trigger("update");
							});
						},
						ondelete: function(items, grid) {
							// parse id's
							var deleteids = {};
							$.each(items, function(index, params){deleteids[params.idstring]=params.idusername});
							mgCreateDeleteDialog(deleteids, function() {
								that.call('remove', {items: deleteids});
								grid.trigger("update");
							});
						}
					}}
				]);
		}
		return true;		
	},
	
	/** dialog */
	dialog: function(item, callback) {
		var that = this;
		var wnd = mgCreateActionPageDialog({
			values: item,
			events: {
				onsuccess: function(values) {
					if(typeof(callback)=="function"){
						callback(values);
					}
				},
			},
			buttons: {
				'Delete':function(){ return true;}
			},
			simpletabs: false,
			tabs: [
				{name:'Overview', fields: [
					{type:'input', label:'Username', readonly: (!item)?false:true, style:{bold:true}, storage:'idusername', margins: {top:10}, hinticon: (!item)?false:true, hint:(!item)?false:'Can not be changed'},
					{type:'divider'},
					{type:'combo', label:'Type', options:this.parameters.types, storage:'idtype', size: 20},
					{type:'combo', label:'Role', options:this.parameters.groups, storage:'role', size: 20},
					{type:'combo', label:'Language', options:this.parameters.languages, storage:'idlocalized', size: 20},
					{type:'divider'},
					{type:'combo', label:'Status', options:this.parameters.statusnames, storage:'status', size: 20},
					{type:'combo', label:'Enabled', options:['No', 'Yes'], storage:'active', size: 20},
					{type:'divider'},
					{type:'input', label:'Join Date', readonly: true, storage:'joindate'},
				]},
				{name:'Meta', fullarea: true, fields: [
					{type:'collection', fitalign: true, autodetect: true, storage:'meta', events: {
						oncollection: function(values, resultcallback) {
							mgCreateCollectionDialog(values, resultcallback);
						}
					}}
				]},
				{name:'Security', fields: [
					{type:'divider', text:'Password', style:{bold:true}},
					{type:'buttons', alignwithlabel: true, buttons: [
						{label:'Issue Password', action: function() {
							var p = that.call('token', {user: item, action:'password'});
							if(p&&p.p) {
								mgCreateMessageDialog({
									position:'topcenter',
									offset: {top: 10},
									buttons:typeof(buttons)=="object"?buttons:{'{%ButtonClose}': false},
									fields: [
										{type:'label', header: true, text: 'Password Issued'},
										{type:'label', text: 'The following password was issued for the user.'},
										{type:'input', readonly: true, label:'Password', text: p.p}
									]
								});
							}								
						}}
					]},
					{type:'divider', text:'API Access', style:{bold:true}},
					{type:'input', id:'idtoken', label:'Token', readonly: true, storage:'idtoken'},
					{type:'buttons', alignwithlabel: true, buttons: [
						{label:'Issue Token', action: function() {
							var p = that.call('token', {user: item, action: 'create'});
							if(p&&p.t) {
								$("#idtoken").find("input").val(p.t).trigger("change");
							}
						}},
						{label:'Revoke Token', action: function() {
						var p = that.call('token', {user: item, action: 'revoke'});
							if(p&&p.t) {
								$("#idtoken").find("input").val("").trigger("change");
							}
						}}
					]}
					
				]}
			]
		});
		
		wnd.show();
	}
};