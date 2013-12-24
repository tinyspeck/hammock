<?php
	$dir = dirname(__FILE__);
	include("$dir/lib/init.php");

	verify_auth();

	load_plugins();


	$services = array();
	foreach (array_keys($plugins_services) as $k){
		$temp = new $k();
		$temp->id = $k;
		$services[] = $temp;
	}
	$smarty->assign('services', $services);

	$auth = array();
	foreach (array_keys($plugins_auth) as $k){
		$temp = new $k();
		$temp->id = $k;
		$auth[] = $temp;
	}
	$smarty->assign('auth', $auth);

	$instance_data = $data->get_all('instances');
	$instances = array();
	foreach ($instance_data as $k => $instance){
		$instances[] = getPluginInstance($k);
	}
	$smarty->assign('instances', $instances);


	$smarty->display('page_index.txt');
