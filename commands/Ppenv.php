<?php

namespace Slack\Command;

class Ppenv extends AbstractCommand implements CommandInterface
{

    /**
     * @var string
     */
    protected $_name = 'Priceportal Environments List';

    /**
     * @var string
     */
    protected $_description = <<<EOT
Command to get Runashop environments IPs.
You can use it without arguments or providing exact environment key:

uspp
eupp
uspp2
eupp2
eupp2t
euppt

EOT;


    /**
     * @param string $command
     * @return mixed
     */
    public function run($command)
    {
        $hosts = array(
            'uspp' => 'US PP1',
            'eupp' => 'EU PP1',
            'uspp2' => 'US PP2',
            'eupp2' => 'EU PP2',
            'eupp2t' => 'EU PP2 Test',
            'euppt' => 'EU PP1 Test',
        );

        $originalHosts = $hosts;
        $response = '';
        if (!empty($command) && array_key_exists($command, $hosts)) {
            $hosts = array_intersect_key($hosts, array_flip([$command]));
        }
        if (!empty($hosts)) {
            foreach ($hosts as $host => $env) {
                $response .= $env . ': ' . gethostbyname($host . '.runashop.net') . PHP_EOL;
            }
        }
        else {
            $response = 'Nothing found. Wrong PP environment. Correct values: ' . PHP_EOL . join(PHP_EOL, array_keys($originalHosts));
        }
        $this->_message->respond($response);
    }

} 