<?php
	$dir = dirname(__FILE__);
	include("$dir/lib/init.php");

	load_plugins();
	load_data();

	$instance = getPluginInstance($_GET['id']);
	if (!is_object($instance)) die("instance not found");

	$instance->checkRequirements();
?>

<h1>Slackware</h1>

<b>Edit service</b>

<?php dumper($instance); ?>
<?php $instance->editConfig(); ?>
