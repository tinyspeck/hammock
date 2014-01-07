<?php
	# this is the abstract class for data storage.
	# actual implementations will override it.

	class SlackData {

		function get($table, $key){
			return array();
		}

		function get_all($table){
			return array();
		}

		function set($table, $key, $value){
			return true;
		}

		function del($table, $key){
			return true;
		}

		function clear($table){
			return true;
		}


		# meta-api for making lists.
		# implementations could override this or not.

		function list_add($list, $item){
		#	$l = $this->get('lists', $list);
		#	if (!is_array($l) || !is_array($l['items'])){
		#		$l = array(
		#			'items' => array(),
		#		);
		#	}
		#	$l['items'][] = 
		}

		function list_remove($list, $item){
		}

		function list_clear($list){
		}
	}

