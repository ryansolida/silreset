<?php

class module_data
{	
	function module_data($module_name='',$get_all=FALSE)
	{
		require_once('relationships.php');
		if ( $module_name != '' )
		{
			$this->module_name = $module_name;
		}
		if ( $get_all ){
			$entries = $this->get_entries();
			return $entries;
		}
	}
	
	function get_entries($entries_arr = '')
	{
		
		if ( is_string($entries_arr) && $entries_arr != ''){
			$this->module_name = $entries_arr;
		}
		
		$this->entries_arr = $entries_arr;
		
		if ($this->module_table == '' )
		{
			$module_info = $this->get_module_info();
		}
		

		
		if ( $module_info === FALSE )
		{
			return $this->error_text;
		}
		
		//let's see if we have any relationships
		$relationships = false;
		if ( is_array($this->form_arr) ){
			foreach ($this->form_arr as $field)
			{
				if (is_array($field['relates_to']))
				{
					$relationsips = true;
					$relate_table  = $field['relates_to']['table'];
					$relate_field_local = $this->module_array['form_prefix'].'_'.$field['name'];
					$relate_field_remote = $field['relates_to']['field'];
					$relate_field_display = $field['relates_to']['display'];
					$relationships = true;
				}
			}
		}
		
		//============================================================
		// 	Start query
		//============================================================
		$query_str = "SELECT ";
		
		//find which fields to include
		if ( $this->entries_arr['include_fields'] != '' )
		{
			$include_fields = explode(',',$this->entries_arr['include_fields']);
			foreach ($include_fields as $field)
			{
				if ( !$this->is_standard_field($field) )
				{
					$fields_to_find .= $this->module_array['form_prefix']."_";
				}
				$fields_to_find .= "o.".trim($field).", ";
			}
			$fields_to_find = substr($fields_to_find,0,-2);
		}
		else
		{
			$fields_to_find = "o.*";
		}
		
		
		//pull in secondary tables
		if ( $relationships )
		{
			$fields_to_find .= ", r.$relate_field_display";
		}
		
		 $query_str .= $fields_to_find . " FROM " . $this->module_table . " AS o";
		 
		
		 //join secondary table
		 if ( $relationships )
		 {
		 	$query_str .= " JOIN $relate_table AS r ON o.$relate_field_local = r.$relate_field_remote";
		 }
		
		if ( is_array($this->entries_arr['criteria']) )
		{
			$query_str .= " WHERE ";
			
			$where_count = 0;
			foreach ($this->entries_arr['criteria'] as $key=>$value)
			{
				if ($where_count > 0 )
				{
					$query_str .= " AND ";
				}
				
				if ( $key == 'id' )
				{
					$field_name = $key;
				}
				else
				{
					$field_name = addslashes($this->module_array['form_prefix']."_".$key);
				}
				
				//=================================================================
				// 	If we find !, <, or > we want to run it as the coder sent it to us, otherwise, we are doing the cleansing and such
				//=================================================================
				if ( strpos($value,'!') !== false || strpos($value,'>') !== false || strpos($value,'<') !== false )
				{
					$query_str .= "o.".$field_name.' '.$value;
				}
				else
				{
					
					$query_str .= "o.".$field_name." = '" . addslashes($value) ."'";
				}
				
				$where_count++;
			}
		}
		
		if ( $this->module_array['reorder'] != '' && $this->entries_arr['order_by'] == '' )
		{
			if ( is_array($this->module_array['reorder']) ){
				$query_str .= " ORDER BY o.".$this->module_array['form_prefix']."_".$this->module_array['reorder'][1].", o." . $this->module_array['form_prefix']."_order";
			}
			else{
				$query_str .= " ORDER BY o." . $this->module_array['form_prefix']."_order";
			}
		} 
		elseif ( $this->entries_arr['order_by'] != '')
		{
			//added by matt to allow ordering by standard fields
			// July 22nd, 2010
			$order_arr = explode(" ", $this->entries_arr['order_by']);
			$order_field = $order_arr[0];
			if ( !$this->is_standard_field($order_field) )
			{
				$query_str .= " ORDER BY o." . $this->module_array['form_prefix']."_".$this->entries_arr['order_by'];
			}
			else
			{
				$query_str .= " ORDER BY o.".$this->entries_arr['order_by'];
			}
		}
		
		if ( $this->entries_arr['limit'] != '' && is_numeric($this->entries_arr['limit']) )
		{
			$query_str .= " LIMIT " . $this->entries_arr['limit'];
		}
		
		//echo $query_str;
		//now time to run the query
		$db = new db;
		$results = $db->qquery($query_str);
		
		//now let's replace the relationship data
		if ( $relationships )
		{
			$results_count = count($results);
			for($i=0; $i<$results_count;$i++)
			{
				//give our original field a place to live like oldfieldname_relatefield
				$results[$i][$relate_field_local.'_'.$relate_field_remote] = $results[$i][$relate_field_local];
				//have the pulled in data replace our original field
				$results[$i][$relate_field_local] = $results[$i][$relate_field_display];
			}
		}
		
		if ( $this->entries_arr['format_results'] !== false )
		{
			$results = $this->format_results($results);
		}
		
		return $results;
	}
	
	// This function was written for a script I wound up not using or even testing, so it may or may not actually work.
	// Use it at your own risk! - Steve
	function count_entries($module_name='')
	{
		$query_str = "SELECT COUNT(*) FROM " . $this->module_table . ";";
		$db = new db;
		$results = $db->qquery($query_str);
		return $results;
	}
	
	function get_module_info($module_name='')
	{
		
		if ( $module_name != '' )
		{
			$this->module_name = $module_name;
		}
		
		/*
		//step 1: find control file
		$query_str = "SELECT * FROM modules WHERE module_admin_action = '".addslashes($this->module_name)."'";
		$db = new db;
		$results = $db->qquery($query_str);

		if (count($results) == 0 )
		{
			$this->error_text = "No module found.";
			return false;
		}
		else
		{
			//open module_def_file
			$module_control_file = $results[0]['module_control_file'];
		}
		*/
		
		//step 2: open control file and push arrays to new attributes
//		$module_dir = str_replace('/control.php','',$module_control_file);
		$module_def_file = ABS_SITE_DIR."/admin/modules/".$this->module_name."/module_def.php";
		if ( is_file($module_def_file) )
		{
			require($module_def_file);
			$this->module_array = $module_array;
			$this->form_arr = $form_arr;
			
			//now some special cases we want to pull out for easy access
			$this->module_table = $this->module_array['form_dest_table'];
						
			return TRUE;
		}
		else
		{
			$this->error_text = "Could not find module control file.";
			return FALSE;
		}
	}
	
	function format_results($results)
	{
		$this->prep_form_array();
		for ( $i=0; $i<count($results); $i++ )
		{
			foreach ($results[$i] as $key=>$value)
			{
				//remove from array if blank
				if ( $value == '' || is_null($value) )
				{
					unset($results[$i][$key]);
				}
				else
				{
					$form_field_name = str_replace($this->module_array['form_prefix']."_",'',$key);
					$input_type = $this->prepped_form_arr[$form_field_name]['input_type'];
					$clean_data = $this->format_data(array('input_type'=>$input_type,'data'=>$value));
					if ( $this->entries_arr['keep_prefix'] !== true )
					{
							unset($results[$i][$key]);
							$results[$i][$form_field_name] = $clean_data;
					}
					else
					{
						$results[$i][$key] = $clean_data;
					}
				}
			}
		}	
		
		return $results;
	}
	
	function prep_form_array()
	{
		$this->prepped_form_arr = array();
		if ( is_array($this->form_arr) ){
			foreach ($this->form_arr as $field )
			{	
				$this->prepped_form_arr[$field['name']] = $field;
			}
		}
	}
	
	function format_data($data)
	{
		$type = $data['input_type'];
		$data = $data['data'];
		
		switch ($type) {
			case 'text':
				return stripslashes($data);
				break;
			case 'textarea':
				return nl2br($data);
				break;
			case 'html':
				return stripslashes($data);
				break;
			default:
				return $data;
		}
	}
	
	function show_datetime_formats()
	{
		print_r($this->format_datetime(array('date'=>'2009-09-09 09:09:09')));
	}
	
	function format_datetime($date_arr)
	{
		$date = $date_arr['datetime'];
		$format = $date_arr['format'];
		$php_format = $date_arr['php_format'];
		
		$dates_array = array();
		$dates_array['mysql_datetime'] = date('Y-m-d H:i:s',strtotime($date));
		$dates_array['formal_date'] = date('F j, Y',strtotime($date));
		$dates_array['slashed_date'] = date('n/j/y',strtotime($date));
		$dates_array['formal_date_no_year'] = date('F j',strtotime($date));
		$dates_array['short_month'] = date('M j, Y',strtotime($date));
		$dates_array['short_month_no_year'] = date('M j',strtotime($date));
		
		foreach ($dates_array as $key=>$value)
		{
			if ( strpos($date,":") !== false && $key != 'mysql_datetime' )
			{
				if ( strpos($date,'00:00:00' ) !== false )
				{
					$dates_array[$key."_with_time"] = $value;
				}
				else
				{
					$dates_array[$key."_with_time"] = $value . " " . date("g:ia",strtotime($date));
					//$dates_array[$key."_with_24_hour_time"] = $value . " " . date("H:i",strtotime($date));
				}
			}
		}
		
		if ( $format != '' )
		{
			return $dates_array[$format];
		}
		elseif ( $php_format != '' )
		{
			return date($php_format,strtotime($date));
		}
		else
		{
			return $dates_array;
		}
		
	}
	
	function is_standard_field($fieldname)
	{
		$standard_fields = array('id','update_datetime','insert_datetime','title');
		if ( in_array($fieldname, $standard_fields) )
		{
			return true;
		}
		else
		{
			return false;
		}
	}
}

?>