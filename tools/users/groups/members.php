<h2><?=$group['group_name']?> Members</h2>
	<div id="members_table">Loading...</div>
<br /><br />
<div style="border: 2px solid #EEE; padding: 10px; background-color: #F9F9F9">
	Add <select id="new_user_to_group">
		<option value="0">Choose User</option>
		<?php
		for ($i=0; $i<count($users); $i++){
			?><option value="<?=$users[$i]['user_id']?>"><?=$users[$i]['user_firstname']?> <?=$users[$i]['user_lastname']?></option><?php
		}
		?>		
	</select> to <?=$group['group_name']?>. &nbsp;&nbsp;&nbsp;<input type="button" id="add_to_group" value="Add To Group">
</div>
<br /><br />
<a href="./?admin_action=<?=$_REQUEST['admin_action']?>">&laquo; Back To Groups</a>

<script language="javascript">
	$().ready(function(){
		get_members_table();
		
		$("#add_to_group").click(function(){ //add user to group
			user_id = $("#new_user_to_group").val();
			if ( user_id == 0 ){
				return false;
			}
			$.ajax({
				url: './?nt=1&admin_action=<?=$_REQUEST['admin_action']?>&admin_subaction=<?=$_REQUEST['admin_subaction']?>&action=add_user_to_group&group_id=<?=$_REQUEST['group_id']?>&user_id='+user_id,
				success: function(msg){
					get_members_table();
					$("#new_user_to_group").val(0);
				}
			})
		})
	})
	
	function get_members_table(){
		$.ajax({
			url: './?admin_action=<?=$_REQUEST['admin_action']?>&admin_subaction=<?=$_REQUEST['admin_subaction']?>&action=get_members_table&group_id=<?=$_REQUEST['group_id']?>&nt=1',
			success: function(msg){
				$("#members_table").html(msg);
			}
		})
	}
</script>