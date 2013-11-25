<?php
	/* 
		(mg)framework Version 5.0
		
		Copyright (c) 1999-2011 eikonlexis LLC. All rights reserved.
		
		This program is protected by copyright laws and international treaties.
		Unauthorized reproduction or distribution of this program, or any 
		portion thereof, may result in serious civil and criminal penalties.
	
		Module 		Analytics Controller
	*/
	

	
	# -------------------------------------------------------------------------------------------------------------------
	# Constants Declaration	
	
	// (tables)
	define("DB_TABLE_ANALYTICS", "analytics");
	
	// (types)
	define("ANALYTICS_TYPE_EVENT", 0);
	
	// (fields)
	define("ANALYTICS_IMPRESSIONS", "imp");
	define("ANALYTICS_UNIQUES", "unq");
	define("ANALYTICS_IPLIST", "ipl");
	
	# -------------------------------------------------------------------------------------------------------------------
	# (class) mgAnalytics, manages a user analytics
	class mgAnalytics  {
	
		# ---------------------------------------------------------------------------------------------------------------
		# (private)
		private $related = false;
		private $stamp = false;
	
		# ---------------------------------------------------------------------------------------------------------------
		# (initialize) initialize the module manager class
		public function __construct($related){
			// today
			$this->stamp = mktime('0', '0', '0', date('n'), date('j'), date('Y'));
			$this->related = $related;
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (Record) records an event
		public function RecordEvent($event) {
			// check eventname
			if(!$event||strlen($event)==0) return false;
			// read today event
			$db = new mgDatabaseObject(DB_TABLE_ANALYTICS, DB_SELECT, Array(DB_FIELD_TYPE=>ANALYTICS_TYPE_EVENT, DB_FIELD_RELATED=>$this->related, DB_FIELD_SOURCE=>$event, DB_FIELD_STAMP=>$this->stamp));
			// check if db exists
			if($db->result!=DB_OK) {
				// register
				$db = new mgDatabaseObject(DB_TABLE_ANALYTICS, DB_CREATE);
				// fill
				if($db->result==DB_OK) {
					
					$db->Write(Array(
						DB_FIELD_RELATED=>$this->related,
						DB_FIELD_STAMP=>(integer)$this->stamp,
						DB_FIELD_TYPE=>ANALYTICS_TYPE_EVENT,
						DB_FIELD_SOURCE=>$event
					), true);
				}
			}
			// check again
			if($db->result==DB_OK) {
				// record
				$this->__record($db);
			}
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (GetJSON) returns all statistics as json - sorted for graph control
		public function GetJSON() {
			// initialize result
			$result = Array();
			// get database
			$db = new mgDatabaseStream(DB_TABLE_ANALYTICS, DB_SELECT, Array(DB_FIELD_RELATED=>$this->related), Array(DB_FIELD_STAMP=>DB_SORTCOLUMN_ASC));
			// check db
			if($db->result==DB_OK) {
				// initialize
				$result = Array(
					"mindate"=>false,
					"maxdate"=>false,
					"events"=>Array()
				);
				// cycle
				foreach($db->getall() as $row) {
					// parse row
					$meta = @unserialize(@$row[DB_FIELD_META]);
					$stamp = @$row[DB_FIELD_STAMP];
					$source = @$row[DB_FIELD_SOURCE];
					$impressions = @$meta[ANALYTICS_IMPRESSIONS];
					$uniques = @$meta[ANALYTICS_UNIQUES];
					// update date
					if($result["mindate"]===false||$stamp<$result["mindate"]) {$result["mindate"] = $stamp;}
					if($result["maxdate"]===false||$stamp>$result["maxdate"]) {$result["maxdate"] = $stamp;}
					// sanity check
					if($source&&strlen($source)!=0) {
						// check type
						switch(@$row[DB_FIELD_TYPE]) {
							// (events)
							case ANALYTICS_TYPE_EVENT:
								// test event
								if(!isset($result["events"][$source])) {
									$result["events"][$source] = Array(
										"data"=>Array("impressions"=>Array(), "uniques"=>Array()),
										"dates"=>Array()
									);
								}
								// record graph data
								$result["events"][$source]["data"]["impressions"][] = $impressions;
								$result["events"][$source]["data"]["uniques"][] = $uniques;
								// add date data
								$result["events"][$source]["dates"][$stamp] = Array(
									"uniques"=>$uniques,
									"impressions"=>$impressions
								);
								break;
						}
					}
				}
				// process string
				$result["datestring"] = sprintf("%s - %s", 
					date("F j, Y", $result["mindate"]),
					date("F j, Y", $result["maxdate"])
				);
			}
			// return
			return $result;
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (__record) records and fills the database
		public function __record($db) {		
			// sanity check
			if($db->result!=DB_OK) return false;
			// read values
			$imp = (integer)$db->ReadFieldValue(DB_FIELD_META, ANALYTICS_IMPRESSIONS);
			$unq = (integer)$db->ReadFieldValue(DB_FIELD_META, ANALYTICS_UNIQUES);
			$ipl = $db->ReadFieldValue(DB_FIELD_META, ANALYTICS_IPLIST);
			// record ipl
			if(!is_array($ipl)) $ipl = Array();
			// check user ip against ipl
			if(!in_array(GetRemoteAddress(), $ipl)) {
				// record ipl
				$ipl[] = GetRemoteAddress();
				// up uniques
				$unq += 1;
			}
			// record
			$db->WriteFieldValue(DB_FIELD_META, Array(
				ANALYTICS_IMPRESSIONS=>$imp+1,
				ANALYTICS_UNIQUES=>$unq,
				ANALYTICS_IPLIST=>$ipl
			));
			// publish
			$db->Publish();
			// return
			return true;
		}
	}
	
	# -------------------------------------------------------------------------------------------------------------------
	# (macro) mgAnalyticsRecordEvent, records analytics
	function mgAnalyticsRecordEvent($related, $event) {
		// create
		$a = new mgAnalytics($related);
		// record
		$a->RecordEvent($event);
	}
	
