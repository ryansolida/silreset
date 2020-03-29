<?php
if ( $_REQUEST['login_form_submit'] != '' || $_REQUEST['login_user_id'] != '' )
{
	require('login_submit.php');
}
else
{
	require('login_form.php');
}
?>