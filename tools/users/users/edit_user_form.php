<?php
$head_text = "Create New User";
$submit_text = "Create User";
if ($_REQUEST['user_id'] != '' ){
	$head_text = "Edit User";
	$submit_text = "Update User";	
}

$current_users = $User->get_users();

?>
<form id="user_form" action="./" method="POST">
	<div class="sys_form">
		<div class="sys_form_container">
			<h2><?=$head_text?></h2>
			<div class="form_body">
				<div id="form_alert" style="display: none; margin-bottom: 10px; padding: 10px; border: 1px solid red; color: red; font-size: 80%; font-weight: bold; background-color: #FFF"></div> 
				<input type="hidden" name="nt" value="1">
				<input type="hidden" name="admin_action" value="<?=$_REQUEST['admin_action']?>">
				<input type="hidden" name="admin_subaction" value="<?=$_REQUEST['admin_subaction']?>">
				<input type="hidden" name="user_id" value="<?=$_REQUEST['user_id']?>">
				<strong>First Name: </strong><br /><input type="text" name="user_firstname" class="required sys_input" value="<?=stripslashes($user['user_firstname'])?>"><br /><br />
				<strong>Last Name: </strong><br /><input type="text" name="user_lastname" class="required sys_input" value="<?=stripslashes($user['user_lastname'])?>"><br /><br />
				<strong>Email: </strong><br /><input type="text" name="user_email" class="required sys_input" value="<?=stripslashes($user['user_email'])?>"><br /><br />
				<strong>User Type: </strong><br />
					<select name="user_priv_level">
						<?php
						if ($current_users){
							?><option value="0"<?=$user['user_priv_level'] == 0?' SELECTED':''?>>Basic User</option><?php
						} ?>
						<option value="50"<?=$user['user_priv_level'] == 50?' SELECTED':''?>>Site Admin</option>
					</select><br /><br />
				<strong>Login: </strong><br /><input type="text" name="user_login" class="required sys_input" value="<?=stripslashes($user['user_login'])?>"><br /><br />
				<strong>Password: </strong><br /><input type="password" name="user_password" id="user_password" class="<?=$_REQUEST['user_id']?'':'required '?>sys_input" value=""> <span style="color: #AAA; font-size: 90%">(Leave blank to keep current password)</span><br /><br />
				<label style="cursor: pointer"><input type="checkbox" name="user_force_password_reset"<?=$user['user_force_password_reset']==1?' CHECKED':''?>> Force password reset on next login</label><br /><br />
			</div>
			<div class="form_actions">
				<input type="submit" value="<?=$submit_text?>"> or <a href="./?admin_action=<?=$_REQUEST['admin_action']?>">Cancel</a>
			</div>
		</div>
	</div>
</form>

<script language="javascript">
	$("#user_form").submit(function(){
		var ok_to_go = true;
		$(".required").each(function(){
			if ( $(this).val() == '' && ok_to_go ){
				ok_to_go = false;
				show_alert("You must fill in all required fields");
			}
		})
		
		if ( ok_to_go && $("#user_password").val().length < 6 && $("#user_password").val().length > 0 ){
			show_alert("Your password must have at least 6 characters");
			ok_to_go = false;
		}
		
		if ( !ok_to_go ){
			return false;
		}
	})
	
	function show_alert(msg){
		$("#form_alert").html(msg).fadeIn(250);
	}
</script>