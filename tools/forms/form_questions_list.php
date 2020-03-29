<div id="form_questions_list" class="dynamic_form_container">
<?php
if ( count($questions) == 0 ){
	?>
	<div class="form_question_entry">
		<div class="form_field">
			<strong>This form currently has no questions</strong><br />
			Create your first form question by <a href="javascript:;" onclick="$('#create_question').click()">Clicking Here</a>
		</div>
	</div>
	<?php
}
else
{
	for ($i=0; $i<count($questions); $i++ ){
	$question = $questions[$i];
	?>
		<div id="question_<?=$question['question_id']?>" class="form_question_entry">
			<?php
			$form_display = new form_display;
			$function = $question['question_type']."_display";
			$form_display->$function($question);
		?>
			<div class="form_field_buttons">
				<a href="javascript:;" rel="<?=$question['question_id']?>" class="edit_question list_button">Edit Question</a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="javascript:;" rel="<?=$question['question_id']?>" class="delete_question list_button">Delete Question</a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="javascript:;" class="move_question list_button" style="cursor: move">Move Question</a>
			</div>
		</div>
	<?php
	}
	?>
	<script language="javascript">
		$().ready(function(){
			$("#form_questions_list").sortable({
				axis: 'y',
				handle: '.move_question',
				stop: function(){
					var order = '';
					$(".form_question_entry").each(function(){
						if ( order != '' ){
							order += ",";
						}
						order += $(this).attr('id').replace('question_','');
					})
					$.post('./?nt=1&admin_action=<?=$_REQUEST['admin_action']?>&admin_subaction=reorder_questions&form_id=<?=$_REQUEST['form_id']?>&question_order='+order,function(ret){
					})	
				}
			});
			
			$(".edit_question").unbind('click').click(function(){
				question_id = $(this).attr('rel');
				$.get("./?nt=1&admin_action=<?=$_REQUEST['admin_action']?>&admin_subaction=edit_question&form_id=<?=$_REQUEST['form_id']?>&question_id="+question_id,function(data){
					$("#form_questions_container").hide();
					$("#form_question_form").html(data).show();
				});
			})
			
			$(".delete_question").unbind('click').click(function(){
				var question_id = $(this).attr('rel');
				if ( confirm("Are you sure you want to delete this question?") ){
					$.post("./?nt=1&admin_action=<?=$_REQUEST['admin_action']?>&admin_subaction=delete_question", {question_id: question_id}, function(data){
						$("#question_"+question_id).fadeOut(250,function(){
							$(this).remove();
						})
					})
				}
			})
			
			$(".form_question_entry").mouseenter(function(){
				$(this).find('.form_field_buttons').show();
			}).mouseleave(function(){
				$(this).find('.form_field_buttons').hide();
			})
			
			$(".dyn_form_text").addClass('sys_input');
		})
	</script>
	<?php
}
?>
</div>

<style type="text/css">
	#form_questions_list{margin: 5px 25px; border: 15px solid #EEE;}
	.form_field_buttons {position: absolute; right: 15px; top: 15px; display: none}
	.form_question_entry{padding: 5px; border-bottom: solid #DDD; border-width: 0px 2px 2px 2px; background-color: #FFF; position: relative;}
	.dynamic_form_container .required_star{color: red; padding-left: 5px;}
	.dynamic_form_container .form_header{ font-size: 150%; font-weight: bold; }
	.dynamic_form_container .form_subheader{ margin: 0px; padding: 0px; color: #999; font-size: 90%;}
	.dynamic_form_container .form_field, .dynamic_form_container .form_header_container{padding: 15px; background-color: #FAFAFA}
	.dynamic_form_container .label{font-weight: bold; font-size: 110%; line-height: 1em; color: #333;}
	.dynamic_form_container .help_text{color: #999; font-size: 85%; padding: 5px 0px; line-height: 1em;}
	.dynamic_form_container .user_entry{margin-top: 10px; padding-left: 10px;}
</style>