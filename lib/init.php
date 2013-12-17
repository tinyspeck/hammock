<?php
	$dir = dirname(__FILE__);
	include("{$dir}/config.php");
	include("{$dir}/service.php");

	function load_plugins(){

		$GLOBALS['plugins'] = array();

		$dir = dirname(__FILE__)."/../plugins";

		if ($dh = opendir($dir)){
			while (($file = readdir($dh)) !== false){

				if (is_dir("{$dir}/{$file}") && is_file("{$dir}/{$file}/plugin.php")){

					if ((include("{$dir}/{$file}/plugin.php"))){

						$GLOBALS['plugins'][$file] = createPluginInstance($file);
					}
				}
			}
			closedir($dh);
		}

		$GLOBALS['plugins_services'] = array();
		$GLOBALS['plugins_auth'    ] = array();

		foreach ($GLOBALS['plugins'] as $k => $v){
			if (is_a($v, 'SlackServicePlugin')) $GLOBALS['plugins_services'][$k] = $v;
			if (is_a($v, 'SlackAuthPlugin'   )) $GLOBALS['plugins_auth'    ][$k] = $v;
		}
	}

	function createPluginInstance($class_name){
		$obj = new $class_name();
		$obj->id = $class_name;
		return $obj;
	}

	function getPluginInstance($iid){

		$icfg = $GLOBALS['data']['instances'][$iid];

		if (!isset($icfg)) return null;

		$plugin = $icfg['plugin'];
		unset($icfg['plugin']);

		$instance = createPluginInstance($plugin);
		$instance->setInstanceConfig($iid, $icfg);

		return $instance;
	}

	function getAuthPlugin($id){

		$cfg = $GLOBALS['data']['auth'][$id];

		$instance = $GLOBALS['plugins_auth'][$id];
		if (!is_object($instance)) return;

		$instance->cfg = $cfg ? $cfg : array();

		return $instance;
	}


	function dumper($foo){
		
		echo "<pre style=\"text-align: left;\">";
		if (is_resource($foo)){
			var_dump($foo);
		}else{
			echo HtmlSpecialChars(var_export($foo, 1));
		}
		echo "</pre>\n";
	}

	class SlackAuthPlugin {

		function saveConfig(){
			load_data();
			$GLOBALS['data']['auth'][$this->id] = $this->cfg;
			save_data();
		}
	}

	function load_data(){
		$data = array();
		$path = dirname(__FILE__)."/../data/data.php";
		if (!file_exists($path)){
			die("Unable find Slackware data storage");
		}
		include($path);
		$GLOBALS['data'] = $data;
	}

	function save_data(){
		$path = dirname(__FILE__)."/../data/data.php";

		$fh = fopen($path, 'c');
		if (!$fh) die("Failed to open data file for writing");

		$retries = 5;

		for ($i=0; $i<$retries; $i++){
			$flag = 0;
			$ok = flock($fh, LOCK_EX | LOCK_NB, $flag);
			if ($ok) break;
			if (!$flag) die("Failed to lock data file");
			sleep(1);
		}
		if (!$ok) die("Failed to lock data file");

		ftruncate($fh, 0);
		fwrite($fh, "<"."?php \$data = ".var_export($GLOBALS['data'], true).';');

		flock($fh, LOCK_UN);
		fclose($fh);
	}
