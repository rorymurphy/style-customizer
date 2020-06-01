<?php
/*
This is copyrighted software. Please see the LICENSE.TXT in the root of the project for permitted uses.
*/

namespace StyleCustomizer;

require_once('stylesheet-compiler.php');
class Settings_Manager {
    const PAGE_NAME = 'wp_style_customizer';
    const OPTION_NAME = 'wp_style_customizer_values';
    var $config_resolver;
    var $stylesheet_compiler;

    function __construct($config_resolver, $stylesheet_compiler) {
        $this->config_resolver = $config_resolver;
        $this->stylesheet_compiler = $stylesheet_compiler;
    }

    function register_hooks() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
    }

    function add_admin_menu() {
        add_submenu_page('themes.php', 'Style Configuration', 'Style Configuration', 'manage_options', self::PAGE_NAME, array($this, 'admin_page') );
    }

    function register_settings() {
        register_setting(self::PAGE_NAME, self::OPTION_NAME, array(
            'sanitize_callback' => array($this, 'compile_styles')
        ));
        $configs = $this->config_resolver->get_resolved_configs();
        $configs = array_values($configs);
        //var_dump($configs);

        $categories = array_unique(
            array_reduce( array_map(function($conf) {
                return array_values(array_map(function($var) {
                    return $var->category;
                }, $conf->variables));
            }, $configs), function($carry, $item){
                return array_merge($carry, $item);
            }, array())
        );

        //var_dump($categories);

        array_map(function($c) {
            $cat_slug = self::PAGE_NAME . '_' . Utils::string_slugify_underscored($c);
            add_settings_section(
                $cat_slug,
                __($c, self::PAGE_NAME),
                array($this, 'render_setting_section'),
                self::PAGE_NAME
            );
        }, $categories);

        $variables = array_reduce(
            array_map(function($conf) {
                return $conf->variables;
            }, $configs),
            function($carry, $item) {
                return array_merge($carry, $item);
            }, array()
        );

        array_map(function($var) {
            $var_slug = $var->name;//Utils::string_slugify_underscored($var->name);
            $cat_slug = self::PAGE_NAME . '_' . Utils::string_slugify_underscored($var->category);
            add_settings_field(
                $var_slug,
                __($var->title, self::PAGE_NAME),
                array($this, 'render_setting_field'),
                self::PAGE_NAME,
                $cat_slug,
                [
                    'label_for' => $var_slug,
                    'slug' => $var_slug,
                    'title' => $var->title,
                    'description' => $var->description,
                    'type' => $var->type
                ]
            );
        }, $variables);

    }

    function compile_styles($value) {
        $configs = $this->config_resolver->get_resolved_configs();
        foreach($configs as $config) {
            $this->stylesheet_compiler->compile($config, $value);
        }

        return $value;
    }

    function render_setting_section($args) {
        printf('', $args['title']);
    }

    function render_setting_field($args) {
        $options = get_option( self::OPTION_NAME );
        $slug = $args['slug'];
        $field_name = self::OPTION_NAME . '[' . $args['slug'] . ']';
        $value = array_key_exists($slug, $options) ? $options[$slug] : '';
        if($options) {
            printf('<input type="text" name="%1$s" value="%2$s"/>', $field_name, $value);
        }else {
            printf('<input type="text" name="%1$s" />', $field_name, $args['title']);
        }

    }

    function admin_page() {
        if(!current_user_can('manage_options')) {
            return;
        }

        // check if the user have submitted the settings
        // wordpress will add the "settings-updated" $_GET parameter to the url
        if ( isset( $_GET['settings-updated'] ) ) {
            // add settings saved message with the class of "updated"
            add_settings_error( self::PAGE_NAME . '_messages', self::PAGE_NAME . '_message', __( 'Settings Saved', self::PAGE_NAME ), 'updated' );
        }
        
        // show error/update messages
        settings_errors( self::PAGE_NAME . '_messages' );
        ?>
        <div class="wrap">
        <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
        <form action="options.php" method="post">
        <?php
        // output security fields for the registered setting "wporg"
        settings_fields( self::PAGE_NAME );
        // output setting sections and their fields
        // (sections are registered for "wporg", each field is registered to a specific section)
        do_settings_sections( self::PAGE_NAME );
        // output save settings button
        submit_button( 'Save Settings' );
        ?>
        </form>
        </div>
        <?php
    }
}