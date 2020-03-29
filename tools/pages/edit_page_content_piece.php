<?php
$rand = rand();
if ( $_REQUEST['id'] != '' )
{
	$query_str = "SELECT * FROM page_pieces_edited WHERE id = " . $_REQUEST['id'];
	$db = new db;
	$results = $db->qquery($query_str);
	$info = $results[0];
	$type = $results[0]['type'];
}
else
{
	$type = $_REQUEST['content_type'];
}

$elements = new local_elements;
$name = $elements->elements_list[$type]['name_display'];

?>
<div class='inline_form'> 
	<div class="action_holder"><?=$name?>:</div>
	<div id='form_<?=$rand?>'>
		<form id='edit_page_content_piece_form' method="POST" action="./" onsubmit="return false">
			<fieldset class="shortform border2" style='padding: 5px; background-color: #FFF;'>
			<input type='hidden' name='admin_action' value='pages'>
			<input type='hidden' name='admin_subaction' value='edit_page_content_piece'>
			<input type='hidden' name='submitted' value='true'>
			<input type='hidden' name='id' value='<?=$_REQUEST['id']?>'>
			<input type='hidden' name='content_page_id' value='<?=$_REQUEST['page_id']?>'>
			<input type='hidden' name='content_type' value='<?=$type?>'>
			<input type='hidden' name='content_order' value='<?=$_REQUEST['content_order']?>'>
			<input type='hidden' name='content_section' value='<?=$_REQUEST['section']?>'>
			<?php
			if ( $elements->elements_list[$type]['json'] ){
				?>
				<input type='hidden' name='json' value='true'/>
				<?php
			}
			?>
			<input type='hidden' name='nt' value='1'>
				
				<div style='padding: 10px 5px'>
					<?php
						$method='display_'.$type.'_edit';
						$elements->$method(array('data'=>$info['data'],'rand'=>$rand));
					?>
				</div>
			</fieldset>
			<div class='actions bg_color3'>
				<input type='submit' value="Update Content"> or <a href='#' class='cancel_inline_form'>Cancel</a>
			</div>
			<div id='submitting_<?=$rand?>' style='display: none; padding: 20px; padding-top: 0px; color: #666; font-size: 90%;'>Submitting Changes... please wait <img src='/cms/images/loader.gif'></div>
		</form>
	</div>
</div>

<script language="javascript">
	var editors_present = false;
	
	var html_editors = new Array(); //for attaching to form later
	
	$().ready(function(){
		var options = {
			beforeSubmit: prep_form,
			success: return_success
		};
		
		$("#edit_page_content_piece_form").ajaxForm(options);
		
		$(".cancel_inline_form").click(function(){
			var this_form = $(this).parents('.inline_form');
			this_form.fadeOut(100, function(){
				this_form.remove();
				refresh_content();
			});
		});
		
		<?php
		//============================================================
		// 	Ryan Added this June 16, 2010
		//		Add a class of 'html_editor' to any textarea and BOOM! you get an html editor
		//============================================================
		?>
		$(".html_editor").each(function(){
		
			unique_id = $(this).attr('name');
			html_editors.push(unique_id); //add to editors array
			editor_height = $(this).attr('rel');
			
			
			eval('o = CKEDITOR.instances.'+unique_id); //if instance exists...
			if ( o ){
				CKEDITOR.remove(o); //Kill it till it's dead from it
			}
			
			CKEDITOR.replace( unique_id, {
				<?php
				if ( file_exists(ABS_SITE_DIR.'/css/editor.css') ){
					?>
					 extraPlugins : 'stylesheetparser',
					 contentsCss : '/css/editor.css',
					 stylesSet : [],
					<?php
				}
				?>
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
	
	function return_success(responseText){	
		//$("#debug").val(responseText).show();
		$("#publish_button").show();
		refresh_content();
	}
	
	function prep_form(formData, jqForm, options) 
	{
		<?php
		//============================================================
		// 	Ryan added this first section June 16, 2010 to allow multiple HTML editors per element
		//============================================================
		?>
		//prep the html editors
		for ( var i=0; i<html_editors.length; i++ )
		{
			eval('editor = CKEDITOR.instances.'+html_editors[i]);
			for(var j=0; j<formData.length; j++ ){
				if ( formData[j].name == html_editors[i] ){
					formData[j].value = editor.getData();
				}
			}
		}
		<?php
		//============================================================
		// 	END Ryan's add
		//============================================================
		
		
		// left this next one as to not break everything until we can fully migrate
		?>
		if (CKEDITOR)
		{
			
			if ( editor = CKEDITOR.instances.content_<?=$rand?> ){
				for(var i=0; i<formData.length; i++ ){
					if ( formData[i].name == 'content_data' ){
						formData[i].value = editor.getData();
					}
				}
			}
		}
		
		$(".actions").hide();
		$("#submitting_<?=$rand?>").show();
	}
</script>