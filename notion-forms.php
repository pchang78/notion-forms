<?php
/*
Plugin Name: Notion Forms
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
define('NOTION_FORMS_PATH', plugin_dir_path(__FILE__));
define('NOTION_FORMS_URL', plugin_dir_url(__FILE__));

// Include necessary files.
require_once NOTION_FORMS_PATH . 'includes/admin/plugin-startup.php';
require_once NOTION_FORMS_PATH . 'includes/admin/settings-page.php';
require_once NOTION_FORMS_PATH . 'includes/admin/confirmation-page.php';
require_once NOTION_FORMS_PATH . 'includes/admin/styles-page.php';
require_once NOTION_FORMS_PATH . 'includes/admin/documentation-page.php';
require_once NOTION_FORMS_PATH . 'includes/admin/refresh-fields.php';
require_once NOTION_FORMS_PATH . 'includes/admin/main-page.php';
require_once NOTION_FORMS_PATH . 'includes/db/create-post-type.php';
require_once NOTION_FORMS_PATH . 'includes/frontend-form.php';

// Register activation hook to create the database table.
register_activation_hook(__FILE__, function() {
    // Create custom post types
    notion_forms_create_post_types();
    
    // Set default confirmation message if it doesn't exist
    if (!get_option('notion_forms_confirmation_content')) {
        add_option('notion_forms_confirmation_content', 'Thank you for your submission.');
    }
});

// Register post types
add_action('init', 'notion_forms_create_post_types');

// Register the admin menu.
function notion_forms_register_menu() {
    add_menu_page(
        'Notion Forms',
        'Notion Forms',
        'manage_options',
        'notion-forms',
        'notion_forms_main_page',
        plugins_url('assets/notion-forms-icon.png', __FILE__),
        22
    );


    add_submenu_page(
        'notion-forms',
        'Confirmation Page',
        'Confirmation Page',
        'manage_options',
        'notion-forms-confirmation',
        'notion_forms_confirmation_page'
    );

    
    add_submenu_page(
        'notion-forms',              // Parent slug
        'Styles',                   // Page title
        'Styles',                   // Menu title
        'manage_options',           // Capability
        'notion-forms-styles',      // Menu slug
        'notion_forms_styles_page' // Callback function
    );

    add_submenu_page(
        'notion-forms',
        'Settings',
        'Settings',
        'manage_options',
        'notion-forms-settings',
        'notion_forms_settings_page'
    );

    add_submenu_page(
        'notion-forms',
        'Documentation',
        'Documentation',
        'manage_options',
        'notion-forms-documentation',
        'notion_forms_documentation_page'
    );


}
add_action('admin_menu', 'notion_forms_register_menu');





function notion_forms_enqueue_scripts($hook) {
    switch($hook) {
        case "toplevel_page_notion-forms":
            wp_enqueue_script('jquery-ui-sortable');
            wp_enqueue_script(
                'notion-forms-drag-drop',
                plugins_url('js/notion-forms-drag-drop.js', __FILE__),
                ['jquery', 'jquery-ui-sortable'],
                '1.0',
                true
            );
            wp_enqueue_script(
                'notion-forms-admin-js',
                plugin_dir_url(__FILE__) . 'js/notion-forms-admin.js',
                [],
                '1.0.0',
                true
            );
            break;

        case "notion-forms_page_notion-forms-confirmation":
            wp_enqueue_editor();
            break;
    }

    $screen = get_current_screen();
    if ($screen && ($screen->id === 'toplevel_page_notion-forms' || strpos($screen->id, 'notion-forms') !== false)) {
        wp_enqueue_style('wp-admin');
        wp_enqueue_style('notion-forms-style', plugins_url('css/notion-forms-style.css', __FILE__));
    }
}
add_action('admin_enqueue_scripts', 'notion_forms_enqueue_scripts');

function notion_forms_enqueue_styles() {
    // Only load the CSS on the Notion Forms admin pages
    $screen = get_current_screen();
    if ($screen && strpos($screen->id, 'notion-forms') !== false) {
        wp_enqueue_style(
            'notion-forms-styles', // Handle
            plugin_dir_url(__FILE__) . 'css/styles.css', // Path to the CSS file
            [], // Dependencies (none)
            '1.0.0' // Version
        );
    }
}
add_action('admin_enqueue_scripts', 'notion_forms_enqueue_styles');


function notion_forms_admin_msg($message) {
?>
    <div class="notice notice-success is-dismissible"> <p><?php echo esc_html($message); ?></p> </div>
<?php
}
