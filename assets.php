<?php

function smarty_static_assets() {

        $GLOBALS['_smarty_js_files'] = array();
        $GLOBALS['_smarty_css_files'] = array();

        function smarty_add_js($tag_attrs, &$compiler){
                $_params = $compiler->_parse_attrs($tag_attrs);
                $group = $_params['group'] ? $_params['group'] : "'regular'";
                $code = "\$GLOBALS['_smarty_js_files'][{$group}][] = {$_params['file']};";
                return $code;
        }

        function smarty_add_css($tag_attrs, &$compiler){
                $_params = $compiler->_parse_attrs($tag_attrs);
                $group = $_params['group'] ? $_params['group'] : "'regular'";
                $code = "\$GLOBALS['_smarty_css_files'][{$group}][] = {$_params['file']};";
                return $code;
        }

        $GLOBALS['smarty']->register_compiler_function('add_js' , 'smarty_add_js');
        $GLOBALS['smarty']->register_compiler_function('add_css', 'smarty_add_css');

        #######################################################################################

        function smarty_output_js($args){

                $group = isset($args['group']) ? $args['group'] : 'regular';
                echo "<!-- output_js \"{$group}\" -->\n";

                $files = $GLOBALS['_smarty_js_files'][$group];
                if (!is_array($files)) return;

                foreach ($files as $file){
                        $full = $GLOBALS['cfg']['root_url'] . "plugins/{$group}/assets/{$file}";
                        echo "<script type=\"text/javascript\" src=\"{$full}\"></script>\n";
                }
        }

        function smarty_output_css($args){

                $group = isset($args['group']) ? $args['group'] : 'regular';
                echo "<!-- output_css \"{$group}\" -->\n";

                $files = $GLOBALS['_smarty_css_files'][$group];
                if (!is_array($files)) return;

                foreach ($files as $file){
                        $full = $GLOBALS['cfg']['root_url'] . "plugins/{$group}/assets/{$file}";
                        echo "{$indent}\twindow.async_css_urls.push('{$full}')\n";
                }
        }

        $GLOBALS['smarty']->register_function('output_js' , 'smarty_output_js');
        $GLOBALS['smarty']->register_function('output_css', 'smarty_output_css');
}