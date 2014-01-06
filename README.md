Slackware
=========

Slackware is a standalone webapp for running [Slack](https://slack.com) integrations.
This allows you to modify existing integrations, write new custom integrations, or use 
certain integrations inside your firewall.

Integrations written for Slackware use the same API as Slack itself, so contributing
new Integrations here will allow them to be added so the main Slack integrations list.


## Requirements

Slackware requires a webserver running a recent version of PHP. For integrations that
require polling, `cron` is also required (or `at`/`schtasks` on Windows).


## Installation

* Make a clone of this git repository onto your web server
* Copy `lib/config.php.example` to `lib/config.php`
* Open `lib/config.php` in a text editor and follow the instructions inside
* Make sure `data/` is writable by your web server
* Visit `index.php` in your browser and start configuring


## Adding integrations

To create your own intergrations [read the service docs](docs/services.md).

You can also check the [full service reference documentation](services_ref.md).


## TODO

So much left to do!

* Style config pages to match current slack.com (somewhat done)
* Log all incoming webhooks and what we sent as a result (and allow replays)
* Plugins provide icons & default bot usernames
* Add 'Slackware' as a Slack service for bidi hooks
* Tab the service config pages probably (Summary, Settings, plugin-defined)
* Do the cron stuff
