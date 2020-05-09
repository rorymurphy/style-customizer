<?php
/*
Plugin Name: Style Customizer
Description: Gives users the power to tweak styles based on LESS stylesheets.
Author: Rory Murphy
Author URI: https://github.com/rorymurphy/
Version: 1.0.0
License: Affero GPL v3
*/
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

require_once('src/config-resolver.php');
require_once('src/settings-manager.php');
require_once('src/style-loader.php');
require_once('src/utils.php');

class Style_Customizer {
    const PLUGIN_NAME = 'style-customizer';


    const CURRENT_SETTINGS_OPTION = self::PLUGIN_NAME . '-settings';

    var $style_loader = null;
    var $config_resolver = null;
    var $settings_manager = null;
    var $stylesheet_compiler = null;

    function __construct() {
        $uploads = wp_upload_dir();
        $output_dir = trailingslashit($uploads['basedir']) . self::PLUGIN_NAME . '/';
        $this->config_resolver = new Config_Resolver();
        $this->stylesheet_compiler = new Stylesheet_Compiler($output_dir);
        $this->style_loader = new Style_Loader($this->config_resolver, $this->stylesheet_compiler);
        $this->settings_manager = new Settings_Manager($this->config_resolver, $this->stylesheet_compiler);

        $this->register_hooks();
    }
    
    function register_hooks() {
        add_filter(Config_Resolver::CONFIG_FILTER_NAME, array($this, 'register_theme_config'));
        $this->style_loader->register_hooks();
        $this->settings_manager->register_hooks();        
    }

    function register_theme_config($configs){
        $stylesheet_dir = trailingslashit(get_stylesheet_directory());
        $stylesheet_uri = get_stylesheet_uri();
        $file_name = $stylesheet_dir . self::PLUGIN_NAME . '-config.json';
        if(file_exists($file_name)){
            $template_values = json_decode(file_get_contents($file_name));
            $entrypoints = array();
            foreach($template_values->entrypoints as $path => $dest) {
                $resolved_path = $stylesheet_dir . $path;
                $resolved_path = realpath($resolved_path);

                if(substr($resolved_path, 0, strlen($stylesheet_dir)) !== $stylesheet_dir) {
                    throw new Exception('Style customizer configs cannot refer to files outside the current theme');
                }

                $dest_path = $stylesheet_dir . $dest;
                $dest_path = realpath($dest_path);
                if(substr($dest_path, 0, strlen($stylesheet_dir)) !== $stylesheet_dir) {
                    throw new Exception('Style customizer configs cannot refer to files outside the current theme');
                }

                //$resolved_url = $stylesheet_uri . $url;
                $entrypoints[$resolved_path] = $dest_path;
            }
            $template_values->entrypoints = $entrypoints;

            $configs[] = new Style_Configuration($template_values);       
            return $configs;
        }
    }
}

$customizer = new Style_Customizer();