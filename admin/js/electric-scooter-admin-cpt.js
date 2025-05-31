(function( $ ) {
    'use strict';

    $(document).ready(function() {
        // JavaScript for Joining Package CPT admin interface.
        // For example, handling the media uploader for a custom image field.

        /*
        if (typeof wp === 'undefined' || typeof wp.media === 'undefined') {
            // WordPress media uploader is not available
            return;
        }

        var mediaUploader;

        $('#upload_package_image_button').on('click', function(e) {
            e.preventDefault();
            if (mediaUploader) {
                mediaUploader.open();
                return;
            }
            mediaUploader = wp.media.frames.file_frame = wp.media({
                title: 'Choose Package Image',
                button: {
                    text: 'Choose Image'
                },
                multiple: false
            });

            mediaUploader.on('select', function() {
                var attachment = mediaUploader.state().get('selection').first().toJSON();
                $('#package_image_id').val(attachment.id);
                $('#package_image_preview_wrapper').html('<img src="' + attachment.url + '" style="max-width:150px;height:auto;" />');
            });

            mediaUploader.open();
        });
        */
    });

})( jQuery );
