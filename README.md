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

Each integration resides in its own folder inside `plugins/`. Take a look at existing
integrations for details. (Real documentation to come, later).

For a simple webhook-to-message plugin, look at `github_commits`.


## TODO

So much left to do!

* Change OAuth to allow team selection at first login, then scope to team
* Style config pages to match current slack.com (somewhat done)
* Log all incoming webhooks and what we sent as a result (and allow replays)
* Plugins provide icons & default bot usernames
* Add 'Slackware' as a Slack service for bidi hooks
* Switch from using a webhook to an API method (needs to be implemented first)
* Tab the service config pages probably (Summary, Settings, plugin-defined)
* Do the cron stuff
