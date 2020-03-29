<?php
class modules{
	//============================================================
	//		Here's the deal on modules
	//			1. We are tacking this by the folders, starting with /admin/modules in the sites directory and looping through each directory	
	//			2. If we don't find a control file in the root of each directory, we assume it's a parent for other modules
	//				2a. if we find index.php in that parent directory, we add the 'has_index' param to the array
	//				2b. if we are directed to that parent dir via ?admin_action and it has an index, we will display the index file
	//			3. We will loop through each child directory and display it as a module, regardless of contents.
	//			4. Every module will be looking for control.php (unless it's a parent then index.php is backup to control.php)
	//============================================================
	
	function get_modules(){
		$module_dir = ABS_SITE_DIR."/admin/modules";
		
		//opten module directory
		$dir = opendir($module_dir);
		
		$module_arr = array(); //start with blank array
		
		//looping trough /modules
		while (false !== ($file = readdir($dir))) { // looping through /admin/modules directories
		
			//if we're not looking at the skeleton or . or ..
			if ( $file != 'skeleton' && strpos($file,'.') === false )
			{
				//let's check to see if this folder contains control.php
				$possible_module_parent = $module_dir.'/'.$file;
				$module_controller = $possible_module_parent.'/control.php';
				$display_name_arr = explode('/',$file);
				$display_name = $display_name_arr[0];
				
				//if it doesn't we're going to assume that this is a new module parent
				if ( !is_file($module_controller) )
				{
					$module_parent = $possible_module_parent;
					
					//now we need to loop through that directory and display those
					//show the parent
					$module_arr[$file] = array('id'=>$file, 'title'=>$this->display($display_name),'admin_action'=>$file,'children'=>array());
					
					//check for index
					if ( is_file($module_parent.'/index.php')){
						$module_arr[$file]['has_index'] = TRUE;
					}
				
					//now loop through these modules
					$subdir = opendir($module_parent);
					while ( ($subfolder = readdir($subdir)) !== false )
					{
						//skip . and ..
						if ( strpos($subfolder,'.') === false )
						{
							$admin_action = $file.'/'.$subfolder;
							$id = str_replace('/',':',$admin_action);
							$module_arr[$file]['children'][] = array('id'=>$id,'title'=>$this->display($subfolder),'admin_action'=>$admin_action);
						}
					}
				}
				else //if this is just a top level directory with control.php
				{
					$module_arr[$file] = array('id'=>$file,'title'=>$this->display($file),'admin_action'=>$file);
				}
			}
		}
		
		//toss forms in on the end
		if ( defined('FORMS_ALLOWED') ){
			$module_arr['forms'] = array('id'=>'forms', 'title'=>'Forms','admin_action'=>'forms');
		}
		
		return $module_arr;
	}
	
	function get_module($id){ 
		$modules = $this->get_modules;
		$module_to_return = false;
		foreach ($modules as $parent_module){
			if ( $parent_module['id'] == $id ){
				$module_to_return = $parent_module;
			}
			elseif ($parent_module['children'] ){
				foreach ($parent_module['children'] as $child_module){
					if ( $child_module['id'] == $id ){
						$module_to_return = $child_module;
					}
				}
			}
		}
		return $module_to_return;
	}
	
	function display($name){
		return ucwords(str_replace('_',' ',$name));
	}
}