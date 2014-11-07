<?php

namespace Slack\Command;

use ThreadMeUp\Slack\Client;
use ThreadMeUp\Slack\Webhooks\Incoming;

interface CommandInterface
{

    /**
     * @param Client $client
     * @param Incoming $message
     */
    public function __construct(Client $client, Incoming $message);

    /**
     * @return string
     */
    public function getName();

    /**
     * @return string
     */
    public function getDescription();

    /**
     * @param string $command
     * @return mixed
     */
    public function run($command);

} 