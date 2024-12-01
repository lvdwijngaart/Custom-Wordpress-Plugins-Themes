<?php 
// class-activity-type.php

class Activity_Type {
    /**
     * Constructor to add actions and filters.
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'register_activity_types_page'));
        add_action('admin_menu', array($this, 'register_edit_activity_type_page'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_styles_and_scripts'));
        // ... other hooks you might need ...
    }
    
    /**
     * Registers the Activity Types subpage
     */
    function register_activity_types_page() {
        add_submenu_page(
            'edit.php?post_type=as-activity', // Parent menu (Activity post type)
            'Activity Types',                // Page title
            'Activity Types',                // Menu title
            'manage_options',                // Capability
            'activity-types',                // Menu slug
            array($this, 'render_activity_types_page')  // Callback to render the page
        );
    }    

    /**
     * 
     */
    public function register_edit_activity_type_page() {
        add_submenu_page(
            null, // Hides this page from the admin menu
            'Edit Activity Type', // Page title
            'Edit Activity Type', // Menu title (not visible because the page is hidden)
            'manage_options', // Capability
            'edit-activity-type', // Menu slug
            function () {
                include plugin_dir_path(__FILE__) . '../admin/views/edit-activity-type.php';
            }
        );
    }
    

    /**
     * 
     */
    public function enqueue_styles_and_scripts($hook_suffix) {
        if ($hook_suffix === 'activity_page_activity-types') {
            wp_enqueue_style(
                'activity-types-admin-style',
                plugin_dir_url(__FILE__) . '../admin/css/activity-signup-admin.css',
                array(),
                '1.0.0'
            );

            wp_enqueue_script(
                'activity-types-admin-script',
                plugin_dir_url(__FILE__) . '../admin/js/activity-signup-admin.js',
                array('jquery'),
                '1.0.0',
                true
            );
        }
    }

    public function render_activity_types_page() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'as_activity_types';


        // Handle add activity type form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_activity_type'])) {
            // Verify the nonce
            if (!isset($_POST['activity_type_nonce']) || !wp_verify_nonce($_POST['activity_type_nonce'], 'add_activity_type_nonce')) {
                wp_die('Invalid nonce.');
            }

            // Sanitize input
            $name = sanitize_text_field($_POST['name']);
            $slug = sanitize_title($_POST['slug']);
            $description = sanitize_textarea_field($_POST['description']);
            $allowed_roles = sanitize_text_field($_POST['allowed_roles']);
            $color = sanitize_hex_color($_POST['color']);

            // Check if slug already exists
            $existing = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE slug = %s", $slug));
            if ($existing > 0) {
                echo '<div class="error"><p>A type with this slug already exists. Please choose another slug.</p></div>';
            } else {
                // Insert the new activity type into the database
                $data = [
                    'name' => $name,
                    'slug' => $slug,
                    'description' => $description,
                    'allowed_roles' => $allowed_roles,
                    'color' => $color,
                ];
                if ($this->insert_activity_type( $wpdb, $table_name, $data )) {
                    echo '<div class="updated"><p>Activity type added successfully!</p></div>';
                } else {
                    echo '<div class="updated"><p>Adding activity type failed!</p></div>';
                }
               

                
            }
        }

        // Get the existing activity types
        $activity_types = $wpdb->get_results("SELECT * FROM $table_name");

        // Include the admin page template
        include plugin_dir_path(__FILE__) . '../admin/views/activity-types-view.php';
    }

    /**
     * Handle database insertion and check for success or failure.
     *
     * @param object $wpdb The WordPress database global object.
     * @param string $table_name The name of the table.
     * @param array $data The data to insert into the table.
     * @return bool True if the insert was successful, false otherwise.
     */
    function insert_activity_type($wpdb, $table_name, $data) {
        $result = $wpdb->insert($table_name, $data);

        if ($result === false) {
            // Log error to debug
            error_log('Database insert failed: ' . $wpdb->last_error);
            return false;
        }

        return true;
    }

    /**
     * 
     */
    function add_activity_type() {

    }

    /**
     * 
     */
    function edit_activity_type() {

    }
 
    /**
     * 
     */
    function delete_activity_type() {
        
    }

    /**
     * Called on plugin activation to set up initial settings.
     */
    public function activate() {
        // TODO
        // $this->register_post_type();
        // flush_rewrite_rules();
    }

    /**
     * Called on plugin deactivation to clean up settings.
     */
    public function deactivate() {
        // TODO
        // unregister_post_type('as-activity');
        // flush_rewrite_rules();
    }
}
?>