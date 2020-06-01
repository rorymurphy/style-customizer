<?php
/*
Copyright (C) 2020 Rory Murphy
This is copyrighted software. Please see the LICENSE.TXT in the root of the project for permitted uses.
*/

namespace StyleCustomizer;

require_once('models/style-variable.php');
require_once('models/style-template.php');
require_once('models/style-configuration.php');
require_once('models/resolved-style-configuration.php');

class Config_Resolver {
    const CONFIG_FILTER_NAME = Style_Customizer::PLUGIN_NAME . '-configurations';
    var $configs = null;

    function __construct() {

    }

    function get_resolved_configs(){
        if(!$this->configs) {
            $configs = apply_filters(self::CONFIG_FILTER_NAME, array());
            $this->configs = array_map(function($c){

                $resolvedConfig = null;
                if($c->template){
                    $template_filename = sprintf('%1$s/templates/%2$s.json', realpath(__DIR__), $c->template);
                    $template_filename = realpath($template_filename);
                    $contents = file_get_contents($template_filename);
                    $template = json_decode($contents);
                    $template->on_deserializing();

                    $resolvedConfig = new Resolved_Style_Configuration($c, $template);
                }else{
                    $resolvedConfig = new Resolved_Style_Configuration($c, new Style_Template());
                }
                return $resolvedConfig;
            }, $configs);
        }
        return $this->configs;
    }
}