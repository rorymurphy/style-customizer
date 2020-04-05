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
use \;

require_once('models/Style_Variable.php');
require_once('models/Style_Template.php');
require_once('models/Style_Configuration.php');
require_once('models/Resolved_Style_Configuration.php');

class Config_Resolver {
    const CONFIG_FILTER_NAME = Style_Customizer::PLUGIN_NAME . '-configurations';
    $configs = null;

    function get_resolved_configs(){
        if($this->configs) {
            $configs = apply_filters(self::CONFIG_FILTER_NAME, array());
            $this->configs = array_map(function($c){
                $c = (Style_Configuration)$c;
                $resolvedConfig = null;
                if($c->template){
                    $template_filename = sprintf('%1$s/templates/%2$s.json', realpath(__DIR__), $c->template);
                    $template_filename = realpath($template_filename);
                    $contents = file_get_contents($template_filename);
                    $template = (StyleTemplate)json_decode($contents);
                    $template->on_deserializing();

                    $resolvedConfig = new Resolved_Style_Configuration($c, $template);
                }else{
                    $resolvedConfig = new Resolved_Style_Configuration($c, null);
                }
                return $resolvedConfig;
            }, $configs);
        }
        return $this->configs;
    }
}