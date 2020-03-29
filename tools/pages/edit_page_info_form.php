<?php
$edit_create = 'create';
if ( $_REQUEST['id'] != '' )
{
	$edit_create = 'edit';
	$page = new page($_REQUEST['id']);
	$page_info = $page->page_info;
}
?>
<h2 class="primary"><?=ucfirst($edit_create)?> Page</h2><div class="clear"></div>
<form id='<?=$edit_create?>_page_info_form' method="POST">
	<input type='hidden' name='admin_action' value='pages'>
	<input type='hidden' name='admin_subaction' value='<?=$edit_create?>_page'>
	<input type='hidden' name='page_id' value='<?=$_REQUEST['id']?>'>
	<input type='hidden' name='nt' value='1'>
	<fieldset class="shortform">
		<label for="page_title">Title:</label>
		<input type="text" name="page_title" id="page_title" size="60" value="<?= $page_info['title'] ?>" style="font-size: 130%" />
		<?=defined('TC_MAX_PAGE_TITLE_LENGTH')?'<br /><div class="field_notes">(Limit: '.TC_MAX_PAGE_TITLE_LENGTH.' characters)</div>':''?>
		<br /><br />
		<?php
		$hide_code = '';
		if ( $page_info['id'] == 1 )
		{
			?>
			<input type="hidden" name="page_name_actual" id="page_name_actual" value="home"/>
			<input type="hidden" name="page_parent" id="page_parent" value="0"/>
			<?php
		}
		else
		{
		?>
			<label for="page_name_actual">Unique Name: </label>
			<input type="text" name="page_name_actual" id="page_name_actual" size="60" value="<?=$page_info['name_actual']?>" style="font-size: 90%" disabled/>
			 <a href="javascript:;" style="font-weight: normal; font-size: 90%; text-decoration: underline" onclick="enable_unique()">Edit</a>
			<br /><br />
	
			<label for="page_parent">Page Parent: </label>
			<select name="page_parent" id="page_parent">
				<?php
				//============================================================
				// 	we're gonna build this here but it should be somewhere else
				//============================================================
				?>
				<option value="1"<?=($page_info['parent'] == 1 ? ' SELECTED':'')?>>Home</option>
				<?php
				$pages = new pages;
				$page_list = $pages->get_pages(array('parent'=>1,'skip_content'=>true));
				foreach($page_list as $p)
				{
					$selected = '';
					if ( $p['id'] == $_REQUEST['page_id'] )
					{
						$selected = ' SELECTED';
					}
					
					?><option value="<?=$p['id']?>"<?=($page_info['parent'] == $p['id'] ? ' SELECTED':'')?><?=$selected?>><?=$p['title']?> (<?=$p['path']?>)</option><?php
					if ( $p['is_parent'] )
					{
						$plist2 = $pages->get_pages(array('parent'=>$p['id'],'skip_content'=>true));
						foreach ($plist2 as $p2)
						{
							$selected = '';
							if ( $p2['id'] == $_REQUEST['page_id'] )
							{
								$selected = ' SELECTED';
							}
							
							?><option value="<?=$p2['id']?>"<?=($page_info['parent'] == $p2['id'] ? ' SELECTED':'')?><?=$selected?>><?=$p2['title']?> (<?=$p2['path']?>)</option>	<?php
						}
					}
				}
				?>
			</select>
			<br /><br />
		<?php
		}
		?>
		<label for="page_hidden">Page Visibility: </label>
		<select name="page_hidden" id="page_hidden">
			<option value="0">Visible</option>
			<option value="1"<?=$page_info['hidden']==1?' SELECTED':''?>>Hidden</option>
		</select>
		<div class='field_subset border2 bg_color3 round10' id='seo_fields'>
			<h3 style='margin: 5px; padding: 5px;'>Search Engine Fields</h3>
			<?php
			$field_arr = array('title','description','keywords');
			foreach ($field_arr as $field)
			{
			?>
				<label for="page_seo_<?=$field?>"><?=ucfirst($field)?>: </label>
				<?php
				if ( $field == 'description' ){
					?><textarea name="page_seo_<?=$field?>" id="page_seo_<?=$field?>" cols="75" rows="5"><?=$page_info['seo_'.$field]?></textarea><?php
				}
				else{
					?>
					<input type="text" name="page_seo_<?=$field?>" id="page_seo_<?=$field?>" size="90" value="<?=$page_info['seo_'.$field]?>"/>
					<?php
				}
				?>
				<br /><br />
			<?php
			}
			?>
		</div>
	</fieldset>
	<br />
	<div class='actions'>
		<input type='submit' value="<?=$edit_create=='create'?'Create Page':'Save Changes'?>"> or <a href='#' class='close_edit_info'>Cancel</a>
	</div>
</form>
<div id='modal_results_container' style='display: none; padding: 0px 15px;'>
	<div id='modal_results'></div>
	<a href='#' class='close_edit_info'>Close this window</a>
</div>

<script language="javascript">
	$().ready(function(){
		
		$(".clickee").html(old_clickee_html).removeClass('clickee');
		
		$("#<?=$edit_create?>_page_info_form").submit(function(){
			$('#page_name_actual').attr('disabled',false);
			$.ajax({
				method: "POST",
				data: $(this).serialize(),
				success: function(responseText){
					if ( responseText != '' ) //will return on update
					{
						show_local_notification(responseText);
						$('.create').html('+ Create New Page');
						window.location = './?admin_action=pages&admin_subaction=edit_page_content&id=<?=$_REQUEST['id']?>';
					}
					else //will return on create
					{	
						result_str = 'Page <?=ucfirst($edit_create)?>ed Successfully';
						show_local_notification(result_str.replace('ee','e'));
						get_pages(1);
						$(".close_edit_info").click();
			//			$("#modal_results_container").show();
			//			setTimeout(function(){$(".close_edit_info").click();}, 1000);
			//			$("#<?=$edit_create?>_page_info_form").hide();
					}

				}
			})
			return false;
		})
		
		<?php if ( $edit_create == 'create' )
		{ ?>
			$("#page_title").keyup(function(){
				$("#page_name_actual").val($("#page_title").val()).change();
			})
		<?php } ?>
		
		<?php
		/*
		if ( defined('TC_MAX_PAGE_TITLE_LENGTH') )
		{
			$max_length = TC_MAX_PAGE_TITLE_LENGTH;
			?>
			$("#page_title").keyup(function(){
				if ( $(this).val().length > <?=$max_length?> )
				{
					$(this).val($(this).val().substr(0,<?=$max_length?>));
				}
//								$(this).val('heyheyhey');
			})
			<?
		}
		*/
		?>
		
		$("#page_name_actual").change(function(){
			$(this).val($(this).val().replace(/ /g,'-')
				.replace(/\'/g,'')
				.replace(/\"/g,'')
				.replace(/\,/g,'')
				.replace(/\>/g,'')
				.replace(/\</g,'')
				.replace(/\//g,'')
				.replace(/\#/g,'')
				.replace(/\?/g,'')
				.replace(/\;/g,'')
				.replace(/\:/g,'')
				.replace(/\./g,'')
				.replace(/\&/g,'and')
				.toLowerCase()
			);
		})
	})
	
	function enable_unique(){
		$('#page_name_actual').attr('disabled',false).focus();	
	}
</script>