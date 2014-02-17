<?php
	$dir = dirname(__FILE__);
	include("$dir/lib/init.php");


	#
	# build request object
	#

	$headers = array();
	foreach ($_SERVER as $k => $v){
		if (substr($k, 0, 5) == 'HTTP_'){
			$k = substr($k, 5);
			$k = StrToLower($k);
			$k = preg_replace_callback('!(^|_)([a-z])!', 'local_replace_header', $k);
			$k = str_replace('_', '-', $k);
			$headers[$k] = $v;
		}
	}

	function local_replace_header($m){
		return $m[1].StrToUpper($m[2]);
	}

    # check for input based body as well
    # as mentioned here, http://www.php.net/manual/en/wrappers.php.php
    $post_body = file_get_contents("php://input");
    if (strlen($post_body) > 0) {
        $_POST = $post_body;
    }

	$req = array(
		'headers'	=> $headers,
		'get'		=> $_GET,
		'post'		=> $_POST,
	);

	#
	# log to a file (this is temporary)
	#

	$log = HAMMOCK_ROOT.'/data/hook_'.uniqid().'.log';
	$fh = fopen($log, 'w');
	fwrite($fh, '<'.'? $req = '.var_export($req, true).';');
	fclose($fh);


	#
	# see if we can find a plugin to handle it
	#

	load_plugins();

	$instance = getPluginInstance($_GET['id']);
	if (is_object($instance)){

		$ret = $instance->onLiveHook($req);
		$out = $instance->getLog();

		$uid = uniqid('', true);

		$data->set('hooks', $uid, array(
			'ts' => time(),
			'req' => $req,
			'ret' => $ret,
			'out' => $out,
		));

		$list = $data->get('hook_lists', $instance->iid);
		$list[] = $uid;
		$data->set('hook_lists', $instance->iid, $list);
	}

	echo "ok\n";
