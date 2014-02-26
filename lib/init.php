<?php
	error_reporting((E_ALL | E_STRICT) ^ E_NOTICE);

	define('HAMMOCK_ROOT', realpath(dirname(__FILE__)."/.."));

	if ($_ENV['HAMMOCK_ROOT']){
		include(HAMMOCK_ROOT."/lib/config_env.php");
	}else{
		include(HAMMOCK_ROOT."/lib/config.php");
	}

	include(HAMMOCK_ROOT."/lib/data.php");
	if ($cfg['data_provider'] == 'redis'){
		include(HAMMOCK_ROOT."/lib/data_redis.php");
	}else{
		include(HAMMOCK_ROOT."/lib/data_files.php");
	}

	include(HAMMOCK_ROOT."/lib/http.php");
	include(HAMMOCK_ROOT."/lib/service.php");
	include(HAMMOCK_ROOT."/lib/auth.php");

	include(HAMMOCK_ROOT."/lib/smarty/Smarty.class.php");

	if (!file_exists(HAMMOCK_ROOT."/data/templates_c")){
		mkdir(HAMMOCK_ROOT."/data/templates_c", 0777, true);
	}

	$smarty = new Smarty();
	$smarty->template_dir = HAMMOCK_ROOT."/templates";
	$smarty->compile_dir = HAMMOCK_ROOT."/data/templates_c";
	$smarty->assign_by_ref('cfg', $cfg);

	function load_plugins(){

		$GLOBALS['plugins'] = array();

		$dir = HAMMOCK_ROOT."/plugins";

		if ($dh = opendir($dir)){
			while (($file = readdir($dh)) !== false){

				if (is_dir("{$dir}/{$file}") && is_file("{$dir}/{$file}/plugin.php")){

					if ((include("{$dir}/{$file}/plugin.php"))){

						$GLOBALS['plugins'][$file] = 1;
					}
				}
			}
			closedir($dh);
		}

		$GLOBALS['plugins_services'] = array();
		$GLOBALS['plugins_auth'    ] = array();

		foreach ($GLOBALS['plugins'] as $k => $v){
			if (is_subclass_of($k, 'SlackServicePlugin')) $GLOBALS['plugins_services'][$k] = 1;
			if (is_subclass_of($k, 'SlackAuthPlugin'   )) $GLOBALS['plugins_auth'    ][$k] = 1;
		}
	}

	function createPluginInstance($class_name){
		$obj = new $class_name();
		$obj->id = $class_name;

		$obj->smarty = new Smarty();
		$obj->smarty->compile_id = "plugins|{$class_name}";
		$obj->smarty->template_dir = HAMMOCK_ROOT."/plugins/{$class_name}/templates";
		$obj->smarty->compile_dir = HAMMOCK_ROOT."/data/templates_c";
		$obj->smarty->assign_by_ref('this', $obj);

		return $obj;
	}

	function getPluginInstance($iid){

		$icfg = $GLOBALS['data']->get('instances', $iid);

		if (!isset($icfg)) return null;

		$plugin = $icfg['plugin'];
		unset($icfg['plugin']);

		$instance = createPluginInstance($plugin);
		$instance->setInstanceConfig($iid, $icfg);

		return $instance;
	}

	function getAuthPlugin($id){

		if (!isset($GLOBALS['plugins_auth'][$id])) return null;

		$instance = createPluginInstance($id);

		$cfg = $GLOBALS['data']->get('auth', $id);
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

	function api_call($method, $args = array()){

		$team = $GLOBALS['data']->get('metadata', 'team');

		$url = $GLOBALS['cfg']['slack_root']."api/".$method."?token=".$team['token'];

		foreach ($args as $k => $v) $url .= '&'.urlencode($k).'='.urlencode($v);

		$ret = SlackHTTP::get($url);

		if ($ret['ok'] && $ret['code'] == '200'){
			return array(
				'ok'	=> true,
				'data'	=> json_decode($ret['body'], true),
			);
		}

		return $ret;
	}

	function api_channels_list(){

		$ret = api_call('channels.list');
		$channels = array();
		foreach ($ret['data']['channels'] as $row){
			if (!$row['is_archived']) $channels[$row['id']] = '#'.$row['name'];
		}

		return $channels;
	}


	function verify_auth(){

		$v = $_COOKIE[$GLOBALS['cfg']['cookie_name']];

		if ($v){
			list($id, $secret) = explode('-', $v);

			$u = $GLOBALS['data']->get('users', $id);

			if (is_array($u) && $u['secret'] == $secret){

				$GLOBALS['cfg']['user'] = $u;
				return;
			}
		}

		$oauth_url = $GLOBALS['cfg']['slack_root']."oauth/authorize";
		$oauth_url .= "?client_id=".$GLOBALS['cfg']['client_id'];
		$oauth_url .= "&redirect_uri={$GLOBALS['cfg']['root_url']}oauth.php";

		$team = $GLOBALS['data']->get('metadata', 'team');
		if ($team['id']){

			$oauth_url .= "&team={$team['id']}";
		}else{
			$GLOBALS['smarty']->assign('first_time', 1);
		}

		$GLOBALS['smarty']->assign('oauth_url', $oauth_url);
		$GLOBALS['smarty']->display('page_login.txt');
		exit;
	}

	function split_sets($in, $size){
		$out = array();
		while (count($in)){
			$out[] = array_slice($in, 0, $size);
			$in = array_slice($in, $size);
		}
		return $out;
        }

