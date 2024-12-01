<?php

/**
 * Fired during plugin deactivation
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Activity_Signup
 * @subpackage Activity_Signup/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Activity_Signup
 * @subpackage Activity_Signup/includes
 * @author     Your Name <email@example.com>
 */
class Activity_Signup_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
		
		// Get the 'ardennen commissie' role object
		$role = get_role('ardennencie');

		// Remove capabilities from this role object
		
		$role->remove_cap('read');
		$role->remove_cap('edit_activity');
		$role->remove_cap('read_activity');
		$role->remove_cap('delete_activity');
		$role->remove_cap('edit_activities');
		$role->remove_cap('edit_others_activities');
		$role->remove_cap('publish_activities');
		$role->remove_cap('read_private_activities');

		$role = get_role('administrator'); // Change to the role you want to assign the capability to
		$role->remove_cap('manage_activity_types');

		$role = get_role('um_bestuur'); // Change to the role you want to assign the capability to
		$role->remove_cap('manage_activity_types');
	}

}
