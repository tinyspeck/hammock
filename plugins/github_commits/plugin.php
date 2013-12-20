<?php

	class github_commits extends SlackServicePlugin {

		public $name = "Github Commits";
		public $desc = "Source control and code management.";

		public $cfg = array(
			'has_token'	=> true,
		);

		function onInit(){

			$channels = $this->getChannelsList();
			foreach ($channels as $k => $v){
				if ($v == '#general'){
					$this->icfg['channel'] = $k;
					$this->icfg['channel_name'] = $v;
				}
			}

			$this->icfg['branch']	= '';
			$this->icfg['botname']	= 'github';
		}

		function onView(){

			return $this->smarty->fetch('view.txt');
		}

		function onEdit(){

			$channels = $this->getChannelsList();

			if ($_GET['save']){

				$this->icfg['channel'] = $_POST['channel'];
				$this->icfg['channel_name'] = $channels[$_POST['channel']];
				$this->icfg['branch'] = $_POST['branch'];
				$this->icfg['botname'] = $_POST['botname'];
				$this->saveConfig();

				header("location: {$this->getViewUrl()}&saved=1");
				exit;
			}

			$this->smarty->assign('channels', $channels);

			return $this->smarty->fetch('edit.txt');
		}

		function onHook($req){

			if (!$this->icfg['channel']){
				return array(
					'ok'	=> false,
					'error'	=> "No channel configured",
				);
			}

			$github_payload = json_decode($req['post']['payload'], true);

			if (!$github_payload || !is_array($github_payload)){
				return array(
					'ok'	=> false,
					'error' => "No payload received from github",
				);
			}


			#
			# branch filtering
			#

			$filter_branches = $this->icfg['branch'] ? explode(',', $this->icfg['branch']) : array();

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
					'status'	=> "Commit not in tracked branch (in {$branch}, showing {$this->icfg['branch']})",
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

		function getLabel(){
			return "Post commits to {$this->icfg['channel_name']} as {$this->icfg['botname']}";
		}

		private function sendMessage($text){

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
