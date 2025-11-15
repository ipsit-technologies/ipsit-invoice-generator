/**
 * Settings Form JavaScript
 */

(function($) {
    'use strict';
    
    // Currency code and symbol mapping
    var currencyMap = {
        'USD': '$',
        'EUR': '€',
        'GBP': '£',
        'JPY': '¥',
        'AUD': 'A$',
        'CAD': 'C$',
        'CHF': 'CHF',
        'CNY': '¥',
        'INR': '₹',
        'SGD': 'S$',
        'NZD': 'NZ$',
        'MXN': 'Mex$',
        'BRL': 'R$',
        'ZAR': 'R',
        'RUB': '₽',
        'KRW': '₩',
        'TRY': '₺',
        'AED': 'د.إ',
        'SAR': 'ر.س'
    };
    
    // Sync currency symbol when currency code changes
    $('#currency').on('change', function() {
        var currencyCode = $(this).val();
        if (currencyMap[currencyCode]) {
            $('#currency_symbol').val(currencyMap[currencyCode]);
        }
    });
    
    // Save settings form
    $('#ig-settings-form').on('submit', function(e) {
        e.preventDefault();
        
        var formData = $(this).serialize();
        formData += '&action=ig_save_settings';
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
    
    // Save company form
    $('#ig-company-form').on('submit', function(e) {
        e.preventDefault();
        
        var formData = new FormData(this);
        formData.append('action', 'ig_save_company');
        formData.append('nonce', igAdmin.nonce);
        
        var submitBtn = $(this).find('button[type="submit"]');
        var originalText = submitBtn.text();
        submitBtn.prop('disabled', true).text(igAdmin.strings.saving);
        
        $.ajax({
            url: igAdmin.ajaxUrl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                submitBtn.prop('disabled', false).text(originalText);
                
                if (response.success) {
                    showNotice(response.data.message, 'success');
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
    
    // Toggle company payment method fields
    $('#default_payment_method').on('change', function() {
        if ($(this).is(':checked')) {
            $('#company-payment-method-fields').removeClass('ig-hidden');
        } else {
            $('#company-payment-method-fields').addClass('ig-hidden');
        }
    });
    
    // Reset colors to defaults
    $('#ig-reset-colors').on('click', function() {
        $('#design_primary_color').val('#2271b1');
        $('#design_secondary_color').val('#646970');
        $('#design_success_color').val('#00a32a');
        $('#design_error_color').val('#d63638');
        $('#design_warning_color').val('#f0b849');
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

