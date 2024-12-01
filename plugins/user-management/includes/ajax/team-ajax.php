<?php

/**
 * 
 */
function ajax_add_team_handler() {
    if (isset($_POST['nonce']) || !wp_verify_nonce( $_POST['nonce'], 'add_team_action' ) ) {
        wp_send_json_error( array('message' => 'Invalid secure token') );
    }

    // Validate and sanitize input
    $team_name = sanitize_text_field( $_POST['name'] );
    $team_slug = sanitize_text_field( $_POST['slug'] );  

    if ( ! $team_name || ! $team_slug ) {
        wp_send_json_error( array('message' => 'All fields are required. ') );
    }

    global $wpdb;
    $teams_table = $wpdb->prefix . 'teams';

    $insert_id = add_team();

    if ($insert_id > 1) {
        wp_send_json_success( array(
            'message' => 'Team added successfully. ', 
            'team' => array(
                'id' => $insert_id, 
                'team_name' => $team_name,
                'team_slug' => $team_slug,
            ),
        ) );
    } else {
        wp_send_json_error( array('message' => 'Something failed when adding the team. ') );
    }
}
add_action( 'wp_ajax_add_team', 'ajax_add_team_handler' );

/**
 * Update a team's info on the Builder Page
 */
function ajax_update_committee_handler() {
    if (isset($_POST['nonce']) || !wp_verify_nonce( $_POST['nonce'], 'update_team_action' ) ) {
        wp_send_json_error( array('message' => 'Invalid secure token') );
    }

    
    // Validate and sanitize input
    $id = intval($_POST['id']);
    $team_name = sanitize_text_field( $_POST['name'] );
    $team_slug = sanitize_text_field( $_POST['slug'] );  

    if ( ! $team_name || ! $team_slug ) {
        wp_send_json_error( array('message' => 'All fields are required. ') );
    }

    global $wpdb;
    $teams_table = $wpdb->prefix . 'teams';

    $updated = $wpdb->update(
        $teams_table,
        array(
            'team_name' => $team_name,
            'team_slug' => $team_slug, 
        ), 
        array('id' => $id),
        array( '%s', '%s')

    );

    if ( $updated === false ) {
        wp_send_json_error( array( 'message' => 'Team added failed' ) );
    } elseif ( $updated = 0 ) {
        wp_send_json_error( array( 'message' => 'No rows updated. Data may be unchanged. ' ) );
    } else {
        wp_send_json_success( array( 'message' => 'Team added successfully' ) );
    }
}
add_action( 'wp_ajax_update_committee', 'ajax_update_committee_handler' );

?>