<?php
function form_sync_for_notion_confirmation_page() {

    if(!form_sync_for_notion_is_setup()) {
        form_sync_for_notion_setup_page();
        return;
    }




    // Check user permissions
    if (!current_user_can('manage_options')) {
        return;
    }

    // Save the confirmation content
    if (isset($_POST['form_sync_for_notion_confirmation_nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['form_sync_for_notion_confirmation_nonce'])), 'form_sync_for_notion_confirmation_save') && isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['form_sync_for_notion_confirmation_content'])) {
        $confirmation_content = wp_kses_post(wp_unslash($_POST['form_sync_for_notion_confirmation_content']));
        update_option('form_sync_for_notion_confirmation_content', $confirmation_content);
        echo '<div class="notice notice-success is-dismissible"><p>Confirmation content updated successfully.</p></div>';
    }

    // Get the current confirmation content
    $confirmation_content = get_option('form_sync_for_notion_confirmation_content', '');

    require_once FORM_SYNC_FOR_NOTION_PATH . 'includes/admin/admin-header.php';


    ?>
    <div class="wrap" id="form-sync-for-notion-container">
        <h1>Confirmation Page</h1>
        <form method="POST" action="">
            <?php wp_nonce_field('form_sync_for_notion_confirmation_save', 'form_sync_for_notion_confirmation_nonce'); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">
                        <label for="form_sync_for_notion_confirmation_content">Confirmation Content</label>
                    </th>
                    <td>
                        <?php
                        wp_editor($confirmation_content, 'form_sync_for_notion_confirmation_content', [
                            'textarea_name' => 'form_sync_for_notion_confirmation_content',
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