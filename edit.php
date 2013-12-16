<?php
	$dir = dirname(__FILE__);
	include("$dir/lib/init.php");

	load_plugins();
	load_data();

	$uid = $_GET['id'];
	if (!isset($data['instances'][$uid])) die("instance not found");

	$instance_cfg = $data['instances'][$uid];
	$instance = new $instance_cfg['plugin'];
	$instance->setConfig($uid, $instance_cfg);
?>

<h1>Slackware</h1>

<b>Edit service</b>

<?php dumper($instance); ?>
