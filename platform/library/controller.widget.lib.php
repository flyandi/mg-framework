<?php
	/* 
		(mg)framework Version 5.0
		
		Copyright (c) 1999-2011 eikonlexis LLC. All rights reserved.
		
		This program is protected by copyright laws and international treaties.
		Unauthorized reproduction or distribution of this program, or any 
		portion thereof, may result in serious civil and criminal penalties.
	
		Module 		Widget Controller
	*/
	
	# -------------------------------------------------------------------------------------------------------------------
	# Constants Declaration	
	
	// (id's)
	define("WIDGET_ID_PATH", "manager");
	
	// (properties) 
	define("WIDGET_CLASS", "mgWidget");
	define("WIDGET_PATH", "widgets/");
	define("WIDGET_XML", "widget.xml");
	define("WIDGET_SORTFILE", "widget.sort");
	define("WIDGET_BACKSCRIPT", "widget.php");
	define("WIDGET_FRONTSCRIPT", "widget.js");
	define("WIDGET_STYLESCRIPT", "widget.css");
	define("WIDGET_SNIPPETPATH", "snippets/");
	
	// (request modes)
	define("WIDGET_DISPLAY_INTERFACE", "interface");
	
	// (output modes)
	define("WIGDGET_OUTPUT_HTML", "html");
	
	# -------------------------------------------------------------------------------------------------------------------
	# (class) mgWidgetManager, manages the widget modules
	class mgWidgetManager extends mgModuleManager {
		# ---------------------------------------------------------------------------------------------------------------
		# (initialize) initialize the module manager class
		public function initialize(){
			// initialize module manager
			return Array(
				MODULE_CLASS=>WIDGET_CLASS,
				MODULE_PATH=>Array(WIDGET_PATH, SHARED_PATH.WIDGET_PATH),
				MODULE_XML=>WIDGET_XML,
				MODULE_RESOURCES=>Array(WIDGET_BACKSCRIPT, WIDGET_FRONTSCRIPT, WIDGET_STYLESCRIPT)
			);
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (getdisplays) returns all the display settings
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
	# (class) mgWidget, manages a single widget
	class mgWidget extends mgModule {
		
		# ---------------------------------------------------------------------------------------------------------------
		# (private) stack
		public $script = false;						
	
		# ---------------------------------------------------------------------------------------------------------------
		# (__initialize) called when the module is initialized
		public function __initialize() {
			// initialize module side script
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (loadmodule) loads the backendscript
		public function loadmodule() {
			// check widget script
			if($this->asBool("registerjs", "value")===true) {
				// register widget script
				$this->manager->framework->resources->asjs($this->asResource(WIDGET_FRONTSCRIPT));
			}
			
			// create filename
			$fn = sprintf("%s/%s", dirname($this->location), WIDGET_BACKSCRIPT);
			// validate filename
			if(file_exists($fn)) {
				try {
					// load library
					require_once($fn);
					// create module name
					$name = sprintf("%s%s", WIDGET_CLASS, ucfirst($this->name));
					// try to create module
					$this->script = new $name($this);
					// done
				} catch(Exception $e) {
					$this->script = false;
				}
			}
		}		
		
		# ---------------------------------------------------------------------------------------------------------------
		# (process) output, creates the widget output
		public function output($target=false, $settings=false, $values=false, $baseurl=false) {
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
					// assign values
					$this->script->values = $values;
					$this->script->rawvalues = $this->rawvalues;
					$this->script->baseurl = $baseurl;
					// parse default requests
					$result = $this->script->process($target, is_array($settings)?$settings:Array(), $baseurl);
					// check result
					if($result!==false) {
						// add css if any
						$this->manager->framework->resources->ascss($this->asResource(WIDGET_STYLESCRIPT));
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
				# (interface)
				case WIDGET_DISPLAY_INTERFACE:
					// create data array
					return Array(
						"widgetid"=>$this->id,
						"widgetenabled"=>$this->asBool("settings", "enabled"),
						"widgetname"=>$this->name,
						"widgetscript"=>$this->asScript(WIDGET_FRONTSCRIPT),
					);				
					break;
			}
		}
	}
	
	# -------------------------------------------------------------------------------------------------------------------
	# (class) mgWidgetClass 
	class mgWidgetClass extends mgAdopterClass {
	
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
				case "urlpath": return sprintf(WIDGET_URLPATH, $this->id); break;
				case "gateway": return sprintf(WIDGET_GATEWAY, $this->urlpath); break;
				case "status": return $this->read("status", WIDGET_STATUS_DISCONNECTED); break;
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
			$fn = sprintf("%s/%s%s.snippet", dirname($this->location), WIDGET_SNIPPETPATH, $name);
			// check if exists
			return file_exists($fn)?file_get_contents($fn):false;
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (isuser) connects to another widget
		public function isuser() {
			return $this->manager->framework->issecured();		
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (connect) connects to another widget
		public function connect() {
			// initialize result
			$result = false;
			// get args
			$args = func_get_args();
			// test 
			if(count($args)<2) return false;
			// get variables
			$target = $args[0];
			$method = $args[1];
			$arguments = (count($args)>2)?array_shift(array_shift($args)):false;
			// get widgets
			$widget = $this->manager->get(sprintf("widget.%s", $target));
			// load script
			if(!$widget->script) {
				$widget->loadmodule();
			}
			// test script and method
			if($widget->script&&method_exists($widget->script, $method)) {
				// assign values
				$widget->script->values = $this->values;
				$widget->script->rawvalues = $this->rawvalues;
				// assign result
				$result = $widget->script->$method($arguments);
			}
			// return result
			return $result;
		}
		
	
		
		# ---------------------------------------------------------------------------------------------------------------
		# (format) formats a string and transltes it
		public function format() {
			$args = func_get_args();
			if(count($args)>0) {
				// get string
				$str = $args[0];
				// replace language string
				$str = str_replace("%language", "default", $str);
				// replace values
				$args = array_shift($args);
				// replace with values
				foreach($this->values as $n=>$v) {
					$str = mgReplaceExactString($n, $v, $str);
				}
				// translate 
				$str = $this->manager->framework->translate->_($str);
				// add variables
				$str = vsprintf($str, $args);
				// return string
				return $str;
			}
			// return false
			return false;
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
		# (condition) processes a conditions array
		public function condition($conditions) {
			// initialize result
			$result = false;
			// cylce conditions
			foreach($conditions as $r=>$p) {
				// check condition name 
				if(strtolower($this->value(@$p["name"])) == strtolower(@$p["value"])) {
					$result = $r;
					break;
				}
			}
			// return result
			return $result;
		}
	}
	
