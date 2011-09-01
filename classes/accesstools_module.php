<?php

class AccessTools_Module extends Core_ModuleBase {

	protected function createModuleInfo() 	{
		return new Core_ModuleInfo(
		"Access Tools",
		"Enables you to edit robots.txt, humans.txt and .htaccess files from the backend",
		"Limewheeel Creative" );
	}
	
	public function listSettingsItems() {
		$result = array(
			array(
				'icon'=>'/modules/accesstools/resources/images/access_tools.png', 
				'title'=>'Access Tools', 
				'url'=>'/accesstools/manage/', 
				'description'=>'Edit the robots.txt, humans.txt and .htaccess files',
				'sort_id'=>200
			)
		);
		return $result;
	}
}

?>