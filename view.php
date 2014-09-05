<?php
	$dir = dirname(__FILE__);
	include("$dir/lib/init.php");
	
	verify_auth();

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
	$smarty->assign('id', $instance->id);
	$smarty->assign('name', $instance::NAME);
	$smarty->assign('icon', $instance->iconUrl(128, "service", true));
	$smarty->assign('asset', $instance->getAssets());

	$editpage = render_edit($GLOBALS['cfg']['team'], $instance);
	$smarty->assign('html', $editpage);

	$smarty->display('page_view.txt');

