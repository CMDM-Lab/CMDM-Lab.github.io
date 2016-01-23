window.HOOTMEDIA = (function(window, document, $, undefined){

	var hootoptions_upload;
	var hootoptions_selector;

	var hootMedia = {};

	hootMedia.add_file = function(event, selector) {

		var upload = $(".uploaded-file"), frame;
		var $el = $(this);
		hootoptions_selector = selector;

		event.preventDefault();

		// If the media frame already exists, reopen it.
		if ( hootoptions_upload ) {
			hootoptions_upload.open();
		} else {
			// Create the media frame.
			hootoptions_upload = wp.media.frames.hootoptions_upload =  wp.media({
				// Set the title of the modal.
				title: $el.data('choose'),

				// Customize the submit button.
				button: {
					// Set the text of the button.
					text: $el.data('update'),
					// Tell the button not to close the modal, since we're
					// going to refresh the page when the image is selected.
					close: false
				}
			});

			// When an image is selected, run a callback.
			hootoptions_upload.on( 'select', function() {
				// Grab the selected attachment.
				var attachment = hootoptions_upload.state().get('selection').first();
				hootoptions_upload.close();
				hootoptions_selector.find('.upload').val(attachment.attributes.url);
				if ( attachment.attributes.type == 'image' ) {
					hootoptions_selector.find('.screenshot').empty().hide().append('<img src="' + attachment.attributes.url + '"><a class="remove-image">Remove</a>').slideDown('fast');
				}
				hootoptions_selector.find('.upload-button').unbind().addClass('remove-file').removeClass('upload-button').val(hootoptions_l10n.remove);
				hootoptions_selector.find('.hoot-of-background-properties').slideDown();
				hootoptions_selector.find('.remove-image, .remove-file').on('click', function() {
					hootMedia.remove_file( $(this).closest('.section') );
				});
			});

		}

		// Finally, open the modal.
		hootoptions_upload.open();
	}

	hootMedia.remove_file = function(selector) {
		selector.find('.remove-image').hide();
		selector.find('.upload').val('');
		selector.find('.hoot-of-background-properties').hide();
		selector.find('.screenshot').slideUp();
		selector.find('.remove-file').unbind().addClass('upload-button').removeClass('remove-file').val(hootoptions_l10n.upload);
		// We don't display the upload button if .upload-notice is present
		// This means the user doesn't have the WordPress 3.5 Media Library Support
		if ( $('.section-upload .upload-notice').length > 0 ) {
			$('.upload-button').remove();
		}
		selector.find('.upload-button').on('click', function(event) {
			hootMedia.add_file(event, $(this).closest('.section'));
		});
	}

	hootMedia.init = function(){
		$('.remove-image, .remove-file').on('click', function() {
			hootMedia.remove_file( $(this).closest('.section') );
		});

		$('.upload-button').click( function( event ) {
			hootMedia.add_file(event, $(this).closest('.section'));
		});
	}

	$(document).ready(hootMedia.init);

	return hootMedia;

})(window, document, jQuery);