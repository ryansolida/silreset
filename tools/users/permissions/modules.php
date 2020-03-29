<h1>Module Privileges for <?=$display_name?></h1>

<?php
if  ($_REQUEST['user_id'] != '' ){
	$type = 'user_id';
	$$type = $_REQUEST['user_id'];
}

if ( $_REQUEST['group_id'] != '' ){
	$type = 'group_id';
	$$type = $_REQUEST['group_id'];
}

?>
<a href="./?admin_action=<?=str_replace('_id','',$type).'s'?>" class="list_button">&laquo; Back To <?=ucfirst(str_replace('_id','',$type))?>s</a>
&nbsp;&nbsp;&nbsp;&nbsp;
<a href="./?admin_action=<?=$_REQUEST['admin_action']?>&admin_subaction=pages&<?=$type?>=<?=$$type?>" class="list_button">View Page Permissions</a>
<br /><br />
<?php
//============================================================
// 	GET moduleS LIST
//============================================================
?>
<table border="0" cellspacing="0" cellpadding="0" width="100%">
	<?php show_modules_recursive() ?>
</table>
<?php

function show_modules_recursive($children_of = false){
	global $modules;
	global $users_class;
	global $type;
	global $$type;
	
	if ( $children_of ){
		$these_modules = $modules[$children_of]['children'];
	}
	else{
		$these_modules = $modules;
	}
	
	foreach ($these_modules as $module){
		$class="odd";
		$level = 1;
		if ( $children_of ){
			$class="even";
			$level = 2;
		}
		?>
		<tr>
			<td style="padding-left: <?=($level/2) * 50?>px;" class="module <?=$class?>" width="40%">
					<?php if ( $module['children'] ){
						?>
						<a href="javascript:;" class="expand" id="expand_<?=$module['id']?>">-</a>
						<?php
					}?>
					<?=$module['title']?>
			</td>
				<td width="60%" class="module <?=$class?> perms" id="perms_<?=$module['id']?>">
					<?php
						$module_perms = $users_class->get_permissions(array($type=>$$type, 'module_id'=>$module['id']));
					?>
						<label><input type="radio" name="module_<?=$module['id']?>" class="perm_radio off" value=""<?=!$module_perms['see']?' CHECKED':''?> />Off</label>
						&nbsp;&nbsp;&nbsp;&nbsp;
						<label><input type="radio" id="<?=$module['id']?>_on" name="module_<?=$module['id']?>" class="perm_radio on" value="see"<?=$module_perms['see']?' CHECKED':''?> />On</label>
					<?php
					?>
				</td>
		</tr>
		<?php
		if ( $module['children'] ){
			?>
			<tr>
				<td colspan="10">
					<div id="children_of_<?=$module['id']?>">
					<table border="0" cellspacing="0" cellpadding="0" width="100%">
						<?php show_modules_recursive($module['id'])?>
					</table>
					</div>
				</td>
			</tr>
			<?php			
		}
	}
}
?>

<style type="text/css">
	.module{padding: 5px; border-bottom: 1px dashed #CCC;}
	.even{background-color: #EEE;}
	.odd{background-color: #F9F9F9;}
	.perms label{font-size: 12px; cursor: pointer}
	.perms{color: #333}
	.perms .active{color: #333}
</style>

<script language="javascript">
	$().ready(function(){
		$(".expand").click(function(){
			this_id = $(this).attr('id').replace("expand_","");
			if ( $(this).html() == '+' ){
				$(this).html('-');
				$("#children_of_"+this_id).slideDown(150);
			} else {
				$(this).html('+');
				$("#children_of_"+this_id).slideUp(150);
			}
		})
		
		//============================================================
		// 	CHECK ONE
		//============================================================
		$(".perm_radio").change(function(){
			
			var module_id = $(this).attr('name').replace('module_','');
			var value = $(this).val();		
			$.ajax({
				url: './?nt=1&admin_action=permissions&admin_subaction=assign_perms_data',
				type: 'POST',
				data: '<?=$type?>=<?=$$type?>&module_id='+module_id+'&perms='+value,
				success: function(msg){
					
					//if we've checked see and it's a child, we need to make sure the parent is enabled
					if ( value == 'see' && module_id.indexOf(":") != -1 ){ 
						var module_split = module_id.split(":");
						parent = module_split[0];
						parent_radio = $("input[name=module_"+parent+"]:not(:checked)"); //get all the radios that are NOT checked
						if ( parent_radio.val() == 'see' ){ //if the SEE radio isn't checked, check it!
							parent_radio.attr('checked','true').change();
						}
					}
					
					//if we're killing a parent module, we need to take out the children too
					if ( value == '' && module_id.indexOf(":") == -1 ){ 
						//alert('round2');
						//$("#children_of_"+module_id+" .on").attr('checked','');
						$("#children_of_"+module_id+" .off").attr('checked','true').change();
					}
					
					//if a child is checked 'off', let's see if any of its siblings are enable.  If not, let's shut the parent off
					if ( value == '' && module_id.indexOf(":") != -1 ){
						var module_split = module_id.split(":");
						parent_name = module_split[0];
						container = $("#children_of_"+parent_name);
						if( container.find(".on:checked").length == 0 ){
							$("#perms_"+parent_name).find(".off").attr('checked','true').change();
						}
						/*
						parent_radio = parent.find("input:not(:checked)"); //get all the radios that are NOT checked
						if ( parent_radio.val() == 'see' ){ //if the SEE radio isn't checked, check it!
							parent_radio.attr('checked','true').change();
						}
						*/
					}
					
				}
			})
			
		})
	})
</script>