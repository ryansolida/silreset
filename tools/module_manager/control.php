<?php
require('modules.php');
$mod = new modules;
$subaction = $_REQUEST['admin_subaction'];
if ( $subaction == 'new_module' ){
	if ( $_POST ){
	} else {
		require('module_form.php');
	}
}
elseif ( $subaction == 'field_form' ){
	require('field_form.php');
}
else{
	require('list.php');
}
?>