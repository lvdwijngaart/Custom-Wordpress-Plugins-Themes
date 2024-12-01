<?php

/**
 * 
 */
function enqueue_custom_admin_script( $hook ) {
    if ( $hook !== 'toplevel_page_user-management' ) {
        return;
    }

    wp_enqueue_style(
        'committee-management-admin-css', 
        plugin_dir_url( __FILE__ ) . '../../css/style.css',
        array(),
        USER_MANAGEMENT_VERSION,
        'all'
    );

    wp_enqueue_script(
        'committee-management-js',
        plugin_dir_url( __FILE__ ) . '../../js/committee-management.js',
        array( 'jquery' ),
        USER_MANAGEMENT_VERSION,
        true
    );

    enqueue_builder_page_script();

    enqueue_team_management_script();

    enqueue_committee_management_script();
        
    //Localize all WP roles to pass on to the JS file
    $roles = get_editable_roles();
    $disallowed_roles = ['administrator', 'editor', 'author', 'contributor', 'subscriber', 'um_bestuur', 'wwwcie'];
    $localized_roles = array();

    foreach ( $roles as $role_slug => $role_details ) {
        if ( ! in_array($role_slug, $disallowed_roles, true )) {
            $localized_roles[ $role_slug ] = $role_details['name'];
        }
    }

    global $wpdb;
    $as_activity_types_table = $wpdb->prefix . 'as_activity_types';
    
    $activity_types = $wpdb->get_results("SELECT id, name FROM $as_activity_types_table ORDER BY name ASC", ARRAY_A);

    // Convert to key-value pairs (id => name) for simplicity if needed
    $localized_activity_types = array_map(function($type) {
        return ['id' => $type['id'], 'name' => $type['name']];
    }, $activity_types);
    
    wp_localize_script('committee-management-js', 'committeeManagementAjax', array(
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonces'  => array(
            'remove_member' => wp_create_nonce('remove_member_action'),
            'add_member'    => wp_create_nonce('add_member_action'),
            'add_committee' => wp_create_nonce('add_committee_action'),
            'update_committee' => wp_create_nonce('update_committee_action'),
        ),
        'roles' => $localized_roles,
        'activity_types' => $localized_activity_types,
    ));
}
add_action( 'admin_enqueue_scripts', 'enqueue_custom_admin_script' );

/**
 * Enqueueing the Javascript for the plugin's builder page. 
 * 
 * The builder page has the following functionalities:
 * * Managing teams
 * * * Adding a team to the list of teams
 * * * Updating a team in the list of teams
 * * * Making a team inactive (will be shown greyed out)
 * 
 * * Managing committees
 * * * Adding a committee to the list of committees
 * * * Updating a committee in the list of committees
 * * * Deleting a committee from the list of committees
 * 
 * * Managing seasons
 * * * Adding a seasons to the list of seasons
 * * * Updating a seasons in the list of seasons
 * * * Making a seasons active/inactive (only one season should be active, the current season)
 */
function enqueue_builder_page_script() {

    wp_enqueue_script(
        'builder-page-js',
        USER_MANAGEMENT_PATH . '/js/committee-management.js', //TODO!
        array( 'jquery' ),
        USER_MANAGEMENT_VERSION,
        true
    );

    //Initialize any variables you want present in the script
    

    wp_localize_script( 'builder-page-js', 'builderPageAjax', array(
        'ajaxUrl' => admin_url( 'admin-ajax.php' ),
        'nonces' => array(
            'add_team' => wp_create_nonce( 'add_team_action' ),
            'update_team' => wp_create_nonce( 'update_team_action' ), 
        ), 
        //any other variables you want to be able to use in the script
    ));
}

/**
 * Enqueueing the Javascript for the Team Management page. 
 * 
 * The Team Management page has the following functionalities:
 * * View a list of teams and members 
 * * Search by a certain filter in a list of teams and members
 * * Download a csv of the team relations in a certain season
 * * Upload a csv of the team relations in a certain season
 */
function enqueue_team_management_script() {

    wp_enqueue_script(
        'team-management-js', 
        USER_MANAGEMENT_PATH . 'js/team-management', 
        array( 'jquery' ), 
        USER_MANAGEMENT_VERSION, 
        true
    );

    // Initialise any variables that you want present in the script

    // Localize any nonces and variables in the script

}

/**
 * Enqueueing the Javascript for the Committee Management page. 
 * 
 * The Committee Management page has the following functionalities:
 * * View a list of committees and members 
 * * Search by a certain filter in a list of committees and members
 * * Add a user to a certain committee
 * * Remove a user from a certain committee
 */
function enqueue_committee_management_script() {

    wp_enqueue_script(
        'committee-management-js', 
        USER_MANAGEMENT_PATH . 'js/committee-management', 
        array( 'jquery' ), 
        USER_MANAGEMENT_VERSION, 
        true
    );

    // Initialise any variables that you want present in the script

    // Localize any nonces and variables in the script
    
}

?>