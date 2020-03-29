<h1>Forms</h1>
<a href="./?admin_action=<?=$_REQUEST['admin_action']?>&admin_subaction=create_form" class="list_button">Create New Form</a>
<br /><br />
<?php
$forms = $Forms->get_forms();
if ( count($forms) > 0 ){
?>
	<table border="0" cellspacing="0" cellpadding="5" width="100%">
		<tr>
			<td class="list_head">
				Form Name
			</td>
			<td class="list_head" colspan="10"></td>
		</tr>
		<?php
		for ($i=0; $i<count($forms); $i++ ){
			?>
			<tr>
				<td class="list_td"><?=$forms[$i]['form_name']?></td>
				<td class="list_td"><a href="./?admin_action=<?=$_REQUEST['admin_action']?>&admin_subaction=view_questions&form_id=<?=$forms[$i]['form_id']?>" class="list_button">Edit Questions</a>
				&nbsp;&nbsp;&nbsp;&nbsp;<a href="./?admin_action=<?=$_REQUEST['admin_action']?>&admin_subaction=edit_form&form_id=<?=$forms[$i]['form_id']?>" class="list_button">Edit Information</a>
				&nbsp;&nbsp;&nbsp;&nbsp;<a href="./?admin_action=<?=$_REQUEST['admin_action']?>&admin_subaction=view_results&form_id=<?=$forms[$i]['form_id']?>" class="list_button">View Results (<?=count($Forms->get_submission_count($forms[$i]['form_id']))?>)</a></td>
			</tr>
			<?php
		}
		?>
	</table>
<?php
}
else {
	?><strong>You currently have no forms</strong><br />
	To create your first form <a href="./?admin_action=<?=$_REQUEST['admin_action']?>&admin_subaction=create_form">Click Here</a>
	<?php
}
?>