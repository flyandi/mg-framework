<?php
	/* 
		(mg)framework Version 5.0
		
		Copyright (c) 1999-2011 eikonlexis LLC. All rights reserved.
		
		This program is protected by copyright laws and international treaties.
		Unauthorized reproduction or distribution of this program, or any 
		portion thereof, may result in serious civil and criminal penalties.
	
		Module 		Security Module
	*/

	# (function) mgEncryptString, encrypts a string
	function mgEncryptString($string, $key=SECURITY_KEY){
		//$key = sha1($key);
		$strLen = strlen($string);
		$keyLen = strlen($key);
		$j = 0; $hash="";
		for ($i = 0; $i < $strLen; $i++) {
			$ordStr = ord(substr($string,$i,1));
			if ($j == $keyLen) { $j = 0; }
			$ordKey = ord(substr($key,$j,1));
			$j++;
			$hash .= strrev(base_convert(dechex($ordStr + $ordKey),16,36));
		}
		return $hash;
	}

	# (function) mgDecryptString, decrypts a string
	function mgDecryptString($string,$key=SECURITY_KEY) {
		//$key = sha1($key);
		$strLen = strlen($string);
		$keyLen = strlen($key);
		$j = 0; $hash="";
		for ($i = 0; $i < $strLen; $i+=2) {
			$ordStr = hexdec(base_convert(strrev(substr($string,$i,2)),36,16));
			
			if ($j == $keyLen) { $j = 0; }
			$ordKey = ord(substr($key,$j,1));
			$j++;
			$hash .= chr($ordStr - $ordKey);
		}
		return $hash;
	}
?>
