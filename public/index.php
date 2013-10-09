<?php
	/* 
		(mg)framework Version 5.0
		
		Copyright (c) 1999-2011 eikonlexis LLC. All rights reserved.
		
		This program is protected by copyright laws and international treaties.
		Unauthorized reproduction or distribution of this program, or any 
		portion thereof, may result in serious civil and criminal penalties.
	
		Module 		Bootstrap Handler
	*/
	

	# -----------------------------------------------------------------------   	
	# Mininum Configuration
	$PLATFORM_PATH      = "../platform";			// Handler Path
	$PLATFORM_HANDLER	= "handler.php";			// Handler Processor
		
	
	# -----------------------------------------------------------------------   	
	# Run Platform
	chdir($PLATFORM_PATH);							// Change Path
	require($PLATFORM_HANDLER);
?>