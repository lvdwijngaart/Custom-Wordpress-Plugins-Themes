<?php

// Ensure this file is accessed via WordPress admin
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo '<div class="error"><p>Invalid activity type ID.</p></div>';
    return;
}

global $wpdb;
$table_name = $wpdb->prefix . 'as_activity_types';

// Fetch the activity type data
$id = intval($_GET['id']);
$activity_type = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $id));

if (!$activity_type) {
    echo '<div class="error"><p>Activity type not found.</p></div>';
    return;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize_text_field($_POST['name']);
    $slug = sanitize_title($_POST['slug']);
    $description = sanitize_textarea_field($_POST['description']);
    $allowed_roles = sanitize_text_field($_POST['allowed_roles']);
    $color = sanitize_hex_color($_POST['color']);

    $updated = $wpdb->update(
        $table_name,
        array(
            'name' => $name,
            'slug' => $slug,
            'description' => $description,
            'allowed_roles' => $allowed_roles, 
            'color' => $color,
        ),
        array('id' => $id),
        array('%s', '%s', '%s', '%s'),
        array('%d')
    );

    if ($updated !== false) {
        echo '<div class="updated"><p>Activity Type updated successfully</p></div>';
    } else {
        echo '<div class="error"><p>Failed to update activity type. Please try again.</p></div>';
    }
}

// Render the edit form
?>
<div class="wrap">
    <h1>Edit Activity Type</h1>
    <form method="post">
        <table class="form-table">
            <tr>
                <th scope="row"><label for="name">Name</label></th>
                <td><input name="name" id="name" type="text" value="<?php echo esc_attr($activity_type->name); ?>" class="regular-text" required></td>
            </tr>
            <tr>
                <th scope="row"><label for="slug">Slug</label></th>
                <td><input name="slug" id="slug" type="text" value="<?php echo esc_attr($activity_type->slug); ?>" class="regular-text" required></td>
            </tr>
            <tr>
                <th scope="row"><label for="description">Description</label></th>
                <td><textarea name="description" id="description" class="regular-text"><?php echo esc_textarea($activity_type->description); ?></textarea></td>
            </tr>
            <tr>
                <th scope="row"><label for="allowed-roles">Allowed Roles</label></th>
                <td><textarea name="allowed_roles" id="allowed-roles" class="regular-text"><?php echo esc_textarea($activity_type->allowed_roles); ?></textarea></td>
            </tr>
            <tr>
                <th scope="row"><label for="color">Color</label></th>
                <td><input name="color" id="color" type="color" value="<?php echo esc_attr($activity_type->color); ?>" required></td>
            </tr>
        </table>
        <?php submit_button('Save Changes'); ?>
    </form>
</div>
<?php