{if $smarty.get.newtoken}
	<p class="alert">Your token has been updated - the webhook URL has changed!</p>
{/if}

<p>Go to your repo's settings page and add this hook URL:</p>

<p><code>{$this->getHookUrl()}</code></p>

<p><b>Post to channel:</b> {$this->icfg.channel_name|escape}</p>
<p><b>Bot name:</b> {$this->icfg.botname|escape}</p>
<p><b>Base url:</b> {$this->icfg.base_url|escape}</p>

<p><a href="{$this->getEditUrl()}" class="btn">Edit settings</a></p>