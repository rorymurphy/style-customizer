<?php
/*
Copyright (C) 2020 Rory Murphy
This is copyrighted software. Please see the LICENSE.TXT in the root of the project for permitted uses.
*/

namespace StyleCustomizer;

class Style_Variable {
    // Typed declarations for PHP 7.4+
    // string name;
    // string category;
    // string type;
    // string title;
    // string description;
    // string default_value;
    // int order;
    public $name;
    public $category;
    public $type;
    public $title;
    public $description;
    public $default_value;
    public $value;
    public $order;

    public static function withValues($name, $category, $type, $title, $description, $default_value, $value, $order) {
        $inst = new Style_Variable();
        $inst->name = $name;
        $inst->category = $category;
        $inst->type = $type;
        $inst->title = $title;
        $inst->description = $description;
        $inst->default_value = $default_value;
        $inst->value = $value;
        $inst->order = $order;

        return $inst;
    }
}