<?php

global $wpdb;

    // Handle form submissions for adding or removing members
    if ( isset( $_POST['add_member'] ) ) {
        $user_id   = intval( $_POST['user_id'] );
        $committee = sanitize_text_field( $_POST['committee'] );
        $year      = sanitize_text_field( $_POST['year'] );
        $role      = sanitize_text_field( $_POST['role'] );

        add_member_to_committee( $user_id, $committee, $year, $role );
        echo '<div class="updated"><p>Member added successfully.</p></div>';
    }

    if ( isset( $_POST['remove_member'] ) ) {
        $membership_id = intval( $_POST['membership_id'] );

        remove_member_by_id( $membership_id );
        echo '<div class="updated"><p>Member removed successfully.</p></div>';
    }

    // Select Committee and Year
    $selected_committee = isset( $_POST['committee'] ) ? sanitize_text_field( $_POST['committee'] ) : '';
    $selected_year      = isset( $_POST['year'] ) ? sanitize_text_field( $_POST['year'] ) : '';

    ?>
    <div class="wrap">
        <h1>Committee Management</h1>
        
        <form method="post">
            <label for="committee">Select Committee:</label>
            <select name="committee" required>
                <option value="WWW-cie" <?php selected( $selected_committee, 'WWW-cie' ); ?>>WWW-cie</option>
                <option value="Social" <?php selected( $selected_committee, 'Social' ); ?>>Social</option>
                <!-- Add more committees here -->
            </select>

            <label for="year">Select Year:</label>
            <select name="year" required>
                <option value="2023-2024" <?php selected( $selected_year, '2023-2024' ); ?>>2023-2024</option>
                <option value="2024-2025" <?php selected( $selected_year, '2024-2025' ); ?>>2024-2025</option>
                <!-- Add more years here -->
            </select>

            <button type="submit" class="button button-primary">View Members</button>
        </form>

        <?php if ( $selected_committee && $selected_year ) : ?>
            <h2>Members of <?php echo esc_html( $selected_committee ); ?> (<?php echo esc_html( $selected_year ); ?>)</h2>
            <?php display_committee_members( $selected_committee, $selected_year ); ?>

            <h3>Add Member to <?php echo esc_html( $selected_committee ); ?></h3>
            <form method="post">
                <input type="hidden" name="committee" value="<?php echo esc_attr( $selected_committee ); ?>">
                <input type="hidden" name="year" value="<?php echo esc_attr( $selected_year ); ?>">
                <label for="user_id">Select User:</label>
                <select name="user_id" required>
                    <?php foreach ( get_users() as $user ) : ?>
                        <option value="<?php echo esc_attr( $user->ID ); ?>"><?php echo esc_html( $user->display_name ); ?></option>
                    <?php endforeach; ?>
                </select>

                <label for="role">Role:</label>
                <input type="text" name="role" required>

                <button type="submit" name="add_member" class="button button-primary">Add Member</button>
            </form>
        <?php endif; ?>
    </div>

<?php


function display_committee_members( $committee, $year ) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'committee_memberships';

    $members = $wpdb->get_results( $wpdb->prepare(
        "SELECT * FROM $table_name WHERE committee = %s AND year = %s",
        $committee,
        $year
    ));

    if ( empty( $members ) ) {
        echo '<p>No members found for this committee and year.</p>';
        return;
    }

    echo '<table class="widefat fixed" cellspacing="0">
        <thead>
            <tr>
                <th>Name</th>
                <th>Role</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>';
    
    foreach ( $members as $member ) {
        $user_info = get_userdata( $member->user_id );

        echo '<tr>
            <td>' . esc_html( $user_info->display_name ) . '</td>
            <td>' . esc_html( $member->role ) . '</td>
            <td>
                <form method="post" style="display:inline;">
                    <input type="hidden" name="membership_id" value="' . esc_attr( $member->id ) . '">
                    <button
                        type="button"
                        class="button button-secondary remove-member-button"
                        data-membership-id="<?php echo esc_attr( $member->id ); ?>">
                        Remove
                    </button>
                </form>
            </td>
        </tr>';
    }

    echo '</tbody></table>';
}

