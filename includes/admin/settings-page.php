<?php

function form_sync_for_notion_settings_page() {

    if(!form_sync_for_notion_is_setup()) {
        form_sync_for_notion_setup_page();
        return;
    }

    require_once FORM_SYNC_FOR_NOTION_PATH . 'includes/admin/admin-header.php';

    ?>
    <div class="wrap" id="form-sync-for-notion-container">
        <h1>Form Sync for Notion Settings</h1>
        <?php 
        // Display settings errors or success messages.
        settings_errors('form_sync_for_notion_messages'); 
        ?>
        <form method="post" action="options.php">
            <?php
            settings_fields('form_sync_for_notion_settings');
            do_settings_sections('form-sync-for-notion-settings');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

// Register settings for the plugin.
function form_sync_for_notion_register_settings() {
    add_settings_section(
        'form_sync_for_notion_main_settings',
        'Main Settings',
        null,
        'form-sync-for-notion-settings'
    );

    add_settings_field(
        'notion_form_api_key',
        'Notion API Key',
        'form_sync_for_notion_api_key_callback',
        'form-sync-for-notion-settings',
        'form_sync_for_notion_main_settings'
    );

    add_settings_field(
        'notion_form_database_url',
        'Notion Database URL',
        'form_sync_for_notion_database_url_callback',
        'form-sync-for-notion-settings',
        'form_sync_for_notion_main_settings'
    );

    register_setting('form_sync_for_notion_settings', 'form_sync_for_notion_api_key', [
        'sanitize_callback' => 'sanitize_text_field',
        'type' => 'string',
        'show_in_rest' => false,
        'default' => '',
    ]);

    register_setting('form_sync_for_notion_settings', 'form_sync_for_notion_database_url', [
        'sanitize_callback' => 'esc_url_raw',
        'type' => 'string',
        'show_in_rest' => false,
        'default' => '',
    ]);




}
add_action('admin_init', 'form_sync_for_notion_register_settings');

// Callbacks for settings fields.
function form_sync_for_notion_api_key_callback() {
    $value = get_option('form_sync_for_notion_api_key', '');
    echo '<input type="text" name="form_sync_for_notion_api_key" value="' . esc_attr($value) . '" class="regular-text">';
}

function form_sync_for_notion_database_url_callback() {
    $value = get_option('form_sync_for_notion_database_url', '');
    echo '<input type="text" name="form_sync_for_notion_database_url" value="' . esc_attr($value) . '" class="regular-text">';
}