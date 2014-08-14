<?php

class yo extends SlackServicePlugin {

    public $name = "Yo";
    public $desc = "Zero character communication.";

    public $cfg = array(
        'has_token' => true,
    );

    function onInit() {
        $channels = $this->getChannelsList();

        foreach ($channels as $k => $v) {
            if ($v == '#yo') {
                $this->icfg['channel']      = $k;
                $this->icfg['channel_name'] = $v;
            }
        }

        $this->icfg['botname'] = "Yo";


        
        $this->icfg['icon_url'] = trim($GLOBALS['cfg']['root_url'], '/') . '/plugins/yo/icon_48.png';
    }

    function onView() {
        return $this->smarty->fetch('view.txt');
    }

    function onEdit() {
        $channels = $this->getChannelsList();
        if ($_GET['save']) {
            $this->icfg['channel']      = $_POST['channel'];
            $this->icfg['channel_name'] = $channels[$_POST['channel']];
            $this->icfg['botname']      = $_POST['botname'];
            $this->saveConfig();

            header("location: {$this->getViewUrl()}&saved=1");
            exit;
        }
        $this->smarty->assign('channels', $channels);
        return $this->smarty->fetch('edit.txt');
    }

    function onHook($request) {

        if (!$request['get']['username']){
            return array('ok' => false, 'error' => "Invalid payload");
        }

        if (!$this->icfg['channel']) {
            return array('ok' => false, 'error' => "No channel configured");
        }

        $message = 'Yo from *'.$request['get']['username'].'*';

        $this->postToChannel($message, array(
            'channel'	=> $this->icfg['channel'],
            'username'	=> $this->icfg['botname'],
            'icon_url'	=> $this->icfg['icon_url'],
        ));
    }

    function getLabel() {
        return "Post notifications to {$this->icfg['channel_name']} as {$this->icfg['botname']}";
    }
}
