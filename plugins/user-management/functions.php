<?php

/**
 * 
//  */
// function add_member_to_committee( $user_id, $committee, $year_id ) {
//     global $wpdb;
//     $table_name = $wpdb->prefix . 'committee_memberships';

//     $inserted = $wpdb->insert( $table_name, array(
//         'user_id'   => $user_id,
//         'committee' => $committee,
//         'year_id'   => $year_id,
//     ), array( '%d', '%s', '%d' ) );

//     return $inserted ? $wpdb->insert_id : false; // Return the inserted ID or false if failed
// }

// /**
//  * Remove a member from a specific committee and year by ID
//  */
// function remove_member_from_committee_by_id( $membership_id, $committee, $year_id ) {
//     global $wpdb;
//     $table_name = $wpdb->prefix . 'committee_memberships';

//     // Delete only the record that matches all three: membership ID, committee, and year ID
//     return $wpdb->delete(
//         $table_name,
//         array(
//             'id' => $membership_id,
//             'committee' => $committee,
//             'year_id' => $year_id,
//         ),
//         array( '%d', '%s', '%d' )
//     );
// }


// /**
//  * Get committee members for a specific committee and year ID
//  */
// function get_committee_members( $committee, $year_id ) {
//     global $wpdb;

//     $table_name = $wpdb->prefix . 'committee_memberships';
//     $results = $wpdb->get_results( $wpdb->prepare(
//         "SELECT cm.id, cm.user_id, cm.committee, y.year
//          FROM $table_name cm
//          INNER JOIN {$wpdb->prefix}user_management_years y ON cm.year_id = y.id
//          WHERE cm.committee = %s AND cm.year_id = %d",
//         $committee,
//         $year_id
//     ));

//     return $results;
// }

// /**
//  * 
//  */
// function display_committee_members( $committee, $year_id ) {
//     global $wpdb;
//     $table_name = $wpdb->prefix . 'committee_memberships';

//     $members = $wpdb->get_results( $wpdb->prepare(
//         "SELECT * FROM $table_name WHERE committee = %s AND year_id = %d",
//         $committee,
//         $year_id
//     ));

//     // Ensure the table structure is outputted, even if no members are present
//     echo '<div class="committee-search-result">';
//     echo '<h3>Members of ' . esc_html( $committee ) . ' (' . esc_html( get_year_by_id( $year_id ) ) . ')</h3>';
//     echo '<table class="widefat fixed striped committee-members-table">
//         <thead>
//             <tr>
//                 <th>Name</th>
//                 <th>Actions</th>
//             </tr>
//         </thead>
//         <tbody>';

//     if ( empty( $members ) ) {
//         echo '<tr class="no-members-row">
//             <td colspan="2">No members found for this committee and year.</td>
//         </tr>';
//     } else {
//         foreach ( $members as $member ) {
//             $user_info = get_userdata( $member->user_id );

//             echo '<tr>
//                 <td>' . esc_html( $user_info->display_name ) . '</td>
//                 <td>
//                     <button
//                         type="button"
//                         class="button button-secondary remove-member-button"
//                         data-membership-id="' . esc_attr( $member->id ) . '"
//                         data-committee="' . esc_attr( $committee ) . '"
//                         data-year-id="' . esc_attr( $year_id ) . '">
//                         Remove
//                     </button>
//                 </td>
//             </tr>';
//         }
//     }

//     echo '</tbody>
//     </table>
//     </div>';
// }



// /**
//  * Display teams for a specific year with pagination
//  */
// function display_teams_for_year( $year_id, $current_page = 1, $per_page = 10 ) {
//     global $wpdb;

//     $table_name = $wpdb->prefix . 'committee_memberships';

//     $offset = ( $current_page - 1 ) * $per_page;

//     $results = $wpdb->get_results( $wpdb->prepare(
//         "SELECT cm.user_id, cm.committee AS team, y.year
//          FROM $table_name cm
//          INNER JOIN {$wpdb->prefix}user_management_years y ON cm.year_id = y.id
//          WHERE cm.year_id = %d AND cm.committee = 'Team'
//          ORDER BY cm.committee ASC
//          LIMIT %d, %d",
//         $year_id,
//         $offset,
//         $per_page
//     ));

//     $total_count = $wpdb->get_var( $wpdb->prepare(
//         "SELECT COUNT(*)
//          FROM $table_name cm
//          WHERE cm.year_id = %d AND cm.committee = 'Team'",
//         $year_id
//     ));

//     $total_pages = ceil( $total_count / $per_page );

//     if ( ! empty( $results ) ) {
//         echo '<table class="widefat fixed striped">
//             <thead>
//                 <tr>
//                     <th>Name</th>
//                     <th>Team</th>
//                     <th>Year</th>
//                 </tr>
//             </thead>
//             <tbody>';

//         foreach ( $results as $row ) {
//             $user_info = get_userdata( $row->user_id );

//             echo '<tr>
//                 <td>' . esc_html( $user_info->display_name ) . '</td>
//                 <td>' . esc_html( $row->team ) . '</td>
//                 <td>' . esc_html( $row->year ) . '</td>
//             </tr>';
//         }

//         echo '</tbody>
//         </table>';
//     } else {
//         echo '<p>No teams found for the selected year.</p>';
//     }

//     if ( $total_pages > 1 ) {
//         echo '<div class="tablenav">
//             <div class="tablenav-pages">';
//         for ( $i = 1; $i <= $total_pages; $i++ ) {
//             $class = $i === $current_page ? 'current' : '';
//             $url = add_query_arg( array( 'paged' => $i, 'year_id' => $year_id ) );
//             echo '<a class="page-numbers ' . esc_attr( $class ) . '" href="' . esc_url( $url ) . '">' . esc_html( $i ) . '</a>';
//         }
//         echo '</div>
//         </div>';
//     }
// }

// /**
//  * Download teams CSV
//  */
// function download_teams_csv( $year_id ) {
//     global $wpdb;

//     $table_name = $wpdb->prefix . 'committee_memberships';

//     $results = $wpdb->get_results( $wpdb->prepare(
//         "SELECT cm.user_id, cm.committee AS team, y.year
//          FROM $table_name cm
//          INNER JOIN {$wpdb->prefix}user_management_years y ON cm.year_id = y.id
//          WHERE cm.year_id = %d AND cm.committee = 'Team'
//          ORDER BY cm.committee ASC",
//         $year_id
//     ));

//     header( 'Content-Type: text/csv' );
//     header( 'Content-Disposition: attachment;filename=team_members_' . $year_id . '.csv' );

//     $output = fopen( 'php://output', 'w' );
//     fputcsv( $output, array( 'Name', 'Team', 'Year' ) );

//     foreach ( $results as $row ) {
//         $user_info = get_userdata( $row->user_id );
//         fputcsv( $output, array(
//             $user_info->display_name,
//             $row->team,
//             $row->year
//         ));
//     }

//     fclose( $output );
//     exit;
// }

// /**
//  * 
//  */
// function ajax_add_member_to_committee() {
//     // Verify nonce for security
//     if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'add_member_action' ) ) {
//         wp_send_json_error( array( 'message' => 'Invalid security token.' ) );
//     }

//     // Validate and sanitize input
//     $user_id   = intval( $_POST['user_id'] );
//     $committee = sanitize_text_field( $_POST['committee'] );
//     $year_id   = intval( $_POST['year_id'] );

//     if ( ! $user_id || ! $committee || ! $year_id ) {
//         wp_send_json_error( array( 'message' => 'Invalid input provided.' ) );
//     }

//      // Call the backend function to add the member
//      $result = add_member_to_committee( $user_id, $committee, $year_id );

//      if ( $result ) {
//         $user_info = get_userdata( $user_id );
//         wp_send_json_success( array(
//             'message' => 'Member added successfully.',
//             'member'  => array(
//                 'id'       => $result,   //Return the new record ID
//                 'name'     => $user_info->display_name,
//                 'committee' => $committee,
//                 'year_id'  => $year_id,
//             ),
//         ));
//     } else {
//         wp_send_json_error( array( 'message' => 'Failed to add member. Please try again.' ) );
//     }
// }
// add_action( 'wp_ajax_add_member', 'ajax_add_member_to_committee' );

// /**
//  * Handle AJAX request to remove a member from a committee
//  */
// function ajax_remove_member_from_committee() {
//     // Verify nonce for security
//     if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'remove_member_action' ) ) {
//         wp_send_json_error( array( 'message' => 'Invalid security token.' ) );
//     }

//     // Validate input
//     $membership_id = intval( $_POST['membership_id'] );
//     $committee = sanitize_text_field( $_POST['committee'] );
//     $year_id = intval( $_POST['year_id'] );

//     if ( $membership_id && $committee && $year_id ) {
//         // Attempt to delete the member record
//         $deleted = remove_member_from_committee_by_id( $membership_id, $committee, $year_id );

//         if ( $deleted ) {
//             wp_send_json_success( array( 'message' => 'Member removed successfully.' ) );
//         } else {
//             wp_send_json_error( array( 'message' => 'Failed to remove member. Please ensure the member exists and try again.' ) );
//         }
//     } else {
//         wp_send_json_error( array( 'message' => 'Invalid input provided.' ) );
//     }
// }
// add_action( 'wp_ajax_remove_member', 'ajax_remove_member_from_committee' );


// function enqueue_custom_admin_script( $hook ) {
//     if ( $hook !== 'toplevel_page_user-management' ) {
//         return;
//     }

//     wp_enqueue_script(
//         'user-management-js',
//         plugin_dir_url( __FILE__ ) . 'js/user-management.js',
//         array( 'jquery' ),
//         USER_MANAGEMENT_VERSION,
//         true
//     );

//     // Combine both nonces in a single localized object
//     wp_localize_script( 'user-management-js', 'userManagementAjax', array(
//         'ajaxUrl' => admin_url( 'admin-ajax.php' ),
//         'nonces'  => array(
//             'remove_member' => wp_create_nonce( 'remove_member_action' ),
//             'add_member'    => wp_create_nonce( 'add_member_action' ),
//         ),
//     ) );
// }
// add_action( 'admin_enqueue_scripts', 'enqueue_custom_admin_script' );

// /**
//  * Utility function to get the year by its ID.
//  *
//  * @param int $year_id The year ID.
//  * @return string|null The year name or null if not found.
//  */
// function get_year_by_id( $year_id ) {
//     global $wpdb;
//     $years_table = $wpdb->prefix . 'user_management_years';
//     return $wpdb->get_var( $wpdb->prepare( "SELECT year FROM $years_table WHERE id = %d", $year_id ) );
// }
?>
