<?php

	class github_commits extends SlackServicePlugin {

		public $name = "Github Commits";
		public $desc = "Source control and code management.";

		public $cfg = array();

		function editConfig(){

			$ret = $this->postToChannel("magic test", array(
				'channel'	=> '#caltest',
				'username'	=> 'edit-bot'
			));
dumper($ret);

			return $this->smarty->fetch('edit.txt');
		}

		function onHook(){

        $log = SLACKWARE_ROOT.'/data/github_'.uniqid().'.log';
        $fh = fopen($log, 'w');
        fwrite($fh, $_POST['payload']);
        fclose($fh);

	$this->cfg['channel'] = '#caltest';


			if (!$this->cfg['channel']){
				return array(
					'ok'	=> false,
					'error'	=> "No channel configured",
				);
			}

			$github_payload = json_decode($_POST['payload'], true);

			if (!$github_payload || !is_array($github_payload)){
				return array(
					'ok'	=> false,
					'error' => "No payload received from github",
				);
			}


			#
			# branch filtering
			#

			$filter_branches = $this->cfg['branch'] ? explode(',', $this->cfg['branch']) : array();

			if ($github_payload['base_ref']){

				$ref_parts = explode('/', $github_payload['ref']);
				$base_ref_parts = explode('/', $github_payload['base_ref']);
				$branch = array_pop($base_ref_parts);
			}else{
				$ref_parts = explode('/', $github_payload['ref']);
				$branch = array_pop($ref_parts);
			}

			if (count($filter_branches) && !in_array($branch, $filter_branches)){
				return array(
					'ok'		=> true,
					'status'	=> "Commit not in tracked branch (in {$branch}, showing {$this->cfg['branch']})",
				);
			}


			#
			# send some messages
			#

			if ($ref_parts[1] == "tags"){

				$text  = $this->escapeText("[{$github_payload['repository']['name']}/{$branch}] ");
				$text .= $this->escapeText("{$github_payload['pusher']['name']} pushed tag ");
				$text .= $this->escapeLink($github_payload['compare'], "{$ref_parts[2]}");

				return $this->sendMessage($text);
			}

			$commit_count = count($github_payload['commits']);

			if ($commit_count > 1){

				$text = $this->escapeText("[{$github_payload['repository']['name']}/{$branch}] $commit_count new commits:");

				$i = 0;
				foreach ($github_payload['commits'] as $commit){
					$short_sha = substr($commit['id'], 0, 12);

					$text .= $this->escapeText("\n[{$github_payload['repository']['name']}/{$branch}] ");
					$text .= $this->escapeLink($commit['url'], "{$short_sha}");
					$text .= $this->escapeText(": {$commit['message']} - {$commit['author']['name']}");
					
					$i++;
					if ($i == 10 && ($commit_count-$i)>1) break;
				}

				if ($i != $commit_count){
					$text .= "\nAnd ".$this->escapeLink($github_payload['compare'], ($commit_count-$i)." others");
				}

				return $this->sendMessage($text);
			}

			if ($commit_count){

				$commit = $github_payload['commits'][0];
				$short_sha = substr($commit['id'], 0, 12);

				$text .= $this->escapeText("[{$github_payload['repository']['name']}/{$branch}] ");
				$text .= $this->escapeLink($commit['url'], "{$short_sha}");
				$text .= $this->escapeText(": {$commit['message']} - {$commit['author']['name']}");

				return $this->sendMessage($text);
			}

			return array(
				'ok'		=> true,
				'status'	=> "Nothing found to report",
			);
		}

		private function sendMessage($text){

			$ret = $this->postToChannel($text, array(
                                'channel'       => $this->cfg['channel'],
                                'username'      => 'edit-bot'
                        ));

			return array(
				'ok'		=> true,
				'status'	=> "Sent a message",
			);
		}
	}
