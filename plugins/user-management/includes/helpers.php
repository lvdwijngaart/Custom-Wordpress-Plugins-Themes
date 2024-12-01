<?php


/**
 * Utility function to get the year by its ID.
 *
 * @param int $year_id The year ID.
 * @return string|null The year name or null if not found.
 */
function get_year_by_id( $year_id ) {
    global $wpdb;
    $years_table = $wpdb->prefix . 'user_management_years';
    return $wpdb->get_var( $wpdb->prepare( "SELECT year FROM $years_table WHERE id = %d", $year_id ) );
}

/**
 * Utility function to get the committee by its ID.
 *
 * @param int $committee_id The committee's ID.
 * @return string|null The committee name or null if not found.
 */
function get_committee_by_id( $committee_id ) {
    global $wpdb;
    $committee_table = $wpdb->prefix . 'committees';
    return $wpdb->get_var( $wpdb->prepare( "SELECT committee_name FROM $committee_table WHERE id = %d", $committee_id ) );
}

function get_activity_type_by_id( $as_activity_type_id ) {
    global $wpdb;
    $activity_types_table = $wpdb->prefix . 'as_activity_types';
    return $wpdb->get_var( $wpdb->prepare( "SELECT as_type_name FROM $activity_types_table WHERE id = %d", $as_activity_type_id ) );
}


?>