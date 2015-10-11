<?php

	class NewRelicPlugin extends SlackServicePlugin {
		const NAME = "New Relic";
		const DESC = "Real-time application performance management.";
		const TOOLTIP = "Posts to a channel when an alert is received.";
		const DEFAULT_BOT_NAME = "New Relic";

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

			if (!$ret['ok']){
				#
				# Unsupported event type, not a real error, so return 'ok'
				# without posting a message. We do not want to return a 500
				# error back to newrelic.
				#
				return array('ok' => true);
			}

			return $this->postMessage($this->icfg['channel'], $ret['message']);
		}

		function onEdit(){
			return $this->smarty->fetch('edit.txt');
		}

		function onNew(){
			return $this->smarty->fetch('new.txt');
		}

		function onDescription(){
			return $this->smarty->fetch('description.txt');
		}

		function onSummary(){
			return $this->smarty->fetch('summary.txt');
		}

		function buildMessageData($payload){

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

		################################################################################################

		function getSeverityData($severity){

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
