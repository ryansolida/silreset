<input type="hidden" name="dropdown_options_present" id="options_present" class="required" value="<?=htmlentities($question_data['options'])?>">
<input type="hidden" name="custom_data_options" value="<?=htmlentities($question_data['options'])?>" id="drop_down_options">
<strong>Options: </strong><br />
<div id="options">
</div>
	<div id="no_options">
		Currently No Options
	</div>
<div id="new_option">
	<strong>Create New Option</strong><br />
	<input type="text" id="new_option_input" class='sys_input'> <input type="button" id="create_new_option" value="Add Option">
</div>


<div style="display: none" id="value_template">
	<div class="drop_down_option"><a href='javascript:;' class="delete_option_value">x</a>&nbsp;&nbsp;<span class="option_value">VALUE</span> <span style="padding-left: 50px; color: #BBB; cursor: move; font-size: 80%; line-height: 1.5em; text-decoration: underline" class="reorder">Drag To Reorder</span></div>
</div>


<script language="javascript">
	
	var options = new Array();
	
	$().ready(function(){
		$('#new_option_input').keypress(function(event) {
			if (event.keyCode == '13') {
				add_option();
				return false;
			}
		})
		
		$("#create_new_option").click(function(){
			add_option()
		})
		
		<?php
		if ( $question_data['options'] ){
			foreach (json_decode($question_data['options'],TRUE) as $option ){
				?>
				options.push("<?=addslashes($option)?>");
				$("#options").append($("#value_template").html().replace("VALUE","<?=addslashes($option)?>"));<?php
			}
			?>
			$("#no_options").hide();
			$("#options_present").val('goforit');
			<?php
		}
		?>
		
		update_actions();
		
	});
	
	
	function add_option(){
		if ( $("#new_option_input").val() != '' ){
			options.push($("#new_option_input").val());
		}
		
		$("#new_option_input").val('').focus(); //clear input and focus
		$("#drop_down_options").val(JSON.stringify(options)); //push new list to hidden field
		
		update_options_display();		
	}
	
	function update_options_display(){
		//repopulate link list
		$("#no_options").hide();
		$("#options_present").val('goforit');
		
		$("#options").html('');
		for ( var i in options ){
			$("#options").append($("#value_template").html().replace("VALUE",options[i]));
		}	
		update_actions();	
	}
	
	function update_actions(){
		$(".delete_option_value").unbind('click').click(function(){
			$(this).parents('.drop_down_option').remove();
			
			update_options_from_list();

			$("#drop_down_options").val(JSON.stringify(options));
		})
		
		$("#options").sortable({
			axis: 'y',
			handle: '.reorder',
			stop: function(){
				update_options_from_list();
			}
		});
		
	}
	
	function update_options_from_list(){
		//now go repopulate options from values available
		options = new Array();
		$("#options .drop_down_option").each(function(){
			options.push($(this).find('.option_value').html());
		})
		
		if ( options.length == 0 ){
			$("#no_options").show();
			$("#options_present").val('');
		}
		
		$("#drop_down_options").val(JSON.stringify(options)); //push new list to hidden field
	}
</script>

<style type="text/css">
	#options .drop_down_option, #new_option{margin: 5px; border: 2px solid #EEE; background-color: #FAFAFA; padding: 10px;}
	#no_options{font-weight: bold; color: #666; padding: 15px;}
</style>