<h2><?=$user['user_firstname']?> Groups</h2>
	<div id="groups_table">Loading...</div>
<br /><br />
<div style="border: 2px solid #EEE; padding: 10px; background-color: #F9F9F9">
	Add <?=$user['user_firstname']?> to <select id="user_to_group">
		<option value="0">Choose Group</option>
		<?php
		for ($i=0; $i<count($groups); $i++){
			?><option value="<?=$groups[$i]['group_id']?>"><?=$groups[$i]['group_name']?></option><?php
		}
		?>	. &nbsp;&nbsp;&nbsp;<input type="button" id="add_to_group" value="Add To Group">
</div>
<br /><br />
<a href="./?admin_action=<?=$_REQUEST['admin_action']?>">&laquo; Back To Users</a>

<script language="javascript">
	$().ready(function(){
		get_groups_table();
		
		$("#add_to_group").click(function(){ //add user to group
			group_id = $("#user_to_group").val();
			if ( group_id == 0 ){
				return false;
			}
			
			$.ajax({
				url: './?nt=1&admin_action=<?=$_REQUEST['admin_action']?>&admin_subaction=<?=$_REQUEST['admin_subaction']?>&action=add_user_to_group&user_id=<?=$_REQUEST['user_id']?>&group_id='+group_id,
				success: function(msg){
					get_groups_table();
					$("#user_to_group").val(0);
				}
			})
		})
	})
	
	function get_groups_table(){
		$.ajax({
			url: './?admin_action=<?=$_REQUEST['admin_action']?>&admin_subaction=<?=$_REQUEST['admin_subaction']?>&action=get_groups_table&user_id=<?=$_REQUEST['user_id']?>&nt=1',
			success: function(msg){
				$("#groups_table").html(msg);
			}
		})
	}
</script>