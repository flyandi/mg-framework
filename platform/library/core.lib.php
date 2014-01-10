<?php
	/* 
		(mg)framework Version 5.0
		
		Copyright (c) 1999-2011 eikonlexis LLC. All rights reserved.
		
		This program is protected by copyright laws and international treaties.
		Unauthorized reproduction or distribution of this program, or any 
		portion thereof, may result in serious civil and criminal penalties.
	
		Module 		Core Library (Essential Functions)
	*/

	# -------------------------------------------------------------------------------------------------------------------
	# Constants
	define("TAG_NOVALUE", null);
	
	# -------------------------------------------------------------------------------------------------------------------
	# Div, creates a div element
	function Div($class, $id = "", $content = "", $style = "") {
		return Tag("div", Array("id"=>$id, "class"=>$class, "style"=>$style), $content);
	}
	
	# -------------------------------------------------------------------------------------------------------------------
	# Tag, creates a html tag element <></>
	function Tag($tagname, $tagargs, $content="", $opentag = false) {
		$cn = "";
		if (is_array($tagargs)) {
			foreach($tagargs as $key=>$value) {
				if($value!==false) {
					if($value===TAG_NOVALUE){
						$cn .= $key." ";
					} else if(strlen($value)>0) {
						$cn .= sprintf("%s=\"%s\" ", $key, $value);
					}
				}
			}
		} else {
			$cn = $tagargs;
		}
		$cn = trim($cn);
		$ret = trim(sprintf("<%s %s", $tagname, strlen($cn)!=0?$cn:""));
		if ($opentag) return $ret."/>";
		return $ret.">".$content."</".$tagname.">";
	}	
	
	# -------------------------------------------------------------------------------------------------------------------
	# TagSelect, creates a select/option field
	function TagSelect($tagargs, $options, $selected=null) {
		$cn = "";
		foreach($options as $value=>$name) {
			$cn .= Tag("option", Array("value"=>$value, (($selected==$value)?"selected":"")=>TAG_NOVALUE), ($name=="")?$value:$name);
		}
		return Tag("select", $tagargs, $cn);
	}

	
	# -------------------------------------------------------------------------------------------------------------------
	# GetVar, returns a var from the stick, priority GP (Get, Post)
	function GetVar($name, $default = "") {
		if (isset($_REQUEST[$name])) return @$_REQUEST[$name];
		if (isset($_COOKIE[$name])) return @$_COOKIE[$name];
		if (isset($_GET[$name])) return urldecode(@$_GET[$name]);
		if (isset($_POST[$name])) return @$_POST[$name];
		if (isset($GLOBALS[$name])) return @$GLOBALS[$name];
		return $default;
	}	
	
	function GetVarEx($name, $variables = false, $default = false) {
		return $variables?DefaultValue(@$variables[$name], $default):GetVar($name, $default);
	}
	
	function GetServerVar($name, $default = "") {
		return isset($_SERVER[$name])?$_SERVER[$name]:$default;
	}
	
	function SetServerVar($name, $value) {
		$_SERVER[$name] = $value;
	}
	
	function GetHTTPVar($name, $default = false) {
		if(function_exists("getallheaders")) {
			$headers = getallheaders();
			return isset($headers[$name])?$headers[$name]:$default;	
		}
		return $default;
	}
	
	function AppVar($name) {
		return defined($name)?constant($name):false;
	}
	
	function IfAppVar($name, $is = null) {
		// get var
		$d = AppVar($name, false);
		// check
		switch(true) {
			case strtolower($d)=="true": $d = true; break;
			case strtolower($d)=="false": $d = false; break;
		}
		return $d&&$d==$is;
	}
	
	function GetQueryString($asarray = true, $withqm = false, $default = "", $fromstring = false) {
		$q = $fromstring!==false?$fromstring:GetServerVar("QUERY_STRING", false);
		if($q!==false&&strlen($q)>0) {
			if($asarray) {
				return array_explodevalues(str_replace("&amp;", "&", $q), "&", "=");
			}
			return sprintf("%s%s", $withqm?"?":"", $q);
		}
		return $default;
	}

	
	# -------------------------------------------------------------------------------------------------------------------
	# EncodePostVar, ensures that the variables can be passed
	function EncodePostVar($variable){return str_replace(".", "@", $variable);}
	
	# -------------------------------------------------------------------------------------------------------------------
	# DecodePostVar, ensures that the variables can be passed
	function DecodePostVar($variable){return str_replace("@", ".", $variable);}
	
	# -------------------------------------------------------------------------------------------------------------------
	# GetSplitVars, returns a splitted array var
	function GetSplitVars($__name) {
		$r = Array(); 
		foreach($_POST as $name=>$value){
			$name=explode(":", $name);
			if((count($name)>1)&&($name[0]==$__name)){
				$r[$name[1]]=$value;
			}
		} 
		return $r;
	}
	
	# -------------------------------------------------------------------------------------------------------------------
	# SetVar, serts a var to the stack, priority GP (Get, Post)
	function SetVar($name, $value = "") {
		$GLOBALS[$name]=$value;
	}		
	
	# -------------------------------------------------------------------------------------------------------------------
	# GetSecureVar, reads a var from the global stack which can't be modified from outside
	function GetSecureVar($name, $default = "") {
		if (isset($GLOBALS[$name])) return @$GLOBALS[$name];
		return $default;
	}		
	
	# -------------------------------------------------------------------------------------------------------------------
	# GetDirVar, evaluates the current directory
	function GetDirVar($i=0, $defaultvalue = "", $path = false){
		if(!$path) {$path = GetServerVar("REQUEST_URI");}
		if(strlen($path)!=0) {
			$r = explode("?", $path);
			$d=explode("/", $r[0]);
			return @DefaultValue(strtolower(@$d[$i+1]), $defaultvalue);
		}
		return false;
	}
	
	# -------------------------------------------------------------------------------------------------------------------
	# GetDirVarEx, evaluates the current directory
	function GetDirVarEx($i=0, $rv = false) {
		$result = GetDirVar($i, false);
		if($result!==false) return $result;
		return GetVar($rv, false);
	}
	
	# -------------------------------------------------------------------------------------------------------------------
	# GetPageRequest, returns the page that is requested
	function GetPageRequest() {
		$r = false;
		if($request = GetServerVar("REQUEST_URI", false)) {
			$r = explode("?", $request);
		}
		return is_array($r)&&isset($r[0])?$r[0]:false;
	}
	
	# -------------------------------------------------------------------------------------------------------------------
	# GetRequestVar, returns the request string after a dir-var
	function GetRequestVar($i=0, $default = false, $nq = false, $rt = false){
		$result = false;
		if($request = GetServerVar("REQUEST_URI", false)) {
			$r=""; $d=explode("/", @$_SERVER['REQUEST_URI'], $i);
			if($i < 1) $i = 1;
			for($x=$i-1;$x<count($d);$x++){ $r.=sprintf("%s%s", $rt?"":"/", $d[$x]);}
			$result = strlen($r)!=0?$r:$default;
			if($nq) {
				$result = explode("?", $result);
				$result = $result[0];
			}
		}
		return $result;
	}	
	
	# -------------------------------------------------------------------------------------------------------------------
	# RemoveSlashs
	function RemoveSlashs($s, $fromback = true, $c = "/") {
		$s = $fromback?strrev($s):$s;
		while(substr($s, 0, 1)=="/") {
			$s = substr($s, 1);
		}
		return $fromback?strrev($s):$s;
	}
	
	# -------------------------------------------------------------------------------------------------------------------
	# GetRequestString, returns the current request
	function GetRequestString($notrail = false, $noparameters = false, $onlyparameters = false, $querymarker = false) {
		$v = GetServerVar("REQUEST_URI");
		if($noparameters || $onlyparameters) {
			$v = explode("?", $v);
			$v = $v[0];
			// check onlyparameters
			if($onlyparameters) {
				return isset($v[1])?sprintf("%s%s", $querymarker?"?":"", $v[1]):"";
			}
		}
		if($notrail&&substr($v, -1)=="/") $v = RemoveSlashs($v);
		return $v;
	}
	
	# -------------------------------------------------------------------------------------------------------------------
	# ClearVar
	function ClearVar($name){
		unset($_COOKIE[$name]);
		unset($_GET[$name]);
		unset($_POST[$name]);		
		//unset($GLOBALS[$name]);	
	}	
	
	# -------------------------------------------------------------------------------------------------------------------
	# DefaultValue
	function DefaultValue($param0,$param1=null){
		if (empty($param0)||(is_string($param0)&&strlen(trim($param0))==0)||$param0==null) return $param1;
		return $param0;
	}
	
	# -------------------------------------------------------------------------------------------------------------------
	# DefaultValueJS 
	function DefaultValueJS($param0,$param1=null){
		if (empty($param0)||(is_string($param0)&&strlen(trim($param0))==0)||$param0==null||$param0=="null") return $param1;
		return $param0;
	}	
	
	# -------------------------------------------------------------------------------------------------------------------
	# CreateUserId
	function CreateUserId() {
		return CreateGUID();
	}
	
	# -------------------------------------------------------------------------------------------------------------------
	# CreateGUID
	function CreateGUID() {
		return md5(uniqid(rand(), true));
	}	

	# -------------------------------------------------------------------------------------------------------------------
	# CreateDigitId	
	function CreateDigitId($digits = 5) {
		$id = "";
		for ($i=0;$i<$digits;$i++) $id .= rand(0, 9);
		return $id;
	}
	
	# -------------------------------------------------------------------------------------------------------------------
	# CreateRandomPassword
	function CreateRandomPassword($length = 8) {
		// initialize
		$result = "";
		$possible = "2346789bcdfghjkmnpqrtvwxyzBCDFGHJKLMNPQRTVWXYZ";
		$maxlength = strlen($possible);
		// buffer overflow protection
		if ($length > $maxlength) {
			$length = $maxlength;
		}
		// cycle
		$i = 0; 
		while ($i < $length) { 
			// pick a random character from the possible ones
			$char = substr($possible, mt_rand(0, $maxlength-1), 1);
			// check
			if (!strstr($result, $char)) { 
				$result .= $char;
			}
			$i++;
		}
		// return
		return $result;
    }

	
	# -------------------------------------------------------------------------------------------------------------------
	# ValidateEMail
	function ValidateEMail($email) {
		return filter_var($email, FILTER_VALIDATE_EMAIL);
	}

	# -------------------------------------------------------------------------------------------------------------------
	# IfIsString	
	function IfIsString($a, $b) {
		if (!(strpos($a, $b) === false)) return true;
		return false;
	}	
	
	# -------------------------------------------------------------------------------------------------------------------
	# implodeValues
	function implodeValues($r, $div=";", $cslash=true) { 
		return array_implodevalues($r, $div, $cslash);
	}
	
	# -------------------------------------------------------------------------------------------------------------------
	# explodeValues
	function explodeValues($uv) { 
		$tmp = explode(";", $uv);
		$arr = Array();
		foreach($tmp as $tmpp) {
			$nv = explode("=", $tmpp);
			if (isset($nv[0]) && isset($nv[1])) {
				$v  = stripslashes($nv[1]);
				// remove magic quotes
				$v  = substr($v, 1, strlen($v)-2);
				// add to array
				$arr[$nv[0]] = $v;
			}
		}
		return $arr;
	}	
	
	# -------------------------------------------------------------------------------------------------------------------
	# GetRemoteAddress
	function GetRemoteAddress(){
		$rm=Array("HTTP_CLIENT_IP", "HTTP_X_FORWARDED", "HTTP_FORWARDED_FOR", "HTTP_X_FORWARDED_FOR", "HTTP_X_CLUSTER_CLIENT_IP");
		foreach($rm as $r){if(isset($_SERVER[$r])){return @$_SERVER[$r];}}
		return @$_SERVER["REMOTE_ADDR"];
	}
	
	# -------------------------------------------------------------------------------------------------------------------
	# ReverseDNSLookup
	function ReverseDNSLookup($ip) {
		// get hostname and ip
		$hostname = GetHostByAddr($ip);
		$hostip = GetHostByName($hostname);
		// return result
		return Array(
			"hostname"=>$hostname,
			"hostip"=>$hostip,
			"sourceip"=>$ip,
			"match"=>$ip==$hostip
		);
	}
	
	# -------------------------------------------------------------------------------------------------------------------
	# GetSession
	function GetSession(){
		return GetVar("session", false);
	}	
	
	# -------------------------------------------------------------------------------------------------------------------
	# Redirect, creates a 302 redirect
	function Redirect($url){header("location: $url"); exit;}
	
	# -------------------------------------------------------------------------------------------------------------------
	# LeftString, compares the left string
	function LeftString($haystack, $needle) {
		return strtolower(substr($haystack, 0, strlen($needle))) == strtolower($needle);
	}
	
	# -------------------------------------------------------------------------------------------------------------------
	# Array Helper

	// array escapestrings
	function array_escapestrings($r, $mysql=true) {while(list($name,$value)=each($r)){$r[$name]=$mysql?mysql_real_escape_string($value):addslashes($value);}return $r;}
	
	// array_multiple_implode
	function array_multiple_implode($r,$p0="=",$p1=","){$s="";$x=0;foreach($r as $ri){while(list($n,$v)=each($r)){$s[$x]=$n.$p0.'"'.$v.'"';$x+=1;}}return implode($p1,$s);}
		
	// array implode values
	function array_implodevalues($r=false,$p0=";",$p1="=",$s=false){if($r===false){return "";}$n=Array();while(list($n1,$n2)=each($r)){if($n2==VALUE_NULL){$n2="NULL";} else if($s===true){$n2=addslashes('"'.$n2.'"');}else{$n2='"'.$n2.'"';}$n[count($n)]=$n1.$p1.$n2;}return implode($p0,$n);}
	
	// array explode values
	function array_explodevalues($r=false,$p0=";",$p1="=",$s=true){if ($r===false){return Array();}$t=explode($p0,$r);$arr=Array();foreach($t as $ts){$n=explode($p1,$ts);if(isset($n[0])&&isset($n[1])){if($s===true){$n[1]=stripslashes($n[1]);}$n[1]=substr($n[1],1,strlen($n[1])-2);$arr[$n[0]]=$n[1];}};return $arr;}
	
	// array quote list
	function array_implodequotelist($r, $sep = ",", $quote = "\"") {
		$rs = Array();
		foreach($r as $n=>$v) {
			$rs[] = sprintf("%s%s%s", $quote, $v, $quote);
		}
		return implode($sep, $rs);
	}
	
	# -------------------------------------------------------------------------------------------------------------------
	# Debug
	function Debug($p){
		echo "<pre>".$p."\n</pre>";
	}
	
	# -------------------------------------------------------------------------------------------------------------------
	# (function) DebugWrite, writes to the debug file
	function DebugWrite($mixed) {
		file_put_contents(DEBUG_LOG, print_r($mixed, true)."\n", FILE_APPEND);
	}	
	# -------------------------------------------------------------------------------------------------------------------
	# DieDebug
	function DieDebug($s) {
		echo "<Pre>";
		var_dump($s);
		exit;
	}
	
	# -------------------------------------------------------------------------------------------------------------------
	# PrepareMessage
	function PrepareMessageDocument($title=false, $header=false, $text=false) {
		// create message
		return $document = sprintf("<html><head><title>%s</title>%s</head>%s</html>",
			$title?$title:"",
			implode("\n", Array(
				Tag("meta", Array("name"=>"robot", "value"=>"noindex"), false, true)
			)),
			Tag("body", Array("style"=>sprintf("width:100%%;height:100%%;overflow:hidden;background:url(%s) no-repeat center 50px #f1f1f1", "data:image/png;base64,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")), 
				sprintf("%s%s",
					Tag("div", Array("style"=>"text-shadow:#fff 1px 1px 0px;color:#333;font:bold 2em Arial;text-align:center;margin-top:270px;letter-spacing:-1px;"), $header),
					Tag("div", Array("style"=>"padding-top:30px;text-align:center;font:normal 1.2em Arial;color:#555;text-shadow:#fff 1px 1px 0px;"), $text)
				)
			)
		);
	}
	
	
	# -------------------------------------------------------------------------------------------------------------------
	# DieCriticalError
	function DieCriticalError($msg=false, $description = false, $code = false, $httpcode = "500 Internal Server Error") {
		// check console
		if(defined('FRAMEWORK_CONSOLE')) {
			die(sprintf("\nError (%s): %s\n%s\n", $code, $msg, $description));
		}
	
		// set header
		header(sprintf("HTTP/1.1 %s", $httpcode));
		// no cache headers
		header("Expires: ".date(DATE_RFC822, time() - (3600 * 24 * 365)));
		header("Last-Modified: ".date(DATE_RFC822, time() - 120));
		header("Pragma: no-cache");
		header("Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0");
		header("X-Robots-Tag: noindex, nofollow");
		// die with document
		Die(PrepareMessageDocument(sprintf("Error%s", $code?sprintf(" (%s)", $code):""), $msg, $description));
	}	
	
	# -------------------------------------------------------------------------------------------------------------------
	# Base64URLEncode / Decode
	function Base64URLEncode($input) {
		return strtr(base64_encode($input), '+/=', '-_,');
	}
	
	function Base64URLDecode($input) {
		return base64_decode(strtr($input, '-_,', '+/='));
	}	
	
	# -------------------------------------------------------------------------------------------------------------------
	# Array
	function is_assoc($array) {
		return (bool)count(array_filter(array_keys($array), 'is_string'));
	}	
	
	# explodeNth
	function explodeNth($delimiter, $string, $n) {
		$arr = explode($delimiter, $string);
		$arr2 = array_chunk($arr, $n);
		$out = array();
		for ($i = 0, $t = count($arr2); $i < $t; $i++) {
			$out[] = implode($delimiter, $arr2[$i]);
		}
		return $out;
	}
	
	# explodeVar 
	function explodeVar($string, $delimiter = "=", $break = "\n") {
		$result = Array();
		foreach(explode($break, $string) as $index=>$line) {
			$line = trim($line);
			if(strlen($line)!=0) {
				$line = explode($delimiter, $line, 2);
				$result[count($line)==2?$line[0]:$index] = count($line)==2?$line[1]:$line[0];
			}
		}
		return $result;
	}	
	
	# islowercase
	function islowercase($s) {
		return strtolower($s)===$s;
	}
		
