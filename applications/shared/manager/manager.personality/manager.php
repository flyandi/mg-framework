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
	class mgManagerModulePersonality extends mgManagerExtension {
		
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
					switch(GetVar("name")) {
						case "language":
							// build dynamic grid
							$grid = $this->xml->grids->grid->GetAttribute("name", "language");
							// add languages
							foreach(mgReadOption("localization.languages") as $language) {
								$child = $grid->addChild("column");
								foreach(Array("name"=>ucfirst($language), "field"=>strtolower($language)) as $n=>$v) {
									$child->addAttribute($n, $v);
								}
							}
							$result = mgManagerGridColumns($grid, false, $this);
							break;
						default: 
							$result = mgManagerGridColumns($this->xml->grids, GetVar("name", MANAGER_GRID_DEFAULT), $this);
							break;
					}
					break;
					
				// (set)
				case "set":
					// get personality
					$p = new mgPersonality(GetVar(DB_FIELD_IDSTRING));
					// verify
					if($p->db->result == DB_OK) {
						// save containers
						$p->db->WriteFieldValue(DB_FIELD_META, PERSONALITY_CONFIGURATION, GetVar(PERSONALITY_CONFIGURATION));
						$p->db->WriteFieldValue(DB_FIELD_META, PERSONALITY_TEMPLATES, GetVar(PERSONALITY_TEMPLATES));
						$p->db->WriteFieldValue(DB_FIELD_META, PERSONALITY_REQUESTS, GetVar(PERSONALITY_REQUESTS));
						$p->db->WriteFieldValue(DB_FIELD_META, PERSONALITY_CONTENTS, GetVar(PERSONALITY_CONTENTS));
						$p->db->WriteFieldValue(DB_FIELD_META, PERSONALITY_SNIPPETS, GetVar(PERSONALITY_SNIPPETS));
						$p->db->WriteFieldValue(DB_FIELD_META, PERSONALITY_EMAILS, GetVar(PERSONALITY_EMAILS));
						// publish containers
						$p->db->Publish();
					} else {
						$result = false;
					}
					break;
			
				// (data) 
				case "data":
					// return result
					$result = mgManagerGridDBData(DB_TABLE_PERSONALITY, function($item) {
						// unserialize package
						$meta = unserialize($item[DB_FIELD_META]);
						// unset meta field
						unset($item[DB_FIELD_META]);
						// assign buckets
						$item[PERSONALITY_CONFIGURATION] = @$meta[PERSONALITY_CONFIGURATION];
						$item[PERSONALITY_TEMPLATES] = @$meta[PERSONALITY_TEMPLATES];
						$item[PERSONALITY_REQUESTS] = @$meta[PERSONALITY_REQUESTS];
						$item[PERSONALITY_CONTENTS] = @$meta[PERSONALITY_CONTENTS];
						$item[PERSONALITY_SNIPPETS] = @$meta[PERSONALITY_SNIPPETS];
						$item[PERSONALITY_EMAILS] = @$meta[PERSONALITY_EMAILS];
						// return item
						return $item;
					});
					break;

				// (parameters) 
				case "parameters":	
					$result = Array(
						"personalities"=>mgGetPersonalities(),
						"languages"=>mgReadOption("localization.languages"),
						"templatetypenames"=>mgReadOption("templates.typenames"),
						"templateformatnames"=>mgReadOption("templates.formatnames"),
						"requeststypenames"=>mgReadOption("requests.typenames"),
						"requestsconditionnames"=>mgReadOption("requests.conditionnames"),
						"metadefaultfields"=>mgReadOption("web.defaultmetafields"),
						"usergroupnames"=>mgReadOption("user.groupnames"),
						"emailsenders"=>mgReadOption("email.senders")
					);
					break;
					
				// (language) 
				case "language":
					// get personality
					$p = new mgPersonality(GetVar(DB_FIELD_IDSTRING));
					// check personality
					if($p->db->result == DB_OK) {
						// initialize language reader
						$reader = new mgLanguageReader(mgReadOption("localization.languages"), $p->languagepath);
						// test reader
						if($reader->count() > 0) {
							$result = $reader->getall();
							break;
						}
					}
					// result is false
					$result = false;
					
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