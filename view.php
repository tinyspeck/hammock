<?php
	$dir = dirname(__FILE__);
	include("$dir/lib/init.php");

	load_plugins();

	$instance = getPluginInstance($_GET['id']);
	if (!is_object($instance)) die("instance not found");

	if ($_POST['delete-instance']){
		$instance->deleteMe();
		header("location: ./");
		exit;
	}

	if ($_POST['new-token']){
		$instance->regenToken();
		$instance->saveConfig();
		header("location: {$instance->getViewUrl()}&newtoken=1");
                exit;
	}

	$instance->checkRequirements();

	$smarty->assign('instance', $instance);
	$smarty->assign('html', $instance->onView());

	$smarty->display('page_view.txt');

