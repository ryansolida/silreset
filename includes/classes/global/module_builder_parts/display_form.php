<?php


//============================================================
//
//		function display_form() 
//		{
//
//============================================================

global $module_actions;

//============================================================
// 	RUN before_form() method
//============================================================
if ( method_exists($module_actions,'before_form') )
{
	$module_actions->before_form();
}

//set the ID correctly
if ( $this->module_arr['single_entry'] ==true && $this->get_entries_count() > 0 ){
	$record_id = 1;
} else {
	if ( $_REQUEST['category_id'] != '' )
	{
		$record_id = $_REQUEST['category_id'];
	}
	else
	{
		$record_id = $_REQUEST['id'];
	}
}

//account for tools with publishing
if ( $this->module_arr['publish'] )
{
	$this->module_arr['form_dest_table'] = $this->module_arr['form_dest_table']."_edited";
}

/*GATHER INFO =================================*/
if ( str_replace('new','',$record_id) != '' ) // "new" is sent by the categories list
{
	if ( $this->viewing_categories() )
	{
		$id_field = $this->module_arr['form_prefix'].'_id';
	}
	elseif ( $this->module_arr['publish'] )
	{
		$id_field = 'record_id';
	}
	else
	{
		$id_field = 'id';
	}
	
	$query_str = "SELECT * FROM " . $this->module_arr['form_dest_table'] . " WHERE $id_field = " . $record_id;
	$db = new db($this->module_arr['db']);
	$results = $db->qquery($query_str);
	$data = $results[0];
	
	if ($this->module_arr['publish'] )
	{
		$record_id = $data['record_id'];
	}
	
}


/*START FORM HEAD =================================*/
?>
<script language="javascript" src="/cms/js/addons/modals/jqmodal.js"></script>
<link rel="stylesheet" href="/cms/js/addons/thickbox/thickbox.css" type="text/css" media="screen" />
<script type="text/javascript" src="/cms/js/addons/form/form.js"></script>
<link rel="stylesheet" media="screen" type="text/css" href="/cms/js/addons/colorpicker/colorpicker.css" />
<script type="text/javascript" src="/cms/js/addons/colorpicker/colorpicker.js"></script>
<link rel="stylesheet" href="/cms/js/addons/ui/ui.all.css" type="text/css">
<script src="/cms/js/addons/ui/jquery.ui.all.js"></script>
<script type="text/javascript" src="/cms/js/ckeditor/ckeditor.js"></script>
<script language="javascript">
	//set up editors array
	active_editors = new Array();
	
	$(document).ready(function(){
	
		/*
		function updateEditors(){
					setTimeout(function(){WPro.updateAllValue();WPro.updateAllHTML(); updateEditors},500);
		}
		
		updateEditors;
		*/
    	//update all editors
    	/*
    	for ( var i=0; i<active_editors.length; i++ )
    	{
    		WPro.editors[active_editors[i]].addEditorEvent('submit', function(editor){
//    			Wpro.editors[active_editors[i]].updateValue();
    			alert(editor.getValue());
    			editor.updateValue();
    		});
    	}
    	*/
		
		
		$("#module_submit").click(function(){
			if ( active_editors.length > 0 )
			{
				WPro.updateAllValue();
				WPro.updateAllHTML();
			}
			$("#module_form").submit();
		});
		
		$('body').append($('.jqmWindow'))
		$('#status_dialog').jqm({
			modal: true,
			overlay: 25,
			onShow: window_fade_in,
			onHide: window_fade_out
		}); 
	
		$('#module_form').ajaxForm({
	        beforeSubmit: function(formData,f,o) {
	        	
        		for ( var i=0; i<html_editors.length; i++ )
				{
					eval('editor = CKEDITOR.instances.'+html_editors[i]);
					for(var j=0; j<formData.length; j++ ){
						if ( formData[j].name == html_editors[i] ){
							formData[j].value = editor.getData();
						}
					}
				}
	        	
				$("#status_dialog").html('<strong>Submitting... </strong><img src="/cms/images/loader.gif">');
				<?php
				if ( $this->form_has_upload() )
				{
				?>
					$("#status_dialog").append('<div id="status_script" style="font-size: 85%; line-height: 1.2em">Depending on the size of your uploads, this may take some time</div>');
				<?php
				}
				?>
				$("#status_dialog").jqmShow();
				
				setTimeout(function(){
					$("#status_script").html("Yeah, you may want to go get some coffee.... Maybe go catch a quick movie.");
				},60000);
	        },
	        success: function(data) {
	        	<?php
	        	if ( $_REQUEST['debug'] != '' )
	        	{
	        		?>alert(data);<?php
	        	}
	        	?>
	        	$("#status_dialog").html('<strong>Submitted!</strong><div>Please Wait...</div>');
				setTimeout(function(){
					<?php
					if ( $_REQUEST['from_category'] != '' )
					{
						$from_text = "&category_id=".$_REQUEST['from_category'];
					}
					?>
					window.location = './?admin_action=<?=$this->module_arr['admin_action']?><?=$from_text?>';
				},500);
	        }
	    });
	});
	
	
	
	function window_fade_out(hash){
		hash.w.fadeOut('1000',function(){hash.o.remove();}); 
	}	
	
	function window_fade_in(hash){
		hash.w.fadeIn('1000');
	}
</script>
<form id="module_form" action="./" method="post" enctype="multipart/form-data">
<input type="hidden" name="admin_action" value="<?=$this->module_arr['admin_action']?>">
<input type="hidden" name="admin_subaction" value="submit">
<input type="hidden" name="t" value="<?=$this->module_arr['form_dest_table']?>">
<input type="hidden" name="prefix" value="<?=$this->module_arr['form_prefix']?>">
<input type="hidden" name="nt" value="1">
<?php
if ( $this->viewing_categories() )
{
	?>
	<input type="hidden" name="category_id" value="<?=$record_id?>">
	<?php
}
else
{
	?>
	<input type="hidden" name="id" value="<?=$record_id?>">
	<?php
}

/*START ANY GLOBAL JAVASCRIPT STUFF========================*/
?>
<script language="javascript">
	//Image selection hide and show 
	function show_img_change(elem)
	{
		$("#"+elem+"_placeholder").val('');
		document.getElementById(elem+'_img_holder').style.display = 'none';
		document.getElementById(elem+'_img_selector').style.display = '';
		cur_name = document.getElementById(elem+'_flag').name;
		cur_name = cur_name.replace("_0","_1");
		document.getElementById(elem+'_flag').name = cur_name;
	}
	
	//file show and hide
	function show_file_change(elem)
	{
		$("#"+elem+"_placeholder").val('');
		document.getElementById(elem+'_file_holder').style.display = 'none';
		document.getElementById(elem+'_file_selector').style.display = '';
		cur_name = document.getElementById(elem+'_flag').name;
		cur_name = cur_name.replace("_0","_1");
		document.getElementById(elem+'_flag').name = cur_name;
	}
</script>

<style type="text/css">
	.module_field_break{
		padding: 15px 10px;
		border-bottom: 1px solid #CCC;
	}
	
	.odd{
		background-color: #FCFCFC;
	}
	
	.hidden{
		padding: 0px;
		border: 0px;
	}
	
	.module_field_label{
		display: block;
	}
</style>
<script language="javascript">
	function reset_evenodd(){
		var break_count = 0;
		$(".module_field_break").each(function(){	
			if ( break_count%2== 0 )
			{
				$(this).addClass("even").removeClass("odd").removeClass('hidden');
			}
			else
			{
				$(this).addClass("odd").removeClass("even").removeClass('hidden');
			}
			break_count++;
		})
	}
	
	$().ready(function(){
		reset_evenodd();
	})
</script>
<?php


/*RUN THROUGH FIELDS  =================================*/

for ( $i=0; $i<count($this->module_arr['form_arr']); $i++ )
{
	$cur_elem = $this->module_arr['form_arr'][$i];
	
	$cur_elem['full_name'] = $this->module_arr['form_prefix'].'_'.$cur_elem['name'];
	
	if ( $i%2 == 0 ){$evenodd = "even";}else{$evenodd = "odd";} 
	
	if ( $cur_elem['input_type'] != 'insert_html' && $cur_elem['input_type'] != 'require_file' && $cur_elem['input_type'] != 'header' )
	{
	?>
	<div class='module_field_break'>
	<?php
	}
	
	switch ( $cur_elem['input_type'] )
	{
		/*CHECKBOX =================================*/
		case 'checkbox':
			if ( $data[$cur_elem['full_name']] != ''){$checked = " CHECKED";}else{$checked = '';}
			?>
			<label><input type="checkbox" name="<?=$cur_elem['full_name']?>" value="ON"<?=$checked?>>&nbsp;&nbsp;<?=$cur_elem['label']?></label>
			<?php
			break;
			
		/*TEXT =================================*/
		case 'text':
			//set up size
			if ($cur_elem['size'] != '' ){
				$size = "size='".$cur_elem['size']."'";
			}else{
				$size = '';
			}

			?>
			<span class='module_field_label'><?=$cur_elem['label']?>: </span>
			<?php
			if ( $cur_elem['notes'] != '' )
			{
				?><div class="module_field_notes"><?=stripslashes($cur_elem['notes'])?></div>	<?php
			}
			?>
			<input type="text" name="<?=$cur_elem['full_name']?>" id="<?=$cur_elem['full_name']?>" value="<?=htmlspecialchars(stripslashes($data[$cur_elem['full_name']]))?>" <?=$size?>>
			<?php
			break;
			
		/*TEXTAREA =================================*/
		case 'textarea':
			//set up cols and rows
			if ($cur_elem['cols'] != '' ){
				$cols = "cols='".$cur_elem['cols']."'";
			}else{
				$cols = '';
			}
			if ($cur_elem['rows'] != '' ){
				$rows = "rows='".$cur_elem['rows']."'";
			}else{
				$rows = '';
			}

			?>
			<span class='module_field_label'><?=$cur_elem['label']?>: </span>
			
			<?php
			if ( $cur_elem['notes'] != '' )
			{
				?><div class="module_field_notes"><?=stripslashes($cur_elem['notes'])?></div>	<?php
			}
			?>
			<textarea name="<?=$cur_elem['full_name']?>" id="<?=$cur_elem['full_name']?>" <?=$cols?> <?=$rows?>><?=stripslashes($data[$cur_elem['full_name']])?></textarea>
			<?php
			break;
			
		/*SELECT =================================*/
		case 'select':

			$cur_select = $data[$cur_elem['full_name']];
			?>
			<span class='module_field_label'><?=$cur_elem['label']?>:</span> 
			
			<?php
			if ( $cur_elem['notes'] != '' )
			{
				?><div class="module_field_notes"><?=stripslashes($cur_elem['notes'])?></div>	<?php
			}
			?>
			<select name='<?=$cur_elem['full_name']?>' id="<?=$cur_elem['full_name']?>">
				<option value="">Choose One</option>
				<?php
				foreach ($cur_elem['options'] as $key=>$value )
				{
					?><option value="<?=$value?>"<?=($value==$cur_select?' SELECTED':'')?>><?=$key?></option><?php
				}
				?>
			</select>
			<?php
			break;
			
		/*DATE/TIME TEXT BOX =================================*/
		case 'datetext':
			if ( substr($data[$cur_elem['full_name']],-8) != '00:00:00' )
			{
				$date_str = "n/j/Y g:ia";
			}
			else
			{
				$date_str = "n/j/Y";
			}
			
			$date_entry = $data[$cur_elem['full_name']];
			if ( $date_entry == '0000-00-00 00:00:00' || $date_entry == '1969-12-31 16:00:00' )
			{
				$date_entry = '';
			}
			else
			{
				$date_entry = date($date_str,strtotime($date_entry));
			}
			
			?>
			<span class='module_field_label'><?=$cur_elem['label']?>: </span>
			
			<?php
			if ( $cur_elem['notes'] != '' )
			{
				?><div class="module_field_notes"><?=stripslashes($cur_elem['notes'])?></div>	<?php
			}
			?>
			<input type="text" name="<?=$cur_elem['full_name']?>" id="<?=$cur_elem['full_name']?>" value="<?php if($data[$cur_elem['full_name']]!=''){echo $date_entry;}else{echo '';}?>" size="20"> <span class="field_comments">ex. 10/14/2008 3:00pm, Oct 14, 2008 3:00pm, Today 3:00pm, Next Wednesday 3:00pm</span>  
			<?php
			break;
			
		/*DATE / TIME SELECT =================================*/
		case 'date':
			// set up parts for editing existing entry
			if ( $_REQUEST['id'] != '' )
			{
				$day = date("j",strtotime($data[$cur_elem['full_name']]));
				$month = date("n",strtotime($data[$cur_elem['full_name']]));
				$year = date("Y",strtotime($data[$cur_elem['full_name']]));
				$hour = date("g",strtotime($data[$cur_elem['full_name']])); 
				$minute = date("i",strtotime($data[$cur_elem['full_name']]));
				$ampm = date("a",strtotime($data[$cur_elem['full_name']]));
			}
			?>
			<span class='module_field_label'><?=$cur_elem['label']?>:</span>
			<?php
			if ( $cur_elem['notes'] != '' )
			{
				?><div class="module_field_notes"><?=stripslashes($cur_elem['notes'])?></div>	<?php
			}
			?>
			<select name="<?=$cur_elem['full_name']?>_month">
				<?php for($j=1; $j<13; $j++ ){?>
					<option value="<?=$j?>"<?=($j==$month?' SELECTED':'')?>><?=$j?></option>
				<?php	}?>
			</select>
			/
			<select name="<?=$cur_elem['full_name']?>_day">
				<?php for($j=1; $j<=31; $j++ ){?>
					<option value="<?=$j?>"<?=($j==$day?' SELECTED':'')?>><?=$j?></option>
				<?php	}?>
			</select>
			/
			<select name="<?=$cur_elem['full_name']?>_year">
				<?php for($j=$cur_elem['year_start']; $j<=$cur_elem['year_end']; $j++ ){?>
					<option value="<?=$j?>"<?=($j==$year?' SELECTED':'')?>><?=$j?></option>
				<?php	}?>
			</select>
			<?php
			//put together time stuff
			if ( $cur_elem['time'] == 'true' )
			{
				?>
				&nbsp;&nbsp;&nbsp;&nbsp;@&nbsp;&nbsp;&nbsp;&nbsp;
				<select name="<?=$cur_elem['full_name']?>_hour">
					<?php for($j=1; $j<=12; $j++ ){?>
						<option value="<?=$j?>"<?=($j==$hour?' SELECTED':'')?>><?=$j?></option>
					<?php	}?>
				</select>
				:
				<select name="<?=$cur_elem['full_name']?>_minute">
					<?php for($j=0; $j<=60; $j++ ){?>
						<option value="<?=str_pad($j,2,'0',STR_PAD_LEFT)?>"<?=($j==$minute?' SELECTED':'')?>><?=str_pad($j,2,'0',STR_PAD_LEFT)?></option>
					<?php	}?>
				</select>
				<select name="<?=$cur_elem['full_name']?>_ampm">
					<option value="am"<?=($ampm=='am'?' SELECTED':'')?>>am</option>
					<option value="pm"<?=($ampm=='pm'?' SELECTED':'')?>>pm</option>
				</select>
				<?php
			}
			?>
			
			
			<?php
			break;		

		//============================================================
		// 	DATEPICKER
		//============================================================				
		case 'datepicker':
			if ( strpos($data[$cur_elem['full_name']],'0000-00-00') === false && $data[$cur_elem['full_name']] != '' )
			{
				$date = date('Y-m-d',strtotime($data[$cur_elem['full_name']]));
			}
			else
			{
				$date = '';
			}
			
			?>
			
			<span class='module_field_label'><?=$cur_elem['label']?></span>
			
			<?php
			if ( $date != '' )
			{
				?><strong>Current:</strong> <?=date("M j, Y",strtotime($date))?><?php
			}
			else
			{
				?><strong>None Set</strong><?php
			}
			?>
			<div id="<?=$cur_elem['full_name']?>_date_container"></div>
			<input type="hidden" id="<?=$cur_elem['full_name']?>" name="<?=$cur_elem['full_name']?>" value="<?=$date?>" style="">
						
			<script language="javascript">
				$().ready(function(){
					<?=$cur_elem['full_name']?>_datepicker = $("#<?=$cur_elem['full_name']?>_date_container").datepicker({
						altFormat: 'yy-mm-dd',
						altField: '#<?=$cur_elem['full_name']?>'
					});
					$("#<?=$cur_elem['full_name']?>").val('');
					<?php
					if ( $date != '' )
					{
						$year = date("Y",strtotime($date));
						$month = date("n",strtotime($date)) - 1;
						$day = date('j',strtotime($date));
						?>
						cur_<?=$cur_elem['full_name']?>_date = new Date(<?=$year?>,<?=$month?>,<?=$day?>)
						<?=$cur_elem['full_name']?>_datepicker.datepicker('setDate',cur_<?=$cur_elem['full_name']?>_date);
						<?php
					}
					?>			
				})
			</script>
			
			<?php
			break;
		
		/*IMAGE =================================*/
		case 'image':
			$full_thumb_path = '';
			$img_exists = false;
			
			if ( $data[$cur_elem['full_name']]  != '' )
			{
			 	//show thumb
				$full_path_exp = explode('/',SITE_URL.$data[$cur_elem['full_name']]);
				$last_num = count($full_path_exp) - 1;
				$last_part = $full_path_exp[$last_num];
				$full_path_exp[$last_num] = 'thumbs/'.$last_part;
				$full_thumb_path = 'http://'.implode('/',$full_path_exp);
			}
			?>
			<span class='module_field_label'><?=$cur_elem['label']?>: </span>
			
			<?php
			if ( $cur_elem['notes'] != '' )
			{
				?><div class="module_field_notes"><?=stripslashes($cur_elem['notes'])?></div>	<?php
			}
			?>
			<?php
			if ( $full_thumb_path != '' ){
			?><div id="<?=$cur_elem['full_name']?>_img_holder"><a href="<?=$data[$cur_elem['full_name']]?>" target="_blank"><img src="<?=$full_thumb_path?>" border="0"></a><br /><a href="javascript:;" onclick="show_img_change('<?=$cur_elem['full_name']?>')">Change or Remove Image</a></div><?php
				$img_exists = true;
			}
			?>
			<div id="<?=$cur_elem['full_name']?>_img_selector" <?=($img_exists?'style="display: none"':'')?>>
				<input type="file" name="<?=$cur_elem['name']?>" id="image_value_<?=$cur_elem['name']?>">
				<input type="hidden" name="<?=$cur_elem['full_name']?>_placeholder" id="<?=$cur_elem['full_name']?>_placeholder" value="<?=$data[$cur_elem['full_name']]?>">
			</div>
			
			<?php
			break;
			
		//FILE====================================
		case 'file':
			if ( $data[$cur_elem['full_name']]  != '' )
			{
			 	//show thumb
				$full_file_path = SITE_URL.$data[$cur_elem['full_name']];
			}
			else
			{
				$full_file_path = '';
			}
			?>
			<span class='module_field_label'><?=$cur_elem['label']?>: </span>
			
			<?php
			if ( $cur_elem['notes'] != '' )
			{
				?><div class="module_field_notes"><?=stripslashes($cur_elem['notes'])?></div>	<?php
			}
			?>
			<?php
			if ( $full_file_path != '' ){
			?><div id="<?=$cur_elem['full_name']?>_file_holder"><strong>Current File: </strong>	http://<?=$full_file_path?><br /><a href="javascript:;" onclick="show_file_change('<?=$cur_elem['full_name']?>')">Change or Remove File</a></div><?php
				$file_exists = true;
			}
			else
			{
				$file_exists = false;
			}
			?>
			<div id="<?=$cur_elem['full_name']?>_file_selector" <?=($file_exists?'style="display: none"':'')?>>
				<input type="file" name="<?=$cur_elem['name']?>" id="file_value_<?=$cur_elem['name']?>">
				<input id="<?=$cur_elem['full_name']?>_flag" type="hidden" name="file_upload_<?=$cur_elem['name']?><?=($file_exists?'_0':'_1')?>" value="<?=$cur_elem['dest_dir']?>">
				<input type="hidden" name="<?=$cur_elem['full_name']?>_placeholder" id="<?=$cur_elem['full_name']?>_placeholder" value="<?=$data[$cur_elem['full_name']]?>">
			</div>
			
			<?php
		break;
		
		/*HEADER =================================*/
		case 'header':
			?>
			<h3 style="border-bottom: 1px solid #666"><?=$cur_elem['label']?></h3>
			<?php
		break;
		
		/*INSERT HTML =================================*/
		case 'insert_html':
			?>
			<?=$cur_elem['html']?>
			<?php
		break;
		
		/*REQUIRE FILE =================================*/
		case 'require_file':
			require($cur_elem['file_name']);
		break;
			
		/*HTML===================================*/
		case 'html':
		?>
			<span class='module_field_label'><?=$cur_elem['label']?>: </span>
			
				<?php
				if ( $cur_elem['notes'] != '' )
				{
					?><div class="module_field_notes"><?=stripslashes($cur_elem['notes'])?></div>	<?php
				}
				?>
				<textarea id='<?=$field_name?>' class='html_editor' name='<?=$cur_elem['full_name']?>' style='width: 100%;' rel='300px'><?=stripslashes($data[$cur_elem['full_name']])?></textarea>
			<?php
			/*
			$abs_editor_dir = THE_GUTS_DIR."/web_space/includes/wysiwygPro/";
			$rel_editor_dir = THE_GUTS_REL_DIR."/includes/wysiwygPro/";
			
			
			include_once($abs_editor_dir."/wysiwygPro.class.php"); 
			$editor = new wysiwygPro();
			
			require(THE_GUTS_DIR."/admin/html_editor_setup.php");
			
			$editor->editorURL = $rel_editor_dir;
			$editor->theme = 'blue';
			$editor->name = $cur_elem['full_name'];
			$editor->value = stripslashes($data[$cur_elem['full_name']]);
			
			$editor->display(($cur_elem['cols']*12), ($cur_elem['rows']*55));
			?>
			
			<script language="javascript">
				active_editors[active_editors.length] = '<?=$cur_elem['full_name']?>';
			</script>
			*/
		break;
		
		/*CATEGORIES =================================*/
		case 'category':
			if ( $_REQUEST['from_category'] != '' )
			{
				$cur_select = $_REQUEST['from_category'];
			}
			else
			{
				$cur_select = $data[$this->module_arr['form_prefix'].'_'.$cur_elem['name']];
			}
			?>
			<strong><?=$cur_elem['label']?>:</strong> 
			
			<?php
			if ( $cur_elem['notes'] != '' )
			{
				?><div class="module_field_notes"><?=stripslashes($cur_elem['notes'])?></div>	<?php
			}
			?>
			<select name='<?=$cur_elem['full_name']?>' id="<?=$cur_elem['full_name']?>">
				<option value="">Choose One</option>
				<?php
				$query_str = "SELECT * FROM " . $cur_elem['table'];
				$db = new db($this->module_arr['db']);
				$categories = $db->qquery($query_str);
				for ($j=0; $j<count($categories); $j++ )
				{
					?><option value="<?=$categories[$j][$cur_elem['value_field']]?>"<?=($categories[$j][$cur_elem['value_field']]==$cur_select?' SELECTED':'')?>><?=stripslashes($categories[$j][$cur_elem['display_field']])?></option><?php
				}
				?>
			</select>
			
			<?php

		break;
		
		case 'colorpicker':
			
			if ( $data[$cur_elem['full_name']] != '' )
			{
				$value = $data[$cur_elem['full_name']];
			}
			else
			{
				$value = "FFFFFF";
			}
			?>
			<strong><?=$cur_elem['label']?>:</strong> 
			
			<?php
			if ( $cur_elem['notes'] != '' )
			{
				?><div class="module_field_notes"><?=stripslashes($cur_elem['notes'])?></div>	<?php
			}
			?>
			<div style="float: left"><input type="text" maxlength="6" size="6" id="colorpickerField_<?=$cur_elem['full_name']?>" name="<?=$cur_elem['full_name']?>" value="<?=$value?>" /></div>
			<div id="colorpicker_preview_<?=$cur_elem['full_name']?>" style="width: 20px; height: 20px; background-color: #<?=$value?>; margin-left: 15px; margin-top: 3px;  border: 1px solid #666; float: left"></div>
			<div style="clear: both"></div>
			<script language="javascript">
				$().ready(function(){
					$('#colorpickerField_<?=$cur_elem['full_name']?>').ColorPicker({ 
						onChange: function(hsb, hex, rgb) {
							$('#colorpickerField_<?=$cur_elem['full_name']?>').val(hex);
							$("#colorpicker_preview_<?=$cur_elem['full_name']?>").css('backgroundColor','#'+hex);
						},
						onBeforeShow: function () {
							$(this).ColorPickerSetColor(this.value);
						},
					});
				});
			</script>
			<?php
			break;
			
			//============================================================
			// 	TAGS >>> PRETTY COOL STUFF
			//============================================================
			case 'tags':
			?>
			<strong><?=$cur_elem['label']?>:</strong> 
			<div class='tags_left'>
			<?php
			if ( $cur_elem['notes'] != '' )
			{
				?><div class="module_field_notes"><?=stripslashes($cur_elem['notes'])?></div>	<?php
			}
			
			//============================================================
			// 	let's build the tag list
			//============================================================
			$tags = $this->pull_tags_from_text($data[$cur_elem['full_name']]);
			?>
			<input type="text" id="<?=$cur_elem['full_name']?>_tag_input" size="20"> <a href="javascript:;" id="<?=$cur_elem['full_name']?>_tag_add" class="list_button">Add Tag</a>
			</div>
			<div class='tags_right'>
				<strong>Current Tags:</strong>
				<ul id="<?=$cur_elem['full_name']?>_tags_list" class="tags_list">
					<?php
					for ( $z=0; $z<count($tags); $z++ )
					{
						if ( $tags[$z] != '' )
						{
						?>
						<li><?=$tags[$z]?></li>
						<?php
						}
					}
					?>
				</ul>
			</div>
			<div style="clear: both"></div>
			<script language="javascript">
				$().ready(function(){
					$("#<?=$cur_elem['full_name']?>_tag_input").keypress(function(e){
						if ( e.keyCode == 13 )
						{
							submit_tag_<?=$cur_elem['full_name']?>();
							return false;
						}
					});
					
					$("#<?=$cur_elem['full_name']?>_tag_add").click(function(){
						submit_tag_<?=$cur_elem['full_name']?>();
					})
					
					add_delete_to_tags_<?=$cur_elem['full_name']?>();
				});
				
				function submit_tag_<?=$cur_elem['full_name']?>(){
					cur_val = $("#<?=$cur_elem['full_name']?>_tag_input").val();
					
					if ( cur_val != '' )
					{
						$("#<?=$cur_elem['full_name']?>_tags_list").append("<li>"+cur_val+"</li>");
						add_delete_to_tags_<?=$cur_elem['full_name']?>();
					}
					$("#<?=$cur_elem['full_name']?>_tag_input").val('');
				
					rebuild_textarea_<?=$cur_elem['full_name']?>()	
				}
				
				function add_delete_to_tags_<?=$cur_elem['full_name']?>()
				{
					$("#<?=$cur_elem['full_name']?>_tags_list").children("li").children("span").remove();
					$("#<?=$cur_elem['full_name']?>_tags_list").children("li").append("<span class='delete_tag'>x</span>");
					
					$(".delete_tag").click(function(){
						$(this).parent().remove();
						rebuild_textarea_<?=$cur_elem['full_name']?>()
					})
				}
				
				function rebuild_textarea_<?=$cur_elem['full_name']?>()
				{
					//empty textarea
					$("#<?=$cur_elem['full_name']?>_tags_holder").val('');
					
					//now add to textarea hidden
					tags_list = $("#<?=$cur_elem['full_name']?>_tags_list").children("li");
					for ( i=0; i<tags_list.length; i++ )
					{
						cur_val = $("#<?=$cur_elem['full_name']?>_tags_holder").val();
						$(tags_list[i]).children("span").remove();
						cur_html = $(tags_list[i]).html();
						if ( cur_val != '' )
						{
							$("#<?=$cur_elem['full_name']?>_tags_holder").val(cur_val+','+cur_html+',');
						}
						else
						{
							$("#<?=$cur_elem['full_name']?>_tags_holder").val(cur_html+',');
						}
					}
					
					//put the deletes back on
					add_delete_to_tags_<?=$cur_elem['full_name']?>();

				}
				
				
			</script>
			
			<textarea name="<?=$cur_elem['full_name']?>" id="<?=$cur_elem['full_name']?>_tags_holder" style="display: none"><?=$data[$cur_elem['full_name']]?></textarea>
			<?php
			break;
	}
	
	if ( $cur_elem['input_type'] != 'insert_html' && $cur_elem['input_type'] != 'require_file' && $cur_elem['input_type'] != 'header' )
	{
	?>
	</div>
	<?php
	}
}

//============================================================
// 	NOW ADD PUBLISH AND EXPIRATION DATES
//============================================================
if ( $this->module_arr['content_expiration_date'] === true || $this->module_arr['content_publish_date'] === true )
{
	?>
	<br /><br />
	<div id="content_expiration_div" style="margin-top: 20px; clear: both;">
		<h3>Content Publishing and Expiration</h3>
	<?php
	if ( $this->module_arr['content_publish_date'] === true ) //publish
	{
		if ( $data['content_publish_date'] != '0000-00-00 00:00:00' && $data['content_publish_date'] != '' )
		{
			$date = date('Y-m-d',strtotime($data['content_publish_date']));
			$time = date('g:ia',strtotime($data['content_publish_date']));
		}
		else
		{
			$date = '';
			$time = '';
		}
		
		?>
		<table border="0" cellspacing="0" cellpadding="0">
			<tr>
				<td colspan="2">
					<h4 style="margin: 0px; padding: 0px;">Publish Date: </h4>
				</td>
			</tr>
			<tr>
				<td valign="top" width="250">
					<?php
					if ( $date != '' )
					{
						?><strong>Current:</strong> <?=date("M j, Y",strtotime($date))?><?php
					}
					else
					{
						?><strong>None Set</strong><?php
					}
					?>
					<div id="content_publish_date_container"></div>
					<input type="hidden" id="content_publish_date" name="content_publish_date" value="<?=$date?>" style="">
					
				</td>
				<td style='padding-left: 20px' valign="top">
					Time<br />
					<input type="text" name="content_publish_time" value="<?=$time?>" size="8">
					<div class="module_field_notes">(optional) ex. 12:00am, 6:00pm, 3:00pm</div>
				</td>
			</tr>
		</table>
		<br />
		<script language="javascript">
			$().ready(function(){
				submit_datepicker = $("#content_publish_date_container").datepicker({
					altFormat: 'yy-mm-dd',
					altField: '#content_publish_date'
				});
				$("#content_publish_date").val('');

				<?php
				if ( $date != '' )
				{
					$year = date("Y",strtotime($date));
					$month = date("n",strtotime($date)) - 1;
					$day = date('j',strtotime($date));
					?>
					pub_date = new Date(<?=$year?>,<?=$month?>,<?=$day?>)
					submit_datepicker.datepicker('setDate',pub_date);
					<?php
				}
				?>
								
			})
		</script>
		<?php
	}
	
	//============================================================
	// 	EXPIRATION
	//============================================================
	if ( $this->module_arr['content_expiration_date'] === true ) //expiration
	{
		if ( $data['content_expiration_date'] != '0000-00-00 00:00:00' && $data['content_expiration_date'] != '' )
		{
			$date = date('Y-m-d',strtotime($data['content_expiration_date']));
			$time = date('g:ia',strtotime($data['content_expiration_date']));
		}
		else
		{
			$date = '';
			$time = '';
		}
		
		?>
		<table border="0" cellspacing="0" cellpadding="0">
			<tr>
				<td colspan="2">
					<h4 style="margin: 0px; padding: 0px;">Expiration Date: </h4>
				</td>
			</tr>
			<tr>
				<td valign="top" width="250">
					<?php
					if ( $date != '' )
					{
						?><strong>Current:</strong> <?=date("M j, Y",strtotime($date))?><?php
					}
					else
					{
						?><strong>None Set</strong><?php
					}
					
					?>
					<div id="content_expiration_date_container"></div>
					<input type="hidden" id="content_expiration_date" name="content_expiration_date" value="<?=$date?>" style="">
					
				</td>
				<td style='padding-left: 20px' valign="top">
					Time<br />
					<input type="text" name="content_expiration_time" value="<?=$time?>" size="8">
					<div class="module_field_notes">(optional) ex. 12:00am, 6:00pm, 3:00pm</div>
				</td>
			</tr>
		</table>
		<br />
		<script language="javascript">
			$().ready(function(){
				expiration_datepicker = $("#content_expiration_date_container").datepicker({
					altFormat: 'yy-mm-dd',
					altField: '#content_expiration_date'
				});
				$("#content_expiration_date").val('');
				<?php
				if ( $date != '' )
				{
					$year = date("Y",strtotime($date));
					$month = date("n",strtotime($date)) - 1;
					$day = date('j',strtotime($date));
					?>
					exp_date = new Date(<?=$year?>,<?=$month?>,<?=$day?>)
					expiration_datepicker.datepicker('setDate',exp_date);
					<?php
				}
				?>
								
			})
		</script>
		<?php
	}

	?>
	</div>
	<?php
}

//============================================================
// 	NOW WRAP UP THE FORM
//============================================================

?>
<br>
<div id="submit_container">
	<input type="button" value="Submit Changes" class='list_button' id='module_submit'><input type='submit' value='Submit Changes' style='display: none'>
	 or 
	<a href='./?admin_action=<?=$this->module_arr['admin_action']?>'>Cancel Changes</a>
</div>
</form>
<div class="jqmWindow" id="status_dialog"></div>
<style type="text/css">
	/*Modal window styling*/
	.jqmWindow {
		display: none;
		
		position: fixed;
		top: 17%;
		left: 50%;
		
		margin-left: -300px;
		width: 600px;
		
		background-color: #FFF;
		border: 3px solid #666;
		padding: 12px;

		z-index: 200;
	}
	
	.jqmOverlay { background-color: #000; z-index: 50}
	
	/* Fixed posistioning emulation for IE6
		 Star selector used to hide definition from browsers other than IE6
		 For valid CSS, use a conditional include instead */
	* html .jqmWindow {
		 position: absolute;
		 top: expression((document.documentElement.scrollTop || document.body.scrollTop) + Math.round(17 * (document.documentElement.offsetHeight || document.body.clientHeight) / 100) + 'px');
	}
	
	#status_dialog{
		top: 25%;
		width: 200px;
		margin-left: -100px;
	}
	
	
	.module_field_notes{
		line-height: 1.2em;
		font-size: 90%;
		color: #666;
	}
	
	
	/*Tags stuff*/
	.tags_left{
		float: left;
	}
	
	.tags_right{
		float: left;
		margin-left: 30px;
		border: 3px solid #CCC;
		background-color: #FEFEFE;
		padding: 5px;
		width: 400px;
	}
	
	.tags_list{
		list-style: none;
		padding: 0px;
		margin: 0px;
	}
	
	.tags_list li{
		float: left;
		border: 1px solid #CCC;
		background-color: #FFF;
		padding: 2px 4px;
		color: #666;
		margin: 2px;
	}

	.tags_list li span{
		padding-left: 10px;
		font-weight: bold;
		cursor: pointer;
	}


	#content_expiration_div{
		border: 1px solid #CCC;
		background-color: #F9F9F9;
		padding: 10px;
	}
	
	#content_expiration_div h3{
		margin-top: 0px;
	}
</style>

<?php
//============================================================
//  RUN after_form()
//============================================================
if ( method_exists($module_actions,'after_form') )
{
	$module_actions->after_form();
}

//============================================================
// 	}
//============================================================
?>
<script language="javascript">
	var html_editors = new Array();
	$().ready(function(){
		$("#tool_title").html('<?php global $admin_page_title; echo addslashes($admin_page_title)?>').show();
		
		$(".html_editor").each(function(){
		
			unique_id = $(this).attr('name');
			html_editors.push(unique_id); //add to editors array
			editor_height = $(this).attr('rel');
			
			
			eval('o = CKEDITOR.instances.'+unique_id); //if instance exists...
			if ( o ){
				CKEDITOR.remove(o); //Kill it till it's dead from it
			}
			
			CKEDITOR.replace( unique_id, {
				 filebrowserBrowseUrl : '/cms/js/ckfinder/ckfinder.html',
				 filebrowserImageBrowseUrl : '/cms/js/ckfinder/ckfinder.html?Type=Images',
				 filebrowserFlashBrowseUrl : '/cms/js/ckfinder/ckfinder.html?Type=Flash',
				 filebrowserUploadUrl : '/cms/js/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Files',
				 filebrowserImageUploadUrl : '/cms/js/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Images',
				 filebrowserFlashUploadUrl : '/cms/js/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Flash',
				 height: editor_height
			} );
		})
		
	})
</script>