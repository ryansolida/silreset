<?php
require_once(THE_GUTS_DIR."/includes/classes/global/image_upload.php");

$file_arr = $_FILES["image_to_upload"];

//see if file is blank, if so, skip everything else
if ( $file_arr['name'] != '' && $file_arr['error'] == 0 )
{
	//the value on this type of input has the destination directory, and the max side in px separated by commas
	$dest_dir = urldecode($_POST['dest_dir']);
	
	if ( $thumb_size == '' )	{
		$thumb_size = '200';
	}
	
	$max_side = 'noresize';
	if ( $_POST['max_side'] != '' ){
		$max_side = $_POST['max_side'];	
	}
	
	//resize and physically upload file
	$upload = new image_upload('image_to_upload', ABS_SITE_DIR.$dest_dir.'/', $max_side, $thumb_size);
	$file_path = $upload->get_file_path();
	
	echo "made it";
}
exit;
?>