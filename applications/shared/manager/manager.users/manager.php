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
	class mgManagerModuleUser extends mgManagerExtension {
		
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
					
				// (getusers) 
				case "setuser":					
					// get user data
					$data = GetVar("user", false);
					// check user data
					if(is_array($data)) {
						// get mode
						$userid = isset($data["idstring"])?$data["idstring"]:DB_CREATE;					
						// create user controller
						$db = new mgUserController($userid);
						// check controller
						if($db->result==DB_OK) {
							// unset joindate
							if(isset($data["joindate"])) {
								unset($data["joindate"]);
							}
							// write
							$db->Write($data);
							// write certain fields only
							$db->Write(Array(
								DB_FIELD_TYPE => DefaultValue(@$data[DB_FIELD_TYPE], 0),
								DB_FIELD_ROLE => DefaultValue(@$data[DB_FIELD_ROLE], 0),
								DB_FIELD_ENABLED =>  DefaultValue(@$data[DB_FIELD_ENABLED], 0),
								DB_FIELD_STATUS => DefaultValue(@$data[DB_FIELD_STATUS], 0),
								DB_FIELD_META => serialize(DefaultValue(@$data[DB_FIELD_META], false)),
								DB_FIELD_LOCALIZED => DefaultValue(@$data[DB_FIELD_LOCALIZED], "")
							));
							// set join date
							
							// publish
							$db->Publish();
						}
					}
					break;
			
				// (getusers) 
				case "getusers":
					// get all users
					$result = mgManagerGridDBData(DB_TABLE_USERS, function($item) {
						// remove user password
						unset($item[DB_FIELD_PASSWORD]);
						// prepare meta data
						$item[DB_FIELD_META] = unserialize(DefaultValue($item[DB_FIELD_META], Array()));
						// return item
						return $item;
					});
					break;
					
					
				// (token)
				case "token":
					// get user
					$data = GetVar("user", false);
					// check user data
					if(is_array($data)&&isset($data["idstring"])) {
						// get userid
						$userid = $data["idstring"];
						// create user controller
						$db = new mgUserController($userid);					
						// check user
						if($db->result==DB_OK) {
							// switch by action
							switch(GetVar("action")) {
								// (issue password)
								case "password":
									// create password
									$newpassword = CreateRandomPassword(15);
									// set password
									$db->Write(DB_FIELD_PASSWORD, $newpassword);
									// publish
									$db->Publish();
									// set result
									$result = Array("p"=>$newpassword);
									break;
								// (issue token)
								case "create":
									// create token
									$newtoken = CreateGUID();
									// set token
									$db->Write(DB_FIELD_TOKEN, $newtoken);
									// publish
									$db->Publish();
									// set result
									$result = Array("t"=>$newtoken);
									break;
								// (revoke token)
								case "revoke":
									// clear token
									$db->Write(DB_FIELD_TOKEN, "");
									// publish
									$db->Publish();
									// set result
									$result = Array("t"=>true);
									break;
							}
						}
					}
					break;						
					
				// (remove)
				case "remove":
					$items = GetVar("items");
					// cycle
					foreach($items as $id=>$name) {
						// create user
						$user = new mgUserController($id);
						// check db
						if($user->result == DB_OK) {
							// remove
							$user->Delete();
						}
					}
					// set result
					$result = true;
					break;

				// (getusers) 
				case "parameters":
					$result = Array(
						"groups"=>mgReadOption("user.groupnames"),
						"types"=>mgReadOption("user.typenames"),
						"languages"=>mgReadOption("localization.languages"),
						"statusnames"=>mgReadOption("user.statusnames")						
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