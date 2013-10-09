<?php
	/* 
		(mg)framework Version 5.0
		
		Copyright (c) 1999-2011 eikonlexis LLC. All rights reserved.
		
		This program is protected by copyright laws and international treaties.
		Unauthorized reproduction or distribution of this program, or any 
		portion thereof, may result in serious civil and criminal penalties.
	
		Module 		Adopter Class
	*/
	
	# -------------------------------------------------------------------------------------------------------------------
	# (class) mgAdopterClass, makes a adoption of a class possible during runtime
	class mgAdopterClass {
		# ---------------------------------------------------------------------------------------------------------------
		# (private) stack
		private $adopted=false;
	
		# ---------------------------------------------------------------------------------------------------------------
		# (public) constructor
		public function __construct($class) {
			$this->adopted = $class;
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (__get)
		public function __get($name) {
			return $this->adopted->{$name};
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (__set)
		public function __set($name, $value) {
			$this->adopted->{$name} = $value;
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (__call)
		public function __call($name, $args) {
			return call_user_func_array(Array($this->adopted, $name), $args);
		}
	}
?>