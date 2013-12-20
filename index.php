<?php
	$dir = dirname(__FILE__);
	include("$dir/lib/init.php");

	load_plugins();
	load_data();


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

	$instances = array();
	foreach ($data['instances'] as $k => $instance){
		$instances[] = getPluginInstance($k);
	}
	$smarty->assign('instances', $instances);


	$oauth_url = "https://dev.slack.com/oauth/authorize";
	$oauth_url .= "?client_id=".$cfg['client_id'];
	$oauth_url .= "&redirect_uri={$cfg['root_url']}oauth.php";
	$smarty->assign('oauth_url', $oauth_url);

	$smarty->display('page_index.txt');
