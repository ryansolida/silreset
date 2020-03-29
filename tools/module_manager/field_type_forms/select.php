<strong>Drop Down Options</strong>
<div class="drop_down_options_container" id="<?=$unique?>_drop_down_options">
	<div style="float: left; width: 200px">
		Type: 
		<select class="drop_down_type" id="<?=$unique?>_drop_down_type">
			<option value="defined">Defined</option>
			<option value="relationship">From Other Table</option>
		</select>
	</div>
	<div style="float: left;" class="defined_drop_down drop_down_options_container empty">
		<strong>Options</strong><br />
		<div class="drop_down_options"></div>
		<a href="javascript:;" class="add_new_option_link">Add New Option</a>
	</div>
	<div style="float: left; width: 700px; display: none" class="drop_down_options_container relationship_drop_down">
		Relationship
	</div>
	<div style="clear: both"></div>
</div>


<div style="display: none" id="option_field_template">
	<div style="border: 2px solid #CCC; margin: 5px 5px 0px 0px; padding: 10px;">
		Text: <input type="text" size="60"> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		Value: <input type="text" size="30">
	</div>
</div>

<script language="javascript">
	$().ready(function(){
		$("#<?=$unique?>_drop_down_type").change(function(){
			var parent_container = $("#<?=$unique?>_drop_down_options");
			parent_container.find(".drop_down_options_container").hide();
			div_to_show = parent_container.find("."+$(this).val()+"_drop_down");
			div_to_show.show();

			if ( $(this).val() == 'defined' ){
				if ( div_to_show.hasClass('empty') ){
					parent_container.find(".drop_down_options").append($("#option_field_template").html());
					div_to_show.removeClass('empty')
				}
			}
		}).change();
		
		$(".add_new_option_link").click(function(){
			$(this).siblings(".drop_down_options").append($("#option_field_template").html());
		})
	})
</script>