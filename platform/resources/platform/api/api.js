/**
  * (mg)framework Version 5.0
  *	Copyright (c) 1999-2011 eikonlexis LLC. All rights reserved.
  *
  * This program is protected by copyright laws and international treaties.
  * Unauthorized reproduction or distribution of this program, or any 
  * portion thereof, may result in serious civil and criminal penalties.
  *
  * Module 		API Library (JavaScript)
  */


/** Result Code Constants */
var API_OK = 1, 
	API_ERROR = 2, 
	API_AUTHERROR = 3,
	API_AUTHREQUIRED = 4,
	API_NOTFOUND = 5,
	API_NOTSUPPORTED = 6,
	API_MISMATCH = 7,
	API_FAILED = 8,
	API_VERSION = '1.0',
	API_EXPIRED = "--expired",
	API_URL = "/api",
	API_TYPE_JSON = "json",
	API_SHOWLOADER = false;

/** 
  * APILOADER Interface Class
  */

var APILOADER = {
	loader: false,
	
	/* (create) */
	create: function(label) {
		// test
		if($(".-api-loader").length!=0) return;
		// create 
		var label = label?label:'Please wait...';
		// complete
		$("<div></div>").disableSelection().css({opacity:0.9}).addClass("-api-loader -corner-all").append("<span>"+label+"</span>").appendTo('body').show();
	},
	
	/* (remove) */
	remove: function () {
		setTimeout(function() {
			$(".-api-loader").remove();
		}, 500);
	}
}	

	
/** 
  * API Interface Class
  */
var API = {
	/** constants */
	apiversion: API_VERSION,
	lasterror: false,
	lasterrorstring: false,
	lastrequest: false,
	showerror: false,
	
	/** options */		
	options: function(settings) {
		var settings = $.extend({}, {loader: false}, settings);
		// assign
		API_SHOWLOADER = settings.loader;
	},
	
	/** calls api with result formattings */		
	call: function(api, params, raw, showerror) {
		var that = this;
		try{	
			var loader = API_SHOWLOADER?true:false;
			// showloader
			if(loader) {
				APILOADER.create();
			}
			// create params
			var params = $.extend({}, {apiurl: API_URL, api:'none', apiversion:this.apiversion, request:'none', type:API_TYPE_JSON, token:''}, params);
			// find empty arrays and replace them
			var params = mgPrepareObjectForTransfer(params, "");
			// api url
			var apiurl = sprintf('%s/%s', params.apiurl, api);
			// execute 
			this.lastresult = $.ajax({async:false, dataType:'json', url: apiurl, type: 'POST', data: params}).responseText;
			// test result
			if(this.lastresult==API_EXPIRED) {window.stop(); window.location = "/"; return; }
			// clear loader
			if(loader) {
				APILOADER.remove();
			}
			// check raw
			if(raw) return this.lastresult;			
			// parse result
			var result = $.parseJSON(this.lastresult);
			// check result
			if(typeof(result) == 'object' && result != null){
				return result;
			}
		} catch(e){
			// update error
			this.update(API_ERROR, e.message, false);
			// show dialog
			if(showerror||this.showerror) {
				mgCreateErrorDialog({
					stack: API.lastresult,
					message: 'API request error',
					fileName: apiurl,
					lineNumber: 0
				});
			}
		}
		// remove loader
		if(loader) {
			APILOADER.remove();
		}
		// return false
		return false;
	},

	/** updates the interface */		
	update: function() {
		this.lasterror = arguments[0];
		this.lasterrorstring = arguments[1]; 
		this.lastrequest = arguments[2];
	}
};