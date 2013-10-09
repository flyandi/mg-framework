/**
  * Manager Module Script
  * @Id: manager.system
  */
var REGISTRY_TYPE_BOOL = 0,
	REGISTRY_TYPE_INTEGER = 1,
	REGISTRY_TYPE_STRING = 2,
	REGISTRY_TYPE_DATE = 3,
	REGISTRY_TYPE_COLLECTION = 4;

var mgResourceScript = {
	/** (run) */
	process: function(action) {
		var that = this;
		switch(action) {
			/** (registry) enables i/o to registry */
			case "registry":
				this.returncontent([
					{type:'label', header:true, text:'{%ManagerModuleHeaderRegistry}'},
					{type:'grid', columns: this.call('getgrid', {name:'registry'}), events: {
						onvalues: function(page, itemsperpage, sort, sortmode, search) {
							return that.call('registry', {requestpage: page, itemsperpage: itemsperpage, sort: sort, sortmode: sortmode, search: search});
						},
						onrowdoubleclick: function(item, grid) {
							that.registrydialog(item.idstring, function() {
								grid.trigger("update");
							});	
							
						},
						oncellformat: function(column, value) {
							switch(column.field) {
								case "value": 
									switch(typeof(value)) {
										case "object": return "(Collection)"; break;
										default: return value;
									}
									break;
							}
						},
						onadd: function(grid) {
							that.registrydialogadd(false, function() {
								grid.trigger("update");
							});
						},
						ondelete: function(items, grid) {
							// parse id's
							var deleteids = [];
							$.each(items, function(index, params){deleteids.push(params.idstring)});
							that.call('registrydeletevalue', {items: deleteids});
							grid.trigger("update");
						},
					}}
				]);
				break;
				
			default:
				alert("There is no system module with name " + action);
		}
		return true;		
	},
	
	/** 
	  * Registry Functions
	  */
	  
	/** dialogregistry */
	registrydialogadd: function(item, f) {	
		var that = this;
		// prepare fields
		var fields = [
			{type:'input', shortlabel: true, label: 'Group', storage:'group'},
			{type:'input', shortlabel: true, label: 'Name', storage:'name'},
			{type:'combo', shortlabel: true, size:15, label: 'Type', options:['Boolean', 'Integer', 'String', 'Date', 'Collection'], storage:'type'},
			{type:'combo', shortlabel: true, size:15, label: 'Mode', options:['Custom', 'System'], storage:'mode'}
		];
		// create dialog
		var wnd = mgCreateButtonDialog({
			blocker: true,
			position:'topcenter',
			width: 300,
			height: 220,
			values: item,
			fields: fields,
			buttons: {'{%ButtonCancel}': false, '{%ButtonSave}':true},
			events: {
				onsuccess: function(values) {
					var result = that.call('registryaddvalue', {fields: values});
					if(!result||!result.id) {
						mgMessageDialog("Error!", "Could not add this value.");
						return false;
					} else {
						that.registrydialog(result.id, f);
						return true;
					}					
				},
				onvalidation: function(values) {
					if(values.name&&values.group&&values.type) {
						return true;
					} else {
						mgMessageDialog("Error", "Please fill out all fields");
						return false;
					}
				}
			}
		});
		wnd.show();
	},
	
	/** dialogregistry */
	registrydialog: function(id, f) {
		// request item
		var item = this.call('registrygetvalue', {id: id}), dlgheight = 170;	
		// verify
		if(!item) return false;
		var that = this;
		// prepare fields
		var fields = [
			{type:'input', shortlabel: true, label: 'Id', text: item.idstring, readonly: true},
			{type:'input', shortlabel: true, label: 'Name', text: item.name, readonly: true}
		];
		// add fields
		switch(parseInt(item.idtype)) {
			case REGISTRY_TYPE_BOOL: fields.push({id:'valinput', size:10, options:{true:'True',false:'False'},type:'combo',shortlabel:true,label:'Value',storage:'value'}); break;
			case REGISTRY_TYPE_COLLECTION: dlgheight = 300; fields.push({id:'valinput', type:'collection', shortlabel: true, label:'Values', storage:'value', widthname: 80}); break;
			case REGISTRY_TYPE_INTEGER: fields.push({id:'valinput', type:'input',shortlabel:true, numeric: true, label:'Value',storage:'value'}); break;
			case REGISTRY_TYPE_DATE: fields.push({id:'valinput', type:'date',shortlabel:true,label:'Value',storage:'value'}); break;
			case REGISTRY_TYPE_STRING:
			default: dlgheight = 250; fields.push({id:'valinput', rows: 6, type:'text',shortlabel:true,label:'Value',storage:'value'}); break;
		}
		
		
		// create dialog
		var wnd = mgCreateButtonDialog({
			blocker: true,
			position:'topcenter',
			width: 400,
			height: dlgheight,
			values: item,
			fields: fields,
			buttons: {'{%ButtonCancel}': false, '{%ButtonSave}':true, '{%ButtonExchange}': function() {
				// create code editor
				mgCreateExchangeDialog(wnd.storages.value, function(v) {
					// set new value
					wnd.storages.value = v;
					// update control
					$("#valinput").trigger("setvalue", [v]);
				});
			}},
			events: {
				onsuccess: function(values) {
					var result = that.call('registryupdatevalue', {fields: values});
					if(typeof(f)=="function") {
						f();
					}		
				}
			}
		});
		wnd.show();
	}
};