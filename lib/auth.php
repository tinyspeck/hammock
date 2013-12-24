<?php

	class SlackAuthPlugin {

		function saveConfig(){
			$GLOBALS['data']->set('auth', $this->id, $this->cfg);
		}

		function isUserAuthed(){

			return false;
		}

		function getConfigUrl(){

			return $GLOBALS['cfg']['root_url'] . 'auth.php?id=' . $this->id;
		}
	}
