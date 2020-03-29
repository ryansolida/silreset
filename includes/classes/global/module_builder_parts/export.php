<?php
require_once('module_data.php');

class module_data_export extends module_data
{
	function module_data_export($module_name='')
	{
		if ( $module_name != '' )
		{
			$this->module_name = $module_name;
		}
	}
	
	function get_rss()
	{
		$module_data = new module_data($this->module_name);
		$this->build_query_from_request();
		$records = $module_data->get_entries($this->entries_arr);
		$records_count = count($records);
		echo '<?xml version="1.0" encoding="UTF-8"?>
		';
		?>
		<rss version="2.0">
			<channel>
				<title><?=$_REQUEST['title']?></title>
				<description><![CDATA[<?=$_REQUEST['description']?>]]></description>
				<link></link>
				<lastBuildDate><?=date("D, j M Y H:i:s")?> -0400</lastBuildDate>
				<pubDate><?=date("D, j M Y H:i:s")?> -0400</pubDate>
		<?php
		for ( $i=0; $i<$records_count; $i++ )
		{
			?>
			<item>
				
			<?php
			$standard_fields = array('title','pubDate','link','description');
			foreach ($standard_fields as $field)
			{
				if ( $field != 'pubDate' )
				{
					if ( $_REQUEST[$field."field"] != '' )
					{
						?><<?=$field?>><![CDATA[<?php
							$contentpieces = explode('//',$_REQUEST[$field."field"]);
							foreach($contentpieces as $piece)
							{
								if ( $piece[0] == '<' )
								{
									echo str_replace('<>','',$piece);
								}
								else
								{
									echo stripslashes($records[$i][$piece]);
								}
							}
							?>]]></<?=$field?>><?php
					}
				}
				//pubDate
				else
				{
					?><pubDate><?=date("D, j M Y H:i:s"	,strtotime($records[$i]['update_datetime']))?> -0400</pubDate><?php
				}
			}
			?>
			</item>
		<?php
		}
		?>
			</channel>
		</rss>
		<?php
	}
	
	function get_xml()
	{
		$module_data = new module_data($this->module_name);
		$this->build_query_from_request();
		$records = $module_data->get_entries($this->entries_arr);
		$records_count = count($records);
		echo '<?xml version="1.0" encoding="UTF-8"?>';
		
		?>
		<records>
		<?php
		for ( $i=0; $i<$records_count; $i++ )
		{
			?>
			<record>
				<?php
				foreach ($records[$i] as $key=>$value)
				{
					?>
					<<?=$key?>><?php
						if ( $this->is_standard_field($key) )
						{
							echo $value;
						}
						else
						{
							?><![CDATA[<?=stripslashes($value)?>]]><?php
						}
					?></<?=$key?>>
					<?php
				}
				?>
			</record>		
		<?php
		}
		?>
		</records>
		<?php
	}
	
	function build_query_from_request()
	{
		//============================================================
		// 	This will be built mainly on request variables so here we go...
		//============================================================
		$this->entries_arr = array();
		
		if ( $_REQUEST['order_by'] != '' ){$this->entries_arr['order_by'] = $_REQUEST['order_by'];}
		if ( $_REQUEST['limit'] != '' ){$this->entries_arr['limit'] = $_REQUEST['limit'];}
		if ($_REQUEST['include_fields'] != '' ){$this->entries_arr['include_fields'] = $_REQUEST['include_fields'];}
	}
}
?>