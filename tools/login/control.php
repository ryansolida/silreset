<?php
if ( ($_REQUEST['login_form_submit'] ?? null) != '' || ($_REQUEST['login_user_id'] ?? null) != '' )
{
	require('login_submit.php');
}
else
{
	require('login_form.php');
}
?>