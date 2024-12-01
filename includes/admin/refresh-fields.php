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

    $table_name = $wpdb->prefix . 'notion_forms';

    // Get all existing column IDs in the local database.
    $existing_columns = $wpdb->get_col("SELECT column_id FROM $table_name");

    // Prepare arrays to track new and matched column IDs.
    $notion_columns = array_keys($data['properties']);
    $processed_columns = [];

    foreach ($data['properties'] as $column_id => $field) {
        $processed_columns[] = $column_id;

        // Check if the column already exists.
        $existing_entry = $wpdb->get_row(
            $wpdb->prepare("SELECT id FROM $table_name WHERE column_id = %s", $column_id)
        );

        switch($field['type']) {
            case "select":
                $arrOptions = array();
                foreach($field['select']['options'] AS $option) {
                    $arrOptions[] = $option['name'];
                }
                $field_attr = implode("|", $arrOptions);


                if ($existing_entry) {
                    $wpdb->update($table_name, [ 'name' => $field['name'], 'field_type' => $field['type'], 'field_attr' => $field_attr, ], ['column_id' => $column_id]);
                } else {
                    $wpdb->insert($table_name, [ 'column_id'  => $column_id, 'name' => $field['name'], 'field_type' => $field['type'], 'field_attr' => $field_attr, 'required' => 0, 'is_active'  => 0, 'order_num' => 0 ]);
                }

                break;

            default:

                if ($existing_entry) {
                    $wpdb->update($table_name, [ 'name' => $field['name'], 'field_type' => $field['type'] ], ['column_id' => $column_id]);
                } else {
                    $wpdb->insert($table_name, [ 'column_id'  => $column_id, 'name' => $field['name'], 'field_type' => $field['type'], 'required' => 0, 'is_active'  => 0, 'order_num' => 0 ]);
                }


                break;
        }





    }

    // Delete entries that are no longer in the Notion data.
    $obsolete_columns = array_diff($existing_columns, $processed_columns);
    if (!empty($obsolete_columns)) {
        $placeholders = implode(',', array_fill(0, count($obsolete_columns), '%s'));
        $wpdb->query(
            $wpdb->prepare("DELETE FROM $table_name WHERE column_id IN ($placeholders)", $obsolete_columns)
        );
    }

}