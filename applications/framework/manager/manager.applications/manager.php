<?php
	/* 
		(mg)framework Version 5.0
		
		Copyright (c) 1999-2012 eikonlexis LLC. All rights reserved.
		
		This program is protected by copyright laws and international treaties.
		Unauthorized reproduction or distribution of this program, or any 
		portion thereof, may result in serious civil and criminal penalties.
	
		Module 	Manager Module Template
	*/
	
	# -------------------------------------------------------------------------------------------------------------------
	# constants
	
	# -------------------------------------------------------------------------------------------------------------------
	# (class) bnServiceModule
	class mgManagerModuleApplications extends mgManagerExtension {
		
		# ---------------------------------------------------------------------------------------------------------------
		# (public) process
		public function process($request) {
			// initialize result
			$result = true;
			// use manager controls
			$this->manager->framework->usecomponent(COMPONENT_MANAGER);			
			// switch by request
			switch($request) {
				// (getgrid), returns the grid information from the xml configuration
				case "getgrid":
					// get grid
					$result = mgManagerGridColumns($this->xml->grids, GetVar("name", MANAGER_GRID_DEFAULT), $this);
					break;
					
				// (getapplications) 
				case "getapplications":
					// load applications
					$applications = new mgApplicationDetector(true, "../");
					// initialize data
					$data = Array();
					// cycle applications
					foreach($applications->applications as $app) {
						// prevent framework indexing and make sure it's a valid app
						if($app->id!="framework"&&isset($app->xml)) {
							// build data grid
							$data[] = Array(
								"idapplication"=>@$app->id,
								"name"=>(string)@$app->xml["name"],
								"version"=>(string)@$app->xml["version"],
								"status"=>(string)@$app->xml->status,
								"icon"=>mgBase64Image($app->icon),
								"variables"=>@$app->variables,
								"connections"=>@$app->connections
							);
						}
					}
					// return data
					$result = mgManagerGridData($data);
					break;

				// (getusers) 
				case "parameters":
					$result = Array(
						"statusnames"=>mgReadOption("application.statusnames")
					);
					break;
			
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