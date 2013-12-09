<?php

	function load_plugins(){

		$GLOBALS['plugins'] = array();

		$dir = dirname(__FILE__)."/../plugins";

		if ($dh = opendir($dir)){
			while (($file = readdir($dh)) !== false){

				if (is_dir("{$dir}/{$file}") && is_file("{$dir}/{$file}/plugin.php")){

					if ((include("{$dir}/{$file}/plugin.php"))){

						$GLOBALS['plugins'][$file] = new $file();
					}
				}
			}
			closedir($dh);
		}
	}


	function dumper($foo){
		
		echo "<pre style=\"text-align: left;\">";
		if (is_resource($foo)){
			var_dump($foo);
		}else{
			echo HtmlSpecialChars(var_export($foo, 1));
		}
		echo "</pre>\n";
	}


	class SlackPlugin {
	}

