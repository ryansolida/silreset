<?php
//simple email class
use \SendGrid\Mail\Mail;
class email
{
        function sendgrid_send($email_array){

            require '../vendor/autoload.php';
            $sg_user = getenv('SENDGRID_USER');
            $sg_api_key = getenv('SENDGRID_PASSWORD');
            $sendgrid = new \SendGrid($sg_api_key);

            $email = new Mail();
            $address_arr = explode(",", $email_array['to_address']);
            $report_arr = array();
            $email->setFrom(
                $email_array['from_address'],
                $email_array['from_name']
            );
            $email->setSubject($email_array['subject']);
            foreach ($address_arr as $address){
                // Replace the email address and name with your recipient
                $email->addTo(
                    trim($address)
                );
            }
            $email->addContent(
                'text/html',
                $email_array['html_body']
            );

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
            return;

    }

	//probably going to need a new method here
	function email_to_queue($email_array)
	{
        return $this->sendgrid_send($email_array);
	}
}
?>
