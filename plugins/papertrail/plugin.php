<?php

// author: Luka Kladaric luka@tripcommon.com
// based on github_commits plugin

	class papertrail extends SlackServicePlugin {

		public $name = "Papertrail";
		public $desc = "Log collection and analysis";

		public $cfg = array(
			'has_token'	=> true,
		);

		function onInit() {

			$channels = $this->getChannelsList();

			foreach ($channels as $k => $v) {
				if ($v == '#general') {
					$this->icfg['channel'] = $k;
					$this->icfg['channel_name'] = $v;
				}
			}

			$this->icfg['event_sample'] = 0;
			$this->icfg['botname']	= 'papertrail';
		}

		function onView() {
			return $this->smarty->fetch('view.txt');
		}

		function onEdit() {

			$channels = $this->getChannelsList();

			if ($_GET['save']) {
				$this->icfg['channel'] = $_POST['channel'];
				$this->icfg['channel_name'] = $channels[$_POST['channel']];
				$this->icfg['botname'] = $_POST['botname'];
				$this->icfg['event_sample'] = (int)$_POST['event_sample'];
				$this->saveConfig();

				header("location: {$this->getViewUrl()}&saved=1");
				exit;
			}

			$this->smarty->assign('channels', $channels);

			return $this->smarty->fetch('edit.txt');
		}

		function onHook($req) {

			if (!$this->icfg['channel']) {
				return array(
					'ok'	=> false,
					'error'	=> "No channel configured",
				);
			}

			$payload = json_decode($req['post']['payload'], true);

			if (!$payload || !is_array($payload)) {
				return array(
					'ok'	=> false,
					'error' => "No payload received from papertrail",
				);
			}


			#
			# send some messages
			#

			$events = $payload['events'];
			$num_events = count($events);

			$sample = $this->icfg['event_sample'];

			if ($sample) {
				// preserve only the last $event_sample events
				$events = array_slice($events, $sample * -1);
			}

			$num_sample = count($events);

			if ($num_events > 1) {

				$text = $this->escapeText("[{$payload['name']}] {$num_events} new events");
				if ($sample) {
					$text .= $this->escapeText(" (showing the latest {$sample})");
				}
				$text .= $this->escapeText(":");

				foreach ($events as $e) {
					$text .= "\n" . $this->renderEvent($e);
				}

				$num_diff = $num_events - $num_sample;
				if ($num_diff) {
					$text .= $this->escapeText("\nAnd {$num_diff}  others");
				}

				return $this->sendMessage($text);
			}

			if ($commit_count) {
				$text = $this->escapeText("[{$payload['name']}] ") . $this->renderEvent($events[0]);
				return $this->sendMessage($text);
			}

			return array(
				'ok'		=> true,
				'status'	=> "Nothing found to report",
			);
		}

		private function renderEvent($e) {
			$text = $this->escapeText("[{$e['display_received_at']}] ");
			$text .= $this->escapeText(" ", implode($e['hostname'], $e['program'], $e['message']));
			return $text;
		}

		function getLabel() {
			return "Post alerts to {$this->icfg['channel_name']} as {$this->icfg['botname']}";
		}

		private function sendMessage($text) {

			$ret = $this->postToChannel($text, array(
                                'channel'       => $this->icfg['channel'],
                                'username'      => $this->icfg['botname'],
                        ));

			return array(
				'ok'		=> true,
				'status'	=> "Sent a message",
			);
		}
	}
