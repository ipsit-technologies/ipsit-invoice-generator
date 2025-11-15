/**
 * Template Builder JavaScript - Visual Field Manager
 */

(function($) {
    'use strict';
    
    // Initialize on page load - detect project template and generate HTML if needed
    $(document).ready(function() {
        var existingHtml = $('#generated_html_content').val();
        var isProjectTemplate = $('#is_project_template').val() === '1';
        
        // If template is loaded but HTML is empty or needs regeneration, generate it
        if (existingHtml && existingHtml.indexOf('{items_table}') !== -1) {
            // Template has placeholders, check if we need to regenerate
            var billFromFields = [];
            $('#bill-from-selected .ig-field-item').each(function() {
                billFromFields.push({
                    field: $(this).data('field'),
                    label: $(this).find('.ig-field-label').val() || $(this).data('label'),
                    enabled: true
                });
            });
            
            var billToFields = [];
            $('#bill-to-selected .ig-field-item').each(function() {
                billToFields.push({
                    field: $(this).data('field'),
                    label: $(this).find('.ig-field-label').val() || $(this).data('label'),
                    enabled: true,
                    variable: $(this).data('variable') || ''
                });
            });
            
            // Only regenerate if we have fields
            if (billFromFields.length > 0 || billToFields.length > 0) {
                generateTemplateHTML(billFromFields, billToFields, isProjectTemplate);
            }
        }
    });
    
    // Make fields sortable
    $('.ig-sortable').sortable({
        handle: '.dashicons-menu',
        placeholder: 'ig-field-placeholder',
        tolerance: 'pointer',
        cursor: 'move'
    });
    
    // Add field from available to selected
    $(document).on('click', '.ig-available-fields .ig-field-item', function() {
        var $item = $(this).clone();
        $item.removeClass('ig-custom-field').addClass('ig-selected');
        $item.find('.dashicons-plus-alt').removeClass('dashicons-plus-alt').addClass('dashicons-menu');
        
        var label = $item.data('label');
        var field = $item.data('field');
        
        // Create new structure
        var $newItem = $('<li class="ig-field-item ig-selected" data-field="' + field + '" data-label="' + label + '">' +
            '<span class="dashicons dashicons-menu"></span>' +
            '<input type="text" class="ig-field-label" value="' + label + '" placeholder="Field Label">' +
            '<span class="ig-field-name">' + field + '</span>' +
            '<button type="button" class="button button-small ig-remove-field">Remove</button>' +
            '</li>');
        
        // Add variable if custom field
        if ($item.data('variable')) {
            $newItem.attr('data-variable', $item.data('variable'));
        }
        
        var $targetList = $(this).closest('.ig-field-manager').find('.ig-selected-fields .ig-field-list');
        $targetList.append($newItem);
        $(this).remove();
    });
    
    // Remove field
    $(document).on('click', '.ig-remove-field', function() {
        var $item = $(this).closest('.ig-field-item');
        var field = $item.data('field');
        var label = $item.find('.ig-field-label').val() || $item.data('label');
        var isCustom = $item.hasClass('ig-custom-field') || $item.data('variable');
        
        // Move back to available
        var $availableList = $item.closest('.ig-field-manager').find('.ig-available-fields .ig-field-list');
        
        var $newItem;
        if (isCustom) {
            $newItem = $('<li class="ig-field-item ig-custom-field" data-field="' + field + '" data-label="' + label + '" data-variable="' + ($item.data('variable') || '') + '">' +
                '<span class="dashicons dashicons-plus-alt"></span>' +
                label + ' <small>(custom)</small>' +
                '</li>');
        } else {
            $newItem = $('<li class="ig-field-item" data-field="' + field + '" data-label="' + label + '">' +
                '<span class="dashicons dashicons-plus-alt"></span>' +
                label +
                '</li>');
        }
        
        $availableList.append($newItem);
        $item.remove();
    });
    
    // Update label
    $(document).on('input', '.ig-field-label', function() {
        var $item = $(this).closest('.ig-field-item');
        $item.data('label', $(this).val());
    });
    
    // Generate HTML/CSS before form submit
    $('#ig-template-form').on('submit', function(e) {
        e.preventDefault();
        
        // Check if this is a project template
        var isProjectTemplate = false;
        var templateName = $('#template_name').val().toLowerCase();
        if (templateName.indexOf('project') !== -1) {
            isProjectTemplate = true;
        }
        // Also check existing template type
        var existingHtml = $('#generated_html_content').val();
        if (existingHtml && (existingHtml.indexOf('Project Description') !== -1 || existingHtml.indexOf('Project Price') !== -1)) {
            isProjectTemplate = true;
        }
        // Also check hidden field
        if ($('#is_project_template').val() === '1') {
            isProjectTemplate = true;
        }
        
        // Collect field configurations
        var billFromFields = [];
        $('#bill-from-selected .ig-field-item').each(function() {
            billFromFields.push({
                field: $(this).data('field'),
                label: $(this).find('.ig-field-label').val() || $(this).data('label'),
                enabled: true
            });
        });
        
        var billToFields = [];
        $('#bill-to-selected .ig-field-item').each(function() {
            billToFields.push({
                field: $(this).data('field'),
                label: $(this).find('.ig-field-label').val() || $(this).data('label'),
                enabled: true,
                variable: $(this).data('variable') || ''
            });
        });
        
        var settings = {
            bill_from_fields: billFromFields,
            bill_to_fields: billToFields,
            is_project_template: isProjectTemplate
        };
        
        var settingsJson = JSON.stringify(settings);
        $('#template_settings').val(settingsJson);
        
        // Generate HTML and CSS
        generateTemplateHTML(billFromFields, billToFields, isProjectTemplate);
        
        // Submit form via AJAX
        var formData = $(this).serialize();
        formData += '&action=ig_save_template';
        formData += '&nonce=' + igAdmin.nonce;
        
        // Ensure template_settings is included (in case serialize() doesn't pick up the updated value)
        formData += '&template_settings=' + encodeURIComponent(settingsJson);
        
        var submitBtn = $(this).find('button[type="submit"]');
        var originalText = submitBtn.text();
        submitBtn.prop('disabled', true).text(igAdmin.strings.saving);
        
        $.ajax({
            url: igAdmin.ajaxUrl,
            type: 'POST',
            data: formData,
            success: function(response) {
                submitBtn.prop('disabled', false).text(originalText);
                
                console.log('AJAX Response:', response); // Debug
                
                if (response.success) {
                    showNotice(response.data.message, 'success');
                    // Increase delay to show message before reload
                    setTimeout(function() {
                        var templateId = response.data.template_id || $('input[name="template_id"]').val();
                        if (templateId) {
                            window.location.href = '?page=ipsit-ig-templates&action=builder&id=' + templateId;
                        } else {
                            window.location.reload();
                        }
                    }, 2000); // Increased to 2 seconds
                } else {
                    showNotice(response.data.message || 'Failed to save template', 'error');
                }
            },
            error: function(xhr, status, error) {
                submitBtn.prop('disabled', false).text(originalText);
                console.error('AJAX Error:', xhr, status, error); // Debug
                showNotice('Error saving template: ' + error, 'error');
            }
        });
        
        return false; // Prevent default form submission
    });
    
    function generateTemplateHTML(billFromFields, billToFields, isProjectTemplate) {
        var html = generateDefaultHTML(billFromFields, billToFields, isProjectTemplate);
        var css = generateDefaultCSS();
        
        $('#generated_html_content').val(html);
        $('#generated_css_content').val(css);
    }
    
    function generateDefaultHTML(billFromFields, billToFields, isProjectTemplate) {
        var tableHeaders = '';
        if (isProjectTemplate) {
            tableHeaders = '<th>Project Description</th>' +
                '<th class="text-right">Project Price</th>' +
                '<th class="text-right">Project Amount</th>';
        } else {
            tableHeaders = '<th>Description</th>' +
                '<th class="text-right">Quantity</th>' +
                '<th class="text-right">Price</th>' +
                '<th class="text-right">Total</th>';
        }
        
        var html = '<div class="invoice-header">' +
            '<div class="company-info">' +
            '{company_logo}' +
            '{company_name_conditional}' +
            '</div>' +
            '<div class="invoice-info">' +
            '<h2>INVOICE</h2>' +
            '<div class="invoice-details-info">' +
            '<p><strong>Invoice #:</strong> {invoice_number}</p>' +
            '<p><strong>Date:</strong> {invoice_date}</p>' +
            '{due_date_row}' +
            '</div>' +
            '</div>' +
            '</div>' +
            '<div class="invoice-details">' +
            '<div class="company-details">' +
            '<div class="section-title">Bill From:</div>' +
            generateFieldsHTML(billFromFields, 'company') +
            '</div>' +
            '<div class="client-details">' +
            '<div class="section-title">Bill To:</div>' +
            generateFieldsHTML(billToFields, 'client') +
            '</div>' +
            '</div>' +
            '<table class="items-table">' +
            '<thead>' +
            '<tr>' +
            tableHeaders +
            '</tr>' +
            '</thead>' +
            '<tbody>' +
            '{items_table}' +
            '</tbody>' +
            '</table>' +
            '<div class="totals-wrapper">' +
            '{payment_method}' +
            '<div class="totals">' +
            '<table>' +
            '<tr>' +
            '<td class="label">Subtotal:</td>' +
            '<td class="amount">{subtotal}</td>' +
            '</tr>' +
            '{tax_row}' +
            '<tr class="total-row">' +
            '<td class="label">Total:</td>' +
            '<td class="amount">{total}</td>' +
            '</tr>' +
            '</table>' +
            '</div>' +
            '</div>' +
            '<div class="notes">' +
            '<strong>Notes:</strong>' +
            '<div>{notes}</div>' +
            '</div>';
        
        return html;
    }
    
    function generateFieldsHTML(fields, type) {
        var html = '';
        fields.forEach(function(field) {
            var variable;
            if (field.variable) {
                // Custom field with explicit variable
                variable = field.variable;
            } else {
                // Standard field - field.field already contains the prefix (e.g., 'client_address' or 'company_name')
                // So we just need to wrap it in braces
                variable = '{' + field.field + '}';
            }
            html += '<p><strong>' + field.label + ':</strong> ' + variable + '</p>';
        });
        return html;
    }
    
    function generateDefaultCSS() {
        return 'body { font-family: Arial, sans-serif; margin: 0; padding: 20px; color: #333; font-size: 12px; }' +
            '.invoice-header { display: flex; justify-content: space-between; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 2px solid #eee; }' +
            '.company-info { display: flex; flex-direction: column; }' +
            '.company-info img { max-width: 150px; max-height: 80px; margin-bottom: 10px; }' +
            '.company-info h1 { margin: 0 0 10px 0; font-size: 24px; color: #2c3e50; }' +
            '.invoice-info { text-align: right; }' +
            '.invoice-info h2 { margin: 0 0 15px 0; font-size: 28px; color: #2c3e50; }' +
            '.invoice-info .invoice-details-info { margin-top: 15px; }' +
            '.invoice-info .invoice-details-info p { margin: 5px 0; }' +
            '.invoice-details { display: flex !important; justify-content: space-between !important; margin: 30px 0 !important; width: 100% !important; }' +
            '.company-details, .client-details { width: 48% !important; display: inline-block !important; vertical-align: top !important; }' +
            '.section-title { margin: 0 0 15px 0; color: #2c3e50; border-bottom: 1px solid #eee; padding-bottom: 5px; font-weight: bold; font-size: 14px; }' +
            '.company-details p, .client-details p { margin: 5px 0; }' +
            '.items-table { width: 100%; border-collapse: collapse; margin: 30px 0; }' +
            '.items-table th, .items-table td { border: 1px solid #ddd; padding: 12px; text-align: left; }' +
            '.items-table th { background-color: #f8f9fa; font-weight: bold; color: #2c3e50; }' +
            '.items-table tr:nth-child(even) { background-color: #f8f9fa; }' +
            '.items-table .text-right { text-align: right; }' +
            '.totals-wrapper { display: flex !important; justify-content: space-between !important; margin: 30px 0 !important; width: 100% !important; }' +
            '.payment-info, .totals { width: 48% !important; display: inline-block !important; vertical-align: top !important; border-top: 1px solid #eee; padding-top: 20px; }' +
            '.payment-info h3 { margin: 0 0 15px 0; color: #2c3e50; border-bottom: none !important; padding-bottom: 5px; font-weight: bold; font-size: 14px; }' +
            '.payment-info p { margin: 5px 0; font-size: 12px; }' +
            '.totals { text-align: right; }' +
            '.totals table { width: 100%; border-collapse: collapse; }' +
            '.totals td { padding: 8px 12px; }' +
            '.totals .label { text-align: right; font-weight: bold; }' +
            '.totals .amount { text-align: right; }' +
            '.total-row { font-size: 18px; font-weight: bold; color: #2c3e50; padding-top: 10px; }' +
            '.total-row .amount { color: #2c3e50; }' +
            '.notes { margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; }';
    }
    
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
    
    // Preview template button
    $('#ig-preview-template').on('click', function() {
        var invoiceId = $(this).data('invoice-id');
        var templateId = $('input[name="template_id"]').val() || 0;
        
        // Check if this is a project template
        var isProjectTemplate = false;
        var templateName = $('#template_name').val().toLowerCase();
        if (templateName.indexOf('project') !== -1) {
            isProjectTemplate = true;
        }
        var existingHtml = $('#generated_html_content').val();
        if (existingHtml && (existingHtml.indexOf('Project Description') !== -1 || existingHtml.indexOf('Project Price') !== -1)) {
            isProjectTemplate = true;
        }
        
        // Collect current field configurations
        var billFromFields = [];
        $('#bill-from-selected .ig-field-item').each(function() {
            billFromFields.push({
                field: $(this).data('field'),
                label: $(this).find('.ig-field-label').val() || $(this).data('label'),
                enabled: true
            });
        });
        
        var billToFields = [];
        $('#bill-to-selected .ig-field-item').each(function() {
            billToFields.push({
                field: $(this).data('field'),
                label: $(this).find('.ig-field-label').val() || $(this).data('label'),
                enabled: true,
                variable: $(this).data('variable') || ''
            });
        });
        
        // Generate HTML and CSS
        generateTemplateHTML(billFromFields, billToFields, isProjectTemplate);
        
        var htmlContent = $('#generated_html_content').val();
        var cssContent = $('#generated_css_content').val();
        
        if (!htmlContent) {
            showNotice('Please configure fields and save template first.', 'error');
            return;
        }
        
        $.ajax({
            url: igAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'ig_preview_template',
                invoice_id: invoiceId,
                template_id: templateId,
                html_content: htmlContent,
                css_content: cssContent,
                nonce: igAdmin.nonce
            },
            success: function(response) {
                if (response.success) {
                    // Create modal if it doesn't exist
                    if ($('#ig-preview-modal').length === 0) {
                        $('body').append('<div id="ig-preview-modal" class="ig-modal"><div class="ig-modal-content ig-modal-large"><span class="ig-modal-close">&times;</span><h2>Template Preview</h2><div id="ig-preview-modal-content"></div></div></div>');
                    }
                    $('#ig-preview-modal-content').html(response.data.html);
                    $('#ig-preview-modal').addClass('is-open');
                } else {
                    showNotice(response.data.message || 'Failed to preview template', 'error');
                }
            },
            error: function(xhr, status, error) {
                console.error('Preview Error:', xhr, status, error);
                showNotice('Error previewing template: ' + error, 'error');
            }
        });
    });
    
    // Close modal
    $(document).on('click', '.ig-modal-close, .ig-modal', function(e) {
        if ($(e.target).hasClass('ig-modal') || $(e.target).hasClass('ig-modal-close')) {
            $('#ig-preview-modal').removeClass('is-open');
        }
    });
    
})(jQuery);
