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
        <form method="post">
            <input type="hidden" name="notion_forms_action" value="refresh_fields">
            <?php submit_button('Refresh Fields'); ?>
        </form>

        <!-- Available Fields Section -->
        <h2>Available Fields</h2>
        <?php if (!empty($available_fields)) : ?>
            <table class="widefat striped">
                <thead>
                    <tr>
                        <th>Field Name</th>
                        <th>Field Type</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($available_fields as $field) : ?>
                        <tr>
                            <td><?php echo esc_html($field->name); ?></td>
                            <td><?php echo esc_html($field->field_type); ?></td>
                            <td>
                                <form method="post" style="display:inline;">
                                    <input type="hidden" name="notion_forms_action" value="activate_field">
                                    <input type="hidden" name="field_id" value="<?php echo esc_attr($field->id); ?>">
                                    <?php submit_button('Activate', 'secondary', '', false); ?>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else : ?>
            <p>No available fields.</p>
        <?php endif; ?>

        <!-- Form Section -->
        <h2>Form</h2>
        <?php if (!empty($form_fields)) : ?>
            <table class="widefat striped">
                <thead>
                    <tr>
                        <th>Field Name</th>
                        <th>Field Type</th>
                        <th>Order</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($form_fields as $field) : ?>
                        <tr>
                            <td><?php echo esc_html($field->name); ?></td>
                            <td><?php echo esc_html($field->field_type); ?></td>
                            <td><?php echo esc_html($field->order_num); ?></td>
                            <td>
                                <form method="post" style="display:inline;">
                                    <input type="hidden" name="notion_forms_action" value="deactivate_field">
                                    <input type="hidden" name="field_id" value="<?php echo esc_attr($field->id); ?>">
                                    <?php submit_button('Deactivate', 'secondary', '', false); ?>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else : ?>
            <p>No fields in the form.</p>
        <?php endif; ?>
    </div>
    <?php
}

// Handle form actions.
function notion_forms_handle_post() {
    echo "Patrick";
    if (!isset($_POST['notion_forms_action']) || !isset($_POST['field_id'])) {
        return;
    }
    global $wpdb;
    $table_name = $wpdb->prefix . 'notion_forms';
    $action = sanitize_text_field($_POST['notion_forms_action']);
    $field_id = intval($_POST['field_id']);

    if ($action === 'activate_field') {
        $wpdb->update(
            $table_name,
            ['is_active' => 1, 'order_num' => 0],
            ['id' => $field_id]
        );
    } elseif ($action === 'deactivate_field') {
        $wpdb->update(
            $table_name,
            ['is_active' => 0, 'order_num' => 0],
            ['id' => $field_id]
        );
    }
}
add_action('admin_post_notion_forms_action', 'notion_forms_handle_post');