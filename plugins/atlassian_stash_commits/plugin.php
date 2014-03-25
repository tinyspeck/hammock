<?php

class atlassian_stash_commits extends SlackServicePlugin {

    public $name = "Atlassian Stash Commits";
    public $desc = "Source control and code management.";

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

        $this->icfg['botname'] = 'stash';
    }

    function onView() {
        return $this->smarty->fetch('view.tpl');
    }

    function onEdit() {
        $channels = $this->getChannelsList();

        if ($_GET['save']) {
            $this->icfg['channel']      = $_POST['channel'];
            $this->icfg['base_url']     = $_POST['base_url'];
            $this->icfg['botname']      = $_POST['botname'];
            $this->icfg['channel_name'] = $channels[$_POST['channel']];
            $this->saveConfig();

            header("location: {$this->getViewUrl()}&saved=1");
            exit;
        }

        $this->smarty->assign('channels', $channels);

        return $this->smarty->fetch('edit.tpl');
    }

    function onHook($req) {
        if (!$this->icfg['channel']) {
            return array(
                'ok'    => false,
                'error' => "No channel configured",
            );
        }

        $stash_payload = json_decode($req['post_body']);

        if (!$stash_payload || !is_object($stash_payload) || !isset($stash_payload->changesets) || !isset($stash_payload->repository)) {
            return array(
                'ok'    => false,
                'error' => "No payload received from stash",
            );
        }

	    $fields  = array();
	    $author  = 'Unknown';
	    $commits = $stash_payload->changesets->values;
	    $project = $this->icfg['base_url'] . '/projects/' . $stash_payload->repository->project->key . '/repos/' . $stash_payload->repository->slug;

	    foreach($commits as $commit) {
            $fields[] = [
                'text' => sprintf(
                    '<%s|%s> - %s (%s files changed)',
                    $project . '/commits/' . $commit->toCommit->id,
                    $commit->toCommit->displayId,
                    $commit->toCommit->message,
                    $commit->changes->size
                ),
                'color' => 'good',
            ];

		    $author = $commit->toCommit->author->name;
	    }

	    $message = sprintf(
		    'New push on <%s|%s> by %s on branch %s (%s Commits)',
		    $project,
		    $stash_payload->repository->project->key . '/' . $stash_payload->repository->name,
		    $author,
		    str_replace('refs/heads/', '', $stash_payload->refChanges->refId),
		    count($fields)
	    );

        if (count($fields) > 0) {
            $this->postToChannel($message, [
                'channel'     => $this->icfg['channel'],
                'username'    => $this->icfg['botname'],
                'icon_url'    => $this->cfg['root_url'] . 'plugins/atlassian_stash_commits/icon_128.png',
                'attachments' => array_reverse($fields)
            ]);
        }

        return array(
            'ok'     => true,
            'status' => "Nothing found to report",
        );
    }

    function getLabel() {
        return "Post commits to {$this->icfg['channel_name']} as {$this->icfg['botname']}";
    }

}