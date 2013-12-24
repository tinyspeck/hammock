<?php
	$dir = dirname(__FILE__);
	include("$dir/lib/init.php");


	#
	# exchange the code for a token
	#

	$params = array(
		'client_id'	=> $cfg['client_id'],
		'client_secret'	=> $cfg['client_secret'],
		'code'		=> $_GET['code'],
		'redirect_uri'	=> "{$cfg['root_url']}oauth.php",
	);

	$url = $cfg['slack_root']."api/oauth.access";

	$ret = SlackHTTP::post($url, $params);

	if ($ret['ok'] && $ret['code'] == '200'){

		$obj = json_decode($ret['body'], true);
		$token = $obj['access_token'];

	}else{
		echo "problem with oauth.access call";
		dumper($ret);
		exit;
	}


	#
	# fetch user info
	#

	$url = $cfg['slack_root']."api/auth.test?token={$token}";
	$ret = SlackHTTP::get($url);

	if ($ret['ok'] && $ret['code'] == '200'){

		$obj = json_decode($ret['body'], true);

	}else{
		echo "problem with auth.test call";
		dumper($ret);
		exit;
	}

	$info = $obj;
	unset($info['ok']);

	$info['access_token'] = $token;
	$info['secret'] = substr(md5(rand()), 0, 10);

	$cookie = $info['user_id'].'-'.$info['secret'];
	$expire = time() + (365 * 24 * 60 * 60);

	setcookie($cfg['cookie_name'], $cookie, $expire, $cfg['cookie_path'], $cfg['cookie_domain']);

	load_data();
	$data['users'][$info['user_id']] = $info;

	# is this the first use?
	if (!$GLOBALS['data']['team']['id']){
		$GLOBALS['data']['team'] = array(
			'id'	=> $info['team_id'],
			'name'	=> $info['team'],
			'token'	=> $info['access_token'],
		);
	}

	save_data();


	header("location: ./");
	exit;
