<?php
$this_dir = THE_GUTS_DIR.'/tools/pages/image_library';
if ( $_REQUEST['library_action'] == 'get_list' ){
	require($this_dir.'/image_list.php');
}
elseif ( $_REQUEST['library_action'] == 'upload_process' ){
	require($this_dir.'/process_upload.php');
}
elseif ( $_REQUEST['library_action'] == 'upload_form'){
	require($this_dir.'/upload_form.php');
}
elseif ( $_REQUEST['library_action'] == 'delete_image' ){
	require($this_dir.'/delete_image.php');
}
?>