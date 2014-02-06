<?php

class sentry extends SlackServicePlugin {

    public $name = "Sentry";
    public $desc = "yes, Yes Y'all";

    function onView(){
        return $this->smarty->fetch('view.html');
    }

    function onEdit(){

        $channels = $this->getChannelsList();

        if ($_GET['save']){

            $this->icfg['channel'] = $_POST['channel'];
            $this->icfg['channel_name'] = $channels[$_POST['channel']];
            $this->icfg['botname'] = $_POST['botname'];
            $this->saveConfig();

            header("location: {$this->getViewUrl()}&saved=1");
            exit;
        }

        $this->smarty->assign('channels', $channels);

        return $this->smarty->fetch('edit.html');
    }

    function onHook($req){

        $data = json_decode(file_get_contents('php://input'), true);
        $level = $this->escapeText(strtoupper($data['level']));
        $project_name = $this->escapeText($data['project_name']);
        $message = $this->escapeText($data['message']);
        $url = $this->escapeLink($data['url']);

        $text = "[{$level}] {$project_name} {$message} [{$url}]";
        $this->sendMessage($text);
    }


    private function sendMessage($text){

        $ret = $this->postToChannel($text, array(
                            'channel'       => $this->icfg['channel'],
                            'username'      => $this->icfg['botname'],
                    ));

        return array(
            'ok'        => true,
            'status'    => "Sent a message",
        );
    }

}
