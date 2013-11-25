<?php
	/* 
		(mg)framework Version 5.0
		
		Copyright (c) 1999-2011 eikonlexis LLC. All rights reserved.
		
		This program is protected by copyright laws and international treaties.
		Unauthorized reproduction or distribution of this program, or any 
		portion thereof, may result in serious civil and criminal penalties.
	
		Module 		SEO Controller
	*/

	
	# -------------------------------------------------------------------------------------------------------------------
	# Constants Declaration	
	
	# (default)
	define("SEO_TIMEOUT_STANDARD", 60 * 60 * 24);	// Standard is 24 hours
	define("SEO_TIMEOUT_FORCE", -1);				// Force
	
	
	# (Sitemap)
	define("SEO_SITEMAP_DEFAULTPRIORITY", "0.5");
	define("SEO_SITEMAP_FREQALWAYS", "always");
	define("SEO_SITEMAP_FREQHOURLY", "hourly");
	define("SEO_SITEMAP_FREQDAILY", "daily");
	define("SEO_SITEMAP_FREQWEEKLY", "weekly");
	define("SEO_SITEMAP_FREQMONTHLY", "monthly");
	define("SEO_SITEMAP_FREQNEVER", "never");
	define("SEO_SITEMAP_FILENAME", "sitemap.xml");
	define("SEO_SITEMAP_FILENAMES", "sitemap%s.xml");
	define("SEO_SITEMAP_COMPACT", ".gz");
	define("SEO_SITEMAP_URLLIMIT", 50000);
	
	define("SEO_SITEMAP_HEADER", "<?xml version=\"1.0\" encoding=\"utf-8\"\n<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n%s\n</urlset>");
	
	# ---------------------------------------------------------------------------------------------------------------
	# (class) mgSEOSitemap, handles a SEO Sitemap
	class mgSEOSitemap {
		# ---------------------------------------------------------------------------------------------------------------
		# (private)
		private $path = false;
		private $filename = false;
		private $basepath = false;
		private $items = Array();
		public $compress = true;
	
		# ---------------------------------------------------------------------------------------------------------------
		# (constructor)
		public function __construct($path = PUBLIC_PATH, $filename = SEO_SITEMAP_FILENAME, $basepath = BASEPATH) {
			$this->path = $path;
			$this->filename = $path.$filename;
			$this->basepath = $basepath;
		}
		
		
		# ---------------------------------------------------------------------------------------------------------------
		# (RequireChange) checks if the sitemap is outdated and updates it
		public function RequireUpdate($items, $timeout = SEO_TIMEOUT_STANDARD) {
			// check file
			$doupdate = mgIsFileExpired($this->filename, $timeout);
			// check update
			if($doupdate) {
				// check if items is a function
				if(is_callable($items)) $items = $items($this);
				
				// check items
				if(is_array($items)) {
					// update and create
					$this->Update($items);
				}
			}
			// return items
			return $items;
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (Update) Update, writes the sitemap
		public function Update($items = false, $ascontent = false) {
			// get items
			if(!$items) $items = $this->items;
			// get filename
			$filename = $this->filename;
			// build items
			$um = $this->__buildxml($items);
			if($this->compress===true) $um = gzencode($um);
			// save compressed
			file_put_contents(sprintf("%s%s", $filename, SEO_SITEMAP_COMPACT), $um);
			// write index file
			file_put_contents($filename, sprintf("<?xml version=\"1.0\" encoding=\"utf-8\"\n<sitemapindex xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n%s</sitemapindex>", 
				sprintf("<sitemap>\n<loc>%s</loc>\n<lastmod>%s</lastmod>\n</sitemap>\n",
					sprintf("http://%s/%s%s", $this->basepath?$this->basepath:"", SEO_SITEMAP_FILENAME, SEO_SITEMAP_COMPACT),
					date("Y-m-d")
				)
			));
			// return status
			return true;
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (Emit) UpdateFromFiles - build from multiple files
		public function UpdateFromFile($filenames, $hasheader = true) {
			// check filenames
			if(!is_array($filenames)) $filenames = Array($filenames);
			// initialize
			$f = 0;
			// sitemap files
			$sxml = "";
			// cycle
			foreach($filenames as $fn) {
				// check fn
				if(file_exists($fn)) {
					// read contents
					$content = file_get_contents($fn);
					// check header
					if(!$hasheader) $content = sprintf(SEO_SITEMAP_HEADER, $content);	
					// compress
					if($this->compress===true) $content = gzencode($content);
					// create sitemap name
					$sname = sprintf("%s%s", sprintf(SEO_SITEMAP_FILENAMES, $f>0?$f:""), $this->compress?SEO_SITEMAP_COMPACT:"");
					// create filename
					$sfn = sprintf("%s/%s", dirname($this->filename), $sname);
					// create xml node
					$sxml .= sprintf("<sitemap>\n<loc>%s</loc>\n<lastmod>%s</lastmod>\n</sitemap>\n",
						sprintf("http://%s/%s", $this->basepath?$this->basepath:"", $sname),
						date("Y-m-d")
					);
					// put file
					file_put_contents($sfn, $content);
					// up
					$f++;					
				}
			}
			// save index file
			file_put_contents($this->filename, sprintf("<?xml version=\"1.0\" encoding=\"utf-8\"\n<sitemapindex xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n%s</sitemapindex>", 
				$sxml
			));
			// return status
			return true;
		}

		# ---------------------------------------------------------------------------------------------------------------
		# (Emit) emit
		public function Emit() {
			if(file_exists($this->filename)) {
				mgOutputBuffer(file_get_contents($this->filename), MIME_XML);
			}
		}			
		
		# ---------------------------------------------------------------------------------------------------------------
		# (Public) CreateItemsFromURLs
		public function CreateItemsFromURLs($items, $modified = true, $changefreq = SEO_SITEMAP_FREQDAILY, $priority = SEO_SITEMAP_DEFAULTPRIORITY, $basepath = false) {
			// initialuze
			$result = Array();
			// prepare
			$modified = $modified===true?date("Y-m-d"):(is_string($modified)?$modified:false);
			// cycle
			foreach($items as $key=>$url) {
				$result[] = Array(
					"loc"=>sprintf("%s%s", $basepath?$basepath:($this->basepath?$this->basepath:""), mgXMLEntities($url)),
					"lastmod"=>$modified,
					"changefreq"=>$changefreq,
					"priority"=>$priority
				);
			}
			// return result
			return $result;
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (private) __buildxml	
		private function __buildxml($items) {
			// create list
			$list = Array();
			foreach($items as $item) $list[] = $this->__buildxmlitem($item);
			// check
			return sprintf(SEO_SITEMAP_HEADER, implode("\n", $list));
		}

	
		# ---------------------------------------------------------------------------------------------------------------
		# (private) __buildxmlitem	
		public function BuildXMLItem($item) {
			return $this->__buildxmlitem($item);
		}
		
		private function __buildxmlitem($item) {
			// initialize
			$result = "";
			// prepare
			if(is_array($item)&&isset($item["loc"])) {
				// cycle
				foreach(Array("loc", "lastmod", "changefreq", "priority") as $n=>$v) {
					if(isset($item[$v])) {
						$result .= Tag($v, false, $item[$v]);
					}
				}
			}
			// return result
			return strlen($result)!=0?sprintf("<url>%s</url>", $result):false;
		
		}
	}
	
