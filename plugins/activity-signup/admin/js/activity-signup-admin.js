(function( $ ) {
	'use strict';

	/**
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */

})( jQuery );

jQuery(document).ready(function ($) {
	$('#post').submit(function(e) {
		// Check if an activity type is selected
		if ($('input[name="activity_types[]"]:checked').length === 0) {
			e.preventDefault(); // Prevent the form from submitting
			// Add the admin notice (you might need to adjust the selector based on your actual markup)
			$('.wrap').prepend('<div class="notice notice-error"><p>Please select an activity type before publishing.</p></div>');
			// Scroll to the top of the page to ensure the user sees the message
			window.scrollTo(0, 0);
		}
	});

    // Show Quick Edit Row
    $('.quick-edit-trigger').on('click', function (e) {
        e.preventDefault();

        const id = $(this).data('id');
        $('.quick-edit-row').hide(); // Hide all other quick edit rows
		$(`#activity-type-row-${id}`).hide(); // hide the clicked row
        $(`#quick-edit-row-${id}`).show(); // Show the clicked row
    });

    // Cancel Quick Edit
    $('.quick-edit-cancel').on('click', function (e) {
        e.preventDefault();

        const id = $(this).data('id');
        $(`#quick-edit-row-${id}`).hide(); // Hide the quick edit row
		$(`#activity-type-row-${id}`).show(); // hide the clicked row
    });

    // Save Quick Edit Changes
    $('.quick-edit-save').on('click', function (e) {
        e.preventDefault();

        const id = $(this).data('id');
        const name = $(`#quick-edit-name-${id}`).val();
        const slug = $(`#quick-edit-slug-${id}`).val();

        $.ajax({
            url: ajaxurl, // WordPress AJAX URL
            type: 'POST',
            data: {
                action: 'quick_edit_activity_type',
                id: id,
                name: name,
                slug: slug,
                nonce: quickEditAjax.nonce // Pass the nonce for security
            },
            success: function (response) {
                if (response.success) {
                    // Update the row with new data
                    const row = $(`tr[data-id="${id}"]`);
                    row.find('td:nth-child(1)').text(name);
                    row.find('td:nth-child(3)').text(slug);

                    // Hide the quick edit row
                    $(`#quick-edit-row-${id}`).hide();
                } else {
                    alert('Failed to update the activity type. Please try again.');
                }
            },
            error: function () {
                alert('An error occurred. Please try again.');
            }
        });
    });
});
