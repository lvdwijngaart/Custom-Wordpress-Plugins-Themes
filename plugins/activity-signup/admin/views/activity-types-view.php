<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <div id="col-container">
        <!-- Left column: Add new category -->
        <div id="col-left">
        <div class="form-wrap">
                <h2>Add New Activity Type</h2>
                <form method="post" action="">
                    <?php wp_nonce_field('add_activity_type_nonce', 'activity_type_nonce'); ?>
                    <div class="form-field">
                        <label for="name">Name</label>
                        <input type="text" name="name" id="name" required>
                        <p>The name as it appears on your site.</p>
                    </div>
                    <div class="form-field">
                        <label for="slug">Slug</label>
                        <input type="text" name="slug" id="slug" required>
                        <p>The “slug” is the URL-friendly version of the name.</p>
                    </div>
                    <div class="form-field">
                        <label for="description">Description</label>
                        <textarea name="description" id="description"></textarea>
                        <p>Optional. Provide a short description of the activity type.</p>
                    </div>
                    <div class="form-field">
                        <label for="allowed_roles">Allowed Roles</label>
                        <input type="text" name="allowed_roles" id="allowed_roles">
                        <p>Enter roles separated by commas, e.g., `administrator,editor`.</p>
                    </div>
                    <div class="form-field">
                        <label for="color">Color</label>
                        <input type="color" name="color" id="color" value="#000000">
                    </div>
                    <p class="submit">
                        <button type="submit" name="add_activity_type" class="button button-primary">Add Activity Type</button>
                    </p>
                </form>
            </div>
        </div>

        <!-- Right column: List categories -->
        <div id="col-right">
            <table class="wp-list-table widefat fixed">
                <thead>
                    <tr class="activity_type_header_row">
                        <th>Name</th>
                        <th>Description</th>
                        <th>Slug</th>
                        <th>Count</th>
                        <th>Color</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($activity_types)): ?>
                        <?php $row_index=0; ?>
                        <?php foreach ($activity_types as $type): ?>
                            <?php $row_class = ($row_index % 2 == 0) ? "row-light" : "row-dark" ; ?>
                            <tr class="activity-type-row <?php echo esc_attr($row_class);?>" id="activity-type-row-<?php echo esc_attr($type->id); ?>" data-id="<?php echo esc_attr($type->id); ?>">
                                <td>
                                    <strong><?php echo esc_html($type->name); ?></strong>
                                    <div class="row-actions">
                                        <span class="edit">
                                            <a href="<?php echo esc_url(admin_url('admin.php?page=edit-activity-type&id=' . $type->id)); ?>">Edit</a>
                                        </span> |
                                        <span class="quick-edit"><a href="#" class="quick-edit-trigger" data-id="<?php echo esc_attr($type->id); ?> ">Quick Edit</a></span> |
                                        <span class="delete"><a href="#" class="delete-link" onclick="return confirm('Are you sure you want to delete this item?');">Delete</a></span> 
                                        <!-- removed for now as I don't deem it necessary -->
                                        <!-- | -->
                                        <!-- <span class="view"><a href="#" target="_blank">View</a></span>  --> 
                                    </div>
                                </td>
                                <td><?php echo esc_html($type->description); ?></td>
                                <td><?php echo esc_html($type->slug); ?></td>
                                <td>0</td> <!-- Replace with dynamic count logic -->
                                <td>
                                    <span style="display:inline-block; width:5px; height:16px; background-color: <?php echo esc_html($type->color); ?>;"></span>
                                </td>
                            </tr>
                            <!-- Quick Edit Row -->
                            <tr class="<?php echo esc_attr($row_class);?>" id="quick-edit-row-<?php echo esc_attr($type->id); ?>" class="quick-edit-row" style="display: none;">
                                <td colspan="5">
                                    <div class="quick-edit-wrapper">
                                        <label for="quick-edit-name-<?php echo esc_attr($type->id); ?>">Name</label>
                                        <input type="text" id="quick-edit-name-<?php echo esc_attr($type->id); ?>" value="<?php echo esc_attr($type->name); ?>">

                                        <label for="quick-edit-slug-<?php echo esc_attr($type->id); ?>">Slug</label>
                                        <input type="text" id="quick-edit-slug-<?php echo esc_attr($type->id); ?>" value="<?php echo esc_attr($type->slug); ?>">

                                        <!-- Buttons -->
                                        <div class="quick-edit-buttons">
                                            <button class="button button-primary quick-edit-save" data-id="<?php echo esc_attr($type->id); ?>">Save</button>
                                            <button class="button quick-edit-cancel" data-id="<?php echo esc_attr($type->id); ?>">Cancel</button>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php $row_index++; endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6">No activity types found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>

            </table>
        </div>
    </div>
</div>
