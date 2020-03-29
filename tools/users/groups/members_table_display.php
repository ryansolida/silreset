<?php
if ( count($users) == 0 ){
	?>
	<strong>There are no users currently in this group.</strong>
	<?php
} else {
	?>
	<table border="0" cellspacing="0" cellpadding="5" width="100%">
		<tr>
			<td class="list_head">
				Username
			</td>
			<td class="list_head">
				Email
			</td>
			<td class="list_head">
				FIrst Name
			</td>
			<td class="list_head">
				Last Name
			</td>
			<td class="list_head"></td>
		</tr>
		<?php
		for ($i=0; $i<count($users); $i++ ){
			?>
			<tr>
				<td class="list_td"><?=$users[$i]['user_login']?></td>
				<td class="list_td"><?=$users[$i]['user_email']?></td>
				<td class="list_td"><?=$users[$i]['user_firstname']?></td>
				<td class="list_td"><?=$users[$i]['user_lastname']?></td>
				<td class="list_td"><a href="javascript:;" id="remove_<?=$users[$i]['user_id']?>" class="remove_from_group list_button">Remove</a></td>
			</tr>
			<?php
		}
		?>
	</table>
	<?php
}
?>

<script language="javascript">
	$().ready(function(){
		$(".remove_from_group").click(function(){
			user_id = $(this).attr('id').replace('remove_','');
			if ( confirm("Are you sure you want to remove this user from this group?") ){
				$(this).html("Removing...");
				$.ajax({
					url: './?nt=1&admin_action=<?=$_REQUEST['admin_action']?>&admin_subaction=<?=$_REQUEST['admin_subaction']?>&action=remove_user_from_group&group_id=<?=$_REQUEST['group_id']?>&user_id='+user_id,
					success: function(msg){
						get_members_table();
					}
				})
			}
		})
	})
</script>