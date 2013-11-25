<?php
	/*
		micrositeguru project (mg)	Version 2.0-1
		
		Copyright (c) 1998 - 2009 eikonlexis LLC. All rights reserved.
		
		This program is protected by copyright laws and international treaties.
		Unauthorized reproduction or distribution of this program, or any 
		portion thereof, may result in serious civil and criminal penalties.
	
		Module 		Database Controller Library
		Version		1.0
		
		Last Changes
		2009/02/19	Andi		Initial
	*/
	
	# -------------------------------------------------------------------------------------------------------------------
	# Constants Declaration	
	define("SESSION_MODULENAME", "sessions");
	
	# -------------------------------------------------------------------------------------------------------------------
	# Class:			mgSession
	#
	# Object:		Session
	# Description:	This controller controls the user session
	#
	# Last Change:	March 06 2009 by Andi		
	
	class mgSession extends mgDatabaseObject {
	
		public $session;			// storage for session
	
		# -------------------------------------------------------------------------------------------------------------------
		# constructor __construct
		public function __construct($session = false) {
			// execute query
			parent::__construct(DB_TABLE_SESSION, DB_SELECT, Array(DB_FIELD_IDSTRING=>$session, DB_FIELD_ADDRESS=>GetRemoteAddress()));
			
			// validate result
			if ($this->result!=DB_OK) {
				$ip = GetRemoteAddress();
				if(strlen($ip)!=0) {
					parent::__construct(DB_TABLE_SESSION, DB_CREATE);
					if($this->result!=DB_OK){
						DieCriticalError(ERROR_CRITICAL_SESSION);
					}
					$this->Write(DB_FIELD_ADDRESS, $ip, true);
					// create statistic event
					mgStatisticEvent(SESSION_MODULENAME, 1, Array("session"=>$this->Read(DB_FIELD_IDSTRING)));
				} else {
					$this->result = DB_NOTSUPPORTED;
					return false;
				}
			} 
			// Update Session
			$this->session = $this->Read(DB_FIELD_IDSTRING);
		}

		# -------------------------------------------------------------------------------------------------------------------
		# (public) Write, writes a session value
		public function WriteMeta($name, $value = false, $publish=true){
			return $this->WriteFieldValue(DB_FIELD_META, $name, $value, $publish);
		}
		
		# -------------------------------------------------------------------------------------------------------------------
		# (public) Read, reads a session value
		public function ReadMeta($name, $default = ""){
			return DefaultValue($this->ReadFieldValue(DB_FIELD_META, $name), $default);
		}	

	}
