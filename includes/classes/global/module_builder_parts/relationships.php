<?php
class relationships
{
	function has_relationship($field_name)
	{
		global $form_arr;
		$field = $this->get_field_arr($field_name);
		if ( is_array($field['relates_to']) )
		{
			return True;
		}
		return;
	}
	
	
	function get_field_display_name($field_name)
	{
		global $form_arr;
		if ( $this->has_relationship($field_name) )
		{
		}
		else
		{
			return $form_arr['label'];
		}
	}
	
	//============================================================
	// FIND FIELD ARRAY 
	// 	This makes it easy to find the field you're looking for in the form_arr array instead of having to loop through each time
	//		* Finds field based on "name" element
	//============================================================
	function get_field_arr($field_name)
	{
		global $form_arr;
		foreach ($form_arr as $key=>$value )
		{
			if ( $value['name'] == $field_name )
			{
				return $value;
			}
		}
	}
	
	
	//============================================================
	// 	DISPLAY MATCH
	//============================================================
	function get_match($arr)
	{
		$local_field = $arr['local_name'];
		$value = $arr['value'];
		
		if ( $this->has_relationship($local_field) )
		{
			$field_arr = $this->get_field_arr($local_field);
			$field_rel = $field_arr['relates_to'];
			if ( $field_rel['type'] == 'db' )
			{
				$query_str = "SELECT " . $field_rel['display'] .  " FROM " . $field_rel['table'] . " WHERE " . $field_rel['field'] . " = '".addslashes($value)."'";
				$db = new db;
				$results = $db->qquery($query_str);
				return $results[0][$field_rel['display']];
			}
		}
	}

}
?>