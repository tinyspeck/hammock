<?php
	$dir = dirname(__FILE__);
	include("$dir/lib/init.php");

	load_plugins();


	if ($_POST['done']){

		$instance = createPluginInstance($_POST['plugin']);
		$instance->iid = $_POST['uid'];

		$instance->onParentInit();
		$instance->onInit();

		$instance->saveConfig();

		header("location: edit.php?id={$instance->iid}");
		exit;
	}


	$id = $_GET['id'];
	if (!isset($plugins[$id])) die("plugin not found");

	$instance = createPluginInstance($id);
	$instance->createInstanceId();

	$instance->checkRequirements();

	$smarty->assign('instance', $instance);

	$smarty->display('page_add.txt');
