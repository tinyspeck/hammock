<?php

	class IntercomPlugin extends SlackServicePlugin {
		const NAME = "Intercom";
		const DESC = "The easiest way to see and talk to your users.";
		const TOOLTIP = "Post Intercom alerts to a channel.";
		const DEFAULT_BOT_NAME = "Intercom";

		function onHook($request){
			if ($request['post']['payload']) {
				$payload = json_decode($request['post']['payload'], true);
			} else {
				$payload = json_decode($request['post_body'], true);
			}

			if (!$payload){
				return array('ok' => false, 'error' => "invalid_payload");
			}

			#
			# Sanity filter
			#
			$message = array();
			$message['text'] = $payload['text'];

			if ($payload['attachments']){
				$message['attachments'] = array();
				$i = 0;
				foreach ($payload['attachments'] as $attachment){
					foreach ($attachment as $key=>$value){
						switch ($key){
							case "fallback":
							case "pretext":
							case "text":
							case "author_icon":
							case "author_name":
							case "author_link":
							case "color":
							case "fields":
								$message['attachments'][$i][$key] = $value;
								break;
						}
					}
					$i++;
				}
			}

			return $this->postMessage($this->icfg['channel'], $message);
		}
	}
