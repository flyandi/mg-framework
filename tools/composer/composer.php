<?php
	/** 
	 * mgFramework Composer
	 *
	 * Copyright (c) 2013 eikonlexis LLC
	*/

	# -----------------------------------------------------------------------------------
	# (constants)
	define("CONSOLE_APPNAME", "Composer");
	define("CONSOLE_APPVERSION", "1.0");
	define("CONSOLE_APPLEGAL", "Copyright (c) 2013");
	
	# -----------------------------------------------------------------------------------
	# Include Shares
	include("../shared/console.lib.php");
	
	# -----------------------------------------------------------------------------------
	# Include Platforms
	include("../../platform/library/controller.database.lib.php");
	include("../../platform/library/controller.image.lib.php");
	include("../../platform/library/controller.cache.lib.php");
	include("../../platform/library/controller.helper.lib.php");
	include("../../platform/library/controller.file.lib.php");
	include("../../platform/library/controller.user.lib.php");
	include("../../platform/library/controller.htmldom.lib.php");

	# -----------------------------------------------------------------------------------
	# Initialize
	ConInit();		
	
	// variables
	$cmApp = ConVar(0);
	$cmAction = strtolower(ConVar(1));
	
	// verify application
	$path = sprintf("../../applications/%s", $cmApp);
	if(!is_dir($path)) {
		ConOut("-- Application %s does not exists.", $cmApp);
		exit;
	}
	
	# -----------------------------------------------------------------------------------
	# Compose
	
	
	