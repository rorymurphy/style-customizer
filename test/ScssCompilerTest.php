<?php
/*
Copyright (C) 2020 Rory Murphy
This is copyrighted software. Please see the LICENSE.TXT in the root of the project for permitted uses.
*/

namespace StyleCustomizer;

use PHPUnit\Framework\TestCase;

require_once(dirname(__FILE__, 2) . '/src/models/style-configuration.php');
require_once(dirname(__FILE__, 2) . '/src/models/style-template.php');
require_once(dirname(__FILE__, 2) . '/src/models/style-variable.php');
require_once(dirname(__FILE__, 2) . '/src/models/resolved-style-configuration.php');
require_once(dirname(__FILE__, 2) . '/src/compilers/scss-compiler.php');

class ScssCompilerTest extends TestCase
{
    public function testCssGeneration() {
        $template = new Style_Template();
        $template->type = 'scss';
        $template->variables[] = Style_Variable::withValues('foreground-color', 'Colors', 'color', 'Foreground Color', 'The primary color for text', '#000', null, '0');
        $template->variables[] = Style_Variable::withValues('background-color', 'Colors', 'color', 'Background Color', 'The primary color for the background', '#fff', null, '1');
        $template->variables[] = Style_Variable::withValues('font-size', 'scalar', 'Fonts', 'Base Font Size', 'The default text size', '12px', null, '2');

        $config = new Style_Configuration(array(
            'type' => 'scss',
            'template' => 'test-template',
            'variables' => array(
                Style_Variable::withValues('font-face', 'font', 'Fonts', 'Font Face', 'The default text font', 'Arial', null, '3')
            ),
            'entrypoints' => array(
                realpath(dirname(__FILE__, 1) . '/resources/basic-template.scss') => 'not/a/real/file.css'
            )
        ));

        $resolved_config = new Resolved_Style_Configuration($config, $template);
        $variable_values = array(
            'foreground-color' => '#333',
            'background-color' => '#ccf',
            'font-size' => '14px',
            'font-face' => 'Comic Sans'
        );

        $result = '';
        $compiler = new Scss_Compiler();
        $compiler->compile($resolved_config, $variable_values, function($src, $dest, $output) use(&$result){
            $result = $output;
        });

        $expected = <<<CSSOUT
body {
  background-color: #ccf;
  color: #333;
  font-size: 14px;
  font-family: Comic Sans; }
CSSOUT;

        $expected = str_replace("\r\n", "\n", $expected);
        $result = str_replace("\r\n", "\n", $result);

        $this->assertEquals(trim($expected), trim($result));
    }
}