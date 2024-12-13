<?php
// Add a shortcode to generate a form based on the local "notion_forms" database

function notion_form_shortcode($no_styles = false) {
    global $wpdb;
    if (isset($_GET['form_submitted']) && $_GET['form_submitted'] === '1') {
        $html = get_option('notion_forms_confirmation_content');
        return wpautop($html);
    }

    $table_name = $wpdb->prefix . 'notion_forms';
    // Fetch active fields from the local database
    $fields = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM $table_name WHERE is_active = %d ORDER BY order_num ASC",
            1
        )
    );

    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['notion-form-submit'])) {
        notion_form_handle_submission($fields, $_POST);
        $current_url = get_permalink();
        $redirect_url = add_query_arg('form_submitted', '1', $current_url);
        wp_redirect($redirect_url);
    }
    // Start building the HTML form
    $html = '<div id="notion-form-container"> <form id="notion-generated-form" method="POST"> <input type="hidden" name="notion-form-submit" value="1"> ';

    foreach ($fields as $field) {
        $required = $field->required ? 'required' : '';
        $label = esc_html($field->name);
        $field_id = esc_attr($field->column_id);

        $html .= '<div class="form-group">';
        $html .= "<label for='$field_id'>$label</label>";

        if ($field->field_type === 'select') {
            $arrOptions = explode("|", $field->field_attr);
            $select_options = "";
            foreach($arrOptions AS $option) {
                $select_options .= "<option value='$option'>$option</option>";
            }
            $html .= "<select id='$field_id' name='$field_id' $required class='form-control'>$select_options</select>";
        }
        elseif ($field->field_type === 'rich_text' && $field->field_attr === 'textarea') {
            $html .= "<textarea id='$field_id' name='$field_id' $required class='form-control'></textarea>";
        } elseif ($field->field_type === 'phone_number') {
            $html .= "<input type='number' id='$field_id' name='$field_id' $required class='form-control' />";
        } else {
            $html .= "<input type='text' id='$field_id' name='$field_id' $required class='form-control' />";
        }

        $html .= '</div>';
    }

    $html .= '<button type="submit" class="btn btn-primary">Submit</button>';
    $html .= '</form></div>';

    if(!$no_styles) {
        $css = get_option('notion_forms_css');
        if(isset($css) && $css) {
            $html .= "<style>$css</style>";
        }
    }

    return $html;
}
add_shortcode('notion_forms', 'notion_form_shortcode');



function notion_form_handle_submission($fields, $form_data) {
    $api_key = get_option('notion_form_api_key');
    $database_url = get_option('notion_form_database_url');

    // Extract the database ID from the full URL
    preg_match('/[a-f0-9]{32}/', $database_url, $matches);
    $database_id = $matches[0] ?? '';

    if (empty($database_id) || empty($api_key)) {
        echo '<div class="error">Error: Notion API Key or Database ID is missing.</div>';
        return;
    }

    // Prepare the data payload for Notion API
    $properties = [];
    foreach ($fields as $field) {
        if (isset($form_data[str_replace(" ", "_", $field->column_id)])) {
	    if($field->field_attr == "textarea") {
		    $value = sanitize_textarea_field($form_data[str_replace(" ", "_", $field->column_id)]);
	    }
	    else {
		    $value = sanitize_text_field($form_data[str_replace(" ", "_", $field->column_id)]);
	    }
            // Handle different Notion field types
            switch ($field->field_type) {
                case 'rich_text':
                    $properties[$field->name] = [
                        'rich_text' => [['text' => ['content' => $value]]],
                    ];
                    break;
                case 'select':
                    $properties[$field->name] = ['select' => ['name' => $value]];
                    break;
                case 'phone_number':
                    $properties[$field->name] = ['phone_number' => $value];
                    break;
                case 'email':
                    $properties[$field->name] = ['email' => $value];
                    break;
                default: // Fallback for other types, such as titles
                    $properties[$field->name] = [
                        'title' => [['text' => ['content' => $value]]],
                    ];
                    break;
            }
        }
    }

    // API request to add entry to Notion database
    $response = wp_remote_post('https://api.notion.com/v1/pages', [
        'headers' => [
            'Authorization' => "Bearer $api_key",
            'Content-Type'  => 'application/json',
            'Notion-Version' => '2022-06-28',
        ],
        'body' => json_encode([
            'parent' => ['database_id' => $database_id],
            'properties' => $properties,
        ]),
    ]);

    if (is_wp_error($response)) {
        echo '<div class="error">Error: Could not submit the form.</div>';
        return;
    }


}
