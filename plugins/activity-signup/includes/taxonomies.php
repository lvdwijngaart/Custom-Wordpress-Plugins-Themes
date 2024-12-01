<?php

function create_activity_taxonomy() {
    $labels = array(
        'name' => _x('Activity Types', 'taxonomy general name'),
        'singular_name' => _x('Activity Type', 'taxonomy singular name'),
        // ... other labels
    );

    $args = array(
        'hierarchical' => true, // true for categories-like, false for tags-like
        'labels' => $labels,
        'show_ui' => true,
        'show_in_rest' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'activity-type'),
        'capabilities' => array(
            'manage_terms' => 'manage_activity_types', // Used for managing taxonomy terms
            'edit_terms' => 'manage_activity_types', // Used for editing taxonomy terms
            'delete_terms' => 'manage_activity_types', // Used for deleting taxonomy terms
            'assign_terms' => 'manage_activity_types', // Used for assigning terms to posts
        ),
    );

    register_taxonomy('activity_type', array('as-activity'), $args);
}
// add_action('init', 'create_activity_taxonomy', 0);

function sync_activity_type_create($term_id, $tt_id) {
    global $wpdb;

    // Get the term data
    $term = get_term($term_id, 'activity_type');

    // Insert into the `wp_as_activity_types` table
    $wpdb->insert(
        $wpdb->prefix . 'as_activity_types',
        array(
            'as_type_slug' => $term->slug,
            'as_type_name' => $term->name,
            'as_type_description' => $term->description,
            'created_by_user_id'   => get_current_user_id(), // Assumes a logged-in user
        ),
        array('%s', '%s', '%d')
    );
}
add_action('created_activity_type', 'sync_activity_type_create', 10, 2);

function sync_activity_type_update($term_id, $tt_id) {
    global $wpdb;

    // Get the term data
    $term = get_term($term_id, 'activity_type');

    // Update the `wp_as_activity_types` table
    $wpdb->update(
        $wpdb->prefix . 'as_activity_types',
        array(
            'as_type_slug' => $term->slug,
            'as_type_name' => $term->name,
            'as_type_description' => $term->description,
        ),
        array('as_type_slug' => $term->slug),
        array('%s', '%s'),
        array('%s')
    );
}
add_action('edited_activity_type', 'sync_activity_type_update', 10, 2);

function sync_activity_type_delete($term_id, $tt_id) {
    global $wpdb;

    // Get the term data
    $term = get_term($term_id, 'activity_type');

    // Delete from the `wp_as_activity_types` table
    $wpdb->delete(
        $wpdb->prefix . 'as_activity_types',
        array('as_type_slug' => $term->slug),
        array('%s')
    );
}
add_action('delete_activity_type', 'sync_activity_type_delete', 10, 2);


?>