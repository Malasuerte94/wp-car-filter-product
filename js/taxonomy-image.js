jQuery(document).ready(function($) {
  // Media uploader script
  $(document).on('click', '.ccc_taxonomy_image_button', function(e) {
    e.preventDefault();
    let button = $(this);
    let inputField = $('#ccc_taxonomy_image');
    let image = wp.media({
      title: 'Select or Upload Image',
      button: {
        text: 'Use Image'
      },
      multiple: false
    });

    image.on('select', function() {
      let attachment = image.state().get('selection').first().toJSON();
      inputField.val(attachment.url);
      button.siblings('.ccc_taxonomy_image_preview').html('<img src="' + attachment.url + '" style="max-width: 100px; max-height: 100px;" />');
    });

    image.open();
  });
});
