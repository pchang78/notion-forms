<?php
function notion_forms_main_page() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'notion_forms';

    // Query fields based on is_active status.
    $available_fields = $wpdb->get_results(
        "SELECT * FROM $table_name WHERE is_active = 0 ORDER BY name ASC"
    );

    $form_fields = $wpdb->get_results(
        "SELECT * FROM $table_name WHERE is_active = 1 ORDER BY order_num ASC"
    );

    ?>

    <div class="wrap">
        <h1>Notion Forms</h1>
        <!-- Refresh Fields Form -->
        <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
            <input type="hidden" name="action" value="notion_forms_action">
            <input type="hidden" name="notion_forms_action" value="refresh_fields">
            <?php submit_button('Refresh Fields'); ?>
        </form>
        <div class="wp-clearfix">
            <div id="notion-forms-wrapper">
                <div class="postbox-container" style="width: 25%; float: left; margin-right: 2%;">
                    <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                    <input type="hidden" name="action" value="notion_forms_action">
                    <input type="hidden" name="notion_forms_action" value="save_form">
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
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php
}

function notion_forms_the_field_item($field, $active = false) {

	if($active) {
		$active_val = 1;
	}
	else {
		$active_val = 0;
	}


?>
                            <li class="notion-field-item" data-id="<?php echo esc_attr($field->id); ?>" draggable="true">
                                <input type="hidden" name="field[<?php echo esc_attr($field->id); ?>][is_active]" value="<?php echo $active_val; ?>" id="is_active<?php echo esc_attr($field->id); ?>">
                                <?php echo esc_html($field->name); ?>
                                <small> <?php echo esc_html($field->field_type); ?> </small>
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
        /*
        echo "<pre>";
        print_r($_POST);
        echo "</pre>";
        */
        notion_forms_save_form();
        wp_safe_redirect(add_query_arg('notion_refresh', 'save_form', $_SERVER['HTTP_REFERER']));
        exit;
    }
    wp_safe_redirect($_SERVER['HTTP_REFERER']);
    exit;
}
add_action('admin_post_notion_forms_action', 'notion_forms_handle_post');


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
    global $wpdb;
    $table_name = $wpdb->prefix . 'notion_forms';
    foreach ($_POST['field'] as $field_id => $field) {
        $field_id = intval($field_id);
        $is_active = intval($field['is_active']);
        $update_result = $wpdb->update(
            $table_name,
            ['is_active' => $is_active], // Default order number for active
            ['id' => $field_id]
        );
    }
    if(isset($_POST['field_order']) && $_POST['field_order']) {
        $arrFields = explode(",", $_POST['field_order']);
        foreach ($arrFields AS $index => $field_id) {
            $field_id = intval($field_id);
            $order_num = $index + 1;
            $update_result = $wpdb->update(
                $table_name,
                ['order_num' => $order_num], // Default order number for active
                ['id' => $field_id]
            );
        }
    }
}

