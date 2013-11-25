<?php
	/* 
		(mg)framework Version 5.0
		
		Copyright (c) 1999-2011 eikonlexis LLC. All rights reserved.
		
		This program is protected by copyright laws and international treaties.
		Unauthorized reproduction or distribution of this program, or any 
		portion thereof, may result in serious civil and criminal penalties.
	
		Module 		Statistics COntroller
	*/
	
	# -------------------------------------------------------------------------------------------------------------------
	# Constants Declaration
	define("STATS_MODULESYSTEM", "system");
	
	// Log File
	define("STATS_LOGFILE", "../../log/statistics/%s.log");
	
	
	# -------------------------------------------------------------------------------------------------------------------
	# (macro) mgStatisticEvent, reports a new statistic event
	function mgStatisticEvent($module = "", $code = 0, $meta = false, $force = false) {
		// remove auto stats
		if($force===false || !defined("STATISTIC_LOG") || STATISTIC_LOG != "true") return;
		// prepare data
		$data = Array(	
			DB_FIELD_SESSION=>isset($meta[DB_FIELD_SESSION])?$meta[DB_FIELD_SESSION]:"",
			DB_FIELD_USERID=>isset($meta[DB_FIELD_USERID])?$meta[DB_FIELD_USERID]:"",
			DB_FIELD_IDADDRESS=>GetRemoteAddress(),
			DB_FIELD_MODULE=>$module,
			DB_FIELD_STATSCODE=>$code,
		); 
		// unset meta
		unset($meta[DB_FIELD_USERID]);
		unset($meta[DB_FIELD_SESSION]);
		// create request
		$request = Array(
			"method"=>@$_SERVER['REQUEST_METHOD'],
			"request"=>@$_SERVER['REQUEST_URI'],
			"requesttime"=>@$_SERVER['REQUEST_TIME'],
			"uagent"=>@$_SERVER['HTTP_USER_AGENT'],
			"language"=>@$_SERVER['HTTP_ACCEPT_LANGUAGE'],
			"referer"=>@$_SERVER['HTTP_REFERER']
		);
		// prepare meta
		$meta = is_array($meta)?$meta:Array();
		// run agains tlog
		return mgStatisticLog($data, $request, $meta);
	}
	
	# -------------------------------------------------------------------------------------------------------------------
	# (class) mgStatisticsController, controller class	
	function mgStatisticLog($data, $request, $meta) {	
		// create directory
		@mkdir(dirname(STATS_LOGFILE));
		// create logfile
		$fn = sprintf(STATS_LOGFILE, date("Ymd"));
		// open file
		$f = fopen($fn, "a+");
		// write data
		fwrite($f, sprintf("%s|%s\n", time(), serialize(Array(
			"data"=>$data,
			"request"=>$request,
			"meta"=>$meta
		))));
		// close
		fclose($f);
	}
