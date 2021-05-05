<?php
/*
Copyright (C) 2020 Rory Murphy
This is copyrighted software. Please see the LICENSE.TXT in the root of the project for permitted uses.
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
                require_once 'compilers/scss-compiler.php';
                $compiler = new Scss_Compiler();
                break;
            default:
                throw new Exception('Unrecognized stylesheet type');
        }
        $output_dir = $this->output_dir;
        $output_closure = function($src, $dest, $output) use ($output_dir) {
            $output_filename = md5($dest) . '.css';
            $output_location = trailingslashit($output_dir) . $output_filename;
            error_log('Writing compiled stylesheet to ' . $output_location);
            file_put_contents($output_location, $output);
        };

        $compiler->compile($config, $variable_values, $output_closure);
    }
}