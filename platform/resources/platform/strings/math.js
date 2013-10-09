/**
  * (mg)framework Version 5.0
  *	Copyright (c) 1999-2011 eikonlexis LLC. All rights reserved.
  *
  * This program is protected by copyright laws and international treaties.
  * Unauthorized reproduction or distribution of this program, or any 
  * portion thereof, may result in serious civil and criminal penalties.
  *
  * Module 		Math Library
  */
  
/** (mgCalcBestWidthHeight) */
var mgCalcBestWidthHeight = function(w, h, mw, mh) {
	var r = w / h;
	w = mw;
	h = Math.floor(mw/r);
	if(h > mh) {
		h = mh;
		w = Math.floor(mh*r);
	}
	var zoom = w/h;
	return {width: w, height: h, ratio: r, zoom: zoom.toFixed(1)};
};

/** (mgCalcZoomLevel) */
var mgCalcZoomLevel = function(ow, oh, w, h, p) {
	var z = w/ow;
	return Math.round(z*10)/10;
};