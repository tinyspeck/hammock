<?php

namespace Slack\Command;

use ThreadMeUp\Slack\Client;
use ThreadMeUp\Slack\Webhooks\Incoming;

abstract class AbstractCommand implements CommandInterface
{

    /**
     * @var Client
     */
    protected $_client;

    /**
     * @var Incoming
     */
    protected $_message;

    /**
     * @var string
     */
    protected $_name = 'Unknown';

    /**
     * @var string
     */
    protected $_description;

    /**
     * @param Client $client
     * @param Incoming $message
     */
    public function __construct(Client $client, Incoming $message)
    {
        $this->_client = $client;
        $this->_message = $message;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->_description;
    }

} 