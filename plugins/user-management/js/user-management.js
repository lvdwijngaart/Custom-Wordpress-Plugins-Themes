jQuery(document).ready(function ($) {
    // Remove Member
    $(document).on("click", ".remove-member-button", function (e) {
        e.preventDefault();

        const button = $(this);
        const membershipId = button.data("membership-id");
        const committee = button.data("committee");
        const yearId = button.data("year-id");

        if (!confirm("Are you sure you want to remove this member?")) {
            return;
        }

        $.ajax({
            url: userManagementAjax.ajaxUrl,
            type: "POST",
            data: {
                action: "remove_member",
                membership_id: membershipId,
                committee: committee,
                year_id: yearId,
                nonce: userManagementAjax.nonces.remove_member // Use correct nonce
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
        const committee = form.find("input[name='committee']").val();
        const yearId = form.find("input[name='year_id']").val();

        $.ajax({
            url: userManagementAjax.ajaxUrl,
            type: "POST",
            data: {
                action: "add_member",
                user_id: userId,
                committee: committee,
                year_id: yearId,
                nonce: userManagementAjax.nonces.add_member // Use correct nonce
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
                                    data-committee="${response.data.member.committee}"
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
                                <h3>Members of ${response.data.member.committee} (${response.data.member.year})</h3>
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
});
