<?php
	/* 
		(mg)framework Version 5.0
		
		Copyright (c) 1999-2011 eikonlexis LLC. All rights reserved.
		
		This program is protected by copyright laws and international treaties.
		Unauthorized reproduction or distribution of this program, or any 
		portion thereof, may result in serious civil and criminal penalties.
	
		Module 		API Auth Controller
	*/
	
	# -------------------------------------------------------------------------------------------------------------------
	# Constants Declaration	
	
	
	# -------------------------------------------------------------------------------------------------------------------
	# (class) mgAPIAuth, api-auth controller
	
	class mgAPIAuthController extends mgDatabaseObject {
		# -------------------------------------------------------------------------------------------------------------------
		# constructor __construct
		public function __construct() {
			// execute query
			parent::__construct(DB_TABLE_APIAUTH, DB_SELECT, Array("apikey"=>GetVar(APIAUTH_KEY, "none"), "apisecret"=>GetVar(APIAUTH_SECRET, "none"), DB_FIELD_ENABLED=>ENABLED));
			// check
			if($this->result != DB_OK) {
				DieCriticalError(ERROR_CRITICAL_APIAUTH);
			}
		}

		# -------------------------------------------------------------------------------------------------------------------
		# (public) IsAuthorized, returns true if the APIAuth Connection is valid
		public function IsAuthorized() {
			return $this->result == DB_OK;
		}

	}
?>