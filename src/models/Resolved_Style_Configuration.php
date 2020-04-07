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