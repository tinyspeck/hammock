<?php
// TestFlight Integration Hook
//
class testflight extends SlackServicePlugin
{
  public $name = 'TestFlight';
  public $desc = 'Provide Support For TestFlight Webhooks';
  
	public $cfg = array(
		'has_token'	=> true,
	);
  
  public function onInit() {
      $channels = $this->getChannelsList();
      foreach ($channels as $channel => $name) {
          if ($name == '#general') {
              $this->icfg['channel'     ] = $channel;
              $this->icfg['channel_name'] = $name;
          }
      }
      $this->icfg['botname']      = 'TestFlight';
      $this->icfg['icon_url']     = trim($GLOBALS['cfg']['root_url'], '/') . '/plugins/testflight/icon_48.png';
  }

  public function onView() {
      return $this->smarty->fetch('view.html');
  }

  public function getLabel() {
      return "Post Builds to {$this->icfg['channel_name']} as {$this->icfg['botname']}";
  }

  public function onEdit() {
      var_dump($this->icfg);
      $channels = $this->getChannelsList();

      if ($_GET['save']) {
          $this->icfg['channel']       = $_POST['channel'];
          $this->icfg['channel_name']  = $channels[$_POST['channel']];
          $this->icfg['botname']       = $_POST['botname'];
          $this->icfg['icon_url']      = $_POST['icon_url'];
          $this->saveConfig();

          header("location: {$this->getViewUrl()}&saved=1");
          exit;
      }

      $this->smarty->assign('channels', $channels);
      return $this->smarty->fetch('edit.html');
  }

  public function onHook($req) {
      $log_message = '';
      
      $tf_payload = json_decode($req['post'], true);
      
      if ($tf_payload && count($tf_payload)) {
        # we have a payload
        $title = trim($tf_payload['title']);
        $link_href = trim($tf_payload['build']['url_html']);

        $chat_message = sprintf("%s \n Build Number (%d) %s", $title, $tf_payload['build']['id'], $this->escapeLink($link_href, "Install Build"));
        $this->sendMessage($chat_message);
      } else {
        ob_start();
        var_dump($req);
        $result = ob_get_clean();
        return array(
          'ok' => false,
          'error' => "No payload received from TestFlight",
          'debug' => $result
        );
      }
      return $log_message;
  }

  private function sendMessage($text) {
      $ret = $this->postToChannel($text, array(
          'channel'  => $this->icfg['channel'],
          'username' => $this->icfg['botname'],
          'icon_url' => $this->icfg['icon_url'],
      ));

      return array(
          'ok'     => true,
          'status' => 'Sent a message: ' . $text,
      );
  }
}