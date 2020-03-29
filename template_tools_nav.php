<?php
if ( $_REQUEST['admin_action'] == 'pages' ){
	$active = 'pages';
}
elseif ( $_REQUEST['admin_action'] == 'groups' || $_REQUEST['admin_action'] == 'users' ){
	$active = 'users';
}
else{
	$active = 'tools';
}
?>
<li><a href="/admin/?admin_action=pages" class="main_menu_link <?=$active=='pages'?'active_tab':'hover1'?>">Pages</a></li>
<?php

require_once(THE_GUTS_DIR.'/includes/classes/private/modules.php');
$modules_class = new modules;
$modules = $modules_class->get_modules();
if ( count($modules) > 0 ){
	?>
	<li class="menu_parent">
		<a href="javascript:;" class="main_menu_link <?=$active=='tools'?'active_tab':'hover1'?>" id="modules_show">Tools</a>
		<ul class="drop_level1 shadow_dark<?=$active=='tools'?' active':''?>" id="modules_drop">
		<?php
		foreach ($modules as $top_module)
		{
			$link = '/admin/?admin_action='.$top_module['admin_action'];
			if ( $top_module['children'] && !$top_module['has_index'] ){
				$link = 'javascript:;';
			}
			
			if ( $User->user_can('see module '.$top_module['admin_action']) )
			{
				?>
				<li class='<?=$top_module['children']?'menu_subparent':''?>'>
					<a href="<?=$link?>" class="main_menu_link"><?=$top_module['title']?></a>
					<?php
					if ( $top_module['children'] ){
					?>
						<ul class="drop_level2">
						<?php
						foreach ($top_module['children'] as $child_module){
							if ( $User->user_can('see module '.$child_module['admin_action']) )
							{
							?>
								<li><a href="/admin/?admin_action=<?=$child_module['admin_action']?>" class="sub_menu_link"><?=$child_module['title']?>	</a></li>
							<?php
							}
						}
						?>
						</ul>
					<?php
					}
					?>
				</li>
				<?php
			}
		}
		?>
		</ul>
	</li>
	<?php
}

//============================================================
// 	USERS
//============================================================
//if the priv level is equal to or greater than 50 AND USER_SEATS is defined
// OR if we are coming in as a multisite admin
if ( ( $_SESSION['reset']['user_priv_level'] >= 50 && defined("CMS_USER_SEATS") ) || $_SESSION['reset']['user_priv_level'] >= 75 )
{
?>
		<li class='menu_parent'>
			<a href="/admin/?admin_action=users" id="users_show" class="<?=$active=='users'?'active_tab':'hover1'?>">Users</a>
			<?php
			if ( defined("CMS_GROUP_SEATS") )
			{
			?>	
			<ul class="drop_level1 border3<?=$active=='users'?' active':''?>">
				<li><a href="/admin/?admin_action=users" class="sub_menu_link">Users</a></li>
				<li><a href="/admin/?admin_action=groups" class="sub_menu_link">Groups</a></li>
			</ul>
			<?php
			}
			?>
		</li>
	<?php
}
?>
