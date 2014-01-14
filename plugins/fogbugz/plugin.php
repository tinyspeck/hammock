<?php
//
// FogBugz Case Events Web Hook
// =============================================================================
//
// Post case opening events from FogBugz to a Slack chat room.
//
// Author: [Craig Davis](craig@there4development.com)
//
// -----------------------------------------------------------------------------
//
class fogbugz extends SlackServicePlugin
{
    public $name = 'FogBugz Case Integration';
    public $desc = 'Provide case open notifications';

    public $cfg = array(
        'has_token' => true,
    );

    public function onInit() {
        $channels = $this->getChannelsList();
        foreach ($channels as $channel => $name) {
            if ($name == '#general') {
                $this->icfg['channel'     ] = $channel;
                $this->icfg['channel_name'] = $name;
            }
        }
        $this->icfg['botname']  = 'fogbugz';
        $this->icfg['icon_url'] = $GLOBALS['cfg']['root_url'] . '/plugins/fogbugz/icon_48.png';
    }

    public function onView() {
        return $this->smarty->fetch('view.html');
    }

    public function getLabel() {
        return "Post commits to {$this->icfg['channel_name']} as {$this->icfg['botname']}";
    }

    public function onEdit() {
        var_dump($this->icfg);
        $channels = $this->getChannelsList();

        if ($_GET['save']) {
            $this->icfg['channel']      = $_POST['channel'];
            $this->icfg['channel_name'] = $channels[$_POST['channel']];
            $this->icfg['botname']      = $_POST['botname'];
            $this->icfg['icon_url']      = $_POST['icon_url'];
            $this->saveConfig();

            header("location: {$this->getViewUrl()}&saved=1");
            exit;
        }

        $this->smarty->assign('channels', $channels);
        return $this->smarty->fetch('edit.html');
    }

    public function onHook($req) {
        $chatMessage = sprintf(
            'Opened <https://learningstation.fogbugz.com/default.asp?%1$d|Case %1$d>: %2$s',
            $req['get']['CaseNumber'],
            $req['get']['Title']
        );
        $logMessage = sprintf('Opened Case %d', $req['get']['CaseNumber']);
        $this->sendMessage($chatMessage);

        return $logMessage;
    }

    private function sendMessage($text) {
        $ret = $this->postToChannel($text, array(
            'channel'  => $this->icfg['channel'],
            'username' => $this->icfg['botname'],
            'icon_url' => $this->icfg['icon_url'],
        ));

        return array(
            'ok'     => true,
            'status' => 'Sent a message',
        );
    }
}

/* End of file plugin.php */
