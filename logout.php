<?php
	$dir = dirname(__FILE__);
	include("$dir/lib/init.php");


	$expire = time();

	setcookie($cfg['cookie_name'], '', $expire, $cfg['cookie_path'], $cfg['cookie_domain']);

	header("location: ./");
	exit;
