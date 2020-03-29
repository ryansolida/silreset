<?php
$rel_library_path = urldecode($_REQUEST['library_path']);
$abs_library_path = ABS_SITE_DIR.$rel_library_path;
$library_name = urldecode($_REQUEST['library_name']); //OUR UNIQUE IDENTIFIER FOR THE GALLERY
$current = urldecode($_REQUEST['current']); 
?>

<div id="image_list_<?=$library_name?>">


<?php
//ready directory and show thumbs
if ($handle = opendir($abs_library_path)) {
	$file_count = 0;
	while (false !== ($file = readdir($handle))) {
	    if ($file != "." && $file != ".." &&  !is_dir($abs_library_path.'/'.$file) ) {
	    	$file_count++;
	    	
	    	$border="2px solid #CCC";
	    	if ( $file == basename($current) ){
	    		$border="2px solid #000";
	    	}
			?>
			<div class='image_library_element' id='<?=urlencode($rel_library_path.'/'.$file)?>' style='cursor: pointer; position: relative; float: left; width: 170px; height: 100px; padding: 5px; border: <?=$border?>; margin: 5px; background-image: url(/media/images/<?=$library_name?>/thumbs/<?=$file?>); background-repeat: no-repeat; background-position: center center; background-color: #FFF'>
				<div class='delete_link' style='position: absolute; bottom: 0px; right: 0px; width: 25px; height: 25px; display: none; background-color: #FFF; line-height: 25px; text-align: center; border: solid #CCC; border-width: 1px 0px 0px 1px;'><span id="<?=urlencode($file)?>" style='font-weight: normal;'>x</span></div>
			</div>
			<?php				
	    }
	}
	
	if ( $file_count > 0 ){
		// CREATE AN EMPTY ELEMENT TO CHOOSE
		$border="2px solid #CCC";
	    if ( $current == '' ){
	    	$border="2px solid #000";
	    }
		?>
		<div class='image_library_element' id='' style='cursor: pointer; position: relative; float: left; width: 170px; height: 100px; padding: 5px; border: <?=$border?>; margin: 5px; background-image: url(/media/images/<?=$library_name?>/thumbs/<?=$file?>); background-repeat: no-repeat; background-position: center center; background-color: #FFF'>
			<div style='line-height: 100px'><center>No Image</center></div>
		</div>
		<?php
	}
	?>
	
	<div style='clear: both'></div>
	<?php
	closedir($handle);
}
if ( $file_count == 0 ){
	echo "<br />&nbsp;&nbsp;No Images Currently Available";
}
?>

</div>
<script language="javascript">
	$().ready(function(){
		$('#image_list_<?=$library_name?>').find('.image_library_element').mouseenter(function(){
			$(this).find('.delete_link').show();
		}).mouseleave(function(){
			$(this).find('.delete_link').hide()
		}).click(function(){
			selected = $(this).attr('id').replace('image_','');
			$('#image_library_value_<?=$library_name?>').val(unescape(selected));
			$('#image_list_<?=$library_name?>').find('.image_library_element').css('border','2px solid #CCC');
			$(this).css('border','2px solid #000');
		})
		
		$('#image_list_<?=$library_name?>').find('.delete_link').mouseenter(function(){
			$(this).css('borderWidth','2px 0px 0px 2px').find('span').css('font-weight','bold').css('color','#000').css('font-size','125%');
		}).mouseleave(function(){
			$(this).css('borderWidth','1px 0px 0px 1px').find('span').css('font-weight','normal').css('color','').css('font-size','');
		}).click(function(){
			var to_close = $(this).parents('.image_library_element');
			var image = $(this).find('span').attr('id');
			if ( confirm('Are you sure you want to delete this image?  This cannot be undone and you will need to remove all references to this image on other pages.') ){
				$.ajax({
					url: './?nt=1&admin_action=pages&admin_subaction=image_library&library_action=delete_image&library_path=<?=urlencode($rel_library_path)?>&image='+image,
					success: function(msg){
						to_close.fadeOut(300,function(){
							reload_image_list_<?=$library_name?>();
						});
					}
				})
			}
		})
	})
</script>