<?php

	class github extends SlackServicePlugin {

		public $name = "Github";
		public $desc = "Source control and code management.";

		public $cfg = array(
			'requires_auth' => 'github_auth',
		);

		function editConfig(){

			# do we have an auth token yet? if not we'll need to create one


		}
	}
