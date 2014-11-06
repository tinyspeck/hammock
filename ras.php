<?php

use ThreadMeUp\Slack\Client as Slack;

$config = [
    'token' => 'xoxp-2760649163-2762475502-2955876209-14c3a7',
    'team' => 'Runashop',
    'username' => 'Runashop',
    'icon' => 'ICON', // Auto detects if it's an icon_url or icon_emoji
    'parse' => '',
];

$slack = new Slack($config);

$incoming = $slack->listen();

if ($incoming) {
    $incoming->respond('Ololo trololo');
}