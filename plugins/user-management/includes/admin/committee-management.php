<?php

defined( 'ABSPATH' ) || exit;


/**
 * Render Committee Management Tab
 */
function render_committee_management_tab() {
    global $wpdb;

    // Fetch available years
    $years_table = $wpdb->prefix . 'user_management_years';
    $available_years = $wpdb->get_results( "SELECT id, year FROM $years_table ORDER BY year DESC" );

    //Fetch available committees
    $committees_table = $wpdb->prefix . 'committees';
    $available_committees = $wpdb->get_results( "SELECT id, committee_name FROM $committees_table ORDER BY committee_name ASC" );

    // Get the selected year ID and committee
    $selected_year_id = isset( $_POST['year_id'] ) ? intval( $_POST['year_id'] ) : null;
    $selected_committee = isset( $_POST['committee_id'] ) ? sanitize_text_field( $_POST['committee_id'] ) : '';

    // Handle adding a member to a committee
    if ( isset( $_POST['add_member'] ) ) {
        $user_id = intval( $_POST['user_id'] );
        $committee_id = sanitize_text_field( $_POST['committee_id'] );

        add_member_to_committee( $user_id, $committee_id, $selected_year_id );
        echo '<div class="updated"><p>committee added successfully.</p></div>';
    }

    ?>
    <h2>Committee Management</h2>

    <!-- Committee and Year Selection Form -->
    <form method="post">
        <label for="committee_id">Select Committee:</label>
        <select name="committee_id" required>
            <?php foreach ( $available_committees as $committee ) : ?>
                <option value="<?php echo esc_attr( $committee->id ); ?>" <?php selected( $selected_committee, $committee->id ); ?>>
                    <?php echo esc_html( $committee->committee_name ); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="year">Select Year:</label>
        <select name="year_id" required>
            <?php foreach ( $available_years as $year ) : ?>
                <option value="<?php echo esc_attr( $year->id ); ?>" <?php selected( $selected_year_id, $year->id ); ?>>
                    <?php echo esc_html( $year->year ); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit" name="view_members" class="button button-primary">View Members</button>
    </form>

    <?php
    // Check if committee and year have been queried
    if ( isset( $_POST['view_members'] ) && $selected_committee && $selected_year_id ) {
        // Display members of the selected committee and year
        display_committee_members( $selected_committee, $selected_year_id );

        // Show Add Member form after querying members
        ?>
        <h3>Add Member to <?php echo esc_html( get_committee_by_id($selected_committee)); ?> (<?php echo esc_html( get_year_by_id( $selected_year_id ) ); ?>)</h3>
        <form method="post" id="add-member-form">
            <input type="hidden" name="committee_id" value="<?php echo esc_attr( $selected_committee ); ?>">
            <input type="hidden" name="year_id" value="<?php echo esc_attr( $selected_year_id ); ?>">
            <label for="user_id">Select User:</label>
            <select name="user_id" required>
                <?php foreach ( get_users() as $user ) : ?>
                    <option value="<?php echo esc_attr( $user->ID ); ?>"><?php echo esc_html( $user->display_name ); ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="button button-primary">Add Member</button>
        </form>

        <?php
    }
}

/**
 * 
 */
function add_member_to_committee( $user_id, $committee_id, $year_id ) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'committee_memberships';

    $inserted = $wpdb->insert( $table_name, array(
        'user_id'   => $user_id,
        'committee_id' => $committee_id,
        'year_id'   => $year_id,
    ), array( '%d', '%s', '%d' ) );

    return $inserted ? $wpdb->insert_id : false; // Return the inserted ID or false if failed
}

/**
 * Remove a member from a specific committee and year by ID
 */
function remove_member_from_committee_by_id( $membership_id, $committee_id, $year_id ) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'committee_memberships';

    // Delete only the record that matches all three: membership ID, committee, and year ID
    return $wpdb->delete(
        $table_name,
        array(
            'id' => $membership_id,
            'committee_id' => $committee_id,
            'year_id' => $year_id,
        ),
        array( '%d', '%s', '%d' )
    );
}

/**
 * Get committee members for a specific committee and year ID
 */
function get_committee_members( $committee_id, $year_id ) {
    global $wpdb;

    $table_name = $wpdb->prefix . 'committee_memberships';
    $results = $wpdb->get_results( $wpdb->prepare(
        "SELECT cm.id, cm.user_id, cm.committee_id, y.year
         FROM $table_name cm
         INNER JOIN {$wpdb->prefix}user_management_years y ON cm.year_id = y.id
         WHERE cm.committee_id = %d AND cm.year_id = %d",
        $committee_id,
        $year_id
    ));

    return $results;
}

/**
 * 
 */
function display_committee_members( $committee_id, $year_id ) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'committee_memberships';

    $members = $wpdb->get_results( $wpdb->prepare(
        "SELECT * FROM $table_name WHERE committee_id = %d AND year_id = %d",
        $committee_id,
        $year_id
    ));

    // Ensure the table structure is outputted, even if no members are present
    echo '<div class="committee-search-result">';
    echo '<h3>Members of ' . esc_html( get_committee_by_id($committee_id)) . ' (' . esc_html( get_year_by_id( $year_id ) ) . ')</h3>';
    echo '<table class="widefat fixed striped committee-members-table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>';

    if ( empty( $members ) ) {
        echo '<tr class="no-members-row">
            <td colspan="2">No members found for this committee and year.</td>
        </tr>';
    } else {
        foreach ( $members as $member ) {
            $user_info = get_userdata( $member->user_id );

            echo '<tr>
                <td>' . esc_html( $user_info->display_name ) . '</td>
                <td>
                    <button
                        type="button"
                        class="button button-secondary remove-member-button"
                        data-membership-id="' . esc_attr( $member->id ) . '"
                        data-committee-id="' . esc_attr( $committee_id ) . '"
                        data-year-id="' . esc_attr( $year_id ) . '">
                        Remove
                    </button>
                </td>
            </tr>';
        }
    }

    echo '</tbody>
    </table>
    </div>';
}

// // Hook this into your admin page rendering logic
// add_action( 'admin_menu', function() {
//     add_submenu_page(
//         'user-management',
//         'Committee Management',
//         'Committees',
//         'manage_options',
//         'committee-management',
//         'render_committee_management_tab'
//     );
// });








