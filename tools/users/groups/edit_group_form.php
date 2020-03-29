<?php
$head_text = "Create New Group";
$submit_text = "Create Group";
if ($_REQUEST['user_id'] != '' ){
	$head_text = "Edit Group";
	$submit_text = "Update Group";	
}
?>
<form action="./" method="POST">
<div class="sys_form">
	<div class="sys_form_container">
		<h2><?=$head_text?></h2>
		<div class="form_body">
			<input type="hidden" name="nt" value="1">
			<input type="hidden" name="admin_action" value="<?=$_REQUEST['admin_action']?>">
			<input type="hidden" name="admin_subaction" value="<?=$_REQUEST['admin_subaction']?>">
			<input type="hidden" name="group_id" value="<?=$_REQUEST['group_id']?>">
			<strong>Group Name: </strong><br /><input type="text" name="group_name" class="required sys_input" value="<?=$group['group_name']?>"><br /><br />
			<strong>Group Description: </strong><br /><textarea class="sys_input" cols="50" rows="3" name="group_desc" class="required sys_input"><?=$group['group_desc']?></textarea><br /><br />
		</div>
		<div class="form_actions">
			<input type="submit" value="<?=$submit_text?>"> or <a href="./?admin_action=<?=$_REQUEST['admin_action']?>">Cancel</a>
		</div>
	</div>
</div>
</form>