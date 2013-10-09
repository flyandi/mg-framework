<?php
	/* 
		(mg)framework Version 5.0
		
		Copyright (c) 1999-2011 eikonlexis LLC. All rights reserved.
		
		This program is protected by copyright laws and international treaties.
		Unauthorized reproduction or distribution of this program, or any 
		portion thereof, may result in serious civil and criminal penalties.
	
		Module 		Honeypot is a simple crawl and bad behavior detection controller
	*/
	
	# -------------------------------------------------------------------------------------------------------------------
	# Constants Declaration
	define("DB_TABLE_HONEYPOTBLOCKED", "honeypotblocked");
	define("DB_TABLE_HONEYPOTTRAPS", "honeypottraps");
	define("DB_TABLE_HONEYPOTTRAFFIC", "honeypottraffic");
	define("DB_TABLE_HONEYPOTRULES", "honeypotrules");
	
	define("HONEYPOT_TRAFFICTYPE_ROBOT", 0);
	define("HONEYPOT_TRAFFICTYPE_TRAP", 1);
	
	define("HONEYPOT_BLOCKTYPE_TEMPORARY", 0);
	define("HONEYPOT_BLOCKTYPE_PERMANENT", 1);
	
	define("HONEYPOT_BANUA", "banua.txt");
	define("HONEYPOT_ALLOWUA", "allowua.txt");
	define("HONEYPOT_VALIDBOTS", "bots.txt");
	
	define("HONEYPOT_TIMEOUT_TEMPORARYBLOCK", 60 * 60 * 24); 	// 24h 
	define("HONEYPOT_TIMEOUT_TRAPBLOCK", 60 * 60);				// 1h
	define("HONEYPOT_TIMEOUT_TRAFFIC", 60 * 60);				// 1h
	define("HONEYPOT_TIMEOUT_TRAP", 60 * 60);					// 1h
	
	define("HONEYPOT_CACHETIMEOUT_TRAP", 60 * 60 * 24);			// 24h

	define("HONEYPOT_BLOCKREASON_MANUAL", 0);
	define("HONEYPOT_BLOCKREASON_UA", 1);
	define("HONEYPOT_BLOCKREASON_TRAP", 2);
	
	define("HONEYPOT_TRAPTYPE_REQUEST", 0);
	define("HONEYPOT_TRAPTYPE_PERMANENT", 1);
	
	define("HONEYPOT_REQUESTEXCEPTIONS", "api,resources,manager,signin");
	define("HONEYPOT_TRAPCACHE", "hptraps");
	define("HONEYPOT_TRAFFICCACHE", "hptraffic");
	
	define("HONEYPOT_ALLOWEDBOT", "allowedbot");
	
	
	
	# -------------------------------------------------------------------------------------------------------------------
	# Class
	class mgHoneypot {
	
		# ---------------------------------------------------------------------------------------------------------------
		# (private)
		private $path;
		private $ip;
		private $cache = false;

		# ---------------------------------------------------------------------------------------------------------------
		# (public) process
		public function __construct($ip = false, $cache = false) {
			// set path
			$this->path = sprintf("%s%s.honeypot/", COMPONENT_PATH, COMPONENT_ID_PATH);
			// this is always for the current ip address
			$this->ip = mgSanitizeString($ip?$ip:GetRemoteAddress());
			// set path
			$this->path = sprintf("%s%s/", COMPONENT_PATH, sprintf("%s.%s", COMPONENT_ID_PATH, "honeypot"));
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (public) NotAllowed
		public function NotAllowed() {
			// check for exceptions
			$ex = explode(",", HONEYPOT_REQUESTEXCEPTIONS);
			foreach($ex as $v) {
				if(mgCompare(GetDirVar(0), $v)) return false;
			}
			// seen?
			$seen = $this->__sawtraffic();
			// check for valid bot traffic
			if(!$seen&&$this->__validbot()) return false;
			// Check Block
			if($this->__isblocked()) return true;
			// Check Traffic
			if(!$seen) {
				// register traffic
				$this->__registertraffic();
				// Check UA
				if($this->__checkua(HONEYPOT_BANUA)) {
					// block ua
					$this->__block(HONEYPOT_BLOCKREASON_UA); 
					return true; 
				}
			} else {
				// Analyze Traffic
				
			}
		}		
		
		# ---------------------------------------------------------------------------------------------------------------
		# (public) registertrap 
		public function RegisterTrap($value, $type = HONEYPOT_TRAPTYPE_REQUEST, $timeout = HONEYPOT_TIMEOUT_TRAP, $data = Array()) {
			$result = false;
			// create fields
			$fields = Array(
				DB_FIELD_ADDRESS=>$this->ip,
				DB_FIELD_VALUE=>$value,
				DB_FIELD_TYPE=>$type,
				DB_FIELD_CREATED=>time(),
				DB_FIELD_META=>serialize(is_array($data)?$data:Array($data)),
				"timeout"=>time() + $timeout
			);
			// file based
			if($this->cache) {
				// save to file
				if($this->cache->store(md5($value), $fields, HONEYPOT_TRAPCACHE, mgIsPageCache()?mgPageCacheTimeout():HONEYPOT_CACHETIMEOUT_TRAP)) {	
					// return value	
					$result = $value;
				}
			}
			// add trap
			$db = new mgDatabaseObject(DB_TABLE_HONEYPOTTRAPS, DB_CREATE);
			// check
			if($db->result == DB_OK) {
				// write data
				$db->Write($fields, true);
				// set
				$result = $value;
			}
			// return result
			return $result;
		}
			
		
		# ---------------------------------------------------------------------------------------------------------------
		# (public) IsTrap 
		public function IsTrap($value=false) {
			// get trap
			$db = new mgDatabaseObject(DB_TABLE_HONEYPOTTRAPS, DB_SELECT, Array(DB_FIELD_VALUE=>$value));
			// check trap
			if($db->result == DB_OK) {
				// trap hit, banned
				$this->__block(HONEYPOT_BLOCKREASON_TRAP, HONEYPOT_BLOCKTYPE_TEMPORARY, HONEYPOT_TIMEOUT_TRAPBLOCK);				
				return true;
			}
			// no trap
			return false;
		}

		
		# ---------------------------------------------------------------------------------------------------------------
		# (private) __validbot
		private function __validbot() {
			// get list of bots
			$list = @file(sprintf("%s%s", $this->path, HONEYPOT_VALIDBOTS));
			// cycle
			$ua = trim(GetServerVar("HTTP_USER_AGENT"));
			// check if ua is not set - this might happening from time to time
			if(!$ua||strlen($ua)==0) return false;	// continue to other analyze methods
			// cycle list
			foreach($list as $bot) {
				// initialie
				$bot = explode("=", $bot);
				// match user agent
				if(preg_match_all(sprintf("~%s~is", trim($bot[0])), $ua, $matches)) {
					// get ip address, can't be proxied
					$botip = GetServerVar("REMOTE_ADDR", false);
					// check botip and execute reverse DNS lookup
					if($botip !== false) {
						// get hostname and ip
						$hostname = GetHostByAddr($botip);
						$hostip = GetHostByName($hostname);
						// check reverse lookup, needs to match up
						if($botip == $hostip && preg_match(sprintf("/%s/is", $bot[1]), $hostname)) {
							// set global variable
							SetVar(HONEYPOT_ALLOWEDBOT, true);
							// successfully identified
							return true;
						}
					}
				}
			}
			// return default
			return false;
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (private) __checkua
		private function __checkua($uas) {
			// get ua
			$ua = trim(GetServerVar("HTTP_USER_AGENT"));
			// check if ua is not set - this might happening from time to time
			if(!$ua||strlen($ua)==0) return false;	// allow 
			// read list
			$list = @file(sprintf("%s%s", $this->path, $uas));
			// cycle list
			foreach($list as $filter) {
				$filter = sprintf("~%s~is", trim($filter));
				if(preg_match_all($filter, $ua, $matches)) {
					return true;
				}
			}
			// no matches
			return false;
		}
	
		# ---------------------------------------------------------------------------------------------------------------
		# (private) __block
		private function __block($reason = HONEYPOT_BLOCKREASON_MANUAL, $type = HONEYPOT_BLOCKTYPE_TEMPORARY , $timeout = HONEYPOT_TIMEOUT_TEMPORARYBLOCK) {
			// create row
			$db = new mgDatabaseObject(DB_TABLE_HONEYPOTBLOCKED, DB_CREATE);
			// write data
			if($db->result == DB_OK) {
				$db->Write(Array(
					DB_FIELD_ADDRESS=>$this->ip,
					DB_FIELD_TYPE=>$type,
					DB_FIELD_META=>serialize(array_merge(
						Array(
							"ua"=>GetServerVar("HTTP_USER_AGENT")
						), 
						ReverseDNSLookup($this->ip)
					)),
					DB_FIELD_CREATED=>time(),
					"timeout"=>time() + $timeout,
					"reason"=>$reason
				), true);
			}
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (private) __unblock
		private function __unblock() {
			// create row
			$db = new mgDatabaseStream(DB_TABLE_HONEYPOTBLOCKED, DB_SELECT, Array(DB_FIELD_ADDRESS=>$this->ip));
			// write data
			if($db->result == DB_OK) {
				for($i=$db->rowcount();$i>=0;$i--) {
					$db->Delete($i);
				}
			}
		}		
		
		# ---------------------------------------------------------------------------------------------------------------
		# (private) __isblocked (checks if current ip address is blocked) 
		private function __isblocked() {
			// check if ip is blocked
			$db = new mgDatabaseObject(DB_TABLE_HONEYPOTBLOCKED, DB_SELECTSQL, sprintf("(idaddress='%s' AND idtype=0 AND timeout>%s) OR (idaddress='%s' AND idtype=1)",
				$this->ip, 
				time(),
				$this->ip
			));
			// check
			return $db->result == DB_OK;
		}	

		# ---------------------------------------------------------------------------------------------------------------
		# (private) __sawtraffic
		private function __sawtraffic() {
			if($this->cache) {
				
			}
		
			// check if ip is seen and register
			$db = new mgDatabaseObject(DB_TABLE_HONEYPOTTRAFFIC, DB_SELECTSQL, sprintf("(idaddress='%s' AND timeout>%s)",
				$this->ip,
				time()
			));
			// return
			return $db->result == DB_OK?$db:false;
		}
		
		
		# ---------------------------------------------------------------------------------------------------------------
		# (private) __registertraffic 
		private function __registertraffic($timeout = HONEYPOT_TIMEOUT_TRAFFIC) {
			// register
			$db = new mgDatabaseObject(DB_TABLE_HONEYPOTTRAFFIC, DB_CREATE);
			if($db->result == DB_OK) {
				// save data
				$db->Write(Array(
					DB_FIELD_ADDRESS=>$this->ip,
					DB_FIELD_CREATED=>time(),
					"timeout"=>time() + $timeout, 
					"referer"=>GetServerVar("HTTP_REFERER"),
					"ua"=>GetServerVar("HTTP_USER_AGENT")
				), true);
			}
			// return
			return false;
		}	

	}
	
	
	# -------------------------------------------------------------------------------------------------------------------
	# (macro) mgHoneypotTrap
	function mgHoneypotTrap($value, $data = Array(), $type = HONEYPOT_TRAPTYPE_REQUEST, $timeout = HONEYPOT_TIMEOUT_TRAP) {
		// create object
		$m = new mgHoneypot();
		// lay trap
		return $m->RegisterTrap($value, $type, $timeout, $data);
	}
	
	# -------------------------------------------------------------------------------------------------------------------
	# (macro) mgHoneypotRandomString - creates random strings
	define("HONEYPOT_RANDOM_FIRSTNAME", "jacob,michael,isabella,sophia,alexander,daniel,chloe,olivia,james,sarah,jeanny,christina,eric,andrew,julian,kerry,john,julia,tina");
	define("HONEYPOT_RANDOM_LASTNAME", "smith,jones,brown,miller,thomas,jackson,davis,johnson,lewis,rodriguez,lee,walker,young,lopez,baker,morris,turner,watson,peterson");
	define("HONEYPOT_RANDOM_CITY", "bakersfield,midway,fairway,riverside,liberty,oakland,shady grove,cedar grove,highland,goergetown,bethel,concord");
	define("HONEYPOT_RANDOM_COUNTY", "sideriver,toyland,catchu,christmasland,albertos,easterside,memorial-lane,catmouse,clarisse,picture");
	define("HONEYPOT_RANDOM_STATE", "state");
	define("HONEYPOT_RANDOM_STATENAME", "statename");
	define("HONEYPOT_RANDOM_FULLNAME", "fullname");
	define("HONEYPOT_RANDOM_ZIP", "zip");
	define("HONEYPOT_RANDOM_CSSID", "premium,basic,free,corner,bolded,stronged,table,row,cell,red,blue,green,white");
	
	
	function mgHoneypotRandomString($type) {
		switch($type) {
			case HONEYPOT_RANDOM_CSSID:
				$values = explode(",", HONEYPOT_RANDOM_CSSID);
				return sprintf("-%s%s%s", 
					chr(rand(97, 122)),
					rand(0,1)==1?"-":"",
					$values[rand(0, count($values)-1)]
				);
				break;
		
			case HONEYPOT_RANDOM_ZIP:
				return rand(10000, 99999);
				break;
				
			case HONEYPOT_RANDOM_STATENAME:
				return mgHoneypotRandomString(array_values(mgReadOption(REGION_STATES_US))); 
				break;
			
			case HONEYPOT_RANDOM_STATE:
				return mgHoneypotRandomString(array_keys(mgReadOption(REGION_STATES_US)));
				break;
		
			case HONEYPOT_RANDOM_FULLNAME:
				return sprintf("%s %s", 
					mgHoneypotRandomString(HONEYPOT_RANDOM_FIRSTNAME),
					mgHoneypotRandomString(HONEYPOT_RANDOM_LASTNAME)
				);
				break;
				
			default:
				// get value
				if(is_string($type)) $type =  explode(",", $type);
				// return
				if(is_array($type) && count($type) != 0) return ucwords(@$type[rand(0, count($type)-1)]);
				break;
		}
		return false;
	}
?>