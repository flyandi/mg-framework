<?php
	/* 
		(mg)framework Version 5.0
		
		Copyright (c) 1999-2011 eikonlexis LLC. All rights reserved.
		
		This program is protected by copyright laws and international treaties.
		Unauthorized reproduction or distribution of this program, or any 
		portion thereof, may result in serious civil and criminal penalties.
	
		Module 		Translate/Internationalization Module
	*/	
	
	# -------------------------------------------------------------------------------------------------------------------
	# (macro) returns the localized item in the array
	function mgLocalizedArray($a, $localized = true, $selectvalue = false) {
		// initialize
		if($localized===true) {$localized = DEFAULTVALUE;}
		// cycle array
		foreach(Array($localized, DEFAULTVALUE) as $l) {
			if(isset($a[$l])) {
				if(isset($a[$l][$selectvalue])) {
					$r = trim($a[$l][$selectvalue]);
					if(strlen($r)!=0) return $r;
				} else {
					return $a[$l];
				}
			}
		}
		return false;
	}
	
	# -------------------------------------------------------------------------------------------------------------------
	# (class) mgLanguageReader, returns all languages from a path as array
	class mgLanguageReader {
		# ---------------------------------------------------------------------------------------------------------------
		# local stack
		private $storages;

		# ---------------------------------------------------------------------------------------------------------------
		# (constructor)
		public function __construct($languages, $path) {
			// reset local storage
			$storages = Array();
			// load storages
			foreach($languages as $l) {
				// prepare l
				$l = trim(strtolower($l));
				// prepare fn
				$fn = sprintf("%s/%s/language/%s", dirname($path), $l, sprintf(LANGUAGE_FILENAME, $l));
				// check filename
				if(file_exists($fn)) {
					// load xml
					$xml = mgLoadXML($fn);
					// add to storages
					$storages[$l] = $xml->l->toArray();
				}
			}
			// compile storages
			$compiled = Array();
			$primary = key($languages); // primary language
			
			// cycle storages
			foreach($storages as $language=>$items) {
				// cycle items
				foreach($items as $item) {
					// get id
					$id = isset($item["@attributes"]["id"])?$item["@attributes"]["id"]:false;
					// check id
					if($id!==false) {
						// get id
						$index = mgSearchArray($compiled, "id", $id);
						// check id in compiled array
						if($index==-1) {
							$index = count($compiled);
							$compiled[] = Array(
								"id"=>$id,
								"related"=>DefaultValue(@$item["@attributes"]["related"], ""),
								"literal"=>DefaultValue(@$item["@attributes"]["literal"], false),
							);
						}
						// assign value (overwrites newer language ids)
						if(!isset($compiled[$index][$language])) {
							$compiled[$index][$language] = @$item[0];
						}
					}
				}
			}
			$this->storages = $compiled;
			// test save for future use
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (count) returns all
		public function count() {
			return count($this->storages);
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (getall) returns all
		public function getall() {
			return $this->storages;
		}
		
		
		
	
	}
	

	# -------------------------------------------------------------------------------------------------------------------
	# (class) mgTranslate, basic input/output translate based on mgXML
	class mgTranslate {
	
		# ---------------------------------------------------------------------------------------------------------------
		# local stack
		private $storage;

		# ---------------------------------------------------------------------------------------------------------------
		# (constructor)
		public function __construct($filename=false, $xml=false) {
			// check filename
			if(!$filename&&$xml) {
				$this->storage = $xml;
			} else {
				// multiple file support
				if(!is_array($filename)) {$filename = Array($filename);}
				// create objects
				$this->storage = new mgXML("<language></language>");
				// cycle filenames
				foreach($filename as $fn) {
					if(file_exists($fn)) {
						$this->storage->MergeFrom(new mgXML(@file_get_contents($fn)));
					}
				}
			}
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (public) _, translate (string/array)
		public function _($identifier, $values=null, $default = null) {
			// translate single string
			if(is_string($identifier)){return $this->__translate($identifier, $values, $default);} else 
			// translate array
			if(is_array($identifier)) {
				$result=Array();
				foreach($identifier as $id) {
					$result[]=$this->__translate($id, $values, $default);
				}
				return $result;
			}	 
			// default result
			return $default;
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (public) arrayindex, finds the request value in pattern and translates it
		public function arrayindex($request, $pattern, $values=null) {
			// check if request is in pattern
			if(!array_key_exists($request, $pattern)){$request=DEFAULTVALUE;}
			// translate
			return $this->_(@$pattern[$request], $values);
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (public) match, matches a id to it's attribute
		public function match($id, $relative, $default = false) {
			// search in attribute
			if($group = $this->storage->l->GetAttributeAll("id", $id, false)) {
				// cycle result
				foreach($group as $index=>$node) {
					// match relative
					if(mgCompare($node["relative"], $relative)) {
						// found and return
						return $this->__compile((string)$node, null, $node);
					}
				}
			}
			// return default
			return $default;
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (private) __translate, translates a single identifier
		private function __translate($identifier, $values=null, $default = null) {
			// get content
			$node = $this->storage->l->GetAttribute("id", $identifier);
			// sanity check content
			if((string)$node=="") {
				return $default===null?$identifier:$default;
			}
			// return translation
			return $this->__compile((string)$node, $values, $node);
		} 
		
		# ---------------------------------------------------------------------------------------------------------------
		# (private) __compile, compiles the translated string
		private function __compile($content, $values=null, $node) {
			// check multiple
			if(isset($node["multiple"])&&$node["multiple"]=="true") { 
				// break into lines
				$b = explode("\n", $content);
				// reset content
				$content = "";
				// go through each line
				foreach($b as $line) {
					// remove all breaks
					$content .= trim(str_replace(array("\n", "\r"), "", $line));
				}
			}
			// replace encoded chars
			foreach(Array("##"=>"&") as $n=>$r) {
				$content = str_replace($n, $r, $content);
			}			
			// replace [] 
			$content = str_replace("[", "<", str_replace("]", ">", $content));			
			// check literal
			if(isset($node["literal"])&&$node["literal"]=="true") { return $content;}
			// process references in content
			preg_match_all("/%(.*?)%/is", $content, $matches);
			// check references
			if(isset($matches[1])&&is_array($matches[1])&&count($matches[1])!=0) {
				// replace
				foreach($matches[1] as $v) {
					if(strpos($v, " ")===false) {
						$content = str_replace(sprintf("%%%s%%", $v), $this->__translate($v), $content);
					}
				}
			}
			// format content and return
			return mgFillVariableString($content, $values);
		}
	}
	
