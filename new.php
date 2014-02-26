<?php
	$dir = dirname(__FILE__);
	include("$dir/lib/init.php");

	verify_auth();

	load_plugins();


	#
	# get list of services and sort them
	#

	$services = array();

	foreach (array_keys($plugins_services) as $k){
		$temp = new $k();
		$temp->id = $k;
		$services[] = $temp;
	}

	usort($services, 'local_sort');

	function local_sort($a, $b){
		return strcasecmp($a->name, $b->name);
	}

	$smarty->assign('services', split_sets($services, 3));


	#
	# load auth services
	#

	$auth = array();
	foreach (array_keys($plugins_auth) as $k){
		$temp = new $k();
		$temp->id = $k;
		$auth[] = $temp;
	}
	$smarty->assign('auth', $auth);


	#
	# output
	#

	$smarty->display('page_new.txt');
