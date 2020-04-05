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
use \;

class Settings_Manager {
    const PAGE_NAME = 'wp_style_customizer';
    $config_resolver;

    function __construct($config_resolver) {
        $this->config_resolver = $config_resolver;
    }

    function register_hooks() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
    }

    function add_admin_menu() {
        add_submenu_page('themes.php', 'Style Configuration', 'Style Configuration', 'manage_options', self::PAGE_NAME, array($this, 'admin_page') );
    }

    function admin_page() {
        if(!current_user_can('manage_options')) {
            return;
        }

        // check if the user have submitted the settings
        // wordpress will add the "settings-updated" $_GET parameter to the url
        if ( isset( $_GET['settings-updated'] ) ) {
            // add settings saved message with the class of "updated"
            add_settings_error( self::PAGE_NAME . '_messages', self::PAGE_NAME . '_message', __( 'Settings Saved', 'wporg' ), 'updated' );
        }
        
        // show error/update messages
        settings_errors( self::PAGE_NAME . '_messages' );
    }
}