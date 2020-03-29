<?php
/*
search phrases
	
	- itemlist
	- showform
	- submitform
	- createtable

*/

require_once('module_builder_parts/relationships.php');

class module
{
	
	function module($module_arr = array())
	{
		$relationships = new relationships;
		
		if ( count($module_arr) > 0 )
		{
			require_once(THE_GUTS_DIR."/includes/classes/global/image_upload.php");
			
			$this->module_arr = $module_arr;
			
			//to compensate for if a db has not been set.  sets default to 0 so we know we're looking at the actual site database
			if ( $this->module_arr['db'] == '' )
			{
				$this->module_arr['db'] = 0;
			}
			
			//this runs each time to make sure that the directories are created for images and files
			$this->initialize_module();
			
			//set up request var real quick so we go to list by default
			if ($_REQUEST['admin_subaction'] == '' )
			{
				$_REQUEST['admin_subaction'] = 'index';
			}
			
			//once the table is created, go ahead and run script
			//read this way: if we are directly told to edit OR if this is a single entry module and we currently aren't submitting, then we show the form
			if ( $_REQUEST['admin_subaction'] == 'edit' || ($this->module_arr['single_entry'] == true && $_REQUEST['admin_subaction'] != 'submit' ) )
			{
				$this->initialize_categories();
				//show the form
				$this->display_form();
			}
			//else if we are distinctly told that we are submitting the form, 
			elseif ( $_REQUEST['admin_subaction'] == 'submit' )
			{
				echo "Submitted!";
				$this->initialize_categories();
				//submit the form
				$this->submit_form();
			}
			//else, if we are distinctly told to delete, do so
			elseif ( $_REQUEST['admin_subaction'] == 'delete' )
			{
				$this->initialize_categories();
				$this->delete_entries();
			}
			//else, if we are distinctly told to delete, do so
			elseif ( $_REQUEST['admin_subaction'] == 'publish' )
			{
				//$this->initialize_categories();
				$this->publish();
			}
			//else, if we are told to reorder the list, now's the time to shine
			elseif ( $_REQUEST['admin_subaction'] == 'reorder' )
			{
				$this->initialize_categories();
				$this->reorder_entries();
				$this->show_item_list();
			}
			//If we're to this point, this basically means we're going to the home screen, which is by default a list... 
			// this is the point at which if we want to override the interface but use all of the hooks, we need to let it know.
			elseif ( $_REQUEST['admin_subaction'] == 'index')
			{
				$this->initialize_categories();
				$this->show_item_list();
			}	
		}
	}
	

	/*=-=-=-=-=-=-=-=-=-=-=-=--=-=-=-=-=-=-=-=-=-=-=-=--=-=-=-=-=-=-=-=-=-=-=-=--=-=-=-=-=-=-=-=-=-=-=-=--=
	=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=--=-=-=-=-=-=-=-=-=-=-=-=--=-=-=-=-=-=-=-=-=-=-=-=--=
	=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=--=-=-=-=-=-=-=-=-=-=-=-=--=-=-=-=-=-=-=-=-=-=-=-=--=
		
		FUNCTION DISPLAY FORM
		
	=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=--=-=-=-=-=-=-=-=-=-=-=-=--=-=-=-=-=-=-=-=-=-=-=-=--=
	=-=-=-=-=-=-=-=---=-=-=-=-=--=-=-=-=-=-=-=-=-=-=-=--=-=-=-=-=-=-=-=-=-=-=-=--=-=-=-=-=-=-=-=-=-=-=-=--=
	=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=--=-=-=-=-=-=-=-=-=-=-=-=--=-=-=-=-=-=-=-=-=-=-=-=--=-=-*/
	
	function display_form()
	{
		$relationships = new relationships;
		//============================================================
		// 	REQURE DISPLAY FORM
		//============================================================
		require_once('module_builder_parts/display_form.php');
	}
	
			
		
	/*=-=-=-=-=-=-=-=-=-=-=-=--=-=-=-=-=-=-=-=-=-=-=-=--=-=-=-=-=-=-=-=-=-=-=-=--=-=-=-=-=-=-=-=-=-=-=-=--=
	=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=--=-=-=-=-=-=-=-=-=-=-=-=--=-=-=-=-=-=-=-=-=-=-=-=--=
	=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=--=-=-=-=-=-=-=-=-=-=-=-=--=-=-=-=-=-=-=-=-=-=-=-=--=
		
		FUNCTION SUBMIT FORM
		submitform
		
	=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=--=-=-=-=-=-=-=-=-=-=-=-=--=-=-=-=-=-=-=-=-=-=-=-=--=
	=-=-=-=-=-=-=-=---=-=-=-=-=--=-=-=-=-=-=-=-=-=-=-=--=-=-=-=-=-=-=-=-=-=-=-=--=-=-=-=-=-=-=-=-=-=-=-=--=
	=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=--=-=-=-=-=-=-=-=-=-=-=-=--=-=-=-=-=-=-=-=-=-=-=-=--=-=-*/
	
	function submit_form()
	{
		$relationships = new relationships;
		require('module_builder_parts/submit_form.php');
	}
	
	/*=-=-=-=-=-=-=-=-=-=-=-=--=-=-=-=-=-=-=-=-=-=-=-=--=-=-=-=-=-=-=-=-=-=-=-=--=-=-=-=-=-=-=-=-=-=-=-=--=
	=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=--=-=-=-=-=-=-=-=-=-=-=-=--=-=-=-=-=-=-=-=-=-=-=-=--=
	=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=--=-=-=-=-=-=-=-=-=-=-=-=--=-=-=-=-=-=-=-=-=-=-=-=--=
		
		FUNCTION REORDER ENTRIES (reorder list)
		
	=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=--=-=-=-=-=-=-=-=-=-=-=-=--=-=-=-=-=-=-=-=-=-=-=-=--=
	=-=-=-=-=-=-=-=---=-=-=-=-=--=-=-=-=-=-=-=-=-=-=-=--=-=-=-=-=-=-=-=-=-=-=-=--=-=-=-=-=-=-=-=-=-=-=-=--=
	=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=--=-=-=-=-=-=-=-=-=-=-=-=--=-=-=-=-=-=-=-=-=-=-=-=--=-=-*/
	function reorder_entries()
	{
		$relationships = new relationships;
		
		$db = new db($this->module_arr['db']);
		for ( $i=0; $i<count($_REQUEST['item']); $i++)
		{
			$order = $i + 1;
			
			if ( $this->viewing_categories() )
			{
				$query_str = "UPDATE " . $this->module_arr['form_dest_table'] . " SET  ". $this->module_arr['form_prefix']."_order =$order WHERE ".$this->module_arr['form_prefix']."_id = " . $_REQUEST['item'][$i];
				$db->set_query_str($query_str);
				$db->db_query();
			}
			elseif ( $this->module_arr['publish'] )
			{
				$query_str = "UPDATE " . $this->module_arr['form_dest_table'] . " SET  ". $this->module_arr['form_prefix']."_order =$order WHERE id = " . $_REQUEST['item'][$i];
				$db->set_query_str($query_str);
				$db->db_query();
				$query_str = "UPDATE " . $this->module_arr['form_dest_table']."_edited SET  ". $this->module_arr['form_prefix']."_order =$order WHERE record_id = " . $_REQUEST['item'][$i];
				$db->set_query_str($query_str);
				$db->db_query();
			}
			else
			{
				$query_str = "UPDATE " . $this->module_arr['form_dest_table'] . " SET  ". $this->module_arr['form_prefix']."_order =$order WHERE id = " . $_REQUEST['item'][$i];
				$db->set_query_str($query_str);
				$db->db_query();
			}
			
		}
		
		return true;
	}
	
	/*=-=-=-=-=-=-=-=-=-=-=-=--=-=-=-=-=-=-=-=-=-=-=-=--=-=-=-=-=-=-=-=-=-=-=-=--=-=-=-=-=-=-=-=-=-=-=-=--=
	=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=--=-=-=-=-=-=-=-=-=-=-=-=--=-=-=-=-=-=-=-=-=-=-=-=--=
	=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=--=-=-=-=-=-=-=-=-=-=-=-=--=-=-=-=-=-=-=-=-=-=-=-=--=
		
		FUNCTION DELETE ENTRIES
		
	=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=--=-=-=-=-=-=-=-=-=-=-=-=--=-=-=-=-=-=-=-=-=-=-=-=--=
	=-=-=-=-=-=-=-=---=-=-=-=-=--=-=-=-=-=-=-=-=-=-=-=--=-=-=-=-=-=-=-=-=-=-=-=--=-=-=-=-=-=-=-=-=-=-=-=--=
	=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=--=-=-=-=-=-=-=-=-=-=-=-=--=-=-=-=-=-=-=-=-=-=-=-=--=-=-*/
	function delete_entries()
	{
		global $module_actions;
		
		//============================================================
		// 	RUN before_delete()
		//============================================================
		if ( method_exists($module_actions,'before_delete') )
		{
			$module_actions->before_delete();
		}

		
		$relationships = new relationships;
		
		if ( $_REQUEST['category_id'] != '' )
		{
			//delete the listing from the categories
			$query_str = "DELETE FROM " . $this->module_arr['form_dest_table'] . " WHERE ".$this->module_arr['form_prefix']."_id=".$_REQUEST['category_id'];
			echo $query_str."\n\n\n";
			$db = new db($this->module_arr['db']);
			$db->set_query_str($query_str);
			$db->db_query();
			
			//now delete all entries from that category list
			$query_str = "DELETE FROM " . $this->module_arr['orig_table'] . " WHERE ".$this->module_arr['orig_prefix']."_".$this->module_arr['form_prefix']." = ".$_REQUEST['category_id'];
			echo $query_str;
			$db = new db($this->module_arr['db']);
			$db->set_query_str($query_str);
			$db->db_query();
		}
		
		if ( $_REQUEST['id'] != '' )
		{
			$query_str = "DELETE FROM " . $this->module_arr['form_dest_table'] . " WHERE id=".$_REQUEST['id'];
			$db = new db($this->module_arr['db']);
			$db->set_query_str($query_str);
			$db->db_query();
			
			
			//===================================
			// kill all edited versions as well
			//===================================
			if ( $this->module_arr['publish'] )
			{
				$query_str = "DELETE FROM " . $this->module_arr['form_dest_table']."_edited WHERE record_id=".$_REQUEST['id'];
				$db = new db($this->module_arr['db']);
				$db->set_query_str($query_str);
				$db->db_query();	
			}
		}
		
		//============================================================
		// 	RUN after_delete()
		//============================================================		
		if ( method_exists($module_actions,'after_delete') )
		{
			$module_actions->after_delete();
		}

		
		return true;
	}
	
	
	
	/*=-=-=-=-=-=-=-=-=-=-=-=--=-=-=-=-=-=-=-=-=-=-=-=--=-=-=-=-=-=-=-=-=-=-=-=--=-=-=-=-=-=-=-=-=-=-=-=--=
	=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=--=-=-=-=-=-=-=-=-=-=-=-=--=-=-=-=-=-=-=-=-=-=-=-=--=
	=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=--=-=-=-=-=-=-=-=-=-=-=-=--=-=-=-=-=-=-=-=-=-=-=-=--=
		
		FUNCTION SHOW ITEM LIST (show list, display list)
		
		
		Change Log:
		11-19-08: Ryan adding functionality for categories
						
	=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=--=-=-=-=-=-=-=-=-=-=-=-=--=-=-=-=-=-=-=-=-=-=-=-=--=
	=-=-=-=-=-=-=-=---=-=-=-=-=--=-=-=-=-=-=-=-=-=-=-=--=-=-=-=-=-=-=-=-=-=-=-=--=-=-=-=-=-=-=-=-=-=-=-=--=
	=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=--=-=-=-=-=-=-=-=-=-=-=-=--=-=-=-=-=-=-=-=-=-=-=-=--=-=-*/
	function show_item_list()
	{
		$relationships = new relationships;
		require('module_builder_parts/show_list.php');
	}
	
	
	
	
	function format_page_name($page_name)
	{	
		// start by setting page name to be lowercase
		$page_name = strtolower(stripslashes($page_name));
		
		//turn spaces into dashes
		$page_name = str_replace(' ','-',$page_name);
		
		//take out apostrophes
		$page_name = str_replace("'","",$page_name);
		
		//take out double quotes
		$page_name = str_replace('"','',$page_name);
		
		//replace & with and
		$page_name = str_replace('&','and',$page_name);
		
		/* move on by checking for bad characters */
		$bad_chars = array('~','!','@','#','$','%','^','*','(',')','{','}','[',']','/','?','.',',','<','>');
		
		foreach ( $bad_chars as $key=>$value )
		{
			$page_name = str_replace($value,'',$page_name);
		}
		
		return $page_name;
	}
	
	
	//checks to see if tables do exists
	function table_exists($table_name)
	{
		$db = new db($this->module_arr['db']);
		$query_str = "SHOW TABLES";
		$db->set_query_str($query_str);
		$db->db_query();
		$results = $db->get_results();
		for ( $i=0; $i<count($results); $i++ )
		{
			$tables[] = $results[$i]['Tables_in_'.DB_NAME];
		}
		
		if ( !in_array($table_name,$tables) ) 
		{
			return;
		}
		
		return true;
	}
	
	
	/*=-=-=-=-=-=-=-=-=-=-=-=--=-=-=-=-=-=-=-=-=-=-=-=--=-=-=-=-=-=-=-=-=-=-=-=--=-=-=-=-=-=-=-=-=-=-=-=--=
	=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=--=-=-=-=-=-=-=-=-=-=-=-=--=-=-=-=-=-=-=-=-=-=-=-=--=
	=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=--=-=-=-=-=-=-=-=-=-=-=-=--=-=-=-=-=-=-=-=-=-=-=-=--=
		
		FUNCTION INITIALIZE MODULE
						
	=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=--=-=-=-=-=-=-=-=-=-=-=-=--=-=-=-=-=-=-=-=-=-=-=-=--=
	=-=-=-=-=-=-=-=---=-=-=-=-=--=-=-=-=-=-=-=-=-=-=-=--=-=-=-=-=-=-=-=-=-=-=-=--=-=-=-=-=-=-=-=-=-=-=-=--=
	=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=--=-=-=-=-=-=-=-=-=-=-=-=--=-=-=-=-=-=-=-=-=-=-=-=--=-=-*/
	function initialize_module()
	{
		//============================================================
		// 	MAIN TABLE START
		//		This is happening in 2 parts.  Part 1 is creating the regular table while part 2 is creating 
		//		the table for holding edited versions of the records if necessary
		//============================================================
		
		if ( $this->module_arr['publish'] )
		{
			$tables = array($this->module_arr['form_dest_table'],$this->module_arr['form_dest_table']."_edited");
		}
		else
		{
			$tables = array($this->module_arr['form_dest_table']);
		}
		
		$form_arr = $this->module_arr['form_arr'];
		
		for ( $z=0; $z<count($tables); $z++ )
		{
			$db = new db;
			$created_query_str = "SHOW TABLES LIKE '".$tables[$z]."'";
			$results = $db->qquery($created_query_str);
			if ( $table_count == 0 && count($results) == 0 )
			{
				//============================================================
				// CREATE TABLE
				//============================================================
				
				//start the blank array
				$create_fields = array();
						
				for ( $i=0; $i<count($form_arr); $i++ )
				{
					
					//full name consists of form prefix and table name
					$full_name = $this->module_arr['form_prefix'].'_'.$form_arr[$i]['name'];
					
					//run this through the input type machine
					if ( $this->process_input_types($full_name, $form_arr[$i]) != 'SKIP' )
					{
						$create_fields[] = $this->process_input_types($full_name, $form_arr[$i]);
						if ( $form_arr[$i]['urlify'] ){
							$create_fields[] = $full_name.'_url VARCHAR(255)';
						}
					}
					
				}
				
				//============================================================
				//	 Allow for reordering
				//============================================================
				if ( $this->module_arr['reorder'] )
				{
					$create_fields[] = $this->module_arr['form_prefix'].'_order INT(11)';
				}
						
						
				//============================================================
				//		Check for content expiration and creation dates
				//============================================================		
				if ( $this->module_arr['content_publish_date'] === true ) //publish
				{
					$create_fields[] = 'content_publish_date DATETIME';
				}
				if ( $this->module_arr['content_expiration_date'] === true ) //expiration
				{
					$create_fields[] = 'content_expiration_date DATETIME';
				}
				
				
				
				
				$create_fields[] = 'update_datetime VARCHAR(255)';
				$create_fields[] = 'insert_datetime VARCHAR(255)';
				
				
				//==========================================================
				//		 IF WE'RE GOING TO PUBLISH WE DON'T WANT IT TO AUTO INCREMENT THE MAIN TABLE
				//==========================================================
				if ( count($tables) > 1 && $z == 0 )
				{
					$auto_increment = '';
				}
				else
				{
					$auto_increment = 'AUTO_INCREMENT PRIMARY KEY';
				}
				
				//now let's run the query
				$query_str = "CREATE TABLE IF NOT EXISTS `" . $tables[$z] . "` ( id INT NOT NULL ".$auto_increment.", ";
				
				
				//we need to add a field for publishing record
				if ( $z > 0 ) //we know that the first table is always going to be the original so if we see that it's gone more than once around, we're now in the editing table
				{
					$query_str .= "record_id INT, ";
				}
								
				for ( $i=0; $i<count($create_fields); $i++ )
				{
					if ( $create_fields[$i] != 'SKIP' ) //the headers don't get put in so we put the work SKIP as the variable returned
					{
						if ( $i != 0 )
						{
							$query_str .= ", ";
						}
						
						$query_str .= $create_fields[$i];
					}
				}
				
				$query_str .= ")";
				
				$db = new db($this->module_arr['db']);
				$db->set_query_str($query_str);
				$db->db_query();
			}					
			else
			{
				//============================================================
				// 	SEE IF WE NEED TO ALTER TABLE
				//============================================================
				$query_str = "DESCRIBE " . $tables[$z];
				$db = new db;
				$results = $db->qquery($query_str);
				$current_fields = array();
				
				for ( $i=0; $i<count($results); $i++ )
				{
					$current_fields[] = $results[$i]['Field'];
				}
				
				for ( $i=0; $i<count($form_arr); $i++ )
				{	
					//full name consists of form prefix and table name
					$full_name = $this->module_arr['form_prefix'].'_'.$form_arr[$i]['name'];
					
					//run this through the input type machine
					if ( $this->process_input_types($full_name, $form_arr[$i]) != 'SKIP')
					{
						$field_name_and_type = $this->process_input_types($full_name, $form_arr[$i]);

						if ( $form_arr[$i]['change'] != '' )
						{
							$change_name = $this->module_arr['form_prefix'].'_'.$form_arr[$i]['change'];
							$query_str = "ALTER TABLE ".$tables[$z]." CHANGE $change_name " . $field_name_and_type;
							$db->set_query_str($query_str);
							$db->db_query();
						}
						elseif ( $form_arr[$i]['drop'] != '' )
						{
							$query_str = "ALTER TABLE ".$tables[$z]." DROP COLUMN $full_name";
							$db->set_query_str($query_str);
							$db->db_query();
						}
						elseif (  !in_array($full_name, $current_fields) )
						{
							$query_str = "ALTER TABLE ".$tables[$z]." ADD $field_name_and_type"; // .$ AFTER $last_full_name";
							$db->set_query_str($query_str);
							$db->db_query();
						}
					}
					
					if ( $form_arr[$i]['urlify'] && !in_array($full_name.'_url',$current_fields)){
						$query_str = "ALTER TABLE ".$tables[$z]." ADD ".$full_name."_url VARCHAR(255)"; // .$ AFTER $last_full_name";
						$db->set_query_str($query_str);
						$db->db_query();
					}
					
					$last_full_name = $full_name;
					
				}
			}
		}
		
		//============================================================
		// 	MAIN TABLE END
		//		CATEGORY TABLE START
		//============================================================
		if ( $this->module_arr['categories'] )
		{
		
			$create_fields = array();
			
			//run through the fields and create the string
			for ( $i=0; $i<count($this->module_arr['category_data']['category_fields']); $i++ )
			{
				$full_name = $this->module_arr['category_data']['form_prefix'].'_'.$this->module_arr['category_data']['category_fields'][$i]['name'];
				
				//add field
				$create_fields[] = $this->process_input_types($full_name, $this->module_arr['category_data']['category_fields'][$i]);
			}
			
			if ( $this->module_arr['category_data']['reorder'] )
			{
				$create_fields[] = $this->module_arr['category_data']['form_prefix'].'_order INT(11)';
			}
			
			
			// add time fields
			$create_fields[] = 'update_datetime VARCHAR(255)';
			$create_fields[] = 'insert_datetime VARCHAR(255)';
			
			//create table
			$query_str = "CREATE TABLE IF NOT EXISTS `". $this->module_arr['category_data']['form_dest_table'] . "` ( ".$this->module_arr['category_data']['form_prefix']."_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY, ";
			
			for ( $i=0; $i<count($create_fields); $i++ )
			{
				if ( $create_fields[$i] != 'SKIP' ) //the headers don't get put in so we put the work SKIP as the variable returned
				{
					if ( $i != 0 )
					{
						$query_str .= ", ";
					}
					
					$query_str .= $create_fields[$i];
				}
			}

			$query_str .= ")";
			
			$db = new db($this->module_arr['db']);
			$db->set_query_str($query_str);
			$db->db_query();
		}
	}
	
	/*=-=-=-=-=-=-=-=-=-=-=-=--=-=-=-=-=-=-=-=-=-=-=-=--=-=-=-=-=-=-=-=-=-=-=-=--=-=-=-=-=-=-=-=-=-=-=-=--=
	=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=--=-=-=-=-=-=-=-=-=-=-=-=--=-=-=-=-=-=-=-=-=-=-=-=--=
	=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=--=-=-=-=-=-=-=-=-=-=-=-=--=-=-=-=-=-=-=-=-=-=-=-=--=
		
		FUNCTION PROCESS INPUT TYPES
		
		Created: 11-19-08 by Ryan
		
		This function will take an input array and spit back the mysql command necessary to make it happen
						
	=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=--=-=-=-=-=-=-=-=-=-=-=-=--=-=-=-=-=-=-=-=-=-=-=-=--=
	=-=-=-=-=-=-=-=---=-=-=-=-=--=-=-=-=-=-=-=-=-=-=-=--=-=-=-=-=-=-=-=-=-=-=-=--=-=-=-=-=-=-=-=-=-=-=-=--=
	=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=--=-=-=-=-=-=-=-=-=-=-=-=--=-=-=-=-=-=-=-=-=-=-=-=--=-=-*/
	
	function process_input_types($full_name, $input)
	{

		
		/*IMAGE UPLOAD =================================*/
		if ( $input['input_type'] == 'image' )
		{
			$create_field = $full_name . ' varchar(255)';
				
			//see if directory exists, if not, create it
			$dest_dir = $input['dest_dir'];
			$abs_dir = ABS_SITE_DIR.$dest_dir;
			if ( !is_dir($abs_dir) )
			{
				//create directory
				mkdir($abs_dir);
				//create thumbs directory
				mkdir($abs_dir."/thumbs");
			}
		}
		
		/*FILE UPLOAD =================================*/
		elseif ( $input['input_type']  == 'file' )
		{	
			$create_field = $full_name . ' varchar(255)';
			
			//let's make sure the folder exists
			$dest_dir = $input['dest_dir'];
			$abs_dir = ABS_SITE_DIR.$dest_dir; //to upload the file
				
			if ( !is_dir($abs_dir) )
			{
				//create directory
				mkdir($abs_dir);
			}
		}
		
		//DATE DROP DOWNS =============================
		elseif ( $input['input_type'] == 'date' )
		{
			$create_field = $full_name . ' datetime';
		}
		
		//DATE TEXT ====================================
		elseif ( $input['input_type'] == 'datetext' )
		{
			$create_field = $full_name . ' datetime';
		}
		
		//CHECKBOX ====================================
		elseif ( $input['input_type'] == 'checkbox' )
		{
			$create_field = $full_name . ' VARCHAR(255)';
		}
		
		//HEADER====================================
		elseif ( $input['input_type'] == 'header' || $input['ignore_on_submit'] != '' || $input['input_type'] == 'insert_html' || $input['input_type'] == 'require_file' )
		{
			$create_field = 'SKIP';
		}
		
		/*EVERYTHING ELSE =================================*/
		else
		{
			$create_field = $full_name . ' ' .$input['mysql_field'];			
		}
		
		return $create_field;
		
	}
	
	function get_entries_count()
	{
		$query_str = "SELECT * FROM " . $this->module_arr['form_dest_table'];
		$db = new db($this->module_arr['db']);
		$results = $db->qquery($query_str);
		return count($results);
	}
	
	function form_has_upload()
	{
		$form_arr = $this->module_arr['form_arr'];
		for ($i=0; $i<count($form_arr); $i++ )
		{
			if ( $form_arr[$i]['input_type']=='file' || $form_arr[$i]['input_type']=='image' )
			{
				return true;
			}
		}
		
		return;
	}
	
	function get_categories()
	{
		$query_str = "SELECT * FROM " . $this->module_arr['form_dest_table'] . "_categories";
		$db = new db($this->module_arr['db']);
		$results = $db->qquery($query_str);
		return $results;
	}
	
	function viewing_categories()
	{
		
		if ( $this->module_arr['categories'] )
		{
			if ( $_REQUEST['admin_subaction'] == '' && $_REQUEST['category_id'] == '' )
			{
				return true;
			}
			
			if ( $_REQUEST['admin_subaction'] == 'edit' && $_REQUEST['category_id'] != '' )
			{
				return true;
			}
			
			if ( $_REQUEST['admin_subaction'] == 'submit' && $_REQUEST['category_id'] != '' )
			{
				return true;
			}
			
			if ( $_REQUEST['admin_subaction'] == 'reorder' && $_REQUEST['categories'] != '' )
			{
				return true;
			}
			
			if ( $_REQUEST['admin_subaction'] == 'delete' && $_REQUEST['category_id'] != '' )
			{
				return true;
			}
		}
		
		return;
	}
	
	function initialize_categories()
	{
		if ( $this->viewing_categories() )
		{
			$this->module_arr['orig_table'] = $this->module_arr['form_dest_table'];
			$this->module_arr['orig_prefix'] = $this->module_arr['form_prefix'];
			$this->module_arr['form_dest_table'] = $this->module_arr['category_data']['form_dest_table'];
			$this->module_arr['single_entry'] = '';
			$this->module_arr['form_prefix'] = $this->module_arr['category_data']['form_prefix'];
			$this->module_arr['form_arr'] = $this->module_arr['category_data']['category_fields'];
			$this->module_arr['reorder'] = $this->module_arr['category_data']['reorder'];
		}
	}
	
	/*==-=-=-==-=-=-=-=-=-=-==-=-=-=-=-=-=-=-=-=-=-=--=-=
	//		Yo, This function will look at the _edited tables and see if the published table
	//		has a newer version... if so, return true
	==-=-=-==-=-=-=-=-=-=-==-=-=-=-=-=-=-=-=-=-=-=--=-=*/
	function is_published($id)
	{
		$db = new db($this->module_arr['db']);
		$query_str = "SELECT * FROM " . str_replace("_edited","",$this->module_arr['form_dest_table']) . " WHERE id = $id";
		$pub_results = $db->qquery($query_str);
		
		$query_str = "SELECT * FROM " . $this->module_arr['form_dest_table']." WHERE record_id = $id ORDER BY update_datetime DESC LIMIT 1";
		$edit_results = $db->qquery($query_str);
		
		if ( $pub_results[0]['update_datetime'] > $edit_results[0]['update_datetime'] )
		{
			return true;
		}
		
		return false;
		
	}
	
	function publish()
	{
		
		$id = $_REQUEST['id'];
		
		global $module_actions;
		
		//============================================================
		// 	RUN before_publish()
		//============================================================
		if ( method_exists($module_actions,'before_publish()') )
		{
			$module_actions->before_publish();
		}

		
		$query_str = "SELECT * FROM " . $this->module_arr['form_dest_table']."_edited WHERE record_id = $id ORDER BY update_datetime DESC LIMIT 1";
		$db = new db($this->module_arr['db']);
		$results = $db->qquery($query_str);
		$results = $results[0];
		
		$insert_fields_str;
		$insert_values_str;
		
		foreach ( $results as $key=>$value )
		{
			if ( $key != 'id' && $key != 'update_datetime' && $key != 'insert_datetime' )
			{
				if ( $key == 'record_id' )
				{
					$key = 'id';
				}
				$insert_fields_str .= "$key, ";
				$insert_values_str .= "'".addslashes(stripslashes($value))."', ";
			}
		}
		
		$insert_fields_str .= "update_datetime, insert_datetime";
		$cur_time = date("Y-m-d H:i:s");
		$insert_values_str .= "'$cur_time','$cur_time'";
		
		//first we need to delete the currently existing record
		$query_str = "DELETE FROM ". $this->module_arr['form_dest_table'] . " WHERE id = $id";
		$db->set_query_str($query_str);
		$db->db_query();
		
		//now let's insert the other
		$query_str = "INSERT INTO ".$this->module_arr['form_dest_table']." (".$insert_fields_str .") VALUES (" . $insert_values_str . ")";
		$db->set_query_str($query_str);
		$db->db_query();
		
		?>
		Refreshing List... <img src="/cms/images/template_images/loader.gif">
		
		<?php
				
		//============================================================
		// 	RUN after_publish()
		//============================================================
		if ( method_exists($module_actions,'after_publish()') )
		{
			$module_actions->after_publish();
		}

		
		?>
		
		<script language="javascript">
			$().ready(function(){
				setTimeout(function(){
					window.location = './?admin_action=<?=$_REQUEST['admin_action']?>';
				}, 1500 );
			})
		</script>
		<?php
		
	}
	
	
	//============================================================
	// 	PULL TAGS FROM TEXT
	//============================================================
	function pull_tags_from_text($text)
	{
		$new_text = explode(',',$text);
		if ($new_text[0] == '' )
		{
			array_shift($new_text);
		}
		
		
		return $new_text;
	}
	
	
	function get_module_info($module_name)
	{
		$query_str = "SELECT * FROM modules WHERE module_name = '$module_name'";
		$db = new db(1);
		$results = $db->qquery($query_str);
		$results = $results[0];
		
		$just_gettin_info = true;
		
		require_once(THE_GUTS_DIR.'/modules'.$results['module_include_file']);
		require_once($admin_page);
		$return_module_info = array();
		$return_module_info['default_index_query_str'] = $default_index_query_str;
		$return_module_info['default_index_markup'] = $default_index_markup;
		$return_module_info['default_index_markup_loop'] = $default_index_markup_loop;
		$return_module_info['default_detail_query_str'] = $default_detail_query_str;
		$return_module_info['default_detail_markup'] = $default_detail_markup;
		
		$this->module_info = $return_module_info;
	}
	
	function get_index_markup()
	{
		$query_str = $this->module_info['default_index_query_str'];
		$db = new db($this->module_arr['db']);
		$results = $db->qquery($query_str);
		
		$page_content = <<< end
				<page>
					<element type='html_content'>
						<content>
							<type>html</type>
								<data>
end;
		
		
		$page_content .= urlencode($this->module_info['default_index_markup']);
		
		for ($i=0; $i<count($results); $i++ )
		{
			$orig_string = $this->module_info['default_index_markup_loop'];
			$str_exp = explode("<<*",$orig_string);
			for ( $z=0; $z<count($str_exp); $z++ )
			{				
				$end_pos = strpos($str_exp[$z],"*>>");
				$var = substr($str_exp[$z],0,$end_pos);
				
				//set basic return
				$return_val = $results[$i][$var];
				
				//if we have a date, we'll need to do some fixin to get it to look right
				if (strpos($var,'--date--') !== false)
				{
					//take out --date-- string
					$var = str_replace('--date--','',$var);
					//now get our results
					$return_val = $results[$i][$var];
					//format date
					$return_val = date("F j, Y",strtotime($return_val));
					//now wipe --date-- from our real string
					$str_exp[$z] = str_replace('--date--','',$str_exp[$z]);
				}
				
				$str_exp[$z] = str_replace($var.'*>>','',$str_exp[$z]);
				$str_exp[$z] = $return_val.$str_exp[$z];
				$page_content .= urlencode($str_exp[$z]);					
			}
		}
		$page_content .= <<<end
							</data>
						</content>
					</element>
				</page>
end;
		return $page_content;
	}
	
	
	//============================================================
	// 
	//		GET DETAIL MARKUP
	//
	//============================================================
	function get_detail_markup($record_id)
	{
		$query_str = $this->module_info['default_detail_query_str'].$record_id;
		$db = new db($this->module_arr['db']);
		$results = $db->qquery($query_str);
		
		$page_content = <<< end
				<page>
					<element type='html_content'>
						<content>
							<type>html</type>
								<data>
end;
		
		if ( count($results) == 0 )
		{
			$page_content .= "We are sorry.  There are no published records with this id.";
		}
		else
		{
			$results = $results[0];
			$orig_string = $this->module_info['default_detail_markup'];
			$str_exp = explode("<<*",$orig_string);
			for ( $z=0; $z<count($str_exp); $z++ )
			{				
				$end_pos = strpos($str_exp[$z],"*>>");
				$var = substr($str_exp[$z],0,$end_pos);
				
				//set basic return
				$return_val = $results[$var];
				
				//if we have a date, we'll need to do some fixin to get it to look right
				if (strpos($var,'--date--') !== false)
				{
					//take out --date-- string
					$var = str_replace('--date--','',$var);
					//now get our results
					$return_val = $results[$var];
					//format date
					$return_val = date("F j, Y",strtotime($return_val));
					//now wipe --date-- from our real string
					$str_exp[$z] = str_replace('--date--','',$str_exp[$z]);
				}
				
				$str_exp[$z] = str_replace($var.'*>>','',$str_exp[$z]);
				$str_exp[$z] = $return_val.$str_exp[$z];
				$page_content .= urlencode($str_exp[$z]);					
			}

		}
		
		$page_content .= <<<end
							</data>
						</content>
					</element>
				</page>
end;
		return $page_content;
	}

}
?>
