<?php
class resize_image {
   
   var $image;
   var $image_type;
 
   function load($filename) {
      $image_info = getimagesize($filename);
      $this->image_type = $image_info[2];
      if( $this->image_type == IMAGETYPE_JPEG ) {
         $this->image = imagecreatefromjpeg($filename);
      } elseif( $this->image_type == IMAGETYPE_GIF ) {
         $this->image = imagecreatefromgif($filename);
      } elseif( $this->image_type == IMAGETYPE_PNG ) {
         $this->image = imagecreatefrompng($filename);
      }
   }
   function save($filename, $image_type=IMAGETYPE_JPEG, $compression=93, $permissions=null) {
      if( $image_type == IMAGETYPE_JPEG ) {
      	
         imagejpeg($this->image,$filename,$compression);
      } elseif( $image_type == IMAGETYPE_GIF ) {
         imagegif($this->image,$filename);         
      } elseif( $image_type == IMAGETYPE_PNG ) {
         imagepng($this->image,$filename);
      }   
      if( $permissions != null) {
         chmod($filename,$permissions);
      }
   }
   function output($image_type=IMAGETYPE_JPEG) {
      if( $image_type == IMAGETYPE_JPEG ) {
         imagejpeg($this->image);
      } elseif( $image_type == IMAGETYPE_GIF ) {
         imagegif($this->image);         
      } elseif( $image_type == IMAGETYPE_PNG ) {
         imagepng($this->image);
      }   
   }
   function getWidth() {
      return imagesx($this->image);
   }
   function getHeight() {
      return imagesy($this->image);
   }
   function resizeToHeight($height) {
      $ratio = $height / $this->getHeight();
      $width = $this->getWidth() * $ratio;
      $this->resize($width,$height);
   }
   function resizeToWidth($width) {
      $ratio = $width / $this->getWidth();
      $height = $this->getheight() * $ratio;
      $this->resize($width,$height);
   }
   function scale($scale) {
      $width = $this->getWidth() * $scale/100;
      $height = $this->getheight() * $scale/100; 
      $this->resize($width,$height);
   }
   function resize($width,$height) {
      $new_image = imagecreatetruecolor($width, $height);
      imagecopyresampled($new_image, $this->image, 0, 0, 0, 0, $width, $height, $this->getWidth(), $this->getHeight());
      $this->image = $new_image;   
   }      
}


class image_upload 
{
	function image_upload($upload_field_name,$save_dir,$max_dimension,$thumb_size=130,$thumb_only=0,$full_only=0)
	{

		//run file upload
		
		// Check post_max_size (http://us3.php.net/manual/en/features.file-upload.php#73762)
			$POST_MAX_SIZE = ini_get('post_max_size');
			$unit = strtoupper(substr($POST_MAX_SIZE, -1));
			$multiplier = ($unit == 'M' ? 1048576 : ($unit == 'K' ? 1024 : ($unit == 'G' ? 1073741824 : 1)));
		
			if ((int)$_SERVER['CONTENT_LENGTH'] > $multiplier*(int)$POST_MAX_SIZE && $POST_MAX_SIZE) {
				?>
				<div class="admin_message">File Too Large.  Try uploading a smaller Image</div>
				<?php
				return false;
			}
		
		// Settings
			$upload_name = $upload_field_name;
			$max_file_size_in_bytes = 2147483647;				// 2GB in bytes
			$extension_whitelist = array("jpg", "jpeg", "gif", "png", "swf", "js", "fla");	// Allowed file extensions
			$valid_chars_regex = '.A-Z0-9_ !@#$%^&()+={}\[\]\',~`-';				// Characters allowed in the file name (in a Regular Expression format)
			
		// Other variables	
			$MAX_FILENAME_LENGTH = 260;
			
		
			$file_extension = substr($_FILES[$upload_name]['name'], -4);
			$uploadErrors = array(
				0=>"There is no error, the file uploaded with success",
				1=>"The uploaded file exceeds the upload_max_filesize directive in php.ini",
				2=>"The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form",
				3=>"The uploaded file was only partially uploaded",
				4=>"No file was uploaded",
				6=>"Missing a temporary folder"
			);
		
		
		// Validate the upload
			if (!isset($_FILES[$upload_name])) {
				?><div class="admin_message">"No upload found in <?=$_FILES?> for <?=$upload_name?></div><?php
				return false;
			} else if (isset($_FILES[$upload_name]["error"]) && $_FILES[$upload_name]["error"] != 0) {
				?><div class="admin_message"><?=$uploadErrors[$_FILES[$upload_name]["error"]]?></div><?php
				return false;
			} else if (!isset($_FILES[$upload_name]["tmp_name"]) || !@is_uploaded_file($_FILES[$upload_name]["tmp_name"])) {
				?><div class="admin_message">Upload failed is_uploaded_file test.</div><?php
				return false;
			} else if (!isset($_FILES[$upload_name]['name'])) {
				?><div class="admin_message">File has no name.</div><?php
				return false;
			}
			
		// Validate the file size (Warning the largest files supported by this code is 2GB)
			$file_size = @filesize($_FILES[$upload_name]["tmp_name"]);
			if (!$file_size || $file_size > $max_file_size_in_bytes) {
				?><div class="admin_message">File exceeds the maximum allowed size</div><?php
				return false;
			}
		
		
		// Validate file extention
			$path_info = pathinfo($_FILES[$upload_name]['name']);
			$file_extension = strtolower($path_info["extension"]);
			$is_valid_extension = false;
			foreach ($extension_whitelist as $extension) {
				if ($file_extension == $extension) { 
					$is_valid_extension = true;
					break;
				}
			}
			if (!$is_valid_extension) {
				?><div class="admin_message">Invalid file extension</div><?php
				return false;
			}
		
		// Validate file contents (extension and mime-type can't be trusted)
			/*
				Validating the file contents is OS and web server configuration dependant.  Also, it may not be reliable.
				See the comments on this page: http://us2.php.net/fileinfo
				
				Also see http://72.14.253.104/search?q=cache:3YGZfcnKDrYJ:www.scanit.be/uploads/php-file-upload.pdf+php+file+command&hl=en&ct=clnk&cd=8&gl=us&client=firefox-a
				 which describes how a PHP script can be embedded within a GIF image file.
				
				Therefore, no sample code will be provided here.  Research the issue, decide how much security is
				 needed, and implement a solution that meets the needs.
			*/
		
		
		// Process the file
			/*
				At this point we are ready to process the valid file. This sample code shows how to save the file. Other tasks
				 could be done such as creating an entry in a database or generating a thumbnail.
				 
				Depending on your server OS and needs you may need to set the Security Permissions on the file after it has
				been saved.
			*/
			
			//============================================================
			// 	Cleansing file name
			//============================================================
			//put timestamp in filename to not overwrite any files
			$file_name = $_FILES[$upload_name]['name'];
			$exp = explode('.',$file_name);
			$file_name = strtolower(str_replace(' ','_',$exp[0]).rand(0,5000).'.'.end($exp)); //replace spaces with dashes
			$temp_file_name = str_replace('.','_full.',$file_name); //make temporary file off of which we  will be doing resizes have the _full added in before the extension
			
			if (strlen($file_name) == 0 || strlen($file_name) > $MAX_FILENAME_LENGTH) {
				?><div class="admin_message">Invalid file name</div><?php
				return false;
			}
			
			$save_path = $save_dir;				// The path were we will save the file (getcwd() may not be reliable and should be tested in your environment)
			if (!@move_uploaded_file($_FILES[$upload_name]["tmp_name"], $save_path.$temp_file_name)) {
				?><div class="admin_message">File could not be saved:<?=$save_path.$temp_file_name?></div><?php
				return false;
			}
	
			//full size
			if($thumb_only != 1)
			{
				$image = new resize_image();
				$image->load($save_path.$temp_file_name);
				if ( $max_dimension != 'noresize' )
				{
					if ( $image->getWidth() > $image->getHeight() )
					{
						$image->resizeToWidth($max_dimension);	
					}
					else
					{
						$image->resizeToHeight($max_dimension);
					}
				}

				$image->save($save_path.$file_name);
			}
			
			//thumb
			if($full_only != 1)
			{
				$image = new resize_image();
				$image->load($save_path.$file_name);
				if ( $image->getWidth() > $image->getHeight() )
				{
					$image->resizeToWidth($thumb_size);	
				}
				else
				{
					$image->resizeToHeight($thumb_size);
				}

				$image->save($save_path."thumbs/".$file_name);
			}
		
			unlink($save_path.$temp_file_name);	
			
			$file_dest = str_replace(ABS_SITE_DIR,'',$save_path.$file_name); //take out absolute path
			$this->file_path = $file_dest;
	}
	
	function get_file_path()
	{
		return $this->file_path;
	}
		
}	
?>
