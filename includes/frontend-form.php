<?php
// Add a shortcode to generate a form based on the local "notion_forms" database

function notion_form_shortcode($no_styles = false) {
    // Start output buffering to prevent headers already sent error
    ob_start();
    
    $form_message = '';
    $submission_token = wp_create_nonce('notion_form_submission');
    
    // Check if this is a successful submission redirect
    if (isset($_GET['submission']) && $_GET['submission'] === 'success') {
        ob_end_clean(); // Clear the buffer
        return wpautop(get_option('notion_forms_confirmation_content', 'Thank you for your submission.'));
    }
    
    // Handle form submission before any output
    if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['notion-form-submit'])) {
        // Verify nonce
        if (isset($_POST['submission_token']) && !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['submission_token'])), 'notion_form_submission')) {
            $form_message = '<div class="error">Invalid submission. Please try again.</div>';
        } else {
            // Fetch active fields for submission
            $fields = get_posts(array(
                'post_type' => 'notion_form_field',
                'posts_per_page' => -1,
                'meta_query' => array(
                    array(
                        'key' => 'is_active',
                        'value' => '1'
                    )
                )
            ));
            
            $submission_result = notion_form_handle_submission($fields, $_POST);
            if ($submission_result === true) {
                $redirect_url = add_query_arg(
                    array(
                        'submission' => 'success'
                    ),
                    get_permalink()
                );
                ob_end_clean(); // Clear the buffer
                ?>
                <script>
                    window.location.href = "<?php echo esc_js($redirect_url); ?>";
                </script>
                <?php
                return "Redirecting...";
            } else {
                $form_message = '<div class="error">' . esc_html($submission_result) . '</div>';
            }
        }
    }

    // Fetch active fields for form display
    $fields = get_posts(array(
        'post_type' => 'notion_form_field',
        'posts_per_page' => -1,
        'meta_query' => array(
            array(
                'key' => 'is_active',
                'value' => '1'
            )
        ),
        'meta_key' => 'order_num',
        'orderby' => 'meta_value_num',
        'order' => 'ASC'
    ));

    // Start building the HTML form
    $html = '<div id="notion-form-container">';
    
    // Add form message if exists
    if ($form_message) {
        $html .= $form_message;
    }
    
    $html .= '<form id="notion-generated-form" method="POST">';
    $html .= '<input type="hidden" name="notion-form-submit" value="1">';
    // Add the nonce field
    $html .= wp_nonce_field('notion_form_submission', 'submission_token', true, false);

    foreach ($fields as $field) {
        // Get field metadata
        $required = get_post_meta($field->ID, 'required', true) ? 'required' : '';
        $field_label = get_post_meta($field->ID, 'label', true);
        $field_type = get_post_meta($field->ID, 'field_type', true);
        $field_attr = get_post_meta($field->ID, 'field_attr', true);
        $field_attr2 = get_post_meta($field->ID, 'field_attr2', true);
        $column_id = get_post_meta($field->ID, 'column_id', true);
        $label = esc_html($field_label ? $field_label : $field->post_title);
        $field_id = esc_attr($field->ID);

        $html .= '<div class="form-group">';
        
        if ($field_type === 'checkbox') {
            $html .= "<div class='checkbox-wrapper'>";
            $html .= "<input type='checkbox' id='$field_id' name='$field_id' value='1' $required class='form-control-checkbox' />";
            $html .= "<label for='$field_id'>$label</label>";
            $html .= "</div>";
        } else {
            $html .= "<label for='$field_id'>$label</label>";
            
            switch ($field_type) {
                case 'select':
                    $arrOptions = explode("|", $field_attr);
                    $display_type = $field_attr2 === 'radio' ? 'radio' : 'select';
                    
                    if ($display_type === 'radio') {
                        $html .= "<div class='radio-group'>";
                        foreach($arrOptions as $option) {
                            $option_id = esc_attr($field_id . '_' . sanitize_title($option));
                            $html .= "<div class='radio-option'>";
                            $html .= "<input type='radio' id='$option_id' name='$field_id' value='$option' $required class='form-control-radio'>";
                            $html .= "<label for='$option_id'>$option</label>";
                            $html .= "</div>";
                        }
                        $html .= "</div>";
                    } else {
                        $select_options = "";
                        foreach($arrOptions as $option) {
                            $select_options .= "<option value='$option'>$option</option>";
                        }
                        $html .= "<select id='$field_id' name='$field_id' $required class='form-control'>$select_options</select>";
                    }
                    break;
                    
                case 'rich_text':
                    if ($field_attr === 'textarea') {
                        $html .= "<textarea id='$field_id' name='$field_id' $required class='form-control'></textarea>";
                    } else {
                        $html .= "<input type='text' id='$field_id' name='$field_id' $required class='form-control' />";
                    }
                    break;
                    
                case 'phone_number':
                    $html .= "<input type='number' id='$field_id' name='$field_id' $required class='form-control' />";
                    break;
                    
                case 'number':
                    $html .= "<input type='number' step='any' id='$field_id' name='$field_id' $required class='form-control' />";
                    break;
                    
                case 'date':
                    $html .= "<input type='date' id='$field_id' name='$field_id' $required class='form-control' />";
                    break;
                    
                default:
                    $html .= "<input type='text' id='$field_id' name='$field_id' $required class='form-control' />";
                    break;
            }
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

    ob_end_clean(); // Clear the buffer before returning
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
        return 'Error: Notion API Key or Database ID is missing.';
    }

    // Prepare the data payload for Notion API
    $properties = [];
    foreach ($fields as $field) {

        $field_type = get_post_meta($field->ID, 'field_type', true);
        $field_name = get_post_meta($field->ID, 'column_id', true);
        if (isset($form_data[$field->ID])) {

            if($field_type == "textarea") {
                $value = sanitize_textarea_field($form_data[$field->ID]);
            }
            else {
                $value = sanitize_text_field($form_data[$field->ID]);
            }

            // Handle different Notion field types
            switch ($field_type) {
                case 'checkbox':
                    $properties[$field_name] = [
                        'checkbox' => !empty($value)
                    ];
                    break;
                case 'rich_text':
                    $properties[$field_name] = [
                        'rich_text' => [['text' => ['content' => $value]]],
                    ];
                    break;
                case 'select':
                    $properties[$field_name] = ['select' => ['name' => $value]];
                    break;
                case 'phone_number':
                    $properties[$field_name] = ['phone_number' => $value];
                    break;
                case 'email':
                    $properties[$field_name] = ['email' => $value];
                    break;
                case 'number':
                    // Convert to float and handle empty values
                    $number_value = $value !== '' ? floatval($value) : null;
                    $properties[$field_name] = ['number' => $number_value];
                    break;
                case 'date':
                    $properties[$field_name] = ['date' => ['start' => $value]];
                    break;
                default: // Fallback for other types, such as titles
                    $properties[$field_name] = [
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
        'body' => wp_json_encode([
            'parent' => ['database_id' => $database_id],
            'properties' => $properties,
        ]),
    ]);

    if (is_wp_error($response)) {
        return 'Error: Could not submit the form.';
    }

    return true; // Successful submission
}
