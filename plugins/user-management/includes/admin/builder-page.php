
<?php 

/**
 * Render Main Management Tab
 */
function render_builder_page() {

    global $wpdb;
    $committees_table = $wpdb->prefix . 'committees';
    $as_activity_types_table = $wpdb->prefix . 'as_activity_types';

    // Handle form submission to add a committee
    if ( isset( $_POST['add_committee'] ) ) {
        $committee_name = sanitize_text_field( $_POST['committee_name'] );
        $role_slug = sanitize_text_field( $_POST['role_slug'] );
        $as_activity_type_id = intval( $_POST['as_activity_type_id'] );
        $description = sanitize_textarea_field( $_POST['description'] );

        add_committee($committee, $role_slug, $as_activity_type_id, $description);
        echo '<div class="updated"><p>Committee added successfully.</p></div>';
    }

    // Handle committee deletion
    if ( isset( $_POST['delete_committee'] ) ) {
        $id = intval( $_POST['id'] );
        $wpdb->delete( $committees_table, array( 'id' => $id ), array( '%d' ) );
    }

    // Fetch committees
    // $committees = $wpdb->get_results( "SELECT * FROM $committees_table ORDER BY committee_name ASC" );
    // Fetch committees with activity type name
    $committees = $wpdb->get_results("
    SELECT c.*, at.name AS activity_type_name
    FROM {$committees_table} c
    LEFT JOIN {$as_activity_types_table} at
    ON c.as_activity_type_id = at.id
    ORDER BY c.committee_name ASC
    ");

    $uneditable_committees = ['um_bestuur', 'wwwcie'];

    // Fetch roles for creation form of committees
    $roles = get_editable_roles();
    $disallowed_roles = ['administrator', 'editor', 'author', 'contributor', 'subscriber', 'um_bestuur', 'wwwcie'];
    $allowed_roles = array_filter(
        $roles,
        function( $role, $role_slug ) use ( $disallowed_roles ) {
            return !in_array( $role_slug, $disallowed_roles, true );
        },
        ARRAY_FILTER_USE_BOTH
    );

    // Fetch activity types for creation form of committees
    $activity_types = $wpdb->get_results( "SELECT * FROM $as_activity_types_table ORDER BY name ASC" );
    
    // Render the management form
    ?>

    <h2>Manage Teams</h2>

    <!-- Add Committee Form -->
    <label for="committee_name">Team Name:</label>
    <input type="text" name="team_name" id="team-name" required>
    <label for="team_slug">Role:</label>
    <input type="text" name="team_slug" id="team-slug" required>
    <button type="submit" name="add_team" class="button button-primary add-team-button" >
        Add Committee
    </button>

    <hr>

    <!-- Committees List -->
    <h3>Existing Teams</h3>
    <table class="widefat striped team-list-table">
        <thead>
            <tr>
                <th>Team Name</th>
                <th>Team Slug</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ( $teams && count( $teams ) > 0 ) : ?>
                <?php foreach ( $teams as $team ) : ?>
                    <tr>
                        <td class="team-name"><?php echo esc_html( $team->name ); ?></td>
                        <td class="team-slug"><?php echo esc_html( $team->slug ); ?></td>
                        <td class="team-status"><?php echo esc_html( $team->status ); ?></td>
                        <?php if ( ! in_array($team->slug, $uneditable_teams, true) ) :?>
                        <td>
                            <button
                                type="button"
                                class="button edit-team-button"
                                data-id="<?php echo esc_attr( $team->id ); ?>"
                                data-team-name="<?php echo esc_attr( $committee->name ); ?>"
                                data-team-slug="<?php echo esc_attr( $committee->slug ); ?>">
                                Edit
                            </button>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="id" value="<?php echo esc_attr( $team->id ); ?>">
                                <button type="submit" name="delete_team" class="button button-secondary">
                                    Delete
                                </button>
                            </form>
                        </td>
                        <?php else: ?>
                        <td></td>
                        <?php endif;  ?>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="5" style="text-align:center;">No teams found. Use the form above to add one.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <hr>

    <!-- COMMITTEE MANAGEMENT -->
    <h2>Manage Committees</h2>

    <!-- Add Committee Form -->
    <label for="committee_name">Committee Name:</label>
    <input type="text" name="committee_name" id="committee-name" required>
    <label for="role_slug">Role:</label>
    <select name="role_slug" id="role-slug" required>
        <?php foreach ( $allowed_roles as $role_slug => $role_details ) : ?>
            <option value="<?php echo esc_attr( $role_slug ); ?>"> 
                <?php echo esc_html( $role_details['name'] ); ?>
            </option>
        <?php endforeach; ?>
    </select>
    <label for="as_activity_type_id">As-Activity-Type:</label>
    <!-- Shows as_type name but value is as_type id -->
    <select name="as_activity_type_id" id="as-activity-type-id" required>
        <option value="" disabled selected>No Activity Type</option> <!-- Empty default option -->
        <?php foreach ( $activity_types as $activity_type ) : ?>
            <option value="<?php echo esc_attr( $activity_type->id ); ?>"> 
                <?php echo esc_html( $activity_type->name ); ?>
            </option>
        <?php endforeach; ?>
    </select>
    <label for="description">Description:</label>
    <textarea name="description" id="description"></textarea>
    <button type="submit" name="add_committee" class="button button-primary add-committee-button" >
        Add Committee
    </button>

    <hr>

    <!-- Committees List -->
    <h3>Existing Committees</h3>
    <table class="widefat striped committee-list-table">
        <thead>
            <tr>
                <th>Committee Name</th>
                <th>Role Slug</th>
                <th>As-Activity-Type Slug</th>
                <th>Description</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ( $committees && count( $committees ) > 0 ) : ?>
                <?php foreach ( $committees as $committee ) : ?>
                    <tr>
                        <td class="committee-name"><?php echo esc_html( $committee->committee_name ); ?></td>
                        <td class="role-slug"><?php echo esc_html( $committee->role_slug ); ?></td>
                        <td class="as-activity-type-id">
                            <?php echo esc_html( $committee->activity_type_name ? $committee->activity_type_name : 'No Activity Type' ); ?>
                        </td>
                        <td class="description"><?php echo esc_html( $committee->description ); ?></td>
                        <?php if ( ! in_array($committee->role_slug, $uneditable_committees, true) ) :?>
                        <td>
                            <button
                                type="button"
                                class="button edit-committee-button"
                                data-id="<?php echo esc_attr( $committee->id ); ?>"
                                data-committee-name="<?php echo esc_attr( $committee->committee_name ); ?>"
                                data-role-slug="<?php echo esc_attr( $committee->role_slug ); ?>"
                                data-as-activity-type-id="<?php echo esc_attr( $committee->as_activity_type_id ); ?>"
                                data-as-activity-type-name="<?php echo esc_attr( $committee->activity_type_name ); ?>"
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
                        <?php 
                            ; else: ?>
                        <td></td>
                        <?php
                            endif;  ?>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="5" style="text-align:center;">No committees found. Use the form above to add one.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <hr>

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
    $years = $wpdb->get_results( "SELECT id, year FROM $year_table ORDER BY year ASC" );
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
                    <!-- Add a column to show which one is the current year, and have buttons to switch 'current/active' to another year -->
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php
}

function add_committee( $committee_name, $role_slug, $as_activity_type_id, $description) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'committees';

    $inserted = $wpdb->insert( $table_name, array(
        'committee_name'   => $committee_name,
        'role_slug' => $role_slug,
        'as_activity_type_id'   => $as_activity_type_id,
        'description' => $description,
    ), array( '%s', '%s', '%d', '%s' ) );

    return $inserted ? $wpdb->insert_id : false; // Return the inserted ID or false if failed
}