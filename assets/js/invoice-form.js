/**
 * Invoice Form JavaScript
 */

(function($) {
    'use strict';
    
    var itemIndex = $('#ig-items-table tbody tr').length;
    
    // Get currency symbol
    function getCurrencySymbol() {
        // Try to get from page, fallback to $
        var symbol = $('#ig-currency-symbol').data('symbol') || '$';
        return symbol;
    }
    
    // Calculate totals
    function calculateTotals() {
        var subtotal = 0;
        var currencySymbol = getCurrencySymbol();
        
        $('#ig-items-table tbody tr').each(function() {
            var qty = parseFloat($(this).find('.ig-item-qty').val()) || 0;
            var price = parseFloat($(this).find('.ig-item-price').val()) || 0;
            var total = qty * price;
            
            $(this).find('.ig-item-total').text(currencySymbol + total.toFixed(2));
            subtotal += total;
        });
        
        var taxRate = parseFloat($('#tax_rate').val()) || 0;
        var tax = subtotal * (taxRate / 100);
        var total = subtotal + tax;
        
        $('#ig-subtotal').text(currencySymbol + subtotal.toFixed(2));
        $('#ig-tax').text(currencySymbol + tax.toFixed(2));
        $('#ig-total').html('<strong>' + currencySymbol + total.toFixed(2) + '</strong>');
    }
    
    // Add item
    $('#ig-add-item').on('click', function() {
        var currencySymbol = getCurrencySymbol();
        var row = '<tr class="ig-item-row">' +
            '<td><textarea name="items[' + itemIndex + '][description]" rows="3" class="regular-text" required></textarea></td>' +
            '<td><input type="number" name="items[' + itemIndex + '][quantity]" value="1" step="0.01" min="0" class="ig-item-qty" required></td>' +
            '<td><input type="number" name="items[' + itemIndex + '][price]" value="0" step="0.01" min="0" class="ig-item-price" required></td>' +
            '<td class="ig-item-total">' + currencySymbol + '0.00</td>' +
            '<td><button type="button" class="button ig-remove-item">Remove</button></td>' +
            '</tr>';
        $('#ig-items-table tbody').append(row);
        itemIndex++;
    });
    
    // Remove item
    $(document).on('click', '.ig-remove-item', function() {
        $(this).closest('tr').remove();
        calculateTotals();
    });
    
    // Calculate on change
    $(document).on('input', '.ig-item-qty, .ig-item-price, #tax_rate', function() {
        calculateTotals();
    });
    
    // Initial calculation
    calculateTotals();
    
    // Toggle payment method fields
    $('#enable_payment_method').on('change', function() {
        if ($(this).is(':checked')) {
            $('#payment-method-fields').removeClass('ig-hidden');
        } else {
            $('#payment-method-fields').addClass('ig-hidden');
        }
    });
    
    // Save invoice form
    $('#ig-invoice-form').on('submit', function(e) {
        e.preventDefault();
        
        var formData = $(this).serialize();
        formData += '&action=ig_save_invoice';
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
                    if (response.data.invoice_id && !$('input[name="invoice_id"]').val()) {
                        window.location.href = '?page=ig-invoices&action=edit&id=' + response.data.invoice_id;
                    }
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
    
    // Toggle email accordion
    $('#ig-email-accordion-header').on('click', function() {
        var content = $('#ig-email-accordion-content');
        var toggle = $(this).find('.ig-accordion-toggle');
        
        if (content.hasClass('is-open')) {
            content.removeClass('is-open').slideUp(300);
            toggle.removeClass('dashicons-arrow-up-alt2').addClass('dashicons-arrow-down-alt2');
        } else {
            content.addClass('is-open').slideDown(300);
            toggle.removeClass('dashicons-arrow-down-alt2').addClass('dashicons-arrow-up-alt2');
        }
    });
    
    // Email form submit
    $('#ig-email-form').on('submit', function(e) {
        e.preventDefault();
        
        var formData = $(this).serialize();
        formData += '&action=ig_send_email';
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
                    $('#ig-email-form')[0].reset();
                    // Optionally collapse the accordion after successful send
                    $('#ig-email-accordion-content').removeClass('is-open').slideUp(300);
                    $('#ig-email-accordion-header .ig-accordion-toggle')
                        .removeClass('dashicons-arrow-up-alt2')
                        .addClass('dashicons-arrow-down-alt2');
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

