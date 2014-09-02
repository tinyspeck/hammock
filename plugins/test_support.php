<?php

	error_reporting((E_ALL | E_STRICT) ^ E_NOTICE);

	$dir = dirname(__FILE__);
	$_plugins_loaded = array();

	#
	# loads a plugin file. You'll probably usually call get_plugin_instance
	# instead
	#
	function load_plugin($name){
		$path = str_replace('-', '_', $name);
		include_once($GLOBALS['dir'] . "/{$path}/{$path}.php");
	}

	#
	# Get an instance of a plugin
	#
	function get_plugin_instance($name){
		load_plugin($name);
		$service_class_name = str_replace(' ', '', ucwords(str_replace('-', ' ', $name))) . "Plugin";

		return new $service_class_name();
	}

	#
	# This is a version of the SlackServicePlugin base class, with stubs in
	# place of any methods that have external side effects.
	#
	# We are lazy, and some of the methods have not been added to this plugin.
	# Feel free to add them yourselves.
	#
	class SlackServicePlugin {

		const NAME = 'NO NAME';
		const DESC = 'NO DESC';
		const TOOLTIP = '';
		const DEFAULT_BOT_NAME = "BOT";

		public $posted_messages = array();

		# this adds the message to the $posted_messages instance variable
		# for later inspection.
		function postMessage($channel, $message, $extras = array()){
			$msg = array(
				'channel' => $channel,
				'message' => $message
			);
			if ($extras) $msg['extras'] = $extras;

			$this->posted_messages[] = $msg;
			return array('ok' => true);
		}

		# duplicate of the same functionality in lib_services_plugins
		function escapeText($str){
			return HtmlSpecialChars($str, ENT_NOQUOTES);
		}

		# duplicate of the same functionality in lib_services_plugins
		function escapeLink($url, $label=null){
			$url = trim($url);
			$url = $this->escapeText($url);
			$url = str_replace('|', '%7C', $url);

			if (strlen($label)){

				$label = $this->escapeText($label);

				return "<{$url}|{$label}>";
			}

			return "<{$url}>";
		}
	}
