<?php
/* This file is used to handle the setup of the Form Sync for Notion plugin.  If the plugin does not have a valid API key and database URL, it will display a setup page.
*/

// Check if the plugin is setup
function form_sync_for_notion_is_setup() {
    $form_sync_for_notion_api_key = esc_attr(get_option('form_sync_for_notion_api_key'));
    $form_sync_for_notion_database_url = esc_attr(get_option('form_sync_for_notion_database_url'));
    if( isset($form_sync_for_notion_api_key) && $form_sync_for_notion_api_key && isset($form_sync_for_notion_database_url) && $form_sync_for_notion_database_url) {
        return true;
    }
    else {
        return false;
    }
}


// Check to see if ID is a page or database
function form_sync_for_notion_check_notion_config($api_key, $pageID) {

    // Check to see if ID is a page
    $results = array();
    $url = "https://api.notion.com/v1/pages/$pageID";
    $response = wp_remote_get(
        $url,
        [
            'headers' => [
                'Authorization' => "Bearer $api_key",
                'Notion-Version' => '2022-06-28',
            ],
        ]
    );

    // Is a page
    if($response["response"]["code"] == 200) {

        $url = "https://api.notion.com/v1/blocks/$pageID/children";
        $response = wp_remote_get($url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $api_key,
                'Notion-Version' => '2022-06-28'
            ]
        ]);
        $body = json_decode(wp_remote_retrieve_body($response), true);
        $results["url_type"] = "Page";
        $results["databases"] = array();
        // Look for Databases
        foreach ($body['results'] as $result) {
            if($result["type"] == "child_database") {
                $arrDB = array();
                $arrDB["id"] = str_replace("-", "", $result["id"]);
                $arrDB["name"] = $result["child_database"]["title"];
                $results["databases"][] = $arrDB;
            }
        }
    }
    else {

        // Check to see if the ID is a database.
        $url = "https://api.notion.com/v1/databases/$pageID";
        $response = wp_remote_get($url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $api_key,
                'Notion-Version' => '2022-06-28'
            ]
        ]);
        if($response["response"]["code"] == "200") {
            $results["url_type"] = "Database";
        }
        else {
            // The ID is either not a database, an invalid ID, or has not been integrated with the API key
            $results["url_type"] = "Not Found";
        }
    }

    return $results;
}

// Display the setup page
function form_sync_for_notion_setup_page() {
    $page = "";
    if(isset($_POST["form_sync_for_notion_check_config"]) && sanitize_text_field(wp_unslash($_POST["form_sync_for_notion_check_config"])) && isset($_POST["form_sync_for_notion_setup_page_form_nonce"]) && wp_verify_nonce( sanitize_text_field(wp_unslash($_POST["form_sync_for_notion_setup_page_form_nonce"])), 'form_sync_for_notion_setup_page_form' )) {
        if(isset($_POST["form_sync_for_notion_api_key"])) {
            $api_key = sanitize_text_field(wp_unslash($_POST["form_sync_for_notion_api_key"]));
        }
        if(isset($_POST["form_sync_for_notion_database_url"])) {
            $database_url = sanitize_text_field(wp_unslash($_POST["form_sync_for_notion_database_url"]));
        }


        preg_match('/([a-f0-9]{32})/', $database_url, $matches);
        $pageID= $matches[1] ?? '';


        $results = form_sync_for_notion_check_notion_config($api_key, $pageID);
        switch($results["url_type"]) {
            case "Page":
                // Check to see if there are child databases and then list them.  If there are not, then give an error message.  
                if(count($results["databases"]) > 0) {
                    // Show list of databases
                    $page = "database";

                }
                else {
                    $msg = "No databases found on the given URL";
                }
                break;

            case "Database":
                // Success!  Save API Key and Database URL into database
                $page = "success";
                update_option('form_sync_for_notion_api_key', sanitize_text_field(wp_unslash($_POST['form_sync_for_notion_api_key'])));
                update_option('form_sync_for_notion_database_url', sanitize_text_field(wp_unslash($_POST['form_sync_for_notion_database_url'])));



                break;
            case "Not Found":
                $msg = "There was an error finding the database.  Please check the API Key and the URL.  Make sure that the Notion Database has been integrated with the API key.";
                break;
        }
    }
    include FORM_SYNC_FOR_NOTION_PATH . 'includes/admin/admin-header.php';

    ?>

    <?php if(isset($msg) && $msg) : ?>
    <div class="notice notice-error is-dismissible">
        <p><?php echo esc_html($msg); ?></p>
    </div>
    <?php endif; ?>

    <div class="wrap" id="form-sync-for-notion-container">
        <h1>Form Sync for Notion Setup</h1>
        <?php 
        switch($page) {

            case "success":
                form_sync_for_notion_setup_page_success(); 
                break;

            case "database":
                form_sync_for_notion_setup_page_choose_database($results["databases"]); 
                break;

            default:
                form_sync_for_notion_setup_page_form(); 
                break;
        }
        ?>
    </div>
<?php
}

// Display the setup page form
function form_sync_for_notion_setup_page_form() {
    $api_key = "";
    if(isset($_POST["form_sync_for_notion_api_key"]) && isset($_POST["form_sync_for_notion_setup_page_form_nonce"]) && wp_verify_nonce( sanitize_text_field(wp_unslash($_POST["form_sync_for_notion_setup_page_form_nonce"])), 'form_sync_for_notion_setup_page_form' )) {
        $api_key = sanitize_text_field(wp_unslash($_POST["form_sync_for_notion_api_key"]));
    }
    $database_url = "";
    if(isset($_POST["form_sync_for_notion_database_url"]) && isset($_POST["form_sync_for_notion_setup_page_form_nonce"]) && wp_verify_nonce( sanitize_text_field(wp_unslash($_POST["form_sync_for_notion_setup_page_form_nonce"])), 'form_sync_for_notion_setup_page_form' )) {
        $database_url = sanitize_text_field(wp_unslash($_POST["form_sync_for_notion_database_url"]));
    }
    ?>
    <p>To get started, you need to enter your Notion API key and database URL.  You can find your API key in the <a href="https://www.notion.so/my-integrations" target="_blank">Notion Integrations</a> page.  Check out the <a href="https://everydaytech.tv/wp/notion-forms/documentation/getting-started/" target="_blank">Getting Started</a> guide for more information.</p>
        <form method="post" action="">
        <input type="hidden" name="form_sync_for_notion_check_config" value="1">
        <?php wp_nonce_field( 'form_sync_for_notion_setup_page_form', 'form_sync_for_notion_setup_page_form_nonce' ); ?>

        <table class="form-table">
            <tr valign="top">
                <th scope="row">
                    Notion API Key
                </th>
                <td><input type="text" name="form_sync_for_notion_api_key" value="<?php echo esc_attr($api_key); ?>" class="regular-text" /></td>
            </tr>
            <tr valign="top">
                <th scope="row">Notion Database URL
                </th>
                <td><input type="text" name="form_sync_for_notion_database_url" value="<?php echo esc_attr($database_url); ?>" class="regular-text" /></td>
            </tr>
        </table>
        <?php submit_button(); ?>
        </form>
<?php
}

// Display the setup page choose database
function form_sync_for_notion_setup_page_choose_database($databases = array()) {
    $api_key = "";
    if(isset($_POST["form_sync_for_notion_api_key"]) && isset($_POST["form_sync_for_notion_setup_page_form_nonce"]) && wp_verify_nonce( sanitize_text_field(wp_unslash($_POST["form_sync_for_notion_setup_page_form_nonce"])), 'form_sync_for_notion_setup_page_form' )) {
        $api_key = sanitize_text_field(wp_unslash($_POST["form_sync_for_notion_api_key"]));
    }
    $database_url = "";
    if(isset($_POST["form_sync_for_notion_database_url"]) && isset($_POST["form_sync_for_notion_setup_page_form_nonce"]) && wp_verify_nonce( sanitize_text_field(wp_unslash($_POST["form_sync_for_notion_setup_page_form_nonce"])), 'form_sync_for_notion_setup_page_form' )) {
        $database_url = sanitize_text_field(wp_unslash($_POST["form_sync_for_notion_database_url"]));
    }
    ?>
        <form method="post" action="">
        <input type="hidden" name="form_sync_for_notion_check_config" value="1">
        <input type="hidden" name="form_sync_for_notion_api_key" value="<?php echo esc_attr($api_key); ?>">
        <?php wp_nonce_field( 'form_sync_for_notion_setup_page_form', 'form_sync_for_notion_setup_page_form_nonce' ); ?>
        <table class="form-table">
            <tr valign="top">
                <th scope="row">Notion Database URL
                </th>
                <td>
                <select name="form_sync_for_notion_database_url">
                <?php foreach($databases AS $database) : ?>
                    <option value="https:www.notion.so/<?php echo esc_attr($database["id"]); ?>"><?php echo esc_html($database["name"]); ?></option>
                <?php endforeach; ?>
                </select>
                </td>
            </tr>
        </table>
        <?php submit_button(); ?>
        </form>
<?php
}


// Display the setup page success
function form_sync_for_notion_setup_page_success() {
    ?>
<div class="wrap">
    <h1>🎉 Congratulations!</h1>
    <div class="postbox">
        <div class="inside">
            <p>Your setup is complete! Your plugin is ready to use.</p>
        </div>
    </div>
    <div class="postbox">
        <div class="inside">
            <h2>Next Steps:</h2>
            <ul>
                <li><strong>Refresh Fields:</strong> Before you create your first form, you need to refresh the content fields from Notion.</li>
                <li><strong>Build Your Form:</strong> Before you create your first form, you need to refresh the content fields from Notion.</li>
                <li><strong>Shortcode:</strong> After you create your form, you can use the shortcode to display the form in your posts or pages.</li>
                <li><strong>Customize Sytles:</strong> Customize your styles in the <a href="<?php echo esc_url(admin_url('admin.php?page=notion-forms-styles')); ?>">Styles</a> page. 
                <li><strong>Documentation:</strong> Visit our <a href="#">documentation</a> for detailed guides and tips.</li>
            </ul>
        </div>
    </div>
    <p>
        <a href="<?php echo esc_url(admin_url('admin.php?page=form-sync-for-notion')); ?>" class="button button-primary">Go to the Form Sync for Notion Page</a>
    </p>
</div>

<?php
}
