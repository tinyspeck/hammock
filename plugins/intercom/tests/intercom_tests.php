<?php

	$dir = dirname(__FILE__);
	include("$dir/../../test_support.php");

	class IntercomTests extends PHPUnit_Framework_TestCase {

		protected $instance;

		protected function setUp() {
			$this->instance = get_plugin_instance("intercom");
			$this->instance->icfg = array('channel' => '#test');
		}

		#
		# Normal message
		#
		public function test_message_1() {

		}
	}
