<?php
	$dir = dirname(__FILE__);
	include("$dir/lib/init.php");


	# log request

	$req = array(
		'server'	=> $_SERVER,
		'get'		=> $_GET,
		'post'		=> $_POST,
	);

	$log = SLACKWARE_ROOT.'/data/hook_'.uniqid().'.log';
	$fh = fopen($log, 'w');
	fwrite($fh, '<'.'? $req = '.var_export($req, true).';');
	fclose($fh);


	# see if we can find a plugin to handle it

	load_plugins();

	$instance = getPluginInstance($_GET['id']);
	if (is_object($instance)){

		if ($instance->cfg['has_token']){
			if ($_GET['token'] != $instance->icfg['token']){
				echo "bad token\n";
				exit;
			}
		}

		$instance->onHook($req);
	}	

	echo "ok\n";
