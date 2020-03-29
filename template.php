<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
	"http://www.w3.org/TR/html4/strict.dtd">
<html>
	<head>
		<link rel="stylesheet" href="/cms/css/systemcss.css" type="text/css" media="all" />
		<link rel="stylesheet" href="/cms/js/plugins/fancybox/jquery.fancybox-1.3.1.css" type="text/css" media="all" />
		<!--[if IE]>
		        <link rel="stylesheet" type="text/css" href="/cms/css/systemcssie.css" />
		<![endif]-->
		<script src="/cms/js/system.js" type="text/javascript"></script>
		<script src="/cms/js/json2.js" type="text/javascript"></script>
		<script src="/cms/js/jquery.js" type="text/javascript"></script>
		<script src="/cms/js/plugins/form.js" type="text/javascript"></script>
		<script src="/cms/js/plugins/jqueryui.js" type="text/javascript"></script>
		<script src="/cms/js/plugins/rounded_corners.js" type="text/javascript"></script>
		<script type="text/javascript" src="/cms/js/plugins/fancybox/jquery.fancybox-1.3.1.pack.js"></script>
		
		<?php
		$root_exp = explode("/",$_SERVER['DOCUMENT_ROOT']);
		$company = $root_exp[2];
		
		//custom css
		$rel_css_file = '/cms/custom/'.$company.'/custom.css';
		$abs_css_file = ABS_SITE_DIR.$rel_css_file;
		if ( file_exists($abs_css_file) ){
			?>
			<link rel="stylesheet" href="<?=$rel_css_file?>" type="text/css" media="all" />
			<?php
		}
		
		//custom js
		$rel_js_file = '/cms/custom/'.$company.'/custom.js';
		$abs_js_file = ABS_SITE_DIR.$rel_js_file;
		if ( file_exists($abs_css_file) ){
			?>
			<script src="<?=$rel_js_file?>" type="text/javascript"></script>
			<?php
		}
		?>

		
		<title><?= SITE_FULL_TITLE ?> Admin</title>
	</head>
	<body>
		<div id="wrapper">
			<div id="title">
				<div id="title_fade"></div>
				<div id="title_content"><?= SITE_FULL_TITLE ?> Admin</div>
				<div id="logoLeft"></div>
				<div id="logoRight"></div>
			</div>
			<?php
			if ( $_SESSION['reset']['logged_in'] != '' && $logged_out == '' || $logged_in == 1 )
			{?>
			<div id="main_menu_holder">
				<ul id="main_menu" class="tabs" style="margin-left: 20px;">
					<?php
						require('template_tools_nav.php');
					?>
				</ul>
				<?php
				/*
				</div>
				*/
				?>
			</div>
			<div id="admin_right_links">
				<a href="/admin/?admin_action=logout">Logout</a></li>
			</div>
			<div style="clear: both"></div>
			<?php
			}
			
			//now set up the script
			if ( !$iframe )
			{
				?>
				<!--<div id="tool_top" class="bg_color2 border1"></div>-->
				<div id="tool_body">
					<h1 id='tool_title' style='display: none; margin: 0px 0px 20px 0px; padding: 0px'></h1>
					<?php require($req_file)?>
				</div><?php
			}
			else
			{
				?><iframe width="100%" height="100%" src="<?=$url?>" border="0" style="border: 0px"><?php
			}
			?>
			<textarea style='display: none' id='debug' cols='80' rows='10'></textarea>
		</div>
		<script language='javascript'>
			var old_clickee_html = '';
			var menu_timer;
			var submenu_timer;
			$().ready(function(){
				
				$(".menu_parent").mouseenter(function(){
					$(".drop_level1, .drop_level2").hide();
					$(".menu_subparent > a").removeClass('active_sub');
					$(this).find('.drop_level1').show();
					clearTimeout(menu_timer);
				}).mouseleave(function(){
					menu_timer = setTimeout(function(){$(".drop_level1, .drop_level2").fadeOut(100)},1000);
				})
				
				$(".menu_subparent").mouseenter(function(){
					$(".drop_level2").hide();
					$(".menu_subparent > a").removeClass('active_sub');
					$(this).find('.drop_level2').show().siblings('a').addClass('active_sub');
					clearTimeout(submenu_timer);
				}).mouseleave(function(){
					submenu_timer = setTimeout(function(){$(".menu_subparent > a").removeClass('active_sub');$(".drop_level2").fadeOut(100)},1000);
				})
			})
			
			function close_menus()
			{
				$(".menu_parent div").fadeOut(200);
			}
			
			function show_local_notification(notification){
				$(".local_notifications").html(notification).show();
				$(".local_notifications").effect("highlight", {color: '#FFF'}, 2000);
				$(".local_notifications").slideUp(50);
				
			}
			
			function init_fancybox(){
				$(".fancybox").fancybox();
			}
		</script>
	</body>
</html>