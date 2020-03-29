<h2><?=($_REQUEST['module_id'])?'Editing '.$module['display_name']:'New Module'?></h2>

<?php $container_style = "border: 3px solid #CCC; padding: 15px; background-color: #F9F9F9; margin-bottom: 25px"; ?>

<div style="<?=$container_style?>">
	<h3 style='margin-top: 0px'>Module Info</h3>
	<?php $div_style = "float: left; width: 300px;"; ?>
	<div style="<?=$div_style?>">
		Module Name<br />
		<input type="text" id="info_display_name" class='sys_input' value="<?=$module['display_name']?>">
	</div>
	<div style="<?=$div_style?>">
		Admin Action<br />
		<input type="text" id="info_admin_action" class='sys_input' value="<?=$module['admin_action']?>">
	</div>
	<div style="<?=$div_style?>">
		Destination Table<br />
		<input type="text" id="info_form_dest_table" class='sys_input' value="<?=$module['form_dest_table']?>">
	</div>
	<div style="<?=$div_style?>">
		Form Prefix<br />
		<input type="text" id="info_form_prefix" class='sys_input' value="<?=$module['form_prefix']?>">
	</div>
	<div style="clear: both"></div>
</div>


<div style="<?=$container_style?>">
	<h3 style='margin-top: 0px'>Fields</h3>
	<ul id="field_list">
		
	</ul>
	Add New <select id="new_field_type">
		<option value="">Choose One</option>
		<?php
		$field_types = $mod->get_field_types();
		foreach ($field_types as $key=>$value){
			?>
			<option value="<?=$value?>"><?=$key?></option>
			<?php
		}
		?>
	</select>
	<input type="button" id="new_field" value="Go">
</div>


<input type="button" id="save_module" value="Save Changes">

<script language="javascript">
	$().ready(function(){
		$("#new_field").click(function(){
			field_type = $("#new_field_type").val();
			if ( field_type == '' ){
				alert("You must select a field type");
				return false;
			}
			$.ajax({
				url: './?admin_action=<?=$_REQUEST['admin_action']?>&admin_subaction=field_form&field_type='+field_type+'&nt=1',
				success: function(msg){
					$("#field_list").append("<li>"+msg+"</li>");
					$("#new_field_type").val('');
				}
			})
		})
		
		$("#save_module").click(function(){
			var form_arr = {};
			var count = 0;
			$(".field_form").each(function(){
				var this_unique = $(this).attr('id').replace('field_form_','');
				var this_field = {};
				$(this).find(".field_data").each(function(){
					this_id = $(this).attr('id').replace(this_unique+'_','');
					this_field[this_id] = $(this).val();
				})
				form_arr[count] = this_field;
				count += 1;
			})
			alert(JSON.stringify(form_arr));
		})
	})
</script>

<style type="text/css">
	#field_list{margin: 0px; padding: 0px; list-style: none; margin-bottom: 25px;}
	.field_form{padding: 10px 15px; margin: 5px 0px; border: 1px solid #CCC; font-size: 90%; background-color: #FFF; line-height: 3em;}
</style>