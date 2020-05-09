<?php
/*
 Copyright (C) 2020 Rory Murphy

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU Affero General Public License as
    published by the Free Software Foundation, either version 3 of the
    License, or (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU Affero General Public License for more details.

    You should have received a copy of the GNU Affero General Public License
    along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

namespace StyleCustomizer;
use \Exception;

require_once('models/style-variable.php');
require_once('models/style-template.php');
require_once('models/style-configuration.php');
require_once('models/resolved-style-configuration.php');

class Style_Loader {
    const OPTION_NAME = 'wp_style_customizer_values';

    var $config_resolver;
    var $stylesheet_compiler;

    function __construct($config_resolver, $stylesheet_compiler) {
        $this->config_resolver = $config_resolver;
        $this->stylesheet_compiler = $stylesheet_compiler;
    }

    function register_hooks() {
        //add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts_and_styles'));
        add_filter('print_styles_array', array($this, 'filter_styles'));
    }

    /* Gets the filesystem location where generated files will be stored */
    protected function get_output_dir(){
        $uploaddir = wp_upload_dir();
        return $uploaddir['basedir'] . '/' . Style_Customizer::PLUGIN_NAME;
    }
    
    /* Gets the URL for the generated stylesheet directory */
    protected function get_output_url(){
        $uploaddir = wp_upload_dir();
        $result = $uploaddir['baseurl'] . '/' . Style_Customizer::PLUGIN_NAME;
        $result = is_ssl() ? str_replace('http://', 'https://', $result) : $result;
        return $result;
    }

    
    protected function get_css_filename($orig_filename, $version){
        return md5($orig_filename) . '.css';
    }

    function filter_styles($handles){
        global $wp_styles;
        $uploaddir = $this->get_output_dir();

        if(is_admin()){return $handles;}
        
        $configs = $this->config_resolver->get_resolved_configs();
        //Construct an array where the full url of the CSS file is the key and the LESS relative path is the value
        $style_urls = array();
        
        foreach($configs as $c){
            foreach($c->entrypoints as $src => $dest){
                $url = '';
                $content_dir = trailingslashit(ABSPATH) . 'wp-content/';
                if(substr($dest, 0, strlen($content_dir)) !== $content_dir) {
                    throw new Exception('Cannot process ' . $dest . '. Customized stylesheets must reside within the wp-content directory');
                }

                $url = str_replace($content_dir, trailingslashit(get_bloginfo('wpurl')) . 'wp-content/', $dest);
                $style_urls[$url] = $dest;
            }
        }

        // var_dump($style_urls);
        // var_dump($wp_styles->registered);
        foreach($handles as $h){
            $s = $wp_styles->registered[$h];

            if(array_key_exists($s->src, $style_urls)){
                $filename = $this->get_output_dir() . '/' . $this->get_css_filename($style_urls[$s->src], null);
                $url = $this->get_output_url() . '/' . $this->get_css_filename($style_urls[$s->src], null);
                printf('<!-- stylesheet %1$s :: %2$s-->', $s->src, $filename);
                if(!file_exists($filename)){
                    $values = get_option(self::OPTION_NAME, null);
                    if($values != null) {
                        foreach($configs as $config) {
                            $this->stylesheet_compiler->compile($config, $values);
                        }
                        $s->src = $url;
                    }

                }else{
                    $s->src = $url;
                }
            }
        }
        
        return $handles;
    }
}