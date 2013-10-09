<?php
	/* 
		(mg)framework Version 5.0
		
		Copyright (c) 1999-2011 eikonlexis LLC. All rights reserved.
		
		This program is protected by copyright laws and international treaties.
		Unauthorized reproduction or distribution of this program, or any 
		portion thereof, may result in serious civil and criminal penalties.
	
		Module 		Framework Controller
	*/

	
	# ------------------------------------------------------------------------------------------------------------------- 	
	# Include Libraries
	include ("library/constants.lib.php");
	include ("library/core.lib.php");
	
	# -------------------------------------------------------------------------------------------------------------------
	# Version Information
	define("VERSION_FRAMEWORK", "5.0");
	
	# -------------------------------------------------------------------------------------------------------------------
	# __autoload
	function __autoload($name) {
	}
	
	# --------------------------------------------------------------------------------------------------------
	# (class) mgFrameworkApplication, framework application controller
	class mgFrameworkApplication {
	
		# ---------------------------------------------------------------------------------------------------------------
		# (private) stack
		private $database;			// database
		private $application;		// application
		private $buildtime;			// buildtime
		
		# ---------------------------------------------------------------------------------------------------------------
		# (public) stack
		public $cache;				// cache
		public $resources;			// resources
		public $personality;		// personality controller
		public $personalityrequest;	// current personality request
		public $template;			// template controller
		public $user;				// user controller
		public $apiauth;			// apiauth controller
		public $session;			// session controller
		public $translate;			// translate/language
		public $components;			// component controller
		
		public $options;			// options storage
		public $output;				// output buffer
		public $properties;			// Property Storage
		public $currentrole;		// Current Role
		
		
		public $localized;			// storage for localized language
			
		# ---------------------------------------------------------------------------------------------------------------
		# (public) stack strings
		public $sessionid;			// shortcut session
		
	
		# ---------------------------------------------------------------------------------------------------------------
		# (constructor)
		public function __construct($application=false) {
			// execute sys constructor
			$this->__frameworkconstruct($application);
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (__frameworkconstruct)
		public function __frameworkconstruct($application=false) {
			// check redirect
			if(IfAppVar("CONTENT_REDIRECTTRAILSLASH", true)) {
				$request = GetRequestString(false);
				if($request != "/" && substr($request, -1) == "/") {
					header("HTTP/1.1 301 Moved Permanently");
					header(sprintf("Location: %s", GetRequestString(true))); 
					exit;
				}
			}
			
			// check lowercase
			if(IfAppVar("CONTENT_REDIRECTLOWERCASE", true)) {
				$request = GetRequestString(false, true);
				if(!IsLowerCase($request)) {
					header("HTTP/1.1 301 Moved Permanently");
					header(sprintf("Location: %s%s", strtolower(GetRequestString(true, true)),  GetRequestString(true, false, true, true))); 
					exit;
				}
			}
		
			// initialize randomizer
			srand((double)microtime()*1000000);
				
			// initialize buildtime
			$this->buildtime = microtime();
		
			// set application
			$this->application = $application;
			
			// set some acl
			$this->currentrole = 0;
			
			// Load Framework Controllers
			$this->__loadassets(Array(
				FRAMEWORK_DEFAULTCONTROLLERS, 
				LIBRARY_PATH, 
				sprintf("%s/%s", $this->application->path, LIBRARY_PATH)
			));
			
			// Try Change to Application Path
			@chdir($this->application->path);
			
			// Initialize Database
			$this->database   = new mgDatabase($this);			
						
			// initialize components
			$this->components = new mgComponents(COMPONENT_ID_PATH);			
			
			// initialize Cache
			$this->cache = new mgCache();
			
			// execute pagecache
			if(mgHasPageCache()) {
				if(!mgPageCacheIsSecureCookie()) {
					if($content = $this->cache->page(false, false, false, IfAppVar("CACHE_PAGECACHEVERBOSE", true))) {
						//if($this->cache->pageresources()) {
						// check onconstruct
						if(method_exists($this, '__afterconstruct')) {
							$this->__afterconstruct();
						}
						// emit content
						$this->emitraw($content, MIME_HTML);
					}
				}
			}
			
			// check non-database transaction
			if(AppVar("REQUESTS_NODATABASE", false)!==false && in_array(GetDirVar(0), explode(",", REQUESTS_NODATABASE))) {
				// initialize non database resources
				$this->__initializeresources(true);
				return; // end and continue
			}
			
			// Initialize Session
			if(AppVar("REQUESTS_NOSESSION", false)!==false && in_array(GetDirVar(0, REQUEST_ROOT), explode(",", REQUESTS_NOSESSION))) {
				$this->session = false;
				$this->sessionid = false;
			} else {
				$this->session	 = new mgSession(GetVar("session", GetHTTPVar("ETag", false)));		
				$this->sessionid = $this->session->session;
			}
			
			// Set Session Var
			SetVar(FRAMEWORK_SESSION, $this->sessionid);			
		
			// Initialize User Manager
			$this->user = new mgUserController(DB_FROMSESSION, $this->sessionid);
			
			// Load Personality based on domain condition or select default
			$this->personality = new mgPersonality();
			
			// initialize resources
			$this->__initializeresources(false);
			
			// Initialize Language
			$this->__language();		
			
			// Initialize Template
			$this->template = new mgTemplate($this->translate, false, $this);	
			
			// set default template
			$this->option(TEMPLATE, TEMPLATE_BLANK);		// blank page
				
			// set default output mode
			$this->outputmode();			
			
			// add personality forms to the template
			foreach(@$this->personality->forms as $form) {
				$this->template->AddForm($form->name, $form->source);
			}
			
			// Initialize API Auth
			$this->apiauth = false;
			if(GetVar(APIAUTH, false)==APIAUTH_ISAPI) {
				// create auth controller
				$this->apiauth = new mgAPIAuthController();
				// sign in user
				$this->__signin(USER_SIGNIN_TOKEN);	
			} else {					
				// check for slug
				$this->__processslug();
			}
			
			// check onconstruct
			if(method_exists($this, '__afterconstruct')) {
				$this->__afterconstruct();
			}
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (constructor) __createrequestinstance, this will create another instance
		public function __createrequestinstance($request, $returnbuffer = false) {
			// set server var
			SetServerVar("REQUEST_URI", $request);
			// create new instance
			$sf = new mgApplication($this->application);
			// buffer
			$buffer = $sf->Emit($returnbuffer);
			// kill instance
			unset($sf);
			$sf = null;
			// return
			return $buffer;
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (property) __set
		public function __set($name, $value) {$this->properties[$name]=$value;}		
		
		# ---------------------------------------------------------------------------------------------------------------
		# (property) __get
		public function __get($name) {return @$this->properties[$name];}			
		
		# ---------------------------------------------------------------------------------------------------------------
		# (property) __call, prevent calling any undefined function
		public function __call($name, $arguments) {}	
		
		# ---------	------------------------------------------------------------------------------------------------------
		# (public) option, sets a option the current stack
		public function option($option, $content=null, $add = false) {
			if($content==null){
				return DefaultValue(@$this->options[$option], false);
			}else{
				if($add) {
					if(!isset($this->options[$option])) {
						$this->options[$option] = Array();
					}
					$this->options[$option][] = $content;
				} else {
					@$this->options[$option]=$content;
				}
			}
		}
		
		# ---------	------------------------------------------------------------------------------------------------------
		# (public) outputmode, sets the output mode
		public function outputmode($mode = OUTPUT_HTML, $type = "text/html", $buffer = false) {
			$this->option(OUTPUT, $mode);	// HTML output
			$this->option(CONTENTTYPE, $type);
			$this->output = $buffer;
		}		
		
		# ---------------------------------------------------------------------------------------------------------------
		# (redirect) redirect, redirects
		public function redirect($url, $code = 302) {$this->__httpredirect($url, $code);}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (forcehttps) forces that the connection is secured
		public function forcehttps() {
			// check api auth
			if($this->apiauth&&$this->apiauth->IsAuthorized()) return false;
			// check if https
			if(!isset($_SERVER['HTTPS'])||$_SERVER['HTTPS']!="on") {
				// redirect
				$this->redirect(sprintf("https://%s%s", $_SERVER['SERVER_NAME'], $_SERVER['REQUEST_URI'])); 
			}
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (redirect) message, displays a user message
		public function message($header, $text, $http = HTTP_200) {
			// return message
			DieCriticalError($header, $text, $header, $http);
		}
		
		
		# ---------------------------------------------------------------------------------------------------------------
		# Browser Warning (Chrome Frame supported)
		public function browserwarning() {
			$browser = mgGetBrowser();
			# Lockdown IE6/7, FF1/2, Unknown
			if(($browser["type"]==BROWSER_UNKNOWN)||($browser["type"]==BROWSER_MSIE)||($browser["type"]==BROWSER_MOZILLA&&$browser["version"]<3)) {
				$this->message($this->translate->_("BrowserNotSupportedTitle"), $this->translate->_("BrowserNotSupportedText"));
				return false;
			}	
			return true;
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# Maintenance Warning 
		public function maintenancewarning() {
			$browser = mgGetBrowser();
			// check maintenance mode
			if(defined("FRAMEWORK_MAINTENANCE")&&FRAMEWORK_MAINTENANCE) {
				// check remote ip address
				$ips = defined("FRAMEWORK_MAINTENANCE_ALLOWED")?explode(",", FRAMEWORK_MAINTENANCE_ALLOWED):Array();
				// check if remote ip is in ip
				if(in_array(GetRemoteAddress(), $ips)) return true;
				// show message
				$this->message($this->translate->_("MaintenanceTitle"), $this->translate->_("MaintenanceText"));
				return false;
			}
			return true;
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (public) ismobile, checks if the current connection is mobilized
		public function ismobile() {
			return mgIsMobileBrowser();
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (public) issecuired, returns if the user is logined
		public function issecured() {
			return ($this->apiauth&&$this->apiauth->IsAuthorized())?$this->user->result==DB_OK:($this->user?$this->user->Verify($this->sessionid):false);
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (public) requestunsecuredcontent (passthrough function)
		public function requestunsecuredcontent($__contentarea, $__request, $areas) {
			// check if request is within allowed areas
			if($this->issecured() && in_array($__request, $areas)) {return CONTENT_UNSECURED;}
			// return 
			return $__contentarea;
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (public) requirerole, requests the user permission
		public function requirerole($role, $redirect=true) {
			// check if user has required role
			$userrole = $this->user->result == DB_OK ? $this->user->Read(DB_FIELD_ROLE) : $this->currentrole;
			$result = ($userrole >= $role);
			// check result
			if(!$result&&$redirect) {
				$this->__httpredirect(HTTP_ROOT);
			}
			// return result
			return $result;
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (public) use, loads a component
		public function usecomponent($name) {
			// get component
			$c = $this->components->get($name);
			// verify
			if($c) {
				// load includes
				$c = $c->includes();
			}
			// return status
			return $c;
		}		
				
		# ---------------------------------------------------------------------------------------------------------------
		# (public) processextension, processes a framework extension
		public function processextension($name, $requiredrole=false, $isapi=false, $request=false) {
			$result = false;
			// check role for this extension
			if($requiredrole!==false){$this->requirerole($requiredrole);}
			// initialize
			if($m = $this->LoadExtension($name, true)) {
				// calls action processor
				if(GetVar("__post")=="true"){
					// action processor
					$m->Action($request?$request:GetDirVar($isapi?2:1));
					// add from post values if available
					$this->template->MergeArray($_POST);
				}
				// call extension processor
				$result = $m->Process($request?$request:GetDirVar($isapi?2:1));
				// parse by ContentType
				switch($m->contentmode) {
					# ---------------------------------------------------------------------------------------------------
					# (content) returns a content, update content
					case EXTENSION_RESULT_CONTENT: 
						$this->option(CONTENT, $m->content->GetParsed()); 
						$this->template->MergeArray($m->content->values);
						break;
					
					# ---------------------------------------------------------------------------------------------------
					# (raw) return content raw
					case EXTENSION_RESULT_RAW:
						$this->RawOutput($m->content->GetParsed(false)); 
						break;
					
					# ---------------------------------------------------------------------------------------------------
					# (default) error
					case EXTENSION_RESULT_ERROR:
					default:
						$this->message($m->errortitle, $m->errormessage);
						break;
				}
			} else {
				// display error message
				$this->message("Broken Arrow", sprintf("Extension mg%s%s does not exists", APPLICATION_NAME, ucfirst($name)));
				//$this->__httpredirect(HTTP_ROOT); 
			}
			return $result;
		}

		# ---------------------------------------------------------------------------------------------------------------
		# (public) loadextension, loads an project extension controller
		public function loadextension($name, $create=true){
			// sanity check
			if(!defined("APPLICATION_NAME")||!defined("APPLICATION_ID")) return false;
			// initialize
			$result = false;
			// create filename
			$fn = sprintf(EXTENSION_FILENAME, APPLICATION_ID, $name);
			// check if file exists
			if($result = file_exists($fn)){
				// include module
				try {
					require_once($fn);
					// test to create
					if($create) { 
						$class = sprintf("mg%s%s", APPLICATION_NAME, ucfirst($name));
						$result = new $class($this);
					}
				}catch(Exception $e) {
					//echo $e->getMessage();
					//exit;
				}				
			}
			// return
			return $result;
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (public) Cron, runs cron related functions
		public function cron() {
			// get arguments
			global $argv;
			// set some acl
			$this->currentrole = ROLE_CRON;
			// initialize arguments
			$arguments = Array();
			// initialize system values
			$cronlock = true;
			// parse arguments			
			foreach(array_slice($argv, 1) as $index=>$value) {
				// switch for system keywords
				switch($value) {
					// Override Cron Lock
					case "-p": $cronlock = false; break;
					// assign value to arguments
					default: $arguments[] = $value;
				}
					
			}
			// cronlock - prevents to run cron multiple times
			if($cronlock) {
				// read lock
				$v = @file_get_contents(FRAMEWORK_CRON_LOCK);
				// check
				if(strlen($v)!=0) {
					// terminate
					return false;
				}
				// run cronlock
				file_put_contents(FRAMEWORK_CRON_LOCK, date("c"));
			}
			// execute default commendas
			$handled = false;
			if(isset($arguments[1])) {
				switch(strtolower($arguments[1])) {
					// (Force Recration of Sitemap)
					case "seositemap": 
						if(method_exists($this, '__seohandler')) {
							$handled = $this->__seohandler(REQUEST_SITEMAP, isset($arguments[2])&&$arguments[2]=="-f"?SEO_TIMEOUT_FORCE:SEO_TIMEOUT_STANDARD, true);
						}					
						break;
				}
				
			}
			if(!$handled) {
				// execute cont	ent with command line options
				$this->__execute(CONTENT_CRON, $arguments);
			}
			// terminate cron
			$this->CleanupCron();		
			// return
			return true;
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (public) Cron, runs cron related functions
		public function CleanupCron() {
			// remove cronlock
			file_put_contents(FRAMEWORK_CRON_LOCK, "");
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (public) Emit, returns the content
		public function emit($returnbuffer = false) {
			// Process any API actions
			$this->__apiauthactions();
			// Process Request
			$this->__request();
			// filter output mode
			switch($this->option(OUTPUT)) {
				# -------------------------------------------------------------------------------------------------------
				# (html)
				case OUTPUT_HTML:
					// Process Actions
					$this->__actions();
					// Run Content
					$this->__content();
					break;
				# -------------------------------------------------------------------------------------------------------
				# (default/raw)
				default: 
					break;
			}
			// Output Headers & Cookies
			if(!$returnbuffer) $this->__emitheaders();
			// Output 
			return $this->__emitoutput($returnbuffer);
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (public) EmitRaw, emits the headers + a defined content
		public function emitraw($output, $mime = false, $exit = true) {
			// Output Headers & Cookies
			$this->__emitheaders();
			// Output 
			mgOutput($output, $mime);
			// exit and end
			if($exit) { exit;}
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (private) __emitheaders, sends headers to the browser
		private function __emitheaders($preventcaching = false) {
			// load headers
			$headers = Array(
				"Vary"=>"Accept-Encoding",
				"X-Powered-By"=>"(mg)framework by eikonlexis.com",
				"X-UA-Compatible"=>"IE=edge,chrome=1",
				"ETag"=>GetVar(ETAG, DefaultValue($this->sessionid, GetHttpVar("ETag")))
			);
			if(isset($this->application->xml->httpheaders)) {
				foreach($this->application->xml->httpheaders->header as $h) {
					$headers[(string)$h["name"]] = (string)$h["value"];
				}
			}
			// output headers and cache control
			mgOutputHeaders($headers, $this->option(HEADER_FORCECACHING)===true?false:$preventcaching);
			
			# -----------------------------------------------------------------------------------------------------------
			# Cookies
			// session cookie 
			if($this->sessionid && $this->option(HEADER_NOCOOKIES) !== true && $this->personality) {
				mgSetCookie("session", $this->sessionid, $this->personality->config("cookie-lifetime", 30) * COOKIETIME_MINUTE);
			}
			// pagecache cookie
			if(mgHasPageCache()&&$this->option(HEADER_NOCOOKIES)!==true && $this->issecured()) {
				mgPageCacheSetCookie($this->issecured());
			}
			
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (private) emitcontent, 
		public function emitcontent() {
			// emit headers
			$this->__emitheaders();
			// build content
			$this->__content(true);
			// output
			$this->__emitoutput();
		}
			
		# ---------------------------------------------------------------------------------------------------------------
		# (private) __emitoutput, sends the output to the device
		public function __emitoutput($returnbuffer = false) {
			// run page cache
			if(mgHasPageCache()) {
				if($this->issecured()) {
					$this->cache->removepage();
				} else {
					$this->cache->page($this->output);
				}
			}
			// start output
			if($returnbuffer) return $this->output;
			// output
			mgOutput($this->output, $this->option(CONTENTTYPE)); 
			// safety exit
			exit;
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (private) __actions, executes actions like posts
		private function __actions() {
			//-----------------------------------------------------------------------------------------------------------
			// Process Posts
			if(mgIsFormPosted()) {
				// selector
				switch(GetDirVar(0)) {
					# ---------------------------------------------------------------------------------------------------
					# (action) SignIn
					case ACTION_SIGNIN: $this->__signin(GetVar("__signinmethod", USER_SIGNIN_CREDENTIALS)); break;
					
					# ---------------------------------------------------------------------------------------------------
					# (action) SignUp
					case ACTION_SIGNUP: $this->__signup(USER_SIGNUP); break;
				}
			} 
			//-----------------------------------------------------------------------------------------------------------
			// execute project action handler
			$this->__executeaction(GetDirVar(0), mgIsFormPosted());
		
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (private) __apiauthactions, executes api-auth actions
		private function __apiauthactions() {
			//--------------------------------------------------------------------------------------------------------
			// Process APIAUTH
			if($this->apiauth&&$this->apiauth->IsAuthorized()) {
				switch(GetDirVar(0)) {
					# ---------------------------------------------------------------------------------------------------
					# (action) Auth Token
					case ACTION_AUTHTOKEN: $this->__signin(USER_SIGNIN_TOKEN); break;
					
					# ---------------------------------------------------------------------------------------------------
					# (action) APIAUTH SignIn
					case ACTION_SIGNIN: $this->__signin(USER_SIGNIN_APIAUTH); break;
					
					# ---------------------------------------------------------------------------------------------------
					# (action) APIAUTH SignUp
					case ACTION_SIGNUP: $this->__signup(USER_SIGNUP_APIAUTH); break;
				}
			}
		}
		
		
		# ---------------------------------------------------------------------------------------------------------------
		# (private) __request, processes the request
		private function __request($orequest = false) {
			// check for maintenance
			if(!$this->maintenancewarning()) return false;
			// execute project content handler
			$__secured = $this->issecured();
			// initialize request
			$this->personalityrequest = false;
			// execute personality requests
			if(isset($this->personality)) {
				if(!in_array(GetDirVar(0), Array(REQUEST_MANAGER, REQUEST_RESOURCES, REQUEST_API, REQUEST_VERIFY, REQUEST_LOCALIZE))) {
					$this->personalityrequest = $this->personality->MatchRequest(GetDirVar(0), $__secured?CONTENT_SECURED:CONTENT_UNSECURED, $this->localized);
				}
			}
			// get request
			$ur = $orequest!==false?$orquest:($this->personalityrequest!==false?$this->personalityrequest->type:GetDirVar(0));
			// handled flag
			$handled = false;
			// switch request
			switch($ur) {
			
				// -------------------------------------------------------------------------------------------------------
				// Request Message
				case REQUEST_MESSAGE:
					DieCriticalError($this->personalityrequest->value);
					break;
				
				// -------------------------------------------------------------------------------------------------------
				// Request Extension
				case REQUEST_EXTENSION:
					// set template
					$this->option(TEMPLATE, @$this->personalityrequest->template);
					// run extension on user
					$this->ProcessExtension($this->personalityrequest->value, $this->personality->usergroup);
					break;
					
				// -------------------------------------------------------------------------------------------------------
				// Request Content
				case REQUEST_CONTENT:
					$this->option(CONTENT, CONTENT_PERSONALITY);
					break;	
				
				// -------------------------------------------------------------------------------------------------------
				// (Redirects)
				case REQUEST_HTTP301:
					header("HTTP/1.1 301 Moved Permanently");
					header(sprintf("Location: %s", $this->personalityrequest->value)); 
					exit;
					break;
				
				case REQUEST_HTTP302:
					header("HTTP/1.1 302 Found");
					header(sprintf("Location: %s", $this->personalityrequest->value)); 
					exit;
					break;
					
				case REQUEST_HTTP303:
					header("HTTP/1.1 303 See Other");
					header(sprintf("Location: %s", $this->personalityrequest->value)); 
					exit;
					break;
				
				// -------------------------------------------------------------------------------------------------------
				// (Errors)
				case REQUEST_HTTP403:
				case REQUEST_HTTP404:
					header("HTTP/1.1 404 Not Found");
					DieCriticalError($this->personalityrequest->value);
					
					break;
				
				// -------------------------------------------------------------------------------------------------------
				// Logout Request
				case REQUEST_LOGOUT: 
					// logout user
					$this->user->Logout(); 
					// redirect user to root
					$this->__httpredirect(HTTP_ROOT); 
					break;
					
				// -------------------------------------------------------------------------------------------------------
				// Language
				case REQUEST_LOCALIZE:
					// switch language
					$language = GetDirVar(1);
					// verify language
					if(in_array($language, explode(",", APPLICATION_LOCALIZED_LANGUAGES))) {
						// set user language
						if($this->issecured()) {
							$this->user->write(DB_FIELD_LOCALIZED, $language, true);
						} 
						// set session
						$this->session->WriteMeta(DB_FIELD_LOCALIZED, $language, true);
					}
					// redirect location
					$this->__httpredirect(isset($_SERVER["HTTP_REFERER"])?$_SERVER["HTTP_REFERER"]:HTTP_ROOT);
					break;
					
					
				// -------------------------------------------------------------------------------------------------------
				// Verify
				case REQUEST_VERIFY:
					// test if resend is requested
					if($__secured&&!$this->user->IsVerified()) {
						if(GetDirVar(1)=="resend") {
							// protect resend (max 3 retries)
							$counter = $this->user->ReadMeta("verify_resend", 0);
							// validate counter
							if($counter < 3) {
								// request verify email
								mgSendTemplatedMail($this->user->username, "verify", Array(
									"username"=>$this->user->username,
									"firstname"=>ucfirst($this->user->ReadMeta("user_firstname")),
									"lastname"=>ucfirst($this->user->ReadMeta("user_lastname")),
									"verify"=>$this->user->Read("verify")
								));		
								// update counter
								$this->user->WriteMeta("verify_resend", $counter + 1, true);
							}
						} else {
							// check if user can be verified
							mgVerifyUser(GetDirVar(1));
						}
					} else {
						// require offline verification
					}
					// redirect user to root
					$this->__httpredirect(HTTP_ROOT);				
					break;
					
				// -------------------------------------------------------------------------------------------------------
				// Resources
				case REQUEST_RESOURCES: 
					// retrieve cache state
					if(GetDirVar(1) == REQUEST_CACHE || GetDirVar(1) == REQUEST_CACHEDYNAMIC) {
						$r = $this->resources->fromcache(GetDirVar(2), IfAppVar("CACHE_FILE_CLEANUP", false)?false:true, GetDirVar(1)== REQUEST_CACHEDYNAMIC);
					} else {
						$r = $this->resources->fromdir(GetRequestVar(3, false, true));
					}
					// set options
					if(DefaultValue(@$r["dynamic"], false) !== true) {
						$this->option(HEADER_NOCOOKIES, true);
						$this->option(HEADER_FORCECACHING, true);
						if(isset($r["filetime"])) {
							SetVar(ETAG, md5($r["filetime"]));
						}
					}
					// set output mode
					$this->outputmode(OUTPUT_RAW, $r?$r["type"]:"", @$r["buffer"]);
					break;

				// -------------------------------------------------------------------------------------------------------
				// Assets
				case REQUEST_ASSETS:
					// create assets
					$assets = new mgAssets($this, $__secured);
					// obtain asset
					$assets->request();
					break;
					
				// -------------------------------------------------------------------------------------------------------
				// API Access, read from project controller
				case REQUEST_API: 
					// switch by request
					switch(GetDirVar(1)) {
						// (option) 
						case REQUEST_OPTION:
							// output option
							$this->emitraw(json_encode(mgReadOption(GetDirVar(2))), mgGetMime(".json"));
							break;
							
						// (assets)
						case REQUEST_ASSETS:
							// create assets
							$assets = new mgAssets($this, $__secured);
							// execute 
							$assets->request(GetDirVar(2));
							break;
							
						// (manager)
						case REQUEST_MANAGER:
							if($__secured) {
								// set resources
								$this->resources->setdynamic();
								// create manager 
								$manager = new mgManager(MANAGER_ID_PATH);
								// set framework
								$manager->setframework($this);
								// process
								$manager->process();
								break;
							}
							
						// (extension)
						case REQUEST_EXTENSION:	
							$this->ProcessExtension(GetDirVar(2), false, true, GetDirVar(3));
							break;
							
						default:
							$this->__execute(CONTENT_API, GetDirVar(1), $__secured); 
							break;					
					}
					break;
					
					
				// -------------------------------------------------------------------------------------------------------
				// Request Manager
				case REQUEST_MANAGER:
					// set handled
					$handled = true;
					// set resources
					$this->resources->setdynamic();
					// check if a template was defined
					if(!$c = $this->personality->MatchRequest(REQUEST_MANAGER, CONTENT_SECURED)) {
						$c = (object)Array(	
							// set template
							"template"=>"<div class='-manager-container'></div>",
							"usergroup"=>ROLE_ADMINISTRATOR,
							"includes"=>false,
							"usecss"=>true
						);
					} 
					// get user role
					$urole = $this->user->result == DB_OK ? $this->user->Read(DB_FIELD_ROLE) : $this->currentrole;
					// verify user if rights are available, access to this panel
					if($urole >= $c->usergroup) {
						// callback
						if(method_exists($this, '__requestmanager')) {
							if($result = $this->__requestmanager()) {
								exit;
							}
						}
						// set template
						$this->option(TEMPLATE, $c->template);
						// initialize manager view
						$manager = new mgManager(MANAGER_ID_PATH);
						// initialize service manager
						$manager->setframework($this);
						// initialize user role
						$manager->setuserrole($urole);
						// includes
						$this->resources->register(Array(
							sprintf("%s/jquery", FRAMEWORK_RESOURCEPATH),
							sprintf("%s/platform", FRAMEWORK_RESOURCEPATH),
						));
						$this->resources->register($c->includes);
						// get modules
						$this->resources->asjs(mgScriptVar("mgManagerModules", $manager->getdisplays(MANAGER_DISPLAY_INTERFACE)));
						// set script
						$this->resources->asjs("var mgManager=false;$(function(){mgManager = new mgCreateManager({target:'.-manager-container', width: 950, modules: typeof(mgManagerModules)=='object'?mgManagerModules:false});});");
						// set css
						if(isset($c->usecss)) {
							$this->resources->ascss(".-manager-container{width:950px;margin:0 auto;margin-top:20px}");
						}
						// end processing
						break;
					}
					
				// -------------------------------------------------------------------------------------------------------
				// SEO Handlers
				case REQUEST_ROBOTS:
				case REQUEST_SITEMAP:
				case REQUEST_SITEMAPGZ:
				case REQUEST_FAVICON:
				case REQUEST_FAVICONICO:
					// check handled
					if($handled === false) {
						// handled
						$handled = false;
						// check if handler is defined
						if(method_exists($this, '__seohandler')) {
							$handled = $this->__seohandler($ur);
						}
						// check handler
						if(!$handled) {
							// public path in application context
							$fn = sprintf("%s%s", PUBLIC_PATH, $ur);
							// check
							if(file_exists($fn)) {
								// get buffer
								$buffer = file_get_contents($fn);
								// replace
								foreach(Array("BASEPATH"=>BASEPATH) as $key=>$value) {
									$buffer = str_replace(sprintf("{%s}", $key), $value, $buffer);
								}
								// no cookies
								$this->option(HEADER_NOCOOKIES, true);
								// response header
								$this->__emitheaders();
								// spit out buffer
								mgOutputBuffer($buffer, mgGetMime($fn));
								break;
							}
						}	
					}
										
				// -------------------------------------------------------------------------------------------------------					
				// (default) read from application controller
				case REQUEST_APPLICATION:
				default: 
					// Process Request
					$this->__execute($__secured?CONTENT_SECURED:CONTENT_UNSECURED, GetDirVar(0), $__secured); 
					break;
			}
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (private) __content, executes the content
		private function __content($cnt = false) {
			// initialize content
			$content = "";
			// get option
			switch($this->option(CONTENT)) {
				# -------------------------------------------------------------------------------------------------------
				# (predefined) SignIn/SignUp
				case CONTENT_SIGNUP: case CONTENT_SIGNIN: case CONTENT_AUTHTOKEN: 
					// assign identifier
					$ident = $this->option(CONTENT);
					// load content snippet
					$content = mgContent($this->__contentfilename($ident));
					// add from post values if available
					$this->template->MergeArray(mgSanitizeArray($_POST));
					break;
					
					
				# -------------------------------------------------------------------------------------------------------
				# (personality)
				case CONTENT_PERSONALITY: 
					// verify personality request
					if($this->personalityrequest) {
						// resources
						if(isset($this->personalityrequest->includes)) {
							$this->resources->register($this->personalityrequest->includes);
						}
						// retrieve content
						if($c = $this->personality->GetContent($this->personalityrequest->value)) {
							// set template
							$this->option(TEMPLATE, (string)$c->template);
							// get localized source
							$content = mgLocalizedArray($c->source, $this->localized, "source");
							break;
						}
					}
					
				# -------------------------------------------------------------------------------------------------------
				# (default)
				default: 
					// get content
					$content = $this->option(CONTENT);
					// test if content is filename
					$content = $this->__contentfilename($content);
			}
			
			//-----------------------------------------------------------------------------------------------------------
			// Process Content
			
			# error reporting (post/form/general)
			$this->template->Write("ACTIONERROR", (strlen($this->errorlist)==0)?"none":"block");
			$this->template->Write("ACTIONERRORLIST", $this->errorlist);
			
			//-----------------------------------------------------------------------------------------------------------
			// Create Template
			switch($this->Option(TEMPLATE)) {
				# -------------------------------------------------------------------------------------------------------
				# Blank page, just display blank page
				case TEMPLATE_BLANK: break;
				
				# -------------------------------------------------------------------------------------------------------
				# (default) assign the selected template
				default:

					// load template
					$this->template->AssignFromSource($this->__template($this->Option(TEMPLATE)));
					
					// set callback
					$this->template->Callback(Array($this->Option(CALLBACK), "Callback"));
					
					// write content
					$this->template->Buffer("CONTENT", $content);
					
					// initialize header
					$header = "";
					
					// only build header for non-content-only
					if(!$cnt) {

						// create meta tags array
						$meta = array_merge(
							is_array($v = $this->personality->config('default-meta'))?$v:Array(),
							is_array($v = $this->Option(META))?$v:Array(),
							Array(
								"mg-framework-version"=>VERSION_FRAMEWORK, 
								"mg-framework-server"=>SERVER_ID, 
								"mg-framework-timestamp"=>time(),
								"mg-framework-buildtime"=>sprintf("%0.3f", mgGetMicrotime() - mgGetMicrotime($this->buildtime))
							)
						);
						
						// add meta tags
						foreach($meta as $name=>$content) {
							if(strlen($name)!=0&&strlen($content)!=0) {
								$header .= Tag("meta", Array("name"=>$name, "content"=>htmlentities($content)), false, true)."\n";
							}
						}
						
						// add charset
						$header .= Tag("meta", Array("http-equiv"=>"Content-Type", "content"=>defined("META_CHARSET")?META_CHARSET:"text/html; charset=utf-8"), false, true)."\n";
						
						// add style resources
						$header .= $this->resources->compilecached(RESOURCES_CSS, true);
						
						// add print stylesheet
						if($print = $this->personality->config("stylesheet-print")) {
							$header .= Tag("link", Array("rel"=>"stylesheet", "media"=>"print", "type"=>"text/css", "href"=>$print), false, true);
						}
						
						// google
						$header .= ($this->personality->config("google-includes")?$this->personality->GetSnippet("google"):"")."\n";						
						
						// add javascript resources
						$header .= $this->resources->compilecached(RESOURCES_JS, true);
						
						// favicon
						if($icon = $this->personality->config("web-favicon")) {
							$header .= Tag("link", Array("rel"=>"icon", "type"=>"image/png", "href"=>$icon), false, true)."\n";
						}
						
						// mobile headers
						if($this->ismobile()) {
							// create apple icons
							$link = $this->personality->config("apple-touch-icon");
							// create
							foreach(Array(57=>false, 72=>false, 114=>false) as $size=>$usesize) {
								// create size
								$sizes = sprintf("%sx%s", $size, $size);
								// add header
								$header .= Tag("link", array_merge(Array("rel"=>"apple-touch-icon", "href"=>sprintf($link, $sizes)), $usesize?Array("sizes"=>$sizes):Array()), false, true);
							}
						}
						
						// document title
						$title = "";
						if($this->option(DOCUMENTTITLE)==null) {
							// try to match page
							$title = $this->translate->match("DocumentTitle", GetDirVar(0, "index"), $this->translate->_("DocumentTitleDefault"));
						} else {
							$title = $this->translate->_($this->option(DOCUMENTTITLE));
						}
						// create tag
						$header .= Tag("title", false, $title);

						
						// add header option
						$hdropt = $this->option(HEADER);
						if(is_array($hdropt)) {
							$header .= implode("\n", $hdropt);
						}

						// document class
						$bodyclass = $this->option(DOCUMENTCLASS);
						
						// add system variables
					}
					// check cnt
					if($cnt) {
						$this->output = mgHTMLCompress($this->template->GetParsed(true));
					} else {
						// build final page
						$page = sprintf("<!%s>\n<head>\n%s\n</head>\n<body%s>\n%s\n</body>\n</html>", 
							$this->personality->config("web-doctype", "DOCTYPE html"),
							$header,
							$bodyclass?sprintf("class=\"%s\"", $bodyclass):"",
							$this->template->GetParsed(true)
						);
						
						// statistics
						// mgStatisticEvent(STATS_PAGELOAD, 0, Array(DB_FIELD_USERID=>$this->user->id, DB_FIELD_SESSION=>$this->sessionid, "page"=>GetPageRequest()));
						
						// prepare for output
						$this->output = mgHTMLCompress($page);
					}
					break;
			}
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (private) __loadassets, loads assets to the framework
		private function __loadassets($paths) {
			// initialize assets
			$assets = Array(); $libassets = Array();
			// cycle paths
			foreach($paths as $path) {
				// get directory
				switch(true) {
					case is_dir($path):
						// load dir
						$d = dir($path);
						// cycle through directory
						while($file = $d->read()) {
							// create filename
							$fn = sprintf("%s%s", $path, $file);
							// check
							if(is_file($fn)) {
								$assets[] = $fn;
							}
						}
						break;
					default:
						// explode and load from library folder
						$d = explode(",", $path);
						// create assets
						foreach($d as $v) {
							// create filename
							$fn = sprintf("%s%s", LIBRARY_PATH, sprintf(LIBRARY_CONTROLLER, $v));
							// check
							if(is_file($fn)) {
								$libassets[] = $fn;
							}
						}
						break;
				}
			}
			// sort assets
			sort($assets);
			sort($libassets);
			// generate assets
			$assets = array_merge($libassets, $assets);
			// load assets
			foreach($assets as $asset) {
				require_once($asset);
			}
		}
		
		
		# ---------------------------------------------------------------------------------------------------------------
		# (private) __language, selects current language
		private function __language() {
			// get default language
			$default = $this->personality->config("default-language", defined("APPLICATION_DEFAULT_LANGUAGE")?APPLICATION_DEFAULT_LANGUAGE:LANGUAGE_DEFAULT);
			// set language
			$language = $default;
			// check user langage
			if($this->issecured()) {
				// get user language
				$language = $this->user->read(DB_FIELD_LOCALIZED);
			} else {
				// check session
				if($this->session) {
					$language = $this->session->ReadMeta(DB_FIELD_LOCALIZED, false);
				}

			}
			// store language
			$this->localized = in_array(strtolower($language), explode(",", APPLICATION_LOCALIZED_LANGUAGES))?strtolower($language):$default;
			// store language identifier in global perspective
			SetVar(FRAMEWORK_LOCALIZED, $this->localized);
			//  update personality
			$this->personality->SetLocalized($this->localized);
			// create filename
			$fn = Array(
				sprintf("%s%s", $this->personality->languagepath, sprintf(LANGUAGE_FILENAME, $this->localized)),
				sprintf("%s%s/language/%s", FRAMEWORK_PATH, LOCALIZED_PATH, sprintf(LANGUAGE_FILENAME, $this->localized)),
			);
			// create object
			$this->translate = new mgTranslate($fn);
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (public) __template, returns the template content
		public function __template($template) {
			// get template
			return $this->personality->GetTemplateContent($template);
		}		
		
		# ---------------------------------------------------------------------------------------------------------------
		# (public) __contentfilename, returns the content filename
		public function __contentfilename($content) {
			// format content filename
			$cn = sprintf(CONTENT_FILENAME, $content);
			// get paths
			if($this->ismobile()) {
				$paths = array($this->personality->localizedmobilepath, $this->personality->mobilepath);
			} else {
				$paths = array($this->personality->localizedcontentpath, $this->personality->contentpath);
			}
			// check if file exists in localized content path
			foreach($paths as $path) {
				if(file_exists($path.$cn)) {
					return mgContent($path.$cn);
				}
			}
			// try to resolve as personality content
			if($c = $this->personality->GetContent($content)) {
				// set template
				$this->option(TEMPLATE, (string)$c->template);
				// get localized source
				$content = mgLocalizedArray($c->source, $this->localized, "source");
			}
			// return content
			return $content;
		}		
		
		# ---------------------------------------------------------------------------------------------------------------
		# (private) __contentfilename, returns the content filename		
		private function __httpredirect($url, $code = 302) {	
			// need to send cookie information as well
			$this->__emitheaders();
			// redirect user
			header("location: {$url}", true, $code); 
			// silent exit
			exit; 
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (public) processemail, sends an e-mail by type and data
		public function processemail($toemail, $subject=false, $content=false, $data=false, $template=false, $fromemail=false) {
			// validate email address
			if(!ValidateEMail($toemail)) return false;
			// prepare content
			$msg = new mgTemplate($this->translate, false, $this);
			// assign template
			$msg->AssignFromText($this->__template($template?$template:defined("TEMPLATE_EMAIL")?TEMPLATE_EMAIL:"{CONTENT}"));
			// load dynamic content
			$f = sprintf("%s%s.email", $this->personality->emailpath, $content);
			if(file_exists($f)) {
				$content = file_get_contents($f);
			}
			// buffer content
			$msg->Buffer("CONTENT", $content);
			// prepare data
			$toname = is_array($toemail)?$toemail[0]:"";
			$toemail = is_array($toemail)?$toemail[1]:$toemail;
			// write data value
			$data = array_merge($data, Array(
				"toemailaddress"=>$toemail,
				"toemailname"=>$toname
			));			
			// write data
			$msg->Write($data);
			// create controller
			$e = new mgSimpleEMailController($fromemail?$fromemail:EMAIL_FROM_NOREPLY, mgFillVariableString($subject, $data));
			// prepare controller
			$e->AddRecipient($toname, $toemail);
			// send email
			return $e->Execute($msg->GetParsed(), true);
		}
	
		
		# ---------------------------------------------------------------------------------------------------------------
		# (private) __signin, authenticates the user
		private function __signin($method=USER_SIGNIN_CREDENTIALS) {
			// trigger method
			switch($method) {
				// ------------------------------------------------------------------------------------------------------
				// Method: Login credentials
				case USER_SIGNIN_CREDENTIALS:
					// check form
					if($form = $this->processform(CONTENT_SIGNIN)) {
						// run authentification
						$result = $this->user->AuthenticateCredentials(GetVar("signin_username"), GetVar("signin_password"), $this->sessionid);
						// check result
						if($result==DB_OK){
							// execute aftersignup event
							if(method_exists($this, '__aftersignin')) {
								if($result = $this->__aftersignin($this->user)) {
									exit;
								}
							}
							// redirect
							$this->__httpredirect(GetVar("redirect", HTTP_ROOT));
						} else {
							$this->errorlist = mgErrorList($this->translate->arrayindex($result, Array(
								DEFAULTVALUE=>"SignInInvalidUser",
								DB_INVALIDPASSWORD=>"SignInInvalidPassword"), Array(GetVar("signin_username"))));
						}
					}
					break;	
				
				// ------------------------------------------------------------------------------------------------------
				// Method: Token
				case USER_SIGNIN_TOKEN: 
					// check apiauth
					$isapiauth = $this->apiauth&&$this->apiauth->IsAuthorized();
					// run token authentification
					$result = $this->user->AuthenticateToken($isapiauth?GetVar(APIAUTH_TOKEN):GetDirVar(1));
					// check result
					$result = $result == DB_OK;
					// check if this is an api connection
					if($isapiauth) {
						// check authorize only, otherwise pass through
						if(GetVar(APIAUTH_AUTHORIZEONLY, false)==APIAUTH_ISAPI) {
							$this->emitraw(json_encode(Array(APIAUTH_RESULT=>$result)), mgGetMime(".json"));
						}
					} else {
						// set result
						if($result) {
							$this->__httpredirect(HTTP_ROOT);
						} else {
							$this->errorlist = mgErrorList($this->translate->arrayindex($result, Array(
								DEFAULTVALUE=>"SignInInvalidUser",
								DB_INVALIDTOKEN=>"SignInInvalidToken"), Array(GetVar("signin_username")))
							);
						}
					}
					break;
				
				// ------------------------------------------------------------------------------------------------------				
				// Method: Token	
				case USER_SIGNIN_APIAUTH:
					// get values
					$username = GetVar("signin_username");
					$password = GetVar("signin_password");
					$encoded = GetVar("encoded", false);
					// check if both are encoded
					if($encoded=="true") {
						// get secret
						$secret = GetVar(APIAUTH_SECRET);
						// decode
						$username = mgDecryptString($username, $secret);
						$password = mgDecryptString($password, $secret);
					}				
					// run authentification
					$result = $this->user->AuthenticateCredentials($username, $password);
					// verify result
					if($result==DB_OK) {
						// create token for authorization
						$token = CreateGUID();
						// set token
						$this->user->write(DB_FIELD_TOKEN, $token, true);
						// set reesult
						$json = Array(APIAUTH_RESULT=>true, APIAUTH_TOKEN=>$token);
					} else {
						// send error result
						$json = Array(APIAUTH_RESULT=>false);
					}
					// send result
					$this->emitraw(json_encode($json), mgGetMime(".json"));
					break;					
			}

		}
		# ---------------------------------------------------------------------------------------------------------------
		# (private) __signup, registers a new user
		private function __signup($method=USER_SIGNUP) {
			// trigger method
			switch($method) {
				// ------------------------------------------------------------------------------------------------------
				// Method: SignUp
				case USER_SIGNUP:
					// check form
					if($form = $this->processform(CONTENT_SIGNUP)) {
						// get username
						$username = GetVar($form->GetFieldTypeName(FIELD_TYPE_USERNAME));
						// check if user e-mail already exists
						if(mgUserNameExists($username)){
							$this->errorlist = mgErrorList($this->translate->_('SignUpUsernameExists', Array($username)));
						} else {
							// create some unqiue values
							$verify = CreateGUID();
							// create user 
							$m = new mgUserController(DB_CREATE);
							// store global fields
							$m->Write(array_merge($form->GetDatabaseValues(DB_VALUES_GLOBAL), Array(DB_FIELD_ENABLED=>ENABLED, DB_FIELD_ROLE=>ROLE_USER, DB_FIELD_VERIFY=>$verify, DB_FIELD_STATUS=>0)));
							// store meta fields
							foreach($form->GetDatabaseValues(DB_VALUES_CUSTOM) as $name=>$value) {
								$m->WriteMeta($name, $value, false);
							}
							// execute storage
							$m->Publish();
							// login user
							$m->Login($this->sessionid);
							// check if a signup e-mail should be sent
							if($this->personality->config("email-signup")) {
								// prepare email signup
								mgSendTemplatedMail($this->user->username, "signup", Array(
									"username"=>$this->user->username,
									"firstname"=>ucfirst($this->user->ReadMeta("user_firstname")),
									"lastname"=>ucfirst($this->user->ReadMeta("user_lastname")),
									"verify"=>$this->user->Read("verify")
								));		
							}
							// execute aftersignup event
							if(method_exists($this, '__aftersignup')) {
								$this->__aftersignup($m);
							}
							// redirect user
							$this->__httpredirect(GetVar("redirect", HTTP_ROOT));
						}
					}
					
					break;
					
				// ------------------------------------------------------------------------------------------------------				
				// Method: API SignUp	
				case USER_SIGNUP_APIAUTH:
					// get data
					$data = @json_decode(stripslashes(GetVar("data", Array())), true);
					// prepare result
					$result = APIAUTH_RESULT_FAILED;
					$token = false;					
					// check
					if(is_array($data)) {
						// complete data
						$r = true;
						foreach(Array("firstname", "lastname", "username", "password") as $v) {if(!isset($data[$v])){$r = false; break;}}
						// check data result
						if(!$r) {
							$result = APIAUTH_RESULT_INCOMPLETE;
						} else {
							// check username
							if(mgUserNameExists($data["username"])){
								// username already exists
								$result = APIAUTH_RESULT_USEREXISTS;
							} else {
								// create token for authorization
								$token = CreateGUID();
								// create some unqiue values
								$verify = CreateGUID();
								// create user 
								$m = new mgUserController(DB_CREATE);
								// store global fields
								$m->Write(Array(
									DB_FIELD_USERNAME=>strtolower($data["username"]),
									DB_FIELD_PASSWORD=>$data["password"],
									DB_FIELD_TOKEN=>$token,
									DB_FIELD_ENABLED=>ENABLED, 
									DB_FIELD_ROLE=>ROLE_USER, 
									DB_FIELD_VERIFY=>$verify, 
									DB_FIELD_STATUS=>0
								));
								// store meta fields
								$m->WriteMeta(Array(
									"user_firstname"=>$data["firstname"],
									"user_lastname"=>$data["lastname"]
								));
								// execute storage
								$m->Publish();
								// check if a signup e-mail should be sent
								if($this->personality->config("email-signup")) {
									// prepare email signup
									$this->processemail($m->username, $this->translate->_("EMailSubjectSignUp"), EMAIL_SIGNUP, Array(
										"username"=>$m->username,
										"firstname"=>ucfirst($m->ReadMeta("user_firstname")),
										"lastname"=>ucfirst($m->ReadMeta("user_lastname")),
										"verify"=>$verify
									));
								}
								// set result
								$result = APIAUTH_RESULT_SIGNEDUP;
							}
						}
					}
					// send result
					$json = Array(APIAUTH_RESULT=>$result, APIAUTH_TOKEN=>$token);
					// send result
					$this->emitraw(json_encode($json), mgGetMime(".json"));
					break;						
			}
		}
		
		
		# ---------------------------------------------------------------------------------------------------------------
		# (public) processform, processes a form template (signup/signin)
		public function processform($name) {	
			// initialize
			$result = false;
			// reset
			$this->errorlist = "";
			// initialize form
			$form = new mgTemplateForm($this->personality->GetForm($name, true));
			// initialize validation
			$v = new mgValidate($this->translate);
			// validate form
			if(!$result = $v->form($_REQUEST, $form->form)) {$this->errorlist = $v->ViolationErrorList();}
			// return result
			return ($result===false)?false:$form;
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (private) __processslug, processes a pre-defined slug
		private function __processslug() {
			// create sluger
			$s = new mgSlug();
			// check slug
			if($d = $s->exists(GetDirVar(0), true)) {
				$this->__httpredirect($d["destination"]);
			}
		}		
		
		# ---------------------------------------------------------------------------------------------------------------
		# (private) __initializeresources, initialize resources
		private function __initializeresources($nodb = false) {
			// default resources
			$ds = Array();
			if(AppVar("FRAMEWORK_DEFAULTRESOURCES")&&!IfAppVar("FRAMEWORK_DEFAULTRESOURCES", false)) {
				foreach(explode(",", FRAMEWORK_DEFAULTRESOURCES) as $r) {
					$ds[] = sprintf("%s/%s", FRAMEWORK_RESOURCEPATH, $r);
				}
			} else {
				$ds = Array(
					sprintf("%s/jquery", FRAMEWORK_RESOURCEPATH),
					sprintf("%s/platform", FRAMEWORK_RESOURCEPATH),
				);
			}
			// initialize resources
			$this->resources = new mgResources($this->cache,  array_merge($ds, defined("APPLICATION_RESOURCES")?Array(APPLICATION_RESOURCES):Array()), $this);
			$this->resources->nodb = $nodb;
		}
	}
	
	
	# -------------------------------------------------------------------------------------------------------------------
	# (class) mgFrameworkExtension, framework extension controller (wrapper)
	class mgFrameworkExtension {
	
		# ---------------------------------------------------------------------------------------------------------------
		# (public) stack
		public $errortitle;					// stores the last error
		public $errormessage;				// stores the error
		public $framework;					// reference to framework
		public $user;						// shortcut to user
		public $content;					// content template
		public $contentmode;				// content mode
		
		# ---------------------------------------------------------------------------------------------------------------
		# (constructor)
		public function __construct($framework) {	
			// set framework reference
			$this->framework = $framework;
			// set user reference
			$this->user = $this->framework->user;
			// initialize content
			$this->content = new mgTemplate();
			// content type
			$this->contentmode = EXTENSION_RESULT_CONTENT;
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (public) SetContent, sets the content
		public function SetContent($content){$this->content->AssignFromText($content);}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (public) LoadContent, loads a content
		public function LoadContent($content){$this->content->AssignFromText($this->framework->__contentfilename($content));}		
		
		# ---------------------------------------------------------------------------------------------------------------
		# (public) ContentMode, sets the content
		public function ContentMode($contentmode=EXTENSION_RESULT_CONTENT){$this->contentmode=$contentmode;}		
		
		# ---------------------------------------------------------------------------------------------------------------
		# (public) Error, sets the content mode to error
		public function Error($title, $message){$this->errortitle = $title; $this->errormessage=$message; $this->contentmode = EXTENSION_RESULT_ERROR;}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (public) _, shortcut to framework extension
		public function _($msg, $params = false){return $this->framework->translate->_($msg, $params);}
	
	}
	
	
	# -------------------------------------------------------------------------------------------------------------------
	# (class) mgApplicationDetector, the application detector
	class mgApplicationDetector {
	
		# ---------------------------------------------------------------------------------------------------------------
		# (local)
		private $applications = Array();
		private $application = false;
		private $adjustedpath = false;
		
		# ---------------------------------------------------------------------------------------------------------------
		# (constructor)
		public function __construct($preventdetection = false, $adjustedpath = false) {
			// adjustedpath
			$this->adjustedpath = $adjustedpath;
			// read application list
			$this->__loadapplications();
			// run detection
			if(!$preventdetection) {
				$this->__detectapplication();
			}
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (__get)
		public function __get($name) {
			// switch by name
			switch($name) {
				case "applicationpath": return $this->application->path; break;
				case "application": return $this->application; break;
				case "applications": return $this->applications; break;
			}
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (retrieve)		
		public function retrieve($name) {
			if(isset($this->applications[$name])) {
				// assign app
				$this->application = $this->applications[$name];
				// initialize application
				$this->__initialize();
				// return
				return $this->application;
			}
			return false;
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (__detectapplication)		
		private function __detectapplication() {
			// initialize result
			$result = FRAMEWORK_APPLICATION_DEFAULT;
			// get application
			foreach($this->applications as $application) {
				// run matches
				if($application->xml->matches) {
					// cycle nodes
					foreach($application->xml->matches->children() as $match) {
						// matched 
						$matched = false;
						// get content
						$content = (string)$match["content"];
						// check content
						if(strlen($content)!=0) {
							// switch by match type
							switch((string)$match["type"]) {
								// validate domain
								case "domain": 
									if($v = preg_match(sprintf("#%s#", $content), @$_SERVER["HTTP_HOST"], $matches)) {
										$matched = true;
									}
									break;
							}
							// check match
							if($matched) {
								// found match
								$result = $application->id;
								// break
								break 2;
							}
						}
					}
				}
			}
			// assign result
			if(isset($this->applications[$result])) {
				// assign app
				$this->application = $this->applications[$result];
				// initialize application
				$this->__initialize();
				
			} else {
				// Terminate Application with failed screen
				$this->__terminate();
			}
		}	
		
		
		# ---------------------------------------------------------------------------------------------------------------
		# (__loadapplication)
		private function __loadapplication($name) {
			// initialize result
			$result = false;
			// create bootxml filename
			$bx = sprintf("%s%s/%s", $this->adjustedpath?$this->adjustedpath:FRAMEWORK_APPLICATION_PATH, $name, FRAMEWORK_APPLICATION_BOOTXML);
			// check if file exists
			if(file_exists($bx)) {
				// load 
				$xml = @simplexml_load_file($bx);
				// create result
				if(@$xml["id"]) {
					// process variables
					$variables = Array();
					if($xml->variables) {
						foreach($xml->variables->children() as $v) {
							if(isset($v["name"])) {
								$variables[(string)$v["name"]] = (string)$v["value"];
							}
						}
					}
					// process connections
					$connections = Array();
					if($xml->connections) {
						foreach($xml->connections->children() as $v) {
							// create parameter list
							$params = Array();
							// check
							foreach($v->children() as $n) {
								$params[$n->getName()] = (string)$n;
							}
							// add connection
							$connections[] = (object)Array(
								"name"=>(string)@$v["name"],
								"primary"=>(string)@$v["primary"]=="true",
								"condition"=>(string)@$v["condition"],
								"parameters"=>(object)$params
							);
						}
					}

					// process result
					$result = (object)Array(
						"xml"=>$xml,
						"path"=>sprintf("%s%s", ($this->adjustedpath?$this->adjustedpath:FRAMEWORK_APPLICATION_PATH), $name),
						"icon"=>sprintf("%s%s/%s", ($this->adjustedpath?$this->adjustedpath:FRAMEWORK_APPLICATION_PATH), $name, FRAMEWORK_APPLICATION_ICON),
						"id"=>(string)@$xml["id"],
						"variables"=>$variables,
						"connections"=>$connections
					);
				}
			}
			// return result
			return $result;
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (__initialize)
		private function __initialize() {
			// initialize variables
			foreach($this->application->variables as $name=>$value) {
				define($name, $value);
			}
			// initialize connection
			foreach($this->application->connections as $name=>$params) {
				// check condition (at the moment we only support primary)
				if($params->primary) {
					SetVar(CONNECTION, $params);
					SetVar(APPLICATION, $this->application);
					break;
				}
			}
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (__loadapplications)
		private function __loadapplications() {
			// initialize
			$this->applications = Array();
			// get paths
			$paths = $this->adjustedpath?$this->adjustedpath:FRAMEWORK_APPLICATION_PATH;
			// validate
			if(is_dir($paths)) {
				// read directory
				$ap = dir($paths);
				// read application
				while($path = $ap->read()) {
					// load application
					if($app = $this->__loadapplication($path)) {
						// assign application
						$this->applications[$app->id] = $app;
					}
				}
			}
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (__terminate)
		private function __terminate() {
			DieCriticalError("Framework Application Error");
		}
	}
?>