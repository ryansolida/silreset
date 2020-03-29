<?php
class admin_helpers
{
	//======================================================================
	// 	SINGLETON DONE BY DIRECTION ON: http://www.imarc.net/communique/118-implementing_a_php_singleton/
	//======================================================================
	private static $instance;
	
//	private function __construct(){}
	
	public function parse_json($data){
		
		$data = json_decode($data,true);
		$return_data = array();
		if ( count($data) > 0 )
		{
			foreach($data as $key=>$value)
			{
				$return_data[$key] = stripslashes(trim(str_replace('%5c','\\',html_entity_decode($value))));
			}
		}
		
		return $return_data;
		
	}
	
	
	//=====================================================================
	// 	Overhaul of function made by ryan June 16, 2010
	//		Pass in the array 'data' and let the magic happen
	//		Other optional params:
	//			field_name - for JSON type stuff
	//			height - define a height for this bad boy
	//
	//		If the function receives a string as the argument, we assumed that's the content to populate the editor	
	//		
	//		*** The trick here is that the textarea is given the class 'html_editor' and the name and id of the field match
	//=====================================================================
	function render_html_editor($arr=false)
	{
	
		if ( !is_array($arr) ){
			$arr = array(
				'data'=>$arr
			);
		}
		
		$field_name = 'content_data';
		if ( $arr['field_name'] ){
			$field_name = $arr['field_name'];
		}
		
		$height = 300;
		if ( $arr['height'] ){
			$height = $arr['height'];
		}
		
		?>
		<textarea id='<?=$field_name?>' class='html_editor' name='<?=$field_name?>' style='width: 100%;' rel='<?=$height?>px'><?=$arr['data']?></textarea>
		<?php
	}
		
	function render_image_library($arr)
	{	
	
		$rel_directory = '/media/images/'.$arr['directory'];
		
		//====================================================================
		// 	$arr['directory'] is our unique identifier so make sure that 'directory' is ALWAYS unique in a given element
		//====================================================================
		
		// If we want to change the field name, pass 'field_name' as a parameter.  For instance, if we need this to be a JSON element, just pass json_field_name as 'field_name' and boom.  Done.
		$field_name = 'content_data';
		if ( $arr['field_name'] ){
			$field_name = $arr['field_name'];
		}
		
		//first, see if the directory is there, if not, create it
		$full_directory = ABS_SITE_DIR.$rel_directory;
		if ( !is_dir($full_directory) ){
			mkdir($full_directory, 0775);
		}
		
		//make the thumbs directory
		$thumbs_directory = $full_directory."/thumbs";
		if ( !is_dir($thumbs_directory) ){
			mkdir($thumbs_directory, 0775);
		}
			
		?>
		<strong>Existing Options:</strong><br />
		<div id="image_list<?=$arr['directory']?>"></div>
		<input id="image_library_value_<?=$arr['directory']?>" type="hidden" name="<?=$field_name?>" value="<?=$arr['current']?>">
		<br />
		
		&nbsp;&nbsp;&nbsp;&nbsp;<a href="./?admin_action=pages&admin_subaction=image_library&library_action=upload_form&nt=1&dest_dir=<?=urlencode($rel_directory)?>&directory=<?=$arr['directory']?>&max_side=<?=$arr['max_side']?>" class="fancybox list_button">+ Upload New Image</a>
		<div style="margin-bottom: 15px; padding-top: 20px; border-bottom: 3px solid #EEE"></div>
		
		<script language="javascript">
			$().ready(function(){
				init_fancybox();
				reload_image_list_<?=$arr['directory']?>();
			})
			
			function reload_image_list_<?=$arr['directory']?>(){
				$.get('./?nt=1&admin_action=pages&admin_subaction=image_library&library_action=get_list&library_path=<?=urlencode($rel_directory)?>&library_name=<?=$arr['directory']?>&current=<?=urlencode($arr['current'])?>', function(data){
					$("#image_list<?=$arr['directory']?>").html(data);
				})
			}
		</script>
		<?php
	}
	
	function get_image_thumb($img)	{
		 return str_replace(basename($img),'thumbs/'.basename($img),$img);
	}
}

?>