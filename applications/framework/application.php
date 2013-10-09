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
	# (class) mgApplication, main app controller
	class mgApplication extends mgFrameworkApplication {
		
		# ---------------------------------------------------------------------------------------------------------------
		# (public) __execute, executes the project (called from framework controller)
		public function __execute($__contentarea, $__request, $__secured=false) {
		
			# -----------------------------------------------------------------------------------------------------------
			# Initialize Template
			$this->option(TEMPLATE, TEMPLATE_INDEX);
			
			# -----------------------------------------------------------------------------------------------------------
			# Content Areas
			switch($__contentarea) {			

				# -------------------------------------------------------------------------------------------------------
				# Content Unsecured
				case CONTENT_UNSECURED:
					// switch by request
					$this->option(CONTENT, CONTENT_SIGNIN);
					break;

				# -------------------------------------------------------------------------------------------------------
				# Content Secured					
				case CONTENT_SECURED:
					$this->resources->register("interface");
					// initialize manager view
					$manager = new mgManager(MANAGER_ID_PATH);
					// initialize service manager
					$manager->setframework($this);
					// get modules
					$this->resources->asjs(mgScriptVar("bnManagerModules", $manager->getdisplays(MANAGER_DISPLAY_INTERFACE)));
					break;
			}
		}
		
		
		# ---------------------------------------------------------------------------------------------------------------
		# (public) __executeaction, executes the action handler
		public function __executeaction($__request, $__ispost) {
			return false;	// not used
		}
	}
?>