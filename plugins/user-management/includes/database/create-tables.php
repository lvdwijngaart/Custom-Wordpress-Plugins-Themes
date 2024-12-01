<?php

/**
 * 
 */
function create_user_management_years_table() {
    global $wpdb;

    // Create the years table
    $years_table = $wpdb->prefix . 'user_management_years';

    // Check if the table already exists
    if ( $wpdb->get_var( "SHOW TABLES LIKE '$years_table'" ) != $years_table ) {
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $years_table (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            year VARCHAR(255) NOT NULL,
            PRIMARY KEY (id),
            UNIQUE (year)
        ) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
    }

    // Prepopulate with default years if the table is empty
    $default_years = array( '2023-2024', '2024-2025' );
    $existing_years = $wpdb->get_var( "SELECT COUNT(*) FROM $years_table" );

    if ( $existing_years == 0 ) {
        foreach ( $default_years as $year ) {
            $wpdb->insert( $years_table, array( 'year' => $year ), array( '%s' ) );
        }
    }
}

/**
 * 
 */
function create_committees_table() {
    global $wpdb;

    // Table name for creation
    $table_name = $wpdb->prefix . 'committees';

    // Check if the table already exists
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            committee_name VARCHAR(255) NOT NULL,
            role_slug VARCHAR(100) NOT NULL,
            as_activity_type_id BIGINT(20) NOT NULL,
            description TEXT,
            PRIMARY KEY (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}

/**
 * 
 */
function create_teams_table() {
    global $wpdb;

    // Table name for creation
    $table_name = $wpdb->prefix . 'teams';

    // Check if the table already exists
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            name VARCHAR(255) NOT NULL,
            slug VARCHAR(100) NOT NULL,
            status ENUM('active', 'inactive', 'archived') DEFAULT 'active',  
            PRIMARY KEY (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}

/**
 * 
 */
function create_committee_membership_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'committee_memberships';
    $years_table = $wpdb->prefix . 'user_management_years';
    $committees_table = $wpdb->prefix . 'committees'; // Assuming you have a `committees` table

    // Check if the table already exists
    if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) != $table_name ) {
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT(20) UNSIGNED NOT NULL,
            committee_id BIGINT(20) UNSIGNED NOT NULL,
            year_id BIGINT(20) UNSIGNED NOT NULL,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY committee_id (committee_id),
            KEY year_id (year_id),
            FOREIGN KEY (committee_id) REFERENCES $committees_table (id) ON DELETE CASCADE,
            FOREIGN KEY (year_id) REFERENCES $years_table (id) ON DELETE CASCADE
        ) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
    }
}

/**
 * 
 */
function create_team_membership_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'team_memberships';
    $years_table = $wpdb->prefix . 'user_management_years';
    $teams_table = $wpdb->prefix . 'teams';
    
    // Check if the table already exists
    if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) != $table_name ) {
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT(20) UNSIGNED NOT NULL,
            team_id BIGINT(20) UNSIGNED NOT NULL,
            year_id BIGINT(20) UNSIGNED NOT NULL,
            is_captain TINYINT(1) NOT NULL DEFAULT 0, 
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY team_id (team_id),
            KEY year_id (year_id),
            FOREIGN KEY (team_id) REFERENCES $teams_table (id) ON DELETE CASCADE,
            FOREIGN KEY (year_id) REFERENCES $years_table (id) ON DELETE CASCADE
        ) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
    }
}

function create_activity_types_table() {
    global $wpdb;

    // Table name for creation
    $table_name = $wpdb->prefix . 'as_activity_types';

     // Check if the table already exists
     if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            as_type_slug VARCHAR(100) NOT NULL UNIQUE,
            as_type_name VARCHAR(255) NOT NULL,
            as_type_description TEXT NULL,
            created_by_user_id BIGINT(20) NULL,
            PRIMARY KEY (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
}

// Add more activation tasks here if needed
?>