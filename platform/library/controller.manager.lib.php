<?php
	/* 
		(mg)framework Version 5.0
		
		Copyright (c) 1999-2011 eikonlexis LLC. All rights reserved.
		
		This program is protected by copyright laws and international treaties.
		Unauthorized reproduction or distribution of this program, or any 
		portion thereof, may result in serious civil and criminal penalties.
	
		Module 		Manager Controller
	*/
	

	
	# -------------------------------------------------------------------------------------------------------------------
	# Constants Declaration	
	
	// (id's)
	define("MANAGER_ID_PATH", "manager");
	
	// (properties) 
	define("MANAGER_CLASS", "mgManagerModule");
	define("MANAGER_PATH", "manager/");
	define("MANAGER_XML", "manager.xml");
	define("MANAGER_SORTFILE", "manager.sort");
	define("MANAGER_BACKSCRIPT", "manager.php");
	define("MANAGER_FRONTSCRIPT", "manager.js");
	define("MANAGER_STYLESCRIPT", "manager.css");
	
	// (request modes)
	define("MANAGER_DISPLAY_INTERFACE", "interface");
	
	// (actions) 
	define("MANAGER_BAR_ACTION", "action");
	define("MANAGER_BAR_HEADER", "header");
	define("MANAGER_BAR_DIVIDER", "divider");
	define("MANAGER_BAR_VIEW", "view");
	
	
	# -------------------------------------------------------------------------------------------------------------------
	# (class) mgManager, manages the management modules
	class mgManager extends mgModuleManager {
	
		# ---------------------------------------------------------------------------------------------------------------
		# (private)
		private $role = false;
	
		# ---------------------------------------------------------------------------------------------------------------
		# (initialize) initialize the module manager class
		public function initialize(){
			// initialize module manager
			return Array(
				MODULE_CLASS=>MANAGER_CLASS,
				MODULE_PATH=>Array(MANAGER_PATH, SHARED_PATH.MANAGER_PATH),
				MODULE_XML=>MANAGER_XML,
				MODULE_RESOURCES=>Array(MANAGER_BACKSCRIPT, MANAGER_FRONTSCRIPT, MANAGER_STYLESCRIPT)
			);
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (getdisplays) returns all the display settings
		public function getdisplays($type = false) {
			// initialize result
			$result = Array();
			// set role
			$role = $this->role;
			// cycle modules
			$this->cycle(function($module) use ($type, &$result, $role) {
				// get module
				$m = $module->getdisplay($type, $role);
				// check result
				if($m) {
					// add to list
					$result[] = $m;
				}
			}); 
			// return result
			return $result;
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (setuserrole) sets the user role
		public function setuserrole($role = false) {
			$this->role = $role;
		}
		
	}
	
	# -------------------------------------------------------------------------------------------------------------------
	# (class) mgManagerModule, manages a single module
	class mgManagerModule extends mgModule {
	
		# ---------------------------------------------------------------------------------------------------------------
		# (private) stack
		private $jsstack = array();
		
		# ---------------------------------------------------------------------------------------------------------------
		# (public) stack
		public $script = false;					
		

		
		# ---------------------------------------------------------------------------------------------------------------
		# (__initialize) called when the module is initialized
		public function __initialize() {
			// check if css should be included
			if($this->asBool("settings", "styles")) {
				$this->manager->framework->resources->ascss($this->asResource(MANAGER_STYLESCRIPT));
			}
			// check if resources needs to be included
			$resources = $this->asSetting("settings", "resources");
			if(strlen($resources)>0) {
				$this->manager->framework->resources->register($resources);
			}
			// compile includes
			if(isset($this->xml->includes->lib)) {
				// cycle
				foreach($this->xml->includes->lib as $lib) {
					// get attributes
					$lib = $lib->attributes();
					// create assets
					$filename = sprintf("%s/%s", dirname($this->location), DefaultValue((string)@$lib->src, false));
					// check
					if(file_exists($filename)) {
						// assign
						switch(DefaultValue((string)@$lib->type, false)) {
							// javascript include
							case "js": $this->jsstack[] = $filename; break;
							// css include
							case "css": $this->manager->framework->resources->ascss(file_get_contents($filename)); break;
						}
					}
				}
			}
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (loadmodule) loads the backendscript
		public function loadmodule() {
			// initialize
			$result = false;
			// create filename
			$fn = sprintf("%s/%s", dirname($this->location), MANAGER_BACKSCRIPT);
			// validate filename
			if(file_exists($fn)) {
				try {
					// load library
					require_once($fn);
					// create module name
					$name = sprintf("%s%s", MANAGER_CLASS, ucfirst($this->name));
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
										$this->content = mgManagerGridColumns($this->script->xml->grids, GetVar("name", MANAGER_GRID_DEFAULT), $this->script);
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
		public function getdisplay($type = false, $requiredrole = false) {
			// validate requiredrole
			if($requiredrole !== false) {
				// get module requirement
				if(!$this->matchrole($requiredrole)) return false;
			}
			// switch by type
			switch($type) {
				# -------------------------------------------------------------------------------------------------------
				# (interface)
				case MANAGER_DISPLAY_INTERFACE:
					// create data array
					return Array(
						"moduleid"=>$this->id,
						"modulevisible"=>$this->asBool("settings", "visible"),
						"modulename"=>$this->name,
						"modulescript"=>$this->asScript(array_merge(array(MANAGER_FRONTSCRIPT), $this->jsstack)),
						"moduleactions"=>$this->getactions(false, $requiredrole),
						"moduleviews"=>$this->getviews(),
					);				
					break;
			}
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (getviews) returns all views
		public function getviews($asxml = false) {
			// get node
			$views = @$this->xml->views;
			// check xml node
			if($asxml) return $views;
			// initialize result
			$result = Array();
			// check node
			if(!$views) return $result;
			// cycle views
			foreach($views->children() as $node) {
				// assign view
				$result[] = Array(
					"type"=>MANAGER_BAR_VIEW, 
					"name"=>(string)@$node["name"],
					"label"=>$this->asLocalizedString((string)$node["label"]), 
					"configurable"=>(string)$node["configurable"]=="true", 
					"configurelabel"=>$this->asLocalizedString((string)$node["configurelabel"])
				);
			}
			// return views
			return $result;			
		}

		# ---------------------------------------------------------------------------------------------------------------
		# (getactions) returns a array of all actions
		public function getactions($asxml = false, $requiredrole = false) {
			// initialize result
			$result = Array();
			// get node
			$actions = $this->xml->actions;
			// sanity check
			if(!$actions) return $result;
			// check xml node
			if($asxml) return $actions;
			// get id
			$id = $this->id;
			// loop through actions
			foreach($actions->children() as $node) {
				// add divider
				if(isset($node["divider"])&&(string)$node["divider"]=="true") {
					$result[] = Array("type"=>MANAGER_BAR_DIVIDER);
				}		
				// check role
				$allowed = true;
				if(isset($node["role"])) {
					$allowed = $this->matchrole($requiredrole, $node["role"]);
				}
				// check allowed
				if($allowed) {
					// switch by name
					switch($node->getName()) {
						// single action item
						case "action":
							// assign
							$result[] = Array("type"=>MANAGER_BAR_ACTION, "id"=>DefaultValue((string)@$node["id"], false), "index"=>DefaultValue((integer)@$node["index"], false), "icon"=>DefaultValue((string)@$node["icon"], ""), "targetview"=>(string)@$node["targetview"], "label"=>$this->asLocalizedString((string)$node["label"]), "level"=>false, "action"=>Array($id, (string)$node["action"]));
							break;
							
						// action group
						case "actiongroup":
							// set header
							$result[] = Array("type"=>MANAGER_BAR_HEADER, "index"=>DefaultValue((integer)@$node["index"], false), "view"=>(string)@$node["view"]=="true", "label"=>$this->asLocalizedString((string)$node["label"]));
							// cycle actions
							foreach($node->children() as $child) {
								// verify
								if($child->getName()=="action") {
									$result[] = Array("type"=>MANAGER_BAR_ACTION, "icon"=>DefaultValue((string)@$child["icon"], ""), "label"=>$this->asLocalizedString((string)$child["label"]), "level"=>true, "action"=>Array($id, (string)$child["action"]));
								}
							}
							break;
					}
				}
			}
			// return result
			return $result;		
		}
		

		
	}
	
	# -------------------------------------------------------------------------------------------------------------------
	# (class) mgManagerClass 
	class mgManagerExtension extends mgAdopterClass {
	
		// content
		public $content = null;
		
		# ---------------------------------------------------------------------------------------------------------------
		# (__get) magic function
		public function __get($name) {
			// switch by name
			switch($name) {
				// (urlpath) return the path to the module
				case "urlpath": return sprintf(MANAGER_URLPATH, $this->id); break;
				case "gateway": return sprintf(MANAGER_GATEWAY, $this->urlpath); break;
				case "status": return $this->read("status", MANAGER_STATUS_DISCONNECTED); break;
			}
			// acquire parent
			return parent::__get($name);
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (readfile) reads a local file
		public function readfile($filename) {
			// enter filename
			$filename = $this->GetLocalFilename($filename);
			// check filename
			return file_exists($filename)?file_get_contents($filename):false;
		}
		
		public function GetLocalFilename($filename) {
			return sprintf("%s/%s", dirname($this->location), $filename);
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
		
		# ---------------------------------------------------------------------------------------------------------------
		# (retrieve) advanced version
		public function retrieve($url, $fields = Array(), $options = false) {
			// set default options
			$options = (object)array_merge(Array("raw"=>false, "operation"=>"post", "referer"=>false, "follow"=>true, "cookiefile"=>false, "useragent"=>false), is_array($options)?$options:Array());
			// initialize curl
			$ch = curl_init();
			// set options
			curl_setopt_array($ch, Array(
				//CURLINFO_HEADER_OUT=> true,
				CURLOPT_URL => trim($url),
				CURLOPT_COOKIEJAR=> $options->cookiefile?$options->cookiefile:false,
				CURLOPT_COOKIEFILE=> $options->cookiefile?$options->cookiefile:false,
				CURLOPT_RETURNTRANSFER => true, 
				CURLOPT_FOLLOWLOCATION => true, 
				CURLOPT_SSL_VERIFYHOST => 0,
				CURLOPT_SSL_VERIFYPEER => 0,
				CURLOPT_PROXY=>$this->currentproxy,
				CURLOPT_USERAGENT => $options->useragent?$options->useragent:false,
				CURLOPT_HEADER => 1,
				CURLOPT_CONNECTTIMEOUT => 15,
				CURLOPT_TIMEOUT=>15
			));
			if($options->raw) {
				curl_setopt($ch, CURLOPT_HTTPHEADER, Array("Content-Type: text/plain"));
			}
			if($options->operation == "post") {
				if(!$options->raw) {
					if(!is_array($fields)||count($fields)!=0) {
						$fields = Array();
					}
					$fields = http_build_query($fields, '', '&');
				}
				curl_setopt_array($ch, Array(
					CURLOPT_POST => true,
					CURLOPT_POSTFIELDS =>$fields
				));
			}
			// referer
			if($options->referer) {
				curl_setopt($ch, CURLOPT_REFERER, $options->referer);
			}
			// execute 
			$result = curl_exec($ch);
			// process result
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
			// return
			return $result;
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (private) __parseheaders
		private function __parseheaders( $header ) {
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