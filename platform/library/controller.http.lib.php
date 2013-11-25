<?php
	/*
		MobilesiteGuru project (mg)	Version 6.0
		
		Copyright (c) 1998 - 2009 eikonlexis LLC. All rights reserved.
		
		This program is protected by copyright laws and international treaties.
		Unauthorized reproduction or distribution of this program, or any 
		portion thereof, may result in serious civil and criminal penalties.
	
		Module 		Simple HTTP Fetcher
		Version		1.0
		
		Last Changes
		2009/02/19	Andi		Initial
	*/
	
	
	
	# -------------------------------------------------------------------------------------------------------------------
	# Class:			mgHTTPController
	#
	# Object:		HTTP Traffic
	# Description:	Simple HTTP Object
	#
	# Last Change:	March 06 2009 by Andi		
	

	class mgHTTPController {
	
		// Internal Variables
		private $__url;        // full URL
		private $__params;		// Parameters
		
		// Public Variables
		public $ErrorNumber=0;
		public $ErrorString="Unknown Error";
		
		// --------------------------------------------------------------------------
		// constructor __construct
		public function __construct($param0=false) {
			$this->__url = $param0;
			$this->__prepareurl();
			return true;
		}		
	

	   	// --------------------------------------------------------------------------
		// GetString, returns an string from the address
		function GetString() {
			if($this->__params===false){return false;}
			
		
			// Build HTTP Header
			$req = implode("\r\n", Array("GET ".$this->__params['resource']." HTTP/1.1", 
									     "Host: ".$this->__params['site'],
										 "User-Agent: MGServiceCrawler/2.3 (www.micrositeguru.com)",
										 "Connection: Close"))."\r\n\r\n";
			
			// Open Connection
			$fp = @fsockopen(($this->__params['protocol'] == 'https' ? 'ssl://' : '').$this->__params['site'], $this->__params['port'], $this->ErrorNumber, $this->ErrorString, 30); // 45 second time out
				
		
			// Test Connection
			if(!$fp){return false;}
			
			// process header and data
			fwrite($fp, $req); $response = "";
			while(!@feof($fp)){$response .= @fread($fp, 16384);}
			@fclose($fp);
		   
			// split header and body
			$pos = strpos($response, "\r\n\r\n");
			if($pos === false) {return($response);}
			$header = substr($response, 0, $pos);
			$body = substr($response, $pos + 2 * strlen("\r\n"));
		   
			// parse headers
			$headers = array();
			$lines = explode("\r\n", $header);
			foreach($lines as $line)
				if(($pos = strpos($line, ':')) !== false)
					$headers[strtolower(trim(substr($line, 0, $pos)))] = trim(substr($line, $pos+1));
			
			//redirection?, redirections are not supported
			/*if(isset($headers['location'])) {
				$this->__construct($header["location"]);
				return $this->GetString();
			}*/
			return $header.$body;			
		}
		
	
		
		// --------------------------------------------------------------------------
		// __prepareurl, preapres the url
		private function __prepareurl() {
			// prepare url, checks different type of entry's
			// http://test.com, http://www.test.com, http://multiple.subdomains.whatever.com
			
			$this->__params = mgParseUrl($this->__url);
			if(is_array($this->__params)){return true;}
			$this->ErrorString = "Invalid URL";
			return false;
		}
		
	   		
	}
	
	
	# -------------------------------------------------------------------------------------------------------------------
	# Function:			mgGetURLContents()
	#
	# Object:			Simple Wrapper Macro
	#
	# Last Change:	March 06 2009 by Andi			
	function mgGetURLContents($url) {
		$m = new mgHTTPController($url);
		return $m->GetString();
	}
