<?php 
// class-activity-post-type.php

class Activity_Post_Type {
    /**
     * Constructor to add actions and filters.
     */
    public function __construct() {
        add_action('init', array($this, 'register_post_type'));
        add_action('activity_type_add_form_fields', array($this, 'add_activity_type_custom_field'));
        add_action('created_activity_type', array($this, 'save_activity_type_custom_field'), 10, 2);
        add_action('activity_type_edit_form_fields', array($this, 'edit_activity_type_custom_field'), 10, 2);
        add_action('edited_activity_type', array($this, 'save_activity_type_custom_field'), 10, 2);
        // ... other hooks you might need ...
    }
    

    /**
     * Registers the custom post type.
     */
    function register_post_type() {
        $labels = array(
            'name'                  => _x('Activities', 'Post type general name', 'textdomain'),
            'singular_name'         => _x('Activity', 'Post type singular name', 'textdomain'),
            'menu_name'             => _x('Activities', 'Admin Menu text', 'textdomain'),
            'name_admin_bar'        => _x('Activity', 'Add New on Toolbar', 'textdomain'),
            'add_new'               => __('Add New', 'textdomain'),
            'add_new_item'          => __('Add New Activity', 'textdomain'),
            'new_item'              => __('New Activity', 'textdomain'),
            'edit_item'             => __('Edit Activity', 'textdomain'),
            'view_item'             => __('View Activity', 'textdomain'),
            'all_items'             => __('All Activities', 'textdomain'),
            'search_items'          => __('Search Activities', 'textdomain'),
            'parent_item_colon'     => __('Parent Activities:', 'textdomain'),
            'not_found'             => __('No activities found.', 'textdomain'),
            'not_found_in_trash'    => __('No activities found in Trash.', 'textdomain'),
            'featured_image'        => _x('Activity Cover Image', 'Overrides the “Featured Image” phrase', 'textdomain'),
            'set_featured_image'    => _x('Set cover image', 'Overrides the “Set featured image” phrase', 'textdomain'),
            'remove_featured_image' => _x('Remove cover image', 'Overrides the “Remove featured image” phrase', 'textdomain'),
            'use_featured_image'    => _x('Use as cover image', 'Overrides the “Use as featured image” phrase', 'textdomain'),
            'archives'              => _x('Activity archives', 'The post type archive label used in nav menus', 'textdomain'),
            'insert_into_item'      => _x('Insert into activity', 'Overrides the “Insert into post”/“Insert into page” phrase', 'textdomain'),
            'uploaded_to_this_item' => _x('Uploaded to this activity', 'Overrides the “Uploaded to this post”/“Uploaded to this page” phrase', 'textdomain'),
            'filter_items_list'     => _x('Filter activities list', 'Screen reader text for the filter links', 'textdomain'),
            'items_list_navigation' => _x('Activities list navigation', 'Screen reader text for the pagination', 'textdomain'),
            'items_list'            => _x('Activities list', 'Screen reader text for the items list', 'textdomain'),
        );
    
        $args = array(
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => array('slug' => 'as-activity'),
            'capability_type'    => 'activity',
            'capabilities' => array(
                'edit_post'          => 'edit_activity',
                'read_post'          => 'read_activity',
                'delete_post'        => 'delete_activity',
                'edit_posts'         => 'edit_activities',
                'edit_others_posts'  => 'edit_others_activities',
                'publish_posts'      => 'publish_activities',
                'read_private_posts' => 'read_private_activities',
            ),
            'map_meta_cap' => true,  // important for mapping the custom capabilities
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => null,
            'supports'           => array('title', 'thumbnail' ),
            'show_in_rest'       => true,  // Needed for Gutenberg to work correctly
        );
    
        register_post_type('as-activity', $args);
    }

    function add_activity_type_custom_field($taxonomy) {
        ?>
        <div class="form-field">
            <label for="activity_type_roles"><?php _e('Allowed Roles', 'textdomain'); ?></label>
            <input type="text" name="activity_type_roles" id="activity_type_roles" value="">
            <p class="description"><?php _e('Enter roles separated by commas. e.g. role1,role2', 'textdomain'); ?></p>
        </div>
        <?php
    }
    
    function edit_activity_type_custom_field($term, $taxonomy) {
        $allowed_roles = get_term_meta($term->term_id, 'activity_type_roles', true);
        ?>
        <tr class="form-field">
            <th scope="row" valign="top"><label for="activity_type_roles"><?php _e('Allowed Roles', 'textdomain'); ?></label></th>
            <td>
                <input type="text" name="activity_type_roles" id="activity_type_roles" value="<?php echo esc_attr($allowed_roles); ?>">
                <p class="description"><?php _e('Enter roles separated by commas. e.g. role1,role2', 'textdomain'); ?></p>
            </td>
        </tr>
        <?php
    }
    
    function save_activity_type_custom_field($term_id) {
        if (isset($_POST['activity_type_roles'])) {
            update_term_meta($term_id, 'activity_type_roles', sanitize_text_field($_POST['activity_type_roles']));
        }
    }
    
    /**
     * Called on plugin activation to set up initial settings.
     */
    public function activate() {
        $this->register_post_type();
        flush_rewrite_rules();
    }

    /**
     * Called on plugin deactivation to clean up settings.
     */
    public function deactivate() {
        unregister_post_type('as-activity');
        flush_rewrite_rules();
    }
}
?>