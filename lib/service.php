<?php
	class SlackServicePlugin {

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
				if (!$auth->IsConfigured()) die("This plugin requires auth be configured - {$auth_plugin}");
				if (!$auth->UserIsAuthed()) die("You need to authenticate before continuing");
			}
		}
	}

