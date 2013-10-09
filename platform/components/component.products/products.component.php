<?php
	/* 
		(mg)framework Version 5.0
		
		Copyright (c) 1999-2011 eikonlexis LLC. All rights reserved.
		
		This program is protected by copyright laws and international treaties.
		Unauthorized reproduction or distribution of this program, or any 
		portion thereof, may result in serious civil and criminal penalties.
	
		Module 		Product Controller
	*/
	
	# -------------------------------------------------------------------------------------------------------------------
	# Constants Declaration	
	
	// (id's)
	define("PRODUCTS_ID_PATH", "product");
	
	// (properties) 
	define("PRODUCTS_CLASS", "mgProduct");
	define("PRODUCTS_PATH", "products/");
	define("PRODUCTS_XML", "product.xml");
	define("PRODUCTS_SORTFILE", "product.sort");
	define("PRODUCTS_BACKSCRIPT", "product.php");
	define("PRODUCTS_FRONTSCRIPT", "product.js");
	define("PRODUCTS_STYLESCRIPT", "product.css");
	define("PRODUCTS_SNIPPETPATH", "snippets/");
	

	// (output modes)
	define("PRODUCT_OUTPUT_HTML", "html");
	define("PRODUCTS_GRID_DEFAULT", "default");
	
	# -------------------------------------------------------------------------------------------------------------------
	# (class) mgProductManager, manages the products
	class mgProductManager extends mgModuleManager {
		# ---------------------------------------------------------------------------------------------------------------
		# (initialize) initialize the module manager class
		public function initialize(){
			// initialize module manager
			return Array(
				MODULE_CLASS=>PRODUCTS_CLASS,
				MODULE_PATH=>Array(PRODUCTS_PATH, SHARED_PATH.PRODUCTS_PATH),
				MODULE_XML=>PRODUCTS_XML,
				MODULE_RESOURCES=>Array(PRODUCTS_BACKSCRIPT, PRODUCTS_FRONTSCRIPT, PRODUCTS_STYLESCRIPT)
			);
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (getdisplay) returns the products as array
		public function getdisplays($type = false) {
			// initialize result
			$result = Array();
			// cycle modules
			$this->cycle(function($module) use ($type, &$result) {
				$result[] = $module->getdisplay($type);
			}); 
			// return result
			return $result;
		}
	}
	
	# -------------------------------------------------------------------------------------------------------------------
	# (class) mgProduct, manages a single product
	class mgProduct extends mgModule {
		
		# ---------------------------------------------------------------------------------------------------------------
		# (private) stack
		public $script = false;						
	
		# ---------------------------------------------------------------------------------------------------------------
		# (__initialize) called when the module is initialized
		public function __initialize() {
			// check if css should be included
			if($this->asBool("settings", "styles")) {
				$this->manager->framework->resources->ascss($this->asResource(PRODUCTS_STYLESCRIPT));
			}
			// check if resources needs to be included
			$resources = $this->asSetting("settings", "resources");
			if(strlen($resources)>0) {
				$this->manager->framework->resources->register($resources);
			}
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (loadmodule) loads the backendscript
		public function loadmodule() {
			// initialize
			$result = false;
			// create filename
			$fn = sprintf("%s/%s", dirname($this->location), PRODUCTS_BACKSCRIPT);
			// validate filename
			if(file_exists($fn)) {
				try {
					// load library
					require_once($fn);
					// create module name
					$name = sprintf("%s%s", PRODUCTS_CLASS, ucfirst($this->name));
					// try to create module
					$this->script = new $name($this);
					// set result
					$result = true;

				} catch(Exception $e) {
					$this->script = false;
				}
			}
			// return
			return $result;
		}		
		
		# ---------------------------------------------------------------------------------------------------------------
		# (process) processes a request
		public function process($request=false, $param0=false, $param1=false, $param2=false) {
			// initialize result
			$result = false;
			// retrieve
			try {
				// lazy load
				if(!$this->script) {
					$this->loadmodule();
				}
				// run script
				if($this->script) {
					// preprocess
					switch($request) {
						// readfile, reads file from directory
						case "readfile":
							$this->content = false;
							// get filename
							$filename = sprintf("%s/%s", dirname($this->location), GetVar("filename", false));
							// check filename
							if(file_exists($filename)) {
								// create new template
								$snippet = new mgTemplate($this->manager->framework->translate, false, $this->manager->framework);
								// assign 
								$snippet->AssignFromFile($filename);
								// set content
								$this->content = $snippet->GetParsed(true);
								// set result
								$result = MODULE_RESULT_RAW;
							}
							break;
							
						// parse default requests
						default: 
							// proccess
							$result = $this->script->process($request, $param0, $param1, $param2);
							// request content
							$this->content = $this->script->content;
							// secondary switch
							if(!$result) {
								switch($request) {
									// (getgrid), returns the grid information from the xml configuration
									case "getgrid":
										// get grid
										$this->content = mgManagerGridColumns($this->script->xml->grids, GetVar("name", PRODUCTS_GRID_DEFAULT), $this->script);
										// set result
										$result = MODULE_RESULT_JSON;
										break;
								}
							}
							break;
					}
				}
				// set content if any
			} catch(Exception $e) {
				return false;
			}
			// send result
			return $result;
		}	
		
	
		# ---------------------------------------------------------------------------------------------------------------
		# (getdisplay) returns the content for the display
		public function getdisplay($type = false) {
			// switch by type
			switch($type) {
				# -------------------------------------------------------------------------------------------------------
				# (default)
				default:
					// create data array
					return Array(
						"id"=>$this->id,
						"name"=>$this->name,
						"label"=>$this->asSetting("settings", "label"),
						"enabled"=>!$this->asBool("settings", "disabled"),
						"script"=>$this->asScript(PRODUCTS_FRONTSCRIPT),
						"metadata"=>$this->asMetaData("metadata")
					);				
					break;
			}
		}
	}
	
	# -------------------------------------------------------------------------------------------------------------------
	# (class) mgProductClass 
	class mgProductClass extends mgAdopterClass {
	
		// content
		public $content = null;
		public $values = null;
		public $rawvalues = null;
		public $baseurl = false;
		
		# ---------------------------------------------------------------------------------------------------------------
		# (__get) magic function
		public function __get($name) {
			// switch by name
			switch($name) {
				// (urlpath) return the path to the module
				case "urlpath": return sprintf(PRODUCTS_URLPATH, $this->id); break;
				case "gateway": return sprintf(PRODUCTS_GATEWAY, $this->urlpath); break;
				case "status": return $this->read("status", PRODUCTS_STATUS_DISCONNECTED); break;
			}
			// acquire parent
			return parent::__get($name);
		}
		
		
		# ---------------------------------------------------------------------------------------------------------------
		# (value) returns the value
		public function value($name, $default = false) {
			return isset($this->values[$name])?$this->values[$name]:$default;
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (snippet) returns the snippet
		public function snippet($name) {
			// create filename
			$fn = sprintf("%s/%s%s.snippet", dirname($this->location), PRODUCTS_SNIPPETPATH, $name);
			// check if exists
			return file_exists($fn)?file_get_contents($fn):false;
		}
		
	
		# ---------------------------------------------------------------------------------------------------------------
		# (request) requests a url
		public function request($url, $post = false) {
			// initialize curl
			$ch = curl_init();
			// set options
			curl_setopt_array($ch, Array(
				CURLOPT_URL 		   => $url,
				CURLOPT_RETURNTRANSFER => true, 
				CURLOPT_FOLLOWLOCATION => true, 
				CURLOPT_SSL_VERIFYHOST => 0,
				CURLOPT_SSL_VERIFYPEER => 0
			));
			// check post
			if(is_array($post)) {
				// set additional options
				curl_setopt_array($ch, Array(
					CURLOPT_POST		   => true,
					CURLOPT_POSTFIELDS 	   => $post
				));
			}
			// execute 
			$result = curl_exec($ch);
			// check failure
			if(!$result) {
				//curl_error($ch);
			}
			// close connection
			curl_close($ch);
			// return result
			return $result;
		}

	}
	
	# -------------------------------------------------------------------------------------------------------------------
	# (macro) mgCreateProductManager
	function mgCreateProductManager($framework = false) {
		$result = new mgProductManager(PRODUCTS_ID_PATH);
		if($framework) {
			$result->setframework($framework);
		}
		return $result;
	}
	
?>