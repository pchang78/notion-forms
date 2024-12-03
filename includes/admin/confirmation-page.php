<?php
function notion_forms_confirmation_page() {
    // Check user permissions
    if (!current_user_can('manage_options')) {
        return;
    }

    // Save the confirmation content
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['notion_forms_confirmation_content'])) {
        $confirmation_content = wp_kses_post($_POST['notion_forms_confirmation_content']);
        update_option('notion_forms_confirmation_content', $confirmation_content);
        echo '<div class="notice notice-success is-dismissible"><p>Confirmation content updated successfully.</p></div>';
    }

    // Get the current confirmation content
    $confirmation_content = get_option('notion_forms_confirmation_content', '');

    require_once NOTION_FORMS_PATH . 'includes/admin/admin-header.php';


    ?>
    <div class="wrap" id="notion-forms-container">
        <h1>Confirmation Page</h1>
        <form method="POST" action="">
            <?php wp_nonce_field('notion_forms_confirmation_save', 'notion_forms_confirmation_nonce'); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">
                        <label for="notion_forms_confirmation_content">Confirmation Content</label>
                    </th>
                    <td>
                        <?php
                        wp_editor($confirmation_content, 'notion_forms_confirmation_content', [
                            'textarea_name' => 'notion_forms_confirmation_content',
                            'textarea_rows' => 10,
                            'media_buttons' => false,
                        ]);
                        ?>
                    </td>
                </tr>
            </table>
            <?php submit_button('Save Changes'); ?>
        </form>
    </div>
    <?php
}