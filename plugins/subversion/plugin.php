<?php

	class subversion extends SlackServicePlugin {
		public $name = "Subversion";
		public $desc = "The Subversion software source control package.";

		public $cfg = array(
			'has_token' => true,
		);

		function onInit() {
			$channels = $this->getChannelsList();

			foreach ($channels as $k => $v) {
	            if ($v == '#yo') {
	                $this->icfg['channel']      = $k;
	                $this->icfg['channel_name'] = $v;
	            }
        	}

        	$this->icfg['botname'] = "svn";
        	$this->icfg['icon_url'] = trim($GLOBALS['cfg']['root_url'], '/') . '/plugins/subversion/icon_48.png';
		}

		function onView() {
			return $this->smarty->fetch('view.txt');
		}

		function onEdit() {
			$channels = $this->getChannelsList();
				if ($_GET['save']) {
	            $this->icfg['channel']      = $_POST['channel'];
	            $this->icfg['channel_name'] = $channels[$_POST['channel']];
	            $this->icfg['botname']      = $_POST['botname'];
	            $this->saveConfig();

	            header("location: {$this->getViewUrl()}&saved=1");
	            exit;
	        }
	        $this->smarty->assign('channels', $channels);
	        return $this->smarty->fetch('edit.txt');
		}

		function onHook($request){

			if ($request['post']['payload']) {
				$payload = json_decode($request['post']['payload'], true);
			} else {
				$payload = json_decode($request['post_body'], true);
			}

			if (!$payload){
				return array('ok' => false, 'error' => "invalid_payload");
			}

			$message = $this->transform($payload);

			$this->postToChannel($message, array(
				'channel'	=> $this->icfg['channel'],
				'username'	=> $this->icfg['botname'],
				'icon_url'	=> $this->icfg['icon_url'],
			));
		}

		function getLabel() {
        	return "Post updates to {$this->icfg['channel_name']} as {$this->icfg['botname']}";
    	}

		private function transform($payload) {
			if (!($payload['author'] && $payload['log'] && $payload['revision'])){
				return;
			}

			$text = "";
			if ($payload['repository']){
				$text .= "[{$payload['repository']}] ";
			}

			if ($payload['url']){
				$text .= $this->escapeLink($payload['url'], $payload['revision']).": ";
			}else{
				$text .= $payload['revision'].": ";
			}

			$text .= $payload['author']." - ".$payload['log'];
			return $text;
		}
	}
