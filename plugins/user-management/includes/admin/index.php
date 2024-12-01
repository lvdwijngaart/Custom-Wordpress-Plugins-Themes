<?php

// Determine the active tab
$active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'team_management';

?>
<div class="wrap">
    <h1>User Management</h1>

    <!-- Tabs -->
    <h2 class="nav-tab-wrapper">
        <a href="?page=user-management&tab=team_management" class="nav-tab <?php echo $active_tab === 'team_management' ? 'nav-tab-active' : ''; ?>">
            Team Management
        </a>
        <a href="?page=user-management&tab=committee_management" class="nav-tab <?php echo $active_tab === 'committee_management' ? 'nav-tab-active' : ''; ?>">
            Committee Management
        </a>
    </h2>

    <!-- Tab Content -->
    <div class="tab-content">
        <?php
        // Include the appropriate file based on the active tab
        switch ( $active_tab ) {
            case 'team_management':
                require_once __DIR__ . '\team-management.php';
                render_team_management_tab();
                break;

            case 'committee_management':
                require_once __DIR__ . '\committee-management.php';
                render_committee_management_tab();
                break;

            default:
                echo '<p>Invalid tab selected.</p>';
                break;
        }
        ?>
    </div>
</div>