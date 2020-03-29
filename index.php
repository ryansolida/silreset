<?php
session_start();

if ( $logging_in != '' )
{
	require('tools/login/control.php');
}
else
{
	if ( $_REQUEST['nt'] == 1 )
	{
		require("control.php");
	}
	else
	{
		require("template.php");
	}
}
?>