<?php
	/* 
		(mg)framework Version 5.0
		
		Copyright (c) 1999-2011 eikonlexis LLC. All rights reserved.
		
		This program is protected by copyright laws and international treaties.
		Unauthorized reproduction or distribution of this program, or any 
		portion thereof, may result in serious civil and criminal penalties.
	
		Module 		Constants
	*/
	
	# -------------------------------------------------------------------------------------------------------------------
	# Framework Constants
	define("FRAMEWORK", "@framework");
	define("FRAMEWORK_LOCALIZED", "@frameworklocalized");
	define("FRAMEWORK_SESSION", "@frameworksession");
	
	// Controllers
	define("FRAMEWORK_DEFAULTCONTROLLERS", "database,module,user,template,smtp,adopter");	
	
	// Framework Project Constants
	define("FRAMEWORK_PATH", "../../platform/");
	define("FRAMEWORK_RESOURCEPATH", sprintf("%sresources", FRAMEWORK_PATH));
	define("FRAMEWORK_DATABASEPATH", sprintf("%sdatabases", FRAMEWORK_PATH));
	define("FRAMEWORK_CRON_LOCK", "../../platform/cron.lock");
	define("FRAMEWORK_ASSETS_PATH", "assets/");	
	
	// Framework Application Constants
	define("FRAMEWORK_APPLICATION_PATH", "../applications/");
	define("FRAMEWORK_APPLICATION_DEFAULT", "default");
	define("FRAMEWORK_APPLICATION_BOOTXML", "application.xml");
	define("FRAMEWORK_APPLICATION_ICON", "application.png");
	define("FRAMEWORK_APPLICATION_CONFIGURATION", "%s/configuration/configuration.php");
	define("FRAMEWORK_APPLICATION_SOURCE", "%s/application.php");
	define("FRAMEWORK_APPLICATION_CLASS", "mgApplication");
	
	
	# -------------------------------------------------------------------------------------------------------------------
	# Shared
	define("SHARED_PATH", "../shared/");
	define("LAST_PATH", "lastpath");
	define("PUBLIC_PATH", "public/");
	
	# -------------------------------------------------------------------------------------------------------------------
	# Library
	define("LIBRARY_PATH", "library/");
	define("LIBRARY_CONTROLLER", "controller.%s.lib.php");
	
	# -------------------------------------------------------------------------------------------------------------------
	# Content constants
	define("CONTENT_ANY", "any");						// Content general, when the user is either logged in or not
	define("CONTENT_UNSECURED", "unsecured");			// Content when the user isn't logged in (Sign-Up, Sign-In, etc)
	define("CONTENT_SECURED", "secured");				// Content when the user has logged in
	define("CONTENT_API", "api");						// API interface commands
	define("CONTENT_CRON", "cron");						// CRON interface
	define("CONTENT_PERSONALITY", "pc");				// Personality Content
	
	# -------------------------------------------------------------------------------------------------------------------
	# Components
	define("COMPONENT_MANAGER", "manager");
	define("COMPONENT_PRODUCTS", "products");
	define("COMPONENT_YOUTUBE", "youtube");
	define("COMPONENT_HONEYPOT", "honeypot");
	
	
	# -------------------------------------------------------------------------------------------------------------------
	# Form Post constants
	define("FORM_POST", "__post");
	define("FORM_ISPOST", "true");	
	
	# -------------------------------------------------------------------------------------------------------------------
	# HTTP constants
	define("HTTP_ROOT", "/");							// HTTP Root
	define("HTTP_200", "200 Ok");					
	define("HTTP_403", "403 Forbidden");					
	define("HTTP_404", "404 Not Found");					
	define("HTTP_500", "500 Internal Server Error");	
		
	
	# -------------------------------------------------------------------------------------------------------------------
	# Option constants
	define("DEFAULTVALUE", "default");					// Default
	define("TEMPLATE", "template");						// Template
	define("CALLBACK", "callback");						// Callback	
	define("CONTENT", "content");						// Template/Page Content
	define("OUTPUT", "output");							// Output
	define("INCLUDES", "includes");						// Includes
	define("META", "meta");								// Meta Tags
	define("DOCUMENTTITLE", "documenttitle");			// Document Title
	define("DOCUMENTCLASS", "documentclass");			// Sets a specific document class
	define("CONTENTTYPE", "content-type");				// Content Type
	define("HEADER", "header");							// Header
	
	# -------------------------------------------------------------------------------------------------------------------
	# Headers constants
	define("HEADER_NOCOOKIES", "headernocookies");	
	define("HEADER_FORCECACHING", "headerforcecaching");
	
	# -------------------------------------------------------------------------------------------------------------------
	# Templates constants
	
	define("TEMPLATE_FILENAME", "%s.template");			// Filename template for Templates
	define("TEMPLATE_PATH", "templates/");				// Directory for Templates
	define("TEMPLATE_BLANK", "blank");					// Blank
	define("TEMPLATE_INDEX", "index");					// Template for Index page
	define("TEMPLATE_MANAGER", "manager");				// Template for Index page
	define("TEMPLATE_ERROR", "error");					// Error Template
	define("TEMPLATE_MESSAGE", "message");				// Message Template (default)
	define("TEMPLATE_TYPE_PAGE", "page");				// Template Type Page
	define("TEMPLATE_TYPE_FILE", "file");				// Template Type File
	define("TEMPLATE_TYPE_FORM", "form");				// Template Type Form
	
	define("TEMPLATE_FIELD_BEGIN", "{");				// begin field delimiter
	define("TEMPLATE_FIELD_END", "}");					// end field delimiter
	define("TEMPLATE_FIELD_DEFAULTSEPERATOR", ",");		// seperator for default value
	define("TEMPLATE_FIELD_ACTIONSEPERATOR", ":");		// seperator for actions
	define("TEMPLATE_FIELD_TRANSLATE", "%");			// Translate Indicator
	define("TEMPLATE_FIELD_ACTION", "&");				// Advance Action	
	define("TEMPLATE_FIELD_LIST", "#");					// Field is List
	define("TEMPLATE_FIELD_CALLBACK", "@");				// Field is Callback
	define("TEMPLATE_FIELD_SYSTEM", "$");				// Field is a system field
	define("TEMPLATE_FIELD_OPTION", "*");				// Field is a option field
	define("TEMPLATE_FIELD_RESOURCE", "^");				// Field is a resource request
	
	# -------------------------------------------------------------------------------------------------------------------
	# E-Mail Constants
	define("EMAIL_PATH", "email/");
	define("EMAIL_SIGNUP", "signup");
	define("EMAIL_PASSWORDRESET", "passwordreset");
	
	
	# -------------------------------------------------------------------------------------------------------------------
	# Content constants
	define("CONTENT_FILENAME", "%s.content");			// Filename template for Content files
	define("CONTENT_PATH", "content/");					// Directory for Content
	define("CONTENT_DEFAULT", "index");					// Default content
	define("CONTENT_SIGNUP", "signup");					// sign up
	define("CONTENT_SIGNIN", "signin");					// sign in
	define("CONTENT_AUTHTOKEN", "authorizetoken");		// authorize token
	
	# -------------------------------------------------------------------------------------------------------------------
	# Mobile constants
	define("MOBILE_PATH", "mobile/");					// Mobile Path
	
	# -------------------------------------------------------------------------------------------------------------------
	# Snippet constants
	define("SNIPPET_PATH", "snippet/");					// Directory for Content	
	
	# -------------------------------------------------------------------------------------------------------------------
	# Output constants
	define("OUTPUT_HTML", "html");						// Output is HTML
	define("OUTPUT_RAW", "raw");						// Output is Raw (anything)
	
	# -------------------------------------------------------------------------------------------------------------------
	# Action constants
	define("ACTION_SIGNIN", CONTENT_SIGNIN);			// sign in handler
	define("ACTION_SIGNUP", CONTENT_SIGNUP);			// sign up handler
	define("ACTION_AUTHTOKEN", CONTENT_AUTHTOKEN);		// Token Authorization
	
	# -------------------------------------------------------------------------------------------------------------------
	# Localized constants
	define("LOCALIZED_PATH", "localized/");	

	# -------------------------------------------------------------------------------------------------------------------
	# Predefined Requests
	define("REQUEST_DEFAULT", "");						// Default request
	define("REQUEST_SIGNIN", CONTENT_SIGNIN);			// Request for SignIn routine
	define("REQUEST_SIGNUP", CONTENT_SIGNUP);			// Request for SignUp routine
	define("REQUEST_VERIFY", "verify");					// Request for User Verification
	define("REQUEST_CRON", "cron");						// Request for Cron
	define("REQUEST_API", "api");						// Request for API access
	define("REQUEST_LOGOUT", "logout");					// Request for User Logout
	define("REQUEST_CACHE", "cache");					// Request for Cache Access
	define("REQUEST_CACHEDYNAMIC", "cached");			// Request for Dynamic Cache Access
	define("REQUEST_RESOURCES", "resources");			// Request for Resources
	define("REQUEST_LOCALIZE", "localize");				// Request to switch language
	define("REQUEST_MANAGER", "manager");				// Request for Manager
	define("REQUEST_OPTION", "option");					// Request a option
	define("REQUEST_APPLICATION", "application");		// Application Request
	define("REQUEST_CONTENT", "content");				// Content Request
	define("REQUEST_EXTENSION", "extension");			// Extension Request
	define("REQUEST_HTTP301", "http-301");				// 301 redirect
	define("REQUEST_HTTP302", "http-302");				// 302 redirect
	define("REQUEST_HTTP303", "http-303");				// 303 redirect
	define("REQUEST_HTTP403", "http-403");				// 403 error
	define("REQUEST_HTTP404", "http-404");				// 404 error
	define("REQUEST_MESSAGE", "message");				// Message
	define("REQUEST_ASSETS", "assets");					// assets call
	define("REQUEST_ROBOTS", "robots.txt");				// robots.txt
	define("REQUEST_SITEMAP", "sitemap.xml");			// sitemap.xml
	define("REQUEST_SITEMAPGZ", "sitemap.xml.gz");		// sitemap.xml.gz
	define("REQUEST_FAVICON", "favicon.png");			// FavIcon PNG Format
	define("REQUEST_FAVICONICO", "favicon.ico");		// FavIcon ICO Format
	define("REQUEST_ROOT", "root");						// Root Request (/)
	
	
	# -------------------------------------------------------------------------------------------------------------------
	# Database Configuration constants
	define("DB_HOST", "db_host");						// Database host
	define("DB_USER", "db_user");						// Database user
	define("DB_PASS", "db_pass");						// Database password
	define("DB_DB", "db_db");							// Database name
	define("DB_ROOT", "db_root");						// Database root identifier
	define("DB_VALUES_GLOBAL", 1);						// used for forms
	define("DB_VALUES_CUSTOM", "custom");				// used for forms
	
	# -------------------------------------------------------------------------------------------------------------------
	# User constants
	define("USER_SIGNIN_CREDENTIALS", 1);				// Sign-In mode with credentials (username/password)
	define("USER_SIGNIN_TOKEN", 2);						// Sign-In mode with token (token)	
	define("USER_SIGNIN_APIAUTH", 3);					// Sign-In mode in APIAUTH
	define("USER_SIGNUP_APIAUTH", 4);					// Sign-Up mode in APIAUTH
	define("USER_SIGNUP", 5);							// Sign-Up
	define("USER_META_UPDATE", "metaupdate");			// Constant for Meta Update
	
	# -------------------------------------------------------------------------------------------------------------------
	# Roles	
	define("ROLE_UNVERIFIEDUSER", 0);					// Unverified user
	define("ROLE_USER", 1);								// Normal user (verified)
	define("ROLE_PREMIUMUSER", 2);						// Premium User
	define("ROLE_ADMINISTRATOR", 9999);					// Administrator user
	define("ROLE_MANAGER", 5000);						// Manager Role
	define("ROLE_DEVELOPER", 8000);						// Developer Role
	define("ROLE_CRON", 10000);							// CRON Role
	
	# -------------------------------------------------------------------------------------------------------------------
	# Language constants
	define("LANGUAGE_PATH", "language/");				// Directory for Language files
	define("LANGUAGE_FILENAME", "%s.language.xml");		// Filename template for Language file
	define("LANGUAGE_ENGLISH", "english");				// Language: English
	define("LANGUAGE_GERMAN", "german");				// Language: German
	define("LANGUAGE_SPANISH", "spanish");				// Language: Spanish
	define("LANGUAGE_DEFAULT", LANGUAGE_ENGLISH);		// Default Language
	define("LANGUAGE_FIELD", TEMPLATE_FIELD_TRANSLATE);	// Language default

	# -------------------------------------------------------------------------------------------------------------------
	# Field	
	define("FIELD_TYPE_USERNAME", "username");			// Form Field Type username	
	
	# -------------------------------------------------------------------------------------------------------------------
	# Extension constants
	define("EXTENSION_FILENAME", "extension/%s.%s.php");	// Extension filename template
	define("EXTENSION_RESULT_ERROR", 1);				// Extension returns an error
	define("EXTENSION_RESULT_RAW", 2);					// returns a raw format
	define("EXTENSION_RESULT_CONTENT", 3);				// returns normal content
	
	# -------------------------------------------------------------------------------------------------------------------
	# Processing constants
	define("PROCESS_PUBLISH", 1000);
	define("PROCESS_BUILDER", 1001);
	define("PROCESS_BACKGROUND", 1002);
	define("PROCESS_SCHEDULED", 1003);
	define("PROCESS_SURFACE", 1004);
	
	# -------------------------------------------------------------------------------------------------------------------
	# Result
	define("RESULT_OK", "true");						// result is ok
	define("RESULT_FAILED", "false");					// result failed
	define("RESULT_EXPIRED", "--expired");				// result expired
	
	# -------------------------------------------------------------------------------------------------------------------
	# Default Paths
	define("PATH_TEMPORARY", "../../temporary/");
	define("PATH_LOGS", "../../log/");
	
	# -------------------------------------------------------------------------------------------------------------------
	# API AUTH Interface
	define("APIAUTH", "apiauth");
	define("APIAUTH_ISAPI", "true");
	define("APIAUTH_RESULT", "result");
	define("APIAUTH_TOKEN", "apitoken");
	define("APIAUTH_AUTHORIZEONLY", "apiauthonly");	
	define("APIAUTH_KEY", "apiauthkey");	
	define("APIAUTH_SECRET", "apiauthsecret");	
	
	define("APIAUTH_RESULT_FAILED", 1);
	define("APIAUTH_RESULT_INCOMPLETE", 2);
	define("APIAUTH_RESULT_USEREXISTS", 3);
	define("APIAUTH_RESULT_SIGNEDUP", 4);
	
	# -------------------------------------------------------------------------------------------------------------------
	# Statistics
	define("STATS_PAGELOAD", "pageload");
	define("STATS_PAGEERROR", "pageerror");
	
	# -------------------------------------------------------------------------------------------------------------------
	# Other
	define("VERIFY_CONFIRMED", "confirmed");
	define("CONNECTION", "@connection");
	define("APPLICATION", "@application");
	
	# -------------------------------------------------------------------------------------------------------------------
	# Options
	define("OPTION_MODE_CUSTOM", 0);
	define("OPTION_MODE_SYSTEM", 1);
	
	define("OPTION_TYPE_BOOL", 0);
	define("OPTION_TYPE_INTEGER", 1);
	define("OPTION_TYPE_STRING", 2);
	define("OPTION_TYPE_DATE", 3);
	define("OPTION_TYPE_COLLECTION", 4);
	
	define("ETAG", "@ETAG");

	
?>