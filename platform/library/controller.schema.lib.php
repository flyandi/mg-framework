<?php
	/* 
		(mg)framework Version 5.0
		
		Copyright (c) 1999-2011 eikonlexis LLC. All rights reserved.
		
		This program is protected by copyright laws and international treaties.
		Unauthorized reproduction or distribution of this program, or any 
		portion thereof, may result in serious civil and criminal penalties.
	
		Module 		Schema Library
	*/
	
	# -------------------------------------------------------------------------------------------------------------------
	# Constants
	define("SCHEMA_BASEURL", "http://schema.org/");
	define("SCHEMA_BREADCRUMB", "http://data-vocabulary.org/Breadcrumb");
	
	// (Thing)
	define("SCHEMA_DESCRIPTION", "description"); 
	define("SCHEMA_IMAGE", "image"); 
	define("SCHEMA_NAME", "name"); 
	define("SCHEMA_TITLE", "title");
	define("SCHEMA_WEBSITE", "url"); 
	define("SCHEMA_CATEGORY", "additionalType");

	
	// (Postal Adress)
	define("SCHEMA_POSTALADDRESS", "address,PostalAddress"); 
	define("SCHEMA_STREET", "streetAddress"); 
	define("SCHEMA_ZIP", "postalCode"); 
	define("SCHEMA_CITY", "addressLocality");
	define("SCHEMA_STATE", "addressRegion");
	define("SCHEMA_COUNTRY", "addressCountry");
	
	// (Contactpoint)
	define("SCHEMA_PHONE", "telephone");
	define("SCHEMA_EMAIL", "email");
	
	// (Geo)
	define("SCHEMA_GEOCOORDINATES", "geo,GeoCoordinates");
	define("SCHEMA_LATITUDE", "latitude");
	define("SCHEMA_LONGITUDE", "longitude");
	
	// (Business Hours)
	define("SCHEMA_BUSINESSHOURS", "openingHoursSpecification,OpeningHoursSpecification");
	define("SCHEMA_WEEKDAY", "dayOfWeek");
	define("SCHEMA_OPENS", "opens");
	define("SCHEMA_CLOSES", "closes");
	define("SCHEMA_OPENINGHOURS", "openingHours");
	
	

	# -------------------------------------------------------------------------------------------------------------------
	# (macro) mgSchema, wraps a string into a 
	function mgSchema($data, $property, $wraptag = "span") {
		return Tag($wraptag, Array("itemprop"=>$property), $data);
	}
	
	# -------------------------------------------------------------------------------------------------------------------
	# (macro) mgSchema, wraps a string into a 
	function mgSchemaEx($data, $scope = false, $type = false, $wraptag = "div") {
		return Tag($wraptag, array_merge(
			$scope?Array("itemscope"=>TAG_NOVALUE):Array(), 
			$type?Array("itemtype"=>$type):Array(),
			Array("class"=>"schema")
		), $data);
	}
	
	
	# -------------------------------------------------------------------------------------------------------------------
	# (macro) mgSchema, wraps a string into a 
	function mgSchemaMeta($property, $content) {
		return Tag("meta", Array("itemprop"=>$property, "content"=>$content), false, true);
	}
	
	# -------------------------------------------------------------------------------------------------------------------
	# (macro) mgSchema, wraps a section
	function mgSchemaSection($data, $section, $wraptag = "span") {
		// get property and schema
		$p = explode(",", $section);
		return Tag($wraptag, Array("itemprop"=>$p[0], "itemscope"=>TAG_NOVALUE, "itemtype"=>sprintf("%s%s", SCHEMA_BASEURL, $p[1])), $data);
	}
	
	# -------------------------------------------------------------------------------------------------------------------
	# (macro) mgSchema, wraps a section
	function mgSchemaHour($hour) {
		if(strlen($hour) < 3) {
			$hour = sprintf("%s:00", $hour);
		}
		return trim($hour);
	}
?>