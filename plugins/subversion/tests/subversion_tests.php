<?php

	$dir = dirname(__FILE__);
	include("$dir/../../test_support.php");

	class SubversionTests extends PHPUnit_Framework_TestCase {

		protected $instance;

		protected function setUp() {
			$this->instance = get_plugin_instance("subversion");
			$this->instance->icfg = array('channel' => '#test');
		}

		/**
		* Test that a payload with all data is formatted correctly
		*/
		public function test_transform_all() {
			$text = $this->instance->transform(array(
				'repository' => "slack",
				'revision' => "r126299",
				'url' => 'http://svn.example.com/wsvn/main/?op=revision&rev=126299',
				'author' => 'ph',
				'log' => 'commit message',
			));

			$this->assertEquals($text, '[slack] <http://svn.example.com/wsvn/main/?op=revision&amp;rev=126299|r126299>: ph - commit message');
		}

		/**
		* Test that a payload with no url is formatted correctly
		*/
		public function test_transform_no_url() {
			$text = $this->instance->transform(array(
				'repository' => "slack",
				'revision' => "r126299",
				'author' => 'ph',
				'log' => 'commit message',
			));

			$this->assertEquals($text, '[slack] r126299: ph - commit message');
		}

		/**
		* Test that a payload with no repo is formatted correctly
		*/
		public function test_transform_no_repository() {

			$text = $this->instance->transform(array(
				'revision' => "r126299",
				'url' => 'http://svn.example.com/wsvn/main/?op=revision&rev=126299',
				'author' => 'ph',
				'log' => 'commit message',
			));

			$this->assertEquals($text, '<http://svn.example.com/wsvn/main/?op=revision&amp;rev=126299|r126299>: ph - commit message');
		}

		/**
		* Test that a payload with minimal data is formatted correctly
		*/
		public function test_transform_minimal() {
			$text = $this->instance->transform(array(
				'revision' => "r126299",
				'author' => 'ph',
				'log' => 'commit message',
			));

			$this->assertEquals($text, 'r126299: ph - commit message');
		}


		/**
		* Test that a good message is actually posted to channel
		*/
		public function test_onHook_ok() {
			$request = array(
				'post' => array(
					'payload' => json_encode(array(
						'revision' => "r126299",
						'author' => 'ph',
						'log' => 'commit message',
					), true),
				)
			);

			$ret = $this->instance->onHook($request);

			$this->assertTrue($ret['ok']);

			$expected = array(array(
				'channel' => '#test',
				'message' => array('text' => 'r126299: ph - commit message', 'mrkdwn' => false)
			));
			$this->assertEquals($expected, $this->instance->posted_messages);
		}

		/**
		* Test that invalid JSON causes an error
		*/
		public function test_onHook_invalid_json() {
			$request = array(
				'post' => array(
					'payload' => "{",
				)
			);

			$ret = $this->instance->onHook($request);

			$this->assertFalse($ret['ok']);
			$this->assertEquals($ret['error'], "invalid_payload");
		}


		/**
		* Test that missing payload data causes an error
		*/
		public function test_onHook_missing_data() {
			$request = array(
				'post' => array(
					'payload' => json_encode(array(
						'author' => 'ph',
						'log' => 'commit message',
					), true),
				)
			);

			$ret = $this->instance->onHook($request);

			$this->assertFalse($ret['ok']);
			$this->assertEquals($ret['error'], "invalid_payload");
		}

	}
