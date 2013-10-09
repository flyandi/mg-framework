/**
  * Manager Module Script
  * @Id: manager.affiliates
  */
 
/** mgResourceScript */ 
var mgResourceScript = {
	/** (run) */
	process: function(action) {
		// initialize 
		var that = this;
		// parameters
		this.parameters = this.call('parameters');
		// switch by action
		switch(action) {

			// (Default)
			default:
				this.returncontent([
					{type:'smallgraph', label:'Select date', data: [0, 5, 50, 30, 20, 60, 40, 30, 100, 60, 40, 30, 10, 20, 100]},
					{type:'grid', columns: this.call('getgrid'), alwayssortable: true, events: {
						onvalues: function(page, itemsperpage, sort, sortmode, search) {
							return that.call('get', {requestpage: page, itemsperpage: itemsperpage, sort: sort, sortmode: sortmode, search: search});
						},
						onrowdoubleclick: function(item, grid) {
							that.dialog(item, function(uitem) {
								that.call('set', {affiliate: uitem});
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
								that.call('set', {affiliate: uitem});
								grid.trigger("update");
							});
						},
						ondelete: function(items, grid) {
							// parse id's
							var deleteids = {};
							$.each(items, function(index, params){deleteids[params.idstring]=params.idusername});
							mgCreateDeleteDialog(deleteids, function() {
								that.call('removeclients', {items: deleteids});
								grid.trigger("update");
							});
						}
					}}
				], false, function() {
					
				});
				break;
		}
	},
	
	
	/** --------------------------------------------------------------------------------------------------------
		Dialogs
		-------------------------------------------------------------------------------------------------------- **/		
	dialog: function(item, callback) {
		// create dialog
		var that = this, wnd = mgCreateButtonDialog({
			width: 380,
			height: 420,
			blocker: true,
			values: item?item:{},
			events: {
				onsuccess: function(values) {
					if(typeof(callback)=="function"){
						callback(values);
					}
				},
			},
			buttons: {
				'Cancel': false,
				'Ok': true
			},
			fullarea: true,
			fields: [
				{type:'crmheader', subheader:'LawSmart Affiliate', header:'Provision New Affiliate', icon:'book'},
				{type:'crmsections', automargin: false, fixed: true, height: 380, collapsable: true, sections: {
					'Affiliate Details': [
						{type:'input', label:'Name', storage:'name', hint: 'Enter a descriptive name for this affiliate'},
						{type:'combo', label:'Type', options: that.parameters.affiliatetypes, select: 1, storage:'idtype', size: 20},
					],					
					'Service Details': [
						{type:'combo', label:'Activate', options:['No', 'Yes'], select: 1, storage:'active', size: 20},
					]
				}},
			]
		});
		wnd.show();
	},	
};