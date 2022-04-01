<?php
//simple email class
use \SendGrid\Mail\Mail;
class email
{
        function sendgrid_send($email_array){

            require '../vendor/autoload.php';
            $sg_user = getenv('SENDGRID_USER');
            $sg_api_key = getenv('SENDGRID_PASSWORD');

            $email = new Mail();
            $address_arr = explode(",", $email_array['to_address']);
            $report_arr = array();
            foreach ($address_arr as $address){
                // Replace the email address and name with your verified sender
                $email->setFrom(
                    $email_array['from_address'],
                    $email_array['from_name']
                );
                $email->setSubject($email_array['subject']);
                // Replace the email address and name with your recipient
                $email->addTo(
                    trim($address)
                );
                $email->addContent(
                    'text/html',
                    $email_array['html_body']
                );
                $sendgrid = new \SendGrid($sg_api_key);
                try {
                    $response = $sendgrid->send($email);
                   // printf("Response status: %d\n\n", $response->statusCode());

                    $headers = array_filter($response->headers());
                    //echo "Response Headers\n\n";
                   /* foreach ($headers as $header) {
                        echo '- ' . $header . "\n";
                    }*/
                } catch (Exception $e) {
                    echo 'Caught exception: '. $e->getMessage() ."\n";
                }
            }
            return;
            require_once(SYSTEM_ROOT_PATH.'/services/cms/includes/libraries/sendgrid/web.php');
            $sg_user = getenv('SENDGRID_USER');
            $sg_api_key = getenv('SENDGRID_PASSWORD');
            $sendgridweb = new sendgridWeb($sg_user,$sg_api_key);

            $address_arr = explode(",", $email_array['to_address']);
            $report_arr = array();
            foreach ($address_arr as $address){
                    $send = $sendgridweb->mail_send(
                            trim($address), //to ... can be an array
                            '', //to name
                            '', //header vars
                            $email_array['subject'], //subject
                            $email_array['html_body'], //html
                            strip_tags(str_replace("<br>",'\n',$email_array['html_body'])), //text
                            $email_array['from_address'], //from
                            '', //bcc
                            $email_array['from_name'], // from name
                            '', //reply to
                            '',//date
                            '', //attachments
                            '' //headers
                    );
                    $report_arr[] = $send;
            }

            return $report_arr;
    }

	//probably going to need a new method here
	function email_to_queue($email_array)
	{
        return $this->sendgrid_send($email_array);
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
					$mail_conn = mysqli_connect('72.32.2.251', 'silsysadmin','mcdsfiasco') or die(mysqli_error());
					mysqli_select_db('!queues', $mail_conn) or die(mysqli_error());
					$queue_query_str = "INSERT DELAYED INTO mail_queue ($field_str) VALUES ($value_str_replaced)";
					mysqli_query($queue_query_str,$mail_conn) or die(mysqli_error());

					//get insert id to place into logging
					$insert_id = mysqli_insert_id($mail_conn);

					/* insert into logging table */
					$mail_log_conn = mysqli_connect('72.32.2.251', 'silsysadmin','mcdsfiasco') or die(mysqli_error());
					mysqli_select_db('!logging', $mail_log_conn) or die(mysqli_error());
					$logging_query_str = "INSERT INTO mail_log ($field_str, id) VALUES ($value_str_replaced, $insert_id)";
					mysqli_query($logging_query_str,$mail_log_conn) or die(mysqli_error());
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
