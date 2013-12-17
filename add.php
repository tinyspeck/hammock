<?php
	$dir = dirname(__FILE__);
	include("$dir/lib/init.php");

	load_plugins();


	if ($_POST['done']){
		$uid = $_POST['uid'];
		$plugin = $_POST['plugin'];

		load_data();
		$data['instances'][$uid] = array();
		$data['instances'][$uid]['plugin'] = $plugin;
		save_data();

		header("location: edit.php?id={$uid}");
		exit;
	}


	$id = $_GET['id'];
	if (!isset($plugins[$id])) die("plugin not found");

	$instance = createPluginInstance($id);
	$instance->createInstanceId();

	$instance->checkRequirements();

	$smarty->assign('instance', $instance);

	$smarty->display('page_add.txt');
