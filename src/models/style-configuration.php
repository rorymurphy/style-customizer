<?php
/*
Copyright (C) 2020 Rory Murphy
This is copyrighted software. Please see the LICENSE.TXT in the root of the project for permitted uses.
*/

namespace StyleCustomizer;

class Style_Configuration {
    // Typed declarations for PHP 7.4+
    // string type;
    // string template;
    // // Must be an array of [Uncompiled Location] => [Compiled Location]
    // array entrypoints;
    // array variables;

    public $type;
    public $template;
    // Must be an array of [Uncompiled Location] => [Compiled Location]
    public $entrypoints;
    public $variables;

    function __construct($values) {
        $values = (array)$values;
        $this->type = (string)$values['type'];
        if(array_key_exists('template', $values)) {
            $this->template = (string)$values['template'];
        }

        $this->entrypoints = (array)$values['entrypoints'];
        $this->variables = (array)$values['variables'];
    }
}