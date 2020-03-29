<?php
$groups = $user['groups'];
if ( !is_array($groups) ){
	?>
	<strong>This user is currently in no groups.</strong>
	<?php
} else {
	?>
	<table border="0" cellspacing="0" cellpadding="5" width="100%">
		<tr>
			<td class="list_head">
				Group Name
			</td>
			<td class="list_head"></td>
		</tr>
		<?php
		for ($i=0; $i<count($groups); $i++ ){
			?>
			<tr>
				<td class="list_td"><?=$groups[$i]['group_name']?></td>
				<td class="list_td"><a href="javascript:;" id="remove_<?=$groups[$i]['group_id']?>" class="remove_from_group list_button">Remove</a></td>
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
			group_id = $(this).attr('id').replace('remove_','');
			if ( confirm("Are you sure you want to remove this user from this group?") ){
				$(this).html("Removing...");
				$.ajax({
					url: './?nt=1&admin_action=<?=$_REQUEST['admin_action']?>&admin_subaction=<?=$_REQUEST['admin_subaction']?>&action=remove_user_from_group&user_id=<?=$_REQUEST['user_id']?>&group_id='+group_id,
					success: function(msg){
						get_groups_table();
					}
				})
			}
		})
	})
</script>