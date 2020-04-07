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

class Settings_Manager {
    const PAGE_NAME = 'wp_style_customizer';
    const OPTION_NAME = 'wp_style_customizer_values';
    var $config_resolver;

    function __construct($config_resolver) {
        $this->config_resolver = $config_resolver;
    }

    function register_hooks() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
    }

    function add_admin_menu() {
        add_submenu_page('themes.php', 'Style Configuration', 'Style Configuration', 'manage_options', self::PAGE_NAME, array($this, 'admin_page') );
    }

    function register_settings() {
        register_setting(self::PAGE_NAME, self::OPTION_NAME);
        $configs = $this->config_resolver->get_resolved_configs();
        $configs = array_values($configs);
        $categories = [];

        array_map(function($conf) {
            array_map(function($var){
                $categories[$var->category] = true;
            }, $conf->variables);
        },$configs);

        $categories = array_keys($categories);
        array_map(function($c) {
            $cat_slug = self::PAGE_NAME . '_' . Utils::string_slugify_underscored($c);
            add_settings_section(
                $cat_slug,
                __($c, self::PAGE_NAME),
                array($this, 'render_setting_section'),
                self::PAGE_NAME
            );
        }, $categories);

        $variables = [];
        array_map(function($conf) {
            array_map(function($var){
                $variables[$var->name] = $var;
            }, $conf->variables);
        },$configs);

        $variables = array_values($variables);

        array_map(function($var) {
            $var_slug = self::PAGE_NAME . '_' . Utils::string_slugify_underscored($var->name);
            $cat_slug = self::PAGE_NAME . '_' . Utils::string_slugify_underscored($var->category);
            add_settings_field(
                $var_slug,
                __($var->title, self::PAGE_NAME),
                array($this, 'render_setting_field'),
                self::PAGE_NAME,
                $cat_slug,
                $var
            );
        }, $variables);

    }

    function render_setting_section($args) {
        sprintf('<h3>%1$s</h3', $args['title']);
    }

    function render_setting($args) {
        sprintf('<div>%1$s</div>', $args->title);
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