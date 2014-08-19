<?php

	class new_relic extends SlackServicePlugin {
		public $name = "New Relic";
		public $desc = "Real-time application performance management.";

		function onInit() {
            $channels = $this->getChannelsList();

            foreach ($channels as $k => $v) {
                if ($v == '#testinghammock') {
                    $this->icfg['channel']      = $k;
                    $this->icfg['channel_name'] = $v;
                }
            }

            $this->icfg['botname'] = "New Relic";
            $this->icfg['icon_url'] = trim($GLOBALS['cfg']['root_url'], '/') . '/plugins/new_relic/icon_48.png';
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

            $payload = $request['post'];
			if (!$payload || !is_array($payload)) return array('ok' => false, 'error' => "invalid_payload");

			if (isset($payload['alert'])){
				$decoded_payload = json_decode($payload['alert'], true);
				if (!$decoded_payload) return array('ok' => false, 'error' => "invalid_payload");
				$decoded_payload['event'] = 'alert';

			}else if (isset($payload['deployment'])){
				$decoded_payload = json_decode($payload['deployment'], true);
				if (!$decoded_payload) return array('ok' => false, 'error' => "invalid_payload");
				$decoded_payload['event'] = 'deployment';
			}

			$ret = $this->buildMessageData($decoded_payload);
			if (!$ret['ok']) return array('ok' => true);

            $this->postToChannel($ret['message']['text'], array(
                'channel'       => $this->icfg['channel'],
                'username'      => $this->icfg['botname'],
                'attachments'   => $ret['message']['attachments'],
                'icon_url'      => $this->icfg['icon_url'],
            ));
        }

        function getLabel() {
            return "Post to {$this->icfg['channel_name']} as {$this->icfg['botname']}";
        }

		private function buildMessageData($payload){

			$text = "";
			$attachment = array();

			switch ($payload['event']){
				case 'alert':
					$old_tz = date_default_timezone_get();
					date_default_timezone_set('UTC');

					if ($payload['short_description'] == 'All alerts have been closed for this incident'){
						return array('ok' => false);
					}

					#
					# Get the application name explicitly, or try to extract it from `long_description`
					#
					if (strrpos($payload['short_description'], "[application name]") === false) {
						if (isset($payload['application_name']) && $payload['application_name'] != ""){
							$application_name = $payload['application_name'];
						}else{
							if (isset($payload['long_description']) && $payload['long_description'] != ""){
								$needle = "Apps currently involved: ";
								$pos = strrpos($payload['long_description'], $needle);
								if ($pos !== false) {
									$substr = substr($payload['long_description'], $pos+strlen($needle));
									if($substr != "") {
										$application_name = $substr;
									}
								}
							}
						}
					}

					if (isset($payload['short_description']) && $payload['short_description'] != ""){
						if(isset($application_name)) {
							$text = "[".$application_name."] ".$payload['short_description'];
						}else{
							#
							# If it's a server alert, build a string of all non-empty servers involved
							#
							if (isset($payload['servers']) && $payload['servers'] != ""){
								$servers = array_unique($payload['servers']);
								$empty = array_search("", $servers);
								if($empty !== false) {
									array_splice($servers, $empty, 1);
					            }
								$text = "[".implode(", ", $servers)."] ".$payload['short_description'];
							} else {
								$text = $payload['short_description'];
							}
						}
					}else{
						$text = "Alert from ".$payload['application_name'];
					}

					$severity = $this->getSeverityData($payload['severity']);

					#
					# Overwrite severity colors for positive messages. NewRelic doesn't give us any state
					# data, so we have to parse from the message content.
					#
					if (substr($text, -5) == 'ended' || strpos($text, 'Ended alert ') !== false || strpos($text, ' recovered ') !== false) {
						$severity['color'] = '59A452';
					}

					$attachment['fields'][] = array(
						'title' => "Message",
						'value' => $this->escapeLink($payload['alert_url'], $payload['message']),
					);
					$attachment['fields'][] = array(
						'title' => "Start Time",
						'value' => date('M d, Y \a\t H:i:s e', strtotime($payload['created_at']." UTC")),
						'short' => true,
					);
					$attachment['fields'][] = array(
						'title' => "Severity",
						'value' => $severity['severity'],
						'short' => true,
					);
					$attachment['color'] = $severity['color'];

					date_default_timezone_set($old_tz);
					break;

				case 'deployment':
					$old_tz = date_default_timezone_get();
					date_default_timezone_set('UTC');

					$attachment['pretext'] 	  = $payload['application_name']." deployed by ".$payload['deployed_by'];
					$attachment['title'] 	  = $payload['revision'];
					$attachment['title_link'] = $payload['deployment_url'];
					$attachment['text'] 	  = $payload['description'];

					date_default_timezone_set($old_tz);
					break;

				default:
					return array('ok' => false);
					break;
			}

			if (!$attachment['fallback']){
				$attachment['fallback'] = $text;
			}

			return array('ok' => true,
						 'message' => array(
							 'text' => $text,
							 'attachments' => array($attachment)
						  ));
		}

		private function getSeverityData($severity){

			$severity_txt = $severity;
			$color = "";
			switch (strtolower($severity)){
				case 'critical':
					$severity_txt = "Critical";
					$color = "d00000";
					break;
				case 'downtime':
					$severity_txt = "Downtime";
					$color = "d00000";
					break;
				case 'caution':
					$severity_txt = "Caution";
					$color = "daa038";
					break;
			}

			return array('severity' => $severity_txt,
						 'color' => $color);
		}

	}
