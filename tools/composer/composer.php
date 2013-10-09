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
	include("../../platform/library/controller.xml.lib.php");
	
	# -----------------------------------------------------------------------------------
	# deploy
	
	function deploy($params, $assets, $values) {
		// cycle assets
		foreach($assets as $location=>$content) {
			// check file
			if(file_exists($location)) { 
				ConOut("%s already exists .. skipped", $location);
			} else {
				// modify content
				foreach(array_merge((array)$params, $values) as $key=>$value) {
					$content = str_replace(sprintf("{%s}", $key), $value, $content);
				}
				// deploy target
				$targetdir = dirname($location);
				@mkdir($targetdir, 777, true);
				// write file
				file_put_contents($location, $content);
				// echo 
				ConOut("%s .. written", $location);
			}
		}
	}
	
	function loadobject($p) {
		global $params;
		return file_get_contents(sprintf("%ssource/%s", $params->object, $p));
	}

	# -----------------------------------------------------------------------------------
	# Initialize
	ConInit();		
	
	// variables
	$cmApp = ConVar(0);
	$cmAction = strtolower(ConVar(1));
	
	// verify application
	$path = sprintf("../../applications/%s/", $cmApp);
	if(!is_dir($path)) {
		ConOut("-- Application %s does not exists.", $cmApp);
		exit;
	}
	
	$object = sprintf("objects/%s/", $cmAction);
	if(!is_dir($object)) {
		ConOut("-- Object %s does not exists.", $cmAction);
		exit;
	}
	
	// load variables
	$xml = new mgXML(file_get_contents(sprintf("%sapplication.xml", $path)));
	
	// create variables
	$params = array(
		"path" => $path,
		"object" => $object
	);
	
	foreach($xml->variables->variable as $v) {
		$params[strtolower(str_replace(Array("_"), "", (string) $v["name"]))] = (string) $v["value"];
	}
	
	// finalize
	$params = (object) $params;
	
	
	# -----------------------------------------------------------------------------------
	# Compose
	
	// include
	include(sprintf("%sobject.php", $object));
	
	// check function
	if(function_exists("objectcreate")) {
		objectcreate($params);
	} else {
		ConOut("-- Object %s does not has a valid header.", $cmAction);
	}
	

	