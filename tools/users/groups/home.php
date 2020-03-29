<h1>Groups</h1>
<?php
if ( !defined("CMS_GROUP_SEATS") || $user_group_seats_left > 0 )
{
?>
	<a href="./?admin_action=<?=$_REQUEST['admin_action']?>&admin_subaction=create" class="list_button">Create New Group</a>
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<?php
}
?>
<a href="./?admin_action=users" class="list_button">View Users List</a>
<br /><br />
<?php
if ( defined("CMS_GROUP_SEATS") ){
	?><strong><span id="user_seats"><?=$user_group_seats_left?></span></strong> of <?=CMS_GROUP_SEATS?> group seats left<br /><br /><?php
}
?>
<table border="0" cellpadding="5" cellspacing="0" width="100%">
	<tr>
		<td class="list_head">
			Group
		</td>
		<td class="list_head">
			# of Members
		</td>
		<td class="list_head">
			Actions
		</td>
	</tr>
<?php
for ( $i=0; $i<count($groups); $i++){
	?>
	<tbody id="group_<?=$groups[$i]['group_id']?>">
		<tr>
			<td class="list_td">
				<?=$groups[$i]['group_name']?>
			</td>
			<td class="list_td">
				<?=$groups[$i]['member_count']?>
			</td>
			<td class="list_td">
				<a href="./?admin_action=<?=$_REQUEST['admin_action']?>&admin_subaction=members&group_id=<?=$groups[$i]['group_id']?>" class="list_button">Members</a>
				&nbsp;&nbsp;
				<a href="./?admin_action=<?=$_REQUEST['admin_action']?>&admin_subaction=edit&group_id=<?=$groups[$i]['group_id']?>" class="list_button">Edit</a>
				&nbsp;&nbsp;
				<a href="./?admin_action=permissions&group_id=<?=$groups[$i]['group_id']?>" class="list_button">Permissions</a>
				&nbsp;&nbsp;
				<a href="javascript:;" class="list_button delete_group" id="delete_<?=$groups[$i]['group_id']?>">Delete</a>
			</td>
		</tr>
	</tbody>
	<?php
}
?>
</table>

<script language="javascript">
	$().ready(function(){
		$(".delete_group").click(function(){
			if ( confirm("Are you sure you want to delete this group?  This cannot be undone!") ){
				var group_id = $(this).attr('id').replace('delete_','');
				$.ajax({
					url: "./?admin_action=<?=$_REQUEST['admin_action']?>&admin_subaction=delete&nt=1",
					type: "POST",
					data: "group_id="+group_id,
					success: function(msg){
						$("#group_"+group_id).fadeOut();
					}
				})
			}
		})
	})
</script>