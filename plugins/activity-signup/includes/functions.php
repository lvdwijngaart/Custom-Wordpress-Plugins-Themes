<?php

function handle_activity_signup() {
    if ( ! isset( $_POST['activity_signup_nonce'] ) || ! wp_verify_nonce( $_POST['activity_signup_nonce'], 'activity_signup' ) ) {
        wp_die( 'Nonce verification failed.' );
    }
    
  
    $activity_id = isset( $_POST['activity_id'] ) ? intval( $_POST['activity_id'] ) : 0;
    $user_id = get_current_user_id(); // It's safer to get the current user ID from the session

    if ( $activity_id && $user_id ) {
      // Retrieve the existing participants
      $participants = get_post_meta($activity_id, 'participants', true) ?: [];
  
      // Check if the user is already signed up
      if ( ! in_array( $user_id, $participants ) ) {
        // Add the current user's ID to the participants array
        $participants[] = $user_id;
        // Update the participants list
        update_post_meta($activity_id, 'participants', $participants);
  
        // Redirect or give a success message
        wp_redirect( get_permalink( $activity_id ) . '?signed_up=1' );
        exit;
      }
    }
  }
  add_action( 'admin_post_signup_for_activity', 'handle_activity_signup' );
//   add_action( 'admin_post_nopriv_signup_activity', 'handle_activity_signup' ); // If you want to allow non-logged users to sign up
  

?>