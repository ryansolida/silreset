<?php
$field_type = $_REQUEST['field_type'];
$unique = rand();
?>
<div id="field_form_<?=$unique?>" class="field_form">	
	<div style="float: left; width: 300px;">
		Label: <input type="text" id="<?=$unique?>_label" class="field_data" size="40">
	</div>
	<div style="float: left; width: 300px;">
		Name: <input type="text" id="<?=$unique?>_name" class="field_data" size="40">
	</div>
	<div style="float: left; width: 500px;">
		Notes: <input type="text" id="<?=$unique?>_notes" class="field_data" size="85">
	</div>
	<div style="clear: both"></div>
	<div class="field_details">
		<?php require('field_type_forms/'.$field_type.'.php'); ?>
	</div>
</div>