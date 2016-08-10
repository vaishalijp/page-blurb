jQuery(document).ready(function($) {

	// Uploading files
	var file_frame;
	

	jQuery.fn.page_blurb_upload_listing_image = function( button ) {
		var button_id = button.attr('id');
		
		var field_id = button_id.replace( '_button', '' );
		// If the media frame already exists, reopen it.
		if ( file_frame ) {
		  file_frame.open();
		  return;
		}

		// Create the media frame.
		file_frame = wp.media.frames.file_frame = wp.media({
		title: button.data( 'uploader_title' ),
		button: {
			text: button.data( 'uploader_button_text' ),
		  },
		  multiple: false
		});

		// When an image is selected, run a callback.
		  file_frame.on( 'select', function() {
		  var attachment = file_frame.state().get('selection').first().toJSON();
		  jQuery("#"+field_id).val(attachment.id);
		  jQuery("#page_blurb_image img").attr('srcSet','');
		  jQuery("#page_blurb_image img").attr('src',attachment.url);
		  jQuery("#page_blurb_image img").show();
		  jQuery( '#' + button_id ).attr( 'id', 'page_blurb_remove_image_button' );
		  jQuery( '#page_blurb_remove_image_button' ).text( 'Remove listing image' );
		});

		// Finally, open the modal
		file_frame.open();
	};

	jQuery('#page_blurb_image').on( 'click', '#page_blurb_upload_image_button', function( event ) {
		event.preventDefault();
		jQuery.fn.page_blurb_upload_listing_image( jQuery(this) );
	});

	jQuery('#page_blurb_image').on( 'click', '#page_blurb_remove_image_button', function( event ) {
	
		event.preventDefault();
		jQuery( '#page_blurb_upload_listing_image' ).val( '' );
		jQuery( '#page_blurb_image img' ).attr( 'src', '' );
		jQuery( '#page_blurb_image img' ).attr( 'srcSet', '' );
		jQuery( '#page_blurb_image img' ).hide();
		jQuery( this ).attr( 'id', 'page_blurb_upload_image_button' );
		jQuery( '#page_blurb_upload_image_button' ).text( 'Set listing image' );
	});

});