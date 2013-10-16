/**
  * (mg)framework Version 5.0
  *	Copyright (c) 1999-2011 eikonlexis LLC. All rights reserved.
  *
  * This program is protected by copyright laws and international treaties.
  * Unauthorized reproduction or distribution of this program, or any 
  * portion thereof, may result in serious civil and criminal penalties.
  *
  * Module 		Array
  */

/** (mgArraySortInteger) */  
var mgArraySortInteger = function(a) {
	a.sort(function(a, b) { return a - b;});
	return a;
};

/** (mgArraySort) */  
var mgArraySort = function(a) {
	return a.sort(function(c, b) {return c.value > b.value ? 1 : -1;});
};

/** (mgCloneArray) */  
var mgCloneArray = function(a) {
	return a.slice(0);
};
  
/** (mgArrayUnique)   */  
var mgArrayUnique = function(a) {
	var result = [];
	$.each(a, function(index, v) {
		if(result.indexOf(v) == -1) result.push(v);
	});
	return result;
};
  
/** (mgArrayDivide) divides an array in sub arrays  */  
var mgArrayDivide = function(a, d) {
	var result = [];
	if(a.length!=0&&d!=0) {
		 var m = Math.round(a.length / d);
		 for(var i=0;i<d;i++) {
			result.push(a.slice(i*m, (i*m)+m+1));
		 }
	}
	return result;	
};

/** (mgArraySliceFirstLast)  */
var mgArraySliceFirstLast = function(a) {
	var a = mgCloneArray(a);
	a.splice(0, 1);
	a.splice(a.length-1);
	return a;
};

/** (mgArrayMax) returns the max value of the array */
var mgArrayMax = function(a) {
	return Math.max.apply(null, a);
};

/** (mgArrayMin) returns the min value of the array */
var mgArrayMin = function(a) {
	return Math.min.apply(null, a);
};
  
/** (mgArraySum) calculates the sum of an array */  
var mgArraySum = function(a) {
	var s = 0;
	$.each(a, function(index, n) {
		try {
			s += parseInt(n);
		} catch(e) {
			// no error
		}
	});
	return s;
};


/** (mgArrayRandom) returns a random element */  
var mgArrayRandom = function(a, returnvalue) {
	var i = Math.floor((Math.random()*a.length)+1) - 1;
	if(i<0||i>=a.length) i = 0;
	return returnvalue?a[i]:i;
};
  
/** (mgArrayAverage) calculates the average of an array */
var mgArrayAverage = function(a) {
	return (a.length>0)?Math.round(mgArraySum(a) / a.length):false;
};

/** (mgIsArray) */
var mgIsArray = function(o) {
	return Object.prototype.toString.call(o) === '[object Array]';
};

/** (mgArraySlice) */
var mgArrayPageSlice = function(o, p, ip) {
	return o.slice(p * ip, (p * ip) + ip);
};
