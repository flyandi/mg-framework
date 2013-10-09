<?php    
	/**
	 * {managerclass}
	 *
	 * Description for this manager
	 *
	 * @author		Name of author
	 * @module		manager.{managerid}
	 * @package		{applicationid}
	 */
	
	# -------------------------------------------------------------------------------------------------------------------
	# constants
		
	# -------------------------------------------------------------------------------------------------------------------
	# (class) mgManagerModule{managerclass}
	class mgManagerModule{managerclass} extends mgManagerExtension {
	
		# ---------------------------------------------------------------------------------------------------------------
		# (public) process
		public function process($request) {
			// initialize result
			$result = true;
			// use manager controls
			$this->manager->framework->usecomponent(COMPONENT_MANAGER);
			// switch by request
			switch($request) {						
				// (parameters)
				case "parameters":
					$result = array(
						
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
