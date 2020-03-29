<strong>When form loaded, set as </strong>
<select name="custom_data_status">
	<option value="unchecked"<?=$question_data['status']=='unchecked'?' SELECTED':''?>>Unchecked</option>
	<option value="checked"<?=$question_data['status']=='checked'?' SELECTED':''?>>Checked</option>
</select>