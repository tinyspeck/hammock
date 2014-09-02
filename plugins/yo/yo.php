<?php

	class YoPlugin extends SlackServicePlugin {
		const NAME = "Yo";
		const DESC = "Zero character communication.";
		const TOOLTIP = "Get Yos in Slack.";
		const DEFAULT_BOT_NAME = "Yo";

		function onHook($request){

			if (!$request['get']['username']){
				return array('ok' => false, 'error' => "invalid_payload");
			}

			$attachment = array(
				'text' 		=> 'Yo from *'.$request['get']['username'].'*',
				'fallback'	=> 'Yo from '.$request['get']['username'],
				'color' 	=> '9B59B6',
				'mrkdwn_in'	=> 'text',
			);

			$message = array();
			$message['attachments'] = array($attachment);

			return $this->postMessage($this->icfg['channel'], $message);
		}
	}
