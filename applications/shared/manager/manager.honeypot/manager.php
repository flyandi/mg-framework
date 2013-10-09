<?php
	/* 
		(mg)framework Version 5.0
		
		Copyright (c) 1999-2012 eikonlexis LLC. All rights reserved.
		
		This program is protected by copyright laws and international treaties.
		Unauthorized reproduction or distribution of this program, or any 
		portion thereof, may result in serious civil and criminal penalties.
	
		Module 	HoneyPot
	*/
	
	# -------------------------------------------------------------------------------------------------------------------
	# constants
	define("REQUEST_BLOCKED", "blocked");
	define("REQUEST_TRAFFIC", "traffic");
	define("REQUEST_TRAPS", "traps");
	define("REQUEST_RULES", "rules");
	
	
	# -------------------------------------------------------------------------------------------------------------------
	# (class) mgManagerModuleHoneyPot
	class mgManagerModuleHoneyPot extends mgManagerExtension {
		
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
					
				// (clear)
				case "clear":
					// get cache
					$cache = GetVar("cache");
					// validate
					if(is_array($cache)) {
						// cycle
						foreach(Array("blocked", "traffic", "traps") as $type) {
							// check type
							$types = Array("exp", "tmp");
							if($type != "traffic") $types[] = "pmt";
							// cycle
							foreach($types as $subtype) {
								// create id
								$id = sprintf("%s%s", $subtype, $type);
								// vaidate
								if(isset($cache[$id])&&$cache[$id]=="true") {
									// initialize
									$query = false;
									// table
									$table = sprintf("honeypot%s", $type);
									switch($subtype) {
										// expired item
										case "exp":
											$query = sprintf("idtype=0 AND timeout < %s", time());
											break;
										case "tmp":
											$query = "idtype=0";
											break;
										case "pmt":
											switch($type) {
												case "blocks":
												case "traps":
													$query = "idtype=1";
													break;
											}
											 break;
									}
									// build query
									if($query!==false) {
										$query = sprintf("DELETE FROM mg_honeypot%s WHERE %s", $type, $query);
										mysql_query($query);
									}
								}
							}
						}
					}			
					$result = true;
					break;
				
				// (data) 
				case "data":
					// prepare
					switch(GetVar("itemtype")) {
						case REQUEST_BLOCKED:
							$table = DB_TABLE_HONEYPOTBLOCKED; break;
						case REQUEST_TRAFFIC:
							$table = DB_TABLE_HONEYPOTTRAFFIC; break;
						case REQUEST_TRAPS:
							$table = DB_TABLE_HONEYPOTTRAPS; break;
						case REQUEST_RULES:
							$table = DB_TABLE_HONEYPOTRULES; break;
						default:
							return false;
					}
					// return result
					$result = mgManagerGridDBData($table, function($item) use ($table) {
						// unserialize package
						$meta = @unserialize($item[DB_FIELD_META]);
						unset($item[DB_FIELD_META]);
						// assign buckets
						$item["stampcreated"] = date("Y-m-d h:ia", (integer)$item[DB_FIELD_CREATED]);
						$item["stamptimeout"] = "Never";
						$item["timeoutseconds"] = 0;
						$item["meta"] = $meta;
						if(isset($item["timeout"])&&$item["timeout"]!=0) {
							$item["stamptimeout"] = date("Y-m-d h:ia", (integer)$item["timeout"]);
							$item["timeoutseconds"] = (integer)$item["timeout"] - (integer)$item[DB_FIELD_CREATED];
						} 
						// return item
						return array_merge($item, is_array($meta)?$meta:Array());
					});
					break;
					
				// (set)
				case "set":
					// initialize result
					$result = Array("result"=>false);
					// get data
					$data = GetVar("data"); 
					// sanity check result
					if(is_array($data)) {
						// get mode
						$mode = isset($data[DB_FIELD_IDSTRING])?$data[DB_FIELD_IDSTRING]:DB_CREATE;
						// switch by type
						$item = false;
						// prepare
						switch(GetVar("itemtype")) {
							case REQUEST_BLOCKED:
								$table = DB_TABLE_HONEYPOTBLOCKED; break;
							case REQUEST_TRAFFIC:
								$table = DB_TABLE_HONEYPOTTRAFFIC; break;
							case REQUEST_TRAPS:
								$table = DB_TABLE_HONEYPOTTRAPS; break;
							case REQUEST_RULES:
								$table = DB_TABLE_HONEYPOTRULES; break;
							default:
								return false;
						}
						// create item
						$item = new mgDatabaseObject($table, $mode);
						// check item
						if($item&&$item->result==DB_OK) {
							// write data
							$item->Write($data);
							// write meta
							$item->Write(DB_FIELD_META, serialize($item->RemoveFieldsData(array_merge($data))));
							// disable item if brand new
							if($mode == DB_CREATE) {
								// set private
								$item->Write(DB_FIELD_CREATED, time());
							}
							// check timeout
							if(isset($data["timeoutseconds"])&&$data["timeoutseconds"]!=0) {
								$timeout = (integer)$item->Read(DB_FIELD_CREATED) + (integer)$data["timeoutseconds"];
								$item->Write("timeout", $timeout);
							}
							// publish containers
							$item->Publish();
							// prepare values for result
							$values = $item->ReadAll();
							// update successful
							$result = Array(
								"result"=>true,
								"item"=>$values,
							);
						}
					}
					break;
					
				// (remove) 				
				case "remove":		
					// cycle by items
					$items = GetVar("items");
					// check array
					if(is_array($items)) {
						// cycle
						foreach($items as $id=>$name) {
							// create item by type
							switch(GetVar("itemtype")) {
								case REQUEST_BLOCKED:
									$table = DB_TABLE_HONEYPOTBLOCKED; break;
								case REQUEST_TRAFFIC:
									$table = DB_TABLE_HONEYPOTTRAFFIC; break;
								case REQUEST_TRAPS:
									$table = DB_TABLE_HONEYPOTTRAPS; break;
								case REQUEST_RULES:
									$table = DB_TABLE_HONEYPOTRULES; break;
								default:
									$table = false;
							}
							// create item
							if($table!==false) {
								$item = new mgDatabaseObject($table, $id);
								// check item
								if($item->result==DB_OK) {
									// no hard delete
									$item->Delete();
								}
							}
						}
						// result
						$result = true;
					}
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