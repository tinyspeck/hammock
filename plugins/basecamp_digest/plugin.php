<?php

	class basecamp_digest extends SlackServicePlugin {

		public $name;
		public $desc;

		private $team;
		private $users;
		private $channels;

		public $cfg = array(
			'has_token'	=> true,
		);

		function basecamp_digest() {
			$this->name = 'Basecamp Digest';
			$this->desc = 'Sends a Digest of channel activity to Basecamp';
			$this->team = $GLOBALS['data']->get('metadata', 'team');
		}

		function onInit(){
			

			$channels = $this->getChannelsList();
			foreach ($channels as $key => $value){
				if ($value == '#general'){
					$this->icfg['channel'] = $key;
					$this->icfg['channel_name'] = $value;
				}
			}
			$this->icfg['cron'] = true;
			$this->icfg['cron_interval'] = '58 23 * * *';


			$this->icfg['basecamp_url'] =
				( array_key_exists('basecamp_url', $GLOBALS['cfg']) ) ?
					$GLOBALS['cfg']['basecamp_url'] :
					'';
			$this->icfg['basecamp_token'] =
				( array_key_exists('basecamp_token', $GLOBALS['cfg']) ) ?
					$GLOBALS['cfg']['basecamp_token'] :
					'';
		}

		function onView(){
			return $this->smarty->fetch('view.txt');
			
		}

		function onEdit(){

			$channels = $this->getChannelsList();
			$projects = $this->getProjects();

			if ($_GET['save']){
				$project = explode('::', $_POST['project']);

				$this->icfg['basecamp_url'] = $_POST['basecamp_url'];
				$this->icfg['basecamp_token'] = $_POST['basecamp_token'];
				$this->icfg['project'] = $project[0];
				$this->icfg['project_name'] = $project[1];
				$this->icfg['channel'] = $_POST['channel'];
				$this->icfg['channel_name'] = $channels[$_POST['channel']];
				$this->saveConfig();

				header("location: {$this->getViewUrl()}&saved=1");
				exit;
			}

			$this->smarty->assign('channels', $channels);
			$this->smarty->assign('projects', $projects);

			return $this->smarty->fetch('edit.txt');
		}

		/**
		 * Respond to an HTTP request to the hook URL. Posts a list of messages from
		 * 
		 * @param {Array} $req information about the incoming request
		 * @return void
		 * 
		 */
		function onHook($req){
			$title = 'Slack Digest for ' . date('M j, Y');
			$body = $this->formatChannelHistory( $this->getChannelHistory(array('channel' => $this->icfg['channel'], 'oldest' => strtotime('24 hours ago') ) ) );
			$private = true;

			if ($body !== false) {
				$res = $this->createMessage($title, $body, $private);	

				if (strpos('201', $res) !== FALSE) {
					header('HTTP/1.1 200 OK');
				}
			}
			else {
				header('HTTP/1.1 200 OK');
			}
			

		}

		/**
		 * The label that describes this instance on the Existing Integrations page
		 *
		 * @return {String} The label text
		 */
		function getLabel(){
			return "Post daily summaries of {$this->icfg['channel_name']} to {$this->icfg['project_name']}";
		}

		/**
		 * Get a list of Basecamp projects.
		 *
		 * @link https://github.com/basecamp/basecamp-classic-api/blob/master/sections/projects.md
		 * @return {Array}
		 */
		public function getProjects() {
			$resourceUrl = $this->icfg['basecamp_url'] . '/projects.xml';

			$ch = curl_init();
			curl_setopt_array($ch, array(
				CURLOPT_URL => $resourceUrl,
				CURLOPT_HTTPHEADER => array(
					'Authorization: Basic ' . base64_encode($this->icfg['basecamp_token'] . ':x'),
					'Accept: application/xml',
					'Content-Type: application/xml',
				),
				CURLOPT_RETURNTRANSFER => true,
			));

			$response = curl_exec($ch);
			$projects = simplexml_load_string($response);

			return $projects;
		}

		/**
		 * Get a list of Slack users
		 *
		 * @return {Array}|{Bool} Array of users if success, false on failure
		 */
		public function getUsers() {
			$users = api_call('users.list');

			if($users['ok']) {
				return $users['data']['members'];
			}
			else {
				return false;
			}
		}

		/**
		 * Posts a message to the Basecamp project specified in icfg
		 *
		 * @param {String} $title The Title of the message
		 * @param {String} $body The body of the message
		 * @param {Boolean} $private Whether the message is private. Default: true
		 * @return The headers and body returned from Basecamp.
		 */
		public function createMessage($title, $body, $private) {
			$resourceUrl = $this->icfg['basecamp_url'] . '/projects/' . $this->icfg['project'] . '/posts.xml';
			
			$private = (is_bool($private)) ? $private : true;
			$private = ($private) ? 'true' : 'false';

			$payload = <<<EOT
<post>
  <title>$title</title>
  <body><![CDATA[$body]]></body>
  <private>$private</private>
</post>
EOT;

			$ch = curl_init();
			curl_setopt_array($ch, array(
				CURLOPT_URL => $resourceUrl,
				CURLOPT_POST => true,
				CURLOPT_HEADER => true,
				CURLOPT_HTTPHEADER => array(
					'Authorization: Basic ' . base64_encode($this->icfg['basecamp_token'] . ':x'),
					'Accept: application/xml',
					'Content-Type: application/xml',
				),
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_POSTFIELDS => $payload,
			));

			return curl_exec($ch);
		}

		private function getChannelHistory($args) {
			$history = api_call('channels.history', $args );

			if( $history['ok'] ) {
				return $history;
			}
			else {
				return false;
			}
		}

		/**
		 * Converts Slack messages into a 
		 * 
		 * @param {Array} $history The array returned by Slack's channel.history
		 * @return {String} HTML-formatted list of messages
		 */
		private function formatChannelHistory($history) {
			// We will need to map users and channels to nice names instead of Slack IDs
			$this->users = $this->getUsers();
			$this->channels = $this->getChannelsList();
			// The API provides messages newest > oldest. For a digest, we want the opposite.
			$messages = array_reverse($history['data']['messages']);
			$messageCount = 0;

			$output = sprintf(
				"<p>Here&rsquo;s what happened in <a href=\"https://%s.slack.com%s%s/s%u\">%s</a> in the past 24 hours: </p><hr/>",
				$this->team['name'],
				'/archives/',
				str_replace('#', '', $this->icfg['channel_name']),
				strtotime('24 hours ago') * 1000000,
				$this->icfg['channel_name']
			);

			foreach($messages as $message) {
				$visibleSubtypes = array(
					'channel_join',
					'channel_leave',
					'channel_topic',
					'channel_purpose',
					//'bot_message',  We specifically don't want bot messages
					'file_share',
					'file_comment',
					'channel_archive',
					'channel_unarchive',
				);

				// Regular messages have no subtype. Others that are visible are listed above.
				if( $message['subtype'] == false || in_array($message['subtype'], $visibleSubtypes) ) {
					$messageCount++;

					$message['user'] = $this->formatUser($message['user']);
					$message['text'] = preg_replace_callback('/<@(.*?)>/', array($this, 'formatUserLink'), $message['text']);
					$message['text'] = preg_replace_callback('/<#(.*?)>/', array($this, 'formatChannelLink'), $message['text']);
					$message['text'] = preg_replace_callback('/<(.*?)\|(.*?)>/', array($this, 'formatGeneralLink'), $message['text']);
					$message['text'] = preg_replace_callback('/<(.*?)>/', array($this, 'formatGeneralLink'), $message['text']);

					$newline = sprintf(
						"<strong><a href=\"https://%s.slack.com%s%s\">%s:</a></strong> %s<br>",
						$this->team['name'],
						'/team/',
						$message['user'],
						$message['user'],
						$message['text']
					);
					
					$output .= $newline;
				}
			}

			return ($messageCount != 0) ? $output : false;
		}

		/**
		 * Returns the name of a user by ID
		 * 
		 * @param {String} $userId A Slack UserId
		 * @return {String} The user's username
		 */
		private function formatUser($userId) {
			$users = $this->users;
			foreach($users as $user) {
				if ($user['id'] == $userId)
				{
					return $user['name'];
				}
			}
		}

		/**
		 * Converts Slack's user mention link to an HTML link 
		 *
		 * @param {Array} $userId An array of matches from preg_match_callback()
		 * @return {String} An HTML link to the user's web profile
		 */
		private function formatUserLink($userId) {
			// preg_replace_callback gives an array, with the match at $array[1]
			if(is_array($userId)) {
				$userId = $userId[1];
			}

			$users = $this->users;
			foreach($users as $user) {
				if ($user['id'] == $userId)
				{
					return sprintf('<a href="https://%s.slack.com%s%s">@%s</a>', $this->team['name'], '/team/', $user['name'], $user['name']);
				}
			}
		}

		/**
		 * Converts Slack's channel mention link to an HTML link 
		 *
		 * @param {Array} $channelId An array of matches from preg_match_callback()
		 * @return {String} An HTML link to the channel's web archive
		 */
		private function formatChannelLink($channelId) {
			// preg_replace_callback gives an array, with the match at $array[1]
			if(is_array($channelId)) {
				$channelId = $channelId[1];
			}

			$channels = $this->channels;
			foreach($channels as $id => $name) {
				if ($channelId == $id) {
					return sprintf('<a href="https://%s.slack.com%s%s">%s</a>', $this->team['name'], '/archives/', str_replace('#','',$name), $name);
				}
			}
		}

		/**
		 * Converts Slack's general link to an HTML link 
		 *
		 * @param {Array} $link An array of matches from preg_match_callback()
		 * @return {String} An HTML link to the user's Slack web profile
		 */
		private function formatGeneralLink($link) {
			// Some links in Slack have anchor text, some don't
			if (! array_key_exists(2, $link)) {
				$link[2] = $link[1];
			}

			// Links that have already been formatted don't need to be formatted again.
			if(preg_match('(<\/?a.*?>)', $link[0]) == 0) {
				return sprintf('<a href="%s">%s</a>', $link[1], $link[2]);
			}
			else {
				return $link[0];
			}
		}
	}
