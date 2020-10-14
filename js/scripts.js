jQuery(document).ready(function($) {
// Creating Property
    
    $('.imageupload').on('change', function() {
        var customeclass = readImageFile(this);
        $(this).addClass(customeclass);
        $(this).closest('label').hide();
    });
    /*
     **============= Create Uploaded Image HTML  ================ 
     */
    function uploadedImageHTML(customeclass, image_url) {
        let html = '';
        html += '<div class="rem-img-box" style="height: auto;">';
        html += '<img src="' + image_url + '">';
        html += '<span class="dashicons dashicons-dismiss rem-media-delete" data-fileinput="'+customeclass+'">';
        html += '</div>';

        return html;
    }
    function readImageFile(input){
        var resp = '';
        for (let i = 0; i < input.files.length; i++) {
            if (input.files && input.files[i]) {
                var reader = new FileReader();
                var extension = input.files[i].name.split('.').pop().toLowerCase();
                var customeclass = '';
                rendom_number = Math.floor((Math.random() * 100) + 1);
                customeclass = extension+rendom_number;
                reader.fileName = input.files[i].name;
                reader.onload = function(e) {
                    
                    var filename = e.target.fileName;
                    var image_url = e.target.result;
                    // Get Uploaded Image HTML BOX
                    var extension = filename.split('.').pop().toLowerCase();
                    const imageHTML = uploadedImageHTML(customeclass, image_url);
                   
                    $('.thumbs-prev').prepend(imageHTML);
                }

                reader.readAsDataURL(input.files[0]);
                resp = customeclass;
            } else {
                alert("Sorry - you're browser doesn't support the FileReader API");
            }
        }
        return resp;
    }

    $('.attachmentsupload').on('change', function() {
        var customeclass = readAttachmentFile(this);
        $(this).addClass(customeclass);
        $(this).closest('label').hide();
    });

    function readAttachmentFile(input){
        
        var resp = '';
        for (let i = 0; i < input.files.length; i++) {
            if (input.files && input.files[i]) {
                var extension = input.files[i].name.split('.').pop().toLowerCase();
                var customeclass = '';
                rendom_number = Math.floor((Math.random() * 100) + 1);
                customeclass = extension+rendom_number;
                var reader = new FileReader();
                reader.fileName = input.files[i].name;
                reader.onload = function(e) {
                    
                    var filename = e.target.fileName;
                    var attachment_url = e.target.result;
                    // Get Uploaded Image HTML BOX
                    const imageHTML = uploadedAttachmentHTML(customeclass, extension);
                    $(input).closest('.upload-attachments-wrap').find('.attachments-prev ').prepend(imageHTML);
                }

                reader.readAsDataURL(input.files[0]);
                resp = customeclass;
            } else {
                alert("Sorry - you're browser doesn't support the FileReader API");
            }
        }

        return resp;
    }
    function uploadedAttachmentHTML(customeclass, extension) {
        let html = '';
        html += '<div class="rem-attachment-box" style="width: 20%;height: auto;">';
        html += '<span class="file-type-icon '+extension+'" filetype="'+extension+'"><span class="fileCorner"></span></span>';
        html += '<span class="dashicons dashicons-dismiss rem-media-delete" data-fileinput="'+customeclass+'">';  
        html += '</div>';

        return html;
    }

    jQuery('.thumbs-prev').on('click', '.dashicons-dismiss', function() {
        jQuery(this).parent('div').remove();
        var inputclass = $(this).data('fileinput');
        inputclass = '.'+inputclass;
        $(inputclass).val('');
        $(inputclass).closest('label').show();
    }); 
    jQuery('.attachments-prev').on('click', '.dashicons-dismiss', function() {
        jQuery(this).parent('div').remove();
        var inputclass = $(this).data('fileinput');
        inputclass = '.'+inputclass;
        $(inputclass).val('');
        $(inputclass).closest('label').show();
    });
    
    jQuery('#create-property-guest-user').submit(function(event){
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
        $.ajax({
          url: ajaxurl,
          data: fd,
          processData: false,
          contentType: false,
          type: 'POST',
          success: function(resp){
            $('.creating-prop').removeClass('alert-info').addClass('alert-success');
            $('.creating-prop .msg').html(rem_property_vars.success_message);
            window.location = resp;
          }
        });
    }); 
});