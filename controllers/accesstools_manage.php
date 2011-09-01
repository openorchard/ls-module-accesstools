<?php 

	class AccessTools_Manage extends Backend_Controller {
		public $data = array(
			'htaccess' => array (
				'file_name' => '.htaccess', 
				'description' => 'Control how the website is accessed'),
			'humanstxt' => array (
				'file_name' => 'humans.txt', 
				'description' => 'Provide information about the people and techniques behind the website'),
			'robotstxt' => array (
				'file_name' => 'robots.txt', 
				'description' => 'Prevent web crawlers and other web robots from accessing all or part of the website')	
			);
		public $implement = 'Db_FormBehavior';
	
		public $form_redirect = null;
		
		public function __construct() 	{
			parent::__construct();
			
			//set up the menu tabs
			$this->app_module = 'system';
			$this->app_tab = 'system';
			$this->app_page = 'settings';
			
			$this->app_module_name = 'System';		
			$this->app_page_title = 'Access Tools';
			
			$this->viewData['filename'] = false;
		}
		
		public function index() 	{
			foreach($this->data as $i => $file) {
				//check if the file exists
				$full_path = PATH_APP.'/'.$file['file_name'];
				if (!file_exists($full_path) || !is_file($full_path)) 	{
					$this->data[$i]['status'] = 'File does not exist';
				}
				else {
					//if it exists, check when it was last modified
					$date = new Phpr_DateTime();
					$date->setPHPDateTime(filemtime($full_path));
					$this->data[$i]['status'] = 'File last modified '.Phpr_Date::display($date, '%x %X');
				}			
			}
			$this->viewData['data'] = $this->data;
		}
		
		public function edit($filename) {
			$this->viewData['data'] = $this->data;
			if(array_key_exists($filename, $this->data)) {
				$file_path = $this->data[$filename]['file_name'];
				$this->viewData['filename'] = $filename;
			
				$this->app_page_title = 'Edit '.$file_path;
				try {
					$full_path = PATH_APP.'/'.$file_path;
					if (!file_exists($full_path) || !is_file($full_path)) 	{
						$this->viewData['file_contents'] = '';					
					}
					elseif(!$this->viewData['file_contents'] = file_get_contents($full_path))  {
						throw new Phpr_ApplicationException('Could not open the file!');
					}
					$this->viewData['full_path'] = $full_path;
					$this->viewData['file_path'] = $file_path;
					
					$pathInfo = pathinfo($file_path);
					$this->viewData['ext'] = 'txt';
				}
				catch (Exception $ex) {
					$this->handlePageError($ex);
				}
			}
			else {
				try 	{
					throw new Phpr_ApplicationException('Unknown file! The file you are trying to edit is not part of Access Tools');
				}
				catch (Exception $ex) {
					$this->handlePageError($ex);
				}
			}
		}
		
		protected function edit_onSave($filename) 	{
			$created = false;
			if(array_key_exists($filename, $this->data)) {
				$file_path = $this->data[$filename]['file_name'];
				$full_path = PATH_APP.'/'.$file_path;
				try {
					if (!file_exists($full_path) || !is_file($full_path)) {
						//if file does not exist, create an empty one with that name
						if(!$file_handle = @fopen($full_path, 'w')) {
							throw new Phpr_ApplicationException('Could not create the file! Please check the folder permissions or create the file yourself.');
						} 
						else {
							$created = true;
							fwrite($file_handle, ' ');
							chmod($full_path, 0755);
							fclose($file_handle);				
						}
					}
	
					if(!@file_put_contents($full_path, post('file_content'))) {
						//throw new Phpr_ApplicationException('Unable to save changes to the file!');
					}
					else	{
						Phpr::$session->flash['success'] = 'File has been successfully ';
						if($created) 
							Phpr::$session->flash['success'] .= 'created and changes have been saved ';
						else
							Phpr::$session->flash['success'] .= 'saved ';
						Phpr::$session->flash['success'] .= ' at '.Phpr_Date::display(Phpr_DateTime::now(), '%X');
						$this->renderPartial('flash');
						
						if (post('redirect', 1)) 	{
							Phpr::$response->redirect(url('accesstools/manage/'));
						}
					}
				}
				catch (Exception $ex) {
					Phpr::$response->ajaxReportException($ex, true, true);
				}
			}
			else {
				try {
					throw new Phpr_ApplicationException('Unknown file!');
				}
				catch (Exception $ex) {
					Phpr::$response->ajaxReportException($ex, true, true);
				}
			}
		}
		
		protected function edit_onCancel ($filename) {
			Phpr::$response->redirect(url('accesstools/manage/'));
		}
	}

?>