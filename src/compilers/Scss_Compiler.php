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
            return $aVar->order - $bVar->order;
        });

        foreach($vars as $v) {
            $variables_file .= '$' . $v['name'] . ': ' . $v['value'] . ";\n";
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
}