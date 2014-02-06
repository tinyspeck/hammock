<?php

class coveralls extends SlackServicePlugin {

    public $name = "Coveralls";
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

        preg_match('/.*\/coveralls_(\d+)\.png/', $req['post']['badge_url'], $matches);
        $percentage = $matches[1];

        $text = $this->escapeText("Coverage: {$percentage}% | ");
        //$this->sendMessage($text);

        $text .= $this->escapeText("{$req['post']['commit_message']} by {$req['post']['committer_name']}");
        $text .= $this->escapeText(" (");
        $text .= $this->escapeLink($req['post']['url']);
        $text .= $this->escapeText(" )");
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
