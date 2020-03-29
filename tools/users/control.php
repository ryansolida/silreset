<?php
$users_class = new users;
$subaction = $_REQUEST['admin_subaction'];

//============================================================
// 	USERS
//============================================================
if ( $_REQUEST['admin_action'] == 'users' ){
	if ( $subaction == 'create' )
	{
		if ( $_POST ){
			$user_info = array();
			foreach ($_POST as $key=>$value){
				if ( strstr($key,'user_') !== false ){
					$user_info[$key] = $value;
				}
			}
			
			if ( $user_info['user_password'] != '' ){
				$user_info['user_password'] = md5($user_info['user_password']);
			} else {
				unset($user_info['user_password']);
			}
			
			if ( $user_info['user_force_password_reset'] ){
				$user_info['user_force_password_reset'] = 1;
			} else {
				$user_info['user_force_password_reset'] = 0;
			}
			
			$users_class->create_user($user_info);
			header('Location: ./?admin_action=users');
			exit;
		} else {
			require('users/edit_user_form.php');
		}
	}
	elseif ( $subaction == 'edit' ){
		if ( $_POST ){
			$user_info = array();
			foreach ($_POST as $key=>$value){
				if ( strstr($key,'user_') !== false && $key != 'user_id'){
					$user_info[$key] = $value;
				}
			}

			if ( $user_info['user_password'] != '' ){
				$user_info['user_password'] = md5($user_info['user_password']);
			} else {
				unset($user_info['user_password']);
			}
			
			if ( $user_info['user_force_password_reset'] ){
				$user_info['user_force_password_reset'] = 1;
			} else {
				$user_info['user_force_password_reset'] = 0;
			}
			
			$users_class->update_user($_POST['user_id'],$user_info);
			header('Location: ./?admin_action=users');
			exit;
		} else {
			$user = $users_class->get_user($_REQUEST['user_id']);
			require('users/edit_user_form.php');
		}
	}
	elseif ( $subaction == 'delete' ){
		if ( $_POST ){
			$users_class->delete_user($_REQUEST['user_id']);
		}
	}
	elseif ( $subaction == 'groups' )
	{
		$user = $users_class->get_user($_REQUEST['user_id']);
		if ( $_REQUEST['action'] == 'add_user_to_group' ){ //add user to group
			$users_class->add_user_to_group($_REQUEST['user_id'],$_REQUEST['group_id']);
		} elseif ( $_REQUEST['action'] == 'remove_user_from_group' ){ //remove user from group
			$users_class->remove_user_from_group($_REQUEST['user_id'],$_REQUEST['group_id']);
		} elseif ($_REQUEST['action'] == 'get_groups_table' ){ //get groups table
			require('users/groups_table.php');
		} else {
			$groups = $users_class->get_groups();
			require('users/groups.php');
		}
	}
	else
	{
		$users = $users_class->get_users();
		$user_seats_left = $users_class->get_open_user_seats();
		require('users/home.php');
	}
//============================================================
// 	GROUPS
//============================================================
} elseif ( $_REQUEST['admin_action'] == 'groups' ){
	if ( $subaction == 'members' ) //group members
	{ 
		if ( $_REQUEST['action'] == 'add_user_to_group' ){ //add user to group
			$users_class->add_user_to_group($_REQUEST['user_id'],$_REQUEST['group_id']);
		} elseif ( $_REQUEST['action'] == 'remove_user_from_group' ){ //remove user from group
			$users_class->remove_user_from_group($_REQUEST['user_id'],$_REQUEST['group_id']);
		} elseif ( $_REQUEST['action'] == 'get_members_table' ){ //get list
			$users = $users_class->get_group_members($_REQUEST['group_id']);
			require('groups/members_table_display.php');
		} else { //get view
			$group = $users_class->get_group($_REQUEST['group_id']);
			$users = $users_class->get_users();
			require('groups/members.php');
		}
	}
	elseif ( $subaction == 'create' )
	{
		if ( $_POST ){
			$group_info = array();
			foreach ($_POST as $key=>$value){
				if ( strstr($key,'group_') !== false ){
					$group_info[$key] = $value;
				}
			}
			
			$users_class->create_group($group_info);
			header('Location: ./?admin_action=groups');
			exit;
		} else {
			require('groups/edit_group_form.php');
		}
	}
	elseif ( $subaction == 'edit' ){
		if ( $_POST ){
			$group_info = array();
			foreach ($_POST as $key=>$value){
				if ( strstr($key,'group_') !== false && $key != 'group_id'){
					$group_info[$key] = $value;
				}
			}

			$users_class->update_group($_POST['group_id'],$group_info);
			header('Location: ./?admin_action=groups');
			exit;
		} else {
			$group = $users_class->get_group($_REQUEST['group_id']);
			require('groups/edit_group_form.php');
		}
	}
	elseif ( $subaction == 'delete' ){
		if ( $_POST ){
			$users_class->delete_group($_REQUEST['group_id']);
		}
	}
	else
	{
		$groups = $users_class->get_groups();
		$user_group_seats_left = $users_class->get_open_user_group_seats();
		require('groups/home.php');
	}
}
//============================================================
// 	PERMISSIONS
//============================================================
elseif ( $_REQUEST['admin_action'] == 'permissions' ){
	if ( $subaction == 'get_perms_data' ){
		//pull permission data
		$type = 'user';
		if ( $_REQUEST['group_id'] ){
			$type = 'group';
		}
		$entity_id = $_REQUEST[$type."_id"];
		echo json_encode($users_class->get_page_perms($type, $entity_id));
		exit;
	}
	elseif( $subaction == 'assign_perms_data' ){
		$type = 'user';
		if ( $_REQUEST['group_id'] ){
			$type = 'group';
		}
		if ( $_REQUEST['page_id'] ){ //page permissions
			///$method = "set_".str_replace('_id','',$type)."_page_perms";
			$users_class->set_page_perms($type,$_REQUEST[$type.'_id'],$_REQUEST['page_id'],$_REQUEST['action'],$_REQUEST['perm']);	
		}
		if ( $_REQUEST['module_id'] ){ //module permissions
			$method = "set_".$type."_module_perms";
			$users_class->$method($_REQUEST[$type."_id"],$_REQUEST['module_id'],explode(',',$_REQUEST['perms']));
		}
	}
	elseif ( $subaction == 'modules' ){
		if ( $_REQUEST['user_id'] != '' ){
			$user_info = $users_class->get_user($_REQUEST['user_id']);
			$display_name = $user_info['user_firstname'].' '.$user_info['user_lastname'];
		}
		if ( $_REQUEST['group_id'] ){
			$group_info = $users_class->get_group($_REQUEST['group_id']);
			$display_name = $group_info['group_name'];
		}
				
		require_once(THE_GUTS_DIR.'/includes/classes/private/modules.php');
		$modules_class = new modules;
		$modules = $modules_class->get_modules();
		require('permissions/modules.php');
	}
	elseif ( $subaction == '' || $subaction == 'pages' ){
		if ( $_REQUEST['user_id'] != '' ){
			$user_info = $users_class->get_user($_REQUEST['user_id']);
			$display_name = $user_info['user_firstname'].' '.$user_info['user_lastname'];
		}
		if ( $_REQUEST['group_id'] ){
			$group_info = $users_class->get_group($_REQUEST['group_id']);
			$display_name = $group_info['group_name'];
		}
		require('permissions/pages.php');
	}
}