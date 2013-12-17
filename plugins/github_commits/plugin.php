<?php

	class github_commits extends SlackServicePlugin {

		public $name = "Github Commits";
		public $desc = "Source control and code management.";

		public $cfg = array();

		function editConfig(){

			return $this->smarty->fetch('edit.txt');
		}

		function onHook(){

			$payload = json_decode($_POST['payload'], true);

			
        $log = SLACKWARE_ROOT.'/data/github_'.uniqid().'.log';
        $fh = fopen($log, 'w');
        fwrite($fh, var_export($payload, 1));
        fclose($fh);

		}
	}
