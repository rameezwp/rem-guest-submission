jQuery(document).ready(function($) {
// Creating Property
    jQuery('.guest_upload_image_button').click(function(event) {
      
      /* Act on the event */
        $("#imageupload").click();
    });
    jQuery('#create-property-gust-user').submit(function(event){
        event.preventDefault();
        $('.creating-prop').show();
        
        if (jQuery("#wp-rem-content-wrap").hasClass("tmce-active")){
            content = tinyMCE.get('rem-content').getContent();
        }else{
            content = $('#rem-content').val();
        }        
        var ajaxurl = $(this).data('ajaxurl');
        var fd = new FormData(this);
        fd.append("content", content);
        fd.append("action", 'rem_guest_create_pro_ajax');
        console.log(ajaxurl);
        $.ajax({
          url: ajaxurl,
          data: fd,
          processData: false,
          contentType: false,
          type: 'POST',
          success: function(resp){
            $('.creating-prop').removeClass('alert-info').addClass('alert-success');
            $('.creating-prop .msg').html(rem_property_vars.success_message);
            // window.location = resp;
          }
        });
    }); 

    var rem_property_images;

    jQuery('.info-block').on('click', '.upload_image_button', function( event ){
     
        event.preventDefault();
    alert("hello"); 
        // var parent = jQuery(this).closest('.tab-content').find('.thumbs-prev');
        // Create the media frame.
        rem_property_images = wp.media.frames.rem_property_images = wp.media({
          title: jQuery( this ).data( 'title' ),
          button: {
            text: jQuery( this ).data( 'btntext' ),
          },
          multiple: true  // Set to true to allow multiple files to be selected
        });
        
        // When an image is selected, run a callback.
        rem_property_images.on( 'select', function() {
            // We set multiple to false so only get one image from the uploader
            var selection = rem_property_images.state().get('selection');
            var already_selected = parseInt($('body .thumbs-prev > div').length);
            var new_selected = parseInt(selection.length);
            var total_images = already_selected + new_selected;
            // rem_property_vars.images_limit is localized variable
            if (total_images > rem_property_vars.images_limit && rem_property_vars.images_limit != 0) {
                alert(rem_property_vars.images_limit_message+rem_property_vars.images_limit);
            };
            selection.map( function( attachment, index ) {
                if ( index < (parseInt(rem_property_vars.images_limit) - already_selected ) || rem_property_vars.images_limit == 0 ) {
                    attachment = attachment.toJSON();
                    jQuery('.thumbs-prev').append('<div><input type="hidden" name="rem_property_data[property_images]['+attachment.id+']" value="'+attachment.id+'"><img src="'+attachment.url+'"><span class="dashicons dashicons-dismiss"></span></div>');
                };

            });
        });
     
        // Finally, open the modal
        rem_property_images.open();
    });
});