<?php

	class TestPlugin extends SlackServicePlugin {
		const NAME = "Test";
		const DESC = "This is a test plugin";
		const TOOLTIP = "Test in Slack";
		const DEFAULT_BOT_NAME = "Test";

		function onHook($req) {

			if(!$req['get']['msg']) {
				return array('ok' => false, 'error' => "invalid_payload");
			}

			$message = array();
			$message['text'] = $req['get']['msg'];

			return $this->postMessage($this->icfg['channel'], $message);
		}

	}
