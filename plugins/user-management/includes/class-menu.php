<?php

class User_Management_Admin_Menu {

    public function __construct() {
        add_action('admin_menu', array($this, 'register_admin_menu'));
    }

    /**
     * Register main plugin menu and submenus
     */
    public function register_admin_menu() {
        // Main menu page
        add_menu_page('User Management',           // Page title             
            'User Management',                     // Menu title
            'edit_posts',                          // Capability
            'user-management',                      // Menu slug
            array($this, 'main_page_callback'),          // Function that has to be triggered to show the menu item's page 
            plugins_url('/images/empy-plugin-icon-16.png', __FILE__)    // Icon image path
        );


        // Submenu: Structure Management
        add_submenu_page(
            'user-management',
            'Builder Page',
            'Builder Page',
            'manage_options',
            'builder-page',
            array($this, 'builder_page_callback')
        );
    }

    /**
     * Callback for the main menu page
     */
    public function main_page_callback() {
        require_once __DIR__ . '/admin/index.php';
    }

    /**
     * Callback for the Structure Management submenu
     */
    public function builder_page_callback() {
        require_once __DIR__ . '/admin/builder-page.php';

        render_builder_page();
    }
}

