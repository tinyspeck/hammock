<?php
	$dir = dirname(__FILE__);
	include("$dir/lib/init.php");

	verify_auth();

	load_plugins();


	#
	# if we have no service instances, redirect to new.php
	#

	$instance_data = $data->get_all('instances');

	if (!count($instance_data)){
		header("location: new.php");
		exit;
	}


	#
	# get instances and group by service
	#

	$instance_groups = array();

	foreach ($instance_data as $k => $instance){
		$inst = getPluginInstance($k);
		$inst->icon_48 = $inst->iconUrl(48);

		if ($inst->icfg['creator_id']){
			$u = $GLOBALS['data']->get('users', $inst->icfg['creator_id']);
			$inst->icfg['creator_name'] = $u['user'];
			$inst->icfg['creator_url'] = "{$u['url']}team/{$u['user']}";
		}

		$instance_groups[$inst->id]['plugin'] = $inst;
		$instance_groups[$inst->id]['instances'][] = $inst;
	}

	usort($instance_groups, 'local_sort');

	function local_sort($a, $b){
		return strcasecmp($a['plugin']->name, $b['plugin']->name);
	}

	$smarty->assign('instances', $instance_groups);


	#
	# output
	#

	$smarty->display('page_index.txt');
