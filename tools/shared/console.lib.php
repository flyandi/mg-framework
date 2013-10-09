<?php
	/* 
		Console Library
		
		Copyright (c) 1999-2012 eikonlexis LLC. All rights reserved.
		
		This program is protected by copyright laws and international treaties.
		Unauthorized reproduction or distribution of this program, or any 
		portion thereof, may result in serious civil and criminal penalties.
	*/
	
	# -----------------------------------------------------------------------------------
	# (includes)
	require_once("../../platform/library/constants.lib.php");
	require_once("../../platform/library/core.lib.php");
	require_once("progressbar.lib.php"); 
	
	# -----------------------------------------------------------------------------------
	# (constants)
	define("FILE_NOTFOUND", "The file %s was not found.");
	
	// Progressbar
	define("PROGRESSBAR_STYLE", "%fraction% [%bar%] %percent%");
	$console_progressbar = false;
	
	// ToolVar 
	define("ASTEXT", 0);
	define("ASFILE", 1);
	
	
	# -----------------------------------------------------------------------------------
	# (ConInit) Console Initialization
	function ConInit($clearscreen=false) {
		ConOut("%s %s %s\n", CONSOLE_APPNAME, CONSOLE_APPVERSION, CONSOLE_APPLEGAL);
	}
	
	# -----------------------------------------------------------------------------------
	# (ConOut) Console Output
	function ConOut() {
		$a = func_get_args();
		echo vsprintf($a[0], array_slice($a, 1))."\n";
		flush();
	}
	
	# -----------------------------------------------------------------------------------
	# (ConProgressInit) initialize the console progressbar
	function ConProgressInit($max, $style=PROGRESSBAR_STYLE) {
		SetVar("console_progressbar", new Console_ProgressBar($style, '=', ' ', 60, $max));
	}
	
	# -----------------------------------------------------------------------------------
	# (ConProgressHide) hides the console progressbar
	function ConProgressHide() {
		$p = GetVar("console_progressbar");
		$p->erase();
	}
	
	# -----------------------------------------------------------------------------------
	# (ConProgress) updates the progress on the bar
	function ConProgress($pos) {
		$p = GetVar("console_progressbar");
		$p->update($pos);
	}

	# -----------------------------------------------------------------------------------
	# (ConVar) returns the argument from the console
	function ConVar($index, $as = ASTEXT) {
		global $argv;
		// adjust index
		$index += 1;
		// get var
		$v = isset($argv[$index])?$argv[$index]:false;
		// test as
		switch($as) {
			case ASFILE:
				if(!file_exists($v)) {Die(ConOut(FILE_NOTFOUND, $v));}
				break;
		}
		// return
		return $v;
	}
?>