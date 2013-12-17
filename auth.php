<?php
	$dir = dirname(__FILE__);
	include("$dir/lib/init.php");

	load_plugins();
	load_data();

	$instance = getAuthPlugin($_GET['id']);
	if (!is_object($instance)) die("instance not found");

?>

<h1>Slackware</h1>

<h2>Auth Plugin - <?php echo $instance->name; ?></h2>

<?php $instance->configPage(); ?>
