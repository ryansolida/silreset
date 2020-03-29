<h2>Module List</h2>

<a href="./?admin_action=<?=$_REQUEST['admin_action']?>&admin_subaction=new_module">Create New Module</a>
<br /><br />
<?php
$modules = $mod->get_modules();
print_r($modules);
?>