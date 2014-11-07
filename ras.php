<?php

require_once "vendor/autoload.php";

use ThreadMeUp\Slack\Client as Slack;
use Slack\Command\CommandException;
use Slack\Command\CommandInterface;

$config = [
    'token' => 'xoxp-2760649163-2762475502-2955876209-14c3a7',
    'team' => 'Runashop',
    'username' => 'Runashop',
    'icon' => 'ICON', // Auto detects if it's an icon_url or icon_emoji
    'parse' => '',
];

try {
    $slack = new Slack($config);

    $incoming = $slack->listen();

    if ($incoming) {
        $text = trim($incoming->text());
        $tokens = preg_split('/\s+/', $text);
        if (count($tokens) > 0) {
            $commandText = array_shift($tokens);
            $commandClass = '\\Slack\\Command\\' . ucfirst($commandText);
            if (!class_exists($commandClass)) {
                throw new RuntimeException('No such command: ' . $commandText);
            }
            $commandText = join(' ', $tokens);
            /** @var CommandInterface $command */
            $command = new $commandClass($slack, $incoming);
            if (!$command instanceof CommandInterface) {
                throw new RuntimeException('No such command: ' . get_class($command));
            }
            if (strtolower($commandText) === 'help') {
                $incoming->respond($command->getDescription());
            }
            else {
                $command->run($commandText);
            }
        }
        else {
            $incoming->respond('Hello! :)');
        }
    }
} catch (CommandException $e) {
    die($e->getCommand()->getName() . ' Command failed with message: ' . $e->getMessage());
} catch (Exception $e) {
    die('Unknown error: ' . $e->getMessage());
}