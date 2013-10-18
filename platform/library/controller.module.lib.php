<?php
	/* 
		(mg)framework Version 5.0
		
		Copyright (c) 1999-2011 eikonlexis LLC. All rights reserved.
		
		This program is protected by copyright laws and international treaties.
		Unauthorized reproduction or distribution of this program, or any 
		portion thereof, may result in serious civil and criminal penalties.
	
		Module 		Module Controller
					Provides basic i/o for creating loadable modules
	*/
	
	
	# -------------------------------------------------------------------------------------------------------------------
	# Constants Declaration	
	define("MODULE_LAZYLOAD", "lazyload");
	define("MODULE_ID_PATH", "idpath");
	define("MODULE_PATH", "path");
	define("MODULE_XML", "xml");
	define("MODULE_RESOURCES", "resources");
	define("MODULE_CLASS", "class");
	
	// (related) related
	define("MODULE_TRANSLATE", "translate");
	
	// (result) results
	define("MODULE_RESULT_PASSTHROUGH", true);
	define("MODULE_RESULT_EXIT", -1);
	define("MODULE_RESULT_JSON", -2);
	define("MODULE_RESULT_ROOT", -3);
	define("MODULE_RESULT_RAW", -4);

	# -------------------------------------------------------------------------------------------------------------------
	# (class) mgModuleManager, manages modules
	class mgModuleManager {
	
		# ---------------------------------------------------------------------------------------------------------------
		# (private) local stack
		private $modules;			// storage for modules
		private $settings;			// storage for manager settings
		
		// messages
		private $messages = Array(
			"header"=>"ModuleErrorNotExistsCaption",
			"message"=>"ModuleErrorNotExistsMessage"
		);		
		
		# ---------------------------------------------------------------------------------------------------------------
		# (public) local stack
		public $framework = false;
		public $user = false;		// user reference
		
		# ---------------------------------------------------------------------------------------------------------------
		# (constructor)
		public function __construct($idpath = false, $uselazyload = true){
			// read settings
			$this->settings = array_merge(Array(MODULE_LAZYLOAD=>$uselazyload, MODULE_ID_PATH=>$idpath), $this->initialize());
			// refresh modules
			$this->refresh();
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (public) Refresh
		public function refresh() {
			// get path
			$paths = $this->path;
			// validate
			if(!is_array($paths)) $paths = Array($paths);
			// initialize
			$this->modules = Array();
			// cycle directories
			foreach($paths as $path) {
				// get directory
				$d = mgGetDirectory($path);
				// check path
				if(is_array($d)) {
					// load modules
					foreach($d as $name) {
						// create filename
						$fn = $path.$name."/".$this->xml;
						// test module
						if(file_exists($fn)) {
							// load module
							$this->modules[strtolower($name)] = $this->lazyload?$fn:$this->__loadmodule($fn);
						}
					}
				}
			}
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (public) setframework, ability to set a framework reference
		public function setframework($framework) {
			// assign framework
			$this->framework = $framework;
			// update user
			$this->user = $framework->user;
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (public) setuser, ability to set a user reference
		public function setuser($user) {
			$this->user = $user;
		}	
		
		# ---------------------------------------------------------------------------------------------------------------
		# (public) setmessages, set error messages
		public function setmessages($header, $message) {
			$this->messages = Array(
				"header"=>$header,
				"message"=>$message
			);				
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (public) cycle, cycles each module and performs the action described in action
		public function cycle($action) {
			// cycle modules
			foreach($this->modules as $name=>$module) {
				// run action
				$action($this->get($name));
			}
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (public) get, returns a module 
		public function get($name) {
			// retrieve correct name
			$name = strtolower($this->idpath?(stristr($name, $this->idpath)===false?sprintf("%s.%s", $this->idpath, $name):$name):$name);
			// validate service
			if(isset($this->modules[$name])) {
				// check module state
				if(is_string($this->modules[$name])) {
					// lazy load module
					$this->modules[$name] = $this->__loadmodule($this->modules[$name]);
				}
				// return module
				return $this->modules[$name];
			}
			return false;
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (public) getall, returns all services
		public function getall() { return $this->modules;}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (public) process, handles the module
		public function process($request = false, $action = false) {
			// initialize request
			if(!$request) {$request = GetDirVar(2);}
			if(!$action) {$action = GetDirVar(3);}
			// initialize result
			$result = false;
			// get service
			$module = $this->get($request);
			// validate service
			if($module) {
				// run request on service
				if($result = $module->process($action)) {
					switch($result) {
						// service redirect to root
						case MODULE_RESULT_ROOT:
							$this->framework->redirect(HTTP_ROOT); 
							break;
							
						
						// service is raw
						case MODULE_RESULT_RAW:
							$this->framework->__emitheaders(); 
							echo $module->content;
							exit;
						
						// service terminates flow
						case MODULE_RESULT_EXIT: 
							$this->framework->__emitheaders(); 
							exit;
							break;
							
						// service is json
						case MODULE_RESULT_JSON: 
							$this->framework->__emitheaders();
							echo json_encode(is_array($module->content)?$module->content:Array($module->content));
							exit;
							break;
							
						// service is passthrough
						case MODULE_RESULT_PASSTHROUGH: $result = $module->content; break;
					}
				}
			} 
			// check result
			if(!$result) {
				// send out error
				$this->framework->message($this->framework->translate->_($this->messages["header"]), $this->framework->translate->_($this->messages["message"], Array($request)));
			}
		
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (__get) returns the settings
		public function __get($name){
			switch($name) {
				case "modules": return $this->modules; break;
			}
			return isset($this->settings[$name])?$this->settings[$name]:false;
		}	


		# ---------------------------------------------------------------------------------------------------------------
		# (__loadmodule) loads a module based on the module xml
		private function __loadmodule($location) {
			return new $this->class($location, $this);
		}		

	}


	# -------------------------------------------------------------------------------------------------------------------
	# (class) mgModule, manages a single module
	class mgModule {
	
		# ---------------------------------------------------------------------------------------------------------------
		# (private) local stack
		private $location = false;			// location of xml storage file
		private $resources = Array();		// buffer to resources
		private $translateobj = Array();	// translate
		private $user = false;				// reference to user
		
		public $manager;
		public $xml = false;				// XML storage
		public $content = false;			// Content Storage
		
		# ---------------------------------------------------------------------------------------------------------------
		# (constructor)
		public function __construct($location, $manager){
			// initialize
			$this->location = $location;
			$this->manager = $manager;
			$this->user = $manager->user;
			// validate location
			if(file_exists($this->location)) {
				$this->xml = @mgLoadXML($this->location);
			}
			// load localization
			if(isset($this->xml->languages)) {
				// get path
				$p = $this->xml->languages->language;
				// get correct languages
				$l = $p->GetAttribute("language", GetVar(FRAMEWORK_LOCALIZED), false, $p->GetAttribute("type", "default"));
				// check return
				if($l != null) {
					// create translation object
					$this->translateobj = new mgTranslate(false, $l);
				}
			}			
			// load resources
			if(is_array($this->manager->resources)) {
				// run filenames
				foreach($this->manager->resources as $filename) {
					// create filename
					$fn = dirname($location)."/".$filename;
					// check
					if(file_exists($fn)) {
						// load resource
						$this->resources[$filename] = @file_get_contents($fn);
					}
				}
			}
			// initialize content
			$content = Array();
			
			// initialize parent module
			if(method_exists($this, "__initialize")) {
				$this->__initialize();
			}
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (public) __get, catch all
		public function __get($name) {
			switch($name) {
				case "location": return $this->location; break;
				default: return (string)@$this->xml[$name]; break;
			}
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (read) reads a setting
		public function read($name, $default = "") {
			return $this->user->ReadMeta(sprintf("%s.%s", $this->id, $name), $default);
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (write) reads a setting
		public function write($name, $value) {
			return $this->user->WriteMeta(sprintf("%s.%s", $this->id, $name), $value);
		}		
		
		# ---------------------------------------------------------------------------------------------------------------
		# (public) translate, uses a language to translate
		public function asLocalizedString($name, $params = false, $default = false){return DefaultValue($this->translateobj->_($name, $params), $default);}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (public) translate, uses a language to translate to a specific language
		public function asLocalizedStringEx($name, $language){
			// init
			$result = null;
			// find
			$node = $this->xml->languages->language->GetAttribute("language", $language);
			// check node
			if($node) {	
				// get l
				$l = $node->l->GetAttribute("id", $name);
				// test 
				$result = (string)$l;
			}
			return DefaultValue($result, $this->asLocalizedString($name));
		}
			
		# ---------------------------------------------------------------------------------------------------------------
		# (toString)
		public function asString($name, $default="") {return DefaultValue((string)@$this->xml->{$name}, $default);}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (asSetting)
		public function asSetting($name, $attribute, $default="") {return DefaultValue((string)@$this->xml->{$name}[$attribute], $default);}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (toBool)
		public function asBool($name, $attribute = false, $asinteger = false){
			$value = $attribute?$this->asSetting($name, $attribute, "false")=="true":$this->toString($name, "false")=="true";
			return $asinteger?($value?1:0):$value;
		}

		
		# ---------------------------------------------------------------------------------------------------------------
		# (toArray)
		public function asArray($tree) {
			if(count($tree)==0){return false;}
			$result = Array();
			foreach($tree as $item) {
				$resultitem = Array();
				$resultitem["values"] = (string)$item;
				foreach($item->attributes() as $name=>$value){$resultitem[(string)$name]=(string)$value;}
				$result[]=$resultitem;
			}
			return $result;		
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (toImage)
		public function asImage($b) {return sprintf("data:image/png;base64,%s", base64_encode($b));}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (toScript)
		public function asScript($stack) {
			// initialize
			$b = "";
			$stack = is_array($stack)?$stack:array($stack);
			// load buffer
			foreach($stack as $s) {
				// prepare file buffer
				$fb = array($s, sprintf("%s/%s", dirname($this->location), $s));
				// find file
				foreach($fb as $f) {
					if(file_exists($f)) {
						$b .= file_get_contents($f);
						break;
					}
				}
			}
			// strip all comments
			$b = mgStripComments($b);
			// format buffer
			$b = str_replace(array("  ", "\t", "\r\n", "\r", "\n"), "", $b);			
			// second pass
			$b = mgStripComments($b);
			// find translators
			preg_match_all(sprintf("/%s%s(.*?)%s/", TEMPLATE_FIELD_BEGIN, TEMPLATE_FIELD_TRANSLATE, TEMPLATE_FIELD_END), $b, $matches, PREG_PATTERN_ORDER);
			// check result
			foreach($matches[1] as $s) {
				// set default
				$ts = $s;
				// try to translate
				if($this->manager->framework) {
					$ts = $this->manager->framework->translate->_($ts);
				}			
				if($this->translateobj) {
					$ts = $this->translateobj->_($ts);
				}
				// replace translation
				$b = str_replace(sprintf("%s%s%s%s", TEMPLATE_FIELD_BEGIN, TEMPLATE_FIELD_TRANSLATE, $s, TEMPLATE_FIELD_END), $ts, $b);
			}
			// return base64 encoded
			return base64_encode($b);
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (asMetaData), returns an array
		public function asMetaData($name) {
			// initialize
			$result = false;
			// get node
			$node = isset($this->xml->{$name})?$this->xml->{$name}:false;
			// check
			if($node===false) return false;
			// process
			$result = Array();
			foreach((array)$node as $name=>$value) {
				$result[$name] = $value;
			}
			// process
			return $result;
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (asResource), returns a resource
		public function asResource($name, $type = false) {	
			// initialize buffer
			$result = "";
			// check if resource is already named
			if(isset($this->resources[$name])) {
				$result = $this->resources[$name];
			} else {
				// read resource
				$resource = isset($this->xml->resources->resource)?@$this->xml->resources->resource->GetAttribute("name", $name):false;
				// find resource
				$filename = sprintf("%s/%s", dirname($this->location), @$resource["file"]);
				// check filename
				if(file_exists($filename)) {
					// get content
					$result = @file_get_contents($filename);
					// get type
					$type = DefaultValue(@$resource["type"], false);
				}
			}
			// transform
			switch($type) {
				case "image": $result = $this->asImage($result); break; 
			}
			// return
			return $result;
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (public) haspermission, checks against the users permissions
		public function haspermission($role) {
			switch($this->requiredrole) {
				case "administrator": $r = 9999; break;
				case "developer": $r = 6000; break;
				case "manager": $r = 5000; break;
				case "premium": $r = 2; break;
				default: return true;	// by default grant access
			}
			return ($role >= $r);
		}
		# -------------------------------------------------------------------------------------------------------------------
		# (matchrole) matchs the role
		public function matchrole($requiredrole, $needrole = false) {
			// set condition
			$result = false;
			// read module role
			$rolemode = $needrole?$needrole:strtolower($this->asSetting("requirerole", "requires", false));
			// check role mode
			if($rolemode === false || strlen($rolemode)==0) return true; // allowed for all
			// parse condition
			$condition = trim(substr($rolemode, 0, 1));
			$value = trim(substr($rolemode, 1));
			$roles = Array("administrator"=>9999, "developer"=>6000, "manager"=>5000, "premium"=>2, "basic"=>1, "unregistered"=>0);
			// get true 
			if(!in_array($condition, Array('+', '-', '='))) { 
				$condition = false;
				$value = $rolemode;
			}
			// get named value
			if(@isset($roles[$value])) {
				$value = $roles[$value];
			}
			// check condition
			switch($condition) {
				case "-": $result = $requiredrole<=$value;  break;
				case "+": $result = $requiredrole>=$value; break;
				default: $result = $requiredrole==$value; break;
			}
			// return result		
			return $result;
		}
	}		
?>