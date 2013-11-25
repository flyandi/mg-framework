<?php
	/* 
		(mg)framework Version 5.0
		
		Copyright (c) 1999-2011 eikonlexis LLC. All rights reserved.
		
		This program is protected by copyright laws and international treaties.
		Unauthorized reproduction or distribution of this program, or any 
		portion thereof, may result in serious civil and criminal penalties.
	
		Module 		Image IPTC Data Controller
	*/
	
	# -------------------------------------------------------------------------------------------------------------------
	# Constants

    define('IPTC_OBJECT_NAME', '005');
    define('IPTC_EDIT_STATUS', '007');
    define('IPTC_PRIORITY', '010');
    define('IPTC_CATEGORY', '015');
    define('IPTC_SUPPLEMENTAL_CATEGORY', '020');
    define('IPTC_FIXTURE_IDENTIFIER', '022');
    define('IPTC_KEYWORDS', '025');
    define('IPTC_RELEASE_DATE', '030');
    define('IPTC_RELEASE_TIME', '035');
    define('IPTC_SPECIAL_INSTRUCTIONS', '040');
    define('IPTC_REFERENCE_SERVICE', '045');
    define('IPTC_REFERENCE_DATE', '047');
    define('IPTC_REFERENCE_NUMBER', '050');
    define('IPTC_CREATED_DATE', '055');
    define('IPTC_CREATED_TIME', '060');
    define('IPTC_ORIGINATING_PROGRAM', '065');
    define('IPTC_PROGRAM_VERSION', '070');
    define('IPTC_OBJECT_CYCLE', '075');
    define('IPTC_BYLINE', '080');
    define('IPTC_BYLINE_TITLE', '085');
    define('IPTC_CITY', '090');
    define('IPTC_PROVINCE_STATE', '095');
    define('IPTC_COUNTRY_CODE', '100');
    define('IPTC_COUNTRY', '101');
    define('IPTC_ORIGINAL_TRANSMISSION_REFERENCE',     '103');
    define('IPTC_HEADLINE', '105');
    define('IPTC_CREDIT', '110');
    define('IPTC_SOURCE', '115');
    define('IPTC_COPYRIGHT_STRING', '116');
    define('IPTC_CAPTION', '120');
    define('IPTC_LOCAL_CAPTION', '121');

	
	# -------------------------------------------------------------------------------------------------------------------
	# (class) mgImageIPTC
    class mgImageIPTC {
	
		# ---------------------------------------------------------------------------------------------------------------
		# (private) stack
        private $meta=Array();
        private $hasmeta=false;
        private $file=false;
		private $image=false;
       
		# ---------------------------------------------------------------------------------------------------------------
		# (constructor) 
        public function __construct($filename=false, $image=false) {
			// check filename
			if($filename&&file_exists($filename)) {
				// load filename
				$size = getimagesize($filename,$info);
				// get meta
				$this->hasmeta = isset($info["APP13"]);
				// check meta
				if($this->hasmeta) {
					$this->meta = iptcparse ($info["APP13"]);
				}
				$this->file = $filename;
			} 
        }
		
		# ---------------------------------------------------------------------------------------------------------------
		# (set)
        public function set($tag, $data) {
            $this->meta["2#$tag"]= Array( $data );
            $this->hasmeta=true;
        }
		
		# ---------------------------------------------------------------------------------------------------------------
		# (get)
        public function get($tag) {
            return isset($this->meta["2#$tag"]) ? $this->meta["2#$tag"][0] : false;
        }
		// magic
		public function __get($tag) {return $this->get($tag);}
	
		# ---------------------------------------------------------------------------------------------------------------
		# (dump)
        function dump($writescreen=false) {
			if($writescreen) {
				print_r($this->meta);
			}
			return $this->meta;
        }
		
        function binary() {
            $iptc_new = '';
            foreach (array_keys($this->meta) as $s) {
                $tag = str_replace("2#", "", $s);
                $iptc_new .= $this->iptc_maketag(2, $tag, $this->meta[$s][0]);
            }       
            return $iptc_new;   
        }
        function iptc_maketag($rec,$dat,$val) {
            $len = strlen($val);
            if ($len < 0x8000) {
                   return chr(0x1c).chr($rec).chr($dat).
                   chr($len >> 8).
                   chr($len & 0xff).
                   $val;
            } else {
                   return chr(0x1c).chr($rec).chr($dat).
                   chr(0x80).chr(0x04).
                   chr(($len >> 24) & 0xff).
                   chr(($len >> 16) & 0xff).
                   chr(($len >> 8 ) & 0xff).
                   chr(($len ) & 0xff).
                   $val;
                  
            }
        }   
        function write() {
            if(!function_exists('iptcembed')) return false;
            $mode = 0;
            $content = iptcembed($this->binary(), $this->file, $mode);   
            $filename = $this->file;
               
            @unlink($filename); #delete if exists
           
            $fp = fopen($filename, "w");
            fwrite($fp, $content);
            fclose($fp);
        }   
       
        #requires GD library installed
        function removeAllTags() {
            $this->hasmeta=false;
            $this->meta=Array();
            $img = imagecreatefromstring(implode(file($this->file)));
            @unlink($this->file); #delete if exists
            imagejpeg($img,$this->file,100);
        }
    }
