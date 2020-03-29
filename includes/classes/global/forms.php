<?php

class forms
{
	function __construct(){
		$this->db = new db;
	}
	
	//============================================================
	//
	// 	RETRIEVAL
	//
	//============================================================
		function get_forms(){
			$results = $this->db->qquery('SELECT * FROM forms ORDER BY form_name DESC');
			return $results;
		}
		
		function get_form($form_id){
			$results = $this->db->qquery("SELECT * FROM forms WHERE form_id=$form_id");
			return $results[0];
		}
		
		function get_question($question_id){
			$results = $this->db->getwhere('form_questions',array('question_id'=>$question_id));
			return $results[0];
		}
		
		function get_form_questions($form_id){
			$results = $this->db->qquery("SELECT * FROM form_questions WHERE question_form_id = $form_id ORDER BY question_order");
			return $results;
		}
		
		function get_submissions($form_id){
			$results = $this->db->qquery("SELECT * FROM forms_submitted WHERE form_id = $form_id ORDER BY submitted_datetime DESC"); //latest first
			return $results;
		}
		
		function get_submission_count($form_id){
			$results = $this->db->qquery("SELECT id, submitted_datetime FROM forms_submitted WHERE form_id = $form_id ORDER BY submitted_datetime DESC");
			return $results;
		}
		
		function get_submission($submission_id){
			$results = $this->db->getwhere('forms_submitted',array('id'=>$submission_id));
			return $results[0];
		}
	//============================================================
	//
	// 	END RETRIEVAL
	//		--------------------------
	//		START MANAGEMENT
	//
	//============================================================
		function create_form($form_data){
			$this->db->insert('forms',$form_data);
			return $this->db->get_insert_id();
		}
		
		function update_form($form_id,$form_data){
			$this->db->update('forms',$form_data,array('form_id'=>$form_id));
			return true;
		}
		
		function delete_form($form_id){
			$this->db->set_query_str("DELETE FROM forms WHERE form_id=$form_id");
			$this->db->db_query();
			
			//now wipe out questions
			$this->db->set_query_str("DELETE FROM form_questions WHERE form_id=$form_id");
			$this->db->db_query();
		}
		
		function create_form_question($question_data){
			if ( $question_data['question_order'] ){ //won't get passed on edit
				//reorder the questions to make room for our new one
				$order = $question_data['question_order'];
				$questions = $this->get_form_questions($question_data['question_form_id']);
				
				for($i=0; $i<count($questions); $i++){
					if ( $questions[$i]['question_order'] >= $order ){
						$this->update_form_question($questions[$i]['question_id'],array('question_order'=>($questions[$i]['question_order']+1)));
					}
				}
			}
			
			$this->db->insert('form_questions',$question_data);
			
		}
		
		function update_form_question($question_id,$question_data){	
			$this->db->update('form_questions',$question_data,array('question_id'=>$question_id));
		}
		
		function delete_form_question($question_id){
			//we first need to pull some data so we can update the rest of the questions associated with this form
			$questions = $this->db->getwhere('form_questions',array('question_id'=>$question_id));
			$question = $questions[0];
			
			$this->db->set_query_str("DELETE FROM form_questions WHERE question_id = $question_id");
			$this->db->db_query();
			
			//now we need to reorder
			$questions = $this->get_form_questions($question['question_form_id']);
			for ($i=0; $i<count($questions); $i++){
				$cur_count = $i+1;
				$questions[$i]['question_order'] = $cur_count;
				$this->update_form_question($questions[$i]['question_id'],$questions[$i]);
			}
			
			return true;
		}
		
		function reorder_form_questions($question_order){
			$question_order = explode(",",$question_order);
			for ( $i=0; $i<count($question_order); $i++ ){
				$cur_pos = $i+1;
				$this->db->update('form_questions',array('question_order'=>$cur_pos),array('question_id'=>$question_order[$i]));
			}
		}
		
	//============================================================
	// 
	//		END MANAGEMENT
	//
	//============================================================
}


class form_display extends forms{
	
	function __construct($form_id=false,$callback=false){
		$this->db = new db;
		if ( $form_id ){
			$this->display_form($form_id,$callback);
		}
	}
	
	function display_form($form_id,$callback=false){
		$questions = $this->get_form_questions($form_id);
		?>
		<div class="dynamic_form_container_outer">
			<a name="dynamic_form_<?=$form_id?>_top"></a>
			<div class="dynamic_form_container">
				<form id="dynamic_form_<?=$form_id?>_parent" class="dynamic_form" type="POST" action="/utilities/form-processor">
					<input type="hidden" name="dynamic_form_id" value="<?=$form_id?>">
					<div class="dynamic_form_questions">			
						<?php
						if ( count($questions) > 0 ){
							$questions_count = 0;
							foreach ($questions as $question){
								$next_question = $questions[$questions_count+1];
								$prev_question = $questions[$questions_count-1];
								$in_same_line = false;
								
								$function = $question['question_type'].'_display';
								
								//first, escape a new line if we ARE not one
								if ( $prev_question['question_same_line'] && !$question['question_same_line'] ){
									?>
										</tr>
									</table>
									<?php
								}
								
								//if we are a question whose following needs be on the same line
								if ( $next_question['question_same_line'] && !$question['question_same_line'] ){
									?>
									<table border="0" cellspacing="0" cellpadding="0">
										<tr>
									<?php
									$in_same_line = true;
								}
								
								if ($question['question_same_line'] ){
									$in_same_line = true;
								}
								
								if ( $in_same_line ){
									?>
									<td><?php $this->$function($question);?></td>
									<?php	
								}
								else {
									$this->$function($question);
								}								
								$questions_count++;	
							}
						}
						?>
					</div>
					<div class="submit_container">
						<input type="submit" value="Submit Form" class="dynamic_form_submit">
					</div>
				</form>
			</div>
			<div class="response_container" style="display: none"></div>
		</div>
		<?php
		$this->display_form_js($callback);
	}
	
	function display_form_js($callback=false){
		?>
		<script type="text/javascript" src="/js/addons/form/form.js"></script>
		<script language="javascript">
			$().ready(function(){
				$(".dynamic_form").ajaxForm({
					success: function(responseText){
						data = JSON.parse(responseText);
						this_parent = $("#dynamic_form_"+data.form_id+"_parent").parents(".dynamic_form_container_outer");
						$(this_parent).find(".dynamic_form_container").hide();
						
						override_response = $("#form_response_"+data.form_id);
						if ( override_response.length > 0 ){
							response_text = override_response.html();
						}
						else {
							response_text = data.response;
						}
						
						<?php
						if ( $callback ){
							?>
							<?=$callback?>();
							<?php
						}
						?>
						$(this_parent).find(".response_container").html(response_text).show();
					},
					beforeSubmit: function(formData){
						var good_to_go = true;
						var form = $("#dynamic_form_"+formData[0].value+"_parent");
						$(form).find('.error_container').removeClass('error_container');
						$(form.find('.error_message').remove());
						form.find(".required").each(function(){
							if ( $(this).val() == '' && good_to_go ){
								$(this).parents('.form_field').addClass('error_container').find('.label').after('<div class="error_message">This field is required</div>'); //prepend("This field is required").show();
								if ( !$(this).hasClass('hidden_required') ){
									$(this).focus();
								}
								good_to_go = false;
							}
						})
						if ( !good_to_go ){
							$("#extend_submit_button").attr("disabled",false).val('Submit Form');
							return false;
						}
						
						$(form).find(".dynamic_form_submit").attr('disabled',true).val("Submitting...");
					}
				})
			})
		</script>
		<?php
	}
	
	//============================================================
	// 	FIELD TYPES
	//============================================================
	function section_header_display($question){
		?>
		<div class="form_header_container">
			<div class="form_header"><?=stripslashes($question['question_label'])?></div>
			<div class="form_subheader"><?=stripslashes($question['question_help_text'])?></div>
		</div>
		<?php
	}
	
	function textarea_display($question){
		$question_data = json_decode($question['question_data'],TRUE);
		$this->show_head($question);
		?>
		<textarea name="dynamic_form_<?=$question['question_id']?>" cols=<?=$question_data['cols']!=''?$question_data['cols']:'50'?> rows=<?=$question_data['rows']!=''?$question_data['rows']:'5'?> class="dyn_form_textarea<?=$question['question_required'] == 1?' required':''?>"></textarea>
		<?php
		$this->show_foot($question);
	}
	
	function text_display($question){
		$question_data = json_decode($question['question_data'],TRUE);
		$this->show_head($question);
		?>
		<input name="dynamic_form_<?=$question['question_id']?>" type="text" size="<?=$question_data['size'] != ''?$question_data['size']:'50'?>" class="dyn_form_text<?=$question['question_required'] == 1?' required':''?>">
		<?php
		$this->show_foot($question);
	}
	
	function dropdown_display($question){
		$this->show_head($question);
		$question_data = json_decode($question['question_data'],TRUE);
		?>
		<select name="dynamic_form_<?=$question['question_id']?>" class="<?=$question['question_required'] == 1?' required':''?>">
			<option value="">Choose One</option>
		<?php
		foreach (json_decode($question_data['options'],TRUE) as $option){
			?>
			<option value="<?=$option?>"><?=$option?></option>
			<?php
		}
		?>
		</select>
		<?php
		$this->show_foot();
	}
	
	function checkbox_display($question){
		$question_data = json_decode($question['question_data'],TRUE);
		?>
		<div class="form_field">
			<div class="error" style="display: none"></div>
			<input type="hidden" id="dynamic_form_<?=$question['question_id']?>" name="dynamic_form_<?=$question['question_id']?>" class="hidden_required<?=$question['question_required'] == 1?' required':''?>" value="<?=$question_data['status']=='checked'?1:''?>">
			<label>
				<table border="0" cellspacing="0" cellpadding="10">
					<td valign="top"><input type="checkbox" id="dynamic_form_<?=$question['question_id']?>_check"<?=$question_data['status']=='checked'?' CHECKED':''?>></td>
					<td valign="top"><?=$question['question_label']?><?=$question['question_required']==1?'<span class="required_star">*</span>':''?></td>
				</table>
			</label>
			<div style="clear: both"></div>
		</div>
		<script language="javascript">
			$().ready(function(){
				$("#dynamic_form_<?=$question['question_id']?>_check").change(function(){
					if ( $(this).attr('checked') ){
						$("#dynamic_form_<?=$question['question_id']?>").val(1);
					}
					else {
						$("#dynamic_form_<?=$question['question_id']?>").val('');
					}
				})
			})
		</script>
		<?php
	}
	
	//============================================================
	// 	STANDARD PIECES
	//============================================================

	function show_head($question){
		
		?>
		<div class="form_field">
			<div class="label"><?=stripslashes($question['question_label'])?><?=$question['question_required']==1?'<span class="required_star">*</span>':''?></div>
			<?php
			if ( $question['question_help_text'] != '' ){
				?>
				<div class="help_text"><?=stripslashes($question['question_help_text'])?></div>
				<?php
			}
			?>
			<div class="user_entry">
			<?php
	}
	
	function show_foot(){
		?>
			</div>
		</div>
		<?php
	}
	
}


class form_processor extends forms{
	function __construct(){
		$this->db = new db;
	}
	
	function process_form(){
		//gather our fields to process
		$to_process = array();
		foreach ($_REQUEST as $key=>$value){
			if ( stripos($key,'dynamic_form') === 0 ){ //dynamic_form at first position of field name
				if ( $key == 'dynamic_form_id' ){
					$form_id = $value;
				}
				else {
					$to_process[str_replace('dynamic_form_','',$key)] = $value;
				}
			}
			elseif ( $key == 'transaction_id' ) {//for payment forms
				$to_process['transaction_id'] = $value;
			}
		}
		
		$answers = array();
		$email_body = '';
		//loop through the fields and gather the question
		foreach ($to_process as $key=>$value){
			if ( $key == 'transaction_id' ){ //if we're looking at transaction_id
				//add to answer array
				$answers[] = array(
					'question_id'=>'transaction_id',
					'question'=>'Transaction ID',
					'answer'=>$value
				);
			}
			else {
				$question = $this->get_question($key);
				
				//deal with checkboxes
				if ($question['question_type'] == 'checkbox' ){
					if ( $value != '' ){
						$value = 'Checked';
					}
					else {
						$value = "Not Checked";
					}
				}
				
				//add to answer array
				$answers[] = array(
					'question_id'=>$key,
					'question'=>stripslashes($question['question_label']),
					'answer'=>$value
				);
			}
			
			//add to email body
			$email_body .= "<br><strong>".stripslashes($question['question_label']).": </strong>".$value;
		}
		
		//insert answers into DB
		$this->db->insert('forms_submitted',array(
			'form_id'=>$form_id,
			'answer_data'=>json_encode($answers),
			'submitted_datetime'=>date("Y-m-d H:i:s")
		));
		
		//email recipients
		$form = $this->get_form($form_id);
		$recipients = array();
		for ($i=0; $i<6; $i++){
			if ( $form['form_email_recipient_'.$i] != ''){
				$recipients[] = $form['form_email_recipient_'.$i];
			}
		}
		
		if ( count($recipients) > 0 ){
			$email = new email;
			foreach( $recipients as $recipient ){
				$email_array = array(
					'to_address'=>$recipient,
					'from_address'=>"DO_NOT_REPLY@".SITE_URL,
					'from_name'=>SITE_FULL_TITLE." Form Processor",
					'subject'=>"Form Submission: ".stripslashes($form['form_name']),
					'html_body'=>$email_body, //html code
					'text_body'=>strip_tags(str_replace('<br>','\r\n',$email_body))
				);
				$email->email_to_queue($email_array);
			}
		}

		return array('form_id'=>$form_id,'response'=>"Your form has been submitted");
	}
	
}

?>