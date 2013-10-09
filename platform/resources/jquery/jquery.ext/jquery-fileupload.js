/**
  * (mg)framework Version 5.0
  *	Copyright (c) 1999-2011 eikonlexis LLC. All rights reserved.
  *
  * This program is protected by copyright laws and international treaties.
  * Unauthorized reproduction or distribution of this program, or any 
  * portion thereof, may result in serious civil and criminal penalties.
  *
  * Module 		JQuery File Upload
  */
  
/*fn.mgFileUpload */
jQuery.fn.selectfile=function(options) {
	/* prepare settings */
	var settings = jQuery.extend({}, {execute: false, form:'selectfile', fallback: false, autoload: true, maxsize: false, accept:false,success: false, failed: false, multiple: false}, options);
	
	/* (support */
	var sp = function() {
		return $("<input type='file'>").get(0).files !== undefined;
	}
	
	/* create hidden input field */
	var fi = $("<input/>").css({cursor:'pointer', display: 'block', opacity:0, width:180, position:'absolute'}).attr({name:'selectfile', type:'file', multiple: (settings.multiple?'true':'false')}).bind({
		'change update': function(e){
			if($.browser.msie&&!sp()) return;			
			e.stopPropagation();
			var data = [], total = this.files.length, completed = 0, exceed = false;
			for(var i=0;i<this.files.length;i++){
				var file=this.files[i],
					fileinfo = {size: file.size, name: file.name, type: file.type, data: false};
				// validate file
				var passed = settings.accept==false?true:(function(){
					var result = false;
					$.each(settings.accept.split(","), function(index, pattern){	
						var p=new RegExp(pattern);
						if(p.test(file.type)){result = true;return false;}
					});
					return result;
				}());
				// check size
				if(settings.maxsize&&file.size>settings.maxsize){
					exceed = true;
				}
				// check validation
				if(!passed) {
					if(typeof(settings.failed)=="function"){settings.failed('mime', fileinfo);}
				} else if(exceed) {
					if(typeof(settings.failed)=="function"){settings.failed('size', fileinfo);}
				} else {
					var reader= new FileReader();
					// assign reader
					reader.onload=function(e){
						fileinfo['data'] = e.target.result;
						data.push(fileinfo);
						completed +=1;
						if(completed>=total&&typeof(settings.success)=="function"){
							settings.success(data.length==1?data[0]:data, !sp());
						}
					}
					// load content
					reader.readAsDataURL(file);
				}
			}
		},
		'click': function(e) {
			if($.browser.msie) {
				var inp = $(this);			
				setTimeout(function() {
					if(!sp()) {
						if(typeof(settings.fallback)=="function") settings.fallback(inp, function(r) {
							if(typeof(settings.success)=="function") {
								settings.success(r, !sp());
							}
						});
					} else {
						inp.trigger("update");
					}
				}, 500);
			}
		}
	});
	/* prepare field */
	$(this).css({position:"relative"}).mousemove(function(e){
		var p=$(this).offset(), x=e.pageX-p.left,y=e.pageY-p.top-10;
		fi.css({left:(x-fi.width()), top:y, 'z-index':99999999});
	}).append($("<form></form>").attr({id:settings.form, name: settings.form}).append(fi));
	// return
	return this;
};