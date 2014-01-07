Slackware
=========

Slackware is a standalone webapp for running [Slack](https://slack.com) integrations.
This allows you to modify existing integrations, write new custom integrations, or use 
certain integrations inside your firewall.

Integrations written for Slackware use the same API as Slack itself, so contributing
new Integrations here will allow them to be added so the main Slack integrations list.


## Requirements

Slackware requires a webserver running a recent version of PHP.  
Ror integrations that require polling, `cron` is also required
(or `at`/`schtasks` on Windows).


## Installation

* Make a clone of this git repository onto your web server
* Copy `lib/config.php.example` to `lib/config.php`
* Open `lib/config.php` in a text editor and follow the instructions inside
* Make sure `data/` is writable by your web server
* Visit `index.php` in your browser and start configuring


## Adding integrations

To create your own intergrations [read the service docs](docs/services.md).

You can also check the [full service reference documentation](services_ref.md).


## Roadmap

This version of Slackware is pretty barebones, designed to support simple webhook-to-slack
style integrations first. To better support this, we'll be adding a replay-debugger for 
capturing incoming webhooks and being able to replay them in a read-only mode while 
developing.

Future plugins will be able to provide cross-plugin authentication, so that e.g. a GitHub
integration can auth you against GitHub once and then allow you to add multiple different
integrations for code, issues, gists, etc. and share the credentials. This will be supported
by a different subclass of plugins.

The visual appearance of Slackware somewhat matches the Slack services pages, but this will
be changed to more closely match, have building blocks for commmon UI elements, and switch
the the planned tabbed interface for integration config.

For integrations that require some kind of polling, Slackware will support polling callbacks
and handle some API call diffing behavior automatically. Using this mechanism, an integration
can register a method to be called when the results of an external API call change.

We also plan to support integrations that are triggered from within Slack, via slash commands
and other user-initiated actions.
