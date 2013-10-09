/**
  * (mg)framework Version 5.0
  *	Copyright (c) 1999-2011 eikonlexis LLC. All rights reserved.
  *
  * This program is protected by copyright laws and international treaties.
  * Unauthorized reproduction or distribution of this program, or any 
  * portion thereof, may result in serious civil and criminal penalties.
  *
  * Module 		Validation Library (JavaScript)
  */
  
var VALIDATE_NONE = "none",
	VALIDATE_EMAIL = "email",
	VALIDATE_PHONE = "phone",
	VALIDATE_CUSTOM = "custom",
	VALIDATE_NUMERIC = "numeric";
  
/** 
  * mgIsEMail
  */
function mgIsEMail(email) {
	var filter = /^([a-zA-Z0-9_.-])+@(([a-zA-Z0-9-])+.)+([a-zA-Z0-9]{2,4})+$/;
	return filter.test(email);
};

/** 
  * mgIsPhone
  */
function mgIsPhone(phone, settings) {
	var filter = /^(\()?\d{3}(\))?(-|\s)?\d{3}(-|\s)\d{4}$/;
	return filter.test(phone);
};

/**
  * mgIsNumeric
  */
var mgIsNumeric = function(d) {
	try {
		return !isNaN(parseInt(d));
	} catch(e) {
		return false;
	}
};
