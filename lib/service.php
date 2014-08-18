<?php
	class SlackServicePlugin {

		public $name = "NO NAME";
		public $desc = "NO DESC";

		public $id;	# class ID
		public $iid;	# instance ID

		public $cfg;	# class config
		public $icfg;	# instance config

		private $log = array();

		function SlackServicePlugin(){
			if ($this->name == "NO NAME"){
				$cn = get_class($this);
				$this->name = "Unnamed ({$cn})";
			}
		}

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
				'channel'	=> '#general',
				'text'		=> $text,
				'username'	=> '',
				'icon_url'	=> $this->iconUrl(48, true),
			);

			$map_params = array(
				'channel',
				'username',
				'icon_url',
				'attachments',
				'unfurl_links',
				'icon_emoji',
				'parse',
			);

			foreach ($map_params as $p){
				if (isset($extra[$p])){
					if ($p == 'attachments'){
						$params[$p] = json_encode($extra[$p]);
					}else{
						$params[$p] = $extra[$p];
					}
				}
			}

			$ret = api_call('chat.postMessage', $params);
error_log($ret);
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

		function onHook($request){
			# handle an incoming hook here
			return array(
				'ok'	=> false,
				'error'	=> 'onHook not implemented',
			);
		}

		function iconUrl($size=32, $abs=false){
			if (!in_array($size, array(32,48,64,128))) $size = 32;
			$pre = $abs ? $GLOBALS['cfg']['root_url'] : '';
			return "{$pre}plugins/{$this->id}/icon_{$size}.png";
		}
	}

