<?php

function notion_forms_refresh_fields() {
    global $wpdb;

    // Fetch API key and database URL from settings.
    $api_key = get_option('notion_form_api_key');
    $database_url = get_option('notion_form_database_url');

    if (!$api_key || !$database_url) {
        echo '<div class="notice notice-error"><p>API Key or Database URL not set.</p></div>';
        return;
    }

    // Parse the database ID from the URL.
    preg_match('/([a-f0-9]{32})/', $database_url, $matches);
    $database_id = $matches[1] ?? '';

    if (!$database_id) {
        echo '<div class="notice notice-error"><p>Invalid Database URL.</p></div>';
        return;
    }

    // Make the Notion API request.
    $response = wp_remote_get(
        "https://api.notion.com/v1/databases/$database_id",
        [
            'headers' => [
                'Authorization' => "Bearer $api_key",
                'Notion-Version' => '2022-06-28',
            ],
        ]
    );

    // Handle API response.
    if (is_wp_error($response)) {
        echo '<div class="notice notice-error"><p>API request failed: ' . esc_html($response->get_error_message()) . '</p></div>';
        return;
    }

    $data = json_decode(wp_remote_retrieve_body($response), true);

    if (empty($data['properties'])) {
        echo '<div class="notice notice-error"><p>No fields found in the Notion database.</p></div>';
        return;
    }

    // Get existing fields
    $existing_fields = get_posts(array(
        'post_type' => 'notion_form_field',
        'posts_per_page' => -1,
        'meta_query' => array(
            array(
                'key' => 'column_id',
                'compare' => 'EXISTS'
            )
        )
    ));

    $existing_column_ids = array_map(function($post) {
        return get_post_meta($post->ID, 'column_id', true);
    }, $existing_fields);

    $processed_columns = [];

    foreach ($data['properties'] as $column_id => $field) {
        $processed_columns[] = $column_id;
        
        // Find existing post by column_id
        $existing_post = get_posts(array(
            'post_type' => 'notion_form_field',
            'meta_key' => 'column_id',
            'meta_value' => $column_id,
            'posts_per_page' => 1
        ));

        $field_data = array(
            'post_title' => $field['name'],
            'post_type' => 'notion_form_field',
            'post_status' => 'publish'
        );

        $meta_data = array(
            'column_id' => $column_id,
            'field_type' => $field['type'],
            'required' => 0,
            'is_active' => 0,
            'order_num' => 0
        );

        if($field['type'] === 'select') {
            $options = array_map(function($option) {
                return $option['name'];
            }, $field['select']['options']);
            $meta_data['field_attr'] = implode('|', $options);
        }

        if($existing_post) {
            $post_id = $existing_post[0]->ID;
            $field_data['ID'] = $post_id;
            wp_update_post($field_data);
        } else {
            $post_id = wp_insert_post($field_data);
        }

        foreach($meta_data as $key => $value) {
            update_post_meta($post_id, $key, $value);
        }
    }

    // Delete obsolete fields
    $obsolete_columns = array_diff($existing_column_ids, $processed_columns);
    foreach($obsolete_columns as $column_id) {
        $obsolete_post = get_posts(array(
            'post_type' => 'notion_form_field',
            'meta_key' => 'column_id',
            'meta_value' => $column_id,
            'posts_per_page' => 1
        ));
        
        if($obsolete_post) {
            wp_delete_post($obsolete_post[0]->ID, true);
        }
    }
}