<?php

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
        <input type="hidden" name="download_year_id" value="<?php echo esc_attr($selected_year_id); ?>">
        <button type="submit" name="download_csv" class="button button-primary">Download CSV</button>
    </form>
    <?php
}


function handle_csv_download_request() {
    if (isset($_POST['download_csv']) && isset($_POST['download_year_id'])) {
        $year_id = intval($_POST['download_year_id']);
        download_teams_csv($year_id); // Call your CSV download function
    }
}
add_action('admin_init', 'handle_csv_download_request');


/**
 * Display teams for a specific year with pagination
 */
function display_teams_for_year( $year_id, $current_page = 1, $per_page = 10 ) {
    global $wpdb;

    $table_name = $wpdb->prefix . 'committee_memberships';

    $offset = ( $current_page - 1 ) * $per_page;

    $results = $wpdb->get_results( $wpdb->prepare(
        "SELECT cm.user_id, cm.committee AS team, y.year
         FROM $table_name cm
         INNER JOIN {$wpdb->prefix}user_management_years y ON cm.year_id = y.id
         WHERE cm.year_id = %d AND cm.committee = 'Team'
         ORDER BY cm.committee ASC
         LIMIT %d, %d",
        $year_id,
        $offset,
        $per_page
    ));

    $total_count = $wpdb->get_var( $wpdb->prepare(
        "SELECT COUNT(*)
         FROM $table_name cm
         WHERE cm.year_id = %d AND cm.committee = 'Team'",
        $year_id
    ));

    $total_pages = ceil( $total_count / $per_page );

    if ( ! empty( $results ) ) {
        echo '<table class="widefat fixed striped">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Team</th>
                    <th>Year</th>
                </tr>
            </thead>
            <tbody>';

        foreach ( $results as $row ) {
            $user_info = get_userdata( $row->user_id );

            echo '<tr>
                <td>' . esc_html( $user_info->display_name ) . '</td>
                <td>' . esc_html( $row->team ) . '</td>
                <td>' . esc_html( $row->year ) . '</td>
            </tr>';
        }

        echo '</tbody>
        </table>';
    } else {
        echo '<p>No teams found for the selected year.</p>';
    }

    if ( $total_pages > 1 ) {
        echo '<div class="tablenav">
            <div class="tablenav-pages">';
        for ( $i = 1; $i <= $total_pages; $i++ ) {
            $class = $i === $current_page ? 'current' : '';
            $url = add_query_arg( array( 'paged' => $i, 'year_id' => $year_id ) );
            echo '<a class="page-numbers ' . esc_attr( $class ) . '" href="' . esc_url( $url ) . '">' . esc_html( $i ) . '</a>';
        }
        echo '</div>
        </div>';
    }
}

/**
 * Download teams CSV
 */
function download_teams_csv($year_id) {
    global $wpdb;

    $table_name = $wpdb->prefix . 'committee_memberships';

    // Fetch data for the selected year and teams
    $results = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT cm.user_id, cm.committee AS team, y.year
             FROM $table_name cm
             INNER JOIN {$wpdb->prefix}user_management_years y ON cm.year_id = y.id
             WHERE cm.year_id = %d AND cm.committee = 'Team'
             ORDER BY cm.committee ASC",
            $year_id
        )
    );

    // Set headers to indicate a CSV file download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment;filename=team_members_' . $year_id . '.csv');

    // Open PHP output stream
    $output = fopen('php://output', 'w');

    // Write the CSV headers
    fputcsv($output, array('Name', 'Team', 'Year'));

    // Write each row of team data
    foreach ($results as $row) {
        $user_info = get_userdata($row->user_id); // Get user info by ID
        fputcsv($output, array(
            $user_info->display_name,
            $row->team,
            $row->year
        ));
    }

    // Close the output stream and terminate the script
    fclose($output);
    exit;
}



//  function download_teams_csv( $year_id ) {
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
