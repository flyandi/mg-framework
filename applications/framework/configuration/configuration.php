<?php
	/* 
		(mg)framework Version 5.0
		
		Copyright (c) 1999-2011 eikonlexis LLC. All rights reserved.
		
		This program is protected by copyright laws and international treaties.
		Unauthorized reproduction or distribution of this program, or any 
		portion thereof, may result in serious civil and criminal penalties.
	
		Module 		Configuration 
	*/
	
	# -----------------------------------------------------------------------------------------------
	# Framework Configuration
	define("FRAMEWORK_FORCECHROMEFRAME", true);		// Google Frame support for IE
	define("FRAMEWORK_URLAFTERLOGOUT", false);		// use default (HTTP_ROOT)
	
	// Caching Options, can be either internal, apc or memcache
	define("FRAMEWORK_CACHING_METHOD", "internal");		
	
	// Maintenance Mode
	define("FRAMEWORK_MAINTENANCE", false);
	define("FRAMEWORK_MAINTENANCE_ALLOWED" , "76.212.176.113");
	
	# -----------------------------------------------------------------------------------------------
	# Project Configuration
	
	// Project Identification, used for various include files
	define("PROJECT_ID", "framework");
	
	// Project Version
	define("PROJECT_VERSION", "1.0");				
	
	// Project Resources
	define("APPLICATION_RESOURCES", "default");
	
	// Project Defaults
	define("PROJECT_DEFAULT_LANGUAGE", LANGUAGE_DEFAULT);	// default language = english
	
	// Project Localized
	define("PROJECT_LOCALIZED", true);	
	define("PROJECT_LOCALIZED_LANGUAGES", "english");
	
	# -----------------------------------------------------------------------------------------------
	# Image Creation
	
	// Allows to embeed a signature into pictures to indicate the source
	// useful for search engines
	define("PROJECT_IMAGE_EMBEEDSIGNATURE", true);
	define("PROJECT_IMAGE_SOURCE", "http://www.babynotify.com");
	define("PROJECT_IMAGE_COPYRIGHT", "Copyright (c) 2012 BabyNotify.com");
	
	# -----------------------------------------------------------------------------------------------
	# Analytics
	define("PROJECT_GA_ACCOUNT", "UA-6549869-14");
	
	# -----------------------------------------------------------------------------------------------
	# Server Configuration
	define("SERVER_ID", 0);			// useful for balance loading identification
	
	# -----------------------------------------------------------------------------------------------
	# Security Configuration
	define("SECURITY_KEY", "89C0kc902j1C3u49C932");
	
	
	# -----------------------------------------------------------------------------------------------
	# SMTP Settings
	define("SMTP_HOST", "babynotify.com"); 
	define("SMTP_PORT", 587);
	define("SMTP_AUTH_USER", "support@babynotify.com");
	define("SMPT_AUTH_PASSWORD", "summer28");
	define("SMPT_AUTH", true);
	
	
	# -----------------------------------------------------------------------------------------------
	# Define E-Mail Addresses
	
	define("EMAIL_FROM_NAME", "BabyNotify");
	define("EMAIL_FROM_SUPPORT", "support@babynotify.com");
	define("EMAIL_FROM_NOREPLY", "noreply@babynotify.com");	

	# -----------------------------------------------------------------------------------------------
	# Database Configuration
	$config_database = Array(
		DB_HOST	=> "localhost",
		DB_USER	=> "root",
		DB_PASS => "test",
		DB_DB   => "framework",
		DB_ROOT => "mg"
	);	

?>