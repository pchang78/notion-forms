<?php

// Render the Styles page
function form_sync_for_notion_styles_page() {

    if(!form_sync_for_notion_is_setup()) {
        form_sync_for_notion_setup_page();
        return;
    }



    // Check if the user has submitted the form
    if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['form_sync_for_notion_css_nonce'])) {
        if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['form_sync_for_notion_css_nonce'])), 'save_form_sync_for_notion_css')) {
            wp_die('Security check failed.');
        }

        // Save the custom CSS
        if(isset($_POST['form_sync_for_notion_css'])) {
            $custom_css = sanitize_textarea_field(wp_unslash($_POST['form_sync_for_notion_css']));
            update_option('form_sync_for_notion_css', $custom_css);
        }

        // Display success message
        echo '<div class="updated notice is-dismissible"><p>Styles saved successfully.</p></div>';
    }

    // Retrieve the saved CSS
    $custom_css = get_option('form_sync_for_notion_css', '');
    require_once FORM_SYNC_FOR_NOTION_PATH . 'includes/admin/admin-header.php';

    ?>
    <div class="wrap" id="form-sync-for-notion-container">
        <h1>Form Sync for Notion Styles</h1>
        <form method="POST">
            <?php wp_nonce_field('save_form_sync_for_notion_css', 'form_sync_for_notion_css_nonce'); ?>

            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><label for="form_sync_for_notion_css">Custom CSS</label></th>
                    <td>
                        <textarea id="form_sync_for_notion_css" name="form_sync_for_notion_css" rows="20" cols="50" class="large-text code"><?php echo esc_textarea($custom_css); ?></textarea>
                    </td>
                </tr>
            </table>

            <?php submit_button('Save Styles'); ?>
        </form>

        <hr>

        <h2>AI Prompt Helper</h2>
        <p>A quick way to style your form is to use AI tools like ChatGPT and Claud AI.</p>
        <p>
            <label>Describe in one or two words the type of style you want the look for your form to be. (i.e. Elegant, Modern, etc)</label>
            <br>
            <input type="text" name="ai_description" id="ai_description" placeholder="Elegant, Modern, etc">
            <br>
            <small>If left blank, the word "clean" will be used.</small>
        </p>

    <form>
<textarea class="hidden" id="ai_prompt">
Given the following html form code:
<?php echo esc_html(str_replace(">", "&gt;", str_replace("<", "&lt;", form_sync_for_notion_shortcode(true)))); ?>

</textarea>
<button id="copyButton" type="button">Copy Prompt to Clipboard</button>


</form>


    </div>


    <?php
}
