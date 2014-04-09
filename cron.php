<?php
	/**
	 * To use Cron: 
	 * 1. Add something like this to your plugin, probably in onInit():
	 *    ` $this->icfg['cron'] = true; `
	 * 2. Give the user a way to specify a cron schedule, and make sure it 
	 *    gets formatted as a cron expression: 
	 *    ` $this->icfg['cron_interval'] = '0,30 * * * *'; `
   *
	 * You could also bake the desired cron schedule into your code.
   *
	 * For best results, write your own cron job to request this file as often as 
	 * you think is necessary. This will automatically create cron jobs as you add
	 * new instances.
	 *
	 **/

	// Bootstrap Hammock
	$dir = dirname(__FILE__);
	include("$dir/lib/init.php");

	$instances = $GLOBALS['data']->get_all('instances');
	$pathToCurl = str_replace("\n", "", `which curl`);

	// Generate a block of cron jobs with delimeters at both ends
	$cronOutput .= PHP_EOL . '## START HAMMOCK CRON ##' . PHP_EOL;
	foreach($instances as $instanceId => $instance) {
		if ($instance['cron'] === true) {
			// TODO: use the built-in getHookUrl method.
			$hookUrl = $GLOBALS['cfg']['root_url'] . 'hook.php?id=' . $instanceId . '&token=' . $instance['token'];

	   	$cronOutput .= sprintf(
	   		'%s %s "%s"',
	   		$instance['cron_interval'],
	   		$pathToCurl,
	   		$hookUrl
	 		);
	 		$cronOutput .= PHP_EOL;
		}
	}
	$cronOutput .= '## END HAMMOCK CRON ##' . PHP_EOL;

	// Put the current crontab in a file, or create a blank file.
	$temporaryCronFile = sys_get_temp_dir() . '/hammock-cron';
	$cronList = shell_exec('crontab -l');
	if($cronList) {
		file_put_contents($temporaryCronFile, $cronList);
	}
	else {
		file_put_contents($temporaryCronFile, '');
	}

	// replace the existing block if present, otherwise append.
	if ( strpos($cronList, '## START HAMMOCK CRON ##') !== false ) {
		$cronList = preg_replace('/[\r\n]## START HAMMOCK CRON ##.*?## END HAMMOCK CRON ##[\r\n]/sim', $cronOutput, $cronList);	
	}
	else {
		$cronList .= $cronOutput;
	}

	// Write our new cron to a file and then load it into crontab.
	file_put_contents($temporaryCronFile, $cronList);
	shell_exec('crontab ' . $temporaryCronFile);
	unlink($temporaryCronFile);

	header('HTTP/1.0 200 OK');
?>