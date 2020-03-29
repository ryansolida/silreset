<h1>Page Privileges for <?=$display_name?></h1>

<?php
if  ($_REQUEST['user_id'] != '' ){
	$type = 'user_id';
	$$type = $_REQUEST['user_id'];
}

if ( $_REQUEST['group_id'] != '' ){
	$type = 'group_id';
	$$type = $_REQUEST['group_id'];
}

?>
<a href="./?admin_action=<?=str_replace('_id','',$type).'s'?>" class="list_button">&laquo; Back To <?=ucfirst(str_replace('_id','',$type))?>s</a>
&nbsp;&nbsp;&nbsp;&nbsp;
<a href="./?admin_action=<?=$_REQUEST['admin_action']?>&admin_subaction=modules&<?=$type?>=<?=$$type?>" class="list_button">View Module Permissions</a>
<br /><br />
<?php
//============================================================
// 	GET PAGES LIST
//============================================================
$pages = new pages;

?>
<table border="0" cellspacing="0" cellpadding="0" width="100%">
	<?php show_pages_recursive(0) ?>
	<?php show_pages_recursive(1) ?>
</table>
<?php

function show_pages_recursive($parent){
	global $pages;
	if($parent > 0){
		$pages_list = $pages->get_pages(array('parent'=>$parent));
	} else {
		$pages_list = $pages->get_pages(array('id'=>1));
	}
	
	$page_count = count($pages_list);
	for ($i=0; $i<$page_count; $i++ ){
		$page = $pages_list[$i];
		$class="odd";
		if ( $page['level']%2==0 ){
			$class="even";
		}
		?>
		<tr>
			<td style="padding-left: <?=($page['level']/2) * 50?>px;" class="page <?=$class?>" width="40%">
					<?php if ( $page['is_parent'] && $page['id'] > 1 ){
						?>
						<a href="javascript:;" class="expand" id="expand_<?=$page['id']?>">+</a>
						<?php
					}?>
					<?=$page['title']?>
				</div>
			</td>
			<td width="60%" class="page <?=$class?> perms" id="perms_<?=$page['id']?>">
				<label><input type="checkbox" class="all_perm_check"> All</label>
				&nbsp;&nbsp;|&nbsp;&nbsp;
				<label><input type="checkbox" class="perm_check" id="create_<?=$page['id']?>"> Create Descendents</label>
				&nbsp;&nbsp;|&nbsp;&nbsp;
				<label><input type="checkbox" class="perm_check" id="edit_content_<?=$page['id']?>"> Edit Content</label>
				&nbsp;&nbsp;|&nbsp;&nbsp;				
				<label><input type="checkbox" class="perm_check" id="publish_<?=$page['id']?>"> Publish</label>
				&nbsp;&nbsp;|&nbsp;&nbsp;								
				<label><input type="checkbox" class="perm_check" id="delete_<?=$page['id']?>"> Delete</label>
				&nbsp;&nbsp;|&nbsp;&nbsp;								
				<label><input type="checkbox" class="perm_check" id="reorder_<?=$page['id']?>"> Reorder Descendents</label>
			</td>
		</tr>
		<?php
		if ( $page['is_parent'] && $page['id'] > 1 ){
			?>
			<tr>
				<td colspan="10">
					<div id="children_of_<?=$page['id']?>" style="display: none">
					<table border="0" cellspacing="0" cellpadding="0" width="100%">
						<?php show_pages_recursive($page['id'])?>
					</table>
					</div>
				</td>
			</tr>
			<?php			
		}
	}
}
?>

<style type="text/css">
	.page{padding: 5px; border-bottom: 1px dashed #CCC;}
	.even{background-color: #EEE;}
	.odd{background-color: #F9F9F9;}
	.perms label{font-size: 12px; cursor: pointer}
	.perms{color: #999}
	.perms .active{color: #333}
</style>

<script language="javascript">
	$().ready(function(){
		$(".expand").click(function(){
			this_id = $(this).attr('id').replace("expand_","");
			if ( $(this).html() == '+' ){
				$(this).html('-');
				$("#children_of_"+this_id).slideDown(150);
			} else {
				$(this).html('+');
				$("#children_of_"+this_id).slideUp(150);
			}
		})
		
		get_permissions();
		
		//============================================================
		// 	CHECK ONE
		//============================================================
		$(".perm_check").change(function(){
			parent = $(this).parents('.perms');
			var page_id = parent.attr('id').replace('perms_','');
			
			//add or delete
			action = 'remove';
			if ( $(this).attr('checked') ){
				action ='add';
			}
			
			perm = $(this).attr('id').replace('_'+page_id,'');
			update_permissions(page_id,action,perm);
		})
		
		//============================================================
		// 	CHECK ALL
		//============================================================
		$(".all_perm_check").click(function(){
			if ( $(this).attr('checked') ) //if all is now checked, we are adding permission, but if 'all' is now unchecked we will be removing all permissions
			{
				$(this).parents('.perms').find('.perm_check').each(function(){
					$(this).attr('checked','true');
					action = 'add';
				})
			}
			else 
			{
				$(this).parents('.perms').find('.perm_check').each(function(){
					$(this).attr('checked','');
					action = 'remove';
				})
			}
			
			page_id = $(this).parents('.perms').attr('id').replace('perms_','');
			update_permissions(page_id,action,'all');
		})
	})
	
	function get_permissions(){
		//make ajax call
		$.ajax({
			url: './?nt=1&admin_action=permissions&admin_subaction=get_perms_data&<?=$type?>=<?=$$type?>&perms_type=pages',
			dataType: 'json',
			success: function(data){
				//alert(JSON.stringify(data));
				//first clear out all checks
				$(".perm_check").attr('checked',false).parents('label').removeClass('active');
				
				for ( var page in data ){
					for ( var perm in data[page] ){//alert(JSON.stringify(data[page]));//[page].create)
						if ( data[page][perm] ){
							$("#"+perm+"_"+page).attr('checked',true).parents('label').addClass('active');
						}
					}
					//if all are checked, check 'ALL' too
					this_page = $("#perms_"+page);
					if ( $(this_page).find('.perm_check:not(:checked)').length == 0 ){
						this_page.find('.all_perm_check').attr('checked','true');
					} else {
						this_page.find('.all_perm_check').attr('checked','');
					}
				}
			}
		})
	}
	
	function update_permissions(page_id,action,perm)
		{	
			$.ajax({
				url: './?nt=1&admin_action=permissions&admin_subaction=assign_perms_data',
				type: 'POST',
				data: '<?=$type?>=<?=$$type?>&page_id='+page_id+'&action='+action+'&perm='+perm,
				success: function(msg){
					get_permissions();
				}
			})
			
			//set to active or inactive
			if ( $(this).attr('checked') ){
				$(this).parents('label').addClass('active');
			} else {
				$(this).parents('label').removeClass('active');
			}
		}
</script>