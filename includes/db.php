<?php
$db_connection = FALSE;
class db
{
	function db($db_source = 0) 
	{
		global $db_connection;
		$this->db_source = $db_source;
		if ( $db_connection ){
			$this->db_connect = $db_connection;
		} else {
			$this->db_connect();
		}
	}
	
	function db_connect()
	{
		if ( $this->db_source == 0 )
		{
			$this->db_name = DB_NAME;
			$this->db_host = DB_DOMAIN;
			$this->db_uname = DB_UNAME;
			$this->db_pw = DB_PASS;
			$this->db_socket = DB_SOCKET;
		}
		elseif ( $this->db_source == 1 )
		{
			$this->db_name = "!master";
			$this->db_host = "localhost";
			$this->db_uname = "dbadmin";
			$this->db_pw = "silDBpass";
		}

		
		$this->db_connect = mysqli_connect($this->db_host, $this->db_uname, $this->db_pw,$this->db_name,'3306',$this->db_socket) or die("Could Not Connect to the Database");
		global $db_connection;
		$db_connection = $this->db_connect;
		//mysqli_select_db($this->db_name, $this->db_connect) or die(mysqli_error());
	}
	
	function qquery($query_str)
	{
		$this->set_query_str($query_str);
		$this->db_query();
		$results = $this->get_results();
		
		/*
		if ( count($this->result_count) > 0 ){
			//stripslashes on all records
			for ($i=0; $i<$this->result_count; $i++ ){
				foreach($results[$i] as $key=>$value){
					$results[$i][$key] = stripslashes($value);
				}
			}
		}
		*/

		
		return $results;
	}
	
	function insert($table_name, $query_arr){
		foreach ($query_arr as $key=>$value){
			$fields_str .= "$key, ";
			$values_str .= "'".addslashes($value)."', ";
		}
		$query_str = "INSERT INTO $table_name (".substr($fields_str,0,-2).") VALUES (".substr($values_str,0,-2).")";
		$this->set_query_str($query_str);
		$this->db_query();
	}
	
	function update($table_name, $query_arr, $criteria){
		foreach ($query_arr as $key=>$value){
			$update_str .= "$key = '".addslashes($value)."', ";
		}
		
		if ( !is_array($criteria) ){
			return FALSE;
		}
		
		$where_str = 'WHERE ';
		$count = 0;
		foreach ($criteria as $key=>$value){
			if ( $count > 0 ){
				$where_str .= " AND ";
			}
			$where_str .= "`$key` = ".addslashes($value);
			$count++;
		}
		
		$query_str = "UPDATE $table_name SET ".substr($update_str,0,-2)." $where_str ";
		$this->set_query_str($query_str);
		$this->db_query();
	}
	
	function getwhere($table_name,$criteria){
		if ( !is_array($criteria) ){
			return FALSE;
		}
		
		$where_str = 'WHERE ';
		$count = 0;
		foreach ($criteria as $key=>$value){
			if ( $count > 0 ){
				$where_str .= " AND ";
			}
			$where_str .= "`$key` = ".addslashes($value);
			$count++;
		}
		$results = $this->qquery("SELECT * FROM $table_name $where_str");
		return $results;
	}
	
	function db_query($error = 0)
	{
		$this->last_query = $this->query_str;
		//execute query
		$this->query = mysqli_query( $this->db_connect,$this->query_str) or die(mysqli_error(). "<br />Query: ".$this->last_query);
		if ( substr($this->query_str, 0, 6) == "SELECT" )
		{
			$this->result_count = mysqli_num_rows($this->query);
		}
		else
		{
			$this->result_count = 0;
		}
	}
	
	function last_query(){
		return $this->last_query;
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
		while ( $result = mysqli_fetch_assoc($this->query))
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