<h1>Reset Password</h1>
<?php
if ( $_POST ){
	$User->update_user($_SESSION['reset']['user_id'],array('user_password'=>md5($_POST['password']),'user_force_password_reset'=>0));
	unset($_SESSION['reset']['password_change']);
	?>
	You have successfully reset your password.<br /><br />
	<a href="/admin">&laquo; Go back to the dashboard</a>
	<?php
}
else
{
	?>
	Resetting password for <?=$_SESSION['reset']['user_firstname']?> <?=$_SESSION['reset']['user_lastname']?><br /><br />
	<form id="password_reset" action="./?admin_action=change_password" method="POST">
		<div style="border: 2px solid #CCC; padding: 20px; background-color: #F9F9F9; width: 400px">
			<div id="pw_alert" style="display: none; margin-bottom: 10px; padding: 10px; border: 1px solid red; color: red; font-size: 80%; font-weight: bold; background-color: #FFF"></div> 
			Password: <br />
			<Input type="password" class="sys_input required" id="password" name="password">
			<br /><br />
			Confirm Password: <br />
			<Input type="password" class="sys_input required" id="password_confirm" name="password_confirm">
		</div>
		<br /><br />
		<input type="submit" class="sys_submit" id="submit_btn" value="Change Password">
	</form>
	
	<script language="javascript">
		$().ready(function(){
			$("#password_reset").submit(function(){
				$("#pw_alert").hide();
				$("#submit_btn").val('Changing Password...');
				var ok_to_go = true;
				
				$(".required").each(function(){
					if ( $(this).val() == '' && ok_to_go ){
						ok_to_go = false;
						$(this).focus();
						$("#pw_alert").html("You must fill in all fields").fadeIn(250);
					}
				})
				
				if ( ok_to_go && $("#password").val() != $("#password_confirm").val() ){
					$("#pw_alert").html("The passwords do not match").fadeIn(250);
					ok_to_go = false;
				}
				
				if ( $("#password").val().length < 6 ){
					$("#pw_alert").html("Your password must be at least 6 characaters").fadeIn(250);
					$("#password").focus();
					ok_to_go = false;
				}
	
				if ( !ok_to_go ){
					$("#submit_btn").val('Change Password');
					return false;
				}
			})
			
			$("#password").focus();
		})	
	</script>
<?php
}
?>
