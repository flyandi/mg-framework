/**  * (mg)framework Version 5.0  *	Copyright (c) 1999-2011 eikonlexis LLC. All rights reserved.  *  * This program is protected by copyright laws and international treaties.  * Unauthorized reproduction or distribution of this program, or any   * portion thereof, may result in serious civil and criminal penalties.  *  * Module 		CSV Handling Library (JavaScript)  */  /** (mgParseCSV) converts a CSV string to a javascript array for easier modification */var mgParseCSV = function(data,delimiter){	// get delimiter	var delimiter = delimiter?delimiter:",";	// split data	var lines = data.split(/\r\n|\r|\n/);	// check	if(typeof(lines)=="object"&&lines.length!=0) {			// get first line		var fields = lines[0].splitcsv(delimiter);		// initialize result		var result = [];		// shift lines		lines.shift();		// cycle		$.each(lines, function(index, line) {			// split line			var sl = line.splitcsv(delimiter);			// create rs			var rs = {};			// assign fields			$.each(fields, function(index, field) {				rs[field] = sl[index];			});				// push to result			result.push(rs);		});		// return result		return result;	}	// return error	return false;};