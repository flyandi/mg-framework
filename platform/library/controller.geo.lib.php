<?php
	/* 
		(mg)framework Version 5.0
		
		Copyright (c) 1999-2011 eikonlexis LLC. All rights reserved.
		
		This program is protected by copyright laws and international treaties.
		Unauthorized reproduction or distribution of this program, or any 
		portion thereof, may result in serious civil and criminal penalties.
	
		Module 		GEO Controller
	*/

	
	# -------------------------------------------------------------------------------------------------------------------
	# Constants Declaration	
	
	# Database Table
	define("GEO_TABLE", "mg_geo");

	# GEO Type Definition
	define("GEO_TYPE_STANDARD", 0);			// Standard 
	define("GEO_TYPE_POBOXONLY", 1);		// Post Box Only
	define("GEO_TYPE_POSTOFFICE", 3);		// Post Office & Community Post Office
	define("GEO_TYPE_MILITARY", 4);			// Military
	define("GEO_TYPE_USPSROUTING", 5);		// USPS Routing Number
	
	# Formatting Options
	define("GEO_OUTPUT_DEFAULT", 0);		// Default no formatting
	define("GEO_OUTPUT_FORMATTED", 1);		// Formatted
	
	# GEO Parameters
	define("GEO_PREFERRED", 1);				// Only use preferred GEO
	
	# GEO Error Codes
	define("GEO_ERROR_NODATABASE", 1);		// No database connection is available
	define("GEO_ERROR_NORESULTS", 2);		// No Results
	
	# -------------------------------------------------------------------------------------------------------------------
	# (class)	 mgGEO
	# 	Base class for GEO Operations
	class mgGEO {
		# ---------------------------------------------------------------------------------------------------------------
		# (public)
		public $lastsql = "";		// Last SQL 
		public $lasterror = -1;	// Last Error
		
		# ---------------------------------------------------------------------------------------------------------------
		# (private)
		private $__format = GEO_OUTPUT_DEFAULT;
		
		# ---------------------------------------------------------------------------------------------------------------
		# (construct) Constructor
		public function __construct($format = GEO_OUTPUT_DEFAULT) {
			$this->__format = $format;
		}
		
	
		# -------------------------------------------------------------------------------------------------------------------
		# (public) query
		# 	returns details about a GEO
		function query($zip, $all=false, $asobject = false) {
			$result = $this->__querycache(array_merge(Array("zip"=>$zip), $all?Array():Array("preferred"=>GEO_PREFERRED)));
			return is_array($result)?($asobject?(object)$result:$result):false;
		}
		
		
		# -------------------------------------------------------------------------------------------------------------------
		# (public) querylocation
		# 	trys to conver the location to GEO, optional state
		function querylocation($city, $state=false, $onlypreferred=true) {
			return $this->__querycache(array_merge($onlypreferred?Array("preferred"=>GEO_PREFERRED):Array(), Array("city"=>$this->__escape(sprintf("%%%s%%", $city))), strlen($state)!=0?Array("state"=>$this->__escape(sprintf("%%%s%%", $state))):Array()), false, " LIKE ");
		}
		
		# -------------------------------------------------------------------------------------------------------------------
		# (public) getlocationarray
		#   gets a compiled version of the location
		function getlocationarray($city, $state=false, $onlypreferred=true, $asarray = true) {
			// initialize
			$result = Array();
			$data = $this->querylocation($city, $state, $onlypreferred);
			// process
			foreach($data as $params) {
				// objectify
				$params = (object)$params;
				$id = trim(str_replace(" ", "", $params->locationid));
				// check result
				if(!isset($result[$id])) {
					$result[$id] = Array(
						"name"=>$params->city,
						"county"=>$params->county,
						"state"=>$params->state,
						"latitude"=>$params->latitude,
						"longitude"=>$params->longitude,
						"locationtext"=>$params->locationtext,
						"zips"=>Array()
					);
				}
				// add zip
				$result[$id]["zips"][] = Array(
					"zip"=>$params->zip,
					"type"=>$params->type,
					"preferred"=>$params->preferred
				);
			}
			// process
			foreach($result as $idx=>$values) {
				// get range string
				$result[$idx]["ziprange"] = $this->__formatziprange($values["zips"]);
				$result[$idx]["defaultzip"] = @$values["zips"][0]["zip"];
			}
			// format result
			if($asarray) {
				// init new result
				$nr = Array();
				foreach($result as $id=>$params) {
					$nr[] = $params;
				}
				$result = $nr;
			}			
			// return
			return $result;
		}
		
		
		# ---------------------------------------------------------------------------------------------------------------
		# (private) __formatziprange
		private function __formatziprange($zips) {
			// initialize result
			$result = "";
			// parse zips
			$z = Array();
			foreach($zips as $zp) {
				$z[] = $zp["zip"];
			}
			// process zips
			$p = false; $rs = false; $re = false; $a = Array();
			foreach($z as $zp) {
				// check previous
				if(!$p) {
					$p = $zp;
					$rs = $zp;
				} else {
					if($zp-1==$p) {
						$re = $zp;
						$p = $zp;
					} else {
						$a[] = $zp;
					}
				}
			}
			// process
			if($rs&&$re) {
				$result = sprintf("%s-%s", $rs, $re);
			} else {
				$a = array_merge(Array($rs), $a);
			}
			$result .= sprintf("%s%s", strlen($result)!=0&&count($a)!=0?", ":"", implode(", ", $a));
			// return
			return $result;
		}
		
		
		# ---------------------------------------------------------------------------------------------------------------
		# (private) __escape, escapes an string
		private function __escape($s) { 
			return sprintf("'%s'", @mysql_real_escape_string($s));
		}
	
		# ---------------------------------------------------------------------------------------------------------------
		# (private) __querycache, queries the cache
		private function __querycache($request, $count = false, $connector = "=", $sql = false) {
			// build fields
			$fields = Array();
			foreach($request as $field=>$value) {
				$fields[] = sprintf("%s%s%s", $field, $connector, $value);
			}
			
			// build query
			$this->lastsql = sprintf("SELECT * FROM %s WHERE %s", GEO_TABLE, implode(" AND ", $fields));
			// execute query
			if($rSql = @mysql_query($this->lastsql)) {
				if(@mysql_num_rows($rSql)!=0) {
					// initialize result
					$result = Array();
					// get result
					while($row = mysql_fetch_assoc($rSql)) {
						// assign values
						$result[] = $this->__format==GEO_OUTPUT_DEFAULT?$row:$this->__formatoutput($row);
						// break 
						if($count!==false&&count($result)>=$count) break;
					}
					return count($result)==1?$result[0]:$result;
				
				}
				return GEO_ERROR_NORESULTS;
			} 
			// return error 
			return GEO_ERROR_NODATABASE;
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (private) __formatoutput, formats the output
		private function __formatoutput($row) {
			foreach(Array("city", "county") as $f) {$row[$f] = ucwords(strtolower($row[$f]));}
			return $row;
		}
		
	}
?>