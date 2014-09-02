<?php

	class SubversionPlugin extends SlackServicePlugin {
		const NAME = "Subversion";
		const DESC = "The Subversion software source control package.";
		const TOOLTIP = "Posts to a channel when code is committed to a repository";
		const DEFAULT_BOT_NAME = "svn";

		function transform($payload) {
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

		function onHook($request){

			$payload = json_decode($request['post']['payload'], true);
			if (!$payload) return array('ok' => false, 'error' => "invalid_payload");

			$text = $this->transform($payload);
			if (!$text) return array('ok' => false, 'error' => "invalid_payload");

			return $this->postMessage($this->icfg['channel'], array(
				'text' => $text,
				'mrkdwn' => false
			));
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

	}
