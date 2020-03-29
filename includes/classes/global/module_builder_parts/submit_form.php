<?php		//print_r($_REQUEST);

//============================================================
// 
//		function submit_form()
//		{
//
//============================================================

global $module_actions;

//============================================================
// 	RUN before_submit()
//============================================================
if ( method_exists($module_actions,'before_submit') )
{
	$module_actions->before_submit();
}


echo $this->viewing_categories();
$form_arr = $this->module_arr['form_arr'];
for ( $i=0; $i<count($form_arr); $i++ )
{
	
	$full_name = $this->module_arr['form_prefix'].'_'.$form_arr[$i]['name'];
	
	/*IMAGE UPLOAD =================================*/
	if ( $form_arr[$i]['input_type'] == 'image' )
	{
		$field_name = $form_arr[$i]['name'];
		$file_arr = $_FILES[$form_arr[$i]['name']];
								echo $full_name."_placeholder";
		//see if file is blank, if so, skip everything else
		if ( $file_arr['name'] != '' && $file_arr['error'] == 0 )
		{
			//the value on this type of input has the destination directory, and the max side in px separated by commas
			$dest_dir = $form_arr[$i]['dest_dir'];
			$max_side = $form_arr[$i]['max_side'];
			$thumb_size = $form_arr[$i]['thumb_size'];
			
			if ( $thumb_size == '' )
			{
				$thumb_size = '200';
			}


			//see if directory exists, if not, create it
			$abs_dir = ABS_SITE_DIR.$dest_dir;
			
			if ( $max_side == '' )
			{
				$max_side = 'noresize';
			}
			
			//resize and physically upload file
			echo $field_name."-".$abs_dir."-".$max_side."-".$thumb_size;
			$upload = new image_upload($field_name, $abs_dir.'/', $max_side, $thumb_size);
			$file_path = $upload->get_file_path();
			
			//add value to value fields
			$insert_fields[] = $full_name;
			$insert_values[] = $file_path; //path + filename
		}
		//if we are wanting to delete the file
		elseif ( $_REQUEST[$full_name."_placeholder"] == '' )
		{
				//add value to value fields
				$insert_fields[] = $full_name;
				$insert_values[] = ""; //path + filename
		}
	}
	
	/*FILE UPLOAD =================================*/
	elseif ( $form_arr[$i]['input_type']  == 'file' )
	{	
		$field_name = $form_arr[$i]['name'];
		$file_arr = $_FILES[$form_arr[$i]['name']];

		//see if file is blank, if so, skip everything else
		if ( $file_arr['name'] != '' && $file_arr['error'] == 0 )
		{
		
			//the value on this type of input has the destination directory, and the max side in px separated by commas
			$dest_dir = $form_arr[$i]['dest_dir'];
			
			//see if directory exists, if not, create it
			$abs_dir = ABS_SITE_DIR.$dest_dir; //to upload the file
			$rel_dir = $dest_dir; //for the db entry
			
			//put timestamp in filename to not overwrite any files
			$file_name = $file_arr['name'];

			$exp = explode('.',$file_name);		
			
			//============================================================
			// 	This section will see if there are more than one periods in the name and if so, wipe them all except the last
			//============================================================	
			if ( count($exp) > 2 )
			{
				$file_name = '';
				for ($i=0; $i<count($exp); $i++ )
				{
					if ( $i != (count($exp) - 1) )
					{
						$file_name .= $exp[$i];
					}
					else
					{
						$file_name .= ".".$exp[$i];
					}
				}
			}
			//============================================================
			// 	Moving on.
			//============================================================
			
			$exp = explode('.',$file_name);
			
			$file_name = strtolower(str_replace(' ','_',$exp[0]).time().'.'.$exp[1]);
			
			//resize and physically upload file
			if (move_uploaded_file($file_arr["tmp_name"],$abs_dir."/".$file_name)) 
			{
				//add value to value fields
				$insert_fields[] = $full_name;
				$insert_values[] = $rel_dir."/".$file_name; //path + filename
			}
		}
		//if we are wanting to delete the file
		elseif ( $_REQUEST[$full_name."_placeholder"] == '' )
		{
				//add value to value fields
				$insert_fields[] = $full_name;
				$insert_values[] = ""; //path + filename
		}
	}
	
	//DATE DROP DOWNS =============================
	elseif ( $form_arr[$i]['input_type'] == 'date' )
	{
		$insert_fields[] = $full_name;
		$len = strlen($_REQUEST['prefix'])+1;	//find length of prefix to chop off
		
		if ( $_REQUEST[$full_name."_hour"] != '' )
		{ //figure out if time exists or not 
			$time = ' '.$_REQUEST[$full_name."_hour"].':'.$_REQUEST[$full_name."_minute"].$_REQUEST[$full_name."_ampm"];
		}
		else
		{
			$time = '';
		}
		$date = $_REQUEST[$full_name."_year"].'-'.$_REQUEST[$full_name."_month"].'-'.$_REQUEST[$full_name."_day"];
		$insert_values[] = date('Y-m-d H:i:s',strtotime($date.$time)); //build datetime
	}
	
	//DATE TEXT ====================================
	elseif ( $form_arr[$i]['input_type'] == 'datetext' )
	{
		$insert_fields[] = $full_name;
		if ( $_REQUEST[$full_name] != '' )
		{
			$insert_values[] = date('Y-m-d H:i:s',strtotime($_REQUEST[$full_name]));
		}
		else
		{
			$insert_values[] = '';
		}
	}
	/*HEADERS AND DONT CREATE'S=================================*/
	elseif ( $form_arr[$i]['input_type'] == 'header' || $form_arr[$i]['input_type'] == 'insert_html' || $form_arr[$i]['input_type'] == 'require_file' || $form_arr[$i]['ignore_on_submit'] != '' )
	{
		//DO NOTHING
	}
	//CUSTOM ==================================
	elseif ( $form_arr[$i]['input_type'] == 'custom' && $form_arr[$i]['placement'] == 'before_query')
	{
		if ( $form_arr[$i]['data_type'] == 'code' )
		{
			eval($form_arr[$i]['code']);
		}
	}
	/*EVERYTHING ELSE =================================*/
	else
	{
		$insert_fields[] = $full_name;
		$insert_values[] = $_REQUEST[$full_name];								
	}
	
	if ( $form_arr[$i]['urlify'] ){
		$insert_fields[] = $full_name.'_url';
		$special_chars = array("?", "[", "]", "/", "\\", "=", "<", ">", ":", ";", ",", "'", "\"", "&", "$", "#", "*", "(", ")", "|", "~", "`", "!", "{", "}");
	    $val = str_replace($special_chars, '', strtolower($_REQUEST[$full_name]));
   		$val = preg_replace('/[\s-]+/', '-', $val);
   		$insert_values[] = $val;
	}
}

//create a few extra fields
/*
$insert_fields[] = 'ip_address';
$insert_values[] = $_SERVER['REMOTE_ADDR'];
$create_fields[] = 'ip_address VARCHAR(255)';
*/

$insert_fields[] = 'update_datetime';
$insert_values[] = date("Y-m-d H:i:s");

if ( $_REQUEST['id'] == '' )  // we only want this updated if we are creating a new record
{
	$insert_fields[] = 'insert_datetime';
	$insert_values[] = date("Y-m-d H:i:s");
}


//============================================================
// 	CONTENT PUBLISH AND EXPIRATION DATES
//============================================================
if ( $this->module_arr['content_expiration_date'] === true ) //expiration
{
	$insert_fields[] = 'content_expiration_date';
	
	//============================================================
	// 	If blank, set fields to 0000-00-00 00:00:00
	//============================================================
	if ( $_REQUEST['content_expiration_date'] == '' || $_REQUEST['content_expiration_date'] == '0000-00-00' )
	{
		$_REQUEST['content_expiration_date'] = '0000-00-00';
		$_REQUEST['content_expiration_time'] = '00:00:00';
	}
	
	$exp_date_time = $_REQUEST['content_expiration_date'] . ' ' . $_REQUEST['content_expiration_time'];
	
	echo "\n\nHEY$exp_date_time\n\n";
		
	//============================================================
	// 	We don't want to use date() if we are just inserting zeros
	//============================================================
	if ( $exp_date_time == '0000-00-00 00:00:00' )
	{
		$exp_date_time_format = $exp_date_time;
	}
	else
	{
		$exp_date_time_format = date("Y-m-d H:i:s",strtotime($exp_date_time));
	}
	$insert_values[] = $exp_date_time_format;
}

if ( $this->module_arr['content_publish_date'] === true ) //expiration
{
	$insert_fields[] = 'content_publish_date';
	
	
	//============================================================
	// 	If blank, set fields to 0000-00-00 00:00:00
	//============================================================
	if ( $_REQUEST['content_publish_date'] == '' || $_REQUEST['content_publish_date'] == '0000-00-00' )
	{
		$_REQUEST['content_publish_date'] = '0000-00-00';
		$_REQUEST['content_publish_time'] = '00:00:00';
	}
	
	$pub_date_time = $_REQUEST['content_publish_date'] . ' ' . $_REQUEST['content_publish_time'];
	
	//============================================================
	// 	We don't want to use date() if we are just inserting zeros
	//============================================================
	if ( $pub_date_time == '0000-00-00 00:00:00' )
	{
		$pub_date_time_format = $pub_date_time;
	}
	else
	{
		$pub_date_time_format = date("Y-m-d H:i:s",strtotime($pub_date_time));
	}
	
	$insert_values[] = $pub_date_time_format;
}



//============================================================
//  CHECK TO SEE IF WE'RE ADDING OR EDITING
//============================================================

if ( ($_REQUEST['id'] == '' && $_REQUEST['category_id'] =='' ) || $_REQUEST['category_id'] == 'new')
{

	if ( $this->module_arr['publish'] )
	{
		//==========================================
		//		IF WE'RE PUBLISHING, WE NEED TO GET THE NEXT RECORD
		//==========================================
		$max_query_str = "SELECT MAX(record_id) FROM ".$this->module_arr['form_dest_table']."_edited";
		echo $max_query_str;
		$db = new db($this->module_arr['db']);
		$results = $db->qquery($max_query_str);
		$last_id = $results[0]['MAX(record_id)'];
		$next_id = $last_id + 1;
		
		array_unshift($insert_fields,'record_id');
		array_unshift($insert_values,$next_id);
	}
	

	//insert record
	$query_str = "INSERT INTO " . $_REQUEST['t'] . " ( ";
	for ( $i=0; $i<count($insert_fields); $i++)
	{
		if ( $i!=0)
		{
			$query_str.= ", ";
		}
		
		$query_str .= $insert_fields[$i];
	}
	
	$query_str .= " ) VALUES ( ";
	
	for ($i=0; $i<count($insert_values); $i++ )
	{
		if ( $i!=0)
		{
			$query_str .= ", ";
		}
		$query_str .= "'".addslashes($insert_values[$i])."'";
	}
	
	$query_str .= " )";
	
	$db = new db($this->module_arr['db']);
	$db->set_query_str($query_str);
	echo "<br><BR>".$query_str;
	$db->db_query();
	
	$admin_message = "New entry added successfully!";
	$submit_success = 1;
}
else //if there is an ID we will be updating
{
	//update record
	$query_str = "UPDATE " . $_REQUEST['t'] . " SET ";
	for ( $i=0; $i<count($insert_fields); $i++ )
	{
		if ( $i!=0)
		{
			$query_str.= ", ";
		}
		
		$query_str .= $insert_fields[$i] . "='".addslashes($insert_values[$i])."'";
	}
	
	if ( $this->viewing_categories() )
	{
		$id_field = $this->module_arr['form_prefix'].'_id';
		$id_request_var = 'category_id';
	}
	elseif ( $this->module_arr['publish'] )
	{
		$id_field = 'record_id';
		$id_request_var = 'id';
	}
	else
	{
		$id_field = 'id';
		$id_request_var = 'id';
	}
	
	$query_str .= " WHERE $id_field =".$_REQUEST[$id_request_var];
	
	echo $query_str;
	$db = new db($this->module_arr['db']);
	$db->set_query_str($query_str);
	echo "<br><BR>".$query_str;
	$db->db_query();						
}

if ( $this->module_arr['single_entry'] == true ){
	echo "Entry Updated Successfully.  Reloading page...";
	echo "<script language='javascript'>setTimeout(\"window.location='./?admin_action=".$this->module_arr['admin_action']."'\",2000)</script>";
} else {
	$admin_message .= "Entry updated successfully!";
}

//============================================================
// 	RUN after_submit()
//============================================================
if ( method_exists($module_actions,'after_submit') )
{
	$module_actions->after_submit();
}


return true;

//============================================================
// 	}
//============================================================
?>
