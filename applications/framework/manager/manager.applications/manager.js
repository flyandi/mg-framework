/**
  * Manager Module Script
  * @Id: manager.applications
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
					{type:'grid', itemheight: 55, height: 5, controlbar: false, columns: this.call('getgrid'), events: {
						onvalues: function(page, itemsperpage, sort, sortmode) {
							return that.call('getapplications');
						},
						onrowdoubleclick: function(item, grid) {
							that.dialog(item, function(uitem) {
								that.call('setuser', {user: uitem});
								grid.trigger("update");
							});
						},
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
			},
			simpletabs: false,
			tabs: [
				{name:'Overview', fields: [
					{type:'input', label:'Application ID', readonly: true, style:{bold:true}, storage:'idapplication', margins: {top:10}},
					{type:'input', label:'Name', storage:'name'},
					{type:'input', label:'Version', storage:'version', size: 10},
					{type:'divider'},
					{type:'combo', label:'Status', options:this.parameters.statusnames, storage:'status', size: 20},
				]},
				{name:'Variables', fullarea: true, fields: [
					{type:'collection', fitalign: true, autodetect: true, storage:'variables', events: {
						oncollection: function(values, resultcallback) {
							mgCreateCollectionDialog(values, resultcallback);
						}
					}}
				]},
				{name:'Connections', fields: [
					{type:'grid', controlactions: {add: true, remove: true}, columns: this.call('getgrid', {name:'database'}), events: {
						onvalues: function(page, itemsperpage, sort, sortmode) {
							return {total: item.connections.length, page: 0, rows: item.connections};
						},
						onrowdoubleclick: function(item, grid) {
							that.dialog(item, function(uitem) {
								that.call('setuser', {user: uitem});
								grid.trigger("update");
							});
						},
					}}
					
				]}
			]
		});
		
		wnd.show();
	}
};