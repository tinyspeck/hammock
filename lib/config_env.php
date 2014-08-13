<?php
	$cfg = array();

	# This ENV var must point to your app's root

	$cfg['root_url'] = $_ENV['HAMMOCK_ROOT'];

	if (!strlen($cfg['root_url'])) die("No HAMMOCK_ROOT set");


	# OAuth config

	$cfg['client_id']	= $_ENV['HAMMOCK_CLIENT_ID'];
	$cfg['client_secret']	= $_ENV['HAMMOCK_CLIENT_SECRET'];


	# Defaults

	$url_bits = parse_url($cfg['root_url']);

	$cfg['cookie_domain']	= $url_bits['host'];
	error_log($cfg['cookie_domain']);
	$cfg['cookie_path']	= $url_bits['path'];
	$cfg['cookie_name']	= 'hammock-auth';
	$cfg['slack_root']	= "https://slack.com/";

	# Heroku specific

	if ($_ENV['REDISTOGO_URL']){
		$cfg['data_provider'] = 'redis';
		$cfg['redis_url'] = $_ENV['REDISTOGO_URL'];
	}


	# Allow some settings to be overridden

	$allow_override = array(
		'cookie_domain',
		'cookie_path',
		'cookie_name',
		'slack_root',
	);

	foreach ($allow_override as $opt){
		$opte = 'HAMMOCK_'.strtoupper($opt);
		if (isset($_ENV[$opte])) $cfg[$opt] = $_ENV[$opte];
	}
