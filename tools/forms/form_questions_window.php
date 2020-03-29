<h1>Questions for "<?=stripslashes($form['form_name'])?>"</h1>
<div id="form_question_form" style="display: none"></div>
<div id="form_questions_container">
	<a href="./?admin_action=<?=$_REQUEST['admin_action']?>" class="list_button">&laquo; Back To Forms</a>&nbsp;&nbsp;&nbsp;&nbsp;
	<a href="javascript:;" id="create_question" class="list_button">Create New Question</a>
	<br /><br />
	<div id="form_questions">
		Loading...	
	</div>
</div>

<script language="javascript">
	$().ready(function(){
		reload_questions_list();
		
		$("#create_question").click(function(){
			$.get("./?nt=1&admin_action=<?=$_REQUEST['admin_action']?>&admin_subaction=create_question&form_id=<?=$_REQUEST['form_id']?>",function(data){
				$("#form_questions_container").hide();
				$("#form_question_form").html(data).show();
			});
		})
	})
	
	function reload_questions_list(){
		$.get("./?nt=1&admin_action=<?=$_REQUEST['admin_action']?>&admin_subaction=get_question_list&form_id=<?=$_REQUEST['form_id']?>",function(data){
			$("#form_questions").html(data);
		});
	}
	
	function cancel_question(){
		$("#form_questions_container").show();
		$("#form_question_form").hide();
	}
	
	function reset_question_form(){
		$("#form_questions_container").show();
		$("#form_question_form").hide();		
		reload_questions_list();
	}
</script>

