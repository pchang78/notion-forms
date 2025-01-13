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

// Enqueue admin scripts only on settings page
function form_sync_for_notion_admin_scripts($hook) {
    // Only enqueue on our settings page
    if ($hook !== 'toplevel_page_form-sync-for-notion' && $hook !== 'form-sync-for-notion_page_form-sync-for-notion-settings') {
        return;
    }

    wp_enqueue_script(
        'form-sync-for-notion-settings',
        plugin_dir_url(__FILE__) . '../../js/form-sync-for-notion-settings.js',
        array('jquery'),
        '1.0.0',
        true
    );
}
add_action('admin_enqueue_scripts', 'form_sync_for_notion_admin_scripts');

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

    // ReCaptcha Settings
    add_settings_section(
        'form_sync_for_notion_recaptcha_settings',
        'ReCaptcha Settings',
        null,
        'form-sync-for-notion-settings'
    );

    add_settings_field(
        'form_sync_for_notion_enable_recaptcha',
        'Enable ReCaptcha',
        'form_sync_for_notion_enable_recaptcha_callback',
        'form-sync-for-notion-settings',
        'form_sync_for_notion_recaptcha_settings'
    );

    add_settings_field(
        'form_sync_for_notion_recaptcha_site_key',
        'ReCaptcha Site Key',
        'form_sync_for_notion_recaptcha_site_key_callback',
        'form-sync-for-notion-settings',
        'form_sync_for_notion_recaptcha_settings'
    );

    add_settings_field(
        'form_sync_for_notion_recaptcha_secret_key',
        'ReCaptcha Secret Key',
        'form_sync_for_notion_recaptcha_secret_key_callback',
        'form-sync-for-notion-settings',
        'form_sync_for_notion_recaptcha_settings'
    );

    add_settings_field(
        'form_sync_for_notion_recaptcha_version',
        'ReCaptcha Version',
        'form_sync_for_notion_recaptcha_version_callback',
        'form-sync-for-notion-settings',
        'form_sync_for_notion_recaptcha_settings'
    );

    register_setting('form_sync_for_notion_settings', 'form_sync_for_notion_enable_recaptcha', [
        'sanitize_callback' => 'rest_sanitize_boolean',
        'type' => 'boolean',
        'show_in_rest' => false,
        'default' => false,
    ]);

    register_setting('form_sync_for_notion_settings', 'form_sync_for_notion_recaptcha_site_key', [
        'sanitize_callback' => 'sanitize_text_field',
        'type' => 'string',
        'show_in_rest' => false,
        'default' => '',
    ]);

    register_setting('form_sync_for_notion_settings', 'form_sync_for_notion_recaptcha_secret_key', [
        'sanitize_callback' => 'sanitize_text_field',
        'type' => 'string',
        'show_in_rest' => false,
        'default' => '',
    ]);

    register_setting('form_sync_for_notion_settings', 'form_sync_for_notion_recaptcha_version', [
        'sanitize_callback' => 'sanitize_text_field',
        'type' => 'string',
        'show_in_rest' => false,
        'default' => 'v2',
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

function form_sync_for_notion_enable_recaptcha_callback() {
    $value = get_option('form_sync_for_notion_enable_recaptcha', false);
    echo '<input type="checkbox" id="form_sync_for_notion_enable_recaptcha" name="form_sync_for_notion_enable_recaptcha" value="1" ' . checked(1, $value, false) . '>';
}

function form_sync_for_notion_recaptcha_site_key_callback() {
    $value = get_option('form_sync_for_notion_recaptcha_site_key', '');
    echo '<input type="text" name="form_sync_for_notion_recaptcha_site_key" value="' . esc_attr($value) . '" 
        class="regular-text recaptcha-dependent-field recaptcha-required-field" required>';
}

function form_sync_for_notion_recaptcha_secret_key_callback() {
    $value = get_option('form_sync_for_notion_recaptcha_secret_key', '');
    echo '<input type="text" name="form_sync_for_notion_recaptcha_secret_key" value="' . esc_attr($value) . '" 
        class="regular-text recaptcha-dependent-field recaptcha-required-field" required>';
}

function form_sync_for_notion_recaptcha_version_callback() {
    $value = get_option('form_sync_for_notion_recaptcha_version', 'v2');
    echo '<select name="form_sync_for_notion_recaptcha_version" class="recaptcha-dependent-field recaptcha-required-field" required>
        <option value="v2" ' . selected('v2', $value, false) . '>ReCaptcha v2</option>
        <option value="v3" ' . selected('v3', $value, false) . '>ReCaptcha v3</option>
    </select>';
}

// Add validation for required fields
function form_sync_for_notion_validate_settings($input) {
    $enable_recaptcha = isset($input['form_sync_for_notion_enable_recaptcha']) ? true : false;
    
    if ($enable_recaptcha) {
        if (empty($input['form_sync_for_notion_recaptcha_site_key'])) {
            add_settings_error(
                'form_sync_for_notion_messages',
                'recaptcha_site_key_required',
                'ReCaptcha Site Key is required when ReCaptcha is enabled.',
                'error'
            );
        }
        
        if (empty($input['form_sync_for_notion_recaptcha_secret_key'])) {
            add_settings_error(
                'form_sync_for_notion_messages',
                'recaptcha_secret_key_required',
                'ReCaptcha Secret Key is required when ReCaptcha is enabled.',
                'error'
            );
        }
        
        if (empty($input['form_sync_for_notion_recaptcha_version'])) {
            add_settings_error(
                'form_sync_for_notion_messages',
                'recaptcha_version_required',
                'ReCaptcha Version is required when ReCaptcha is enabled.',
                'error'
            );
        }
    }
    
    return $input;
}
add_filter('pre_update_option_form_sync_for_notion_settings', 'form_sync_for_notion_validate_settings', 10, 1);