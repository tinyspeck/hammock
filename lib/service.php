<?php
	class SlackServicePlugin {

		#
		# A plugin should provide these constants:
		#
		const NAME = 'NO NAME'; # the display name for this plugin
		const DESC = 'NO DESC'; # a two line description, used in /services/new
		const TOOLTIP = '';     # a short tooltip, used in /services/new
		const DEFAULT_BOT_NAME = "BOT"; # the default bot name to use

		public $id;	# class ID
		public $iid;	# instance ID

		public $cfg;	# class config
		public $icfg;	# instance config

		private $log = array();

		public $new_template;
		public $edit_template;
		public $summary_template;
		public $description_template;

		function SlackServicePlugin(){
			if ($this::NAME == "NO NAME"){
				$cn = get_class($this);
				$this->name = "Unnamed ({$cn})";
			}
			$this->new_template = 'new.txt';
			$this->edit_template = 'edit.txt';
			$this->summary_template = 'summary.txt';
			$this->description_template = 'description.txt';
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

		#These are called before their respective pages are rendered, to load custom
		# JS/CSS files for individual plugins
		
		function onEdit(){
			# Assign smarty variables or render different templates
		}

		function onNew(){
			# Assign smarty variables or render different templates	
		}

		function onDescription(){
			# Assign smarty variables or render different templates
		}

		function onSummary(){
			# Assign smarty variables or render different templates
		}


		function getHookUrl(){

			$url =  $GLOBALS['cfg']['root_url'] . 'hook.php?id=' . $this->iid;

			if ($this->cfg['has_token']) $url .= "&token={$this->icfg['token']}";

			return $url;
		}

		function getSaveUrl(){
			return $GLOBALS['cfg']['root_url'] . 'add.php?id=' . $_GET['id'];
		}

		function getViewUrl(){

			return $GLOBALS['cfg']['root_url'] . 'view.php?id=' . $this->iid;
		}

		function getAssets(){
			return $GLOBALS['cfg']['root_url'] . "plugins/{$this->id}/assets/";
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

		function postMessage($channel, $message, $extras = array()){

			$params = array();

			if (!$channel) return array('ok' => false, 'error' => "no channel");
			$params['channel'] = $channel;

			if (!$message['text'] && !$message['attachments']) return array('ok' => false, 'error' => "no text");

			if ($message['text']) $params['text'] = $message['text'];
			if ($message['attachments']) $params['attachments'] = json_encode($message['attachments']);
			
			$this->icfg['bot_name'] ? $params['username'] = $this->icfg['bot_name'] : $this::DEFAULT_BOT_NAME;

			$this->icfg['bot_icon'] ? $params['icon_url'] = $this->icfg['bot_icon'] : $params['icon_url'] = $this->iconUrl(48, "bot", true);

			$extra_params = array(
				'text',
				'parse',
				'link_names',
				'unfurl_links',
				'icon_emoji',
			);

			foreach ($extra_params as $p){
				if (isset($extras[$p])){
					if ($p == 'attachments'){
						$params[$p] = json_encode($extras[$p]);
					}else{
						$params[$p] = $extras[$p];
					}
					error_log($params[$p]);
				}
			}

			$ret = api_call('chat.postMessage', $params);

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

		function getChannelName($channel){
			$channels = $this->getChannelsList();

			foreach ($channels as $k => $v) {
			    if ($k == $this->icfg['channel']) {
			    	return $v;
			    }
			}
			return "Unknown channel";
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

		function iconUrl($size=32, $type, $abs=false){
			if (!in_array($size, array(32,48,64,128))) $size = 32;
			$pre = $abs ? $GLOBALS['cfg']['root_url'] : '';
			return "{$pre}plugins/{$this->id}/assets/{$type}_{$size}.png";
		}

		function onHook($request){
			# handle an incoming hook here
			return array(
				'ok'	=> false,
				'error'	=> 'onHook not implemented',
			);
		}

		function onInit(){
			# set default options in $this->icfg here
			$this->smarty->assign('DESC', constant(get_class($this)."::DESC"));
			$this->icfg['bot_name'] = $this::DEFAULT_BOT_NAME;
		}
	}
