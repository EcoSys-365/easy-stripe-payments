// On clicking the .product_image_button, open the WordPress media uploader to select an image.
// When an image is selected, find the nearest .product_image_input field (preferring inside the modal if present)
// and set its value to the selected image URL. If no input field is found, log a warning in the console.
jQuery(document).ready(function($) {
    
    $(document).on('click', '.product_image_button', function(e) {
        
        e.preventDefault();
        let $button = $(this);

        let mediaUploader = wp.media({
            title: 'Choose an image',
            button: { text: 'Use image' },
            multiple: false
        });

        mediaUploader.on('select', function() {
            const attachment = mediaUploader.state().get('selection').first().toJSON();

            // Versuche zuerst Input im Modal zu finden
            let $input = $button.closest('.modal-content').find('.product_image_input');

            // Falls nicht im Modal, suche Input in der Nähe vom Button (z.B. Eltern)
            if ($input.length === 0) {
                $input = $button.closest('form, div').find('.product_image_input');
            }

            // Falls Input gefunden, Wert setzen
            if ($input.length) {
                $input.val(attachment.url);
            } else {
                console.warn('Kein passendes Input-Feld für Bild-URL gefunden.');
            }
        });

        mediaUploader.open();
        
    });
    
});
