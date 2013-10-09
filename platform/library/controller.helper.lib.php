<?php
	/* 
		(mg)framework Version 5.0
		
		Copyright (c) 1999-2011 eikonlexis LLC. All rights reserved.
		
		This program is protected by copyright laws and international treaties.
		Unauthorized reproduction or distribution of this program, or any 
		portion thereof, may result in serious civil and criminal penalties.
	
		Module 		Helper Library
	*/
	
	# -------------------------------------------------------------------------------------------------------------------
	# Cookie Management
	# -------------------------------------------------------------------------------------------------------------------
	
	// Constants
	define("COOKIETIME_30MIN", 1800);
	define("COOKIETIME_60MIN", 3600);
	define("COOKIETIME_MINUTE", 60);
	define("COOKIETIME_MONTH", 2592000);
	
	// (function) mgSetCookie, sets a path cookie
	function mgSetCookie($param0,$param1,$time=COOKIETIME_60MIN) {
		setcookie($param0, $param1, time()+$time, "/"); 
	}

	# -------------------------------------------------------------------------------------------------------------------
	# JSON/MSON Data Management
	# -------------------------------------------------------------------------------------------------------------------
	
	# -------------------------------------------------------------------------------------------------------------------
	# (function) mgJSONDecode, decodes a javascript like JSON string
	function mgJSONDecode($json, $assoc = false) {
		return json_decode(preg_replace('/([{,])(\s*)([^"]+?)\s*:/','$1"$3":',str_replace(array("\n","\r"),"",$json)), $assoc);
	}
	
	# -------------------------------------------------------------------------------------------------------------------
	# (function) mgJSONResult, encodes a array to JSON with an header
	function mgJSONResult($result, $data, $header=Array()) {
		return json_encode(Array("result"=>$result, "header"=>$header, "data"=>$data));
	}
	
	# -------------------------------------------------------------------------------------------------------------------
	# (function) mgMSONDecode, decodes a MSON formatted string
	function mgMSONDecode($mson) {
		// initialize
		$result = Array();
		// mson
		if(is_array($mson)&&isset($mson[0]["source"])) $mson = $mson[0]["source"];
		// create nodes
		$nodes = explode(";", trim($mson));
		// walk nodes
		foreach($nodes as $node) {
			// split node name
			$parts = explode(":", $node);
			// create parameters string
			$params = preg_split("/,[^\[(.*?)\]]/", preg_replace("/\(|\)/", "", trim(@$parts[1])));
			// find parameter lists
			foreach($params as $key=>$param) {
				if(@$param{0}=="[") {
					$params[$key]=explode("/", preg_replace("/\[|\]/", "", $param));
				}
			}
			// assign values
			$result[trim($parts[0])] = $params;
		}		
		// result
		return $result;
	}
	
	# -------------------------------------------------------------------------------------------------------------------
	# Options Management
	# -------------------------------------------------------------------------------------------------------------------
	
	// OptionCache
	$OPTIONCACHE = Array();
	
	// (mgGetOption)
	function mgFormatOptionValue($type, $value, $write = false) {
		switch($type) {
			case OPTION_TYPE_COLLECTION: $value = $write?serialize($value):unserialize($value); break;
			case OPTION_TYPE_INTEGER: $value = (integer) $value; break;
		}		
		// return value
		return $value;	
	}
	
	// (mgGetOption)
	function mgGetOption($name, $isid = false, $returnarray = false) {
		// check cache first
		$cached = false;
		if(isset($GLOBALS['OPTIONCACHE'][$name])) {
			$cached = true;
			$db = $GLOBALS['OPTIONCACHE'][$name];
		} else {
			// run db
			$db = new mgDatabaseObject(DB_TABLE_OPTIONS, DB_SELECT, $isid?Array(DB_FIELD_IDSTRING=>$name):Array(DB_FIELD_NAME=>$name));
		}
		// get result
		$result = $db->result==DB_OK?($returnarray?$db->getrow():$db):false;
		// check result
		if($result&&$returnarray) {
			$result["value"] = mgFormatOptionValue($db->Read(DB_FIELD_TYPE), $db->Read(DB_FIELD_VALUE), false);
		}
		// store in cache
		if(!$cached) {
			$GLOBALS['OPTIONCACHE'][$name] = $db;
		}
		// return result
		return $result;
	}
	
	// (mgGetOptionValue)
	function mgGetOptionValue($name, $key = false, $isid = false) {
		// get option
		$option = mgGetOption($name, $isid, true);
		// check if value exists
		if(is_array($option["value"])) {
			$a = $option["value"];
			return isset($a[$key])?$a[$key]:false;
		}
		return @$option["value"];
	}
	
	// (mgDeleteOption)
	function mgDeleteOption($name, $isid = false) {
		if($db = mgGetOption($name, $isid)) {
			$db->Delete();
			return true;
		}
		return false;
	}
	
	// (mgOptionExists)
	function mgOptionExists($name, $isid = false) {
		return mgGetOption($name, $isid)!==false?true:false;
	}	
	
	// (mgWriteOption)
	function mgWriteOption($name, $value, $isid=false, $secured=false) {
		if($db = mgGetOption($name, $isid)) {
			$db->Write(DB_FIELD_VALUE, mgFormatOptionValue($db->Read(DB_FIELD_TYPE), $value, true), true);
			return true;
		}
		return false;
	}	
	
	// (mgReadOption)
	function mgReadOption($name, $isid=false, $secured=false) {
		$result = false;
		if($db = mgGetOption($name, $isid)) {
			$result = mgFormatOptionValue($db->Read(DB_FIELD_TYPE), $db->Read(DB_FIELD_VALUE), false);
		}
		return $result;
	}
	
	// (mgRegisterOption)
	function mgRegisterOption($name, $type = OPTION_TYPE_STRING, $mode = OPTION_MODE_CUSTOM) {
		// initialize result
		$result = false;
		// verify option
		if(mgOptionExists($name)) return $result;
		// create db 
		$db = new mgDatabaseObject(DB_TABLE_OPTIONS, DB_CREATE);
		// check
		if($db->result==DB_OK) {
			// write name
			$db->Write(Array(
				DB_FIELD_NAME=>$name,
				DB_FIELD_TYPE=>$type,
				DB_FIELD_MODE=>$mode,
			), true);
			// return id
			$result = $db->Read(DB_FIELD_IDSTRING);
		}
		// return result
		return $result;
	}
	
	# -------------------------------------------------------------------------------------------------------------------
	# Cache and HTML Management
	# -------------------------------------------------------------------------------------------------------------------

	# -------------------------------------------------------------------------------------------------------------------
	# (function) mgCreateHTMLHeaderRef, creates a html header reference
	// constants
	define("HEADER_HTML_VCARD", "vcard");
	
	// macro
	function mgCreateHTMLHeaderRef($type) {
		switch($type) {
			case HEADER_HTML_VCARD: return Tag("link", Array("rel"=>"profile", "href"=>"http://microformats.org/profile/hcard"), false, true); break;
		}
	}
	
	# -------------------------------------------------------------------------------------------------------------------
	# (function) mgScriptVar, creates a javascript confirm variable
	function mgScriptVar($name, $value = false) {
		if(is_array($value)) { 
			$value = json_encode($value);
		} else if(is_string($value)) {
			$value = sprintf("'%s'", $value);
		} else {
			if(strlen($value)==0){return "";}
		}
		return sprintf("var %s=%s;", $name, $value);
	}
	
	# -------------------------------------------------------------------------------------------------------------------
	# (function) mgStyleVar, creates a css class
	function mgStyleVar($name, $value = false, $isclass = true) {
		if(is_array($value)) {
			$r = Array();
			foreach($value as $n=>$v) {
				$r[] = sprintf("%s:%s", $n, $v);
			}
			$value = implode(";", $r);
		} 
		$value = trim($value);
		if(!$value||strlen($value)==0) return "";
		return sprintf("%s%s{%s}", $isclass?".":"", $name, $value);
	}
	
	
	
	# -------------------------------------------------------------------------------------------------------------------
	# (function) mgIsFormPosted, returns true if a form was posted
	function mgIsFormPosted() {
		return GetVar(FORM_POST)==FORM_ISPOST;
	}
	
	# -------------------------------------------------------------------------------------------------------------------
	# (function) mgHTMLCompress, compresses the HTML
	function mgHTMLCompress($buffer){
		$buffer = str_replace("\n", "", $buffer);
		$buffer = str_replace("\r", "", $buffer);
		$buffer = str_replace("\t", "", $buffer);
		$buffer = str_replace("  ", " ", $buffer);
		return $buffer;
	}
	
	# -------------------------------------------------------------------------------------------------------------------
	# mgHTMLBody, compresses all content between the body tag
	function mgHTMLCompressBody($buffer, $width=false){
		$starttag = "<body>"; $endtag = "</body>";
		
		$start = strpos($buffer, $starttag);
		$end   = strpos($buffer, $endtag);
		if($start===false||$end===false){return $buffer;}
		$nbuffer = mgHTMLCompress(substr($buffer, $start+strlen($starttag), $end-$start-strlen($starttag)));
		
		// replace tet
		$buffer = substr($buffer, 0, $start+strlen($starttag)).$nbuffer.substr($buffer, $end);

		return $buffer;
	}	
	
	# (function) mgGetSelectContentArray, returns the array as select content with optional selection
	function mgGetSelectContentArray($a, $ccid="") {
		$c = "";
		while(list($cid, $cname)=each($a)){	
			$c .= Tag("option", array_merge(Array("value"=>$cid), ($ccid == $cid)?Array("selected"=>TAG_NOVALUE):Array()), $cname);
		}
		return $c;
	}	
	
	# (function) mgClearFloat
	function mgClearFloat() {
		return Div("-clear");
	}
	
	# -------------------------------------------------------------------------------------------------------------------
	# URL functions
	# -------------------------------------------------------------------------------------------------------------------	
	
	# (function) mgGetObfuscatedEMailAddress
	function mgGetObfuscatedEMailAddress($email) {
		// initialize
		$result = "";
		// ignore
		$ignore = array('.', ':', '@');
		// Encode string using oct and hex character codes
		for ($i=0;$i<strlen($email);$i++) {
			// Encode 25% of characters including several that always should be encoded
			if (in_array($email[$i], $ignore) || mt_rand(1, 100) < 25) {
				if (mt_rand(0, 1)) {
					$result .= '&#' . ord($email[$i]) . ';';
				} else {
					$result .= '&#x' . dechex(ord($email[$i])) . ';';
				}
			} else {
				$result .= $email[$i];
			}
		}
		// return result
		return $result;
	}
	
	# (function) mgObfuscatedEMailLink
	function mgObfuscatedEMailLink($email, $params = Array()) {
		// initialize
		$result = "";
		// initialize coding array
		$ignore = Array(".", "@", "+");
		// parse email
		$em = "";
		for($i=0;$i<strlen($email);$i++) {
			// Encode 25% of characters
			if (!in_array($email[$i], $ignore) && mt_rand(1, 100) < 25) {
				$charCode = ord($email[$i]);
				$em .= '%';
				$em .= dechex(($charCode >> 4) & 0xF);
				$em .= dechex($charCode & 0xF);
			} else {
				$em .=  $email[$i];
			}
		}
		// parse
		$obfuscatedEmail = mgGetObfuscatedEMailAddress($email);
		$obfuscatedEmailUrl = mgGetObfuscatedEMailAddress('mailto:' . $em);
		// limit
		if(isset($params['limit'])) {
			if(strlen($obfuscatedEmail)>$params['limit'][0]) $obfuscatedEmail = $params['limit'][1];
		}
		// create link
		return Tag('a', Array("href"=>$obfuscatedEmailUrl, "rel"=>"nofollow"), $obfuscatedEmail);
	}
	
	# (function) mgCorrectUrl, corrects a url and validates
	function mgCorrectUrl($url) {
		$url = mgParseUrl($url);
		return sprintf("http://%s", @$url["path"]);
	}
	
	# (function) mgParseUrl, parses a url and returns as array
	function mgParseUrl($url) {
		return parse_url($url);		// Native PHP 5 support
	}	
	
	# (function) mgUTFParseUrl, parses a url and returns as array
	function mgUTFParseUrl($url) {
		static $keys = array('scheme'=>0,'user'=>0,'pass'=>0,'host'=>0,'port'=>0,'path'=>0,'query'=>0,'fragment'=>0);
		if (is_string($url) && preg_match(
				'~^((?P<scheme>[^:/?#]+):(//))?((\\3|//)?(?:(?P<user>[^:]+):(?P<pass>[^@]+)@)?(?P<host>[^/?:#]*))(:(?P<port>\\d+))?' .
				'(?P<path>[^?#]*)(\\?(?P<query>[^#]*))?(#(?P<fragment>.*))?~u', $url, $matches))
		{
			foreach ($matches as $key => $value)
				if (!isset($keys[$key]) || empty($value))
					unset($matches[$key]);
			return $matches;
		}
		return false;
	} 	
	
	# (function) mgIsDomain
	function mgIsDomain($url) {
		// return 
		return preg_match("@^(https?\://)?(www\.)?([a-z0-9]([a-z0-9]|(\-[a-z0-9]))*\.)+[a-z]+$@i", $url) != 0;
	}
	
	# (function) mgGetURLQueryParameters
	function mgGetURLQueryParameters($query = false, $keytolower = false) {
		// get query
		$query  = explode('&', $query?$query:@$_SERVER['QUERY_STRING']);
		// initialze result
		$result = Array();
		// cycle
		foreach($query as $param) {
			// parse param
			$values = explode('=', $param);
			// test
			if(count($values)==2) {
				$key = urldecode($values[0]);
				$result[$keytolower?strtolower($key):$key] = urldecode($values[1]);
			}
		}
		// return result
		return $result;
	}
	
	# (function) mgCleanURLPath - removes invalid characters from a URL
	function mgCleanURLPath($path) {
		$path = preg_replace("/ /g", "-", $path);
		$path = preg_replace("/[^a-zA-Z0-9\-\/]/g", "", $path);
		return strtolower($path);
	}	
		

	# -------------------------------------------------------------------------------------------------------------------
	# Error Formatting
	# -------------------------------------------------------------------------------------------------------------------
	
	# -------------------------------------------------------------------------------------------------------------------
	# (function) mgFieldError, creates a single error message
	function mgFieldError($caption, $icon=true){
		//$icon = ($icon==true)?Tag("span", Array("class"=>"ui-icon ui-icon-alert")):"";
		$icon = "";
		return Tag("p", Array("class"=>"error-field"), $icon.$caption);
	}
	
	# -------------------------------------------------------------------------------------------------------------------
	# (function) mgErrorList, creates a error list (array/string)
	function mgErrorList($params=false, $icon=true){
		// sanity check
		if($params===false||$params==null) return "";
		// check if string
		if(is_string($params)) {return mgFieldError($params, $icon);}
		// initialize
		$result = "";
		// cycle
		foreach($params as $field) { $result .= mgFieldError($field, $icon);}
		// return result
		return $result;
	}	
	
	# -------------------------------------------------------------------------------------------------------------------
	# String Functions
	# -------------------------------------------------------------------------------------------------------------------
	
	# -------------------------------------------------------------------------------------------------------------------
	# (function) mgIssetArray
	function mgIssetArray($values, $fields, $notempty = true) {
		foreach($fields as $f) {
			if(!isset($values[$f])||empty($values[$f])||strlen(trim($values[$f]))==0) return false;
		}
		return true;
	}
	
	# -------------------------------------------------------------------------------------------------------------------
	# (function) mgTrimArray
	function mgTrimArray($values, $max = false) {
		$result = Array();
		foreach($values as $name=>$value) {
			$result[$name] = trim($max?substr($value, 0, $max):$value);
		}
		return $result;
	}
	
	# -------------------------------------------------------------------------------------------------------------------
	# (function) mgNumberToWords, converts a number to it's english words
	function mgNumberToWords($number) {
		$hyphen      = '-';
		$conjunction = '';
		$separator   = ', ';
		$negative    = 'negative ';
		$decimal     = ' point ';
		$dictionary  = array(
			0  					=> 'zero',
			1 				 	=> 'one',
			2                   => 'two',
			3                   => 'three',
			4                   => 'four',
			5                   => 'five',
			6                   => 'six',
			7                   => 'seven',
			8                   => 'eight',
			9                   => 'nine',
			10                  => 'ten',
			11                  => 'eleven',
			12                  => 'twelve',
			13                  => 'thirteen',
			14                  => 'fourteen',
			15                  => 'fifteen',
			16                  => 'sixteen',
			17                  => 'seventeen',
			18                  => 'eighteen',
			19                  => 'nineteen',
			20                  => 'twenty',
			30                  => 'thirty',
			40                  => 'fourty',
			50                  => 'fifty',
			60                  => 'sixty',
			70                  => 'seventy',
			80                  => 'eighty',
			90                  => 'ninety',
			100                 => 'hundred',
			1000                => 'thousand',
			1000000             => 'million',
			1000000000          => 'billion',
			1000000000000       => 'trillion',
			1000000000000000    => 'quadrillion',
			1000000000000000000 => 'quintillion'
		);
	   
		if (!is_numeric($number)) {
			return false;
		}
	   
		if (($number >= 0 && (int) $number < 0) || (int) $number < 0 - PHP_INT_MAX) {
			return false;
		}

		if ($number < 0) {
			return $negative . mgNumberToWords(abs($number));
		}
	   
		$string = $fraction = null;
	   
		if (strpos($number, '.') !== false) {
			list($number, $fraction) = explode('.', $number);
		}
	   
		switch (true) {
			case $number < 21:
				$string = $dictionary[$number];
				break;
			case $number < 100:
				$tens   = ((int) ($number / 10)) * 10;
				$units  = $number % 10;
				$string = $dictionary[$tens];
				if ($units) {
					$string .= $hyphen . $dictionary[$units];
				}
				break;
			case $number < 1000:
				$hundreds  = $number / 100;
				$remainder = $number % 100;
				$string = $dictionary[$hundreds] . ' ' . $dictionary[100];
				if ($remainder) {
					$string .= $conjunction . mgNumberToWords($remainder);
				}
				break;
			default:
				$baseUnit = pow(1000, floor(log($number, 1000)));
				$numBaseUnits = (int) ($number / $baseUnit);
				$remainder = $number % $baseUnit;
				$string = mgNumberToWords($numBaseUnits) . ' ' . $dictionary[$baseUnit];
				if ($remainder) {
					$string .= $remainder < 100 ? $conjunction : $separator;
					$string .= mgNumberToWords($remainder);
				}
				break;
		}
	   
		if (null !== $fraction && is_numeric($fraction)) {
			$string .= $decimal;
			$words = array();
			foreach (str_split((string) $fraction) as $number) {
				$words[] = $dictionary[$number];
			}
			$string .= implode(' ', $words);
		}
	   
		return $string;
	}
	
	
	# (function) mgFillVariableString, fills an variable string
	function mgFillVariableString($string, $data, $simplematch = false, $st = TEMPLATE_FIELD_BEGIN, $et = TEMPLATE_FIELD_END) {
		foreach(is_array($data)?$data:Array() as $name=>$value) {
			if(is_string($value)) {
				// template field
				$string = str_replace(sprintf("%s%s%s", $st, $name, $et), $value, $string);
				// test simple match
				if($simplematch) {
					$string = str_replace($name, $value, $string);
				}
			}
		}
		return $string;
	}
	
	# (function) mgFormatVariableFields, formats variable fields
	function mgFormatVariableFields($s, $translate=false) {
		if(preg_match_all("/%(.*?)%/is", $s, $matches)) {
			// check references
			if(isset($matches[1])&&is_array($matches[1])&&count($matches[1])!=0) {
				// replace
				foreach($matches[1] as $v) {
					if(strpos($v, " ")===false) {
						$s = str_replace(sprintf("%%%s%%", $v), sprintf("%s", $translate!==false?$translate->_($v):$v), $s);
					}
				}
			}
		}
		return $s;
	}
	
	# (function) mgCreateArrayVariables
	function mgCreateArrayVariables($data, $path = false) {
		// initialize
		$result = Array();
		// cycle
		if(is_array($data)) {
			foreach($data as $key=>$value) {
				// create path variable
				$vn = sprintf("%s%s", $path?sprintf("%s.", $path):"", $key);
				// test value
				if(is_array($value)) {
					$result = array_merge($result, mgCreateArrayVariables($value, $vn));
				} else {
					$result[$vn] = $value;
				}
			}
		}
		
		// return result
		return $result;
	}
	
	# (function) mgCreateVariables, creates a variable map from an array
	function mgCreateVariables($data, $path = false, $framework = false) {
		// initialize result
		$result = Array();
		// find serialized data
		
		foreach($data as $key=>$value) {
			// try to unserialize value
			$dt = @unserialize($value);
			
			// check value
			if(is_array($dt)&&$dt!==false) {
				$value = $dt;
			}
			// test value
			if(is_array($value)) {
				$result = array_merge($result, mgCreateArrayVariables($value, $path));
			} else {
				$result[sprintf("%s%s", $path!==false?sprintf("%s.", $path):"", $key)] = $value;
			}	
		}
		// parse data
		foreach($result as $key=>$value) {
			// replace data with data
			$result[$key] = mgFillVariableString(mgFormatVariableFields($value, $framework!==false?$framework->translate:false), $result, true);
		}
		// return result
		return $result;
	}
	
	# (function) mgCreateArrayFromVariables, creates a variable from a path variable
	function mgCreateArrayFromVariables($values, $path) {
		// initialize result
		$result = Array();
		// cycle
		foreach($values as $name=>$value) {
			// test
			if(mgLeftString($name, $path)) {
				// modify variable
				$v = explode(".", str_replace($path.".", "", $name), 2);
				// check id
				if(count($v)==2) {
					// get index
					$index = (integer)$v[0];
					// get name
					$name = $v[1];
					// check if index is present
					if(!isset($result[$index])) $result[$index] = Array();
					// assign value
					$result[$index][$name] = $value;
				}
			}
		}
		// return result
		return $result;
	}
	
	# (function) mgModifyArrayFields, modifies fields in an array
	function mgModifyArrayFields($values, $before = false, $after = false) {
		// initialize
		$result = Array();
		// cycle
		foreach($values as $field=>$value) {
			$result[sprintf("%s%s%s", $before?$before:"", $field, $after?$after:"")] = $value;
		}
		// return
		return $result;
	}
	
	# (function) mgFilterArrayFields
	function mgFilterArrayFields($values, $list) {
		// initialize
		$result = Array();
		// cycle
		foreach($list as $field) {
			if(isset($values[$field])) {
				$result[$field] = $values[$field];
			}
		}
		// return
		return $result;
	}
	
	# (function) mgCreateLink
	function mgCreateLink($link) {
		return strtolower(str_replace(" ", "-", $link));
	}

	# (function) mgLeftString
	function mgLeftString($needle, $stack) {
		return substr(strtolower($needle), 0, strlen($stack))==strtolower($stack);
	}
	
	# (function) mgRightString
	function mgRightString($needle, $stack) {
		return substr(strtolower($needle), strlen($needle)-strlen($stack))==strtolower($stack);
	}
	
	
	# (function) mgContent, returns the filename as string
    function mgContent($filename) 	{ 	
		return file_exists($filename)?file_get_contents($filename):$filename;
	}  		
	
	# (function) mgCompare, compares two string
	function mgCompare($a,$b,$noempty=false){
		if($noempty&&(strlen(trim($a))==0||strlen(trim($b))==0)) return false;
		return (strtolower(trim($a))==strtolower(trim($b))); 
	}
	
	# (function) mgInCompare, compares two string
	function mgInCompare($a,$b,$noempty=false){
		if($noempty&&(strlen(trim($a))==0||strlen(trim($b))==0)) return false;
		// get result
		return strpos($a, $b)===false?(strpos($b, $a)===false?false:true):true;
	}
	
	# (function) mgToNumber, converts a string to only numbers
	function mgToNumber($a){return preg_replace("/\D/", "", $a);}
	
	# (function) mgFormatString, formats an mg string 
	function mgFormatString($format, $args) {
		// initialize
		$result = $format;
		// cycle
		foreach($args as $key=>$value) {
			// pre format
			if(is_numeric($key)){$key += 1;}
			// replace
			$result = str_replace("%".$key, $value, $result);
		}
		// return result
		return $result;
	}
	
	# (function) mgReplaceBetweenTag, replaces the content in a tag
	function mgReplaceBetweenTag($tag, $new, $source){
		return preg_replace("/(\<{$tag}.*?\>)(.*?)(\<\/{$tag}\>)/ism", "$1{$new}$3", $source);
	}
	
	# (function) mgReplaceExactString, replaces an exact string
	function mgReplaceExactString($i, $r, $s) {
		if(strpos(sprintf("%s ", $i), $s)!==false) {
			return str_replace($i, $r, $s);
		} else if ($i == $s) {
			return $r;
		}
		return $s;
	}
	
	# (function) mgRemoveTag, replaces the tag with something else
	function mgRemoveTag($tag, $new, $source){
		return preg_replace("/(\<{$tag}(.*)?\>)/ism", $new, $source);
	}	
	
	# (function) mgFormatSEOFriendly, creates a seo friendly string
	function mgFormatSEOFriendly($s) {
		// lower case
		$s = strtolower($s);
		// strip any unwanted characters
		$s = preg_replace("/[^a-z0-9_\s-]/", "", $s);
		// clean multiple dashes or whitespaces
		$s = preg_replace("/[\s-]+/", " ", $s);
		// convert whitespaces and underscore to dash
		$s = preg_replace("/[\s_]/", "-", $s);
		// return
		return $s;
	}
	
	# (function) mgSanitizeString, returns a sanitized string
	function mgSanitizeString($s, $striphtml = true) {
		if($striphtml) {
			// remove all html references
			$s = preg_replace('/<[^>]*>/', '', $s);
		} else {
			// convert html characters to 
			$s = htmlentities($s);
		}
		// remove illegal chars
		$s = str_replace("\"", "", $s);
		$s = preg_replace('/[^\w\d_ -.@]/si', '', $s);
		// return string
		return $s;
	}

	# (function) mgSanitizeArray, sanitizes a array of strings
	function mgSanitizeArray($a, $striphtml = true) {
		// initialize string
		$result = Array();
		// cycle array
		foreach($a as $k=>$v) {
			// call string sanitization
			$result[$k] = mgSanitizeString($v, $striphtml);
		}
		// return result
		return $result;
	}

	
	# -------------------------------------------------------------------------------------------------------------------
	# Array Functions
	
	# -------------------------------------------------------------------------------------------------------------------
	# (function) mgGetArrayValue, sometimes useful
	function mgGetArrayValue($o, $key) {
		return isset($o[$key])?$o[$key]:null;
	}
	
	# -------------------------------------------------------------------------------------------------------------------
	# (function) mgObjectify
	function mgObjectify($o) {
		return is_object($o)?$o:(object)$o;
	}
	
		
	# -------------------------------------------------------------------------------------------------------------------
	# (function) mgSearchArray
	function mgSearchArray($array, $key, $value, $asbool = false, $multi = false) {
		$result = false;
		// get
		$x = 0;
		foreach($array as $k=>$v) {
			$v = is_object($v)?(array)$v:$v;
			if($multi&&isset($v[$key])&&mgCompare($v[$key], $value)) {
				$result = $x;
				break;
			} else if(mgCompare($k, $key)&&mgCompare($v, $value)) {
				$result = $x;
				break;
			}
			$x+=1;
		}
		return $result!==false?($asbool?true:$result):($asbool?false:-1);
	}
	
	# -------------------------------------------------------------------------------------------------------------------
	# (function) mgSortArray
	function mgSortArray(&$array, $key) {
		$sorter=array();
		$ret=array();
		reset($array);
		foreach ($array as $ii => $va) {
			$sorter[$ii]=$va[$key];
		}
		asort($sorter);
		foreach ($sorter as $ii => $va) {
			$ret[$ii]=$array[$ii];
		}
		$array=$ret;
	}
	
	# -------------------------------------------------------------------------------------------------------------------
	# Region Functions
	
	# constants
	define("REGION_STATES_US", "region.states.us");
	
	# GetRegionName
	function GetRegionName($name, $data = REGION_STATES_US) {
		// get data
		$data = mgReadOption($data);
		// get name
		return isset($data[$name])?$data[$name]:$name;
	}
	
	# -------------------------------------------------------------------------------------------------------------------
	# Reflection
	
	function mgIsClosure($f) {
		$rf = new ReflectionFunction($f);
		return $rf->isClosure();
	}
	
	