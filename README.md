Hammock
=========

Hammock is a standalone webapp for running [Slack](https://slack.com) integrations.
This allows you to modify existing integrations, write new custom integrations, or use 
certain integrations inside your firewall.

Integrations written for Hammock use the same API as Slack itself, so contributing
new Integrations here will allow them to be added to the main Slack integrations list.


## Requirements

Hammock requires a webserver running a recent version of PHP.  
For integrations that require polling, `cron` is also required
(or `at`/`schtasks` on Windows).


## Installation

* Make a clone of this git repository onto your web server
* Copy `lib/config.php.example` to `lib/config.php`
* Open `lib/config.php` in a text editor and follow the instructions inside
* Make sure `data/` is writable by your web server
* Visit `index.php` in your browser and start configuring


## Heroku

You can run Hammock on Heroku using the following commands (you'll need  to have installed
the Heroku toolbelt already):

    cd hammock
    heroku create
    heroku config:set BUILDPACK_URL=https://github.com/heroku/heroku-buildpack-php.git#redis
    heroku config:set HAMMOCK_ROOT=http://{URL-TO-APP}/
    heroku config:set HAMMOCK_CLIENT_ID={YOUR-CLIENT-ID}
    heroku config:set HAMMOCK_CLIENT_SECRET={YOUR-CLIENT-SECRET}
    heroku addons:add redistogo
    git push heroku master

All config options are loaded from the environment variables and data is stored in Redis.


## Adding integrations

To create your own integrations [read the service docs](docs/services.md).

You can also check the [full service reference documentation](docs/services_ref.md).


## Roadmap

This version of Hammock is pretty barebones, designed to support simple webhook-to-Slack
style integrations first. To better support this, we'll be adding a replay-debugger for 
capturing incoming webhooks and being able to replay them in a read-only mode while 
developing.

Future plugins will be able to provide cross-plugin authentication, so that e.g. a GitHub
integration can auth you against GitHub once and then allow you to add multiple different
integrations for code, issues, gists, etc. and share the credentials. This will be supported
by a different subclass of plugins.

The visual appearance of Hammock somewhat matches the Slack services pages, but this will
be changed to more closely match, have building blocks for commmon UI elements, and switch
the the planned tabbed interface for integration config.

For integrations that require some kind of polling, Hammock will support polling callbacks
and handle some API call diffing behavior automatically. Using this mechanism, an integration
can register a method to be called when the results of an external API call change.

We also plan to support integrations that are triggered from within Slack, via slash commands
and other user-initiated actions.
