<h1>Form Results</h1>
<?php
if ( $submission ){
	?>
	<a href="./?admin_action=<?=$_REQUEST['admin_action']?>&admin_subaction=<?=$_REQUEST['admin_subaction']?>&form_id=<?=$_REQUEST['form_id']?>" class="list_button">Back To Results</a><br /><br />
	<?php
	$answers = json_decode($submission['answer_data'],TRUE);
	foreach ($answers as $answer){
		?>
		<strong><?=stripslashes($answer['question'])?>: </strong><?=stripslashes($answer['answer'])?><br />
		<?php
	}
}
else {
	if ( count($submissions) > 0 ){
		?>
		<a href="./?admin_action=<?=$_REQUEST['admin_action']?>" class="list_button">Back To Forms</a><br /><br />
		<table border="0" cellspacing="0" cellpadding="5" width="100%">
			<tr>
				<td class="list_head">
					Submission
				</td>
				<td class="list_head" colspan="10"></td>
			</tr>
			<?php
			for ($i=0; $i<count($submissions); $i++ ){
				?>
				<tr>
					<td class="list_td"><?=date("M j, Y g:ia",strtotime($submissions[$i]['submitted_datetime']))?></td>
					<td class="list_td"><a href="./?admin_action=<?=$_REQUEST['admin_action']?>&admin_subaction=view_results&submission_id=<?=$submissions[$i]['id']?>&form_id=<?=$_REQUEST['form_id']?>" class="list_button">View Submission</a></td>
				</tr>
				<?php
			}
		?>
		</table>
		<?php
	}
}
?>