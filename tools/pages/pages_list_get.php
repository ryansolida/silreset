<?php
$pages = new pages;
$orig_page_list = $pages->get_pages(array('parent'=>$_REQUEST['parent'],'fields'=>'id', 'order_by'=>'`order`','skip_content'=>true));

if ( !$orig_page_list ){$orig_page_list = array();}

if ( $_REQUEST['parent'] == 1 )
{
	$home = new page(array('id'=>1,'skip_content'=>true));
	array_unshift($orig_page_list, $home->page_info);
}
foreach ($orig_page_list as $orig_page)
{
	if ( $orig_page['id'] != 1 && $first_ul_created == '')
	{
		?><ul class='page_list' id='page_list_<?=$_REQUEST['parent']?>'><?php
		$first_ul_created = 'created';
	}
	$page = new page(array('id'=>$orig_page['id'],'skip_content'=>true));
	$page_info = $page->page_info;
	?>
	<li class='page_li' id='page_<?=$page_info['id']?>'>
		<div class="page<?=($page_info['level']%2)==0?' even':' odd'?>">
			<div class="content">
				<table border="0" cellspacing="0" cellpadding="0" width="100%" id="page_table_<?=$page_info['id']?>">
					<tr>
						<?php
						$first_col_width = ($page_info['level']-1)*2;
						?>
						<td width="<?=$first_col_width?>%" style="background-color: #FFF;"></td>
						<td width="<?=45-$first_col_width?>%">
							
							<div class='page_title' style="margin-left: 15px; float: left;">
								<?php
								if ( $page_info['is_parent'] && $page_info['id'] != 1 ) //we don't want to show this for the home page
								{
									?><a href='javascript:;' class='child_toggle list_button' style="padding: 2px 5px; margin: 0px;" id='of_<?=$page_info['id']?>'<?php if(TC_EXPAND_PAGE_LISTS==true){echo " style='display: none'";}?>><img src="/cms/images/child_right_arrow.png" border="0"></a>&nbsp;&nbsp;<?php
								}
								else{
									?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php
								}
								?>
								<?=stripslashes($page_info['title'])?>
							</div>
							<?php
							if ( !$page_info['published_is_latest'] && $User->user_can('publish page '.$page_info['id']) )
								{
									?>
									<div style="float: right; text-align: right;"><a class="preview_link fade_button" href="<?=$page_info['path']?>?preview=1" target="_blank" style="text-decoration: underline">Preview Changes</a>&nbsp;&nbsp;<a href='javascript:;' class='publish_button fade_button' id='publish_<?=$page_info['id']?>'>Publish</a></div>
									<?php
								}	
							?>
						</td>
						<td width="40%">
							<div class='page_buttons'>
								<?php			
								if ( $User->user_can('edit_content in page '.$page_info['id']))
								{
								?>
								<a href='./?admin_action=pages&admin_subaction=edit_page_content&id=<?=$page_info['id']?>' class='fade_button list_button'>Edit</a>
								<?php
								}
								if ($page_info['is_published']){
									?>
									<a href='<?=$page_info['path']?>' target="_blank" class='fade_button list_button'>View</a>
									<?php
								}
								?>
							</div>
						</td>
						<td width="15%" nowrap style="text-align: right; padding-right: 15px;" class="action_icons">
							<!--
							<div class='page_published callout_text1 text_shadow_light'>
								<?php
								if ( $page_info['is_published'] )
								{
									?>Last Published <?=date('M j',strtotime($page_info['last_publish_date']))?><?php
								}
								else
								{
									?><span style='color: red'>Never Published</span><?php
								}
								?>
							</div>
							-->
							<?php
							if ( $page_info['id'] != 1 ) //we don't want to delete the homepage
							{	
								if ( $User->user_can('delete page '.$page_info['id']) )
								{
								?>
								<!--<a href='javascript:;' class='list_button delete<?= $page_info['is_parent']?' parent':''?>' id='delete_<?=$page_info['id']?>'>Delete</a>-->
								<div style="float: right; width: 16px; margin-left: 5px;"><a href="javascript:;" class="action_icon delete<?= $page_info['is_parent']?' parent':''?>" id='delete_<?=$page_info['id']?>' alt="Delete Page" title="Delete Page"><img src="/cms/images/ico_trash.png" height="14" border="0" style="padding-top: 5px"></a></div>
								<?php
								}
								if ( $User->user_can('reorder page '.$page_info['parent']) )
								{
								?>
								<!--<a href='javascript:;' class='list_button reorder' style='cursor: move;'>&uarr;&darr;</a>-->
								<div style="float: right; width: 16px; height: 13px; margin-top: 5px; cursor: move; background: url(/cms/images/ico_arrows.png)" class="action_icon reorder"></div>
								<?php
								}
							}
							?>
							<div style="clear: both"></div>
						</td>
					</tr>
				</table>
			</div>
		</div>
		<?php
		if ( $page_info['is_parent'] )
		{
			?>
			<div id="page_list_container_<?=$page_info['id']?>"></div>
			<?php
		}
		?>
	</li>
	<?php
}
?>
</ul>
<script language="javascript">
$().ready(function(){
	
	//============================================================
	//  CHILDREN HIDE AND SHOW
	//============================================================
	$(".child_toggle").unbind('click').click(function(){
		parent_id = $(this).attr('id').replace('of_','');
		if ( $(this).hasClass('showing') )
		{
			$("#page_list_container_"+parent_id+" *").fadeOut(200,function(){$(this).remove()});
			$(this).html('<img src="/cms/images/child_right_arrow.png" border="0">').removeClass('showing');
		}
		else
		{
			get_pages(parent_id);
			$(this).html('<img src="/cms/images/child_down_arrow.png" border="0">').addClass('showing');
		}
	})
	
	<?php
	if ( TC_EXPAND_PAGE_LISTS )
	{
		?>
		$(".child_toggle").each(function(){
			if (!$(this).hasClass('showing'))
			{
				$(this).click();
			}
		});
		<?php
	}
	?>
	
	$(".delete").unbind('click').click(function(){
			conf_text = "Are you sure you want to delete this page"
			if ( $(this).hasClass('parent') )
			{
				conf_text += " AND ALL OF ITS SUBPAGES";
			}
			
			if ( confirm(conf_text+"?") )
			{
				var page_id = $(this).attr('id').replace('delete_','');
				if ( confirm("NOTE: Deleting pages is NOT UNDOABLE\n\nAre you sure you want to continue deleting?") )
				{
					//$("#delete_"+page_id).html('Deleting...').unbind('click');
					$.ajax({
						type: 'POST',
						url: "./?admin_action=pages&task=delete_page&nt=1&page_id="+page_id,
						success: function(msg){
							$("#page_"+page_id).fadeOut(300,function(){
							//reload pages list from the top down
							})
						}
					})
				}
			}
		})
		
		$(".publish_button").unbind('click').click(function(){
			var page_id = $(this).attr('id').replace('publish_','');
			if ( confirm("Are you sure you want to publish this page?") )
			{
				$("#publish_"+page_id).html('Publishing...').unbind('click');
				$.ajax({
					type: 'POST',
					url: "./?admin_action=pages&task=publish_page&page_id="+page_id+"&nt=1",
					success: function(msg){
						//alert(msg);
						$("#publish_"+page_id).fadeOut(200);
						$("#publish_"+page_id).siblings('.preview_link').fadeOut(200);
					}
				})
			}
		})
		
		for ( i=1; i<=10; i++)
		{
			//window['pages_opened'+i] = new Array();
		}
		
		function get_open_pages()
		{
			for ( i=1; i<=10; i++ )
			{
				$(".level"+i).each(function(){
					if ( $(this).hasClass('showing') )
					{
						page_id = $(this).parents('.page').attr('id').replace('page_','');
						//window['pages_opened'+i][] = page_id;
					}
				})
			}
		}
		
		//============================================================
		// 	START REORDER
		//============================================================
		$(".page_list").sortable('destroy');
		$(".page_list").sortable({
			start: function(){
				sorting = true
			}, 
			stop: function(){
				sorting = false;
				order = $(this).sortable('serialize');
				var parent = $(this).attr('id').replace('page_list_','');
				$.ajax({
					url: './?admin_action=pages&admin_subaction=reorder_pages&nt=1&'+order,
					success: function(msg){
					}
				})
			},
			handle: $(".reorder"),
			axis: 'y'
		});
})
</script>