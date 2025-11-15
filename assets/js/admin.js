/**
 * Admin JavaScript for Invoice Generator
 * Improved with module pattern and reduced duplication
 */

(function($) {
    'use strict';
    
    /**
     * Invoice Generator Admin Module
     */
    var IGAdmin = {
        
        /**
         * Initialize
         */
        init: function() {
            this.bindEvents();
        },
        
        /**
         * Bind all event handlers
         */
        bindEvents: function() {
            $(document).on('click', '.ig-delete-invoice', this.deleteHandler.bind(this, 'invoice'));
            $(document).on('click', '.ig-delete-client', this.deleteHandler.bind(this, 'client'));
            $(document).on('click', '.ig-delete-template', this.deleteHandler.bind(this, 'template'));
        },
        
        /**
         * Generic delete handler
         */
        deleteHandler: function(type, e) {
            e.preventDefault();
            
            if (!confirm(igAdmin.strings.confirmDelete)) {
                return;
            }
            
            var $btn = $(e.currentTarget);
            var id = $btn.data(type + '-id');
            var $row = $btn.closest('tr');
            
            // Disable button during request
            $btn.prop('disabled', true).addClass('disabled');
            
            $.ajax({
                url: igAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'ig_delete_' + type,
                    [type + '_id']: id,
                    nonce: igAdmin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $row.fadeOut(400, function() {
                            $row.remove();
                            // Check if table is empty
                            var $tbody = $row.parent();
                            if ($tbody.find('tr').length === 0) {
                                $tbody.closest('table').replaceWith(
                                    '<p>No ' + type + 's found.</p>'
                                );
                            }
                        });
                        IGAdmin.showNotice(response.data.message, 'success');
                    } else {
                        $btn.prop('disabled', false).removeClass('disabled');
                        IGAdmin.showNotice(response.data.message, 'error');
                    }
                },
                error: function() {
                    $btn.prop('disabled', false).removeClass('disabled');
                    IGAdmin.showNotice('An error occurred. Please try again.', 'error');
                }
            });
        },
        
        /**
         * Show notice message
         */
        showNotice: function(message, type) {
            type = type || 'success';
            var $notice = $('<div class="ig-notice ig-notice-' + type + '">' + message + '</div>');
            $('.wrap h1').after($notice);
            
            setTimeout(function() {
                $notice.fadeOut(400, function() {
                    $notice.remove();
                });
            }, 5000);
        },
        
        /**
         * Show success notice
         */
        showSuccess: function(message) {
            this.showNotice(message, 'success');
        },
        
        /**
         * Show error notice
         */
        showError: function(message) {
            this.showNotice(message, 'error');
        },
        
        /**
         * Show loading state on element
         */
        showLoading: function($element, text) {
            text = text || 'Loading...';
            $element.prop('disabled', true).addClass('disabled loading');
            if ($element.is('button, input[type="submit"]')) {
                $element.data('original-text', $element.val() || $element.text());
                $element.is('input') ? $element.val(text) : $element.text(text);
            }
        },
        
        /**
         * Hide loading state on element
         */
        hideLoading: function($element) {
            $element.prop('disabled', false).removeClass('disabled loading');
            var originalText = $element.data('original-text');
            if (originalText) {
                $element.is('input') ? $element.val(originalText) : $element.text(originalText);
                $element.removeData('original-text');
            }
        },
        
        /**
         * Make AJAX request
         */
        ajax: function(action, data, callback) {
            var requestData = $.extend({}, data, {
                action: action,
                nonce: igAdmin.nonce
            });
            
            $.ajax({
                url: igAdmin.ajaxUrl,
                type: 'POST',
                data: requestData,
                success: function(response) {
                    callback(response.success, response.data || {});
                },
                error: function() {
                    callback(false, { message: 'An error occurred. Please try again.' });
                }
            });
        }
    };
    
    // Initialize on document ready
    $(document).ready(function() {
        IGAdmin.init();
    });
    
    // Expose globally for external use
    window.IGAdmin = IGAdmin;
    
})(jQuery);
