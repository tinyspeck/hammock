<?php
	class SlackDataRedis extends SlackData {

		function SlackDataRedis(){

			$this->redis = new Redis();

			$url = parse_url($GLOBALS['cfg']['redis_url']);

			$this->redis->connect($url['host'], $url['port']);

			if ($url['pass']) $this->redis->auth($url['pass']);
		}

		function get($table, $key){

			$ret = $this->redis->get("{$table}.{$key}");
			return $ret ? json_decode($ret, true) : null;
		}

		function get_all($table){

			$keys = $this->redis->keys("{$table}.*");

			$out = array();
			$pl = strlen($table)+1;

			foreach ($keys as $key){
				$rec_key = substr($key, $pl);
				$out[$rec_key] = $this->get($table, $rec_key);
			}
			return $out;
		}

		function set($table, $key, $value){

			$this->redis->set("{$table}.{$key}", json_encode($value));
			return true;
		}

		function del($table, $key){

			$this->redis->del("{$table}.{$key}");
			return true;
		}

		function clear($table){
			$keys = $this->redis->keys("{$table}.*");
			$this->redis->del($keys);
			return true;
		}
	}

	$GLOBALS['data'] = new SlackDataRedis();
