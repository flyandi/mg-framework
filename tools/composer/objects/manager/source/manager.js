/**
 * {managerclass}
 *
 * Description for this manager
 *
 * @author		Name of author
 * @module		manager.{managerid}
 * @package		{applicationid}
 */
 
/** mgResourceScript */ 
var mgResourceScript = {

	/** (process) */
	process: function(action) {
		// initialize 
		var that = this;
		
		// parameters
		this.parameters = this.call('parameters');
		
		// switch by action
		switch(action) {
			// (default)
			default:
				this.returncontent([
			
				], false, function() {
					
				});
				break;
		}
	},
};