<?php

/**
 * Fired during plugin activation
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Activity_Signup
 * @subpackage Activity_Signup/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Activity_Signup
 * @subpackage Activity_Signup/includes
 * @author     Your Name <email@example.com>
 */
class Activity_Signup_Activator { 

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		
		// Get the 'ardennen' role object
		$role = get_role('ardennencie');

		// Add capabilities to this role object
		
		$role->add_cap('read');
		$role->add_cap('edit_activity');
		$role->add_cap('read_activity');
		$role->add_cap('delete_activity');
		$role->add_cap('edit_activities');
		$role->add_cap('edit_others_activities');
		$role->add_cap('publish_activities');
		$role->add_cap('read_private_activities');

		$role = get_role('administrator'); // Change to the role you want to assign the capability to
		$role->add_cap('manage_activity_types');

		$role = get_role('um_bestuur'); // Change to the role you want to assign the capability to
		$role->add_cap('manage_activity_types');
	}

}
