<?php

// Create the database table.
function form_sync_for_notion_create_post_types() {
    // Register Custom Post Type for Notion Form Fields
    register_post_type('notion_form_field', array(
        'labels' => array(
            'name' => 'Notion Form Fields',
            'singular_name' => 'Notion Form Field'
        ),
        'public' => false,
        'show_ui' => false,
        'supports' => array('title', 'custom-fields'),
        'has_archive' => false
    ));

    // No need for custom tables as we'll use wp_posts and wp_postmeta
}