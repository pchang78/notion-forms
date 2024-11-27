<?php
/**
 * Plugin Name: Notion Forms
 * Description: Integrate Notion databases as forms in WordPress.
 * Version: 1.0.0
 * Author: Patrick Chang
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

// Define constants.
define('NOTION_FORMS_PATH', plugin_dir_path(__FILE__));
define('NOTION_FORMS_URL', plugin_dir_url(__FILE__));

// Include necessary files.
require_once NOTION_FORMS_PATH . 'includes/admin/settings-page.php';
require_once NOTION_FORMS_PATH . 'includes/admin/main-page.php';
require_once NOTION_FORMS_PATH . 'includes/db/create-table.php';

// Register activation hook to create the database table.
register_activation_hook(__FILE__, 'notion_forms_create_table');

// Register the admin menu.
function notion_forms_register_menu() {
    add_menu_page(
        'Notion Forms',
        'Notion Forms',
        'manage_options',
        'notion-forms',
        'notion_forms_main_page',
        'dashicons-feedback',
        20
    );

    add_submenu_page(
        'notion-forms',
        'Settings',
        'Settings',
        'manage_options',
        'notion-forms-settings',
        'notion_forms_settings_page'
    );
}
add_action('admin_menu', 'notion_forms_register_menu');