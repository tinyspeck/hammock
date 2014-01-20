# Service Plugin Reference

This documentation lists all of the currently available properties and methods
for custom `SlackServicePlugin` classes. This will be expanded as more are added.


## Properties

There are 6 core properties for services:

	$this->id;   # class name
	$this->iid;  # unique instance ID 

	$this->cfg;  # static class config
	$this->icfg; # instance config

	$this->name; # service name
	$this->desc; # service description

You'll never set the `id` and `iid` props, but they are sometimes useful.

The `cfg` hash is used to toggle on certain functionality. The only currently
supported flag is `has_token` which makes sure `icfg->token` is populated and
provides a UI for resetting the token.

The `icfg` hash is where you store instance properties. You'll need to call
`$this->saveConfig();` to save any changes you make here (except in `onInit`).

The `name` and `desc` props should not be changed at run time.

Each plugin is also provided with a Smarty instance at `$this->smarty`. It is 
configured to use templates from the `templates` sub-directory of your plugin.
It already hs the plugin instance assigned as `$this`.

Since UI methods expect HTML to be returned rather than output directly, be
usre to use `->fetch()` rather than `->display()`.


## Methods to override

### onInit()

This is called when a new instance of a plugin is created. This is the place to
populate `$this->icfg` with any default values. You _don't_ need to call
`$this->saveConfig();` to save these changes (that happens automatically).

Any `$cfg` based options will have been applied before this method is called,
so (for example) `$icfg->token` will have already been set.

### onView()

Called when a user clicks on an instance of the service. Should return informational
HTML, optionally with a link to the 'edit' view.

### onEdit()

Called for editing the service config.

### onHook($req)

Called when the service's webhook URL is requested, or by the replay-debugger.

### getLabel()

Should return a user-friendly description of the service instance, based on config 
information. For example, a service that posts Github commits to a channel might 
return a line of text describing the source repo and the target channel (e.g. "Post 
commits from Hammock to #hammock").


## Methods to call

### getViewUrl() / getEditUrl() / getHookUrl()

Returns the various URLs for the service instance. Never try and build these yourself.

### dump()

Dump the contents of the service instance as HTML. Use this rather than calling `print_r`
on the object since this method first disconnects the Smarty object to avoid recursive 
loops.

### saveConfig()

Make sure that changes to `$this->icfg` are persisted. Your should call this after 
modifying the instance config in onView, onEdit or onHook.

### postToChannel($text, $extra)

Send a message to a Slack channel. This method currently expects your messages to be 
[properly escaped](https://api.slack.com/docs/formatting), but this will be optional
in the future.

The first argument is the message to post. The second optional array argument can 
contain a `channel` to post to and a `username` to post as.

### escapeText($str)

Takes any text string and returns one escaped to display as-is in Slack

### escapeLink($url, $label)

Takes a URL and (optionally) a label and makes a valid Slack link.

### getChannelsList()

Returns a hash of `{ID -> Name}` for public channels in your Slack instance. This can
be used to present a list of channels to choose from when configuring an instance.

