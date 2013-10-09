<?php
	/* 
		(mg)framework Version 5.0
		
		Copyright (c) 1999-2013 eikonlexis LLC. All rights reserved.
		
		This program is protected by copyright laws and international treaties.
		Unauthorized reproduction or distribution of this program, or any 
		portion thereof, may result in serious civil and criminal penalties.
	
		Module 		YouTube Controller
	*/
	
	# -------------------------------------------------------------------------------------------------------------------
	# Constants Declaration
	define("YOUTUBE_SOURCE", false);
	define("YOUTUBE_LOGINURL", "https://www.google.com/youtube/accounts/ClientLogin");
	define("YOUTUBE_UPLOADURL", "uploads.gdata.youtube.com");
	
	
	# -------------------------------------------------------------------------------------------------------------------
	# Class
	class mgYouTube {
	
		# ---------------------------------------------------------------------------------------------------------------
		# (private)
		private $apptoken = false;
		private $username = false;
		private $password = false;
		private $source = false;
		private $usertoken = Array();
		private $error = false;
	
		# ---------------------------------------------------------------------------------------------------------------
		# (public) process
		public function __construct($token, $username = false, $password = false, $source = false) {
			$this->apptoken = $token;
			$this->username = $username;
			$this->password = $password;
			$this->source = $source;
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (public) login
		public function login($username = false, $password = false) {
			// initialize
			$result = false;
			// check
			if(!$username) $username = $this->username;
			if(!$password) $password = $this->password;
			// request
			$result = $this->__request(YOUTUBE_LOGINURL, Array(
				"Email"=>rawurlencode($username),
				"Passwd"=>rawurlencode($password),
				"service"=>"youtube",
				"source"=>$this->source
			));
			// check result
			if($result->header->status->code == 200) {
				// explode body
				$this->usertoken = Array();
				foreach(explode("\n", $result->body) as $line) {
					$p = explode("=", $line, 2);
					if(count($p)==2) {
						$this->usertoken[ strtolower($p[0])] = $p[1];
					}
				}
				$this->usertoken = (count($this->usertoken)!=0)?(object)$this->usertoken:false;
				// return
				return true;				
			}
			// return failed
			return false;
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (public) Upload
		function Upload($filename, $title = false, $description = false, $keywords = false, $category = false) {
			// check
			if(!$this->usertoken) return false;
			// initialize
			$ersult = false;
			$this->error = "";
			// create XML
				$xml = sprintf('<?xml version="1.0"?>
<entry xmlns="http://www.w3.org/2005/Atom"
  xmlns:media="http://search.yahoo.com/mrss/"
  xmlns:yt="http://gdata.youtube.com/schemas/2007">
  <media:group>
	<media:title type="plain">%s</media:title>
	<media:description type="plain">%s</media:description>
	<media:category scheme="http://gdata.youtube.com/schemas/2007/categories.cat">%s</media:category>
	<media:keywords>%s</media:keywords>
  </media:group>
</entry>', $title, $description?$description:"", $category?$category:"Entertainment", $keywords?$keywords:"");
			// create body
			$bs = sprintf("lcu%s", md5(time()));
			$bsr = "\r\n";
			$bsa = sprintf("--%s", $bs);
			// create post data
			$postdata = "";
			// add xml
			$postdata .= $bsa.$bsr."Content-Type: application/atom+xml; charset=UTF-8".$bsr.$bsr.$xml.$bsr;
			// add binary
			$postdata .= $bsa.$bsr."Content-Type: application/octet-stream".$bsr;
			$postdata .= "Content-Transfer-Encoding: binary".$bsr.$bsr;
			$postdata .= file_get_contents($filename).$bsr.$bsa."--".$bsr;
			//$postdata .= "<<FILEDATA>>".$bsr.$bsa."--".$bsr;
			// create header
			$header  = "POST /feeds/api/users/default/uploads HTTP/1.1".$bsr;
			$header .= "Host: uploads.gdata.youtube.com".$bsr;
			$header .= sprintf("Authorization: GoogleLogin auth=\"%s\"", $this->usertoken->auth).$bsr;
			$header .= sprintf("X-GData-Client: %s", $this->usertoken->youtubeuser).$bsr;
			$header .= sprintf("X-GData-Key: key=%s", $this->apptoken).$bsr;
			$header .= sprintf("Slug: %s", basename($filename)).$bsr;
			$header .= sprintf("Content-Type: multipart/related; boundary=\"%s\"", $bs).$bsr;
			$header .= sprintf("Content-Length: %s", strlen($postdata)).$bsr;
			$header .= "Connection: close".$bsr.$bsr;
			
			//echo $header;
			//echo $postdata;
			//exit;
			
			// create socket connection
			$fh = fsockopen(YOUTUBE_UPLOADURL, 80);
			// check fh
			if($fh) {
				fwrite($fh, $header); 
				fwrite($fh, $postdata);
				
				// read reply
				$header = "";
				$data = "";
				while(!feof($fh)) {
					$line = fgets($fh);
					if ($line == "\r\n") break;
					$header .= $line;
				}

				// find header
				$matches = array();
				preg_match('/Content-Length: (\d+)/is', $header, $matches);
				
				if ($matches[1]) {
					$sendheader = false;
					$cl = intval($matches[1]);
					$data = '';
					if ($cl) {
						$data = fread($fh, $cl);
						fclose($fh);
					}
				}
				// parse result
				$header = $this->__parseheaders($header);
				if($header->status->code == 200 || $header->status->code == 201) {
					// successfully uploaded, parse xml
					$r = new mgXML($data);
					// check if
					if(isset($r->id)) {
						// get data
						$vid = str_replace("http://gdata.youtube.com/feeds/api/videos/", "", $r->id);
						// return data
						return (object)Array(
							"video"=>$vid,
							"url"=>sprintf("http://www.youtube.com/watch?v=%s", $vid),
							"xml"=>$r
						);
					}
				}				
			}
			// return error
			return false;
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (private) request
		private function __request($url, $post, $headers = false) {
			// initialize curl
			$ch = curl_init();
			// set options
			curl_setopt_array($ch, Array(
				CURLOPT_URL 		   => $url,
				CURLOPT_RETURNTRANSFER => true, 
				CURLOPT_FOLLOWLOCATION => true, 
				CURLOPT_SSL_VERIFYHOST => 0,
				CURLOPT_SSL_VERIFYPEER => 0,
				CURLOPT_HEADER => 1,
				CURLOPT_POST => true,
				CURLOPT_POSTFIELDS => http_build_query($post),
			));
			// headers
			if($headers) {
				curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    		}
			// execute 
			$result = curl_exec($ch);
			// check failure
			if($result) {
				$parts = explode("\r\n\r\n", $result, 2);
				$result = false;
				if(count($parts)==2) {
					// parse header
					$result = (object)Array(
						"header"=>$this->__parseheaders($parts[0]),
						"body"=>trim($parts[1])
					);
				}
			}
			// close connection
			curl_close($ch);
			// return result
			return $result;
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (private) __parseheaders
		function __parseheaders( $header ) {
			$result = array();
			$fields = explode("\r\n", preg_replace('/\x0D\x0A[\x09\x20]+/', ' ', $header));
			foreach( $fields as $field ) {
				$field = trim($field);
				if(strtolower(substr($field, 0, 4))=="http") {
					$status = explode(" ", $field, 3);
					$result["status"] = (object)Array(
						"version"=>isset($status[0])?$status[0]:false,
						"code"=>isset($status[1])?$status[1]:false,
						"reason"=>isset($status[2])?$status[2]:false
					);
				} else if( preg_match('/([^:]+): (.+)/m', $field, $match) ) {
					$match[1] = preg_replace('/(?<=^|[\x09\x20\x2D])./e', 'strtoupper("\0")', strtolower(trim($match[1])));
					if( isset($result[$match[1]]) ) {
						if (!is_array($result[$match[1]])) {
							$result[$match[1]] = array($result[$match[1]]);
						}
						$result[$match[1]][] = $match[2];
					} else {
						$result[$match[1]] = trim($match[2]);
					}
				}
			}
			return (object)$result;
		}		
	}
?>