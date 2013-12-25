<?php
	class SlackServicePlugin {

		public $name = "NO NAME";
		public $desc = "NO DESC";

		public $id;	# class ID
		public $iid;	# instance ID

		public $cfg;	# class config
		public $icfg;	# instance config

		private $log = array();

		function createInstanceId(){
			$this->iid = uniqid();
		}

		function setInstanceConfig($iid, $icfg){
			$this->iid = $iid;
			$this->icfg = $icfg;
		}

		function checkRequirements(){

			if ($this->cfg['requires_auth']){
				$auth_plugin = $this->cfg['requires_auth'];
				$auth = getAuthPlugin($auth_plugin);
				if (!$auth->isConfigured()) die("This plugin requires auth be configured - {$auth_plugin}");
				if (!$auth->isUserAuthed()) die("You need to authenticate before continuing");
			}
		}

		function getHookUrl(){

			$url =  $GLOBALS['cfg']['root_url'] . 'hook.php?id=' . $this->iid;

			if ($this->cfg['has_token']) $url .= "&token={$this->icfg['token']}";

			return $url;
		}

		function getEditUrl(){

			return $GLOBALS['cfg']['root_url'] . 'edit.php?id=' . $this->iid;
		}

		function getViewUrl(){

			return $GLOBALS['cfg']['root_url'] . 'view.php?id=' . $this->iid;
		}

		function dump(){
			$s = $this->smarty;
			unset($this->smarty);
			dumper($this);
			$this->smarty = $s;
		}

                function saveConfig(){
			$cfg = $this->icfg;
			$cfg['plugin'] = $this->id;
			$GLOBALS['data']->set('instances', $this->iid, $cfg);
		}

		function deleteMe(){
			$cfg = $GLOBALS['data']->get('instances', $this->iid);
			$GLOBALS['data']->set('deleted_instances', $this->iid, $cfg);
			$GLOBALS['data']->del('instances', $this->iid); 
		}

		function postToChannel($text, $extra){

			$this->log[] = array(
				'type' => 'message_post',
				'text' => $text,
				'extra' => $extra,
			);

			$params = array(
				'text'	=> $text,
			);

			if (isset($extra['channel' ])) $params['channel' ] = $extra['channel' ];
			if (isset($extra['username'])) $params['username'] = $extra['username'];

			$params = array(
				'payload' => json_encode($params),
			);

			$ret = SlackHTTP::Post($GLOBALS['cfg']['webhook_url'], $params);

			return $ret;
		}

		function getLog(){
			return $this->log;
		}

		function escapeText($str){
			return HtmlSpecialChars($str, ENT_NOQUOTES);
		}

		function escapeLink($url, $label=null){
			$url = trim($url);

			$url = $this->escapeText($url);
			$url = str_replace('|', '%7C', $url);

			if (strlen($label)){

				$label = $this->escapeText($label);

				return "<{$url}|{$label}>";
			}

			return "<{$url}>";
		}

		function onParentInit(){

			if ($this->cfg['has_token']){
				$this->regenToken();
			}
		}

		function regenToken(){

			$this->icfg['token'] = substr(sha1(rand()), 1, 10);
		}

		function getChannelsList(){

			return api_channels_list();
		}

		function onLiveHook($req){

			if ($this->cfg['has_token']){
				if ($req['get']['token'] != $this->icfg['token']){
					return array(
						'ok'		=> false,
						'error'		=> 'bad_token',
						'sent'		=> $req['get']['token'],
						'expected'	=> $this->icfg['token'],
					);
				}
			}

			return $this->onHook($req);
		}


		# things to override

		function onView(){

			return "<p>No information for this plugin.</p>";
		}

		function onEdit(){

			return "<p>No config for this plugin.</p>";
		}

		function getLabel(){

			return "No label ({$this->iid})";
		}

		function onInit(){
			# set default options in $this->icfg here
		}

		function onHook(){
			# handle an incoming hook here
			return array(
				'ok'	=> false,
				'error'	=> 'onHook not implemented',
			);
		}
	}

