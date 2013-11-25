<?php
	/*
		(mg) Framework HTML

		Copyright (c) 1999-2010 eikonlexis LLC. All rights reserved.
		
		This program is protected by copyright laws and international treaties.
		Unauthorized reproduction or distribution of this program, or any 
		portion thereof, may result in serious civil and criminal penalties.
	
		Module 		HTML Controller
		Version		4.0.0 Generation BN-2010
	*/
	
	# -------------------------------------------------------------------------------------------------------------------
	# (constants)
	define("LIST_COLUMN_MANAGE", 100);				// Column Manage
	define("LIST_COLUMNS", 105);					// Columns
	define("LIST_HEADER_OPTIONS", 110);				// Header Options
	define("LIST_ROW_OPTIONS", 111);				// Row Options
	define("LIST_INDEX", 101);						// Index of the list
	define("LIST_ROWS", 106);						// Rows
	define("LIST_MANAGE_EDIT", 102);				// Manage Item Edit
	define("LIST_MANAGE_DELETE", 103);				// Manage Item Delete/Remove
	define("AS_HTML", "html");						// As HTML
	
	# -------------------------------------------------------------------------------------------------------------------
	# (class) mgList, creates a list
	class mgList {
		# -------------------------------------------------------------------------------------------------------------------
		# (local)
		private $__list;
		
		# -------------------------------------------------------------------------------------------------------------------
		# (constructor)
		public function __construct($list) {
			$this->__list = $list;
		}
		
		# -------------------------------------------------------------------------------------------------------------------
		# (html) returns the html
		public function html() {
			// create header
			$header = Tag("div", Array("class"=>"list-header ".@$this->__list[LIST_HEADER_OPTIONS]['classes']), $this->__getheader(AS_HTML));
			// create rows
			$rows = Tag("div", Array("class"=>"list-rows"), $this->__getrows(AS_HTML));
			// return list
			return $header.$rows;
		}
		
		# -------------------------------------------------------------------------------------------------------------------
		# (private) __getheader, returns the header of this list
		private function __getheader($type=AS_HTML) {
			// initialize result
			$result = "";
			// cycle
			foreach($this->__list[LIST_COLUMNS] as $name=>$params) {
				switch($type) {
					case AS_HTML: $result .= Tag("span", Array("style"=>sprintf("width:%spx", $params[0])), $name);
				}
			}
			//return result
			return $result;
			
		}
		
		# -------------------------------------------------------------------------------------------------------------------
		# (private) __getheader, returns the header of this list
		private function __getrows($type=AS_HTML) {
			// initialize result
			$result = "";
			// cycle rows
			foreach($this->__list[LIST_ROWS] as $row) {
				// reset row content
				$rowresult = "";
				// cycle column information
				foreach($this->__list[LIST_COLUMNS] as $name=>$params) {
					// create value
					$value = @$row[@$params[1]];
					// create row field
					switch($type) {
						case AS_HTML: 
							$rowresult .= Tag("span", Array("style"=>sprintf("width:%spx", $params[0])), $value);
					}
				}
				// add row
				$result .= Tag("div", Array("class"=>"list-row ".@$this->__list[LIST_ROW_OPTIONS]['classes']), $rowresult);
			}
			//return result
			return $result;
			
		}
	}

