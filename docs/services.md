# Creating your own integration service

This document describes how to create new Slack integrations. If you're just 
looking to install an existing integration, copy the directory into the `plugins/`
directory.

If reading docs isn't your thing, take a look at the `github_commits` for a
simple webhook-to-message example.

The full [full reference documentation](services_ref.md) lists everything your
plugin can take advantage of.


## Bare bones

Create a new sub-directory inside `plugins/`. This will be the name of your service
plugin's class, so don't use any dashes (or anything fancy). Inside the directory,
create a file called `plugin.php` and enter some code:

	<?php

	class my_service extends SlackServicePlugin {
	}

This is the simplest service you can build. Load up the index page of your Slackware
install and you should see your new service listed. For now, it doesn't have a name
or description, so we'll fix that first:

	class my_service extends SlackServicePlugin {

		public $name = "My Awesome Service";
		public $desc = "This is what it does, yo";

After that, it should show up correctly in the services list. If you create an 
instance of it, you'll find it has "No information" to display. To display any 
information, instructions or settings you'll need to start providing some methods:

	function onView(){
		return "<p>this is my service</p>";
	}

Building HTML in code is tedious and fragile, so Slackware include Smarty for 
templating. Create a sub-directory in your plugin called `templates` and then create
`view.txt` inside that:

	<p>This is my service!</p>

	<p><a href="{$this->getEditUrl()}" class="btn">Edit settings</a></p>

We can then use this template from the PHP class:

	function onView(){
		return $this->smarty->fetch('view.txt');
	}

Each service object has a `$this->smarty` property which contains a pre-configured
Smarty instance.

Service plugins currently provide a 'view' and an 'edit' page. This will likely turn 
into a tabbed and combined view/edit page with custom tabs in the future.


## Webhooks

The simplest form of service plugin is to handle incoming webhooks. If you're going 
to provide a webhook URL, you should set this config property on your class:

	public $cfg = array(
		'has_token'        => true,
	);

This ensures that a randomized token is initialized for the service.

In your view template, present `{$this->getHookUrl()}` to users as the URL to use for
the inbound hook. When the hook is run, the class's `onHook` method will be called,
passing in information about the request:

	function onHook($req){

		# GET vars     : $req['get']
		# POST vars    : $req['post']
		# HTTP headers : $req['headers']
	}

Your code should _only_ use the data passed in `$req` and not use superglobals like
`$_GET` - hook code can be called by the replay-debugger in which case superglobals
will not be present.

Anything returned from the `onHook()` handler will be logged for later debugging, so
returning a simple text status about different conditions encountered can be very 
helpful.

Once you've processed the incoming data, you'll probably want to send a message into 
Slack. This can be done via the `postToChannel()` method:

	function onHook($req){

		$this->postToChannel($req['get']['text']);

		$this->postToChannel("hello", array(
			'channel'	=> 'C12398612345',
			'username'	=> 'Testbot',
		));
	}

The first argument is the text to post, while the optional second argument contains
a hash of options.

For a full list of all properties, methods and events, check the 
[full reference documentation](services_ref.md).

