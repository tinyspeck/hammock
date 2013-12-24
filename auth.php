<?php
	$dir = dirname(__FILE__);
	include("$dir/lib/init.php");

	load_plugins();

	$instance = getAuthPlugin($_GET['id']);
	if (!is_object($instance)) die("instance not found");


	$html = $instance->configPage();

	$smarty->assign('html', $html);
	$smarty->assign('instance', $instance);

	$smarty->display('page_auth.txt');
