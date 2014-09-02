<?php

	$dir = dirname(__FILE__);
	include("$dir/../../test_support.php");

	class NewRelicTests extends PHPUnit_Framework_TestCase {

		protected $instance;

		protected function setUp() {
			$this->instance = get_plugin_instance("new-relic");
			$this->instance->icfg = array('channel' => '#test');
		}

		#
		# Normal message
		#
		public function test_message_1() {
			$request = array(
				'post' => array(
					'alert' => json_encode(array(
						"created_at" => "2014-07-03T22:09:34+00:00",
						"application_name" => "Application name",
						"account_name" => "Account name",
						"severity" => "critical",
						"message" => "Apdex score fell below critical level of 0.90",
						"short_description" => "[application name] alert opened",
						"long_description" => "Alert opened on [application name]: Apdex score fell below critical level of 0.90",
						"alert_url" => "https://rpm.newrelc.com/accounts/[account_id]/applications/[application_id]/incidents/[incident_id]"
					), true),
				)
			);

			$ret = $this->instance->onHook($request);

			$this->assertTrue($ret['ok']);

			$expected = array(array(
				'channel' => '#test',
				'message' => array(
					'text' => '[application name] alert opened',
					'attachments' => array(array(
						'fields' => array(
							array(
								'title' => 'Message',
								'value' => '<https://rpm.newrelc.com/accounts/[account_id]/applications/[application_id]/incidents/[incident_id]|Apdex score fell below critical level of 0.90>'),
							array(
								'title' => 'Start Time',
								'value' => 'Jul 03, 2014 at 22:09:34 UTC',
								'short' => true),
							array(
								'title' => 'Severity',
								'value' => 'Critical',
								'short' => true),
								),
						'fallback' => '[application name] alert opened',
						'color' => 'd00000'))
			)));
			$this->assertEquals($expected, $this->instance->posted_messages);
		}

		#
		# Message with multiple servers and no application name
		#
		public function test_message_2() {
			$request = array(
				'post' => array(
					'alert' => json_encode(array(
						"created_at" => "2014-07-03T22:09:34+00:00",
					    "servers" => ["server1","my.server.local"],
					    "account_name" => "Account name",
					    "severity" =>"Critical",
					    "message"=>"Disk IO > 85%",
					    "short_description"=>"New alert on my.server.local",
					    "long_description"=>"Alert opened: Disk IO > 85% - Apps currently invlved: test1, test2, test3",
					    "alert_url"=>"http://PATH_TO_NEW_RELIC/accounts/nnn/incidents/nnn",
					    "server_events"=> Array("server"=>"my.server.local","created_at"=>"2014-03-04T22:41:07Z","message"=>"Disk IO > 85%")
					), true),
				)
			);

			$ret = $this->instance->onHook($request);

			$this->assertTrue($ret['ok']);

			$expected = array(array(
				'channel' => '#test',
				'message' => array(
					'text' => '[server1, my.server.local] New alert on my.server.local',
					'attachments' => array(array(
						'fields' => array(
							array(
								'title' => 'Message',
								'value' => '<http://PATH_TO_NEW_RELIC/accounts/nnn/incidents/nnn|Disk IO &gt; 85%>'),
							array(
								'title' => 'Start Time',
								'value' => 'Jul 03, 2014 at 22:09:34 UTC',
								'short' => true),
							array(
								'title' => 'Severity',
								'value' => 'Critical',
								'short' => true),
								),
						'fallback' => '[server1, my.server.local] New alert on my.server.local',
						'color' => 'd00000'))
			)));
			$this->assertEquals($expected, $this->instance->posted_messages);
		}
	}
