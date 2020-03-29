<?php
$library_path = ABS_SITE_DIR.urldecode($_REQUEST['library_path']);
$image = urldecode($_REQUEST['image']);
echo $library_path.$image."\n\n";

unlink($library_path.'/'.$image);
unlink($library_path.'/thumbs/'.$image);
?>