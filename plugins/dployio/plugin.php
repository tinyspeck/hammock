<?php

class dployio extends SlackServicePlugin {

    public $name = "Dploy.io deployments";
    public $desc = "Automatic deployments by Dploy.io.";

    public $cfg = array(
        'has_token' => true,
    );

    function onInit() {
        $channels = $this->getChannelsList();

        foreach ($channels as $k => $v) {
            if ($v == '#general') {
                $this->icfg['channel']      = $k;
                $this->icfg['channel_name'] = $v;
            }
        }

        $this->icfg['botname'] = 'dploy.io';
    }

    function onView() {
        return $this->smarty->fetch('view.tpl');
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

        return $this->smarty->fetch('edit.tpl');
    }

    function onHook($req) {
        global $cfg;

        if (!$this->icfg['channel']) {
            return array(
                'ok'    => false,
                'error' => "No channel configured",
            );
        }

        $dploy_payload = json_decode($req['post_body']);

        if (!$dploy_payload || !is_object($dploy_payload)) {
            return array(
                'ok'    => false,
                'error' => "No payload received from dploy.io",
            );
        }

        if (is_null($dploy_payload->deployed_at)) {
            $message = '[%s] started deployment to %s environment on %s.';
        } else {
            $message = '[%s] was deployed to %s environment on %s.';
        }

        $message = sprintf(
            $message,
            str_replace('.git', '', $dploy_payload->repository),
            $dploy_payload->environment,
            $dploy_payload->server
        );

        $this->postToChannel($message, array(
            'channel'     => $this->icfg['channel'],
            'username'    => $this->icfg['botname'],
            'icon_url'    => $cfg['root_url'] . 'plugins/dployio/icon_128.png'
        ));
    }

    function getLabel() {
        return "Post commits to {$this->icfg['channel_name']} as {$this->icfg['botname']}";
    }

}
