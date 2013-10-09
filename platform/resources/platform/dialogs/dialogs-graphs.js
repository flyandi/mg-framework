/**
  * (mg)framework Version 5.0
  *	Copyright (c) 1999-2012 eikonlexis LLC. All rights reserved.
  *
  * This program is protected by copyright laws and international treaties.
  * Unauthorized reproduction or distribution of this program, or any 
  * portion thereof, may result in serious civil and criminal penalties.
  *
  * Module 		Manager Class
  */

/** (constants) */
var GRAPHSTYLE_BAR = 0,
	GRAPHSTYLE_LINE = 1;
	
jQuery.fn.mgSVGPlotLine = function(settings) {
	var settings = $.extend({}, {legend: false, lines: false, linewidth: 1, linecolor: '#aaaaaa', fullgraph: false, circleclick: false, circles: false, classname: false, strokecolor: '#0066CC', strokewidth: 2, data: [], width: false, height: false}, settings);
	return this.each(function() {
		var target =$(this),
			width = settings.width?settings.width:target.width(),
			height = settings.height?settings.height:target.height();
		// create container
		target.css({width: width, height: height}).svg({onLoad: function(svg) {
			if(settings.data.length>0) {
				var ay = 0, ax = 0;
				if(settings.fullgraph) {
					var ow = width;
					width -= 10;
					height -= 10;
					ay = 5;
					ax = 5;
				}
				// draw lines
				if(settings.lines) {
					var lh = Math.round(height / (settings.lines-1)),
						g = svg.group({height: 1, stroke: settings.linecolor, strokeWidth: settings.linewidth});
					for(var i=0;i<settings.lines;i++) {
						svg.line(g, 0, ay + (i * lh), ow, ay + (i * lh));
					}
					if(settings.legend) {
						settings.legend.find("div").each(function() {
							$(this).css("margin-top", lh - 14);
						});
						settings.legend.find("div:first").css("margin-top", ay + 12);
					}
				}
				// draw graph				
				var max = Math.max.apply(null, settings.data),
					sx = Math.round(width / (settings.data.length-1));
					path = [],
					g = svg.group({stroke: settings.strokecolor, strokeWidth: settings.strokewidth});
					x = ax, ox = ax, oy = 0;
				$.each(settings.data, function(i, value) {
					var y= height - Math.round((value * height) / max) + ay;
					if(i>0) {
						svg.line(g, ox, oy, x, y);
					}
					if(settings.circles) {
						var circle = svg.circle(x, y, 3, {style: 'cursor:pointer', fill: settings.strokecolor, stroke: settings.strokecolor, strokeWidth: 1});
						if(typeof(settings.circleclick) == "function") {
							$(circle).click(function() {
								settings.circleclick(circle, value, {x: x, y: y});
							});
						}
					};
					oy = y;
					ox = x;
					x += sx;
				});
			}
		}});
	});
};

/** (mgGraphLegendValues) */
var mgGraphLegendValues = function(data, limit, reversed) {
	var result = [], data = mgArrayUnique(mgCloneArray(data));
	if(data.length>0) {
		result.push(mgArrayMax(data)); 
		var n = mgArraySliceFirstLast(data),
			p = mgArrayDivide(n, limit);
		$.each(p, function(index, a) {
			result.push(mgArrayAverage(a));
		});
		result.push(mgArrayMin(data));
	}
	result = mgArraySortInteger(result);
	return reversed?result:result.reverse();
};

/**s
  * (graph) provides a full graph
  */
mgDialogFieldControls.graph = function(controller, item, params, index) {
	// create params
	var that = this, 
		params = $.extend({}, {width: controller.parentoptions.parentwidth, action: false, legendwidth: 100, legend: true, data: false}, params);
	// add class
	item.disableSelection('default').addClass("graph normal -corner-all-small");
	// create legend
	if(params.legend) {
		var legend = $("<div></div>").addClass("legend").css({width: params.legendwidth}).appendTo(item);
		$.each(mgGraphLegendValues(params.data, 5), function(index, value) {
			$("<div></div>").append(value).appendTo(legend);
		});
	}
	// create mini graph
	$("<div></div>").addClass("canvas").mgSVGPlotLine({
		fullgraph: true,
		circles: true,
		circleclick: function(o, value, coordinates) {
			alert(value);
			
		},
		lines: 7,
		legend: legend,
		data: params.data,
		width: parseInt(params.width) - (params.legend?params.legendwidth:0) - 50,
		height: 160,
		strokewidth: 2
	}).appendTo(item);
	// add clear
	item.append(mgClear());
	// return item
	return item;
};
	
/**
  * (smallgraph) simple inline graph widget
  */
mgDialogFieldControls.smallgraph = function(controller, item, params, index) {
	// create params
	var that = this, 
		params = $.extend({}, {compact: false, action: false, buttonize: false, legend: false, style: GRAPHSTYLE_LINE, data: false, label: false, subtitle: false, labeloptions: false}, params);
	// add class
	item.disableSelection(params.buttonize?'pointer':'default').addClass("smallgraph -corner-all-small").addClass(params.compact?"compact":false).css({width: params.compact?80:230});
	// create legend
	if(!params.compact&&params.legend) {
		var legend = $("<div></div>").addClass("legend").appendTo(item);
		$.each(mgGraphLegendValues(params.data, 2), function(index, value) {
			$("<div></div>").append(value).appendTo(legend);
		});
	}
	// create mini graph
	$("<div></div>").addClass("canvas").mgSVGPlotLine({
		data: params.data,
		width: params.compact?80:100,
		height: params.compact?30:50,
		strokewidth: params.compact?1:2
	}).appendTo(item);
	// add label
	var label = $("<div></div>").addClass("label").appendTo(item), lv = params.label;
	if(params.labeloptions) {
		var lo = $.extend({}, {showtotal: false}, params.labeloptions);
		if(lo.showtotal) {
			lv = mgFormatNumber(mgArraySum(params.data));
		}
	}
	label.append($("<div></div>").append(lv));
	// add sublabel
	if(params.sublabel) {
		label.append($("<span></span>").append(params.sublabel));
	}
	// add button
	if(params.buttonize) {
		item.addClass("buttonize").bind('click touchend', params.action);
	}
	// add clear
	item.append(mgClear());
	// return item
	return item;
};