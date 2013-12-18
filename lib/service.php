<?php
	class SlackServicePlugin {

		public $name = "NO NAME";
		public $desc = "NO DESC";

		public $id;	# class ID
		public $iid;	# instance ID

		public $cfg;	# class config
		public $icfg;	# instance config

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

			return $GLOBALS['cfg']['root_url'] . 'hook.php?id=' . $this->iid;
		}

		function getEditUrl(){

			return $GLOBALS['cfg']['root_url'] . 'edit.php?id=' . $this->iid;
		}

		function dump(){
			$s = $this->smarty;
			unset($this->smarty);
			dumper($this);
			$this->smarty = $s;
		}

                function saveConfig(){
			load_data();
			$GLOBALS['data']['instances'][$this->iid] = $this->icfg;
			$GLOBALS['data']['instances'][$this->iid]['plugin'] = $this->id;
			save_data();
		}

		function deleteMe(){
			load_data();
			$GLOBALS['data']['deleted_instances'][$this->iid] = $GLOBALS['data']['instances'][$this->iid];
			unset($GLOBALS['data']['instances'][$this->iid]);
			save_data();
		}

		function editConfig(){

			return "<p>No config for this plugin.</p>";
		}

		function postToChannel($text, $extra){

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
	}

