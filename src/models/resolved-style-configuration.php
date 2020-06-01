<?php
/*
Copyright (C) 2020 Rory Murphy
This is copyrighted software. Please see the LICENSE.TXT in the root of the project for permitted uses.
*/

namespace StyleCustomizer;

class Resolved_Style_Configuration {
    // string type;
    // // Must be an array of [Uncompiled Location] => [Compiled Location]
    // array entrypoints;
    // array variables;

    public $type;
    // Must be an array of [Uncompiled Location] => [Compiled Location]
    public $entrypoints;
    public $variables;
    
    function __construct(Style_Configuration $config, Style_Template $template) {
        $this->type = $config->type;
        $this->entrypoints = $config->entrypoints;
        $this->variables = [];
        if($template && $template->variables) {
            if($template->type != $this->type) {
                throw new Exception('Styles template type must match the configuration type');
            }
            array_map(function($var) {
                $this->variables[$var->name] = $var;
            }, $template->variables);
        }
        array_map(function($var) {
            $this->variables[$var->name] = $var;
        }, $config->variables);
    }


}