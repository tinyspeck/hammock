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
* Make sure `data/data.php` is writable by your web server
* Visit `index.php` in your browser and start configuring


## Adding integrations

Each integration resides in its own folder inside `plugins/`. Take a look at existing
integrations for details. (Real documentation to come, later).


## TODO

* Include tokens in webhooks, not just IDs (and allow changing them)
* Style config pages to match current slack.com
* Log all incoming webhooks and what we sent as a result
* Add proper Slack user auth
* Add 'Slackware' as a Slack service for bidi hooks
* Plugins provide icons & default bot usernames
