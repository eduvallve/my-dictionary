<?php

/**
 * Functions (Front-office)
 */

require_once 'functions.global.php';
require_once 'functions.database.php';
require_once 'functions.page-inspect.php';

if (is_admin()) {
    function my_dictionary_plugin() {
        createDB_pluginTables();
    }

    add_action('wp_enqueue_scripts', 'my_dictionary_plugin');
}


if (!is_admin()) {
    require_once 'functions.menu-language-switcher.php';
    require_once 'functions.page-replace.php';

    add_filter( 'locale', function() {
        return getCurrentLanguage();
    });

    add_filter('the_content','replace_page_content');
    wp_enqueue_script('my_custom_script', plugin_dir_url(__DIR__) . 'ui/js/ui.js');
    wp_enqueue_style('my_custom_style', plugin_dir_url(__DIR__) . 'ui/css/ui.css');
}

?>