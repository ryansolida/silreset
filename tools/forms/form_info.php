<form id="form_form" action="./?admin_action=<?=$_REQUEST['admin_action']?>&admin_subaction=<?=$_REQUEST['admin_subaction']?>" method="POST">
	<?php
	if ( $_REQUEST['form_id'] ){
		?>
		<input type="hidden" name="id" value="<?=$_REQUEST['form_id']?>">
		<?php
	}
	?>
	<div class="sys_form">
		<div class="sys_form_container">
			<h2><?=$_REQUEST['form_id']?'Edit Form':'Create New Form'?></h2>
			<div class="form_body">
				<strong>Form Name</strong><br />
				<input type="text" class="sys_input required" name="form_name" size="50" value="<?=htmlentities(stripslashes($form['form_name']))?>">
				
				<?php
				for ($i=1; $i<=5; $i++){
					?>
						<br /><br />
					<strong>Recipient Email Address <?=$i?></strong><br />
					<input type="text" class="sys_input email" name="form_email_recipient_<?=$i?>" size="40" value="<?=$form['form_email_recipient_'.$i]?>">
				
					<?php
				}
				?>
					<br /><br />
				</div>
				<div class="form_actions">
					<input type="submit" class="submit_button" value="<?=$_REQUEST['form_id']?'Update Form':'Create Form'?>"> or <a href="./?admin_action=<?=$_REQUEST['admin_action']?>">Cancel</a>
				</div>
			</div>
		</div>
	</div>
</form>

<script language="javascript">
	$().ready(function(){
		$("#form_form").submit(function(){
			var good_to_go = true;
			
			$("#form_form .required").each(function(){
				if ($(this).val() == '' && good_to_go ){
					alert("You must fill in all required fields");
					$(this).focus();
					good_to_go = false;
				}
			})
			
			var email_present = false;
			$("#form_form .email").each(function(){
				if ( $(this).val() != '' ){
					email_present = true;
				}
			})
			if ( !email_present && good_to_go ){
				alert("You must enter at least one email address");
				good_to_go = false;
				$("#form_form .email:first").focus();
			}
			
			if ( !good_to_go ){
				return false;
			}
		})
	})
</script>