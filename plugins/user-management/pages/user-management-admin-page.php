<?php

// Determine the active tab
$active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'main_management';

?>
<div class="wrap">
    <h1>User Management</h1>

    <!-- Tabs -->
    <h2 class="nav-tab-wrapper">
        <a href="?page=user-management&tab=main_management" class="nav-tab <?php echo $active_tab === 'main_management' ? 'nav-tab-active' : ''; ?>">
            User Management
        </a>
        <a href="?page=user-management&tab=team_management" class="nav-tab <?php echo $active_tab === 'team_management' ? 'nav-tab-active' : ''; ?>">
            Team Management
        </a>
        <a href="?page=user-management&tab=committee_management" class="nav-tab <?php echo $active_tab === 'committee_management' ? 'nav-tab-active' : ''; ?>">
            Committee Management
        </a>
    </h2>

    <!-- Tab Content -->
    <div class="tab-content">
        <?php
        if ( $active_tab === 'main_management' ) {
            render_main_management_tab();
        } elseif ( $active_tab === 'team_management' ) {
            render_team_management_tab();
        } elseif ( $active_tab === 'committee_management' ) {
            render_committee_management_tab();
        }
        ?>
    </div>
</div>
<?php

/**
 * Render Main Management Tab
 */
function render_main_management_tab() {

    global $wpdb;

    // Committees table
    $committees_table = $wpdb->prefix . 'committees';

    // Handle form submission to add or edit a committee
    if ( isset( $_POST['save_committee'] ) ) {
        $id = intval( $_POST['id'] );
        $committee_name = sanitize_text_field( $_POST['committee_name'] );
        $role_slug = sanitize_text_field( $_POST['role_slug'] );
        $as_activity_type_id = sanitize_text_field( $_POST['as_activity_type_id'] );
        $description = sanitize_textarea_field( $_POST['description'] );

        if ( $id ) {
            // Update existing committee
            $wpdb->update(
                $committees_table,
                compact( 'committee_name', 'role_slug', 'as_activity_type_id', 'description' ),
                array( 'id' => $id ),
                array( '%s', '%s', '%s', '%s' ),
                array( '%d' )
            );
        } else {
            // Insert new committee
            $wpdb->insert(
                $committees_table,
                compact( 'committee_name', 'role_slug', 'as_activity_type_id', 'description' ),
                array( '%s', '%s', '%s', '%s' )
            );
        }
    }

    // Handle committee deletion
    if ( isset( $_POST['delete_committee'] ) ) {
        $id = intval( $_POST['id'] );
        $wpdb->delete( $committees_table, array( 'id' => $id ), array( '%d' ) );
    }

    // Fetch committees
    $committees = $wpdb->get_results( "SELECT * FROM $committees_table ORDER BY committee_name ASC" );

    // Render the management form
    ?>
    <h2>Manage Committees</h2>

    <!-- Add / Edit Committee Form -->
    <form method="post" id="committee-form">
        <input type="hidden" name="id" id="committee-id">
        <label for="committee_name">Committee Name:</label>
        <input type="text" name="committee_name" id="committee-name" required>
        <label for="role_slug">Role Slug:</label>
        <input type="text" name="role_slug" id="role-slug" required>
        <label for="as_activity_type_id">As-Activity-Type ID:</label>
        <input type="text" name="as_activity_type_id" id="as-activity-type-id" >
        <label for="description">Description:</label>
        <textarea name="description" id="description"></textarea>
        <button type="submit" name="save_committee" class="button button-primary">Save Committee</button>
    </form>

    <hr>

    <!-- Committees List -->
    <h3>Existing Committees</h3>
    <table class="widefat striped">
        <thead>
            <tr>
                <th>Committee Name</th>
                <th>Role Slug</th>
                <th>As-Activity-Type ID</th>
                <th>Description</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ( $committees ) : ?>
                <?php foreach ( $committees as $committee ) : ?>
                    <tr>
                        <td><?php echo esc_html( $committee->committee_name ); ?></td>
                        <td><?php echo esc_html( $committee->role_slug ); ?></td>
                        <td><?php echo esc_html( $committee->as_activity_type_id ); ?></td>
                        <td><?php echo esc_html( $committee->description ); ?></td>
                        <td>
                            <button
                                type="button"
                                class="button edit-committee-button"
                                data-id="<?php echo esc_attr( $committee->id ); ?>"
                                data-committee-name="<?php echo esc_attr( $committee->committee_name ); ?>"
                                data-role-slug="<?php echo esc_attr( $committee->role_slug ); ?>"
                                data-as-activity-type-id="<?php echo esc_attr( $committee->as_activity_type_id ); ?>"
                                data-description="<?php echo esc_attr( $committee->description ); ?>">
                                Edit
                            </button>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="id" value="<?php echo esc_attr( $committee->id ); ?>">
                                <button type="submit" name="delete_committee" class="button button-secondary">
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="5">No committees found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    <script>
        jQuery(document).ready(function ($) {
            $(".edit-committee-button").on("click", function () {
                const button = $(this);
                $("#committee-id").val(button.data("id"));
                $("#committee-name").val(button.data("committee-name"));
                $("#role-slug").val(button.data("role-slug"));
                $("#as-activity-type-id").val(button.data("as-activity-type-id"));
                $("#description").val(button.data("description"));
            });
        });
    </script>
    <?php

    $year_table = $wpdb->prefix . 'user_management_years';

    if ( isset( $_POST['add_year'] ) && ! empty( $_POST['new_year'] ) ) {
        $new_year = sanitize_text_field( $_POST['new_year'] );

        // Prevent duplicate years
        $existing_year = $wpdb->get_var( $wpdb->prepare( "SELECT year FROM $year_table WHERE year = %s", $new_year ) );

        if ( $existing_year ) {
            echo '<div class="error"><p>The year already exists.</p></div>';
        } else {
            $wpdb->insert( $year_table, array( 'year' => $new_year ), array( '%s' ) );
            echo '<div class="updated"><p>Year added successfully.</p></div>';
        }
    }

    // Fetch existing years
    $years = $wpdb->get_results( "SELECT id, year FROM $table_name ORDER BY year ASC" );
    ?>
    <h2>Manage Years</h2>

    <form method="post">
        <label for="new_year">Add New Year:</label>
        <input type="text" id="new_year" name="new_year" required>
        <button type="submit" name="add_year" class="button button-primary">Add Year</button>
    </form>

    <table class="widefat striped">
        <thead>
            <tr>
                <th>Year</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ( $years as $year ) : ?>
                <tr>
                    <td><?php echo esc_html( $year->year ); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php
}

/**
 * Render Team Management Tab
 */
function render_team_management_tab() {
    global $wpdb;
    $years_table = $wpdb->prefix . 'user_management_years';

    $available_years = $wpdb->get_results( "SELECT id, year FROM $years_table ORDER BY year ASC" );
    $default_year = end( $available_years );
    $selected_year_id = isset( $_GET['year_id'] ) ? intval( $_GET['year_id'] ) : $default_year->id;

    ?>
    <h2>Team Management</h2>
    <form method="get" action="">
        <input type="hidden" name="page" value="user-management">
        <input type="hidden" name="tab" value="team_management">
        <label for="year">Select Season:</label>
        <select name="year_id" id="year" onchange="this.form.submit();">
            <?php foreach ( $available_years as $year ) : ?>
                <option value="<?php echo esc_attr( $year->id ); ?>" <?php selected( $selected_year_id, $year->id ); ?>>
                    <?php echo esc_html( $year->year ); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>

    <h3>Teams for <?php echo esc_html( $default_year->year ); ?></h3>
    <?php
    $current_page = isset( $_GET['paged'] ) ? max( 1, intval( $_GET['paged'] ) ) : 1;
    $per_page = 10;

    display_teams_for_year( $selected_year_id, $current_page, $per_page );
    ?>
    <form method="post">
        <input type="hidden" name="download_year_id" value="<?php echo esc_attr( $selected_year_id ); ?>">
        <button type="submit" name="download_csv" class="button button-primary">Download CSV</button>
    </form>
    <?php
}


/**
 * Render Committee Management Tab
 */
function render_committee_management_tab() {
    global $wpdb;

    // Fetch available years
    $years_table = $wpdb->prefix . 'user_management_years';
    $available_years = $wpdb->get_results( "SELECT id, year FROM $years_table ORDER BY year DESC" );

    // Get the selected year ID and committee
    $selected_year_id = isset( $_POST['year_id'] ) ? intval( $_POST['year_id'] ) : null;
    $selected_committee = isset( $_POST['committee'] ) ? sanitize_text_field( $_POST['committee'] ) : '';

    // Handle adding a member to a committee
    if ( isset( $_POST['add_member'] ) ) {
        $user_id = intval( $_POST['user_id'] );
        $committee = sanitize_text_field( $_POST['committee'] );

        add_member_to_committee( $user_id, $committee, $selected_year_id );
        echo '<div class="updated"><p>Member added successfully.</p></div>';
    }

    ?>
    <h2>Committee Management</h2>

    <!-- Committee and Year Selection Form -->
    <form method="post">
        <label for="committee">Select Committee:</label>
        <select name="committee" required>
            <option value="WWW-cie" <?php selected( $selected_committee, 'WWW-cie' ); ?>>WWW-cie</option>
            <option value="Social" <?php selected( $selected_committee, 'Social' ); ?>>Social</option>
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
        <h3>Add Member to <?php echo esc_html( $selected_committee ); ?> (<?php echo esc_html( get_year_by_id( $selected_year_id ) ); ?>)</h3>
        <form method="post" id="add-member-form">
            <input type="hidden" name="committee" value="<?php echo esc_attr( $selected_committee ); ?>">
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




?>
