<?php
namespace Marcus\StudyMetadataSearch\ExternalModule;

/**
 * ExternalModule  - (required) Abstract implementation of REDCap module
 */
class ExternalModule extends \ExternalModules\AbstractExternalModule {	
	/**
	 * __construct
	 *
	 * @return void
	 */
	public function __construct() {
		parent::__construct();
	}

	public function getPrefix(){
		return "marcus_study_metadata_search";
	}
		
	/**
	 * redcap_module_system_enable
	 *
	 * @param  mixed $version
	 * @return void
	 */
	public function redcap_module_system_enable( $version ) {
		
	}
    
    /**
     * redcap_project_home_page
     *
     * @param  mixed $project_id
     * @return void
     */
    function redcap_project_home_page ($project_id) 
    {

    }
}