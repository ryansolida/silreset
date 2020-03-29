<?php
require_once('pages.php');
class page extends pages
{
	function page($arr='',$published='')
	{	
		if ($arr == '' )
		{
			return; 
		}
		
		if ( $published != true )
		{
			$published = false;
		}

		//a little trickery here for some shortcuts
		if ( !is_array($arr) ) //if what we're given isn't an array
		{
			if ( is_numeric($arr) ) //if it's a number
			{
				$arr = array('id'=>$arr); //we'll assume it's an id
			}
			// it's just a page name
			elseif( stristr($arr,"/") === FALSE )
			{
				$arr = array('name_actual'=>$arr);
			}
			else // if it's a string
			{
				$arr = array('path'=>$arr); //we'll assume it's a url (page_path)
			}
		}
		
		$field_search = array();
		
		$skip_content = false;
		if ( $arr['skip_content'] ){
			$skip_content = true;
			unset($arr['skip_content']);
		}
		
		foreach ($arr as $key=>$value)
		{
			$field_search['field'] = $key;
			$field_search['str'] = $value;
		}
		
		$page_info = $this->get_pages(array('field_search'=>$field_search,'published'=>$published,'show_hidden'=>true,'skip_content'=>$skip_content,'limit'=>1));
		if ( !$page_info )
		{
			return false;
		}
		
		//if we have more than one page
		//let's return false to dispaly the 404 page
		if(count($page_info) > 1)
		{
			return false;
		}
		
		$this->page_info = $page_info[0];
		
//		return $page_info;	
	}

	//============================================================
	// 	CREATE PAGE
	//============================================================
	function create_page($arr='')
	{
		if ( $arr['title'] == '' || $arr['name_actual'] == '' || $arr['parent'] == '' )
		{
			echo "Not enough information to create new page";
			return false;
		}
		
		//we need to get the parent page to pull the path
		$parent_page = $this->get_pages(array('id'=>$arr['parent']));
		$parent_page_path = $parent_page[0]['path'];
		$this_page_path = str_replace('//','/',$parent_page_path."/".$arr['name_actual']);
		
		//search to see if this page already exists
		$similar_page = $this->get_pages(array('field_search'=>array('field'=>'path','str'=>$this_page_path)));
		if ( $similar_page !== false )
		{
			echo "This page already exists. Please choose another name or location for the page.";
			return false;
		}
		
		//we need to get the last id
		$last_page = $this->get_pages(array('fields'=>'id','order_by'=>'id DESC'));
		$last_page_id = $last_page[0]['id'];
		$next_id = $last_page_id + 1;
		
		//we also need to get the last id
		$sibling_pages = $this->get_pages(array('parent'=>$arr['parent'],'fields'=>'`order`','order_by'=>'`order` DESC'));
		$next_order = $sibling_pages[0]['order'] + 1;
		
		foreach ($arr as $key=>$value)
		{
			if($key != 'id')
			{
				$fields_str .= '`'.$key.'`, ';
				$values_str .= "'".addslashes($value)."', ";
			}
		}
		
		$db = new db;
		$query_str = "INSERT INTO pages (id,path,`order`,".substr($fields_str,0,-2).") VALUES ($next_id, '".addslashes($this_page_path)."', $next_order, ".substr($values_str,0,-2).")";
		$db->set_query_str($query_str);
		$db->db_query();
	}
	
	//============================================================
	// 	EDIT PAGE
	//============================================================
	function edit_page($arr)
	{
		if ( $arr['title'] == '' || $arr['name_actual'] == '' || $arr['parent'] == '' || $arr['id'] == '' )
		{
			echo "You must fill in all fields.";
			return false;
		}
		
		if ( $arr['id'] == 1 ) // we don't want to run any of this on the homepage
		{
			$new_page_path = "/"; 
			$need_to_update_status = false;
		}
		else
		{
			//pull current page info
			$this_page = new page(array('id'=>$arr['id']));
			$orig_page_info = $this_page->page_info;
			
			//find path
			$parent_page = new page(array('id'=>$arr['parent']));
			$parent_page_info = $parent_page->page_info;
			$parent_page_path = $parent_page_info['path'];
			$new_page_path = str_replace('//','/',$parent_page_path."/".$arr['name_actual']);
			
			//we need to check and see that we're actuall updating stuff for sake of whether or not to update the page status
			$need_to_update_status = false;
			foreach ( $arr as $key=>$value )
			{
				if ( $key != 'parent' )
				{
					if ( $orig_page_info[$key] != $value )
					{
						$need_to_update_status = true;
					}
				}
			}
		}

		
		//begin updating tables
		$db = new db;
		$table_arr = array('pages','pages_published');
		
		//we are building the query string here
		$update_str = '';
		foreach ($arr as $key=>$value)
		{
			if ( $key != 'id')
			{
				$update_str .= $key."='".addslashes($value)."', ";
			}
		}
		$update_str = substr($update_str, 0, -2);
		
		foreach ($table_arr as $page_table )
		{
			$query_str = "UPDATE ".$page_table." SET $update_str, path='$new_page_path' WHERE id=" . $arr['id'];
			$db->set_query_str($query_str);
			$db->db_query();
		}
		
		if ( $need_to_update_status )
		{
			$this->update_status($arr['id']);
		}
		
		//============================================================
		// 	UDPATING PAGE ORDERS
		//============================================================
		//if we're moving pages, we need to move the children pages and reset the order of the pages becoming its siblings
		if ( $orig_page_info['path'] != $new_page_path && $arr['id'] != 1 )
		{
			$this->update_child_paths(array('parent_id'=>$arr['id'],'parent_path'=>$new_page_path));
			
			
			//begin new sort order
			$new_siblings = $this->get_pages(array('parent'=>$arr['parent']));
			if ( $new_siblings )
			{
				$order = 1;
				foreach ($new_siblings as $sibling)
				{
					if ( $sibling['id'] != $arr['id'] )
					{
						foreach ($table_arr as $table)
						{
							$query_str = "UPDATE $table SET `order` = $order WHERE id = " . $sibling['id'];
							$db->set_query_str($query_str);
							$db->db_query();
						}
						$order++;
					}
				}
				
				//now update our newly added page
				foreach ($table_arr as $table)
				{
					$query_str = "UPDATE $table SET `order` = $order WHERE id = " . $orig_page_info['id'];
					$db->set_query_str($query_str);
					$db->db_query();
				}	
			}
			
			//now we need to reorder the siblings we're leaving behind
			$old_siblings = $this->get_pages(array('parent'=>$orig_page_info['parent']));
			if ($old_siblings)
			{
				$order = 1;
				foreach ( $old_siblings as $sibling )
				{
					foreach ($table_arr as $table)
					{
						$query_str = "UPDATE $table SET `order` = $order WHERE id = " . $sibling['id'];
						$db->set_query_str($query_str);
						$db->db_query();
					}	
					$order++;
				}
			}
		}
		
		echo "Changes Saved";
		
		return true;
	}
	
	//============================================================
	// 	SPECIAL RECURSIVE FUNCTION
	//============================================================	
	function update_child_paths($parent_arr)
	{
		$db = new db;
		//first, gather the children pages
		$children = $this->get_pages(array('parent'=>$parent_arr['parent_id']));
		//check to see if there are children
		if ( $children )
		{
			foreach ($children as $child )
			{
				$child_page_path = $parent_arr['parent_path'].'/'.$child['name_actual']; //create new page path based on parent_path/name
				$table_arr = array('pages','pages_published');
				foreach ($table_arr as $page_table ) //update both tables
				{
					$query_str = "UPDATE ".$page_table."  SET path = '".addslashes($child_page_path)."' WHERE id = " . $child['id'];
					$db->set_query_str($query_str);
					$db->db_query();
				}
				
				if ( $child['is_parent'] ) //if the child is a prent, make the move down
				{
					$this->update_child_paths(array('parent_id'=>$child['id'],'parent_path'=>$child['path']));
				}
			}
		}
	}

	
	//============================================================
	// 	UPDATE CONTENT PIECE
	//============================================================
	function update_content_piece($arr)
	{	
		if ( $arr['id'] != '' ) //updating a current content piece
		{
			$query_str = "UPDATE page_pieces_edited SET data = '".addslashes($arr['content_data'])."', section = '".$arr['content_section']."' WHERE id = " . $arr['id'] . " AND page_id = " . $arr['content_page_id'];
			echo $query_str;
		}		
		else //creating a new content piece
		{
			//let's shift the order so this one can have a home
			$query_str = "SELECT * FROM page_pieces_edited WHERE page_id = " . $arr['content_page_id'] . " ORDER BY `order`";
			$db = new db;
			$content_pieces = $db->qquery($query_str);
			
			//reorder the current pieces to make room for the new one
			if ( count($content_pieces) > 0 )
			{
				foreach ($content_pieces as $piece) //for each content piece currently there
				{
					if ( $piece['order'] >= $arr['content_order'] ) //if the order is higher than or equald to the new content piece order
					{
						$new_order = $piece['order'] +1;
						$query_str = "UPDATE page_pieces_edited SET `order`= $new_order WHERE id=".$piece['id']; //bump it up one
						$db->set_query_str($query_str);
						$db->db_query();
					}
				}
			}
			else
			{
				$arr['content_order'] = 1;
			}
			
			//now let's go ahead and add our new piece
			$query_str = "INSERT INTO page_pieces_edited (type, data, section, `order`, page_id) VALUES ('".addslashes($arr['content_type'])."', '".addslashes($arr['content_data'])."', '".addslashes($arr['content_section'])."', ".$arr['content_order'].", '".$arr['content_page_id']."')";
			echo $query_str;
		}
		
		$db = new db;
		$db->set_query_str($query_str);
		$db->db_query();
		
		$this->update_status($arr['content_page_id']);
		
		return true;
	}
	
	//============================================================
	// 	DELETE CONTENT PIECE
	//============================================================
	function delete_content_piece($arr)
	{
		//first, grab some data from the piece
		$db = new db;
		$query_str = "SELECT * FROM page_pieces_edited WHERE id=".$arr['id'];
		$pieces = $db->qquery($query_str);
		$orig_piece = $pieces[0];
		
		//delete the piece
		$query_str = "DELETE FROM page_pieces_edited WHERE id=".$arr['id']." AND page_id = " . $arr['page_id'];
		$db->set_query_str($query_str);
		$db->db_query();
		
		//then let's updat the orders of the remaining pieces
		$query_str = "SELECT * FROM page_pieces_edited WHERE page_id=".$arr['page_id'];
		$pieces = $db->qquery($query_str);
		if ( count($pieces) > 0 )
		{
			foreach($pieces as $piece)
			{
				if( $piece['order'] > $orig_piece['order'] )
				{
					$new_order = $piece['order'] - 1;
					$query_str = "UPDATE page_pieces_edited SET `order`= $new_order WHERE id=".$piece['id'];
					$db->set_query_str($query_str);
					$db->db_query();
				}
			}
		}
		
		//finally set the page to updated status
		$this->update_status($arr['page_id']);
	}
	
	//============================================================
	// 	REORDER CONTENT PIECES
	//============================================================
	function reorder_content_pieces($arr)
	{
		$db = new db;
		
		$page_id = $arr['page_id'];
		
		$count = 1;
		foreach ($arr['pieces'] as $piece )
		{
			$query_str = "UPDATE page_pieces_edited SET `order` = $count WHERE id = $piece";
			$db->set_query_str($query_str);
			$db->db_query();
			$count++;
		}
		
		$this->update_status($page_id);
		
	}
	
	//============================================================
	// 	PUBLISH PAGE
	//============================================================
	function publish_page($arr)
	{
		//get edited info
		$page_info = $this->get_pages(array('id'=>$arr['id']));
		$page_info = $page_info[0];
		
		$db = new db;
		
		$query_str = "DELETE FROM pages_published WHERE id=".$arr['id'];
		$db->set_query_str($query_str);
		$db->db_query();
		
		$move_arr = array(
			'id'=>$page_info['id'],
			'name_actual'=>$page_info['name_actual'],
			'path'=>$page_info['path'],
			'parent'=>$page_info['parent'],
			'title'=>$page_info['title'],
			'order'=>$page_info['order'],
			'hidden'=>$page_info['hidden'],
			'seo_title'=>$page_info['seo_title'],
			'seo_keywords'=>$page_info['seo_keywords'],
			'seo_description'=>$page_info['seo_description'],
			'published_datetime'=>date('Y-m-d H:i:s')
		);
		
		$count = 0;
		foreach ($move_arr as $key=>$value){
			if ( $count > 0 ){
				$fields_str .= ",";
				$values_str .= ",";
			}
			$fields_str .= "`$key`";
			$values_str .= "'".addslashes($value)."'";
			$count++;
		}
		
		$query_str = "INSERT INTO pages_published (".$fields_str.") VALUES (".$values_str.")";
		echo $query_str;
		
		//id,name_actual,path,parent,title,`order`,hidden,published_datetime) VALUES (".$page_info['id'].",'".$page_info['name_actual']."','".addslashes($page_info['path'])."',".$page_info['parent'].",'".addslashes($page_info['title'])."',".$page_info['order'].",".$page_info['hidden'].",NOW())";
		$db->set_query_str($query_str);
		$db->db_query();
		
		//time to publish the fields
		if ( $arr['publish_fields'] !== false )
		{
			
			$query_str = "SELECT * FROM page_pieces_edited WHERE page_id = " . $arr['id'];
			$content_blocks = $db->qquery($query_str);
			
			$query_str = "DELETE FROM page_pieces_published WHERE page_id = " . $arr['id'];
			$db = new db;
			$db->set_query_str($query_str);
			$db->db_query();
			
			for ($i=0; $i<count($content_blocks); $i++ )
			{
				$query_str = "INSERT INTO page_pieces_published (page_id, type, section, data, `order`) VALUES (".$arr['id'].",'".$content_blocks[$i]['type']."', '".$content_blocks[$i]['section']."', '".addslashes(stripslashes(str_replace("\\n","\\\\n",str_replace("\\t","",$content_blocks[$i]['data']))))."',".$content_blocks[$i]['order'].")";
				$db->set_query_str($query_str);
				$db->db_query();
			}
		}
	}
	
	//============================================================
	// 	DELETE PAGE
	//============================================================
	function delete_page($arr)
	{
		//first, gather some page info
		$page_info = $this->get_pages(array('id'=>$arr['id']));
		$page_info = $page_info[0];
		$page_id = $page_info['id'];
		
		//gather all children
		$child_pages = $this->get_pages(array('field_search'=>array('field'=>'path','str'=>$page_info['path']."%")));
		$child_pages_count = count($child_pages);
		
		$db = new db;
		
		//delete this page from tables
		$query_str = "DELETE FROM pages_published WHERE id=$page_id";
		$db->set_query_str($query_str);
		$db->db_query();
		
		$query_str = "DELETE FROM pages WHERE id=$page_id";
		$db->set_query_str($query_str);
		$db->db_query();
				
		$query_str = "DELETE FROM page_pieces_edited WHERE page_id=$page_id";
		$db->set_query_str($query_str);
		$db->db_query();
		
		$query_str = "DELETE FROM page_pieces_published WHERE page_id=$page_id";
		$db->set_query_str($query_str);
		$db->db_query();
		
		//delete child pages and page pieces
		for ( $i=0; $i<$child_pages_count; $i++ )
		{
			$page_id = $child_pages[$i]['id'];
			
			//delete this page from tables
			$query_str = "DELETE FROM pages_published WHERE id=$page_id";
			$db->set_query_str($query_str);
			$db->db_query();
			
			$query_str = "DELETE FROM pages WHERE id=$page_id";
			$db->set_query_str($query_str);
			$db->db_query();
					
			$query_str = "DELETE FROM page_pieces_edited WHERE page_id=$page_id";
			$db->set_query_str($query_str);
			$db->db_query();
			
			$query_str = "DELETE FROM page_pieces_published WHERE page_id=$page_id";
			$db->set_query_str($query_str);
			$db->db_query();
		}
		
		return true;
	}
	
	//============================================================
	// 	MOVE PAGE
	//============================================================
	function move_page($arr)
	{
		
	}
	
	//============================================================
	// 	UPDATE STATUS
	//============================================================
	function update_status($page_id)
	{
		$query_str = "UPDATE pages SET edited_datetime = NOW() WHERE id = $page_id";
		$db = new db;
		$db->set_query_str($query_str);
		$db->db_query();
	}
}

?>