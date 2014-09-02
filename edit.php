<?php
	$dir = dirname(__FILE__);
	include("$dir/lib/init.php");
	
	verify_auth();

	load_plugins();

	$instance = getPluginInstance($_GET['id']);
	if (!is_object($instance)) die("instance not found");

	$type = $_GET['ref'];

	$instance->checkRequirements();
	$smarty->assign('instance', $instance);
	$smarty->assign('html', $instance->onEdit());

	$smarty->display('page_edit.txt');

	if ($_POST['channel']){
		$instance = createPluginInstance($id);
		$instance->iid = $_POST['uid'];

		$instance->onParentInit();
		$instance->onInit();

		$instance->icfg['created'] = time();
		$instance->icfg['creator_id'] = $GLOBALS['cfg']['user']['user_id'];
		$instance->icfg['channel'] = $_POST['channel'];
		$instance->saveConfig();

		header("location: view.php?id={$instance->iid}");
		exit;
	}