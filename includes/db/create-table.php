<?php

// Create the database table.
function notion_forms_create_table() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'notion_forms';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        column_id VARCHAR(255) NOT NULL,
        name VARCHAR(255) NOT NULL,
        field_type VARCHAR(100) NOT NULL,
        required TINYINT(1) DEFAULT 0 NOT NULL,
        is_active TINYINT(1) DEFAULT 0 NOT NULL,
        order_num INT(11) DEFAULT 0 NOT NULL,
        PRIMARY KEY (id),
        UNIQUE KEY (column_id),
        KEY required (required),
        KEY is_active (is_active)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}