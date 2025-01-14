<?php
// Add a shortcode to generate a form based on the local "notion_forms" database

function form_sync_for_notion_shortcode($no_styles = false, $no_recaptcha = false) {
    // Start output buffering to prevent headers already sent error
    ob_start();
    
    $form_message = '';
    $submission_token = wp_create_nonce('form_sync_for_notion_submission');
    $should_process_form = true;
    
    // Check if this is a successful submission redirect
    if (isset($_GET['submission']) && $_GET['submission'] === 'success') {
        ob_end_clean(); // Clear the buffer
        return wpautop(get_option('notion_forms_confirmation_content', 'Thank you for your submission.'));
    }
    
    // Handle form submission before any output
    if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['form-sync-for-notion-submit'])) {
        // Verify nonce
        if (!isset($_POST['submission_token']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['submission_token'])), 'form_sync_for_notion_submission')) {
            $form_message = '<div class="error">Invalid submission. Please try again.</div>';
            $should_process_form = false;
        }

        // Verify ReCaptcha if enabled
        if ($should_process_form) {
            $recaptcha_enabled = get_option('form_sync_for_notion_enable_recaptcha', false);
            if ($recaptcha_enabled) {
                $recaptcha_secret = get_option('form_sync_for_notion_recaptcha_secret_key', '');
                $recaptcha_version = get_option('form_sync_for_notion_recaptcha_version', 'v2');
                $recaptcha_response = isset($_POST['g-recaptcha-response']) ? sanitize_text_field(wp_unslash($_POST['g-recaptcha-response'])) : '';
                
                if (empty($recaptcha_response)) {
                    $form_message = '<div class="error">Please complete the ReCaptcha verification.</div>';
                    $should_process_form = false;
                } else {
                    // Verify ReCaptcha response
                    $verify_url = 'https://www.google.com/recaptcha/api/siteverify';
                    $response = wp_remote_post($verify_url, [
                        'body' => [
                            'secret' => $recaptcha_secret,
                            'response' => $recaptcha_response
                        ]
                    ]);

                    if (is_wp_error($response)) {
                        $form_message = '<div class="error">ReCaptcha verification failed. Please try again.</div>';
                        $should_process_form = false;
                    } else {
                        $body = wp_remote_retrieve_body($response);
                        $result = json_decode($body, true);

                        if (!$result['success']) {
                            $form_message = '<div class="error">ReCaptcha verification failed. Please try again.</div>';
                            $should_process_form = false;
                        } elseif ($recaptcha_version === 'v3' && (!isset($result['score']) || $result['score'] < 0.5)) {
                            $form_message = '<div class="error">ReCaptcha verification failed. Please try again.</div>';
                            $should_process_form = false;
                        }
                    }
                }
            }
        }

        // Process form submission if all validations pass
        if ($should_process_form) {
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
            
            $submission_result = form_sync_for_notion_handle_submission($fields, $_POST);
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

    // Get ReCaptcha settings
    $recaptcha_enabled = get_option('form_sync_for_notion_enable_recaptcha', false);
    $recaptcha_site_key = get_option('form_sync_for_notion_recaptcha_site_key', '');
    $recaptcha_version = get_option('form_sync_for_notion_recaptcha_version', 'v2');

    // Add ReCaptcha script if enabled
    if ($recaptcha_enabled && !$no_recaptcha) {
        if ($recaptcha_version === 'v2') {
            wp_enqueue_script('recaptcha-v2', 'https://www.google.com/recaptcha/api.js', array(), null, true);
        } else {
            wp_enqueue_script('recaptcha-v3', 'https://www.google.com/recaptcha/api.js?render=' . esc_attr($recaptcha_site_key) . '&onload=onloadRecaptcha', array(), null, true);
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
    $html = '<div id="form-sync-for-notion-container">';
    
    // Add form message if exists
    if ($form_message) {
        $html .= $form_message;
    }
    
    $html .= '<form id="form-sync-for-notion-generated-form" method="POST">';
    $html .= '<input type="hidden" name="form-sync-for-notion-submit" value="1">';
    // Add the nonce field
    $html .= wp_nonce_field('form_sync_for_notion_submission', 'submission_token', true, false);

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

        // Get previously submitted value if it exists
        $submitted_value = isset($_POST[$field_id]) ? stripslashes(sanitize_text_field(wp_unslash($_POST[$field_id]))) : '';
        if ($field_type !== 'checkbox' && $field_type !== 'multi_select' && 
            $field_type !== 'rich_text' && $field_type !== 'text') {
            $submitted_value = esc_attr($submitted_value);
        }
        $html .= '<div class="form-group">';
        
        if ($field_type === 'checkbox') {
            $html .= "<div class='checkbox-wrapper'>";
            $checked = !empty($submitted_value) ? 'checked' : '';
            $html .= "<input type='checkbox' id='$field_id' name='$field_id' value='1' $required $checked class='form-control-checkbox' />";
            $html .= "<label for='$field_id'>$label</label>";
            $html .= "</div>";
        } else {
            $html .= "<label for='$field_id'>$label</label>";
            
            switch ($field_type) {
                case 'select':
                case 'status':
                    $arrOptions = explode("|", $field_attr);
                    $display_type = $field_attr2 === 'radio' ? 'radio' : 'select';
                    
                    if ($display_type === 'radio') {
                        $html .= "<div class='radio-group'>";
                        foreach($arrOptions as $option) {
                            $option_id = esc_attr($field_id . '_' . sanitize_title($option));
                            $checked = $submitted_value === $option ? 'checked' : '';
                            $html .= "<div class='radio-option'>";
                            $html .= "<input type='radio' id='$option_id' name='$field_id' value='$option' $required $checked class='form-control-radio'>";
                            $html .= "<label for='$option_id'>$option</label>";
                            $html .= "</div>";
                        }
                        $html .= "</div>";
                    } else {
                        $select_options = "";
                        foreach($arrOptions as $option) {
                            $selected = $submitted_value === $option ? 'selected' : '';
                            $select_options .= "<option value='$option' $selected>$option</option>";
                        }
                        $html .= "<select id='$field_id' name='$field_id' $required class='form-control'>$select_options</select>";
                    }
                    break;
                    
                case 'multi_select':
                    $arrOptions = explode("|", $field_attr);
                    $submitted_values = isset($_POST[$field->ID]) ? array_map('sanitize_text_field', (array)wp_unslash($_POST[$field->ID])) : array();





                    $html .= "<div class='checkbox-group'>";
                    foreach($arrOptions as $option) {
                        $option_id = esc_attr($field_id . '_' . sanitize_title($option));
                        $checked = in_array($option, $submitted_values) ? 'checked' : '';
                        $html .= "<div class='checkbox-option'>";
                        $html .= "<input type='checkbox' id='$option_id' name='{$field_id}[]' value='$option' $checked class='form-control-checkbox'>";
                        $html .= "<label for='$option_id'>$option</label>";
                        $html .= "</div>";
                    }
                    $html .= "</div>";
                    break;
                    
                case 'rich_text':
                    if ($field_attr === 'textarea') {
                        $html .= "<textarea id='$field_id' name='$field_id' $required class='form-control'>" . 
                            wp_specialchars_decode($submitted_value, ENT_QUOTES) . "</textarea>";
                    } else {
                        $html .= "<input type='text' id='$field_id' name='$field_id' value='" . 
                            wp_specialchars_decode($submitted_value, ENT_QUOTES) . "' $required class='form-control' />";
                    }
                    break;
                    
                case 'phone_number':
                    $html .= "<input type='tel' id='$field_id' name='$field_id' value='$submitted_value' $required class='form-control' />";
                    break;
                    
                case 'number':
                    $html .= "<input type='number' step='any' id='$field_id' name='$field_id' value='$submitted_value' $required class='form-control' />";
                    break;
                    
                case 'date':
                    $html .= "<input type='date' id='$field_id' name='$field_id' value='$submitted_value' $required class='form-control' />";
                    break;
                    
                default:
                    $html .= "<input type='text' id='$field_id' name='$field_id' value='$submitted_value' $required class='form-control' />";
                    break;
            }
        }

        $html .= '</div>';
    }

    // Add ReCaptcha before submit button if enabled
    if ($recaptcha_enabled && !$no_recaptcha) {
        $html .= '<div class="form-group">';
        if ($recaptcha_version === 'v2') {
            $html .= '<div class="g-recaptcha" data-sitekey="' . esc_attr($recaptcha_site_key) . '"></div>';
        } else {
            $html .= '<input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">';
            $html .= '<script>
                window.onloadRecaptcha = function() {
                    grecaptcha.ready(function() {
                        grecaptcha.execute("' . esc_js($recaptcha_site_key) . '", {action: "submit"}).then(function(token) {
                            document.getElementById("g-recaptcha-response").value = token;
                        });
                    });
                }
            </script>';
        }
        $html .= '</div>';
    }

    $html .= '<button type="submit" class="btn btn-primary">Submit</button>';
    $html .= '</form></div>';

    if(!$no_styles) {
        $css = get_option('form_sync_for_notion_css');
        if(isset($css) && $css) {
            $html .= "<style>$css</style>";
        }
    }

    ob_end_clean(); // Clear the buffer before returning
    return $html;
}
add_shortcode('form_sync_for_notion', 'form_sync_for_notion_shortcode');



function form_sync_for_notion_handle_submission($fields, $form_data) {
    $api_key = get_option('form_sync_for_notion_api_key');
    $database_url = get_option('form_sync_for_notion_database_url');

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
                $value = stripslashes(sanitize_textarea_field($form_data[$field->ID]));
            }
            else {
                $value = stripslashes(sanitize_text_field($form_data[$field->ID]));
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
                case 'multi_select':
                    $selected_values = isset($form_data[$field->ID]) ? (array)$form_data[$field->ID] : [];
                    $multi_select_options = array_map(function($option) {
                        return ['name' => sanitize_text_field($option)];
                    }, $selected_values);
                    $properties[$field_name] = ['multi_select' => $multi_select_options];
                    break;
                case 'status':
                    $properties[$field_name] = ['status' => ['name' => $value]];
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
