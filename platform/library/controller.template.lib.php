<?php
	/* 
		(mg)framework Version 5.0
		
		Copyright (c) 1999-2011 eikonlexis LLC. All rights reserved.
		
		This program is protected by copyright laws and international treaties.
		Unauthorized reproduction or distribution of this program, or any 
		portion thereof, may result in serious civil and criminal penalties.
	
		Module 		Template Controller and Associated Controllers
	*/
	
	# -------------------------------------------------------------------------------------------------------------------
	# (class) mgTemplateForm, class for templated forms as MSON strings
	class mgTemplateForm {
	
		# ---------------------------------------------------------------------------------------------------------------
		# (public) stack
		public $form;
		
		# ---------------------------------------------------------------------------------------------------------------
		# (constructor), expects an MSON string
		public function __construct($mson){
			// initialize
			$this->form = mgMSONDecode($mson);	// decode string
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (public) GetFieldTypeName, returns the name of the requested type
		public function GetFieldTypeName($request){
			// cycle
			foreach($this->form as $name=>$params){if(mgCompare($params[0], $request)) {return $name;}}	
			// return nothing
			return false;
		}
		
		
		# ---------------------------------------------------------------------------------------------------------------
		# (public) GetDatabaseValues, returns the assiocated db values
		public function GetDatabaseValues($request){
			// global namespace
			$namespace = Array("username"=>DB_FIELD_USERNAME, "password"=>DB_FIELD_PASSWORD, "role"=>DB_FIELD_ROLE);
			// initialize
			$result = Array();
			// cycle
			foreach($this->form as $name=>$params){
				// check for custom field
				if($request==DB_VALUES_CUSTOM&&$params[0]==DB_VALUES_CUSTOM) {
					$result[$name]=GetVar($name);
				} else if($request==DB_VALUES_GLOBAL) {
					if(array_key_exists($params[0], $namespace)) {
						$result[$namespace[$params[0]]]=GetVar($name);
					}
				}
			}
			// return result
			return $result;
		}
		
		
		# ---------------------------------------------------------------------------------------------------------------
		# (public) html, returns the HTML for the form, expects an template class 
		public function html($template){
			// initialize
			$result = Tag("input", Array("type"=>"hidden", "name"=>FORM_POST, "value"=>FORM_ISPOST), false, true);
			// cycle
			foreach($this->form as $name=>$field) {
				// initialize
				$input = ""; $label = ""; $l = true;
				switch(@$field[1]) {	
					// hint
					case "hint": $l = false; $input = Tag("div", Array("class"=>"hint"), $template->translate->_(@$field[2])); break;
					// string
					case "string": $input = Tag("input", Array("type"=>@$field[1], "readonly"=>TAG_NOVALUE, "name"=>$name, "value"=>$template->Read($name)), "", true); break;
					// textarea
					case "multi": $label = "textarea"; $input = Tag("textarea", Array("name"=>$name), $template->Read($name)); break;
					// enable 0/1
					case "enable": $input = TagSelect(Array("name"=>$name), Array(0=>$template->translate->_("SystemTextDisabled"), 1=>$template->translate->_("SystemTextEnabled")), $template->Read($name)); break;
					// default input
					default: $input = Tag("input", Array("type"=>@$field[1], "name"=>$name, "value"=>$template->Read($name)), "", true); break;
				}
				// assign
				if($l) {
					$result .= Tag("div", Array("class"=>"input {$label}".(is_string(@$field[4])&&strlen(@$field[4])!=0?$field[4]:"")), Tag("span", Array("class"=>"label"), $template->translate->_(@$field[2])).$input);
				} else {
					$result .= $input;
				}
			}
			// return
			return $result;
		}
	}
	
	
	# -------------------------------------------------------------------------------------------------------------------
	# (class) mgTemplateSnippet, wrapper class for snippets
	class mgTemplateSnippet {
		# private
		private $localized = false;
	
		# ---------------------------------------------------------------------------------------------------------------
		# (constructor), expects an MSON string
		public function __construct($params, $localized = false){
			// parse params
			$this->areas = explode(TEMPLATE_FIELD_DEFAULTSEPERATOR, $params);
			// set localized
			$this->localized = $localized;
		}

		# ---------------------------------------------------------------------------------------------------------------
		# (public) html, returns the HTML for the form, expects an template class 
		public function html($template=false){
			// initialize result
			$result = "";
			// check reference
			if($template->framework&&is_array($this->areas)) {
				// initialize
				$result = new mgTemplate($template->translate, $template->callback, $template->framework);
				// determinate source and area
				$source = false;
				$f = isset($this->areas[1])&&$template->framework->issecured()?$this->areas[1]:$this->areas[0];
				// check mobile
				if($template->framework->ismobile()) {
					// get source
					$source = $template->framework->personality->getsnippet($f.".mobile", $this->localized);
				}
				if($source == false) {
					$source = $template->framework->personality->getsnippet($f, $this->localized);
				}
				// load snippet from personality service
				$result->AssignFromSource($source);
				// write data
				$result->Write($template->Values());
				// return result
				$result = $result->GetParsed();
			}
			// return result
			return $result;
		}
	}	

	
	# -------------------------------------------------------------------------------------------------------------------
	# (class) mgTemplate, base class for templates	
 	class mgTemplate {

		# ---------------------------------------------------------------------------------------------------------------
		# (private) stack
		private $values;				// values stack
		private $buffer;				// Buffer
		private $prebuffer;				// PreBuffer
		private $forms;					// Forms

		# ---------------------------------------------------------------------------------------------------------------
		# (public) stack		
		public $translate;				// translation reference
		public $framework;				// framework reference
		public $callback;				// action callback
		public $debug = false;			// debug
		
		# ---------------------------------------------------------------------------------------------------------------
		# (constructor)
		public function __construct($translate=false, $actioncallback=false, $framework = false){
			// assign translate reference
			$this->translate = $translate;
			// assign framework reference
			$this->framework = $framework;
			// set callback
			$this->callback = $actioncallback;
			// initialize
			$this->Clear();
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (properties) __get
		public function __get($name){
			switch($name){
				case "values": return $this->values; break;
			}
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (public) AssignFromFile, loads an template from a file
		public function AssignFromFile($filename) {if (!file_exists($filename)){return false;} $this->buffer = file_get_contents($filename);return true;}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (public) AssignFromText, loads an template from a string
		public function AssignFromText($data){$this->buffer = $data; return true;}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (public) AssignFromSource, automatic
		public function	AssignFromSource($data) {
			if(strlen($data)<1024&&is_file($data)) {
				$this->AssignFromFile($data);
			} else {
				$this->AssignFromText($data);
			}
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (public) Callback, sets the callback
		public function Callback($callback=false){$this->callback=$callback;}		
		
		# ---------------------------------------------------------------------------------------------------------------
		# (public) Write, writes an variable to the stack
		public function Write($name,$value=false){if(is_array($name)){$this->MergeArray($name);}else{$this->values[$name]=$value;}}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (public) Read, reads an variable from the stack (rarely used)
		public function Read($name){return @$this->values[$name];}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (public) Values, retuns all values
		public function Values(){return $this->values;}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (public) MergeArray, merges an array to the stack
		public function MergeArray($data){foreach($data as $name=>$value){$this->Write($name, $value);}}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (public) AddForm, adds a form to the template (must be a MSON string)
		public function AddForm($name, $form){$this->forms[$name]=$form;}

		# ---------------------------------------------------------------------------------------------------------------
		# (public) GetForm, retuns the form MSON string
		public function GetForm($name){return @$this->forms[$name];}

		# ---------------------------------------------------------------------------------------------------------------
		# (public) Exists, checks if a name is in the stack
		public function Exists($name){return array_key_exists($name, $this->values);}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (public) Clear, clears the stack
		public function Clear(){$this->values=Array();$this->prebuffer=Array();$this->forms=Array();}
		
		// SetFieldClass
		public function SetFieldClass($param0,$param1){$this->Write("fieldclass=$param0", $param1);}		
		
		// Replace
		public function Replace($needle, $buffer){$this->buffer=str_ireplace($needle, $buffer, $this->buffer);}		

		// Apply, straight apply
		public function Apply($param0, $param1) {$this->buffer = str_replace(sprintf("{%s}", $param0), $param1, $this->buffer);}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (public)  Buffer, adds to prebuffer
		public function Buffer($param0, $param1) {$this->prebuffer[$param0] = $param1;}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (public) GetParsed, parses the template with all fields 
		public function GetParsed($clearfields=true) {return $this->__build($clearfields);}

	
		# ---------------------------------------------------------------------------------------------------------------
		# (private) __build, builds the template with different options
		private function __build($clearfields=false) {
			return $this->__processbuffer($this->__processprebuffer($this->buffer), $clearfields);
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (private) __processbuffer, processes a string buffer
		private function __processprebuffer($buffer) {
			// cycle pre buffer
			foreach($this->prebuffer as $field=>$stack) {
				$buffer = $this->__fillfield($field, $stack, $buffer);
			}
			// return
			return $buffer;
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (private) __processbuffer, processes a string buffer
		private function __processbuffer($buffer, $clearfields=false) {
			// find all fields in buffer
			$fields = $this->__getfields($buffer, true);
			// cycle fields
			foreach($fields as $field) {$buffer = $this->__processfield($field, $buffer, $clearfields);}
			// return buffer
			return $buffer;		
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (private) __processfield, processes a field
		private function __processfield($field, $buffer, $clearfield=false) {
			// initialize
			$value = ""; $param = $field; $f = false;
			if(is_string($field)&&strlen($field)!=0){
				$f = $field{0};
				$param = substr($field, 1);
			}
			// switch field type
			switch($f){
				// option field
				case TEMPLATE_FIELD_OPTION:
					// parse default value
					$temp = explode(TEMPLATE_FIELD_DEFAULTSEPERATOR, $field);
					// get option
					$option = (object)@mgGetOption(substr(@$temp[0], 1), false, true);
					// check secondary option
					switch(@$temp[1]) {
						case "select":
							if(is_array(@$option->value)) {
								// create select 
								foreach($option->value as $key=>$v) {
									$value .= Tag("option", Array("value"=>$key), $v);
								}
							}
							break;
					}
					break;
				// system field
				case TEMPLATE_FIELD_SYSTEM: 
					switch(strtolower(substr($field, 1))) {
						case "projectversion": $value = PROJECT_VERSION; break;
						case "localized": $value = $this->framework?LOCALIZED_PATH.$this->framework->localized:""; break;
						case "username": $value = $this->framework->user->username; break;
						case "userid": $value = $this->framework->user->userid; break;
						case "year": case "copyrightyear": $value = date("Y"); break;
					}
					break;
				// callback
				case TEMPLATE_FIELD_CALLBACK: $value = ($this->callback!==false)?@call_user_func($this->callback, $param):""; break;
				// list
				case TEMPLATE_FIELD_LIST: $value = $this->__fieldlist($param); break;
				// action field
				case TEMPLATE_FIELD_ACTION: $value = $this->__fieldaction($param); break;
				// translation field
				case TEMPLATE_FIELD_TRANSLATE: $value = $this->__processbuffer($this->__translatevalue(trim(str_replace(TEMPLATE_FIELD_TRANSLATE, "", $field)))); break;
				// (default) normal field
				default:
					// parse default value
					$temp = explode(TEMPLATE_FIELD_DEFAULTSEPERATOR, $field);
					// get default value
					switch(count($temp)) {
						case 2: $default = $temp[1]; $fieldname = $temp[0]; break;
						default: $default = false; $fieldname = $temp[0]; break;
					}
					// get value for field
					$value = $this->Read($fieldname);
					// check default
					$value = (($value===null||$value===false)&&$default!==false)?$default:$value;
			}
			// validate value
			$value = ($value===null||$value===false)?"":$value;
			// fill field
			if($value!=""||($value==""&&$clearfield)){
				$buffer = $this->__fillfield($field, $value, $buffer);
			}
			// return
			return $buffer;
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (private) __fieldaction, processes a field action
		private function __fieldaction($field) {
			// initialize
			$result = null;
			// parse field action
			$params = explode(TEMPLATE_FIELD_ACTIONSEPERATOR, $field);
			// get field action
			switch($params[0]) {
				// (cache) includes a file from the cache
				case "cache": 
					// create cache item
					$result = mgCacheReadStored($params[1]);
					break;
			
				// (form) creates an form based on SimpleXMLElement
				case "form": 
					// create form
					$form = new mgTemplateForm($this->GetForm($params[1]));
					// build form
					$result = $form->html($this);
					break;
					
				// (snippet) includes a snippet
				case "snippet":
					// create snippet
					$snippet = new mgTemplateSnippet($params[1]);
					// build snippet
					$result = $snippet->html($this);
					break;
				
				// (requestsnippet) includes a snippet based on a request
				case "requestsnippet":
					if(in_array(strtolower(GetDirVar(0)), explode(",", @$params[1]))) {
						// create snippet
						$snippet = new mgTemplateSnippet(@$params[2]);
						// build snippet
						$result = $snippet->html($this);
					}					
					break;
					
				// (localizedsnippet) includes a localizes snippet
				case "localizedsnippet":
					// create snippet
					$snippet = new mgTemplateSnippet($params[1], true);
					// build snippet
					$result = $snippet->html($this);
					break;
			}
			// result
			return $result;
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (private) __fieldlist, processes a field list
		private function __fieldlist($field) {
			//#<li href="/%name">%1</li>,settings:(%DashboardMenuSettings);logout:(%DashboardMenuLogout)}
			// initialize
			$result = "";
			// parse string
			$params = explode(",", $field, 3);
			// create stacks
			$html = @$params[0];
			$options = explode(";", @$params[1]);
			$data = mgMSONDecode(@$params[2]);
			// length
			$last= count($data)-1; $count=0;
			// cycle
			foreach($data as $key=>$values) {
				// translate values
				$values = $this->__translatevalue($values);
				// validate option divider
				$divider = (in_array("divider", $options)&&$count<$last)?"divider":"";
				// create list item
				$result .= mgFormatString($html, array_merge(Array("name"=>$key, "divider"=>$divider), $values)); 
				// count
				$count += 1;
			}
			// return result
			return $result;
		}	

		# ---------------------------------------------------------------------------------------------------------------
		# (private) __fillfield, fills a field
		private function __fillfield($field, $value, $buffer) {
			return str_replace(sprintf("%s%s%s", TEMPLATE_FIELD_BEGIN, $field, TEMPLATE_FIELD_END), $value, $buffer);
		}

		
		# ---------------------------------------------------------------------------------------------------------------
		# (private) __translatevalue, translates an value
		private function __translatevalue($value) {
			// sanity check, if translate object is available
			if(!$this->translate) return $value;
			// get object
			return $this->translate->_($value);
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (private) __getfields, retrieve all fields from a string
		private function __getfields($buffer, $clearscripts=true) {
			// clear javascript/css/anything else before processing buffer
			if($clearscripts) {
				// define tags
				$tags = Array("script", "style");
				// remove tags
				foreach($tags as $tag) { $buffer = preg_replace(sprintf("#<%s[^>]*>.*?</%s>#is", $tag, $tag), "", $buffer); }
			}
			// run regular expression
			preg_match_all(sprintf("/%s(.*?)%s/", TEMPLATE_FIELD_BEGIN, TEMPLATE_FIELD_END), $buffer, $matches, PREG_PATTERN_ORDER);
			// return result
			return $matches[1];
		}

		// --------------------------------------------------------------------------
		// SaveTo
		public function SaveTo($filename, $r=true) {
			$buffer = $this->GetParsed($r);
			$fh = fopen($filename,'w');
			if($fh){
				@fwrite($fh, $buffer);
				@fclose($fh);
			}
		}
 	}
	
	
?>