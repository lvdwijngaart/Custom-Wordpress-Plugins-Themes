<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://example.com
 * @since             1.0.0
 * @package           Activity_Signup
 *
 * @wordpress-plugin
 * Plugin Name:       Activity Signups
 * Plugin URI:        http://example.com/activity-signup-uri/
 * Description:       This plugin will handle signups to activities.
 * Version:           1.0.0
 * Author:            Luca van der Wijngaart
 * Author URI:        http://example.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       activity-signup
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'ACTIVITY_SIGNUP_VERSION', '1.0.0' );

// Include the necessary files.
require_once plugin_dir_path( __FILE__ ) . 'includes/class-activity-post-type.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-activity-type.php';

/**
* The core plugin class that is used to define internationalization,
* admin-specific hooks, and public-facing site hooks.
*/
require plugin_dir_path( __FILE__ ) . 'includes/functions.php';
require plugin_dir_path( __FILE__ ) . 'includes/class-activity-signup.php';
require plugin_dir_path( __FILE__ ) . 'includes/taxonomies.php';
require plugin_dir_path( __FILE__ ) . 'includes/meta-boxes.php';
require plugin_dir_path( __FILE__ ) . 'includes/shortcodes.php';
require plugin_dir_path( __FILE__ ) . 'includes/admin-notices.php';

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-activity-signup-activator.php
 */
function activate_activity_signup() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-activity-signup-activator.php';
	Activity_Signup_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-activity-signup-deactivator.php
 */
function deactivate_activity_signup() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-activity-signup-deactivator.php';
	Activity_Signup_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_activity_signup' );
register_deactivation_hook( __FILE__, 'deactivate_activity_signup' );

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_activity_signup() {

    $plugin = new Activity_Signup();
    $plugin->run();

}
run_activity_signup();

$plugin_public = new Activity_Signup_Public( 'activity_signup', '1.0.0' );
$plugin_admin = new Activity_Signup_Admin( 'activity_signup', '1.0.0' );

// Create an instance of your loader class.
$loader = new Activity_Signup_Loader();

// Add the enqueue_styles method of the public class to the loader's actions.
$loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );

// Add the styles and scripts of the admin class to the loader's actions. 
$loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
$loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

// Run the loader to execute all actions and filters.
$loader->run();

function filter_posts_by_activity_type($query) {
    if (is_admin() && $query->is_main_query() && $query->get('post_type') === 'as-activity') {
        // Get the current user's allowed roles
        $current_user = wp_get_current_user();
        $user_roles = $current_user->roles;

        if (empty($user_roles)) {
            // Prevent posts from being shown if the user has no roles
            $query->set('post__in', array(0));
            return;
        }
        // Check if the current user is an administrator
        if (in_array('administrator', $current_user->roles, true)) {
            // Administrators can see all posts, no need to filter
            return;
        }

        // Fetch allowed activity types from the database
        global $wpdb;

        // Build a dynamic SQL query for allowed roles
        $placeholders = implode(' OR ', array_fill(0, count($user_roles), "FIND_IN_SET(%s, allowed_roles) > 0"));
        $query_string = "
            SELECT id 
            FROM {$wpdb->prefix}as_activity_types 
            WHERE $placeholders
        ";

        $allowed_types = $wpdb->get_results($wpdb->prepare($query_string, $user_roles));

        // If the user has allowed types, filter posts by their activity type meta
        if (!empty($allowed_types)) {
            $allowed_type_ids = wp_list_pluck($allowed_types, 'id');

            $meta_query = array(
                array(
                    'key' => '_activity_types', // Custom meta field storing the associated activity type IDs
                    'value' => $allowed_type_ids,
                    'compare' => 'IN'
                ),
            );
            $query->set('meta_query', $meta_query);
        } else {
            // If no activity types are allowed, prevent any posts from being shown
            $query->set('post__in', array(0));
        }
    }
}
add_action('pre_get_posts', 'filter_posts_by_activity_type');



function get_allowed_activity_types_for_current_user_from_db($user) {
    global $wpdb;

    $allowed_activity_types = array();

    // Get the current user's roles
    $user_roles = $user->roles;

    if (empty($user_roles)) {
        return $allowed_activity_types; // Return empty if user has no roles
    }

    // Convert user roles to a format suitable for SQL
    $roles_placeholder = implode(',', array_fill(0, count($user_roles), '%s'));

    // Query the database for activity types where the allowed_roles column matches any of the user's roles
    $results = $wpdb->get_results(
        $wpdb->prepare(
            "
            SELECT slug 
            FROM {$wpdb->prefix}as_activity_types 
            WHERE 
                allowed_roles REGEXP CONCAT('(^|,)', ?, '(,|$)')
            ",
            implode('|', $user_roles) // Use a regular expression to match any role
        )
    );

    // Extract slugs from results
    foreach ($results as $result) {
        $allowed_activity_types[] = $result->slug;
    }

    return $allowed_activity_types;
}





// function filter_activity_posts_by_user_role($query) {
//     // Check if we are in admin and the main query is being modified
//     if (is_admin() && $query->is_main_query()) {
//         // Get the current user info
//         $current_user = wp_get_current_user();

//         // Continue only if the query is for as-activity post type
//         if ('as-activity' === $query->get('post_type')) {
//             // Determine the allowed activity types for the current user based on roles
//             $allowed_activity_types = get_allowed_activity_types_for_current_user($current_user);

//             // If the user is allowed to see certain activity types
//             if (!empty($allowed_activity_types)) {
//                 // Set the tax_query for the activity_type taxonomy
//                 $tax_query = array(
//                     array(
//                         'taxonomy' => 'activity_type',
//                         'field' => 'slug',
//                         'terms' => $allowed_activity_types,
//                     ),
//                 );

//                 // Modify the query
//                 $query->set('tax_query', $tax_query);
//             }
//         }
//     }
// }add_action('pre_get_posts', 'filter_activity_posts_by_user_role');

// function get_allowed_activity_types_for_current_user($user) {
//     $allowed_activity_types = array();

//     // Get all activity types
//     $all_activity_types = get_terms(array(
//         'taxonomy' => 'activity_type',
//         'hide_empty' => false,
//     ));

//     // Loop through each activity type
//     foreach ($all_activity_types as $activity_type) {
//         // Check if the user's role is allowed for this activity type
//         if (check_activity_type_allowed_roles($activity_type->term_id)) {
//             // Add the activity type to the allowed list
//             $allowed_activity_types[] = $activity_type->slug;
//         }
//     }

//     return array_unique($allowed_activity_types);
// }

// function check_activity_type_allowed_roles($term_id) {
//     // Retrieve allowed roles for the activity type
//     $allowed_roles = get_term_meta($term_id, 'activity_type_roles', true);

//     if (!$allowed_roles) {
//         // No allowed roles defined for this activity type
//         return false;
//     }

//     // Convert the comma-separated string to an array
//     $allowed_roles_array = explode(',', $allowed_roles);

//     // Trim whitespace from each role
//     $allowed_roles_array = array_map('trim', $allowed_roles_array);

//     // Get the current user's roles
//     $current_user = wp_get_current_user();

//     // Check if any of the user's roles are in the allowed roles
//     foreach ($current_user->roles as $role) {
//         if (in_array($role, $allowed_roles_array)) {
//             return true; // User has a role that is allowed
//         }
//     }

//     return false; // User does not have any role that is allowed
// }

