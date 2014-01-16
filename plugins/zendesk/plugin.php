<?php

	class zendesk extends SlackServicePlugin {

		public $name = "Zendesk";
		public $desc = "Posts updates from Zendesk to Slack"; 

		public $target_name = "Slack Webhook";
		public $trigger_name = "Post new tickets to Slack";

		public $hook_url = "";		# TODO: populate this
		public $zendesk_url = "";	# TODO: populate this

		function onInit() {

			$this->icfg['botname'] = 'zendesk';
			$this->hook_url = "http://requestb.in/w4g8yjw4";
			$this->zendesk_url = "https://slack1387747815.zendesk.com/";

		}

		function onView() {

			return $this->smarty->fetch('view.txt');

		}

		function onEdit() {

			$channels = $this->getChannelsList();

			if ($_GET['save']){

				$this->icfg['channel'] = $_POST['channel'];
				$this->icfg['channel_name'] = $channels[$_POST['channel']];
				$this->icfg['botname'] = $_POST['botname'];
				$this->saveConfig();

				header("location: {$this->getViewUrl()}&saved=1");
				exit;
			}

			$this->smarty->assign('channels', $channels);

			return $this->smarty->fetch('edit.txt');

		}

		function onHook($req) {

			# The payload will come in as a wad of JSON that is a valid payload for
			# an attachments-bearing message. Make a boring text message out of the
			# data for now.
			$payload = json_decode($req['get']['payload'], true);
			$message = $payload['text'] . "\n" . $payload['attachments'][0]['fallback'];
			$this->postToChannel($message, null);

		}

		function getLabel() {

			return "Posts ticket changes to {$this->icfg['channel_name']} as {$this->icfg['botname']}";

		}

		##########################################################################
		# 
		# The Target is the action that Zendesk will invoke to send a message. The
		# end user won't ever need or want to customize this.
		#
		private function create_or_update_target() {

			if (!is_admin()) return false;
			
			#
			# Decide whether to update an existing target or create a new one.
			#
			$ret = json_decode(zendesk_call('targets.json'), true);
			$exists = null;
			foreach ($ret['targets'] as $target) {
				if ($target['title'] == $target_name) {
					$exists = $target['id'];
					continue;
				}
			}

			#
			# Values are the same regardless of whether we're creating or updating.
			# 
			$target_values = array(
				'target' => array(
					'title' => $target_title,
					'type' => 'url_target',
					'active' => true,
					'target_url' => $hook_url,
					'method' => 'POST',
					'attribute' => 'payload',
				),
			);

			#
			# This is the call for creating a new target from scratch.
			#
			if ($exists == null) {
				$created = json_decode(zendesk_call('targets.json', $target_values), true);
				if (isset($created['error'])) {
					return false;
				}

				return $created['target']['id'];

			}

			#
			# Target already exists. Update it to make sure it has the correct URL.
			#
			else {

				$ch = curl_init($zendesk_url . 'api/v2/targets/' . $exists . '.json');
				# TODO: Add auth
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
				curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Length: ' . strlen(json_encode($target_values, true))));
				curl_setopt($ch, CURLOPT_POSTFIELDS, $target_values);
	
				$updated = json_decode(curl_exec($ch), true);
				curl_close($ch);

				if (isset($updated['error'])) {
					return false;
				}

				return $updated['target']['id'];

			}
				
		}

		##########################################################################
		# 
		# The Trigger is the conditional statement that Zendesk runs to determine
		# whether it should notify the Target.
		#
		private function create_or_update_trigger() {

			$ret = json_decode(zendesk_call('triggers.json'), true);
			$exists = null;

			foreach ($ret['triggers'] as $trigger) {
				if ($trigger['title'] == $trigger_name) {
					$exists = $trigger['id'];
					continue;
				}
			}

			#
			# Messing with triggers is dicey business. For now we'll just throw an
			# error here and let the user sort it out. The best solution is probably
			# to 1) compare the triggers and see if they're identical, 2) if 
			# different, copy the existing trigger to a new one and disable it to 
			# prevent duplicate notifications, and 3) update the existing trigger. 
			# That'll preserve any customization the user might have done and it'll
			# give the user a way to start fresh if they messed up the trigger we
			# set up originally.
			#
			if ($exists) {
				# TODO: Tell the user that they already have a trigger named 
				# $trigger_name and we did not create a new one for them
				return false;
			}
			else {

				#
				# There's a lot of data in here. Build it up in bits. Here's the basic
				# stuff.
				#
				$trigger = array(
					'title' => $trigger_name,
					'active' => true,
					'position' => 0,
				);

				#
				# This trigger should fire on every newly created ticket
				#
				$conditions = array(
					array('all' => 
						array(
							array(
								'field' => 'status',
								'operator' => 'is',
								'value' => 'new',
							),
							array(
								'field' => 'update_type',
								'operator' => 'is',
								'value' => 'Create',
							)
						)
					)
				);
				$trigger['conditions'] = $conditions;

				#
				# The trigger will send a tiny sprinkling of JSON to the Target
				#
				$message_template = '{\"text\":\"New ticket from {{ticket.requester.name}} at {{ticket.organization.name}}: <{{ticket.link}}|Ticket {{ticket.id}}>\",\"attachments\":[{\"color\":\"#f5ca00\",\"fallback\":\"{{ticket.title}}\\n\\n{{ticket.latest_comment}}\",\"fields\":[{\"title\":\"Subject\",\"value\":\"{{ticket.title}}\",\"short\":false},{\"title\":\"Comment\",\"value\":\"{{ticket.latest_comment}}\",\"short\":false}]}]}"';
				$actions = array(
					array(
						'field' => 'notification_target',
						'value' => array(
							$target_id,
							$message_template,
						),
					),
				);
				$trigger['actions'] = $actions;

				$ret = json_decode(zendesk_call('triggers.json', array('trigger' => $trigger)), true);

			}
		}

		##########################################################################
		# 
		# The authenticated user needs to be an admin to create Targets and Triggers
		# 
		#
		private function is_admin() {

			$ret = json_decode(zendesk_call('users/me.json'), true);

			if ($ret['role'] == 'admin') {
				return true;
			}

			return false;
		}

		##################################################################
		#
		# TODO: The user needs to be authed somehow.
		#
		private function zendesk_call($endpoint, $post_data = array(), $verbose = false) {
	
			$ch = curl_init();
			curl_setopt($ch, $zendesk_url . $endpoint);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_VERBOSE, $verbose);
			if (count($post_data) > 0) {
				$post_data = json_encode($post_data);
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
				curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    			'Content-Type: application/json',
    			'Content-Length: ' . strlen($post_data)));
			}
			else {
				curl_setopt($ch, CURLOPT_HTTPGET, true);
			}
	
			$ret = curl_exec($ch);
			return $ret;
	
		}
	}

?>
