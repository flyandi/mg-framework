<?php
	/* 
		(mg)framework Version 5.0
		
		Copyright (c) 1999-2012 eikonlexis LLC. All rights reserved.
		
		This program is protected by copyright laws and international treaties.
		Unauthorized reproduction or distribution of this program, or any 
		portion thereof, may result in serious civil and criminal penalties.
	
		Module 	Manager Module System
	*/
	
	# -------------------------------------------------------------------------------------------------------------------
	# constants
	
	# -------------------------------------------------------------------------------------------------------------------
	# (class) bnServiceModule
	class mgManagerModuleSystem extends mgManagerExtension {
		
		# ---------------------------------------------------------------------------------------------------------------
		# (public) process
		public function process($request) {
			// initialize result
			$result = true;
			// use manager controls
			$this->manager->framework->usecomponent(COMPONENT_MANAGER);			
			// switch by request
			switch($request) {
				# ----------------------------------------------------------------------
				# Default Functions
			
				// (getgrid), returns the grid information from the xml configuration
				case "getgrid":
					// get grid
					$result = mgManagerGridColumns($this->xml->grids, GetVar("name", MANAGER_GRID_DEFAULT), $this);
					break;
				
				# ----------------------------------------------------------------------
				# Registry Functions
				
				// (registrygetvalue)
				case "registrygetvalue":
					// get item
					if($item = mgGetOption(GetVar("id"), true, true)) {
						$result = $item;
					}
					break;
					
				// (registrydeletevalue) 		
				case "registrydeletevalue":					
					// get items
					$items = GetVar("items");
					// cycle
					foreach($items as $id) {
						mgDeleteOption($id, true);
					}
					// set result
					$result = true;
					break;
				
				// (registryupdatevalue) 	
				case "registryupdatevalue":
					// get values
					$fields = GetVar("fields");
					// set result
					$result = mgWriteOption($fields["idstring"], $fields["value"], true);
					break;
				
				// (registryaddvalue) 
				case "registryaddvalue":
					// get fields
					$fields = GetVar("fields");
					// add option
					if($id = mgRegisterOption(sprintf("%s.%s", $fields["group"], $fields["name"]), $fields["type"], DefaultValue(@$fields["mode"], 0))) {
						$result = Array("id"=>$id);
					}
					break;
			
				// (registry) 
				case "registry":
					$result = mgManagerGridDBData(DB_TABLE_OPTIONS, function($item) {
						// prepare meta data
						if($item[DB_FIELD_TYPE] == OPTION_TYPE_COLLECTION) {
							$item[DB_FIELD_VALUE] = unserialize($item[DB_FIELD_VALUE]);
						}
						// return item
						return $item;
					});
					break;
					

				# ----------------------------------------------------------------------
				# Other
				
				// undefined request
				default: 
					$result = false;
			}
			// action was successfull
			$this->content = $result;
			// return result
			return $result!==false?MODULE_RESULT_JSON:false;
		}
	}
?>