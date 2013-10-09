/**
  * (mg)framework Version 5.0
  *	Copyright (c) 1999-2011 eikonlexis LLC. All rights reserved.
  *
  * This program is protected by copyright laws and international treaties.
  * Unauthorized reproduction or distribution of this program, or any 
  * portion thereof, may result in serious civil and criminal penalties.
  *
  * Module 		Feature Library (JavaScript)
  */

/** (mgSupportsSVG) */
var mgSupportsSVG = function() {return !!document.createElementNS && !!document.createElementNS('http://www.w3.org/2000/svg', "svg").createSVGRect;};