<?php
	/**
	 *  {classname}
	 *
	 *	This program is protected by copyright laws and international treaties.
	 *	Unauthorized reproduction or distribution of this program, or any 
	 *	portion thereof, may result in serious civil and criminal penalties.
	*/
	
	# -------------------------------------------------------------------------------------------------------------------
	# (constants)
	
	# -------------------------------------------------------------------------------------------------------------------
	# (class) {classname}, Description goes here
	class {classname} extends mgFrameworkExtension {
	
		# ---------------------------------------------------------------------------------------------------------------
		# (public) Process, processes the extension (called from project controller)
		public function Process($__request) {
			// set callback
			$this->framework->option(CALLBACK, $this);
			// process request
			$this->SetContent($this->__processrequest($__request));
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (public) Action, executes an action item
		public function Action($__request) {
	
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (public) Callback, executes a callback
		public function Callback($__request) {
		
		}
		
		# ---------------------------------------------------------------------------------------------------------------
		# (private) __processrequest, processes the current request
		private function __processrequest($__request, $__action=false) {
			// initialize result
			$result = false;
			
			// extension code goes here
		}
	}