<?php
$page = new page($_REQUEST['page_id']);
$page_info = $page->page_info;
$page_sections = $page_info['page_content'];

$elements = new local_elements();
if ( $page_sections['pre_process'] ){
	?>
	<div class='nocontent' style='padding: 10px'>
		<p style='padding: 20px; border-width: 3px' class="border2 bg_color3 round5 pre_process"><strong>This page uses an external script to run.</strong> <br />
		<?php if ( $_SESSION['reset']['user_priv_level'] < 99 ){
			?>
			Contact your site administrator if updates are necessary to this page.
			<?php
		} else {
			$piece = $page_sections['pre_process'][0];
			?>
			<li id="pieces_<?=$piece['id']?>" class='content_li pre_process'>
				<div class='content_piece'>
					<div class='action_holder top_round3'>
						<div class="contents">
							<a href='javascript:;' class='edit list_button' id='edit_<?=$piece['id']?>'>Edit Content</a>
							&nbsp;&nbsp;&nbsp;&nbsp;
							<strong><?=$elements->elements_list[$piece['type']]['name_display']?></strong>
							<div style="float: right; padding-right: 30px; padding-top: 10px;">
								<div style="float: right; margin: 0px; padding: 0px; margin-left: 6px; height: 13px; line-height: 0px;"><a href="javascript:;" class="action_icon delete" rel="<?=$piece['id']?>"><img src="/cms/images/ico_trash.png" height="13" border="0"></a></div>
							</div>
							<div style="clear: both"></div>
						</div>
					</div>
					<div class='content_display' style='clear: both'>
						<div class="content">
							<?php
							$method = 'display_'.$piece['type'];
							$elements->$method(array('data'=>$piece['data'],'location'=>'admin'));
							?>
						</div>
					</div>
				</div>
			</li>
			<?php
		}
		?>
		</p>
	</div>
	<?php
}
elseif ( count($page_sections[$_REQUEST['section']]) == 0 )
{
	?>
	<div class='nocontent' style='padding: 10px'>
		<p style='padding: 20px; border-width: 3px' class="border2 bg_color3 round5"><strong>There is currently no content in this page section.</strong> <br />Choose your first content type from the list below.</p>
	</div>
	<?php
}
else{
	foreach ($page_sections[$_REQUEST['section']] as $piece)
	{
		?>
		<li id="pieces_<?=$piece['id']?>" class='content_li'>
			<div class='content_piece'>
				<div class='action_holder top_round3'>
					<div class="contents">
						<a href='javascript:;' class='edit list_button' id='edit_<?=$piece['id']?>'>Edit Content</a>
						&nbsp;&nbsp;&nbsp;&nbsp;
						<strong><?=$elements->elements_list[$piece['type']]['name_display']?></strong>
						<div style="position: absolute; right: 15px; top: 10px;"><?php /*float: left; padding-right: 30px; padding-top: 10px;"> */ ?>
							<div style="float: right; margin: 0px; padding: 0px; margin-left: 6px; height: 13px; line-height: 0px;"><a href="javascript:;" class="action_icon delete" rel="<?=$piece['id']?>"><img src="/cms/images/ico_trash.png" height="13" border="0"></a></div>
							<div style="float: right; width: 16px; height: 13px; cursor: move; background: url(/cms/images/ico_arrows.png)" class="action_icon reorder"></div>
						</div>
						<div style="clear: both"></div>
					</div>
				</div>
				<div class='content_display' style='clear: both'>
					<div class="content">
						<?php
						$method = 'display_'.$piece['type'];
						$elements->$method(array('data'=>$piece['data'],'location'=>'admin'));
						?>
					</div>
				</div>
			</div>
		</li>
		<?php		
	}

}

//============================================================
// 	BUILD "ADD CONTENT" LIST
//============================================================
foreach ($elements->elements_list as $element)
{
	$show_link = false;
	
	if ( $_SESSION['reset']['user_priv_level'] >= $element['min_priv_level'] ){
		if ( !$element['page_sections'] ){
			$show_link = true;
		} else {
			if ( is_string($element['page_sections']) ){
				$element['page_sections'] = array($element['page_sections']);
			}
			if ( in_array($_REQUEST['section'],$element['page_sections']) ){
				$show_link = true;
			}
		}
	}
	
	if ( $show_link ){
		$links .= "<a href='javascript:;' class='new_content_link' rel='".$element['name_actual']."'>".$element['name_display']."</a> ";
		$options .= "<option value='".$element['name_actual']."'>".$element['name_display']."</option>";
	}
}
?>


<div id="new_content_template" style='display: none;'>
	<a href="javascript:" class="new_content_btn" rel="REL"></a>
	<div class='new_content' rel='REL'>
		<div class='new_cont_container round5 border1 bg_color3' style="display: none" rel="REL">
			<span class='color1' style="font-size: 80%">Add New: </span> 
			<select class="content_types">
				<?=$options?>
			</select>
			&nbsp;
			<a href="javascript:;" class="list_button new_content_link" style="margin-right: 0px">Add Content</a>&nbsp;&nbsp;<span style="font-size: 90%">or</span> <a href="javascript:;" class="new_content_close" rel="REL">Cancel</a>
		</div>
		<div class='clear'></div>
	</div>
</div>