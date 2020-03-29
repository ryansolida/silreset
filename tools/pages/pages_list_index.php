<div id="pages_list">
	<h2 class='primary'>Page List</h2><a href='#' class='primary create' id='create_page_link'>+ Create New Page</a>
	<div class='clear'></div>
	<div class="local_notifications"></div>
	<div class='instructions' style="display: none">
		<div class='title'>Instructions:</div>
		Either choose a page to edit, delete, publish or reorder or create a new page using the "Create New Page" button.
	</div>
	<div id='page_list_container_1' style='padding: 3px 10px'></div>
</div>

<!--<div class='modal' id='create_page_form_container' style='position: absolute; top: 75px; left: 150px; width: 740px; border: 5px solid #CCC; background-color: #FFF; padding: 10px; display: none'>
</div>-->

<div id='create_page_form_container' style='display: none'></div>

<script language="javascript">
	var sorting = false;
	$().ready(function(){
	
		//============================================================
		// 	CREATE PAGE MODAL
		//============================================================
		$(".close_edit_info").live('click',function(){
			$("#create_page_form_container").slideUp(100,function(){
				$("#pages_list").slideDown(100);
			})
		})
		
		
		$('.create').live('click', function(){
			old_clickee_html = $(this).html();
			$(this).html('Please Wait...').addClass('clickee');
			var parent_page = $(this).attr('rel');
			$.ajax({
				url: './?admin_action=pages&admin_subaction=get_page_form&nt=1&page_id='+parent_page,
				success: function(msg){
					$("#pages_list").slideUp(100,function(){
						$("#create_page_form_container").html(msg).slideDown(100);
						$("#page_title").focus();
					})
				}
			})
		})
		
		$(".page").live('mouseenter',function(){
			if ( !sorting ){
				$(this).addClass('hover');
				$(this).find('.fade_button').css('opacity','1');
				$(this).find('.action_icon').css('opacity','.7');
			}
		}).live('mouseleave',function(){
			$(this).removeClass('hover');
			$(this).find('.fade_button').animate({opacity:'.5'},100);
			$(this).find('.action_icon').animate({opacity:'0'},100);
		})
		
		$(".action_icon").live('mouseenter',function(){
			$(this).animate({opacity:'1'},10);
		}).live('mouseleave',function(){
			$(this).animate({opacity:'.7'},10);
		})
				
		//get first list by default
		get_pages(1);
	})
	//============================================================
	// 	GET PAGES
	//============================================================
	function get_pages(parent)
	{
		$("#page_list_container_"+parent).html('Loading Pages...')
		$.ajax({
			url: './?admin_action=pages&admin_subaction=get_pages_list&parent='+parent+'&nt=1',
			success: function(msg){
				$("#page_list_container_"+parent).html(msg);
				$(".fade_button").animate({opacity: '.5'})
				$(".action_icon").animate({opacity: '0'})
			}
		})
	}

</script>