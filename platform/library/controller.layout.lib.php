<?php
	/* 
		(mg)framework Version 5.0
		
		Copyright (c) 1999-2011 eikonlexis LLC. All rights reserved.
		
		This program is protected by copyright laws and international treaties.
		Unauthorized reproduction or distribution of this program, or any 
		portion thereof, may result in serious civil and criminal penalties.
	
		Module 		Layout Controller
	*/
	
	# -------------------------------------------------------------------------------------------------------------------
	# Constants Declaration
	define("LAYOUT_ITEM_TITLE", "title");
	define("LAYOUT_ITEM_WIDGET", "widget");
	define("LAYOUT_ITEM_COLUMNS", "columns");
	define("LAYOUT_ITEM_COLUMN", "column");
	define("LAYOUT_ITEM_BREADBAR", "breadbar");
	
	# -------------------------------------------------------------------------------------------------------------------
	# (class) mgLayout
	class mgLayout {
	
		# ---------------------------------------------------------------------------------------------------------------
		# (local)
		private $layout = Array();
		private $widgets = null;
		private $values = Array();
		private $rawvalues = Array();
		private $framework = false;
		private $baseurl = false;
	
		# ---------------------------------------------------------------------------------------------------------------
		# (constructor)
		public function __construct($layout = false) {
			// create widgets
			$this->widgets = new mgWidgetManager();
			// assign layout
			if($layout) {
				$this->AssignLayout($layout);
			}
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (public) AssignLayout
		public function AssignLayout($layout) {
			// test if filename
			if($layout!==false&&@file_exists($layout)) {
				$layout = file_get_contents($layout);
			}
			// convert
			if(is_string($layout)) {
				// try to convert to template
				if($this->framework) {
					if($l = $this->framework->personality->GetTemplateContent($layout)) {
						$layout = $l;
					}
				}
				// try to convert
				$layout = @json_decode($layout, true);
			}
			// assign
			if(is_array($layout)) {
				$this->layout = $layout;
				return true;
			}
			return false;
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (public) AssignDatabaseClass, assigns from a DB class
		public function AssignDatabaseClass($class, $path = false, $register = false) {
			// double check class
			if($class->result==DB_OK) {
				// get values
				$this->values = mgCreateVariables($class->getrow(), $path, $this->framework);
				$this->rawvalues = $class->getrow();
				// assign layout
				$this->AssignLayout($class->GetLayout());
				// register
				$this->framework->resources->register($register);
			}
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (public) SetFramework
		public function SetFramework($framework) {
			// assign
			$this->framework = $framework;
			$this->widgets->SetFramework($framework);
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (public) SetBaseURL
		public function SetBaseURL($baseurl) {
			$this->baseurl = $baseurl;
		}
		
		
		# ---------------------------------------------------------------------------------------------------------------
		# (public) AddValue
		public function AddValue($name, $value = false) {
			if(!is_array($name)) { $name = Array($name=>$value);}
			foreach($name as $k=>$v) {
				$this->values[$k] = $v;
			}
		}

		# ---------------------------------------------------------------------------------------------------------------
		# (public) HasLayout
		public function HasLayout() {
			return count($this->values)!=0 && is_array($this->layout) && count($this->layout) != 0;
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (public) html, returns the layout as html
		public function html() {
			// compiler
			return $this->__compile();
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (private) __compile
		private function __compile() {
			// initialize result
			$result = "";
			// cycle layout
			foreach($this->layout as $item) {
				$result .= $this->__compileitem($item);
			}
			// return result
			return $result;
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (private) __compileitem
		private function __compileitem($item, $override = false) {
			// initialize result
			$result = "";
			// transform item to object
			$item = (object)$item;
			// switch by type
			switch($override?$override:@$item->type) {
				// (columns)
				case LAYOUT_ITEM_COLUMNS:
					// cycle columns
					foreach($item->columns as $subitem) {
						$result .= $this->__compileitem($subitem, LAYOUT_ITEM_COLUMN);
					}
					// wrap content
					$result .= Div("-clear");
					break;
					
				// (column)
				case LAYOUT_ITEM_COLUMN:
					// cycle column content
					foreach($item->content as $subitem) {
						$result .= $this->__compileitem($subitem);
					}
					// wrap content
					$result = Tag("div", Array("class"=>@$item->classname), $result);
					break;					
				
				// (widget) adds the widget to the content
				case LAYOUT_ITEM_WIDGET:
					// get widget
					$widget = $this->widgets->get(sprintf("widget.%s", $item->widget));
					// test widget
					if($widget!==false) {
						// assign
						$widget->rawvalues = $this->rawvalues;
						// create content
						$content = $widget->output(WIGDGET_OUTPUT_HTML, @$item->settings, $this->values, $this->baseurl);
						// check content
						if($content !== false) {
							// create result container
							$result = Tag("div", Array("related"=>"widget", "widget"=>$item->widget), $content);
						}
					}
					break;
			}
			// return result
			return $result;		
		}
	}
	
	# -------------------------------------------------------------------------------------------------------------------
	# (macro) mgGetLayoutParsed
	function mgGetLayoutParsed($layout, $framework = false) {
		// create layout
		$l = new mgLayout();
		// apply framework
		$l->SetFramework($framework);
		// assign layout
		$l->AssignLayout($layout);
		// return layout
		return $l->html();
	}
	
?>