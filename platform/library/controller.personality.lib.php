<?php
	/*
		(mg) Framework Personality Management

		Copyright (c) 2010 eikonlexis LLC. All rights reserved.
		
		This program is protected by copyright laws and international treaties.
		Unauthorized reproduction or distribution of this program, or any 
		portion thereof, may result in serious civil and criminal penalties.
	
	*/
	# -------------------------------------------------------------------------------------------------------------------
	# Constants
	define("PERSONALITY_DIRECTORY", "personality/");			// working directory
	define("PERSONALITY_RESOURCES", "resources/");				// resources
	define("PERSONALITY_PROFILE", "profile.xml");				// Profile XML
	define("PERSONALITY_DEFAULT", "default");					// Default Personality
	define("PERSONALITY_CONFIGURATION", "configuration");		// Configuration
	define("PERSONALITY_TEMPLATES", "templates");				// Templates
	define("PERSONALITY_REQUESTS", "requests");					// Requests
	define("PERSONALITY_CONTENTS", "contents");					// Contents
	define("PERSONALITY_SNIPPETS", "snippets");					// Snippets
	define("PERSONALITY_EMAILS", "emails");						// E-Mails
	
	define("PERSONALITY_TEMPLATEDEFAULT", 0);					
	define("PERSONALITY_TEMPLATEMOBILE", 1);

	# -------------------------------------------------------------------------------------------------------------------
	# (class) mgPersonality, manages a personality
	class mgPersonality  {
		
		# ---------------------------------------------------------------------------------------------------------------
		# Local Variables
		private $personality=false;		// stores the name of the personality
		private $localized;				// variable for localized

		// (public)
		public $db;						// database storage of personality
		
		# ---------------------------------------------------------------------------------------------------------------
		# (constructor)
		public function __construct($personality=false) {
			$this->LoadPersonality($personality);
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (properties) different properties
		public function __get($name) {
			switch($name) {
				// Template Path
				case "templatepath": return sprintf("%s%s/%s", PERSONALITY_DIRECTORY, $this->personality, TEMPLATE_PATH); break;
				// Content Path
				case "contentpath": return sprintf("%s%s/%s%s/%s", PERSONALITY_DIRECTORY, $this->personality, LOCALIZED_PATH, PERSONALITY_DEFAULT, CONTENT_PATH);	break;
				// Localized Path
				case "localizedcontentpath": return sprintf("%s%s/%s%s/%s", PERSONALITY_DIRECTORY, $this->personality, LOCALIZED_PATH, $this->localized, CONTENT_PATH);	break;				
				// Mobile Path
				case "mobilepath": return sprintf("%s%s/%s%s/%s", PERSONALITY_DIRECTORY, $this->personality, LOCALIZED_PATH, PERSONALITY_DEFAULT, MOBILE_PATH);	break;				
				// Localized Path
				case "localizedmobilepath": return sprintf("%s%s/%s%s/%s", PERSONALITY_DIRECTORY, $this->personality, LOCALIZED_PATH, $this->localized, MOBILE_PATH);	break;								
				// Snippet path
				case "snippetpath": return sprintf("%s%s/%s%s/%s", PERSONALITY_DIRECTORY, $this->personality, LOCALIZED_PATH, PERSONALITY_DEFAULT, SNIPPET_PATH);	break;
				// Localized Snippet Path
				case "localizedsnippetpath": return sprintf("%s%s/%s%s/%s", PERSONALITY_DIRECTORY, $this->personality, LOCALIZED_PATH, $this->localized, SNIPPET_PATH);	break;
				// Email path
				case "emailpath": return sprintf("%s%s/%s%s/%s", PERSONALITY_DIRECTORY, $this->personality, LOCALIZED_PATH, $this->localized, EMAIL_PATH); break;				
				// Web Path
				case "webpath": return sprintf("%s%s/", PERSONALITY_WEB, $this->personality); break;
				// Language Path
				case "languagepath": return sprintf("%s%s/%s%s/%s", PERSONALITY_DIRECTORY, $this->personality, LOCALIZED_PATH, $this->localized, LANGUAGE_PATH); break;
				// Profile Configuration
				case "configuration": return $this->profile->configuration; break;
				// Resource Path
				case "resourcepath": return sprintf("%s%s/%s", PERSONALITY_DIRECTORY, $this->personality, PERSONALITY_RESOURCES); break;
				// Profile Web
				case "web": return $this->profile->web; break;
				
				// ------------------------------------------------------------------------------
				// (contents) returns all contents
				case "contents": return $this->__listmeta(PERSONALITY_CONTENTS); break;
				// (snippets) returns all snippets
				case "snippets": return $this->__listmeta(PERSONALITY_SNIPPETS); break;
				// (emails) returns all emails
				case "emails": return $this->__listmeta(PERSONALITY_EMAILS); break;
				// (requests) returns all requests
				case "requests": return $this->__listmeta(PERSONALITY_REQUESTS); break;
				// (templates) returns all templates
				case "templates": return $this->__listmeta(PERSONALITY_TEMPLATES); break;
				// ------------------------------------------------------------------------------					
				// (forms) returns all templates that are forms
				case "forms":
					$result = Array();
					foreach(@$this->templates as $form) {
						if(@$form->type==TEMPLATE_TYPE_FORM) {
							$result[] = $form;
						}
					}
					return $result;
					break;
					
				
				// default
				default: return null; break;
			}
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (public) LoadPersonality, loads the personality 
		public function LoadPersonality($personality=false) {
			// get personality based on domain name
			if($personality===false) {$personality = $this->__matchpersonality();}
			// load personality based on name
			$this->db = new mgDatabaseObject(DB_TABLE_PERSONALITY, DB_SELECTSQL, ($personality!==false)?sprintf("%s='%s'", DB_FIELD_IDSTRING, $personality):sprintf("domain='%s'", PERSONALITY_DEFAULT));
			// check results
			if($this->db->result == DB_OK) {
				// set personality
				$personality = $this->db->Read(DB_FIELD_IDSTRING);
				// set personality
				$this->personality = $personality;	
				// initialize personality
				$this->__initialize();
			} else {
				// set personality to false 
				$this->personality = false;
			}
			// return result
			return ($this->personality!==false)?true:false;
		}

		# ---------------------------------------------------------------------------------------------------------------
		# (public) GetContent, returns the content
		public function GetContent($name, $localized = true, $source = false) {
			foreach($this->contents as $content) {
				if(mgCompare($content->name, $name)) {
					if($source) {
						return $localized?$content->source[$this->localized]["source"]:$content->source["default"]["source"];
					}
					return $content;
				}
			}
			return false;
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (public) GetContent, returns the content
		public function GetEMail($name) {
			foreach($this->emails as $email) {
				if(mgCompare($email->name, $name)) {
					return $email;
				}
			}
			return false;
		}
		

		# ---------------------------------------------------------------------------------------------------------------
		# (public) GetSnippet, returns the snippet
		public function GetSnippet($name, $localized = true) {
			foreach($this->snippets as $snippet) {
				if(mgCompare($snippet->name, $name)) {
					return mgLocalizedArray($snippet->source, $localized, "source");
				}
			}
			return false;
		}		
		
		# ---------------------------------------------------------------------------------------------------------------
		# (public) GetTemplateContent, returns the template content
		public function GetTemplateContent($name) {		
			foreach($this->templates as $template) {
				if(mgCompare($template->name, $name)) {
					switch($template->type) {
						// (file)
						case TEMPLATE_TYPE_FILE:
							// create filename
							$fn = sprintf("%s%s", $this->templatepath, sprintf(TEMPLATE_FILENAME, @$template->location));
							// load file
							if(file_exists($fn)) {
								return file_get_contents($fn);
							}
							break;
						// (page) 
						case TEMPLATE_TYPE_PAGE:
						default:
							// check multi template
							if(is_array($template->source)) {
								// check mobile
								if(mgIsMobileBrowser() && isset($template->source[PERSONALITY_TEMPLATEMOBILE]["source"])) {
									// get content
									$content = $template->source[PERSONALITY_TEMPLATEMOBILE]["source"];
									// validate
									if(strlen($content)!=0) return $content;
								}
								// check default
								return isset($template->source[PERSONALITY_TEMPLATEDEFAULT]["source"])?$template->source[PERSONALITY_TEMPLATEDEFAULT]["source"]:$name;
							}
							// return standard
							return @$template->source; // just return 
							break;
					}
				}
			}
			return $name;
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (public) GetForm
		public function GetForm($name, $justsource = false) {
			foreach($this->forms as $form) {
				if(mgCompare($form->name, $name)) {
					return $justsource?$form->source:$form;
				}
			}
			return false;
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (public) MatchRequest
		public function MatchRequest($name, $condition = true, $localized = false) {
			// lowercase name
			$name = strtolower($name);
			// run request
			foreach($this->requests as $request) {
				// split request
				$r = GetDirVar(0, false, strtolower($request->request));
				// match condition
				if($condition === true || $request->condition == CONTENT_ANY || $condition == $request->condition) {
					// create match list
					$list = Array($r);
					// add localizations
					if($localized!==false&&is_array($request->localrequests)&&isset($request->localrequests[$localized])) {
						// get localized 
						$lr = $request->localrequests[$localized];
						// test localized
						if($lr&&strlen($lr)!=0) {
							// format lr
							$lr = GetDirVar(0, false, strtolower($lr));
							// check lr
							if(strlen($lr)!=0) {
								$list[] = $lr;
							}
						}
					}
					// match request
					if(in_array($name, $list)) {
						// found match
						return $request;
					}
				}
			}
			return false;
		}
	
		# ---------------------------------------------------------------------------------------------------------------
		# (public) SetLocalized, updates the local variable
		public function SetLocalized($localized) {
			$this->localized = $localized;
		}
		
		
		# ---------------------------------------------------------------------------------------------------------------
		# (public) config, sets or returns the configuration key
		public function config($name, $default=null) {
			// get result
			$result = GetVarEx($name, $this->db->ReadFieldValue(DB_FIELD_META, PERSONALITY_CONFIGURATION), $default);
			// parse result
			if(is_numeric($result)) {
				return intval($result);
			} else {
				switch($result) {
					case "true": return true;
					case "false": return false;
					default: return $result;
				}
			}
		}	
		
		# ---------------------------------------------------------------------------------------------------------------
		# (public) ConfigRelated, returns all keys
		public function ConfigRelated($related) {
			// set or get
			return $this->profile->configuration->config->GetAttributeAll("rel", $related);
		}		
		
		# ---------------------------------------------------------------------------------------------------------------
		# (private) initialize
		public function __initialize() {
			// initialize templates and create references
			foreach($this->templates as $template) {
				if(isset($template->reference)&&strlen($template->reference)!=0&&!defined($template->reference)) {
					define(strtoupper($template->reference), $template->name);
				}
			}
		}		
		
		# ---------------------------------------------------------------------------------------------------------------
		# (private) __matchpersonality, matches the personality based on hostname
		private function __matchpersonality() {
			// create databse filter search
			$r = new mgDatabaseStream(DB_TABLE_PERSONALITY, DB_SELECTSQL, sprintf("domain='%s' OR domain='%s'", @$_SERVER["HTTP_HOST"], @$_SERVER["SERVER_NAME"]));
			// check results
			if($r->result==DB_OK) { return $r->Read(DB_FIELD_IDSTRING);}
			// return 
			return false;
		}	
		
		# ---------------------------------------------------------------------------------------------------------------
		# (private) __listmeta, list the metas
		private function __listmeta($d, $asobject = true) {
			// initialize result
			$result = Array();
			// get data
			$arr = $this->db->ReadFieldValue(DB_FIELD_META, $d);
			// verify
			if(!is_array($arr)) $arr = Array();
			// cycle
			foreach($arr as $content) {
				$result[] = $asobject?(object)$content:$content;
			}
			return $result;
		}
		
	}
	
	# -------------------------------------------------------------------------------------------------------------------
	# (function) mgGetPersonalities, returns all personalities
	function mgGetPersonalities() {
		$r = new mgDatabaseStream(DB_TABLE_PERSONALITY, DB_SELECTALL);
		return $r->getall();
	}
	
