<?php

//============================================================
// 
//		function show_list()
//		{
//
//============================================================

global $module_actions;

//============================================================
// 	RUN before_list()
//============================================================
if ( method_exists($module_actions,'before_list') )
{
	$module_actions->before_list();
}


//============================================================
// 	SET UP PERMISSIONS
//============================================================

$permissions = array();			
$permissions['can_create'] = true;
$permissions['can_delete'] = true;
$permissions['can_edit'] = true;
$permissions['can_reorder'] = true;
$permissions['can_publish'] = true;

if ( DEEP_PERMISSIONS === true )
{
	if ( $_SESSION['priv_level'] < 50 )
	{
		//get this module id
		$query_str = "SELECT * FROM modules WHERE module_admin_action = '" . $_REQUEST['admin_action'] . "'";
		$db = new db();
		$module_info = $db->qquery($query_str);
		$module_id = $module_info[0]['module_id'];
		
		$query_str = "SELECT * FROM user_group_permissions WHERE ( user_id = " . $_SESSION['user_id'] . " AND module_id = $module_id )";
		
		for ( $i=0; $i<count($_SESSION['user_groups']); $i++ )
		{
			$query_str .= " OR (group_id = " . $_SESSION['user_groups'][$i] . " AND module_id = $module_id )";
		}
		
		$db = new db($this->module_arr['db']);
		$results = $db->qquery($query_str);
		$permissions_str = '';
		
		for ( $i=0; $i<count($results); $i++ )
		{
			$permissions_str.=$results[$i]['permissions'];
		}
		
		if ( strstr($permissions_str,'create') === false )
		{
			$permissions['can_create'] = false;
		}
		
		if ( strstr($permissions_str,'delete') === false )
		{
			$permissions['can_delete'] = false;
		}
		
		if ( strstr($permissions_str,'edit') === false )
		{
			$permissions['can_edit'] = false;
		}
		
		if ( strstr($permissions_str,'reorder') === false )
		{
			$permissions['can_reorder'] = false;
		}
		
		if ( strstr($permissions_str,'publish') === false )
		{
			$permissions['can_publish'] = false;
		}
	}
}

//============================================================
// 	SETTING THE STAGE
//============================================================

//if we are viewing categories, we want to be looking at the category_data list instead of the main module list
if ( $this->viewing_categories() )
{
	$base_arr = $this->module_arr['category_data']['list_data'];
}
else
{
	$base_arr = $this->module_arr['list_data'];
}

//============================================================
//		DISPLAY CATEGORY TITLE IF WE HAVE ONE
//============================================================
if ( $_REQUEST['category_id'] != '' && $_REQUEST['nt'] == '' )
{
	$query_str = "SELECT * FROM " . $this->module_arr['category_data']['form_dest_table'] . " WHERE " . $this->module_arr['category_data']['form_prefix']."_id = " . $_REQUEST['category_id'];
	$db = new db($this->module_arr['db']);
	$results = $db->qquery($query_str);
	?><span style="font-weight: bold; font-size: 135%"><?=$results[0][$this->module_arr['category_data']['form_prefix']."_".$this->module_arr['category_data']['title_field']]?></span>&nbsp;&nbsp;&nbsp;&nbsp;<a href="./?admin_action=<?=$this->module_arr['admin_action']?>">Back To <?=$this->module_arr['category_data']['category_title']?></span><br /><br /><?php
}


//=====================================
//		ACCOUNT FOR PUBLISHING
//=====================================
if ( $this->module_arr['publish'] )
{
	$this->module_arr['form_dest_table'] = $this->module_arr['form_dest_table']."_edited";
}


//============================================================
//   FINDING THE SORT FIELD
//============================================================

// if reorder = true, we will sort by that	
if ( $this->module_arr['reorder'] )
{
	if ( is_array($this->module_arr['reorder']) && $this->module_arr['reorder'][1] != '' ){
		$order_by = $this->module_arr['form_prefix'].'_'.$this->module_arr['reorder'][1].','.$this->module_arr['form_prefix']."_order";
	}
	else{
		$order_by = $this->module_arr['form_prefix']."_order";
	}
}
//if we are sorting on a standard field, we'll go ahead and account for those here
elseif ( $base_arr['order_by'] == 'id DESC' || $base_arr['order_by'] == 'id ASC' || $base_arr['order_by'] == 'insert_datetime DESC' || $base_arr['order_by'] == 'insert_datetime ASC')
{
	$order_by= $base_arr['order_by'];
}
//everything else is pretty standard if it has a value
elseif ( $base_arr['order_by'] != '' )
{
	if ( strpos($base_arr['order_by'],',') !== false )
	{
		$order_by_exp = explode(',',$base_arr['order_by']);
		foreach ($order_by_exp as $value)
		{
			$order_by .= $this->module_arr['form_prefix']."_".trim($value).", ";
		}
		
		$order_by = substr($order_by, 0, -2);
	}
	else
	{
		$order_by = $this->module_arr['form_prefix']."_".$base_arr['order_by'];
	}
}



//============================================================
// 	RUN THE QUERY
//============================================================
if ( $_REQUEST['category_id'] != '' )
{
	$query_str = "SELECT * FROM ".$this->module_arr['form_dest_table']." WHERE " . $this->module_arr['form_prefix'].'_'.$this->module_arr['category_data']['form_prefix'] . " = " . $_REQUEST['category_id'];
}
else
{
	$query_str = "SELECT * FROM ".$this->module_arr['form_dest_table'];
	
	if ( $_REQUEST['filter'] != '' )
	{
		$query_str .= " WHERE " . $this->module_arr['form_prefix']."_".$base_arr['filter_by'] . " = '" . $_REQUEST['filter'] . "'";
	}
}

//sort field is set just above this
if ( $order_by != '' )
{
	$query_str .= " ORDER BY ".$order_by;
}

$db = new db($this->module_arr['db']);
$list = $db->qquery($query_str);

//if we are coming in from an ajax call, we don't want the javascript includes or the main block
if ( $_REQUEST['nt'] == '' )
{
?>
	<script language="javascript" type="text/javascript" src="/cms/includes/js_libraries/jquery/addons/ui/ui.core.js"></script>
	<script language="javascript" type="text/javascript" src="/cms/includes/js_libraries/jquery/addons/ui/ui.sortable.js"></script>
	<div id="module_list">
<?php
}


//============================================================
// 	BACK TO PARENT BUTTON
//============================================================
if ( count($this->module_arr['parent']) > 0 )
{
	?>
		<div class='list_back_to_parent_container'>
			<a class='list_back_to_parent' href='./?admin_action=<?=$this->module_arr['parent']['admin_action']?>'>&laquo Back To <?=$this->module_arr['parent']['title']?></a>
		</div>
		<style type='text/css'>
			.list_back_to_parent_container{
				border: solid #CCC; 
				border-width: 1px 0px 1px 0px; 
				margin-bottom: 15px;
			}
			
			.list_back_to_parent{
				display: block;
				padding: 10px;
				background-color: #EEE;
				text-decoration: none;
				font-height: 110%;
			}
			
			.list_back_to_parent:hover{
				background-color: #F9F9F9;
			}
		</style>
	<?php
}


//============================================================
// 	CREATE AND RESORT BUTTONS
//============================================================
?>
<div id="main_module_buttons" style='position: relative;'>
<?php
//standard create button
//if we have a category id, we want to have that category already highlighted when we go into the form

if ( $_REQUEST['category_id'] != '' )
{
	$from_text = "&from_category=".$_REQUEST['category_id'];
}

	//============================================================
//		IF WE DON'T HAVE ANY CATEGORIES YET, LET'S NOT SHOW THE CREATE ITEM BUTTON
//============================================================
if ( $this->viewing_categories() )
{
	$query_str ="SELECT * FROM " . $this->module_arr['form_dest_table'];
	$db = new db($this->module_arr['db']);
	$results = $db->qquery($query_str);
	if ( count($results) > 0 )
	{
		$print_create_button = true;
	}
}
else
{
	$print_create_button = true;
}

if ( $print_create_button && $permissions['can_create'] )
{
	?>
	<a href='./?admin_action=<?=$this->module_arr['admin_action']?>&admin_subaction=edit<?=$from_text?>' class='list_button'>Create New</a>
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<?php
}
//create category button
if ( $this->viewing_categories() && $permissions['can_create'] )
{
	?>
	<a href='./?admin_action=<?=$this->module_arr['admin_action']?>&admin_subaction=edit&category_id=new' class='list_button'>Create New <?=$this->module_arr['category_data']['category_title']?></a>
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<?php
}
//reorder button
if ( $this->module_arr['reorder'] && count($list) != 0 && $permissions['can_reorder'] )
{
	?><a href="javascript:;" onclick="show_reorder()" class="list_button">Reorder List</a><?php
}
?>

<?php
//============================================================
// 	FILTER
//============================================================
if ( $base_arr['filter_by'] != '' )
{	
	?>
	<div style='position: absolute; right: 25px; top: 0px'>
		<form id='module_filter' action='./' method="GET">
			<input type='hidden' name='admin_action' value='<?=$this->module_arr['admin_action']?>'>
			<select name='filter' id='filter_select'>
				<option value=''>Show All Records</option>
				<?php
				$filter_field = $this->module_arr['form_prefix']."_".$base_arr['filter_by'];
				$query_str = "SELECT ". $filter_field ." FROM " . $this->module_arr['form_dest_table'] . ' GROUP BY ' . $filter_field;
				$db = new db;
				$results = $db->qquery($query_str);
				echo count($results).'results';
				for ($i=0; $i<count($results); $i++ )
				{
					?>
					<option value='<?=$results[$i][$filter_field]?>'>Show 
						<?php
							if ($relationships->has_relationship($base_arr['filter_by']) )
							{
								if ( is_null($results[$i][$filter_field]) )
								{
									echo "No Association";
								}
								else
								{
									echo stripslashes($relationships->get_match(array('local_name'=>$base_arr['filter_by'],'value'=>$results[$i][$filter_field])));
								}
							}
							else
							{
								echo $results[$i][$filter_field];
							}
						?>
					</option>
					<?php
				}
				?>
			</select>
			<script language="javascript">
				$().ready(function(){
					$("#filter_select").change(function(){
						if ( $("#filter_select").val() != '' || $("#filter_select").val() != '<?=$_REQUEST['filter']?>' )
						{
							$("#module_filter").submit();
						}
						
					});
					
					<?php
					if ($_REQUEST['filter'] != '' )
					{
						?>
						$("#filter_select").val('<?=$_REQUEST['filter']?>');
						<?php
					}
					?>
				})
			</script>
		</form>
	</div>
	<?php
}
?>
</div>

<?php
//============================================================
// 	SHOW DROP DOWN LIST OF WHAT TO VIEW IF WE HAVE EXPIRING CONTENT
//============================================================
if ( $this->module_arr['content_expiration_date'] === true || $this->module_arr['content_publish_date'] === true )
{
	?>
		<div style="float: right; padding-right: 35px;">
		<strong>Show All Records That</strong>
		<select id="show_results">
			<option value="all_but_expired">Aren't Expired</option>
			<option value="live_content">Are Live (unless never published)</option>
			<option value="future_content">Have Future Publish Dates</option>
			<option value="expired_content">Are Expired</option>
			<option value="no_exp_date">Have No Expiration Date</option>
			<option value="no_pub_date">Have No Publish Date</option>
			<option value="all">All</option>
		</select>
		</div>
		
		<script language="javascript">
			$().ready(function(){
				$("#show_results").change(function(){
					cur_val = $("#show_results").val();
					
					$('.list_item').hide();
					
					if ( cur_val == 'all_but_expired' )
					{
						//============================================================
						// Show Live Content
						//============================================================
						live_content = $(".live_content");
						for (i=0; i<live_content.length; i++ )
						{
							cur_id = $(live_content[i]).attr('id').replace('class_holder_','');
							$("#tbody_"+cur_id).show()
						}
						
						//============================================================
						// 	Show Future Content
						//============================================================
						future_content = $(".future_content");
						for (i=0; i<future_content.length; i++ )
						{
							cur_id = $(future_content[i]).attr('id').replace('class_holder_','');
							$("#tbody_"+cur_id).show()
						}
					}
					else if ( cur_val == 'all' )
					{
						$('.list_item').show();
					}
					else
					{
						new_list = ($("."+cur_val));
						for (i=0; i<new_list.length; i++ )
						{
							cur_id = $(new_list[i]).attr('id').replace('class_holder_','');
							$("#tbody_"+cur_id).show();
						}
							
					}
					
				})
			})
		</script>
	<?php
}
?>


<div style="clear: both"></div>
<?php



//============================================================
// 	SHOW THE RECORDS
//============================================================		
if ( count($list) == 0 )
{
	?>
	<br /><h2>There are currently no entries</h2><?php
}
else
{
	//now we list the main stuff
	//SHOWN REGARDLESS OF AJAX
	?>
		<div id="module_list_table">
			<?php
			
			//============================================================
			// 	BEGIN MAIN LIST
			//============================================================
			?><br />
			<table border="0" cellspacing="0" cellpadding="5" width="100%">
				<tr>
					<?php
					for ( $i=0; $i<count($base_arr); $i++ )
					{
						?>
						<td class="list_head"><?=$base_arr[$i]['display_name']?>&nbsp;</td>
						<?php
					}
					?>
					<td colspan="2" class="list_head">Actions&nbsp;</td>
				</tr>
			<?php
			for ( $i=0; $i<count($list); $i++ )
			{
				if ( $this->viewing_categories() )
				{
					$list[$i]['id'] = $list[$i][$this->module_arr['form_prefix'].'_id'];
				}
				
				if ( $this->viewing_categories() )
				{
					$id = $list[$i][$this->module_arr['form_prefix'].'_id'];
					$cat_prefix = 'category_';
				}
				elseif ($this->module_arr['publish'] )
				{
					$id = $list[$i]['record_id'];
					$cat_prefix = '';
				}
				else
				{
					$id = $list[$i]['id'];
					$cat_prefix = '';
				}
			
				?>
					<tbody id="tbody_<?=$id?>" class='list_item'>
					<tr>
						<?php
						for ( $j=0; $j<count($base_arr); $j++ )
						{
							//find fields not specific to tool and leave out prefix
							if ( $base_arr[$j]['field'] == 'insert_datetime' || $base_arr[$j]['field'] == 'update_datetime' ||  $base_arr[$j]['field'] == 'id' || $base_arr[$j]['field'] == 'content_publish_date' || $base_arr[$j]['field'] == 'content_expiration_date' )
							{
								$value = $list[$i][$base_arr[$j]['field']];
							}
							elseif ( $base_arr[$j]['field'] == 'count' ) //if we want the count inside the categories, put it here
							{
								$query_str = "SELECT * FROM " . $this->module_arr['orig_table'] . " WHERE " . $this->module_arr['orig_prefix'] . "_" . $base_arr[$j]['assoc_field'] . " = " . $list[$i]['id'];
								$db = new db($this->module_arr['db']);
								$results = $db->qquery($query_str);
								$value = count($results);
								?><div id="count_items_<?=$list[$i]['id']?>" style="width: 1px; height: 1px; visibility: hidden"><?=$value?></div><?php
							}
							//if we are supposed to grab an associative value from another table, do it now
							elseif ( $base_arr[$j]['assoc_table'] != '' )
							{
								$query_str = "SELECT * FROM " . $base_arr[$j]['assoc_table'] . " WHERE " . $base_arr[$j]['assoc_field'] . " = " .  $list[$i][$this->module_arr['form_prefix']."_".$base_arr[$j]['field']];
								$assoc_results = $db->qquery($query_str);
								$value = $assoc_results[0][$base_arr[$j]['assoc_display_field']];
							}
							else
							{
								$value = $list[$i][$this->module_arr['form_prefix'].'_'.$base_arr[$j]['field']];
								
								if ( $relationships->has_relationship($base_arr[$j]['field']) )
								{
									$value = stripslashes($relationships->get_match(array('local_name'=>$base_arr[$j]['field'],'value'=>$value)));
								}
							}
							
							$type = $base_arr[$j]['type'];

							if ( $type == 'datetime' ){
								if ( $value == '0000-00-00 00:00:00' )
								{
									$value = '';
								}
								else
								{
									$value = date("M j, Y g:ia",strtotime($value));
								}
							}
							elseif ( $type =='date' ){
								if ( $value == '0000-00-00 00:00:00' || $value == '0000-00-00' )
								{
									$value = '';
								}
								else
								{
									$value = date("M j, Y",strtotime($value));
								}
							}	
							elseif ( $type == 'bool' ){
								$value = 'No';
								if ( $value == 1 ){
									$value = 'Yes';
								}
							}
							?>
							<td class="list_td"><?=stripslashes($value)?>&nbsp;</td>
							<?php
						}
						
						
						//============================================================
						//  LIST BUTTONS
						//============================================================
						?>
						<td class="list_td" style='text-align: right' NOWRAP>
						<?php
						if ( $this->viewing_categories() )
						{
						?>
						<a href="./?admin_action=<?=$this->module_arr['admin_action']?>&category_id=<?=$id?>" class="list_button">View</a>
						&nbsp;&nbsp;
						<?php
						}
						if ( is_array($base_arr['action_buttons']) )
						{
							foreach ($base_arr['action_buttons'] as $button)
							{
								if ( $button['action'] != '' )
								{
									$link = "./?admin_action=".$this->module_arr['admin_action']."&admin_subaction=action&action=".$button['action']."&id=".$id;
								}
								else
								{
									$link = 'javascript:;';
								}
								
								$class='';
								if ( $button['class'] != '' )
								{
									$class = ' '.$button['class'];
								}
								
								?>
								<a href="<?=$link?>" class="list_button<?=$class?>" id='<?=$button['id_prefix']?><?=$id?>'><?=$button['text']?></a><?php // $from_text is created earlier in this function?>
								&nbsp;&nbsp;
								<?php
							}
						}
						if ( $this->module_arr['publish'] )
						{
							if ( !$this->is_published($list[$i]['record_id']) && $permissions['can_publish'] )
							{
								?>
								<a href="./?admin_action=<?=$this->module_arr['admin_action']?>&admin_subaction=publish&id=<?=$list[$i]['record_id']?>" class="list_button">Publish</a><?php // $from_text is created earlier in this function?>
								&nbsp;&nbsp;
								<?php
							}
						}
						?>
						<?php
						if (  $permissions['can_edit'] )
						{
						?>
						<a href="./?admin_action=<?=$this->module_arr['admin_action']?>&admin_subaction=edit&<?=$cat_prefix?>id=<?=$id?><?=$from_text?>" class="list_button">Edit</a><?php // $from_text is created earlier in this function?>
						&nbsp;&nbsp;
						<?php
						}
						if ( $permissions['can_delete'] )
						{
						?>
						<span id="delete_button_<?=$id?>"><a id="delete_<?=$id?>" href="javascript:;" class="list_button delete">Delete</a></span></td>
						<?php
						}
						?>
					</tr>
				</tbody>
				<?php
				
				//============================================================
				// 	Okay, this is going to be interesting
				//		My idea is to have a hidden div with a number of classes attached to it that will tell us whether
				//		the item is current, expired, a future item or doesn't have any dates at all.
				//
				// 	We will only run this if and when we have content expiration or content publish dates
				//
				//		The list display will then be controlled by javascript which will hide and show certain rows
				//		based on this date criteria
				//============================================================
				if ( $this->module_arr['content_expiration_date'] === true || $this->module_arr['content_publish_date'] === true )
				{
					$classes = '';
					
					$exp_date = $list[$i]['content_expiration_date'];
					$pub_date = $list[$i]['content_publish_date'];
					
					//if the expiration date has passed, it's expired
					if ( strtotime($exp_date) < time() && date('Y',strtotime($exp_date)) != '1969' ){$classes .= "expired_content ";}
					
					//if there is no exp-date
					if ( date('Y',strtotime($exp_date)) == '1969' ){$classes .= "no_exp_date "; }
					
					//if there is no pub date
					if ( date('Y',strtotime($pub_date)) == '1969' ){$classes .= "no_pub_date "; }
					
					//if the publish date hasn't come yet, 
					if ( strtotime($pub_date) > time() ){ $classes .= "future_content "; }
					
					//if we are live, baby!
					if ( ( date('Y',strtotime($pub_date)) == '1969' || strtotime($pub_date) < time() ) && ( strtotime($exp_date) > time() || date('Y',strtotime($exp_date)) == '1969')){ $classes .= "live_content "; }
					
					$classes = substr($classes, 0, -1);
					
					?>
					<div class="<?=$classes?>" id="class_holder_<?=$id?>" style="display: none"></div>
					<?php
				}
			}
			?>
			</table>
		</div>
		<?php
		//************************************************************************************
		// END MAIN LIST				************************************************************
		// BEGIN REORDER LIST		************************************************************
		//************************************************************************************
		if ( $this->module_arr['reorder'] )
		{
		?>
		<div id="module_list_list" style="display: none">
			<a href="javascript:;" onclick="save_reorder()" class="list_button">Save Changes</a> or
			<a href="javascript:;" onclick="cancel_reorder()">Cancel Reorder</a>
			<br /><br />
			<ul id="module_list_ul">

				<?php
				for ( $i=0; $i<count($list); $i++ )
				{
					if ( $this->viewing_categories() )
					{
						$list[$i]['id'] = $list[$i][$this->module_arr['form_prefix'].'_id'];
					}
					elseif ( $this->module_arr['publish'] )
					{
						$list[$i]['id'] = $list[$i]['record_id'];
					}
					
					?>
						<li id="item_<?=$list[$i]['id']?>">
							<?php
							
							$already_started= 0;
							for ( $j=0; $j<count($base_arr); $j++ )
							{
								
								if ( $base_arr[$j]['display_name'] != '' && $base_arr[$j]['field'] != 'count')
								{	
									//find fields not specific to tool and leave out prefix
									if ( $base_arr[$j]['field'] == 'insert_datetime' || $base_arr[$j]['field'] == 'update_datetime' ||  $base_arr[$j]['field'] == 'id')
									{
										$value = $list[$i][$base_arr[$j]['field']];
									}
									else
									{
										//find value
										$value = $list[$i][$this->module_arr['form_prefix'].'_'.$base_arr[$j]['field']];
									}
									
									$type = $base_arr[$j]['type'];
		
									if ( $type == 'datetime' ){
										$value = date("M j, Y g:ia",strtotime($value));
									}
									elseif ( $type =='date' ){
										$value = date("M j, Y",strtotime($value));
									}	
								}
								else
								{
									$value = '';
								}
								
								if ( $value != '' )
								{
									
									if ( $already_started == 0 )
									{
										$already_started = 1;
									}
									else
									{
										echo " - ";
									}
									echo stripslashes($value);
								}
							}
							?>
						</li>
					<?php
				}
				?>
			</ul>
		</div>
		<script language="javascript">
			//start sort
			$(document).ready(function(){
				$("#module_list_ul").sortable(); 
			})
		</script>
		<?php
		}
		
		//============================================================
		//		END REORDER LIST
		//============================================================
		
		
		
		//============================================================
		//  REORDER JAVASCRIPT CODE
		//============================================================
		//more reorder stuff we only want the first time around..... not after an ajax call
		if ( $_REQUEST['nt'] == '' && $this->module_arr['reorder'] )
		{
		?>
		</div>
		<script language="javascript">
			function show_reorder()
			{
				$("#module_list_table").hide(200);
				$("#main_module_buttons").hide();
				$("#module_list_list").show(200);
			}
			
			function cancel_reorder()
			{
				$("#module_list_table").show(200);
				$("#main_module_buttons").show();
				$("#module_list_list").hide(200);
			}
			
			function save_reorder()
			{
				var list_arr = new Array();
				list_arr = $('#module_list_ul').sortable('serialize');
				$("#module_list_list").fadeOut(200,function(){
					$("#module_list_list").html("</strong>Saving Changes...</strong><img src='/cms/images/loader.gif'>").show();
					setTimeout(function(){
						$.ajax({
							type: "POST",
							<?php
							if ( $this->viewing_categories() )
							{
								$add_to_request_str = "&categories=true";
							}
							elseif ( $this->module_arr['categories'] )
							{
								$add_to_request_str = "&category_id=".$_REQUEST['category_id'];
							}
							?>
							url: "./?admin_action=<?=$this->module_arr['admin_action']?>&admin_subaction=reorder&nt=1<?=$add_to_request_str?>",
							data: list_arr,
							success: function(msg){
								$("#module_list_list").html("<strong>Changes Saved!</strong><br />Refreshing list... <img src='/cms/images/loader.gif'>");
								
								setTimeout(function(){
									$("#module_list").hide(200, function(){
										$("#module_list").html(msg).show(100);
									});
								}, 1000);
							}
						});
					}, 1000);
				});
			}
		</script>
	<?php
	}
	?>
		<script language="javascript">
				$(".delete").click(function(){
					id = $(this).attr("id").replace("delete_",'');
					warning_addon = '';
					<?php
					if ( $this->viewing_categories() )
					{
						?>
						count = $("#count_items_"+id).html();
						if ( count > 0 )
						{
							if ( !(confirm("This item is a folder containing "+count+" items. Are you sure you want to continue?") ) )
							{
								return false;
							}
							else
							{
								warning_addon = ' and all of its contents';
							}
						}
						<?php
					}
					?>
					conf = confirm('Are you sure you want to delete this entry'+warning_addon+'?\nWARNING: This cannot be undone.');					
					if ( conf ){
						$("#delete_button_"+id).html("Deleting... <img src='/cms/images/loader.gif' height='14'>");
						$.ajax({
							type: "GET",
							url: "./?admin_action=<?=$this->module_arr['admin_action']?>&admin_subaction=delete&nt=1",
							<?php
							if ( $this->viewing_categories() )
							{
							?>
							data:	"category_id="+id,
							<?php
							}
							else
							{
							?>
							data:	"id="+id,
							<?php
							}
							?>
							success: function(msg){
								$("#tbody_"+id).fadeOut(500);
							}
						});
					}
				});
			</script>
	<?php
}

//============================================================
// 	RUN after_list()
//============================================================
if ( method_exists($module_actions,'after_list') )
{
	$module_actions->after_list();
}


//============================================================
// 	}
//============================================================
?>
<script language="javascript">
	$().ready(function(){
		$("#tool_title").html('<?php global $admin_page_title; echo $admin_page_title?>').show();
	})
</script>