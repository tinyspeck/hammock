<?php

	class github_commits extends SlackServicePlugin {

		public $name = "Github Commits";
		public $desc = "Source control and code management.";

		public $cfg = array();

		function editConfig(){

			echo "Go to your repo's settings page and add this hook URL:<br />";
			echo "<code>{$this->getHookUrl()}</code>";
		}
	}
