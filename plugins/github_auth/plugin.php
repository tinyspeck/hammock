<?php
	class github_auth extends SlackAuthPlugin {

		public $name = "Github Authentication";
                public $desc = "Authenticate for Github API.";

		public $cfg = array();

		function isConfigured(){

			return !!$this->cfg['client_id'];
		}

		function configPage(){

			$url = 'http://doats.net/slackware/auth.php?id=github_auth';

			if ($_GET['setconfig']){

				$this->cfg['client_id']  = $_POST['client_id'];
				$this->cfg['client_secret']  = $_POST['client_secret'];
				$this->saveConfig();

				header("location: {$url}&saved=1");
				exit;
			}

			if (!$this->cfg['client_id'] || $_GET['config']){

				return $this->smarty->fetch('config.txt');
			}

			return $this->smarty->fetch('config_current.txt');
		}		
	}
