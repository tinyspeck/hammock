<?php
	$dir = dirname(__FILE__);
	include("$dir/lib/init.php");
	
	verify_auth();

	load_plugins();

	$id = $_GET['id'];

	if ($_POST['channel']){
		
		$instance = getPluginInstance($id);
		if (is_object($instance)){
			if ($_POST['channel'] != $instance->icfg['channel']) {
				$instance->icfg['channel'] 	= $_POST['channel'];
				$instance->icfg['channel_name'] = $instance->getChannelName($_POST['channel']);
			}
			if(($_POST['label']))    $instance->icfg['label']    = $_POST['label'];
			if(($_POST['bot_name'])) $instance->icfg['bot_name'] = $_POST['bot_name'];
			if(($_POST['bot_icon'])) $instance->icfg['bot_icon'] = $_POST['bot_icon'];
			$instance->saveConfig();
		
		} else {
			$instance = createPluginInstance($id);
			$instance->iid = $_POST['uid'];

			$instance->onParentInit();
			$instance->onInit();

			$instance->icfg['created'] 	= time();
			$instance->icfg['creator_id'] 	= $GLOBALS['cfg']['user']['user_id'];
			$instance->icfg['channel'] 	= $_POST['channel'];
			$instance->icfg['channel_name'] = $instance->getChannelName($_POST['channel']);
			$instance->saveConfig();
		}
		header("location: view.php?id={$instance->iid}");
		exit;
	}

	if (!isset($plugins[$id])) die("plugin not found");

	$instance = createPluginInstance($id);
	$instance->createInstanceId();

	$instance->checkRequirements();

	$smarty->assign('instance', $instance);

	$newpage = render_new($GLOBALS['cfg']['team'], $instance);

	$smarty->assign('html', $newpage);
	$smarty->assign('id', $id);
	$smarty->assign('p_name', $instance::NAME);
	$smarty->assign('p_desc', $instance::DESC);

	$smarty->display('new_plugin.txt');
