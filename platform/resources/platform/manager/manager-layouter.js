/**
  * (layouter) provides simple interface to construct layouter file
  */
mgDialogFieldControls.layouter = function(controller, item, params, index) {
	// create params
	var that = this, 
		params = $.extend({}, {storage: false, source: false}, params);
	// add class
	item.addClass("layouter");
	// get correct content as json
	var value = params.storage?controller.readstorage(params.storage):(typeof(params.source)=="object"?params.source:{});
	// verify content
	if(typeof(value)!="object") {
		try {
			value = JSON.parse(value);
		} catch(e) {
			value = {};
		}
	};
	// prepare item
	item.data("layout", value);
	// add layout engine
	item.bind({
		updatelayout: function() {
			// clear area
			item.html("");
			// initialize area
			var area = $("<div></div>").addClass("base").disableSelection().appendTo(item),
				layout = $(this).data("layout");
			// cycle layout
			$.each(layout, function(index, obj) {
				switch(obj.type) {
					// (columns)
					case "columns":
						// calculate correct width
						var widths = {'wide':0, 'short': 0};
							totalwidth = controller.parentoptions.parentwidth - (15 * obj.columns.length) - 5;
						alert(totalwidth);
						// get amount
						$.each(obj.columns, function(index, column) {
							widths[column.column.toLowerCase()] += 1;
						});
						// calculate amount
						var wx = Math.round(totalwidth / obj.columns.length), 
							widths = {
								'wide': wx,
								'short': wx
						};
						jd(widths);
						// process columns
						$.each(obj.columns, function(index, column) {
							var col = $("<div></div>").addClass("column -corner-all-small").css("width", widths[column.column]+"px").appendTo(area);
							$.each(column.widget, function(index, w) {
								col.append($("<div></div>").addClass("widget -corner-all-small").data("widget", w).append(w.widget)); 	
							});
						});
						area.append(mgClear());
						break;
					// (any control)
					default:
						area.append($("<div></div>").addClass("widget -corner-all-small").data("widget", obj).append(obj.type)); 
						break;
				}
			});	
			
			
		
		}
	});
	// update layout	
	item.trigger('updatelayout');
	
	// return item
	return item;

};