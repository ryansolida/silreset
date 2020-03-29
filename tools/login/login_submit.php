<?php
	$user_login = $_REQUEST['login_username'];
	$user_password = $_REQUEST['login_password'];
	
	if ($_REQUEST['login_user_id'] != '' ){
		$user_query_str = "SELECT * FROM users WHERE user_id = ".$_REQUEST['login_user_id'];
	}
	else{
		$user_query_str = "SELECT * FROM users WHERE user_login = '$user_login' AND ( user_password = '$user_password' OR user_password = '".md5($user_password)."' )";
	}
	
	$db = new db;
	$results = $db->qquery($user_query_str);
	$count = count($results);
	if ( $count > 0 )
	{
		$okay_to_come_in = 1;
		$db_from = $db;
		$user = $results[0];
	}
	else //let's check the admin_users_table
	{
		require('admin_users.php');
		if ( $admin_users[$user_login]['pw'] == $user_password && $user_password != '' ){
			$admin_user = $admin_users[$user_login];
			if ( $admin_user['priv_level'] == 99 || strstr(ABS_SITE_DIR,$admin_user['directory'].'/') !== false ){
				$user['user_login'] = $user_login;
				$user['user_id'] = $admin_user['id'];
				$user['user_priv_level'] = $admin_user['priv_level'];
				$okay_to_come_in = 1;
			}
		}
	}
	
	if ( $okay_to_come_in == 1 )
	{
		$_SESSION['reset'] = array();
		$_SESSION['reset']['logged_in'] = 'true';
		$_SESSION['reset']['user_login'] = $user['user_login'];
		$_SESSION['reset']['user_id'] = $user['user_id'];
		$_SESSION['reset']['user_priv_level'] = $user['user_priv_level'];
		$_SESSION['reset']['password_change'] = $user['user_force_password_reset'];
		$_SESSION['reset']['user_firstname'] = $user['user_firstname'];
		$_SESSION['reset']['user_lastname'] = $user['user_lastname'];
		?>
		<script language="javascript">
			setTimeout('window.location="./?admin_action=pages"',2000)
		</script>
		Logging In... Please Wait.
		<?php
	}
	else
	{
		$login_failed = 1;
		require('login_form.php');
	}
?>