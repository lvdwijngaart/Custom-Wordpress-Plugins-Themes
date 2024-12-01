<?php 

require_once USER_MANAGEMENT_PATH . 'includes\admin\builder-page.php';

/**
 * 
 */
function ajax_add_member_to_committee() {
    // Verify nonce for security
    if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'add_member_action' ) ) {
        wp_send_json_error( array( 'message' => 'Invalid security token.' ) );
    }

    // Validate and sanitize input
    $user_id   = intval( $_POST['user_id'] );
    $committee_id = intval( $_POST['committee_id'] );
    $year_id   = intval( $_POST['year_id'] );

    if ( ! $user_id || ! $committee_id || ! $year_id ) {
        wp_send_json_error( array( 'message' => 'Invalid input provided.' ) );
    }

     // Call the backend function to add the member
     $result = add_member_to_committee( $user_id, $committee_id, $year_id );
     $committee_name = get_committee_by_id($committee_id);

     if ( $result ) {
        $user_info = get_userdata( $user_id );
        wp_send_json_success( array(
            'message' => 'Member added successfully.',
            'member'  => array(
                'id'       => $result,   //Return the new record ID
                'name'     => $user_info->display_name,
                'committee_id' => $committee_id,
                'committee_name' => $committee_name,
                'year_id'  => $year_id,
            ),
        ));
    } else {
        wp_send_json_error( array( 'message' => 'Failed to add member. Please try again.' ) );
    }
}
add_action( 'wp_ajax_add_member', 'ajax_add_member_to_committee' );

/**
 * Handle AJAX request to remove a member from a committee
 */
function ajax_remove_member_from_committee() {
    // Verify nonce for security
    if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'remove_member_action' ) ) {
        wp_send_json_error( array( 'message' => 'Invalid security token.' ) );
    }

    // Validate input
    $membership_id = intval( $_POST['membership_id'] );
    $committee_id = intval( $_POST['committee_id'] );
    $year_id = intval( $_POST['year_id'] );

    if ( $membership_id && $committee_id && $year_id ) {
        // Attempt to delete the member record
        $deleted = remove_member_from_committee_by_id( $membership_id, $committee_id, $year_id );

        if ( $deleted ) {
            wp_send_json_success( array( 'message' => 'Member removed successfully.' ) );
        } else {
            wp_send_json_error( array( 'message' => 'Failed to remove member. Please ensure the member exists and try again.' ) );
        }
    } else {
        wp_send_json_error( array( 'message' => 'Invalid input provided.' ) );
    }
}
add_action( 'wp_ajax_remove_member', 'ajax_remove_member_from_committee' );


function ajax_add_committee_handler() {
    // Verify nonce
    if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'add_committee_action' ) ) {
        wp_send_json_error( array( 'message' => 'Invalid security token.' ) );
    }

    $committee_name = sanitize_text_field($_POST['committee_name']);
    $role_slug = sanitize_text_field($_POST['role_slug']);
    $as_activity_type_id = intval($_POST['as_activity_type_id']);
    $as_activity_type_name = sanitize_text_field($_POST['as_activity_type_name']);
    $description = sanitize_text_field($_POST['description']);

    if ( ! $committee_name || ! $role_slug || ! $as_activity_type_id || ! $description ) {
        wp_send_json_error( array( 'message' => 'All fields are required.' ) );
    }

    //Find the committee table
    global $wpdb;
    $table_name = $wpdb->prefix . 'committees';

    // Use `add_committee` to perform the insertion
    $insert_id = add_committee( $committee_name, $role_slug, $as_activity_type_id, $description );

    if ( $insert_id >= 1 ) {
        wp_send_json_success( array(
            'message' => 'Committee added successfully.',
            'committee' => array(
                'id'                    => $insert_id,
                'committee_name'        => $committee_name,
                'role_slug'             => $role_slug,
                'as_activity_type_id'   => $as_activity_type_id,
                'as_activity_type_name' => $as_activity_type_name,
                'description'           => $description,
            ),
        ) );
    } else {
        wp_send_json_error( array( 'message' => 'Failed to add the committee. ' . $insert_id . $committee_name . $role_slug . $as_activity_type_id . $description  ) );
    }

}
add_action( 'wp_ajax_add_committee', 'ajax_add_committee_handler' );


/**
 * 
 */
function update_committee_handler() {
    // Verify nonce
    if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'update_committee_action' ) ) {
        wp_send_json_error( array( 'message' => 'Invalid security token.' ) );
    }

    // Validate and sanitize inputs
    $id = intval( $_POST['id'] );
    $committee_name = sanitize_text_field( $_POST['committee_name'] );
    $role_slug = sanitize_text_field( $_POST['role_slug'] );
    $as_activity_type_id = intval( $_POST['as_activity_type_id'] );
    $description = sanitize_text_field( $_POST['description'] );

    if ( ! $id || ! $committee_name || ! $role_slug || ! $as_activity_type_id || ! $description ) {
        wp_send_json_error( array( 'message' => 'All fields are required.' ) );
    }

    //Find the committee table
    global $wpdb;
    $table_name = $wpdb->prefix . 'committees';

    // Perform the update
    $updated = $wpdb->update(
        $table_name,
        array(
            'committee_name' => $committee_name,
            'role_slug' => $role_slug,
            'as_activity_type_id' => $as_activity_type_id,
            'description' => $description,
        ),
        array( 'id' => $id ),
        array( '%s', '%s', '%d', '%s' ), 
        array( '%s' )
    );

    if ( $updated === false ) {
        wp_send_json_error( array( 'message' => 'Failed to update the committee.' ) );
    } elseif ($updated === 0) {
        wp_send_json_error( array( 'message' => 'No rows updated. Data may be unchanged. ' ) );
    } else {
        wp_send_json_success( array( 'message' => 'Committee updated successfully.' ) ); 
    }
}
add_action( 'wp_ajax_update_committee', 'update_committee_handler' );

?>