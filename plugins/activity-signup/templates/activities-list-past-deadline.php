<?php 

$activity_types = get_terms(array(
    'taxonomy' => 'activity_type',
    'hide_empty' => true,
));

foreach ($activity_types as $type) {
    $args = array(
        'post_type' => 'as-activity',
        'posts_per_page' => 1,
        'orderby' => 'date', // Order by post date
        'order' => 'DESC', // Order by DESC to get the latest post
        'tax_query' => array(
            array(
                'taxonomy' => 'activity_type',
                'field' => 'term_id',
                'terms' => $type->term_id,
            ),
        ),
    );

    $latest_activity = new WP_Query($args);

    if ($latest_activity->have_posts()) {
        while ($latest_activity->have_posts()) {
            $latest_activity->the_post();
            $post_ID = get_the_ID();
            $signup_deadline_datetime = get_post_meta($post_ID, 'signup_deadline', true);
            $activity_start_datetime = get_post_meta($post_ID, '_start_time', true);
            try {
                $signup_deadline_datetime = new DateTime($signup_deadline_datetime);
                $formatted_signup_deadline = $signup_deadline_datetime->format('d-m-Y'); // Converts date to 'dd-mm-yyyy' format
            } catch (Exception $e) {
                $formatted_signup_deadline = 'ERROR';
            }

            $activity_start_datetime = new DateTime($activity_start_datetime);
            
            $current_date = new DateTime(); // Current date and time
    
            if ($formatted_signup_deadline && $activity_start_datetime >= $current_date && $signup_deadline_datetime <= $current_date) { // Compare DateTime objects
                // Output your markup
                echo '<div class="activity-list-item" style="color: grey"><a href="' . get_permalink() . '">';
                echo '<h2 class="activity-list-title">' . get_the_title() . '<span class="activity-list-due-date"> signup deadline: ' . esc_html($formatted_signup_deadline) . '</span></h2>';
                echo '</a></div>';
            } else {
                // Don't print this activity since it has happened already.
            }
        }
    }

    // Reset post data
    wp_reset_postdata();
}

?>