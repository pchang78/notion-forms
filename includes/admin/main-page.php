<?php
function notion_forms_main_page() {
    global $wpdb;


    if(isset($_POST["action"]) && $_POST["action"] == "notion_forms_refresh_fields") {
        notion_forms_refresh_fields();
        notion_forms_admin_msg("Fields refreshed successfully!");

    }
    if(isset($_POST["action"]) && $_POST["action"] == "notion_forms_save_form") {
        notion_forms_save_form();
        notion_forms_admin_msg("Form saved.");
    }

    $table_name = $wpdb->prefix . 'notion_forms';

    // Query fields based on is_active status.
    $available_fields = get_posts(array(
        'post_type' => 'notion_form_field',
        'posts_per_page' => -1,
        'meta_query' => array(
            array(
                'key' => 'is_active',
                'value' => '0'
            ),
            array(
                'key' => 'field_type',
                'value' => 'last_edited_time',
                'compare' => '!='
            )
        ),
        'orderby' => 'title',
        'order' => 'ASC'
    ));

    $form_fields = get_posts(array(
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


    require_once NOTION_FORMS_PATH . 'includes/admin/admin-header.php';


    ?>

    <div class="wrap" id="notion-forms-container">
        <h1>Notion Forms</h1>
        <br>
        <div class="notion-forms-shortcode">
            <label for="notion-forms-shortcode-input">
                <strong>Use this shortcode to embed the form:</strong>
            </label>
            <div style="display: flex; align-items: center; gap: 10px;">
                <input type="text" id="notion-forms-shortcode-input" value="[notion_forms]" readonly style="width: 300px; padding: 5px; border: 1px solid #ccc; border-radius: 4px; background-color: #f9f9f9; font-family: monospace;" 
        />
                <button type="button" id="copy-shortcode-button" style="padding: 5px 10px; border: none; background-color: #0073aa; color: white; border-radius: 4px; cursor: pointer;"> Copy </button>
            </div>
            <span id="copy-feedback" style="margin-left: 10px; color: green; display: none;">Shortcode copied!</span>
        </div>
        <br>
        <hr>

        <!-- Refresh Fields Form -->
        <form method="post">
            <input type="hidden" name="action" value="notion_forms_refresh_fields">
            <?php submit_button('Refresh Fields'); ?>
        </form>
        <div class="wp-clearfix">
            <form method="post">
            <div id="notion-forms-wrapper">
                <div class="postbox-container" style="width: 25%; float: left; margin-right: 2%;">
                    <input type="hidden" name="action" value="notion_forms_save_form">
                    <input type="hidden" name="field_order" value="" id="notion_forms_field_order">
                    <!-- Available Fields -->
                    <h2>Available Fields</h2>
                    <ul id="available-fields" class="notion-forms-list drop-area">
                        <?php foreach ($available_fields as $field): ?>
                            <?php notion_forms_the_field_item($field); ?>
                        <?php endforeach; ?>

                    </ul>
                </div>
                <div class="postbox-container" style="width: 60%; float: left;">
                    <h2>Form Fields</h2>
                    <ul id="form-fields" class="notion-forms-list drop-area">
                        <?php foreach ($form_fields as $field): ?>
                            <?php notion_forms_the_field_item($field, true); ?>
                        <?php endforeach; ?>

                    </ul>
                    <?php submit_button('Save Form', 'primary', 'submit', true, 'style="float: right;"'); ?>
                </div>
            </div>
            </form>
        </div>
    </div>
    <?php
}

// Render individual field items
function notion_forms_the_field_item($post, $active = false) {
    $field_id = $post->ID;
    $field_name = $post->post_title;
    $field_type = get_post_meta($field_id, 'field_type', true);
    $field_attr = get_post_meta($field_id, 'field_attr', true);
    $required = get_post_meta($field_id, 'required', true);
    $is_active = $active ? 1 : 0;
    $hide = $active ? '' : 'hidden';
    $checked = $required ? 'CHECKED' : '';
    

	if($active) {
		$active_val = 1;
        $hide = "";
	}
	else {
		$active_val = 0;
        $hide = "hidden";
	}

?>


    <li class="notion-field-item" data-id="<?php echo esc_attr($field_id); ?>" draggable="true">
    <input type="hidden" name="field[<?php echo esc_attr($field_id); ?>][is_active]" value="<?php echo $active_val; ?>" id="is_active<?php echo esc_attr($field_id); ?>">
    <p>
        <?php echo esc_html($field_name); ?> <small> <?php echo esc_html($field_type); ?> </small>
    </p>
    <p class="attributes <?php echo $hide; ?>">
        <input type="checkbox" name="field[<?php echo esc_attr($field_id); ?>][required]" value="1" id="required<?php echo esc_attr($field_id); ?>" <?php echo $checked; ?>> Required
        <br>
<?php 
if($field_type == "rich_text") :
?>
        <label for="field_attr<?php echo esc_attr($field_id); ?>">
        Field Type:
        <select name="field[<?php echo esc_attr($field_id); ?>][field_attr]" id="field_attr<?php echo esc_attr($field_id); ?>">
            <option value="text" <?php if($field_attr == "text") echo "selected"; ?>>Text</option>
            <option value="textarea" <?php if($field_attr == "textarea") echo "selected"; ?>>Textarea</option>
        </select> 
        </label>
<?php
endif;
?>

    </p>
</li>
<?php
}


// Handle form actions.
function notion_forms_handle_post() {
    if (!isset($_POST['notion_forms_action'])) {
        return;
    }
    global $wpdb;
    $table_name = $wpdb->prefix . 'notion_forms';
    $action = sanitize_text_field($_POST['notion_forms_action']);
    if ($action === 'refresh_fields') {
        notion_forms_refresh_fields();
        wp_safe_redirect(add_query_arg('notion_refresh', 'success', $_SERVER['HTTP_REFERER']));
        exit;
    } 
    elseif ($action === 'save_form') {
        notion_forms_save_form();
        wp_safe_redirect(add_query_arg('notion_refresh', 'save_form', $_SERVER['HTTP_REFERER']));
        exit;
    }
    wp_safe_redirect($_SERVER['HTTP_REFERER']);
    exit;
}
// add_action('admin_post_notion_forms_action', 'notion_forms_handle_post');


function notion_forms_admin_notices() {
    if (isset($_GET['notion_refresh']) && $_GET['notion_refresh'] === 'success') {
        ?>
        <div class="notice notice-success is-dismissible">
            <p><?php _e('Fields refreshed successfully!', 'notion-forms'); ?></p>
        </div>
        <?php
    }
    elseif (isset($_GET['notion_refresh']) && $_GET['notion_refresh'] === 'save_form') {
        ?>
        <div class="notice notice-success is-dismissible">
            <p><?php _e('Form Saved!', 'notion-forms'); ?></p>
        </div>
        <?php
    }
}
add_action('admin_notices', 'notion_forms_admin_notices');


function notion_forms_save_form() {
    foreach ($_POST['field'] as $field_id => $field) {
        $field_id = intval($field_id);
        $is_active = intval($field['is_active']);
        $required = isset($field['required']) && $field['required'] ? 1 : 0;

        // Update is_active status
        update_post_meta($field_id, 'is_active', $is_active);
        
        // Update required status
        update_post_meta($field_id, 'required', $required);

        // Update field_attr if it exists
        if(isset($field['field_attr']) && $field['field_attr']) {
            update_post_meta($field_id, 'field_attr', sanitize_text_field($field['field_attr']));
        }
    }

    // Update order numbers
    if(isset($_POST['field_order']) && $_POST['field_order']) {
        $field_ids = explode(",", $_POST['field_order']);
        foreach ($field_ids as $index => $field_id) {
            $field_id = intval($field_id);
            $order_num = $index + 1;
            update_post_meta($field_id, 'order_num', $order_num);
        }
    }
}

