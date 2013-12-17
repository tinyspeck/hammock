<?php
	$dir = dirname(__FILE__);
	include("$dir/lib/init.php");

	load_plugins();


	if ($_POST['done']){
		$uid = $_POST['uid'];
		$plugin = $_POST['plugin'];

		load_data();
		$data['instances'][$uid] = array();
		$data['instances'][$uid]['plugin'] = $plugin;
		save_data();

		header("location: edit.php?id={$uid}");
		exit;
	}


	$id = $_GET['id'];
	if (!isset($plugins[$id])) die("plugin not found");

	$instance = createPluginInstance($id);
	$instance->createInstanceId();

	$instance->checkRequirements();
?>

<h1>Slackware</h1>

<b>Add new service</b>

<?php $instance->dump(); ?>

<form action="add.php" method="post">
<input type="hidden" name="done" value="1" />
<input type="hidden" name="plugin" value="<?php echo $instance->id; ?>" />
<input type="hidden" name="uid" value="<?php echo $instance->iid; ?>" />

<input type="submit" value="Create" />
</form>

