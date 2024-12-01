<?php

function activity_register_meta_boxes() {
    // Array of meta boxes with id, title, callback, context, and priority
    $meta_boxes = [
        ['activity_description_meta', 'Activity Description', 'activity_description_callback', 'normal', 'high'],
        ['activity_date', 'Date and time', 'activity_date_callback', 'side', 'high'],
        ['signup_deadline', 'Sign-up Deadline', 'signup_deadline_callback', 'side', 'high'],
        ['signed_up_users', 'Signed Up Users', 'signed_up_users_callback', 'normal', 'high'],
        [
            'activity_types_meta_box',          // Unique ID for the meta box
            'Activity Types',                 // Title of the meta box
            'activity_types_meta_box_html',    // Callback function that will render the meta box's HTML
            'side',                          // Context where the box will appear ('normal', 'side', 'advanced')
            'default'                        // Priority within the context where the box should show ('high', 'low', 'default')
        ],
        // Add more meta boxes here if needed
    ];

    // Register all meta boxes
    foreach ($meta_boxes as $box) {
        add_meta_box($box[0], $box[1], $box[2], 'as-activity', $box[3], $box[4]);
    }
}
add_action('add_meta_boxes', 'activity_register_meta_boxes');

function activity_types_meta_box_html($post) {
    global $wpdb;

    // Get the current user and their roles
    $current_user = wp_get_current_user();
    $user_roles = $current_user->roles;

    // Retrieve all activity types from the database
    $all_activity_types = $wpdb->get_results("SELECT id, name, allowed_roles FROM {$wpdb->prefix}as_activity_types");

    // Array to hold allowed activity types
    $allowed_activity_types = array();

    // Check which activity types the user is allowed to see
    foreach ($all_activity_types as $activity_type) {
        // Parse the allowed roles (comma-separated) for each activity type
        $allowed_roles = array_map('trim', explode(',', $activity_type->allowed_roles));

        // Check if the user's roles intersect with the allowed roles
        if (in_array('administrator', $user_roles) || in_array('um_bestuur', $user_roles) || array_intersect($user_roles, $allowed_roles)) {
            $allowed_activity_types[] = $activity_type;
        }
    }

    // Retrieve the currently selected activity type IDs for the post
    $current_activity_types = get_post_meta($post->ID, '_activity_types', true);
    $current_activity_types = !empty($current_activity_types) ? explode(',', $current_activity_types) : array();

    // Nonce field for security
    wp_nonce_field('activity_types_meta_box', 'activity_types_meta_box_nonce');

    // Output checkboxes for each allowed activity type
    foreach ($allowed_activity_types as $type) {
        $is_checked = in_array($type->id, $current_activity_types) ? ' checked="checked"' : '';

        echo '<div>';
        echo '<input type="checkbox" id="activity_type_' . esc_attr($type->id) . '" name="activity_types[]" value="' . esc_attr($type->id) . '"' . $is_checked . '>';
        echo '<label for="activity_type_' . esc_attr($type->id) . '">' . esc_html($type->name) . '</label>';
        echo '</div>';
    }
}

/**
 * 
 */
function activity_description_callback($post) {
    // Retrieve the current value of the 'activity_description' meta key
    $description = get_post_meta($post->ID, 'activity_description', true);

    // Use a nonce for verification to ensure the form submission came from the current site
    wp_nonce_field('activity_description_save', 'activity_description_nonce');

    // Settings for wp_editor
    $settings = array(
        'textarea_name' => 'activity_description',
        'editor_height' => 200, // height in pixels
        'media_buttons' => true, // true to include media insert/upload buttons
        'tinymce' => true, // Use TinyMCE for rich text editing
        'quicktags' => true // Use quicktags for HTML tags
    );
    echo "<div>";
    // Create the WYSIWYG editor
    wp_editor($description, 'activity_description', $settings);
    echo "</div>";
}


/**
 * 
 */
function activity_date_callback($post) {
     // Add a nonce field so we can check for it later.
     wp_nonce_field('activity_date_meta_box', 'activity_date_meta_box_nonce');

     $start_time = get_post_meta($post->ID, '_start_time', true);
     $end_time = get_post_meta($post->ID, '_end_time', true);
 
     // Use the current time as placeholders if the fields are empty
     $start_placeholder = !empty($start_time) ? $start_time : 'YYYY-MM-DD HH:MM';
     $end_placeholder = !empty($end_time) ? $end_time : 'YYYY-MM-DD HH:MM';
 
     echo '<label for="start_time" class="meta-box-input">' . __('Start Time', 'textdomain') . '</label>';
     echo '<input type="datetime-local" id="start_time" name="start_time" value="' . esc_attr($start_placeholder) . '" style="float:right;" /><br/><br/>';
     
     echo '<label for="end_time">' . __('End Time', 'textdomain') . '</label>';
     echo '<input type="datetime-local" id="end_time" name="end_time" value="' . esc_attr($end_placeholder) . '" style="float:right;" />';
}


/**
 * 
 */
function signup_deadline_callback($post) {
    $deadline = get_post_meta($post->ID, 'signup_deadline', true);
    echo '<input type="datetime-local" name="signup_deadline" value="' . esc_attr($deadline) . '">';
}

/**
 * 
 */
function signed_up_users_callback($post) {
    $participants = get_post_meta($post->ID, 'participants', true) ?: [];

    if (!empty($participants) && is_array($participants)) {
        echo '<ul>';
        foreach ($participants as $user_id) {
            $user_info = get_userdata($user_id);
            if ($user_info) {
                echo '<li>' . esc_html($user_info->display_name) . '</li>';
            }
        }
        echo '</ul>';
    } else {
        echo 'No participants yet.';
    }
}

/**
 * 
 */
function activity_save_meta_boxes_data($post_id) {
    // Check autosave, nonce, permissions...
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE || !current_user_can('edit_post', $post_id)) {
        return;
    }

     // Check if our nonce is set.
     if (!isset($_POST['activity_description_nonce'])) {
        return;
    }

    // Sanitize and update the content for rich text
    if (isset($_POST['activity_description'])) {
        $description = wp_kses_post($_POST['activity_description']);
        update_post_meta($post_id, 'activity_description', $description);
    }

    // Make sure that it is set.
    if (!isset($_POST['start_time']) || !isset($_POST['end_time'])) {
        return;
    }

    // Sanitize user input.
    $start_time = sanitize_text_field($_POST['start_time']);
    $end_time = sanitize_text_field($_POST['end_time']);

    // Update the meta field in the database.
    update_post_meta($post_id, '_start_time', $start_time);
    update_post_meta($post_id, '_end_time', $end_time);

    if (isset($_POST['signup_deadline'])) {
        update_post_meta($post_id, 'signup_deadline', $_POST['signup_deadline']);
    }

    // Handle term associations for activity types if needed...
}
add_action('save_post', 'activity_save_meta_boxes_data');

/**
 * 
 */
function save_activity_types_meta_box_data($post_id) {
    // Verify nonce
    if (!isset($_POST['activity_types_meta_box_nonce']) ||
        !wp_verify_nonce($_POST['activity_types_meta_box_nonce'], 'activity_types_meta_box')
    ) {
        return;
    }

    // Prevent saving during autosave or if the user lacks permissions
    if ((defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || !current_user_can('edit_post', $post_id)) {
        return;
    }

    // Check if activity types were submitted
    if (isset($_POST['activity_types'])) {
        // Sanitize and save the selected activity type IDs
        $selected_activity_types = array_map('intval', $_POST['activity_types']);
        update_post_meta($post_id, '_activity_types', implode(',', $selected_activity_types));
    } else {
        // If no activity types were selected, delete the meta key
        delete_post_meta($post_id, '_activity_types');
    }
}
add_action('save_post', 'save_activity_types_meta_box_data');

/**
 * 
 */
function remove_default_activity_types_meta_box() {
    remove_meta_box('activity_typediv', 'as-activity', 'side');
}
add_action('add_meta_boxes', 'remove_default_activity_types_meta_box', 11);


// Remove the default activity types meta box
function remove_theme_design_settings() {
    remove_meta_box('lightning_design_setting', 'as-activity', 'side');
}
add_action('add_meta_boxes', 'remove_theme_design_settings', 11);

// Rest of the callbacks and utility functions...


?>