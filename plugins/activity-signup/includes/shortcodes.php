<?php

function activity_list_shortcode() {
    $args = array(
        'post_type' => 'as-activity',
        // ... other arguments like 'posts_per_page' or 'orderby'
    );
    $activities = new WP_Query($args);

    $template_file = plugin_dir_path(__FILE__) . '../templates/activities-list.php';
    if (file_exists($template_file)) {
        ob_start();
        // Include template file from your plugin's templates directory
        include $template_file;
        return ob_get_clean();
    } else {
        echo 'Template file not found.';
    }
}
add_shortcode('activity_list', 'activity_list_shortcode');

function activity_list_past_deadline_shortcode() {
    $args = array(
        'post_type' => 'as-activity',
        // ... other arguments like 'posts_per_page' or 'orderby'
    );
    $activities = new WP_Query($args);

    $template_file = plugin_dir_path(__FILE__) . '../templates/activities-list-past-deadline.php';
    if (file_exists($template_file)) {
        ob_start();
        // Include template file from your plugin's templates directory
        include $template_file;
        return ob_get_clean();
    } else {
        echo 'Template file not found.';
    }
}
add_shortcode('activity_list_past_deadline', 'activity_list_past_deadline_shortcode');

?>