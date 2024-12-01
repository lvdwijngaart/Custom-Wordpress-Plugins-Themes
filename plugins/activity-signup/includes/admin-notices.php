<?php

function show_activity_type_error_admin_notice() {
    $screen = get_current_screen();
    if ($screen->id !== 'as-activity') {
        return;
    }

    // Check if the transient is set, and display the error message if it is.
    if (get_transient('activity_type_error_' . get_the_ID())) {
        echo '<div class="notice notice-error is-dismissible"><p>Please select an activity type before publishing.</p></div>';
        // Delete the transient so it doesn't show again
        delete_transient('activity_type_error_' . get_the_ID());
    }
}
add_action('admin_notices', 'show_activity_type_error_admin_notice');

?>