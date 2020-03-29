<div style="width: 500px; padding: 40px;">
	<h1 style="margin-top: 0px">Upload New Image</h1>
	<div style='border: 5px solid #DDD; background-color: #FAFAFA; padding: 25px;' id="upload_container">
		<strong>Choose File To Upload: </strong><br /><br />
		<form id="upload_form" action="./" method="POST" enctype="multipart/form-data">
			<input type="hidden" name="admin_action" value="pages">
			<input type="hidden" name="admin_subaction" value="image_library">
			<input type="hidden" name="library_action" value="upload_process">
			<input type="hidden" name="dest_dir" value="<?=$_REQUEST['dest_dir']?>">
			<input type="hidden" name="max_side" value="<?=$_REQUEST['max_side']?>">
			<input type="hidden" name="nt" value="1">
			<input type="file" name="image_to_upload">
			<br /><br /><br />
			<div style='padding: 10px; border-top: 1px dashed #CCC;' id="submit_section">
				<input type="submit" value="Upload Image"> or <a href="javascript:;" onclick="$.fancybox.close()">Cancel</a>
			</div>
		</form>
	</div>
</div>

<script language="javascript">
	$().ready(function(){
		$('#upload_form').ajaxForm({
			beforeSubmit: function(){
				$("#submit_section").html('Uploading Image.  Please wait...');
			},
			 success: function(data) {
				$("#upload_container").html("File Uploaded! <br /><span style='font-size: 80%; color: #999'>(This window will close in 2 seconds)</span>");
				setTimeout(function(){$.fancybox.close(); reload_image_list_<?=$_REQUEST['directory']?>()},2000);
			 }
		})
	})
</script>