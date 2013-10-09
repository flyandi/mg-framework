<?php
	/* 
		(mg)framework Version 5.0
		
		Copyright (c) 1999-2011 eikonlexis LLC. All rights reserved.
		
		This program is protected by copyright laws and international treaties.
		Unauthorized reproduction or distribution of this program, or any 
		portion thereof, may result in serious civil and criminal penalties.
	
		Module 		Bootstrap File 
	*/
	
	# -------------------------------------------------------------------------------------------------------------------
	# Load Framework
	require ("library/controller.framework.lib.php");
	
	# -------------------------------------------------------------------------------------------------------------------
	# Load Application
	
	// create app
	$APPLICATION = new mgApplicationDetector();
	// include application controller
	require(strtolower(sprintf(FRAMEWORK_APPLICATION_SOURCE, $APPLICATION->applicationpath)));
	// create class
	$aclass = FRAMEWORK_APPLICATION_CLASS;
	// initialize application
	$app = new $aclass($APPLICATION->application);
	// set framework
	SetVar(FRAMEWORK, $app);
	// run application
	$app->Emit();	
?>