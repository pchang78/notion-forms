<?php

// Create the database table.
function notion_forms_create_post_types() {
    // Register Custom Post Type for Notion Form Fields
    register_post_type('notion_form_field', array(
        'public' => false,
        'publicly_queryable' => false,
        'show_ui' => false,
        'supports' => array('title'),
        'can_export' => true,
        'delete_with_user' => false
    ));

    // No need for custom tables as we'll use wp_posts and wp_postmeta
}