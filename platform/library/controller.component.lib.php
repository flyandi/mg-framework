<?php
	/* 
		(mg)framework Version 5.0
		
		Copyright (c) 1999-2011 eikonlexis LLC. All rights reserved.
		
		This program is protected by copyright laws and international treaties.
		Unauthorized reproduction or distribution of this program, or any 
		portion thereof, may result in serious civil and criminal penalties.
	
		Module 		Component Controller
	*/

	# -------------------------------------------------------------------------------------------------------------------
	# Constants Declaration	
	
	// (id)
	define("COMPONENT_ID_PATH", "component");

	// (properties) 
	define("COMPONENT_CLASS", "mgComponent");
	define("COMPONENT_PATH", "../../platform/components/");
	define("COMPONENT_XML", "component.xml");
	define("COMPONENT_INCLUDE", "%s.component.php");
	
	
	# -------------------------------------------------------------------------------------------------------------------
	# (class) mgComponents, manages the components
	class mgComponents extends mgModuleManager {
		# ---------------------------------------------------------------------------------------------------------------
		# (initialize) initialize the module manager class
		public function initialize(){
			// initialize module manager
			return Array(
				MODULE_CLASS=>COMPONENT_CLASS,
				MODULE_PATH=>COMPONENT_PATH,
				MODULE_XML=>COMPONENT_XML
			);
		}
	}
	
	# -------------------------------------------------------------------------------------------------------------------
	# (class) mgComponent, manages a single component
	class mgComponent extends mgModule {
		
		# ---------------------------------------------------------------------------------------------------------------
		# (__initialize) called when the module is initialized
		public function __initialize() {
			// initialize module side script
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (includecomponents) includes, includes all scripts from the component
		public function includes() {
			// get all includes
			foreach($this->xml->includes as $node) {
				// create include filename
				$fn = sprintf("%s%s/%s", COMPONENT_PATH, $this->id, sprintf(COMPONENT_INCLUDE, (string)$node["name"]));
				// validate filename
				if(file_exists($fn)) {
					try {
						// load library
						require_once($fn);
						// done
					} catch(Exception $e) {
					
					}
				}
			}
			// return 
			return true;
		}		
	}
?>