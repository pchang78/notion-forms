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
    require_once NOTION_FORMS_PATH . 'includes/admin/admin-header.php';

    ?>
    <div class="wrap" id="notion-forms-container">
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
<?php echo esc_html(str_replace(">", "&gt;", str_replace("<", "&lt;", notion_form_shortcode(true)))); ?>

</textarea>
<button id="copyButton" type="button">Copy to Prompt to Clipboard</button>

<script>
  // JavaScript to handle the copy functionality
  document.getElementById('copyButton').addEventListener('click', function () {
    event.preventDefault();
    const textarea = document.getElementById('ai_prompt');
    const ai_description = document.getElementById('ai_description');
    textarea.select();
    textarea.setSelectionRange(0, 99999); // For mobile devices

    ai_description_value = ai_description.value.trim();
    prompt_value = textarea.value;

    if(ai_description_value.length === 0) {
        ai_description_value = "clean";
    }
    prompt_value = prompt_value + "Generate CSS to create a " + ai_description_value + " online form.  Have it scoped to #notion-form-container";
    // Copy the text to the clipboard
    navigator.clipboard.writeText(prompt_value)
      .then(() => {
        alert('Prompt copied to clipboard!  Paste this prompt into your AI tool and then paste the generated CSS code into the text box above.');
      })
      .catch(err => {
        console.error('Failed to copy text: ', err);
      });
  });
</script>
</form>


    </div>


    <?php
}
