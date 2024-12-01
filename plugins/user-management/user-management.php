<?php
/*
Plugin Name: User Management
Plugin URI: None
Description: A plugin for handling certain data behind users/members. 
Author: Luca van der Wijngaart
Author URI: https://www.instagram.com/lvdwijngaart/
Version: 1.0.0
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// Define plugin version and plugin directory
// define( 'UM_PLUGIN_NAME', 'user_management' );
define( 'USER_MANAGEMENT_VERSION', '1.0.0' );
define( 'USER_MANAGEMENT_PATH', plugin_dir_path( __FILE__ ) );

// Include files
require_once __DIR__ . '/includes/class-menu.php';
require_once __DIR__ . '/includes/ajax/committee-ajax.php';
require_once __DIR__ . '/includes/ajax/team-ajax.php';
require_once __DIR__ . '/includes/ajax/year-ajax.php';
require_once __DIR__ . '/includes/admin/committee-management.php';
require_once __DIR__ . '/includes/admin/team-management.php';
require_once __DIR__ . '/includes/admin/year-management.php';
require_once __DIR__ . '/includes/admin/builder-page.php';
require_once __DIR__ . '/includes/database/create-tables.php';
require_once __DIR__ . '/includes/helpers.php';


//Ran when the plugin is activated, and tables are created where necessary. See /includes/database/create-tables.php
function user_management_activate() {
    create_committee_membership_table();
    create_user_management_years_table();
    create_committees_table();
    create_activity_types_table();
    create_teams_table();
    create_team_membership_table();
}
register_activation_hook(__FILE__, 'user_management_activate');

//Ran when the plugin is deactivated. 
function user_management_deactivate() {
    // If tables need to be dropped, create a file under /includes/database
}
register_deactivation_hook(__FILE__, 'user_management_deactivate');

//Is ran when the plugin is uninstalled. 
function user_management_uninstall() {
    // If tables need to be dropped, create a file under /includes/database
}
register_uninstall_hook(__FILE__, 'pluginprefix_function_to_run');

// Initialize the menu
if (is_admin()) {
    new User_Management_Admin_Menu();
}

require_once __DIR__ . '/includes/admin/enqueue-scripts.php';

?>