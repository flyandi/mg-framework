<?php
	/* 
		JParser Tools
		
		Copyright (c) 1999-2012 eikonlexis LLC. All rights reserved.
		
		This program is protected by copyright laws and international treaties.
		Unauthorized reproduction or distribution of this program, or any 
		portion thereof, may result in serious civil and criminal penalties.
	*/
	
	
	# -----------------------------------------------------------------------------------
	# (jsCreateStringMap) builds a string map from the xml source
	function jsCreateStringMap($source) {
		// intialize result
		$result = Array();
		// create progressbar
		//ConProgressInit(count($token));
		// cycle
		$x = 0;
		foreach($source as $token) {
			// list variables
			list($t, $s, $l, $c ) = $token;
			// switch by type
			switch($t) {
				case J_STRING_LITERAL:
					$result[]= Array($s, $l, $c);
					break;
			}
			$x += 1;
			//ConProgress($x);
		}
		//ConOut(" Completed");
		// return
		return $result;
	}
?>