<?php
	$dir = dirname(__FILE__);
	include("$dir/lib/init.php");

	$params = array(
		'client_id'	=> $cfg['client_id'],
		'client_secret'	=> $cfg['client_secret'],
		'code'		=> $_GET['code'],
		'redirect_uri'	=> "{$cfg['root_url']}oauth.php",
	);

	$url = "https://dev.slack.com/api/oauth.access";

	$ret = SlackHTTP::post($url, $params);

	dumper($ret);
