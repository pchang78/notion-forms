<?php
/*
Plugin Name: Form Sync for Notion
Description: Integrate Notion databases as forms in WordPress.
Version: 1.0.0
Author: Patrick Chang
Author URI: https://everydaytech.tv/wp/
License: GPLv2 or later
*/

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

// Define constants.
define('FORM_SYNC_FOR_NOTION_PATH', plugin_dir_path(__FILE__));
define('FORM_SYNC_FOR_NOTION_URL', plugin_dir_url(__FILE__));

// Include necessary files.
require_once FORM_SYNC_FOR_NOTION_PATH . 'includes/admin/plugin-startup.php';
require_once FORM_SYNC_FOR_NOTION_PATH . 'includes/admin/settings-page.php';
require_once FORM_SYNC_FOR_NOTION_PATH . 'includes/admin/confirmation-page.php';
require_once FORM_SYNC_FOR_NOTION_PATH . 'includes/admin/styles-page.php';
require_once FORM_SYNC_FOR_NOTION_PATH . 'includes/admin/documentation-page.php';
require_once FORM_SYNC_FOR_NOTION_PATH . 'includes/admin/refresh-fields.php';
require_once FORM_SYNC_FOR_NOTION_PATH . 'includes/admin/main-page.php';
require_once FORM_SYNC_FOR_NOTION_PATH . 'includes/db/create-post-type.php';
require_once FORM_SYNC_FOR_NOTION_PATH . 'includes/frontend-form.php';

// Register activation hook to create the database table.
register_activation_hook(__FILE__, function() {
    // Create custom post types
    form_sync_for_notion_create_post_types();
    
    // Set default confirmation message if it doesn't exist
    if (!get_option('form_sync_for_notion_confirmation_content')) {
        add_option('form_sync_for_notion_confirmation_content', 'Thank you for your submission.');
    }
});

// Register post types
add_action('init', 'form_sync_for_notion_create_post_types');

// Register the admin menu.
function form_sync_for_notion_register_menu() {
    add_menu_page(
        'Form Sync for Notion',
        'Form Sync for Notion',
        'manage_options',
        'form-sync-for-notion',
        'form_sync_for_notion_main_page',
        plugins_url('assets/notion-forms-icon.png', __FILE__),
        22
    );


    add_submenu_page(
        'form-sync-for-notion',
        'Form Builder',
        'Form Builder',
        'manage_options',
        'form-sync-for-notion',
        'form_sync_for_notion_main_page'
    );



    add_submenu_page(
        'form-sync-for-notion',
        'Confirmation Page',
        'Confirmation Page',
        'manage_options',
        'form-sync-for-notion-confirmation',
        'form_sync_for_notion_confirmation_page'
    );

    
    add_submenu_page(
        'form-sync-for-notion',              // Parent slug
        'Styles',                   // Page title
        'Styles',                   // Menu title
        'manage_options',           // Capability
        'form-sync-for-notion-styles',      // Menu slug
        'form_sync_for_notion_styles_page' // Callback function
    );

    add_submenu_page(
        'form-sync-for-notion',
        'Settings',
        'Settings',
        'manage_options',
        'form-sync-for-notion-settings',
        'form_sync_for_notion_settings_page'
    );

    add_submenu_page(
        'form-sync-for-notion',
        'Documentation',
        'Documentation',
        'manage_options',
        'form-sync-for-notion-documentation',
        'form_sync_for_notion_documentation_page'
    );


}
add_action('admin_menu', 'form_sync_for_notion_register_menu');





function form_sync_for_notion_enqueue_scripts($hook) {


    switch($hook) {
        case "toplevel_page_form-sync-for-notion":
            wp_enqueue_script('jquery-ui-sortable');
            wp_enqueue_script(
                'form-sync-for-notion-drag-drop',
                plugins_url('js/notion-forms-drag-drop.js', __FILE__),
                ['jquery', 'jquery-ui-sortable'],
                '1.0',
                true
            );

            wp_enqueue_script(
                'form-sync-for-notion-admin-js',
                plugin_dir_url(__FILE__) . 'js/form-sync-for-notion-admin.js',
                [],
                '1.0.0',
                true
            );

            break;


        case "form-sync-for-notion_page_form-sync-for-notion-styles":
            wp_enqueue_script(
                'form-sync-for-notion-admin-js',
                plugin_dir_url(__FILE__) . 'js/form-sync-for-notion-copy-prompt.js',
                [],
                '1.0.0',
                true
            );
            break;


        case "form-sync-for-notion_page_form-sync-for-notion-confirmation":
            wp_enqueue_editor();
            break;
    }

    $screen = get_current_screen();
    if ($screen && ($screen->id === 'toplevel_page_form-sync-for-notion' || strpos($screen->id, 'form-sync-for-notion') !== false)) {
        wp_enqueue_style('wp-admin');
        wp_enqueue_style('form-sync-for-notion-style', plugins_url('css/form-sync-for-notion-style.css', __FILE__));
    }
}
add_action('admin_enqueue_scripts', 'form_sync_for_notion_enqueue_scripts');

function form_sync_for_notion_enqueue_styles() {
    // Only load the CSS on the Form Sync for Notion admin pages
    $screen = get_current_screen();
    if ($screen && strpos($screen->id, 'form-sync-for-notion') !== false) {
        wp_enqueue_style(
            'form-sync-for-notion-styles', // Handle
            plugin_dir_url(__FILE__) . 'css/styles.css', // Path to the CSS file
            [], // Dependencies (none)
            '1.0.0' // Version
        );
    }
}
add_action('admin_enqueue_scripts', 'form_sync_for_notion_enqueue_styles');


function form_sync_for_notion_admin_msg($message) {
?>
    <div class="notice notice-success is-dismissible"> <p><?php echo esc_html($message); ?></p> </div>
<?php
}
