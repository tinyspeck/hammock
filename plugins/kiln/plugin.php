<?php
//
// Kiln Commit Integration Web Hook
// =============================================================================
//
// Post commit messages from Kiln to a Slack chat room.
//
// Author: [Craig Davis](craig@there4development.com)
//
// For more information see:
// * http://kiln.stackexchange.com/questions/952/
// * http://kiln.stackexchange.com/questions/1345/
// * http://help.fogcreek.com/8111/web-hooks-integrating-kiln-with-other-services#Custom_Web_Hooks
//
// -----------------------------------------------------------------------------
//
class kiln extends SlackServicePlugin
{
    public $name = 'Kiln Commit Integration';
    public $desc = 'Provide commit summaries from Kiln';

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
        $this->icfg['botname']      = 'kiln';
        $this->icfg['max_messages'] = '5';
        $this->icfg['icon_url']     = $GLOBALS['cfg']['root_url'] . '/plugins/kiln/icon_48.png';
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
            $this->icfg['icon_url']     = $_POST['icon_url'];
            $this->icfg['max_messages'] = $_POST['max_messages'];
            $this->saveConfig();

            header("location: {$this->getViewUrl()}&saved=1");
            exit;
        }

        $this->smarty->assign('channels', $channels);
        return $this->smarty->fetch('edit.html');
    }

    public function onHook($req) {
        $payload        = json_decode($req['post']['payload'], true);
        $repositoryName = $payload['repository']['name'];
        $pusherName     = $payload['pusher']['fullName'];

        $commits        = array_reverse($payload['commits']);
        $commitCount    = count($commits);
        $commits        = array_slice($commits, 0, $this->icfg['max_messages']);

        $messageCount = 0;
        foreach ($commits as $commit) {
            $messageCount++;
            $commit = (object) $commit;

            // Fix a recent problem where the commit url comes from port 81
            $commit->url = str_replace(":81", "", $commit->url);

            // TODO: Handle commit messages with newlines
            $message = sprintf(
                '%s pushed to <%s|%s>: %s',
                $pusherName,
                $commit->url,
                $repositoryName,
                $commit->message
            );
            $this->sendMessage($message);

            // If this is a merge, just show the first message
            if (stripos($commit->message, 'merg') !== false) {
                break;
            }
        }

        if ($messageCount > $this->icfg['max_messages']) {
            $remaining        = $messageCount - $this->icfg['max_messages'];
            $commitInflection = ($remaining > 1) ? 'commits' : 'commit';
            $this->sendMessage("and $remaining other $commitInflection.");
        }

        return $logMessage;
    }

    private function sendMessage($text) {
        $ret = $this->postToChannel($text, array(
            'channel'  => $this->icfg['channel'],
            'username' => $this->icfg['botname'],
            'icon_url' => $this->icfg['icon_url']
        ));

        return array(
            'ok'     => true,
            'status' => 'Sent a message',
        );
    }
}

/* End of file plugin.php */
