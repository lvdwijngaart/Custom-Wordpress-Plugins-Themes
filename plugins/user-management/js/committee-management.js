jQuery(document).ready(function ($) {
    // Remove Member
    $(document).on("click", ".remove-member-button", function (e) {
        e.preventDefault();

        const button = $(this);
        const membershipId = button.data("membership-id");
        const committeeId = button.data("committee-id");
        const yearId = button.data("year-id");

        if (!confirm("Are you sure you want to remove this member?")) {
            return;
        }

        $.ajax({
            url: committeeManagementAjax.ajaxUrl,
            type: "POST",
            data: {
                action: "remove_member",
                membership_id: membershipId,
                committee_id: committeeId,
                year_id: yearId,
                nonce: committeeManagementAjax.nonces.remove_member // Use correct nonce
            },
            success: function (response) {
                if (response.success) {
                    alert(response.data.message);

                    const row = button.closest("tr");
                    row.remove();

                    // Check if the table has no more rows
                    if ($(".committee-members-table tbody tr").length === 0) {
                        $(".committee-members-table tbody").html('<tr class="no-members-row"><td colspan="2">No members found for this committee and year.</td></tr>');
                    }
                } else {
                    alert(response.data.message);
                }
            },
            error: function () {
                alert("An error occurred. Please try again.");
            }
        });
    });

    // Add Member
    $("#add-member-form").on("submit", function (e) {
        e.preventDefault();

        const form = $(this);
        const userId = form.find("select[name='user_id']").val();
        const committeeId = form.find("input[name='committee_id']").val();
        const yearId = form.find("input[name='year_id']").val();

        $.ajax({
            url: committeeManagementAjax.ajaxUrl,
            type: "POST",
            data: {
                action: "add_member",
                user_id: userId,
                committee_id: committeeId,
                year_id: yearId,
                nonce: committeeManagementAjax.nonces.add_member // Use correct nonce
            },
            success: function (response) {
                if (response.success) {
                    alert(response.data.message);

                    const newRow = `
                        <tr>
                            <td>${response.data.member.name}</td>
                            <td>
                                <button
                                    type="button"
                                    class="button button-secondary remove-member-button"
                                    data-membership-id="${response.data.member.id}"
                                    data-committee-id="${response.data.member.committee_id}"
                                    data-year-id="${response.data.member.year_id}">
                                    Remove
                                </button>
                            </td>
                        </tr>
                    `;

                    // Check if table exists
                    if ($(".committee-members-table").length === 0) {
                        const tableHtml = `
                            <div class="committee-search-result">
                                <h3>Members of ${response.data.member.committee_name} (${response.data.member.year})</h3>
                                <table class="widefat fixed striped committee-members-table">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${newRow}
                                    </tbody>
                                </table>
                            </div>
                        `;

                        $(".committee-container").append(tableHtml);
                    } else {
                        // Remove "No members" row if it exists
                        $(".committee-members-table tbody .no-members-row").remove();

                        // Append the new member row
                        $(".committee-members-table tbody").append(newRow);
                    }
                } else {
                    alert(response.data.message);
                }
            },
            error: function () {
                alert("An error occurred. Please try again.");
            }
        });
    });
    
    // Event delegation for dynamically added buttons
    $(document).on("click", ".edit-committee-button", function () {
        const button = $(this);
        const row = button.closest("tr");

        // Get current values from the row
        const committeeId = button.data("id");
        const committeeName = button.data("committee-name");
        const roleSlugField = button.data("role-slug");
        const asActivityTypeId = button.data("as-activity-type-id");
        const asActivityTypeName = button.data("as-activity-type-name");
        const description = button.data("description");

        // Replace row content with form inputs
        const originalRowContent = row.html();
        row.html(`
            <td>
                <input type="text" class="committee-name-input" value="${committeeName}">
            </td>
            <td>
                <select class="role-slug-input">
                    ${Object.entries(committeeManagementAjax.roles).map(([roleSlug, roleName]) => {
                        const selected = roleSlugField === roleSlug ? "selected" : "";
                        return `<option value="${roleSlug}" ${selected}>${roleName}</option>`;
                    }).join("")}
                </select>
            </td>
            <td>
                <select class="as-activity-type-id-input">
                    ${committeeManagementAjax.activity_types.map((activityType) => {
                        const selected = activityType.name === asActivityTypeName ? "selected" : "";
                        return `<option value="${activityType.id}" ${selected}>${activityType.name}</option>`;
                    }).join("")}
                </select>
            </td>
            <td>
                <textarea class="description-input">${description}</textarea>
            </td>
            <td>
                <button type="button" class="button save-committee-button">Save</button>
                <button type="button" class="button cancel-edit-button">Cancel</button>
            </td>
        `);

        // Save button click handler
        row.find(".save-committee-button").on("click", function () {
            const updatedCommitteeName = row.find(".committee-name-input").val();
            const updatedRoleSlug = row.find(".role-slug-input").val();
            const updatedAsActivityTypeId = row.find(".as-activity-type-id-input").val();
            const updatedAsActivityTypeName = row.find('.as-activity-type-id-input option:selected').text(); // Get selected option text
            const updatedDescription = row.find(".description-input").val();

            // AJAX request to update committee
            $.ajax({
                url: committeeManagementAjax.ajaxUrl,
                type: "POST",
                data: {
                    action: "update_committee",
                    id: committeeId,
                    committee_name: updatedCommitteeName,
                    role_slug: updatedRoleSlug,
                    as_activity_type_id: updatedAsActivityTypeId,
                    description: updatedDescription,
                    nonce: committeeManagementAjax.nonces.update_committee
                },
                success: function (response) {
                    if (response.success) {
                        // Replace inputs with updated values
                        row.html(`
                            <td class="committee-name">${updatedCommitteeName}</td>
                            <td class="role-slug">${updatedRoleSlug}</td>
                            <td class="as-activity-type-id">${updatedAsActivityTypeName}</td>
                            <td class="description">${updatedDescription}</td>
                            <td>
                                <button
                                    type="button"
                                    class="button edit-committee-button"
                                    data-id="${committeeId}"
                                    data-committee-name="${updatedCommitteeName}"
                                    data-role-slug="${updatedRoleSlug}"
                                    data-as-activity-type-id="${updatedAsActivityTypeId}"
                                    data-as-activity-type-name="${updatedAsActivityTypeName}"
                                    data-description="${updatedDescription}">
                                    Edit
                                </button>
                                <form method="post" style="display:inline;">
                                    <input type="hidden" name="id" value="${committeeId}">
                                    <button type="submit" name="delete_committee" class="button button-secondary">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        `);
                    } else {
                        alert(response.data.message || "Failed to update the committee.");
                    }
                },
                error: function () {
                    alert("An error occurred. Please try again.");
                }
            });
        });

        // Cancel button click handler
        row.find(".cancel-edit-button").on("click", function () {
            row.html(originalRowContent);
        });
    });

    // handling adding a committee without reloading the page
    $(document).on("click", ".add-committee-button", function () {

        const committeeName = $('#committee-name').val();
        const roleSlug = $('#role-slug').val();
        const asActivityTypeId = $('#as-activity-type-id').val();
        const asActivityTypeName = $('#as-activity-type-id option:selected').text(); // Get selected option text
        const Description = $('#description').val();
        
        $.ajax({
            url: committeeManagementAjax.ajaxUrl,
            type: "POST",
            data: {
                action: "add_committee",
                committee_name: committeeName,
                role_slug: roleSlug,
                as_activity_type_id: asActivityTypeId,
                as_activity_type_name: asActivityTypeName,
                description: Description,
                nonce: committeeManagementAjax.nonces.add_committee // Use correct nonce
            },
            success: function (response) {
                if (response.success) {
                    // Alert success
                    alert(response.data.message);

                    // Add the new committee to the table
                    const newRow = `
                        <tr>
                            <td class="committee-name">${response.data.committee.committee_name}</td>
                            <td class="role-slug">${response.data.committee.role_slug}</td>
                            <td class="as-activity-type-id">${response.data.committee.as_activity_type_name}</td>    
                            <td class="description">${response.data.committee.description}</td>
                            <td>
                                <button
                                    type="button"
                                    class="button edit-committee-button"
                                    data-id="${response.data.committee.id}"
                                    data-committee-name="${response.data.committee.committee_name}"
                                    data-role-slug="${response.data.committee.role_slug}"
                                    data-as-activity-type-id="${response.data.committee.as_activity_type_id}"
                                    data-as-activity-type-name="${response.data.committee.as_activity_type_name}"
                                    data-description="${response.data.committee.description}">
                                    Edit
                                </button>
                                <form method="post" style="display:inline;">
                                    <input type="hidden" name="id" value="${response.data.committee.id}">
                                    <button type="submit" name="delete_committee" class="button button-secondary">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    `;
                    $(".committee-list-table tbody").append(newRow); // Update the table dynamically

                } else {
                    alert(response.data.message);
                }
            },
            error: function () {
                alert("An error occurred. Please try again.");
            }
        });
    });

});


