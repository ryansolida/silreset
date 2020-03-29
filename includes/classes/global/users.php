<?php
class users
{
	function __construct(){
		$this->db = new db;
		$this->logged_in_user = false; //optional class var
	}
	
	//============================================================
	//
	// 	USER RETRIEVAL
	//
	//============================================================		
			function get_users($user_arr = false){
				//============================================================
				// 	user_arr
				//			-	search (key=>value array of search fields
				//			- page_perms (ID of page you want to find users with permissions for)
				//			- module_perms (ID of module you want to find users with permissions for)		
				//			- user_id (ID of user for permissions matching
				//============================================================
				$db = new db;
				
				//general info
				if ( is_array($user_arr['search']) ){
					$query_str = "SELECT * FROM users WHERE ";
					$search_count = 0;
					foreach ($user_arr['search'] as $key=>$value) {
						if ( $search_count > 0 ){
							$query_str .= "AND ";
						}
						$query_str .= "`$key`= '".addslashes($value)."'";
						$search_count++;
					}
					
				} else {
					$query_str = "SELECT * FROM users";
				}
		
				$users = $db->qquery($query_str);
				
				//with page permissions
				if ( $user_arr['page_perms'] ){
					$query_str = "SELECT * FROM users AS u JOIN user_group_perms AS p ON u.user_id WHERE p.page_id = ".$user_arr['page_perms']." AND p.user_id = " . $user_arr['user_id'];
					$users = $db->qquery($query_str);
				}
				
				//with module permissions
				if ( $user_arr['module_perms'] ){
					$query_str = "SELECT * FROM users AS u JOIN user_group_perms AS p ON u.user_id WHERE p.module_id = ".$user_arr['module_perms']." AND p.user_id = " . $user_arr['user_id'];
					$users = $db->qquery($query_str);
				}
				
				//in a certain group
				if ( $user_arr['group'] ){
					$query_str = "SELECT * FROM users AS u JOIN user_group_assoc AS a ON u.user_id = a.user_id WHERE a.group_id = " . $user_arr['group'];
					$users = $db->qquery($query_str);
				}
				
				$user_count = count($users);
				
				if ( $user_count > 0 ){
				                                   
					for ($i=0; $i<$user_count; $i++){
						//============================================================
						// 	GET GROUPS
						//============================================================
						$users[$i]['groups'] = false;				
						$query_str = "SELECT * FROM user_group_assoc WHERE user_id = ".$users[$i]['user_id'];
						$groups = $db->qquery($query_str);
						$groups_count = count($groups);
						if ( $groups_count > 0 ){
							$users[$i]['groups'] = array();
							foreach ($groups as $group){
								//$users[$i]['groups'][] = $group['group_id'];	
								$group_info = $this->db->getwhere('user_groups',array('group_id'=>$group['group_id']));
								$users[$i]['groups'][] = $group_info[0];
							}
						}
					}
					
					return $users;
				}
				
				return FALSE;
				
			}
		
			function get_user($search_arr){	
				//if search_arr is numeric, we assume a user ID
				if ( is_numeric($search_arr) ){
					$search_arr = array('user_id'=>$search_arr);
				}
				
				$user = $this->get_users(array('search'=>$search_arr));
				
				if ( $user ){
					return $user[0];
				}
				
				return FALSE;
			}	
	//============================================================
	//
	// 	END USER RETRIEVAL
	//
	//============================================================
	
	
	//============================================================
	// 
	//		USER MODIFICATION
	//
	//============================================================
			function create_user($user_arr){
				$db = new db;
				$db->insert('users',$user_arr);
				return TRUE;
			}
			
			function update_user($user_id, $user_arr){
				$db = new db;
				$db->update('users',$user_arr,array('user_id'=>$user_id));
				return TRUE;
			}
			
			function delete_user($user_id){
				$db = new db;
				//delete user from groups
				//delete user permissions
				//delete user
				$db->set_query_str("DELETE FROM users WHERE user_id = $user_id");
				$db->db_query();
			}
	//============================================================
	// 
	//		END USER MODIFICATION
	//
	//============================================================
	
	//============================================================
	// 
	//		PERMISSION RETRIEVAL
	//
	//============================================================
			function get_permissions($perm_arr){
				//subject
				if ( $perm_arr['user_id'] ){
					$subject_type = 'user';
					$subject_id = $perm_arr['user_id'];
				}
				
				if ( $perm_arr['group_id'] ){
					$subject_type = 'group';
					$subject_id = $perm_arr['group_id'];
				}
				
				//object
				if ( $perm_arr['page_id'] ){
					$object_type = 'page';
					$object_id = $perm_arr['page_id'];
					$permissions_list_method = 'get_page_permission_options';
				}
				
				if ( $perm_arr['module_id'] ){
					$object_type = 'module';
					$object_id = $perm_arr['module_id'];
					$permissions_list_method = 'get_module_permission_options';
				}
				
				//let's pull the user record to see if he/she is an admin
				$is_admin = false;
				if ( $subject_type == 'user' ){
					$user = $this->get_user($subject_id);
					if ( $user['user_priv_level'] >= 50 ){
						$is_admin = true;
					}
				}
				
				//see if user has page perms
				$query_str = "SELECT permissions FROM user_group_perms WHERE {$subject_type}_id = $subject_id AND {$object_type}_id = '$object_id'";
				$results = $this->db->qquery($query_str);
				
				$permissions = array();
				if ( $results[0]['permissions'] != '' ){
					$permissions = json_decode($results[0]['permissions'], true);
				}
			
				//if we're specifically looking for user
				if ( $subject_type == 'user' && !$perm_arr['exclusive'] ) //we can pass exclusive=true to retrieve ONLY the users records and skip the group
				{
					//see if any of the users groups have perms
					$user = $this->get_user($subject_id);
					$group_page_permissions = false;
					if ( $user['groups'] ){
						$group_page_permissions = array();
						foreach ( $user['groups'] as $group ){
							$query_str = "SELECT permissions FROM user_group_perms WHERE group_id = {$group['group_id']} AND {$object_type}_id = '$object_id'";
							$results = $this->db->qquery($query_str);
							if ( count($results) > 0 ){
								$group_permissions[] = json_decode($results[0]['permissions'], true);
							}
						}
					}
					
					//let's get all of our permissions into one level
					for ($i=0; $i<count($group_permissions); $i++){
						foreach($group_permissions[$i] as $permission){
							$permissions[] = $permission;
						}
					}
					
				}
				
				//now let's loop through available permissions and see what we have
				$return_perms = array();
				$perms = $this->$permissions_list_method();
				foreach ($perms as $perm){
					$return_perms[$perm] = false;
					//look through user perms
					if ( in_array($perm, $permissions) || $is_admin ){
						$return_perms[$perm] = true;
					}
				}
				
				return $return_perms;
			}
			
			//============================================================
			// 	USER CAN
			//============================================================
			function user_can($can_arr=false,$user_id=false){
				if ( !$can_arr ){
					return FALSE;
				}
				
				if ( !is_array($can_arr) ){ //if we passed a string --- ex: "edit page 2" turns into "action: edit, type: page, id: 2"
					//allow for more human readable strings
					$strip_words = array(
						' in ',
						' of '
					);
					foreach ($strip_words as $word){
						$can_arr = str_replace($word, ' ', $can_arr);
					}
					
					//break up each space and assign to arr param
					$explode_string = explode(" ",$can_arr);
					$can_arr = array(
						'action'=>$explode_string[0],
						'type'=>$explode_string[1],
						'criteria'=>$explode_string[2]
					);
					
					if ( $can_arr['type'] == 'module' ){
						$can_arr['criteria'] = str_replace('/',':',$can_arr['criteria']); //modules in permissions table have a : instead of a /
					}
					
				}
				
				if ( !$user_id ){
					if ( !$this->logged_in_user ){ //if there's not user id to be found, kick em back
						return FALSE;
					} else {
						$user_id = $this->logged_in_user;
					}
				}
				
				//default to page
				$id_type = 'page_id';
				if ( $can_arr['type'] == 'module' ){
					$id_type = 'module_id';
				}	
				
				$perms = $this->get_permissions(array(
					'user_id'=>$user_id,
					$id_type=>$can_arr['criteria'] //ex: page_id=2 or module_id=9
				));
				
				
				//if we find from our get_permissions_array that we have the permission, return TRUE
				if ( $perms[$can_arr['action']] || $_SESSION['reset']['user_priv_level'] >= 50 ){
					return TRUE;
				}
								
				return FALSE;
			}
	//============================================================
	// 	
	//		END PERMISSION RETRIEVAL
	//
	//============================================================
	
	
	//============================================================
	//
	// 	SET PERMISSIONS
	//
	//============================================================
			function set_page_perms($entity_type, $entity_id, $page_id, $action, $perm){
				//this page and all descendents
				$pages = new pages;
				$page_array = $pages->get_descendents_of($page_id);
				$page_array[] = $page_id;
				
				//insert new record
				foreach ($page_array as $page){
					//get current record
					$cur_perms_query  = $this->db->getwhere('user_group_perms',array($entity_type.'_id'=>$entity_id,'page_id'=>$page));
					
					$cur_perms = array();
					if ( count($cur_perms_query) > 0 )
					{
						$cur_perms = json_decode($cur_perms_query[0]['permissions'],TRUE);
					}

					if ( $action == 'add' ){
						if ( $perm == 'all' ){
							$cur_perms = $this->get_page_permission_options();
						}
						else {
							if ( !in_array($perm, $cur_perms) ){
								$cur_perms[] = $perm;
							}
						}
					}
					elseif ( $action == 'remove' ){
						if ( $perm == 'all' ){
							$cur_perms = array(); //reset to blank array
						}
						else {
							if ( in_array($perm, $cur_perms) ){
								$key = array_search($perm,$cur_perms);
								unset($cur_perms[$key]);
							}
						}
					}
					
					//delete current record
					$this->db->set_query_str("DELETE FROM user_group_perms WHERE ".$entity_type."_id = $entity_id AND page_id = $page");
					$this->db->db_query();
					
					
					//insert new record
					$this->db->insert('user_group_perms',array($entity_type.'_id'=>$entity_id,'page_id'=>$page,'permissions'=>json_encode($cur_perms)));
				}
				return TRUE;
			}
			
			function set_user_module_perms($user_id, $module_id, $perms=false){
				//delete any current records
				$this->db->set_query_str("DELETE FROM user_group_perms WHERE user_id = $user_id AND module_id = '$module_id'");
				$this->db->db_query();
				
				if ( $perms )
				{
					//insert new record
					$this->db->insert('user_group_perms',array('user_id'=>$user_id,'module_id'=>$module_id,'permissions'=>json_encode($perms)));
					return TRUE;
				}
			}
			
			function set_group_page_perms($group_id, $page_id, $perms){
				//delete any current records
				$this->db->set_query_str("DELETE FROM user_group_perms WHERE group_id = $group_id AND page_id = $page_id");
				$thid->db->db_query();
				
				//insert new record
				$this->db->insert('user_group_perms',array('group_id'=>$group_id,'page_id'=>$page_id,'perms'=>json_encode($perms)));
				return TRUE;
			}
	
			function set_group_module_perms($group_id, $module_id, $perms){
				//delete any current records
				$this->db->set_query_str("DELETE FROM user_group_perms WHERE group_id = $group_id AND module_id = '$module_id'");
				$this->db->db_query();
				
				//insert new record
				$this->db->insert('user_group_perms',array('group_id'=>$group_id,'module_id'=>$module_id,'permissions'=>json_encode($perms)));
				return TRUE;
			}
	
	//============================================================
	//
	// 	END SET PERMISSIONS
	//
	//============================================================
	

	
	//============================================================
	//
	// 	GROUPS RETRIEVAL
	//
	//============================================================
			function get_groups($search_arr=false){
				if ( !$search_arr ){
					$groups = $this->db->qquery("SELECT * FROM user_groups");
				} else {
					
					$groups = $this->db->getwhere('user_groups',$search_arr);
				}
				
				if ( !$groups ){
					return FALSE;
				}
				
				for($i=0; $i<count($groups); $i++ ){
					$groups[$i]['member_count'] = $this->get_group_member_count($groups[$i]['group_id']);
				}
						
				return $groups;
			}
			
			function get_group($search_arr){
				if ( is_numeric($search_arr) ){
					$search_arr = array('group_id'=>$search_arr);
				}
				
				$groups = $this->get_groups($search_arr);
				return $groups[0];
			}
			
			function get_group_members($group_id){
				$group_members = $this->db->getwhere('user_group_assoc',array('group_id'=>$group_id));
				$group_member_details = array();
				for ($i=0; $i<count($group_members); $i++ ){
					$user = $this->db->getwhere('users',array('user_id'=>$group_members[$i]['user_id']));
					$group_member_details[] = $user[0];
				}
				return $group_member_details;
			}
			
			function get_group_member_count($group_id){
				$results = $this->db->qquery("SELECT user_id FROM user_group_assoc WHERE group_id = $group_id");
				return count($results);
			}
	//============================================================
	// 
	//		END GROUPS RETRIEVAL
	//
	//============================================================
	
	//============================================================
	// 
	//		GROUPS MODIFICATION
	//
	//============================================================
			function create_group($group_arr){
				$this->db->insert('user_groups',$group_arr);
				return TRUE;	
			}
			
			function update_group($group_id, $group_arr){
				$this->db->update('user_groups',$group_arr,array('group_id'=>$group_id));
				return TRUE;
			}
			
			function delete_group($group_id){
				//delete group permissions
				//delete group user relationships
				//delete group
				$this->db->set_query_str("DELETE FROM user_groups WHERE group_id = $group_id");
				$this->db->db_query();
			}
	//============================================================
	// 	
	//		END GROUPS MODIFICATION
	//
	//============================================================
	
	//============================================================
	//
	// 	ASSOC FUNCTIONS
	//
	//============================================================
			function add_user_to_group($user_id, $group_id){
				//remove any current entries
				$this->remove_user_from_group($user_id, $group_id);
				
				//add new
				$this->db->insert('user_group_assoc',array('user_id'=>$user_id,'group_id'=>$group_id));
			}
			
			function remove_user_from_group($user_id, $group_id){
				$this->db->set_query_str("DELETE FROM user_group_assoc WHERE user_id = $user_id AND group_id = $group_id");
				$this->db->db_query();		
			}
	//============================================================
	// 
	//		END ASSOC FUNCTIONS
	//
	//============================================================
		
	//============================================================
	//
	// 	MISC FUNCTIONS
	//
	//============================================================
			function get_open_user_seats(){
				if ( defined("CMS_USER_SEATS") ){
					$users = $this->db->qquery("SELECT user_id FROM users");
					$total_left = CMS_USER_SEATS - count($users);
					return $total_left;
				}
				return FALSE;
			}
			
			function get_open_user_group_seats(){
				if ( defined("CMS_GROUP_SEATS") ){
					$users = $this->db->qquery("SELECT group_id FROM user_groups");
					$total_left = CMS_GROUP_SEATS - count($users);
					return $total_left;
				}
				return FALSE;
			}
			
			//============================================================
			// 	AVAILABLE PAGE PERMS
			//		1. create - allows the user to create a descendent page of the given original page
			//		2. edit_content - allows the user to edit the content of a given page
			//		3. edit_info - allows user to edit overhead info on a page (ex. parent, title, seo, etc)
			//		4. publish - allows the user to publish the page
			//		5. delete - allows the user to delete this page
			//============================================================
			function get_page_permission_options(){
				$permissions = array(
					'create',
					'edit_content',
					'edit_info',
					'publish',
					'reorder',
					'delete'
				);
				
				return $permissions;
			}
			
			//============================================================
			// 	AVAILABLE MODULE PERMS
			//		1. create - allows the user to create a new record
			//		2. edit - allows the user to edit records
			//		3. reorder - allows the user to reorder records
			//		4. publish - allows the user to publish the records
			//		5. delete - allows the user to delete records
			//============================================================
			function get_module_permission_options(){
				$permissions = array(
					'see'
				);
				
				return $permissions;
			}
			
			
			//============================================================
			// 	GET ALL USER PAGE PERMS
			//============================================================
			function get_page_perms($entity_type, $entity_id){
				$pages_class = new pages;
				$pages = $pages_class->get_descendents_of(0);
				$pages_count = count($pages);
				$return_arr = array();
				for ($i=0; $i<$pages_count; $i++){
					$perms = $this->get_permissions(array($entity_type.'_id'=>$entity_id,'page_id'=>$pages[$i]));
					$return_arr[$pages[$i]] = $perms;
				}
				return $return_arr;
			}
	//============================================================
	// 	
	//		END MISC FUNCTIONS
	//
	//============================================================
}
?>