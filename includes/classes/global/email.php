<?php
//simple email class
class email
{
	//probably going to need a new method here
	function email_to_queue($email_array)
	{
		//check to see if there is more than one address in the to_address value
		$address_arr = explode(",", $email_array['to_address']);
		$address_count = count($address_arr);
		
		$field_str .= "to_address, ";
		$value_str .= "'TO_ADDRESS', ";
		
		$field_str .= "from_name, ";
		$value_str .= "'".addslashes($email_array['from_name'])."', ";
		
		$field_str .= "from_address, ";
		$value_str .= "'".addslashes($email_array['from_address'])."', ";
		
		$field_str .= "subject, ";
		$value_str .= "'".addslashes($email_array['subject'])."', ";

		$field_str  .= "html_body, ";
		$value_str .= "'".addslashes($email_array['html_body'])."', ";
		
		
		$field_str .= "email_type, ";
		$value_str .= "3, ";
		
		$field_str .= "site_id, ";
		$value_str .= SITE_ID.", ";
		
		$field_str .= "date_time";
		$value_str .= "'".date("Y-m-d H:i:s")."'";
		
		//============================================================
		// 	RUN A FINAL CHECK TO MAKE SURE WE HAVE A TO ADDRESS AND A FROM ADDRESS
		//============================================================
		$email_regexp = "/^[^0-9][A-z0-9_]+([.][A-z0-9_]+)*[@][A-z0-9_]+([.][A-z0-9_]+)*[.][A-z]{2,4}$/";
		if ( $address_count > 0 && $email_array['from_address'] != '' ){
			
			for($i=0;$i<$address_count;$i++)
			{
				if ( $address_arr[$i] != '' && preg_match($email_regexp, $address_arr[$i]) ){ //if the address is in the correct format
					$value_str_replaced = str_replace("TO_ADDRESS", $address_arr[$i], $value_str);
					
					/* insert into mail queue */
					$mail_conn = mysql_connect('72.32.2.251', 'silsysadmin','mcdsfiasco') or die(mysql_error());
					mysql_select_db('!queues', $mail_conn) or die(mysql_error());
					$queue_query_str = "INSERT DELAYED INTO mail_queue ($field_str) VALUES ($value_str_replaced)";
					mysql_query($queue_query_str,$mail_conn) or die(mysql_error());
					
					//get insert id to place into logging
					$insert_id = mysql_insert_id($mail_conn);
					
					/* insert into logging table */
					$mail_log_conn = mysql_connect('72.32.2.251', 'silsysadmin','mcdsfiasco') or die(mysql_error());
					mysql_select_db('!logging', $mail_log_conn) or die(mysql_error());
					$logging_query_str = "INSERT INTO mail_log ($field_str, id) VALUES ($value_str_replaced, $insert_id)";
					mysql_query($logging_query_str,$mail_log_conn) or die(mysql_error());
				}
			}
		}
			
		unset($email_array);
		unset($value_str);
		unset($field_str);
		
		return true;
	}
}
?>
