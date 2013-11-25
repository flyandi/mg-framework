<?php
	/* 
		(mg)framework Version 5.0
		
		Copyright (c) 1999-2011 eikonlexis LLC. All rights reserved.
		
		This program is protected by copyright laws and international treaties.
		Unauthorized reproduction or distribution of this program, or any 
		portion thereof, may result in serious civil and criminal penalties.
	
		Module 		Slugs Controller
	*/

	
	# -------------------------------------------------------------------------------------------------------------------
	# Constants Declaration	
	define("SLUG_CREATE", 0);
	define("SLUG_FOLLOW", 1);
	
	// length
	define("SLUG_CODELENGTH", 8);
	define("SLUG_MAXCODERETRY", 5);
	
	// table
	define("DB_TABLE_SLUGS", "slugs");
	
	
	# -------------------------------------------------------------------------------------------------------------------
	# (mgSlug) main class
	class mgSlug {
		
		# ---------------------------------------------------------------------------------------------------------------
		# (constructor)
		public function __construct() {		
		
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (create), creats a new slug
		public function create($destination, $enable = true, $slug = false) {
			// get code
			$slug = $slug?$slug:$this->__createslug();
			// check code
			if(!$slug) return false;
			// register code
			$db = new mgDatabaseObject(DB_TABLE_SLUGS, DB_CREATE);
			// check db
			if($db->result == DB_OK) {
				// write codes
				$db->Write(Array(
					"slug"=>$slug,
					"destination"=>$destination,
					"active"=>$enable?1:0
				));
				// update table
				$db->Publish();
				// return
				return $slug;
			}
			// return default
			return false;
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (exists), checks if a slug exists
		public function exists($slug, $returndata = false) {
			// query database
			$db = new mgDatabaseObject(DB_TABLE_SLUGS, DB_SELECT, Array(DB_FIELD_ENABLED=>ENABLED, "slug"=>$slug));
			// check
			if($db->result == DB_OK) {
				return $returndata?$db->getrow():true;
			}
			// return false
			return false;
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (update), updates an slug
		public function update($slug, $destination, $enabled = true) {
			// query database
			$db = new mgDatabaseObject(DB_TABLE_SLUGS, DB_SELECT, Array("slug"=>$slug));
			// check
			if($db->result == DB_OK) {
				$db->Write(Array(
					"destination"=>$destination,
					DB_FIELD_ENABLED=>$enabled?ENABLED:DISABLED
				), true);
				// return
				return true;
			}
			// return false
			return false;
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (__createslug), creates a unique slug code
		private function __createslug($retry = 0) {
			// test retry
			if($retry > SLUG_MAXCODERETRY) return false;
			// create table
			$table = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";       
			// create data
			$data = str_shuffle(str_repeat($table, rand(1, SLUG_CODELENGTH)));
			// create code
			$code = substr($data, rand(0, strlen($data)-SLUG_CODELENGTH), SLUG_CODELENGTH); 
			// check code
			if($this->exists($code)) { return $this->__createslug($retry + 1);}
			// return code
			return $code;
		}
		
	
	}
	
	# -------------------------------------------------------------------------------------------------------------------
	# (mgCreateSlug) registers a new slug
	function mgCreateSlug($destination=false, $enabled=true) {
		// create slug class
		$slug = new mgSlug();
		// register slug
		return $slug->create($destination, $enabled);
	}

	
