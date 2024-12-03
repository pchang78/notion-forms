<?php

function notion_forms_settings_page() {


    require_once NOTION_FORMS_PATH . 'includes/admin/admin-header.php';

    ?>
    <div class="wrap" id="notion-forms-container">
        <h1>Notion Forms Settings</h1>
        <?php 
        // Display settings errors or success messages.
        settings_errors('notion_forms_messages'); 
        ?>
        <form method="post" action="options.php">
            <?php
            settings_fields('notion_forms_settings');
            do_settings_sections('notion-forms-settings');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

// Register settings for the plugin.
function notion_forms_register_settings() {
    add_settings_section(
        'notion_forms_main_settings',
        'Main Settings',
        null,
        'notion-forms-settings'
    );

    add_settings_field(
        'notion_form_api_key',
        'Notion API Key',
        'notion_forms_api_key_callback',
        'notion-forms-settings',
        'notion_forms_main_settings'
    );

    add_settings_field(
        'notion_form_database_url',
        'Notion Database URL',
        'notion_forms_database_url_callback',
        'notion-forms-settings',
        'notion_forms_main_settings'
    );

    register_setting('notion_forms_settings', 'notion_form_api_key', [
        'sanitize_callback' => 'sanitize_text_field',
        'type' => 'string',
        'show_in_rest' => false,
        'default' => '',
    ]);

    register_setting('notion_forms_settings', 'notion_form_database_url', [
        'sanitize_callback' => 'esc_url_raw',
        'type' => 'string',
        'show_in_rest' => false,
        'default' => '',
    ]);

    // Add a success message after settings are saved.
    if (isset($_GET['settings-updated']) && $_GET['settings-updated']) {
        add_settings_error(
            'notion_forms_messages',
            'notion_forms_message',
            'Settings saved successfully!',
            'updated'
        );
    }
}
add_action('admin_init', 'notion_forms_register_settings');

// Callbacks for settings fields.
function notion_forms_api_key_callback() {
    $value = get_option('notion_form_api_key', '');
    echo '<input type="text" name="notion_form_api_key" value="' . esc_attr($value) . '" class="regular-text">';
}

function notion_forms_database_url_callback() {
    $value = get_option('notion_form_database_url', '');
    echo '<input type="text" name="notion_form_database_url" value="' . esc_attr($value) . '" class="regular-text">';
}