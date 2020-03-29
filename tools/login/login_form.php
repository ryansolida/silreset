<div id="login_form" class="round5 border2 bg_color3">
	<?php
	if ( $login_failed == 1 )
	{
		?>
		<div id="failed_login">Incorrect Login!</div>
		<?php
	}
	?>
	<h2>Please Log In</h2>
	<form action="/admin/" method="POST">
	<input type="hidden" name="login_form_submit" value="true">
	Username:<br /><input type="text" class="sys_input" name="login_username" id="user_login" /><br /><br />
	Password:<br /> <input type="password" class="sys_input" name="login_password" /><br /><br />
	<input type="submit" class="list_button" value="Log In">
	</form>
</div>

<script language="javascript">
	$().ready(function(){
		$("#user_login").focus();
	})
</script>