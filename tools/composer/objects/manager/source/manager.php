<?php
	/* 
		Application  	Affiliates
		Author			Andreas Schwarz
		Version		 	1.0
	*/
	
	# -------------------------------------------------------------------------------------------------------------------
	# constants
		
	# -------------------------------------------------------------------------------------------------------------------
	# (class) mgManagerModuleForms
	class mgManagerModuleAffiliates extends mgManagerExtension {
	
		# ---------------------------------------------------------------------------------------------------------------
		# (public) process
		public function process($request) {
			// initialize result
			$result = true;
			// use manager controls
			$this->manager->framework->usecomponent(COMPONENT_MANAGER);
			// switch by request
			switch($request) {
				// (get)
				case"get":
					// get all users
					$result = mgManagerGridDBData(DB_TABLE_AFFILIATES, function($item) {
						// get meta
						$meta = unserialize($item[DB_FIELD_META]);
						// unset
						unset($item[DB_FIELD_META]);
						// assign
						if(is_array($meta)) {
							$item = array_merge($item, $meta);
						}
						// return item
						return $item;
					});
					break;
					
				// (set) 
				case "set":
					// get data
					$data = GetVar("affiliate", false);
					// check user data
					if(is_array($data)) {
						// get object
						$d = (object)$data;
						// create database
						$db = new mgDatabaseObject(DB_TABLE_AFFILIATES, isset($d->idstring)?$d->idstring:DB_CREATE);
						// check db
						if($db->result == DB_OK) {
							// write
							$db->Write($data);
							// write data to buckets
							$db->WriteFieldValue(DB_FIELD_META, $data);
							// publish
							$db->Publish();
							// switch result
							$result = true;
						}
					}				
					break;					
			
				// (parameters)
				case "parameters":
					$result = array(
						"affiliatetypes" => mgReadOption("lawsmart.affiliatetypes")
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
