<?php
	/*
		(mg) Framework Validation

		Copyright (c) 1999-2010 eikonlexis LLC. All rights reserved.
		
		This program is protected by copyright laws and international treaties.
		Unauthorized reproduction or distribution of this program, or any 
		portion thereof, may result in serious civil and criminal penalties.
	
		Module 		Validation Controller
		Version		4.0.0 Generation BN-2010
	*/
	
	
	# -------------------------------------------------------------------------------------------------------------------
	# Constants
	define("FIELD_REQUIRED", "required");           // not empty
    define("FIELD_NUMERIC", "numeric");             // only numeric
    define("FIELD_MUSTLENGTH", "mustlengh");        // must field length (array)
	define("FIELD_EMAIL", "email");					// E-Mail
	define("FIELD_MATCH", "match");					// String Match
	define("FIELD_BOOL", "bool");					// Bool Match
		
	
	# -------------------------------------------------------------------------------------------------------------------
	# (class) mgValidate, utilizes different validations
	class mgValidate {
		#----------------------------------------------------------------------------------------------------------------
        # (local) stack
		private $violations;
		private $translate;		// Translate reference
		
		#----------------------------------------------------------------------------------------------------------------
        # (constructor)
		public function __construct($translate=false) {
			// initialize
			$this->Clear();
			$this->translate = $translate;
		}
		
		#----------------------------------------------------------------------------------------------------------------
        # (public) Clear, clears the local stack
		public function Clear(){$this->violations=Array();}
		
		#----------------------------------------------------------------------------------------------------------------
        # (public) Count, returns the number of violations
		public function Count(){return count($this->violations);}	

		#----------------------------------------------------------------------------------------------------------------
        # (public) ViolationErrorList, formats a errorlist based on the violations
		public function ViolationErrorList() {
			// sanity check
			if($this->translate===false) return;
			// initialize
			$result = "";
			// cycle
			foreach($this->violations as $violation=>$params) {
				$result .= mgErrorList($this->translate->_(($params[2]==null)?sprintf("ValidateViolation%s", ucfirst($params[0])):$params[2], $this->translate->_($params[1])));
			}
			// return
			return $result;
		}
	
		#----------------------------------------------------------------------------------------------------------------
        # (public) form, validates a MSON form
		public function form($values, $form) {
			// run form
			foreach(@$form as $field=>$params) {
				// cycle rules
				if(is_array(@$params[3])) {
					foreach(@$params[3] as $__rule) {
						// decode rule
						$rule = explode("%", $__rule);	// decode language, 0=rule, 1=language
						$ruleparams = explode("=", $rule[0]); // rule parameters, 0=rule, 1=parameters
						$rule[0] = (count($ruleparams)==2)?$ruleparams[0]:$rule[0]; // assign the correct rule name
						$ruleparams = (count($ruleparams)==2)?((array_key_exists($ruleparams[1], $values))?$values[$ruleparams[1]]:$ruleparams[1]):"";
						// test against rule
						if(!$this->__rule(@$values[$field], $rule[0], $ruleparams)) {
							// add violation
							$this->__addviolation($field, $rule[0], Array($params[2]), @$rule[1]);
							// only count one
							break 1;
						} 
					}
				}
			}
			// return result
			return ($this->Count()==0);
		}		
		
		#----------------------------------------------------------------------------------------------------------------
        # (private) __addviolation, adds a violation to the stack
		private function __addviolation($field, $violatedrule, $params, $definedtext=null) {$this->violations[$field]=Array($violatedrule, $params, $definedtext);}
		
		#----------------------------------------------------------------------------------------------------------------
        # (private) __rule, validates a value based on a rule, returns true on success and false on failed
		private function __rule($value, $rule=null, $params=null) {
			// switch rule
			switch($rule){
				// (required)
                case FIELD_REQUIRED: return (!empty($value)); break;
				// (numeric) 
                case FIELD_NUMERIC: return (is_numeric($value)); break;
				// (mustlength)
                case FIELD_MUSTLENGTH: return (strlen($value)==$params); break;
				// (email)
				case FIELD_EMAIL: return filter_var($value, FILTER_VALIDATE_EMAIL); break;
				// (match)
				case FIELD_MATCH: return (trim($value)==trim($params)); break;
				// (bool) 1/0
				case FIELD_BOOL: return (intval($value)==1); break;
				// (default) no rule specified
				default: return true;	
            }
		}
	}
    
