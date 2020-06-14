<?php
/*
Copyright (C) 2020 Rory Murphy
This is copyrighted software. Please see the LICENSE.TXT in the root of the project for permitted uses.
*/

global $is_admin_callback;
$is_admin_callback = function() {return false;};

function is_admin() {
    global $is_admin_callback;
    return $is_admin_callback();
}

global $apply_filters_callback;
$apply_filters_callback = function($filter_name, $value) {
    global $apply_filters_callback;
    return $value;
};

function apply_filters($filter_name, $value) {
    global $apply_filters_callback;
    return $apply_filters_callback($filter_name, $value);
}

global $get_bloginfo_callback;
$get_bloginfo_callback = function($attr) {
    global $get_bloginfo_callback;
    return null;
};

function get_bloginfo($attr) {
    return $get_bloginfo_callback($attr);
}


global $get_option_callback;
$get_option_callback = function($option_name, $default_value = null) {
    global $get_option_callback;
    return $default_value;
};

function get_option($option_name, $default_value = null) {
    return $get_option_callback($option_name, $default_value);
}

global $wp_upload_dir_callback;
$wp_upload_dir_callback = function() { return array('basedir' => __DIR__ . '/uploads'); };

function wp_upload_dir() {
    global $wp_upload_dir_callback;
    return $wp_upload_dir_callback();
}

global $is_ssl_value;
$is_ssl_value = false;
function is_ssl() {
    global $is_ssl_value;
    return $is_ssl_value;
}