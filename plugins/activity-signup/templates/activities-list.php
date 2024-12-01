<?php 

global $wpdb;

// Fetch activity types from the custom `wp_as_activity_types` table
$activity_types = $wpdb->get_results("
    SELECT id, name 
    FROM {$wpdb->prefix}as_activity_types
");

foreach ($activity_types as $type) {
    // Query for the latest activity post associated with this activity type
    $args = array(
        'post_type' => 'as-activity',
        'posts_per_page' => 1,
        'orderby' => 'date', // Order by post date
        'order' => 'DESC', // Order by DESC to get the latest post
        'meta_query' => array(
            array(
                'key' => '_activity_types', // Custom meta field storing the associated activity type IDs
                'value' => $type->id,
                'compare' => 'LIKE', // Match the activity type ID
            ),
        ),
    );

    $latest_activity = new WP_Query($args);

    if ($latest_activity->have_posts()) {
        while ($latest_activity->have_posts()) {
            $latest_activity->the_post();
            $post_ID = get_the_ID();

            // Get the signup deadline from post meta
            $datetime_string = get_post_meta($post_ID, 'signup_deadline', true);

            try {
                $date = new DateTime($datetime_string);
                $formatted_date = $date->format('d-m-Y'); // Converts date to 'dd-mm-yyyy' format
            } catch (Exception $e) {
                $formatted_date = '';
            }
            
            $current_date = new DateTime(); // Current date and time

            if ($formatted_date && $date >= $current_date) { // Compare DateTime objects
                // Output your markup
                echo '<div class="activity-list-item"><a href="' . get_permalink() . '">';
                echo '<h2 class="activity-list-title">' . get_the_title() . '<span class="activity-list-due-date"> signup deadline: ' . esc_html($formatted_date) . '</span></h2>';
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


<?php 

// $activity_types = get_terms(array(
//     'taxonomy' => 'activity_type',
//     'hide_empty' => true,
// ));

// foreach ($activity_types as $type) {
//     $args = array(
//         'post_type' => 'as-activity',
//         'posts_per_page' => 1,
//         'orderby' => 'date', // Order by post date
//         'order' => 'DESC', // Order by DESC to get the latest post
//         'tax_query' => array(
//             array(
//                 'taxonomy' => 'activity_type',
//                 'field' => 'term_id',
//                 'terms' => $type->term_id,
//             ),
//         ),
//     );

//     $latest_activity = new WP_Query($args);

//     if ($latest_activity->have_posts()) {
//         while ($latest_activity->have_posts()) {
//             $latest_activity->the_post();
//             $post_ID = get_the_ID();
//             $datetime_string = get_post_meta($post_ID, 'signup_deadline', true);
//             try {
//                 $date = new DateTime($datetime_string);
//                 $formatted_date = $date->format('d-m-Y'); // Converts date to 'dd-mm-yyyy' format
//             } catch (Exception $e) {
//                 $formatted_date = '';
//             }
            
//             $current_date = new DateTime(); // Current date and time
    
//             if ($formatted_date && $date >= $current_date) { // Compare DateTime objects
//                 // Output your markup
//                 echo '<div class="activity-list-item"><a href="' . get_permalink() . '">';
//                 echo '<h2 class="activity-list-title">' . get_the_title() . '<span class="activity-list-due-date"> signup deadline: ' . esc_html($formatted_date) . '</span></h2>';
//                 echo '</a></div>';
//             } else {
//                 // Don't print this activity since it has happened already.
//             }
//         }
//     }

//     // Reset post data
//     wp_reset_postdata();
// }

?> 