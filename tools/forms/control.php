<?php
require_once(THE_GUTS_DIR.'/includes/classes/global/forms.php');
$Forms = new forms;
$subaction = $_REQUEST['admin_subaction'];


if ( $_REQUEST['admin_subaction'] == 'create_form' ){
	if ( $_POST ){
		$form_data = array();
		foreach ($_POST as $key=>$value){
			if ( stripos($key,'form_') !== false ){
				$form_data[$key]=$value;
			}
		}
		$form_id = $Forms->create_form($form_data);
		header("Location: ./?admin_action=".$_REQUEST['admin_action'].'&admin_subaction=view_questions&form_id='.$form_id);
		exit;
	} 
	else{
		require('form_info.php');
	}
}
elseif ( $_REQUEST['admin_subaction'] == 'edit_form' ){
	if ( $_POST ){
		$form_data = array();
		foreach ($_POST as $key=>$value){
			if ( stripos($key,'form_') !== false ){
				$form_data[$key]=$value;
			}
		}
		$Forms->update_form($_REQUEST['id'],$form_data);
		header("Location: ./?admin_action=".$_REQUEST['admin_action']);
		exit;
	} 
	else{
		$form = $Forms->get_form($_REQUEST['form_id']);
		require('form_info.php');
	}
}
elseif ($_REQUEST['admin_subaction'] == 'view_questions' ){
	$form = $Forms->get_form($_REQUEST['form_id']);
	require('form_questions_window.php');
}
elseif ($_REQUEST['admin_subaction'] == 'reorder_questions' ){
	$Forms->reorder_form_questions($_REQUEST['question_order']);
}
elseif ($_REQUEST['admin_subaction'] == 'get_question_list' ){
	$questions = $Forms->get_form_questions($_REQUEST['form_id']);
	require('form_questions_list.php');
}
elseif ( $_REQUEST['admin_subaction'] == 'create_question' ){
	if ( $_POST ){
		$question_data = array();
		foreach ($_POST as $key=>$value){
			if ( stripos($key,'question_') !== false ){
				$question_data[$key]=$value;
			}
		}
		
		$custom_data = array();
		foreach ($_POST as $key=>$value){
			if ( stripos($key,'custom_data_') !== false ){
				$custom_data[str_replace('custom_data_','',$key)]=$value;
			}
		}
		
		$question_data['question_data'] = json_encode($custom_data);
		
		print_r($question_data);
		
		$Forms->create_form_question($question_data);
		//header("Location: ./?admin_action=".$_REQUEST['admin_action']);
		exit;
	}
	else {
		$questions = $Forms->get_form_questions($_REQUEST['form_id']);
		require('question_form.php');
	}
}
elseif ($_REQUEST['admin_subaction'] == 'edit_question' ){
	if ( $_POST ){
		$question_data = array();
		foreach ($_POST as $key=>$value){
			if ( stripos($key,'question_') !== false && $key != 'question_id' ){
				$question_data[$key]=$value;
			}
		}
		
		$custom_data = array();
		foreach ($_POST as $key=>$value){
			if ( stripos($key,'custom_data_') !== false ){
				$custom_data[str_replace('custom_data_','',$key)]=$value;
			}
		}
		
		$question_data['question_data'] = json_encode($custom_data);
		
		print_r($question_data);
		
		$Forms->update_form_question($_REQUEST['question_id'],$question_data);
		//header("Location: ./?admin_action=".$_REQUEST['admin_action']);
		exit;

	}
	else {
		$question = $Forms->get_question($_REQUEST['question_id']);
		require('question_form.php');
	}
}
elseif ( $_REQUEST['admin_subaction'] == 'delete_question' ){
	if ( $_POST ){
		$Forms->delete_form_question($_REQUEST['question_id']);
	}
}
elseif ( $_REQUEST['admin_subaction'] == 'get_question_detail_form' ){
	if ( $_REQUEST['question_id'] ){
		$question = $Forms->get_question($_REQUEST['question_id']);
		$question_data = json_decode($question['question_data'],TRUE);
	}
	require('question_type_forms/'.$_REQUEST['type'].'.php');
}
elseif ( $_REQUEST['admin_subaction'] == 'view_results' ){
	if ($_REQUEST['submission_id'] ){
		$submission = $Forms->get_submission($_REQUEST['submission_id']);
	}
	else{
		$submissions = $Forms->get_submission_count($_REQUEST['form_id']);
	}
	require('results.php');
}
else{
	require('home.php');
}
