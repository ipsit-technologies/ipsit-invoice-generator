/**
 * Clients JavaScript
 */

(function($) {
    'use strict';
    
    // Use timestamp to ensure unique keys for new fields
    var fieldIndex = Date.now();
    
    // Add custom field
    $('#ig-add-custom-field').on('click', function() {
        var template = $('#ig-custom-field-template').html();
        template = template.replace(/\[new\]/g, '[new_' + fieldIndex + ']');
        $('#ig-custom-fields').append(template);
        fieldIndex++;
    });
    
    // Remove custom field
    $(document).on('click', '.ig-remove-field', function() {
        $(this).closest('.ig-custom-field-row').remove();
    });
    
    // Save client form
    $('#ig-client-form').on('submit', function(e) {
        e.preventDefault();
        
        var formData = $(this).serialize();
        formData += '&action=ig_save_client';
        formData += '&nonce=' + igAdmin.nonce;
        
        var submitBtn = $(this).find('button[type="submit"]');
        var originalText = submitBtn.text();
        submitBtn.prop('disabled', true).text(igAdmin.strings.saving);
        
        $.ajax({
            url: igAdmin.ajaxUrl,
            type: 'POST',
            data: formData,
            success: function(response) {
                submitBtn.prop('disabled', false).text(originalText);
                
                if (response.success) {
                    showNotice(response.data.message, 'success');
                    // Reload page after a short delay to show updated custom fields
                    setTimeout(function() {
                        if (response.data.client_id) {
                            window.location.href = '?page=ipsit-ig-clients&action=edit&id=' + response.data.client_id;
                        } else {
                            window.location.reload();
                        }
                    }, 1000);
                } else {
                    showNotice(response.data.message, 'error');
                }
            },
            error: function() {
                submitBtn.prop('disabled', false).text(originalText);
                showNotice(igAdmin.strings.error, 'error');
            }
        });
    });
    
    function showNotice(message, type) {
        type = type || 'success';
        var notice = $('<div class="ig-notice ig-notice-' + type + '">' + message + '</div>');
        $('.wrap h1').after(notice);
        setTimeout(function() {
            notice.fadeOut(function() {
                notice.remove();
            });
        }, 5000);
    }
    
})(jQuery);

