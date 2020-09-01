<?php

namespace StyleCustomizer;

use PHPUnit\Framework\TestCase;
use \Mockery;

require_once('WordPressFunctionMocks.php');
require_once(dirname(__FILE__, 2) . '/src/models/style-variable.php');
require_once(dirname(__FILE__, 2) . '/src/models/style-template.php');
require_once(dirname(__FILE__, 2) . '/src/stylesheet-compiler.php');
require_once(dirname(__FILE__, 2) . '/src/style-loader.php');
require_once(dirname(__FILE__, 2) . '/src/config-resolver.php');


class StyleLoaderTest extends TestCase {
    private $TEST_TEMPLATE_DIR;

    function __construct() {
        $TEST_TEMPLATE_DIR = dirname(__FILE__, 1) . '/resources/';
        parent::__construct();
    }
    public function testStyleSheetLoading() {
        $template = new Style_Template();
        $template->type = 'scss';
        $template->variables[] = Style_Variable::withValues('foreground-color', 'Colors', 'color', 'Foreground Color', 'The primary color for text', '#000', null, '0');
        $template->variables[] = Style_Variable::withValues('background-color', 'Colors', 'color', 'Background Color', 'The primary color for the background', '#fff', null, '1');
        $template->variables[] = Style_Variable::withValues('font-size', 'scalar', 'Fonts', 'Base Font Size', 'The default text size', '12px', null, '2');

        $get_bloginfo_callback = function($attr) {
            if('wpurl' == $attr) {
                return 'http://test.test/';
            }else {
                return null;
            }
        };

        $get_option_callback = function($option_name, $default_value = null) {
            if('wp_style_customizer_values' == $option_name) {
                return <<<'EOD'
{
    "foreground-color": "#333",
    "background-color": "#ccf",
    "font-size": "14px",
    "font-face": "Comic San"
}       
EOD;
            }else {
                return $default_value;
            }
        };

        $config = new Style_Configuration(array(
            'type' => 'scss',
            'template' => 'test-template',
            'variables' => array(
                Style_Variable::withValues('font-face', 'font', 'Fonts', 'Font Face', 'The default text font', 'Arial', null, '3')
            ),
            'entrypoints' => array(
                realpath($this->TEST_TEMPLATE_DIR . '/basic-template.scss') => 'not/a/real/file.css'
            )
        ));

        $style_compiler = Mockery::mock('StyleCustomizer\Stylesheet_Compiler');
        $style_compiler->shouldReceive('compile')->times(1);

        $config_resolver = new Config_Resolver($this->TEST_TEMPLATE_DIR);
        $style_loader = new Style_Loader($config_resolver, $style_compiler);

        global $wp_styles;
        $original_wp_styles = $wp_styles;
        $wp_styles = (Object)array('registered' => array(
            'test-stylesheet' => (Object)array('src' => 'not/a/real/file.css')
        ));

        $result = null;
        try{
            $result = $style_loader->filter_styles(array('test-stylesheet'));
        } finally {
            $wp_styles = $original_wp_styles;
        }

        $this->assertEquals(array('test-stylesheet'), $result);
    }
}
