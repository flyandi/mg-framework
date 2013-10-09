<?php
	/* 
		(mg)framework Version 5.0
		
		Copyright (c) 1999-2011 eikonlexis LLC. All rights reserved.
		
		This program is protected by copyright laws and international treaties.
		Unauthorized reproduction or distribution of this program, or any 
		portion thereof, may result in serious civil and criminal penalties.
	
		Module 		Spam Controller
	*/
	
	# -------------------------------------------------------------------------------------------------------------------
	# (constants)
	define("SPAM_ANY", true);
	define("SPAM_URL", "url");
	define("SPAM_UAGENT", "uagent");
	define("SPAM_KEYWORD", "keyword");
	
	define("SPAM_KEYWORDLIST_SPAM", sprintf("%s/spamwords.txt", FRAMEWORK_DATABASEPATH));
	define("SPAM_KEYWORDLIST_BADWORDS", sprintf("%s/badwords.txt", FRAMEWORK_DATABASEPATH));
	define("SPAM_KEYWORDLIST_BLACKWORDS", sprintf("%s/blackwords.txt", FRAMEWORK_DATABASEPATH));

	# -------------------------------------------------------------------------------------------------------------------
	# (class) mgSpam, spam controller
	class mgSpam {
	
		# ---------------------------------------------------------------------------------------------------------------
		# (private)
		private $testfor = SPAM_ANY;
		private $keywords = Array();
	
		# ---------------------------------------------------------------------------------------------------------------
		# (constructor)
		public function __construct($testfor = SPAM_ANY) {
			$this->testfor = $testfor;
			// build keywordlist
			$this->keywords = array_merge(@file(SPAM_KEYWORDLIST_SPAM), @file(SPAM_KEYWORDLIST_BADWORDS), @file(SPAM_KEYWORDLIST_BLACKWORDS));
			// add constants
			$c = get_defined_constants(true);
			if(isset($c['user'])) {
				$this->keywords = array_merge($this->keywords, array_values($c['user']));
			}
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (public) IsKeyword
		public function MatchKeyword($source, $keywordlist =false, $exceptions = Array()) {
			// initialize
			$keywordlist = $keywordlist===false?Array():(!is_array($keywordlist)?Array($keywordlist):$keywordlist);
			// build keyword list
			$l = array_merge($this->keywords, $keywordlist);
			// remove exceptions
			foreach($exceptions as $key=>$value) {
				$k = array_search($value, $l);
				if($k!==false) unset($l[$k]);
			}
			// prepare source
			$source = strtolower($source);
			// cycle
			foreach($l as $index=>$keyword) {
				// prepare keyword
				$keyword = trim($keyword);
				// check
				if(strlen($keyword)>3) {
					// prepare keyword
					if(strpos($keyword, "*")===false) $keyword = "*".$keyword."*";
					// match
					if(fnmatch(strtolower($keyword), $source)) {
						return Array($keyword, $index);
					}
				}
			}
			// return
			return false;
		}
		
			
		# ---------------------------------------------------------------------------------------------------------------
		# (public) IsSpam
		public function IsSpam($source) {
			// keyword
			if($this->MatchKeyword($source)) return true;
			// no spam
			return false;
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (public) RemoveSpam
		public function RemoveSpam($buffer) {
			// remove links
			$buffer = $this->RemoveLinks($buffer);
			// return buffer
			return $buffer;
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (public) RemoveLinks
		public function RemoveLinks($buffer) {
			$buffer = preg_replace("/(www|http|https|ftp|ftps)(\:\/\/|\.)[a-zA-Z0-9\-\.]+\.[a-zA-Z]+(\/\S*)?/", "", $buffer);
			$buffer = preg_replace("/[a-zA-Z0-9\-\.]+\.(com|net|org)(\/\S*)?/", "", $buffer);
			
			return $buffer;
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (public) RemoveLinks
		public function CheckArrayHash($array, $at = 150) {
			// sort array
			$hash = Array(); $text = Array();
			foreach($array as $name=>$value) {

				if(strpos($name, "_hash")!==false) {
					$hash[$name] = $value;
				} else {
					$text[$name] = $value;
				}
			}
			// set result
			$result = true; 			
			/*
			foreach($text as $name=>$value) {
				// get hashs
				$h = isset($hash[sprintf("%s_hash", $name)])?$hash[sprintf("%s_hash", $name)]:false;
				// test hash
				if(!$h) {$result = false; break;}
				// get array
				$a = explode(",", $h);
				// flip array
				DebugWrite($a);
				$f = array_flip($a);
				
				// check first value, needs to be empty
				if(count($f)==0||$f[0]!="") {$result = false; break;}
				// initialize r
				$r = false;
				// calculate differences between times on keystroke
				foreach($f as $v) {
					if($r!==false) {
						$d = $v - $r;
						DebugWrite(sprintf("%s = %s - %s = %s", $name, $v, $r, $d));
						if($d < $at) {$result = false; break;}	
					}
					$r = $v;
				}
			} */
			// return true
			return $result;
		}
	}
?>