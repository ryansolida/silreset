<?php
class logging
{
	function log_page_request($page_data)
	{
		//print_r($page_data);
		//exit;
		$page_request_vars = $_REQUEST;
		$r_vars_skip = array('PHPSESSID', 'p', 'url_parent', 'url_child', '__utmz', 'utmcmd', '__utmc', '__utmb', '__utma');
		foreach($page_request_vars as $key => $value)
		{
			if(!in_array($key, $r_vars_skip))
			{
				$value = addslashes($value);
				$request_var_str .= "$key=$value, ";
			}
		}
		if(isset($request_var_str))
		{
			$request_var_str = substr($request_var_str, 0, -2);
		}
		else
		{
			$request_var_str = '';
		}
		
		$bot_flag = $this->bot_check($_SERVER['HTTP_USER_AGENT']);
		
		//break out search terms from the referrer as the request comes in
		//only doing google and yahoo right now
		if(isset($_SERVER['HTTP_REFERER']))
		{
			$url_param_arr = explode("&", $_SERVER['HTTP_REFERER']);
			foreach($url_param_arr as $param)
			{
				if(stristr($param, 'q=') == TRUE)
				{
					$tmp_phrase_arr = explode("q=", $param);
					$tmp_phrase = $tmp_phrase_arr[count($tmp_phrase_arr)-1];
					$search_terms = str_replace('+', ' ', $tmp_phrase);	
				}
				//handles yahoo
				if(stristr($param, 'p=') == TRUE)
				{
					$tmp_phrase_arr = explode("p=", $param);
					$tmp_phrase = $tmp_phrase_arr[count($tmp_phrase_arr)-1];
					$yahoo_search_terms = str_replace('+', ' ', $tmp_phrase);
					$search_terms .= $yahoo_search_terms;
				}
			}
		}
		else
		{
			$_SERVER['HTTP_REFERER'] = '';
			$search_terms = '';
		}
			
		$page_query_str = "INSERT INTO site_stats ";
		$page_query_str .= "(page_assoc_id, page_name, page_path, requested_path, remote_ip_address, http_referrer, http_user_agent, bot_request, request_vars,  php_sess_id, search_terms, request_date) ";
		$page_query_str .= "VALUES (";
		$page_query_str .= "'".$page_data['id']."', ";
		$page_query_str .= "'".addslashes($page_data['name_actual'])."', ";
		$page_query_str .= "'".addslashes($page_data['path'])."', ";
		$page_query_str .= "'".addslashes($_SESSION['original_request_path'])."', ";
		$page_query_str .= "'".$_SERVER['REMOTE_ADDR']."', ";
		$page_query_str .= "'".addslashes($_SERVER['HTTP_REFERER'])."', ";
		$page_query_str .= "'".addslashes($_SERVER['HTTP_USER_AGENT'])."', ";
		$page_query_str .= "'".$bot_flag."', ";
		$page_query_str .= "'".addslashes($request_var_str)."', ";
		$page_query_str .= "'".session_id()."', ";
		$page_query_str .= "'".addslashes($search_terms)."', ";
		$page_query_str .= "NOW())";
		$server = "logging";
		$logging_db = new db;
		$logging_db->db_connect($server);
		$logging_db->set_query_str($page_query_str);
		$logging_db->db_query();
		
		unset($_SESSION['original_request_path']);
	}
	
	function get_bots_array()
	{
		$db = new db;
		$query_str = "SELECT bot_search_string FROM robots";
		$results = $db->qquery($query_str);
		$count = count($results);
		
		for($i=0;$i<$count;$i++)
		{
			$bots_arr[] = $results[$i]['bot_search_string'];
		}
		
		return $bots_arr;
	}
	
	function bot_check($user_agent='')
	{
		if($user_agent == '')
		{
			$user_agent = $_SERVER['HTTP_USER_AGENT'];
		}
		
		$bot_flag = 0;
		$bots_arr = $this->get_bots_array();
		foreach($bots_arr as $bot)
		{
			if(stristr($user_agent, $bot) !== FALSE)
			{
				$bot_flag = 1;
			}
		}
		
		return $bot_flag;
	}
}
?>