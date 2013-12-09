<?php
	$dir = dirname(__FILE__);
	include("$dir/lib/init.php");

	echo "<h1>Slackware</h1>";

	load_plugins();
	dumper($plugins);
