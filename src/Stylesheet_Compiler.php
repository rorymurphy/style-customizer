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

class Stylesheet_Compiler {
    var $output_dir;
    function __construct($output_dir) {
        $this->output_dir = $output_dir;
    }

    function compile($config, $variable_values) {
        $compiler;
        if(!file_exists($this->output_dir)) {
            mkdir($this->output_dir, 0777, true);
        }
        switch(strtolower($config->type)) {
            case 'scss':
                require 'compilers/Scss_Compiler.php';
                $compiler = new Scss_Compiler();
                break;
            default:
                var_dump($config);
                throw new Exception('Unrecognized stylesheet type');
        }

        $compiler->compile($config, $variable_values, $this->output_dir);
    }
}