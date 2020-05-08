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
require_once( dirname(__FILE__, 3) . "/vendor/scssphp/scssphp/scss.inc.php" );

use ScssPhp\ScssPhp\Compiler;

class Scss_Compiler {
    function compile($config, $variable_values, $output_dir) {
        $variables_file = '';
        $vars = array();

        foreach($variable_values as $key => $val) {
            $vars[] = array(
                'name' => $key,
                'value' => $val
            );
        }

        usort($vars, function($a, $b) use($config) {
            $aVar = $config->variables[$a['name']];
            $bVar = $config->variables[$b['name']];
            if($aVar->order === $bVar->order) {
                if($aVar->name == $bVar->name) {
                    return 0;
                }else {
                    return ($aVar->name > $bVar->name) ? 1 : -1;
                }
            } else {
                return $aVar->order - $bVar->order;
            }
        });

        $curr_map_name = null;
        $curr_mappings = array();
        foreach($vars as $v) {
            $matches = array();
            $pattern = '/([\w\-\_]+)(?:\(([\w\-\_]+)\))?/';

            preg_match($pattern, $v['name'], $matches);
            //var_dump($matches);
            $is_map = count($matches) == 3;
            $map_name = $is_map ? $matches[1] : null;
            $map_key = $is_map ? $matches[2] : null;

            if($curr_map_name !== $map_name && $curr_map_name !== null) {
                $variables_file .= $this->get_map_css($curr_map_name, $curr_mappings);
                $curr_mappings = null;
            }

            if($is_map && $map_name !== $curr_map_name) {
                $curr_mappings = $map_name;
                $curr_mappings = array();
                $curr_mappings[$map_key] = $v['value'];
            } elseif($is_map) {
                $curr_mappings[$map_key] = $v['value'];
            } else {
                $variables_file .= $this->get_variable_css($v['name'], $v['value']);
            }
        }
        //In case we ended with a map, have to flush the last one
        if($curr_map_name !== $map_name && $curr_map_name !== null) {
            $variables_file .= $this->get_map_css($curr_map_name, $curr_mappings);
        }

        $compiler = new Compiler();

        foreach($config->entrypoints as $src => $dest) {
            $import_path = dirname($src);
            $import_file = basename($src);
            $compiler->setImportPaths($import_path);
            $scss = $variables_file . '@import "' . $import_file . '";';
            $output = $compiler->compile($scss);
            $output_filename = md5($dest) . '.css';
            $output_location = trailingslashit($output_dir) . $output_filename;
            error_log('Writing compiled stylesheet to ' . $output_location);
            file_put_contents($output_location, $output);
        }


    }

    private function get_variable_css($name, $value) {
        return '$' . $name . ': ' . $value . ";\n";
    }

    private function get_map_css($map_name, $mappings) {
        $result = sprintf('$%1$s: (\n', $map_name);

        $i = 0;
        foreach($mappings as $key => $value) {
            $is_last = ++$i == count($mappings);
            $result .= sprintf('"%1$s": %2$s%3$s\n', $key, $value, $is_last ? ',' : '');
        }
        $result .= ');\n';
        return $result;
    }
}