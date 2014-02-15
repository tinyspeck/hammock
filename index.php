<?php
	$dir = dirname(__FILE__);
	include("$dir/lib/init.php");

	verify_auth();

	load_plugins();

	$instance_data = $data->get_all('instances');

	if (!count($instance_data)){
		header("location: new.php");
		exit;
	}

	$instances = array();
	foreach ($instance_data as $k => $instance){
		$instances[] = getPluginInstance($k);
	}
	$smarty->assign('instances', $instances);


	$smarty->display('page_index.txt');
