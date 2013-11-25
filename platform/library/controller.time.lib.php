<?php
	/* 
		(mg)framework Version 5.0
		
		Copyright (c) 1999-2011 eikonlexis LLC. All rights reserved.
		
		This program is protected by copyright laws and international treaties.
		Unauthorized reproduction or distribution of this program, or any 
		portion thereof, may result in serious civil and criminal penalties.
	
		Module 		Time Controller
	*/
	
	# -------------------------------------------------------------------------------------------------------------------
	# Constants Declaration
	define("TIMESTAMP_NOW", 0);
	define("TIMESTAMP_MYSQL", 1);
	
	# -------------------------------------------------------------------------------------------------------------------
	# (class) mgTimeController
	class mgTimeController {
	
		# local variable, in microseconds
		private $stamp;
	
		# -------------------------------------------------------------------------------------------------------------------
		# __construct
		# param0 = timestamp
		# param1 = timestamp format
		public function __construct($param0=false,$param1=TIMESTAMP_NOW) {
			if($param0==false){$this->stamp=microtime();}
			// create stamp from another source
			switch($param1){
				case TIMESTAMP_MYSQL: $this->__convertmysqlstamp($param0);
			}
		}
			
		# -------------------------------------------------------------------------------------------------------------------
		# __convertmysqlstamp
		private function __convertmysqlstamp($timestamp){
			$this->stamp = mktime(substr($timestamp,11,2), substr($timestamp,14,2), substr($timestamp,17,2),
								  substr($timestamp,5,2),  substr($timestamp,8,2), substr($timestamp,0,4));
		}
		
		# -------------------------------------------------------------------------------------------------------------------
		# Return Time Stamp
		public function TimeStamp(){return $this->stamp;}
		
		# -------------------------------------------------------------------------------------------------------------------
		# Return Full Date
		public function FullDate(){return date("l, F j, Y", $this->stamp);}

		# -------------------------------------------------------------------------------------------------------------------
		# Return Time String
		public function TimeString(){return date("g:i A", $this->stamp);}		
		
		# -------------------------------------------------------------------------------------------------------------------
		# AddYear, automatically adds one year to the time stamp
		public function AddYear(){
			$this->stamp += (365 * 24 * 60 * 60);
		}
	}
	
	# -------------------------------------------------------------------------------------------------------------------
	# (class) mgGetMicrotime
	function mgGetMicrotime($mt = false) {
		if($mt===false) $mt = microtime();
		list($msec, $sec) = explode(' ', (string)$mt);
		return ((float)$msec + (float)$sec);
	}
	


