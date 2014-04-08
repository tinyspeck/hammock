<?php
	class SlackDataFiles extends SlackData {

		private $cache = array();

		function get($table, $key){

			if (!isset($this->cache[$table])){
				$this->cache[$table] = $this->load($table);
			}

			return $this->cache[$table] ? $this->cache[$table][$key] : null;
		}

		function get_all($table){

			if (!isset($this->cache[$table])){
				$this->cache[$table] = $this->load($table);
			}

			return $this->cache[$table];
		}

		function set($table, $key, $value){

			$this->cache[$table] = $this->load($table);
			$this->cache[$table][$key] = $value;
			$this->save($table, $this->cache[$table]);
			return true;
		}

		function del($table, $key){

			$this->cache[$table] = $this->load($table);
			unset($this->cache[$table][$key]);
			$this->save($table, $this->cache[$table]);
			return true;
		}

		function clear($table){
			$this->cache[$table] = array();
			$this->save($table, $this->cache[$table]);
			return true;
		}

		###

		private function load($table){

			$table_enc = urlencode($table);
			$path = HAMMOCK_ROOT."/data/data_{$table_enc}.php";

			$data = array();
			if (file_exists($path)){
				$fh = fopen($path, 'r');
				if (!$fh) die("Failed to open data file for reading");

				$flag = 0;
				$ok = flock($fh, LOCK_SH);
				if (!$ok) die("Failed to locl data file for reading");

				include($path);
				flock($fh, LOCK_UN);
				fclose($fh);

				if (!is_array($data)) $data = array();
			}
			return $data;
		}

		private function save($table, $data){

			$table_enc = urlencode($table);
			$path = HAMMOCK_ROOT."/data/data_{$table_enc}.php";

			$fh = fopen($path, 'c');
			if (!$fh) die("Failed to open data file for writing");

			$retries = 5;
			for ($i=0; $i<$retries; $i++){
				$flag = 0;
				$ok = flock($fh, LOCK_EX | LOCK_NB, $flag);
				if ($ok) break;
				if (!$flag) die("Failed to lock data file");
				sleep(1);
			}
	                if (!$ok) die("Failed to lock data file");

			ftruncate($fh, 0);
			fwrite($fh, "<"."?php \$data = ".var_export($data, true).';');

			flock($fh, LOCK_UN);
			fclose($fh);
		}
	}

	$GLOBALS['data'] = new SlackDataFiles();
