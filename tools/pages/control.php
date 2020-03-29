<?php

if ( $_REQUEST['nt'] == '' )
{
	?><style type='text/css'><?php
	require('pages.css');
	?></style><?php
}

require_once(THE_GUTS_DIR."/includes/classes/global/pages.php");
require_once(THE_GUTS_DIR."/includes/classes/global/page.php");
$page = new page;

$subaction = $_REQUEST['admin_subaction'];

if ( $subaction == 'edit_page_content' )
{
	require('edit_page_content.php');
}
elseif ( $subaction == 'get_page_pieces' )
{
	require('page_pieces.php');
}
elseif ( $subaction == 'edit_page_content_piece' )
{
	if ($_REQUEST['submitted'] == '' )
	{
		require('edit_page_content_piece.php');
	}
	else
	{	
		$content_arr = array();
		
		foreach ($_REQUEST as $key=>$value)
		{
			if ( substr($key,0,7) == 'content' )
			{
				$content_arr[$key] = $value;
			}
		}
		
		//============================================================
		// 	READING THROUGH MULTI FIELD TYPES
		//============================================================
		if ( $_REQUEST['json'] )
		{
			//look through each fields and find which ones start with the multi triggers (currently multi_ and json_)
			// and add those to the array
			
			$multi_content_arr = array();
			foreach ($_REQUEST as $key=>$value)
			{
				$json_prefix_count = false;
				
				if ( substr($key,0,6) == 'multi_' ){ //if 'multi_'
					$json_prefix_count = 6;
				}
				
				if ( substr($key,0,5) == 'json_' ){ //if json_
					$json_prefix_count = 5;
				}
				
				if ($json_prefix_count){ //add said field value to the element as the key with the trigger stripper out.  IE: 'json_first_field' is just 'first_field'
					$multi_content_arr[substr($key,$json_prefix_count)] = str_replace('\\','%5c',htmlentities($value));
				}
			}
			
			$content_arr['content_data'] = json_encode($multi_content_arr); //json encode the data to be put into the database
		}
		
		$content_arr['id'] = $_REQUEST['id'];
		
		$page->update_content_piece($content_arr);
	}
}
elseif ( $subaction == 'delete_content_piece' )
{
	$piece_arr = array('id'=>$_REQUEST['id'],'page_id'=>$_REQUEST['page_id']);
	$page->delete_content_piece($piece_arr);
}
elseif ($subaction == 'reorder_pieces' )
{
	$arr = array('pieces'=>$_REQUEST['pieces'],'page_id'=>$_REQUEST['page_id']);
	$page->reorder_content_pieces($arr);
}
elseif ( $subaction == 'get_page_form' )
{
	require('edit_page_info_form.php');
}
elseif ( $subaction == 'get_pages_list' )
{
	require('pages_list_get.php');
}
elseif ( $subaction == 'create_page' || $subaction == 'edit_page' )
{
	$create_arr = array();
	foreach ($_REQUEST as $key=>$value )
	{
		if ( substr($key,0,5) == 'page_' )
		{
			$create_arr[substr($key,5)] = $value;
		}
	}
	$next_action = str_replace('_page','',$subaction).'_page';
	$page->$next_action($create_arr);
}
elseif ( $subaction == 'reorder_pages' )
{
	$page->reorder_pages($_REQUEST['page']);
}
elseif ( $subaction == 'image_library' )
{
	require(THE_GUTS_DIR.'/tools/pages/image_library/control.php');
}
elseif ( $_REQUEST['task'] != '' )
{
	$arr = array();
	foreach ($_REQUEST as $key=>$value)
	{
		if ( substr($key,0,5) == 'page_' )
		{
			$arr[substr($key,5)] = $value;
		}
	}
	$method = $_REQUEST['task'];
	$page->$method($arr);
}
else
{
	require('pages_list_index.php');
}
?>