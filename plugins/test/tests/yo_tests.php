<?php

	$dir = dirname(__FILE__);
	include("$dir/../../test_support.php");

	class YoTests extends PHPUnit_Framework_TestCase {

		protected $instance;

		protected function setUp() {
			$this->instance = get_plugin_instance("yo");
			$this->instance->icfg = array('channel' => '#test');
		}

		#
		# Normal message
		#
		public function test_message_1() {
			$request = array(
				'get' => array(
					'username' => 'TEST_USER'
				)
			);

			$ret = $this->instance->onHook($request);

			$this->assertTrue($ret['ok']);

			$expected = array(array(
				'channel' => '#test',
				'message' => array(
					'attachments' => array(array(
						'text' 		=> 'Yo from *TEST_USER*',
						'fallback' 	=> 'Yo from TEST_USER',
						'color' 	=> '9B59B6',
						'mrkdwn_in'	=> ['text'],
					)
				)
			)));
			$this->assertEquals($expected, $this->instance->posted_messages);
		}

		#
		# Bad payload
		#
		public function test_message_2() {
			$request = array(
				'get' => array(
					'invalid' => '12345'
				)
			);

			$ret = $this->instance->onHook($request);

			$expected = array(
				'ok' 	=> false,
				'error' => 'invalid_payload'
			);
			$this->assertEquals($expected, $ret);
		}

		#
		# POST instead of GET
		#
		public function test_message_3() {
			$request = array(
				'post' => array(
					'username' => 'TEST_USER'
				)
			);

			$ret = $this->instance->onHook($request);

			$expected = array(
				'ok' 	=> false,
				'error' => 'invalid_payload'
			);
			$this->assertEquals($expected, $ret);
		}
	}
