<?php
	/* 
		(mg)framework Version 5.0
		
		Copyright (c) 1999-2011 eikonlexis LLC. All rights reserved.
		
		This program is protected by copyright laws and international treaties.
		Unauthorized reproduction or distribution of this program, or any 
		portion thereof, may result in serious civil and criminal penalties.
	
		Component	Manager Controls
					Provides extended functions for the manager controls
	*/
	
	
	# -------------------------------------------------------------------------------------------------------------------
	# Constants Declaration	
	define("MANAGER_GRID_DEFAULT", "default");
	
	# -------------------------------------------------------------------------------------------------------------------
	# (mgManagerGridData) creates the data for a manager grid
	function mgManagerGridData($data, $page = 0, $totalcount=false) {
		// verify
		$data = is_array($data)?$data:Array();
		// create header
		return Array("total"=>$totalcount!==false?$totalcount:count($data), "page"=>$page, "rows"=>$data);
	
	}
	
	# -------------------------------------------------------------------------------------------------------------------
	# (mgManagerGridDBData) creates data from db for a grid
	function mgManagerGridDBData($table, $filter = false, $fields = false) {
		// get values
		$page = intval(GetVar("requestpage", 0));
		$itemsperpage = intval(GetVar("itemsperpage", 10));
		$search = GetVar("search", false);
		$sort = GetVar("sort", false);
		$prefilter = GetVar("filter", false);
		$dbmode = DB_SELECT;
		// create filter
		if(is_array($prefilter)) {
			if(!is_array($fields)) $fields = Array();
			$fields = array_merge($fields, $prefilter);
		}		
		// create sort
		if(strlen($sort)!=0) {
			// get sortmode
			$sortmode = GetVar("sortmode","d");
			$sortmode = $sortmode=="a"?DB_SORTCOLUMN_ASC:DB_SORTCOLUMN_DESC;
			$sort = Array($sort=>$sortmode);
		}
		// advance filter
		$db = false;
		if(is_array($fields)) {
			foreach($fields as $n=>$v) {
				switch(strtolower($n)) {
					// (match)
					case "@match":
						unset($fields[$n]);
						$db = new mgDatabaseStream($table, DB_SELECTRAW, sprintf("SELECT * FROM mg_%s ta, mg_%s tb WHERE ta.%s=tb.%s AND ta.idstring<>tb.idstring ORDER BY ta.%s ASC",
							$table, $table, $v, $v, $v
						));
						break 2;
				}
			}
		}
		// execute
		if($db===false) {
			if(mgCompare($search, "false")) $search = false;
			if(is_string($search)&&strlen($search)!=0) {
				$db = new mgDatabaseSearch($table, $search, $fields, $sort);
			} else {
				$db = new mgDatabaseStream($table, $dbmode, $fields, $sort);
			}		
		}
		// initialize
		$data = Array();
		// check
		if($db->result == DB_OK) {
			// validate values
			$itemsperpage = $itemsperpage<10?10:($itemsperpage>500?500:$itemsperpage);		
			$page = $page*$itemsperpage > $db->rowcount()?round($db->rowcount()/$itemsperpage, 0, PHP_ROUND_HALF_DOWN):$page;
			// get data
			foreach($db->getsliced($page+1, $itemsperpage) as $index=>$item) {
				// check filter
				if($filter) {
					$item = $filter($item);
				}
				// assign item
				if($item!==false) {
					$data[] = $item;
				}
			}
		}
		// create output
		return mgManagerGridData($data, $page, $db->rowcount());	
	}
	
	# -------------------------------------------------------------------------------------------------------------------
	# (mgManagerGridColumns) creates the column description based on the incoming xml file
	function mgManagerGridColumns($grids, $name = MANAGER_GRID_DEFAULT, $module = false) {
		// initialize result
		$result = Array();
		// get grid
		if($name===false) {
			$grid = $grids;
		} else {
			$grid = $grids->grid->GetAttribute("name", $name);
		}
		// check
		if($grid) {
		// cycle grid
			foreach($grid->column as $column) {
				// create format
				$format = isset($column["format"])?strtolower(trim((string)$column["format"])):false;
				// get formatting
				switch($format) {
					// (option)
					case "option":
						// read option value
						$values = mgReadOption((string)@$column["option"]);
						// test for array
						if(is_array($values)) {
							$format = $values;
						}
						break;					
					// read formatting options
					case "true": case true:
						// reset container
						$format = Array();
						// cycle options
						foreach($column->format as $f) {
							// add to formatting options
							$format[(string)@$f["value"]] = (string)$f["label"];
						}
						break;						
					// other options
					default: 
						
						break;
				}
				
				// create item
				$item = Array(
					"label"=>$module?$module->asLocalizedString((string)$column["name"]):(string)$column["name"],
					"field"=>(string)$column["field"],
					"width"=>isset($column["width"])?(string)$column["width"]:false,
					"sortable"=>isset($column["sortable"])?true:false,
					"align"=>isset($column["align"])?(string)$column["align"]:false,
					"format"=>$format,
					"icon"=>isset($column["icon"])?(string)$column["icon"]:false,
					"sortfield"=>isset($column["sortfield"])?(string)$column["sortfield"]:false
				);
				// add to result
				$result[] = $item;
			}
		}
		// return result
		return $result;
	}
	
?>