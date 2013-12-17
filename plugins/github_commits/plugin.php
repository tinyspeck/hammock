<?php

	class github_commits extends SlackServicePlugin {

		public $name = "Github Commits";
		public $desc = "Source control and code management.";

		public $cfg = array();

		function editConfig(){

			return $this->smarty->fetch('edit.txt');
		}
	}
