<?php
class db
{
	function db() 
	{
		$this->db_connect();
	}
	
	function db_connect()
	{
		$this->db_name = DB_NAME;
		$this->db_host = DB_DOMAIN;
		$this->db_uname = DB_UNAME;
		$this->db_pw = DB_PASS;
		
		$this->db_connect = mysql_connect($this->db_host, $this->db_uname, $this->db_pw) or die(mysql_error());
		mysql_select_db($this->db_name, $this->db_connect) or die(mysql_error());
	}
	
	function qquery($query_str)
	{
		$this->set_query_str($query_str);
		$this->db_query();
		$results = $this->get_results();
		return $results;
	}
	
	function db_query($error = 0)
	{
		//execute query
		$this->query = mysql_query($this->query_str, $this->db_connect) or die(mysql_error());
					
		if ( substr($this->query_str, 0, 6) == "SELECT" )
		{
			$this->result_count = mysql_num_rows($this->query);
		}
		else
		{
			$this->result_count = 0;
		}
	}
	
	
	function get_db_connection()
	{
		return $this->db_connect;
	}
	
	function set_query_str($string)
	{
		$this->query_str = $string;
	}
	
	function get_count()
	{
		return $this->result_count;
	}
	
	function get_results()
	{
		while ( $result = mysql_fetch_assoc($this->query))
		{
			$results[] = $result;
		}
		return $results;
	}
	
	//==============================
	//we need to close our connections
	//==============================
	function close_connection()
	{
		 mysql_close($this->db_connect);
	}
	
	//==============================
	//pulls back the insert id
	//==============================
	function get_insert_id()
	{
		return $this->insert_id = mysql_insert_id($this->db_connect);
	}	
}
?>