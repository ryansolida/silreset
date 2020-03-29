<?php
class pages
{
	//============================================================
	//  GET PAGES
	//============================================================
	function get_pages($arr='')
	{
		//set arr to array if not one currently to quiet any errors
		if ( !is_array($arr) )
		{
			echo "You must send get_pages() an array";
			return false;
		}
		
		
		//tell query string what fields to select
		if ( $arr['fields'] != '' )
		{
			$query_str_fields = trim($arr['fields']);
		}
		else
		{
			$query_str_fields = "*";
		}
		
		//which table are we selecting from
		if ( $arr['published'] == true )
		{
			$published = true;
			$query_str_from = "pages_published";
		}
		else
		{
			$published = false;
			$query_str_from = "pages";
		}
		
		
		//set up shortcut for searching for id
		if ( $arr['id'] != '' )
		{
			$arr['field_search'] = array('field'=>'id','str'=>$arr['id']);
		}
		//and shortcut for parent id
		if ( $arr['parent'] != '' )
		{
			$arr['field_search'] = array('field'=>'parent','str'=>$arr['parent']);
		}
		
		//are we searching for a specific search string
		if ( is_array($arr['field_search']) )
		{	
			$query_str_where = $arr['field_search']['field']." ";
			if ( is_numeric($arr['field_search']['str']) ){
				$query_str_where .= "= ".addslashes($arr['field_search']['str']);
			}
			else {
				$query_str_where .= "LIKE '".addslashes($arr['field_search']['str'])."'";
			}
		}
		
		//show or forget hidden pages
		if ( !IN_ADMIN || !defined('IN_ADMIN') ) //if you're on the public side
		{
			if ( !$arr['show_hidden'] ) //and show_hidden is not set to true
			{
				if ( $query_str_where != '' ){
					$query_str_where .= " AND ";
				}
				$query_str_where .= "hidden = 0";
			}
		}
		
		//any certain order?
		if ( $arr['order_by'] != '' )
		{
			$query_str_order = $arr['order_by'];
		}
		else
		{
			$query_str_order = "`order`";
		}
		
		if ( $arr['limit'] != '' )
		{
			$query_str_limit = " LIMIT " . $arr['limit'];
		}
		
		//============================================================
		// 	Let's build the query
		//============================================================
		$query_str = "SELECT $query_str_fields FROM $query_str_from";
		if ( $query_str_where != '' )
		{
			$query_str .= " WHERE " . $query_str_where;
		}
		
		$query_str .= " ORDER BY $query_str_order".$query_str_limit;	
		
		$db = new db;
		$results = $db->qquery(trim($query_str));
		$results_count = count($results);
		//return false if nothing comes backi]
		if ( $results_count == 0 )
		{
			return false;
		}
		
		//now let's add some more information to our results
		if ( $arr['fields'] == '' )
		{
			for ($i=0; $i<$results_count; $i++)
			{
				
				$results[$i]['query_str'] = $query_str;
				
				if ( !$arr['skip_content'] ){
					$page_pieces = array();
					
					//first, let's get the page pieces
					$page_pieces = $this->get_page_pieces(array('page_id'=>$results[$i]['id'],'published'=>$published));
					
					$results[$i]['page_content'] = array();
					
					if ( count($page_pieces) > 0 ){
						foreach ($page_pieces as $piece)
						{
							if ( $piece['section'] == '' ){
								$piece['section'] = 'main_content';
							}
							if ( $piece['type'] == 'pre_process' ){
								$piece['section'] = 'pre_process';
							}
							
							$results[$i]['page_content'][$piece['section']][] = $piece;
						}
					}
				}
				
				//have we been published?
				if ( !$published ) //we only care if we're looking at the unpublished pages
				{
					$published_datetime = $this->is_published(array('page_id'=>$results[$i]['id']));
					
					$results[$i]['is_published'] = false;
					if ( $published_datetime ){
						$results[$i]['is_published'] = $published_datetime;
					}
				}
				
				//have there been updates since the last publish?
				if ( $results[$i]['is_published'] )
				{
					$published_results = $this->published_is_latest($published_datetime,$results[$i]['id']);
					$results[$i]['published_is_latest'] = $published_results['result'];
					$results[$i]['last_publish_date'] = $published_results['last_publish_date'];
				}
				
				//are we a parent page?
				$child_pages = $this->get_pages(array('parent'=>$results[$i]['id'],'fields'=>'id','skip_content'=>true,'limit'=>1));
				if ( $child_pages !== false )
				{
					$results[$i]['is_parent'] = true;
				}
				
				//let's find our page level
				$page_path_exp = explode('/',$results[$i]['path']);
				$results[$i]['level'] = count($page_path_exp) - 1;
				
			}
		}
		
		//just for fun, let's strip slashes right here
		for ( $i=0; $i<$results_count; $i++ )
		{
			foreach ( $results[$i] as $key=>$value )
			{
				if ( !is_array($value) )
				{
					$results[$i][$key] = stripslashes($value);
				}
			}
		}
		
		return $results;
		
	}

	function get_descendents_of($page_id){
		if ( $page_id == 0 ){
			$page_id = 1;
		}
		$db = new db;
		$query_str = "SELECT path FROM pages WHERE id=$page_id";
		$results = $db->qquery($query_str);
		$orig_path = $results[0]['path'];
		$all_results = $db->qquery("SELECT id FROM pages WHERE path LIKE '".$orig_path."%' AND id != $page_id");
		$descendents = array();
		if ( count($all_results) > 0 ){
			foreach($all_results as $page){
				$descendents[] = $page['id'];
			}
		}
		
		return $descendents;
		
	}
	
	//============================================================
	// 	 GET PAGE PIECES
	//============================================================
	function get_page_pieces($arr = '')
	{
		if ( !is_array($arr) )
		{
			echo "You must send get_page_pieces() an array";
			return false;
		}
		
		if ( $arr['published'] == true )
		{
			$table = "page_pieces_published";
		}
		else
		{
			$table = "page_pieces_edited";
		}
		
		$query_str = "SELECT * FROM $table WHERE page_id = " . $arr['page_id'] . " ORDER BY `order`";
		
		$db = new db;
		$results = $db->qquery($query_str);
		
		//stripslashes right here so we don't have to do it later.
		for ( $i=0; $i<count($results); $i++ )
		{
			$results[$i]['data'] = stripslashes(str_replace("\\n","",str_replace("\\t","",$results[$i]['data'])));
		}
		
		return $results;
		
	}
	
	//============================================================
	// 	IS PUBLISHED
	//============================================================
	function is_published($arr = '')
	{
		if ( !is_array($arr) )
		{
			echo "You must send is_published() an array";
			return false;
		}
		
		$publish_test = $this->get_pages(array('published'=>true,'fields'=>'published_datetime', 'id'=>$arr['page_id'],'skip_content'=>true,'limit'=>1));

		//if this comes back false, then we know it's not published
		if ( !$publish_test )
		{
			return false;
		}

		//if we get this far, we found a page
		return $publish_test[0]['published_datetime'];		
	}
	
	//============================================================
	// 	PUBLISHED IS LATEST
	//============================================================
	function published_is_latest($published_datetime,$page_id)
	{
		//$publish_results = $this->get_pages(array('published'=>true,'fields'=>'published_datetime','id'=>$arr['page_id'],'skip_content'=>true,'order_by'=>'published_datetime DESC','limit'=>1));
		$edit_results = $this->get_pages(array('fields'=>'edited_datetime','id'=>$page_id,'skip_content'=>true,'limit'=>1));
		
		$return_array = array();
		
		//echo "<BR>".$page_id.' - '.$published_datetime.' - '.$edit_results[0]['edited_datetime']."<BR>";
		
		if ( strtotime($published_datetime) > strtotime($edit_results[0]['edited_datetime']) )
		{
			$return_array['result'] = true;
		}
		else
		{
			$return_array['result'] = false;
		}

		$return_array['last_publish_date'] = $published_datetime;
		
		return $return_array;
	}
	
	//============================================================
	// 	REORDER PAGES
	//============================================================
	function reorder_pages($arr)
	{
		$db = new db;
		foreach ($arr as $key=>$value )
		{
			$order = $key+1;
			$query_str = "UPDATE pages SET `order` = $order WHERE id=$value";
			$db->set_query_str($query_str);
			$db->db_query();
			
			$query_str = "UPDATE pages_published SET `order` = $order WHERE id=$value";
			$db->set_query_str($query_str);
			$db->db_query();
		}
	}
}
?>
