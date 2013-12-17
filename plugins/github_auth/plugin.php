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

				echo "<p>You'll need to create a <a href=\"https://github.com/settings/applications/new\">github application</a>.</p>\n";
				echo "<p>Set the auth callback URL to <code>{$url}&oauth=1</code>.</p>";
				echo "<p>Once that's done, fill out this form:</p>";

				echo "<form action=\"{$url}&setconfig=1\" method=\"post\">\n";
				echo "<p>Client ID: <input type=\"text\" name=\"client_id\" value=\"\" /></p>\n";
				echo "<p>Client Secret: <input type=\"text\" name=\"client_secret\" value=\"\" /></p>\n";
				echo "<p><input type=\"submit\" value=\"Save Settings\" /></p>";
				echo "</form>";

				exit;
			}

			echo "Current config:<br /\n>";
			echo "Client ID: {$this->cfg['client_id']}<br />\n";
			echo "Client Secret: {$this->cfg['client_secret']}<br />\n";
			echo "<a href=\"{$url}&config=1\">Update settings</a>";
		}		
	}
