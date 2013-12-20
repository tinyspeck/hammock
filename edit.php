<?php
	$dir = dirname(__FILE__);
	include("$dir/lib/init.php");

	load_plugins();
	load_data();

	$instance = getPluginInstance($_GET['id']);
	if (!is_object($instance)) die("instance not found");

	$instance->checkRequirements();

	$smarty->assign('instance', $instance);
	$smarty->assign('html', $instance->onEdit());

	$smarty->display('page_edit.txt');

