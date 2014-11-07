<?php

namespace Slack\Command;

use Exception;

class CommandException extends \Exception
{

    /**
     * @var CommandInterface
     */
    protected $_command;

    /**
     * @param CommandInterface $command
     * @param string $message
     * @param int $code
     * @param Exception $previous
     */
    public function __construct(CommandInterface $command, $message = "", $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->_command = $command;
    }

    /**
     * @return CommandInterface
     */
    public function getCommand()
    {
        return $this->_command;
    }

    /**
     * @param CommandInterface $command
     * @return $this
     */
    public function setCommand($command)
    {
        $this->_command = $command;
        return $this;
    }

} 