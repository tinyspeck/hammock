<?php
	$dir = dirname(__FILE__);
	include("$dir/lib/init.php");


	# log request

	$req = array(
		'auth_user'	=> $_SERVER['PHP_AUTH_USER'],
		'auth_pass'	=> $_SERVER['PHP_AUTH_PW'],
		'get'		=> $_GET,
		'post'		=> $_POST,
	);

	$log = SLACKWARE_ROOT.'/data/hook_'.uniqid().'.log';
	$fh = fopen($log, 'w');
	fwrite($fh, json_encode($data));
	fclose($fh);


	# see if we can find a plugin to handle it

	load_plugins();
	load_data();

	$instance = getPluginInstance($_GET['id']);
	if (is_object($instance)){
		$instance->onHook();
	}	

	echo "ok\n";
