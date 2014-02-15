<?php
	$dir = dirname(__FILE__);
	include("$dir/lib/init.php");

	verify_auth();

	load_plugins();


	$services = array();
	$set = array();

	foreach (array_keys($plugins_services) as $k){
		$temp = new $k();
		$temp->id = $k;
		$set[] = $temp;
		if (count($set) == 3){ $services[] = $set; $set = array(); }
	}
	if (count($set)) $services[] = $set;

	$smarty->assign('services', $services);

	$auth = array();
	foreach (array_keys($plugins_auth) as $k){
		$temp = new $k();
		$temp->id = $k;
		$auth[] = $temp;
	}
	$smarty->assign('auth', $auth);


	$smarty->display('page_new.txt');
