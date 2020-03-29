<h1>Users</h1>
<?php
if ( !defined("CMS_USER_SEATS") || $user_seats_left > 0 )
{
?>
	<a href="./?admin_action=<?=$_REQUEST['admin_action']?>&admin_subaction=create" class="list_button">Create New User</a>
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<?php
}
?>
<a href="./?admin_action=groups" class="list_button">View Groups List</a>
<br /><br />
<?php
if ( defined("CMS_USER_SEATS") ){
	?><strong><span id="user_seats"><?=$user_seats_left?></span></strong> of <?=CMS_USER_SEATS?> user seats left<br /><br /><?php
}

if ( !$users ){
	?>
	<strong>This site currently has no users.</strong><br />
	To create your first user <a href="./?admin_action=<?=$_REQUEST['admin_action']?>&admin_subaction=create">Click Here.</a>
	<?php
}
else {
	?>
	<table border="0" cellpadding="5" cellspacing="0" width="100%">
		<tr>
			<td class="list_head">
				Username
			</td>
			<td class="list_head">
				Actions
			</td>
		</tr>
	<?php
	for ( $i=0; $i<count($users); $i++){
		?>
		<tbody id="user_<?=$users[$i]['user_id']?>">
			<tr>
				<td class="list_td">
					<?=$users[$i]['user_login']?>
				</td>
				<td class="list_td">
					<a href="./?admin_action=<?=$_REQUEST['admin_action']?>&admin_subaction=edit&user_id=<?=$users[$i]['user_id']?>" class="list_button">Edit</a>
					&nbsp;&nbsp;
					<a href="./?admin_action=<?=$_REQUEST['admin_action']?>&admin_subaction=groups&user_id=<?=$users[$i]['user_id']?>" class="list_button">Groups</a>
					&nbsp;&nbsp;
					<a href="./?admin_action=permissions&user_id=<?=$users[$i]['user_id']?>" class="list_button">Permissions</a>
					&nbsp;&nbsp;
					<a href="javascript:;" class="list_button delete_user" id="delete_<?=$users[$i]['user_id']?>">Delete</a>
				</td>
			</tr>
		</tbody>
		<?php
	}
	?>
	</table>
	
	<script language="javascript">
		$().ready(function(){
			$(".delete_user").click(function(){
				if ( confirm("Are you sure you want to delete this user?  This cannot be undone!") ){
					var user_id = $(this).attr('id').replace('delete_','');
					$.ajax({
						url: "./?admin_action=<?=$_REQUEST['admin_action']?>&admin_subaction=delete&nt=1",
						type: "POST",
						data: "user_id="+user_id,
						success: function(msg){
							$("#user_"+user_id).fadeOut();
							$("#user_seats").html(parseInt($("#user_seats").html())+1);
						}
					})
				}
			})
		})
	</script>
<?php
}
?>