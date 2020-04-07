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

require_once('models/Style_Variable.php');
require_once('models/Style_Template.php');
require_once('models/Style_Configuration.php');
require_once('models/Resolved_Style_Configuration.php');

class Style_Loader {
    var $config_resolver;

    function __construct($config_resolver) {
        $this->config_resolver = $config_resolver;
    }

    function register_hooks() {
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts_and_styles'));
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

    
    protected function get_css_filename($css_url, $version){
        $wpurl = get_bloginfo('wpurl');
        if(strpos($css_url, get_bloginfo('wpurl')) === 0){
            $css_url = '~/' . substr($css_url, strlen($wpurl));
        }
        return md5($css_url) . '-' . $version . '.css';
    }

    function filter_styles($handles){
        global $wp_styles;
        $uploaddir = $this->get_output_dir();

        if(is_admin()){return $handles;}
        
        $configs = $this->config_resolver->get_resolved_configs();
        //Construct an array where the full url of the CSS file is the key and the LESS relative path is the value
        $style_urls = array();
        
        foreach($configs as $c){
            foreach($c->entrypoints as $key => $value){
                $url = '';
                if(preg_match('/^https?\:\/\//', $value)){
                    $url = $value;   
                }else{
                    $url = Utils::canonicalize(substr($c->url, 0, strrpos($c->url, '/')) . '/' . $value);
                }

                $style_urls[] = $url;
            }
        }
        foreach($handles as $h){
            $s = $wp_styles->registered[$h];
            if(in_array($s->src, $style_urls)){
                $filename = $this->get_output_dir() . '/' . $this->get_css_filename($s->src, null);
                printf('<!-- stylesheet %1$s :: %2$s-->', $s->src, $this->get_css_filename($s->src, null));
                if(file_exists($filename)){
                    $s->src = $this->get_output_url() . '/' . $this->get_css_filename($s->src, null);
                }
            }
        }
        
        return $handles;
    }
}