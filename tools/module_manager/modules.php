<?php

class modules
{
	function __construct(){
		$this->db = new db;
	}
	
	function get_modules($module=false)
	{
		if ( $module ){
			$modules = $this->db->qquery("SELECT * FROM modules WHERE module_id = $module");
		} else {
			$modules = $this->db->qquery("SELECT * FROM modules");
		}
		
		for ($i=0; $i<count($modules); $i++){
			$modules[$i]['form_arr'] = json_decode($modules[$i]['form_arr'], TRUE);
			$modules[$i]['list_data'] = json_decode($modules[$i]['list_data'], TRUE);
		}
		
		if ( $module ){
			return $modules[0];
		}
		
		return $modules;
	}
	
	function get_field_types(){
		$field_types = array(
			'Text'=>'text',
			'Textarea'=>'textarea',
			'Image'=>'image',
			'HTML Content'=>'html_content',
			'Drop Down'=>'select'
		);
		
		return $field_types;
	}
}
?>