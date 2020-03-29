<?php
define('IN_ADMIN',TRUE);
require_once(THE_GUTS_DIR.'/includes/classes/global/pages.php');
require_once(THE_GUTS_DIR.'/includes/classes/global/users.php');

if ( $_SESSION['reset']['logged_in'] != '' && $logged_out == '' )
{
	$User = new users;
	$User->logged_in_user = $_SESSION['reset']['user_id'];
	
	// test example using iframe
	if ( $_REQUEST['admin_action'] == 'logout' )
	{
		$req_file = 'tools/login/logout.php';
	}
	elseif ( $_SESSION['reset']['password_change'] )
	{
		$req_file = 'tools/login/password_reset.php';
	}
	elseif ( $_REQUEST['admin_action'] == 'google' )
	{
		$iframe = true;
		$url = "http://google.com";
	}
	elseif ($_REQUEST['admin_action'] == 'pages' )
	{
		$req_file = 'tools/pages/control.php';
	}
	elseif ( $_REQUEST['admin_action'] == 'module_manager')
	{
		$req_file = 'tools/module_manager/control.php';
	}
	elseif ( $_REQUEST['admin_action'] == 'users' || $_REQUEST['admin_action'] == 'groups' || $_REQUEST['admin_action'] == 'permissions' ) //users and groups
	{
		$req_file = 'tools/users/control.php';
	}
	elseif ( $_REQUEST['admin_action'] == 'forms'){
		$req_file = 'tools/forms/control.php';
	}
	elseif ( is_file(ABS_SITE_DIR."/admin/modules/".$_REQUEST['admin_action']."/control.php") )
	{
		$req_file = ABS_SITE_DIR."/admin/modules/".$_REQUEST['admin_action']."/control.php";
	}
	elseif ( is_file(ABS_SITE_DIR."/admin/modules/".$_REQUEST['admin_action']."/index.php") )
	{
		$req_file = ABS_SITE_DIR."/admin/modules/".$_REQUEST['admin_action']."/index.php";
	}
	else
	{
		$req_file = 'home.php';
	}
}
else
{
	$req_file = 'tools/login/control.php';
}

if ( $_REQUEST['nt'] != '' )
{
	require($req_file);
}
else
{
	require("template.php");
}

?>