<?php
$page = new page($_REQUEST['id']);
$page_info = $page->page_info;
$elements = new local_elements;
?>
<div id="page_content">
	<script type="text/javascript" src="/cms/js/ckeditor/ckeditor.js"></script>
	<h2 class='primary'>Editing Page "<?=$page_info['title']?>"</h2><a href='./?admin_action=pages' class='primary'>&larr; Back To Pages</a>
	<div class='clear'></div>
	<div class="local_notifications"></div>
	<div class='instructions' style="display: none">
		<div class='title'>Instructions:</div>
		Either choose a section of content to edit or create a new section by clicking any of the "Create New Content" buttons.<br />
		<strong>Note: </strong>Changes are saved automatically
	</div>
	
	<?php
	$rand = rand();
	$new_content_line = "";
	?>
	
	<table border='0' cellspacing='0' cellpadding='0' width='100%'>
		<tr>
			<td width='75%' valign='top'>
				<div class='ct_mgr border1 round5' style="border-width: 1px;">
					<?php
					if ( count($elements->page_sections) > 0 ){
						?>
						<div class="bg_color1" style="height: 22px; padding-top: 15px;">
							<ul class="tabs" id="page_section_tabs" style="float: left; width: 400px; margin-left: 20px; padding: 0px; height: 22px; z-index: 5">
								<?php
								foreach ($elements->page_sections as $page_section){
									?>
									<li><a href="javascript:;" onclick="refresh_content('<?=$page_section['name_actual']?>')" class="hover1 page_section_tab" rel="#<?=$page_section['name_actual']?>"><?=$page_section['name_display']?></a></li>
									<?php
								}
								?>
							</ul>
							<div style="float: right; margin-right: 20px; margin-top: -5px;">
								<a href="javascript:;" onclick="expand_contract_content()" style="color: #FFF; font-size: 80%; font-weight: normal;">Expand / Collapse Content</a>
							</div>
							<div style="clear: both"></div>
						</div>
						<?php
					}
					?>
					<div class="bg_color2 round5" style="padding: 15px; padding-top: 20px;">
						<ul id='page_pieces'></ul>
					</div>
				</div>
			</td>
			<td width='25%' valign='top' style="padding: 0px 10px;">
				<div class='right_bar bg_color3 border1 round5'>
					<div class='right_bar_title bg_color2 color1 text_shadow_light'><a href="javascript:;" id="edit_page_link" >Page Information / SEO</a><!--<div style="float: right"><a href="javascript:;" id="edit_page_link" style="font-weight: normal; font-size: 95%; text-decoration: underline; color: #333">Edit</a>&nbsp;&nbsp;</div>--></div>
					<div class='page_info'>
						<div class='title'><?=$page_info['title']?></div>
						<a target="_blank" href='' class='link'><?=$page_info['path']?></a>
						<br />
						<?php
							if ($page_info['seo_title'] != '' ){
								?><div class="status" style="margin-top: 15px;"><strong>SEO Title</strong><br /><?=$page_info['seo_title']?></div><?php
							}
							if ($page_info['seo_description'] != '' ){
								?><div class="status" style="margin-top: 15px;"><strong>SEO Description</strong><br /><?=$page_info['seo_description']?></div><?php
							}
							if ($page_info['seo_keywords'] != '' ){
								?><div class="status" style="margin-top: 15px;"><strong>SEO Keywords</strong><br /><?=$page_info['seo_keywords']?></div><?php
							}
						?>
					</div>
				</div>
				
				<div class="right_bar">
					<div class="page_info">
						<a href='..<?=$page_info['path']?>?preview=1' target='_blank' class="list_button">Preview Changes</a><br /><br />
						<div id="publish_button" style="display: none; padding-top: 5px">
							<a href='javascript:;' class='publish_button' style="padding: 10px 15px; font-size: 110%">Publish Updates</a><br /><br />
						</div>
						<div class='status'>Status: <span class='status_text callout_text1'><?=$page_info['published_is_latest']?'Published':'<span style="color: red">Needs Published</span>'?></span></div>
						<div class='last_publish'>Last Published: <span class='publish_text callout_text1'><?=$page_info['is_published']?date("M d, Y",strtotime($page_info['last_publish_date'])):'<span style="color: red">Never Published</span>'?></span></div>
					</div>
				</div>	
			</td>
		</tr>
	</table>
</div>

<div id="edit_page_form_container" style="display: none"></div>
<!--<div class='modal' id='edit_page_form_container' style='position: absolute; top: 75px; left:  150px; width: 740px; border: 5px solid #CCC; background-color: #FFF; padding: 10px; display: none'></div>-->

<script language="javascript">
	$().ready(function(){
		if ( $(".page_section_tab").length > 0 ){
			$(".page_section_tab:first").click();
		}
		else {
			refresh_content();
		}
				
		<?php	
		if ( !$page_info['published_is_latest'] )
		{
			?>$("#publish_button").show();<?php
		}
		?>
		
		$(".new_content_btn").live('click',function(){
			$(this).hide();
			box_to_show = $(".new_cont_container[rel="+$(this).attr('rel')+"]");
			box_to_show.fadeIn();
			box_to_show.find('.content_types').focus();
		})
		
		$(".new_content_close").live('click',function(){
			$(this).parents('.new_cont_container').hide();
			$(".new_content_btn[rel="+$(this).attr('rel')+"]").fadeIn();
		})
		
		//============================================================
		// 	PUBLISH
		//============================================================
		$(".publish_button").unbind('click').click(function(){
			var page_id = '<?=$_REQUEST['id']?>';
			if ( confirm("Are you sure you want to publish this page?") )
			{
				$(this).html('Publishing...');
				$.ajax({
					type: 'POST',
					url: "./?admin_action=pages&task=publish_page&page_id="+page_id+"&nt=1",
					success: function(msg){
						$(".status_text").html("Published");
						
						//pull month for display
						var m_names = new Array("Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec");
						var d = new Date();
						var curr_date = d.getDate();
						var curr_month = d.getMonth();
						var curr_year = d.getFullYear();
						$(".publish_text").html(m_names[curr_month] + " " + curr_date + ", " + curr_year);
						
						$("#publish_button").hide().find("a").html("Publish Updates");
						show_local_notification("Updates Published.");
					}
				})
			}
		})
		
		//============================================================
		// 	EDIT PAGE
		//============================================================
		$("#edit_page_link").click(function(){
			old_clickee_html = $(this).html();
			$.ajax({
				url: './?admin_action=pages&admin_subaction=get_page_form&id=<?=$_REQUEST['id']?>&nt=1',
				success: function(msg){
					/*
					$("#edit_page_form_container").html(msg).fadeIn(100);
					$("#edit_page_form_container input:first").focus();
					*/
					$("#page_content").slideUp(100,function(){
						$("#edit_page_form_container").html(msg).slideDown(100);
					})
				}
			})
		})

		$(".close_edit_info").live('click',function(){
			$("#edit_page_form_container").slideUp(100,function(){
				$("#page_content").slideDown(100);
			})
		})
		
		$(".action_icon").live('mouseenter',function(){
			$(this).animate({opacity: 1},10)
		}).live('mouseleave',function(){
			$(this).animate({opacity: .5},10)
		})
	});
	
	//============================================================
	// 	GENERATE ADD CONTENT LINKS
	//============================================================
	function generate_add_links()
	{
		$(".new_content:not(:last)").remove();
		
		var content_count = 0;
		
		$(".content_piece").each(function(){
			content_count++;
			$(this).before($("#new_content_template").html().replace(/REL/g,content_count));
		})
		
		content_count++;
		$(".content_piece:last").after($("#new_content_template").html().replace(/REL/g,content_count));
		
		content_count++;
		$(".nocontent").after($("#new_content_template").html().replace(/REL/g,content_count));
		
		$(".new_content_link").unbind('click').click(function(){
			var parent_cont = $(this).parents('.new_content');
			var content_type = $(this).siblings('.content_types').val();
			order = parent_cont.attr('rel');//id').replace('new_content_','');
			$(this).parent('.new_cont_container').html('Please Wait...');
			$(".nocontent").hide();
			$.ajax({
				url: './?admin_action=pages&admin_subaction=edit_page_content_piece&nt=1&page_id=<?=$_REQUEST['id']?>&content_type='+content_type+'&content_order='+order+'&section='+page_section,
				success: function(msg){
					parent_cont.fadeOut(100,function(){
						parent_cont.after(msg);
					});
				}
			})
		})
		
		$("#page_section_select").change(function(){
			refresh_content($(this).val());
		})
		
		$(".new_content").fadeIn(200);
	}
	
	
	
	
	var page_section = 'main_content'; //default
	function refresh_content(section)
	{
		if ( section != undefined ){
			section = section.replace('#',''); //pound sign added to fool firefox when our link is called 'sidebar'
			page_section = section;
		}
		
		$(".page_section_tab").removeClass('active_tab').addClass('hover1');
		$(".page_section_tab[rel=#"+page_section+"]").removeClass('hover1').addClass('active_tab');
		
		
		$("#section_loading").show();
		$.ajax({
			url: './?admin_action=pages&admin_subaction=get_page_pieces&section='+page_section+'&nt=1&page_id=<?=$_REQUEST['id']?>',
			success: function(msg){
				$("#section_loading").hide();
				$("#page_pieces").html(msg);
				if ( $("#page_pieces").find(".pre_process").length == 0 ){
					generate_add_links();
				}
				
				
				if ( expanded == false ){
					expanded = true;
					expand_contract_content();	
				}
								
				//============================================================
				// 	EDIT CONTENT
				//============================================================
				$(".edit").unbind('click').click(function(){
					this_id = $(this).attr('id').replace('edit_','');
					var parent_cont = $(this).parents('.content_li');
					parent_cont.find('.options').html('Please Wait...').css('padding-right','70px').css('font-size','12px');
					$.ajax({
						url: './?admin_action=pages&admin_subaction=edit_page_content_piece&nt=1&page_id=<?=$_REQUEST['id']?>&id='+this_id+'&section='+page_section,
						success: function(msg){
							parent_cont.fadeOut(100,function(){
								parent_cont.after(msg);
							});
						}
					})
				})
				
				//============================================================
				// 	DELETE
				//============================================================
				$(".delete").unbind('click').click(function(){
					var parent = $(this).parents('.content_li');
					piece_id = $(this).attr('rel');

					if ( confirm("Are you sure you want to delete this piece of content?\n\nTHIS ACTION IS NOT UNDOABLE") )
					{
						$.ajax({
							type: 'POST',
							url: "./?admin_action=pages&admin_subaction=delete_content_piece&page_id=<?=$_REQUEST['id']?>&nt=1&id="+piece_id,
							success: function(msg){
								parent.fadeOut(300,function(){
									refresh_content();
									$("#publish_button").show();
								})
							}
						})
					}
				})
				
				//============================================================
				// 	SORT
				//============================================================
				$("#page_pieces").sortable('destroy');
				$("#page_pieces").sortable({
					start: function(){
						$(".new_content, .new_content_btn").hide();
					},
					stop: function(){
						order = $("#page_pieces").sortable('serialize');
						$.ajax({
							url: './?admin_action=pages&admin_subaction=reorder_pieces&page_id=<?=$_REQUEST['id']?>&nt=1&'+order,
							success: function(msg){
								refresh_content();
								$("#publish_button").show();
							}
						});
						generate_add_links();
					},
					handle: $(".reorder"),
					axis: 'y'
				}).disableSelection();
				
				$(".action_icon").animate({opacity: '.5'},200)
			}
		})
	}
	
	var expanded = true;
	function expand_contract_content(){
		if ( expanded ){
			$(".content_display").animate({'height':'50'},0).css('overflow','hidden');
			expanded = false;
		}
		else{
			$(".content_display").css('height','auto').css('overflow','auto');
			expanded = true;
		}
	}
	
</script>
<textarea cols='70' rows='20' id='debug' style='display: none'></textarea>