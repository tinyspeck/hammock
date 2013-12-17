<?php

	class SlackAuthPlugin {

		function saveConfig(){
			load_data();
			$GLOBALS['data']['auth'][$this->id] = $this->cfg;
			save_data();
		}

		function isUserAuthed(){

			return false;
		}

		function getConfigUrl(){

			return $GLOBALS['cfg']['root_url'] . 'auth.php?id=' . $this->id;
		}
	}
