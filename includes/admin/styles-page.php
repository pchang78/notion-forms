<?php

// Render the Styles page
function notion_forms_styles_page() {
    // Check if the user has submitted the form
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['notion_forms_css_nonce'])) {
        if (!wp_verify_nonce($_POST['notion_forms_css_nonce'], 'save_notion_forms_css')) {
            wp_die('Security check failed.');
        }

        // Save the custom CSS
        $custom_css = sanitize_textarea_field($_POST['notion_forms_css']);
        update_option('notion_forms_css', $custom_css);

        // Display success message
        echo '<div class="updated notice is-dismissible"><p>Styles saved successfully.</p></div>';
    }

    // Retrieve the saved CSS
    $custom_css = get_option('notion_forms_css', '');

    ?>
    <div class="wrap">
        <h1>Notion Forms Styles</h1>
        <form method="POST">
            <?php wp_nonce_field('save_notion_forms_css', 'notion_forms_css_nonce'); ?>

            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><label for="notion_forms_css">Custom CSS</label></th>
                    <td>
                        <textarea id="notion_forms_css" name="notion_forms_css" rows="20" cols="50" class="large-text code"><?php echo esc_textarea($custom_css); ?></textarea>
                    </td>
                </tr>
            </table>

            <?php submit_button('Save Styles'); ?>
        </form>
    </div>
    <?php
}
