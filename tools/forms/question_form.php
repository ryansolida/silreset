	<form id="question_form" action="./?admin_action=<?=$_REQUEST['admin_action']?>&admin_subaction=<?=$_REQUEST['question_id']?'edit_question':'create_question'?>" method="POST">
	<input type="hidden" name="question_form_id" value="<?=$_REQUEST['form_id']?>">
	<input type="hidden" name="question_id" value="<?=$_REQUEST['question_id']?>">
	<input type="hidden" name="nt" value="1">
	<div class="sys_form">
		<div class="sys_form_container" id="form_form">
			<h2><?=$_REQUEST['question_id']?'Edit Question':'Create New Question'?></h2>
			<div class="form_body">
				<strong>Question Label</strong><br />
				<input type="text" class="sys_input required focus" id="question_label" name="question_label" size="50" value="<?=$question['question_label']?>">
				<br /><br />

				<strong>Help Text</strong><br />
				<input type="text" class="sys_input" name="question_help_text" size="75"  value="<?=$question['question_help_text']?>">
				<?php
					//hide the dropdown for question type if we're editing a question
				?>
				<div<?=$question?' style="display: none"':''?>>
					<br /><br />
					<strong>Question Type: </strong> <select name="question_type" id="question_type" class="required">
						<option value="">Choose One</option>
						<?php
						$question_types = array(
							'text'=>'Text',
							'textarea'=>'Paragraph Text',
							'dropdown'=>'Drop Down',
							'section_header'=>'Form Section Header',
							'checkbox'=>'Checkbox'
						);
						foreach ($question_types as $key=>$value){
							?>
							<option value="<?=$key?>"><?=$value?></option>
							<?php
						}
						?>
					</select>
				</div>
				<?php
				//============================================================
				// 	Hide question position if editing
				//============================================================
				if ( !$question ){
				?>
					<br />
					<strong>Place Question </strong> <select name="question_order">
						<option value="1">at the beginning of the form</option>
						<option value="<?=count($questions)?>">at the end of the form</option>
						<?php
						for ($i=0; $i<count($questions); $i++){
							?><option value="<?=$questions[$i]['question_order']+1?>">After "<?=$questions[$i]['question_label']?>"</option><?php
						}
						?>
					</select>
				<?php
				}
				?>
				<div style="margin: 20px 0px; border: 2px solid #BBB; padding: 7px; font-size: 85%; background-color: #FAFAFA; color: #666">
					<input type="hidden" name="question_required" id="required_field" value="<?=$question['question_required']?>">
					<label style="cursor: pointer"><input type="checkbox" id="required_check"> This is a Required Field</label>
				</div>
				
				<div style="margin: 20px 0px; border: 2px solid #BBB; padding: 7px; font-size: 85%; background-color: #FAFAFA; color: #666">
					<input type="hidden" name="question_same_line" id="same_line_field" value="<?=$question['question_same_line']?>">
					<label style="cursor: pointer"><input type="checkbox" id="same_line_check"> Keep this question on the same line as the previous question.</label>
				</div>				
				
				
				<div id="question_form_details" style="display: none"></div>
				</div>
				<div class="form_actions">
					<input type="submit" class="submit_button" value="<?=$_REQUEST['question_id']?'Update Question':'Create Question'?>"> or <a href="javascript:;" onclick="cancel_question()">Cancel</a>
				</div>
			</div>
		</div>
	</form>

<script language="javascript">
	$().ready(function(){
		
		$('#question_form').ajaxForm({
			
			success: function(responseText) { 
				//alert(responseText);
				reset_question_form();
			},
			beforeSubmit: function(){
				var good_to_go = true;
				
				//============================================================
				// 	START WITH REQUIRED FIELDS
				//============================================================
				$("#form_form .required").each(function(){
					if ($(this).val() == '' && good_to_go ){
						if ( $(this).attr('name') == 'dropdown_options_present' ){
							alert("You must enter at least one option");
						}
						else if ( $(this).attr('name') == 'question_type' ){
							alert("You must select a question type");
							$(this).focus();
						}
						else {
							alert("You must fill in all required fields");
							$(this).focus();
						}
						
						good_to_go = false
					}
				})
				
				if ( !good_to_go ){
					return false;
				}
				
			}
			
    	});
    	
    	$("#question_type").change(function(){
    		cur_type = $(this).val();
    		if ( cur_type == 'checkbox' ){
    			$("#question_label").attr('size','100');
    		} 
    		else {
    			$("#question_label").attr('size','50');
    		}
    		
    		$.get('./?nt=1&admin_action=<?=$_REQUEST['admin_action']?>&admin_subaction=get_question_detail_form&question_id=<?=$_REQUEST['question_id']?>&type='+cur_type,function(ret){
    			$("#question_form_details").html(ret).show();
    		})
    	})
    	
    	<?php
    	if ( $question ){
    		?>
    		$("#question_type").val("<?=$question['question_type']?>").change();
    		<?php
    		if ( $question['question_required'] == 1 ){
    			?>
    			$("#required_check").attr('checked',true);
    			<?php
    		}
    		if ( $question['question_same_line'] == 1 ){
    			?>
    			$("#same_line_check").attr('checked',true);
    			<?php
    		}
    	}
    	?>
    	
    	$("#required_check").change(function(){
    		if ( $(this).attr('checked') == true ){
    			$("#required_field").val(1);
    		} else {
    			$("#required_field").val(0);
    		}
    	})
    	
   	   $("#same_line_check").change(function(){
    		if ( $(this).attr('checked') == true ){
    			$("#same_line_field").val(1);
    		} else {
    			$("#same_line_field").val(0);
    		}
    	})
    })
</script>