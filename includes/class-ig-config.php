<?php
/**
 * Configuration Class
 *
 * Central configuration and constants for the plugin
 */

if (!defined('ABSPATH')) {
    exit;
}

class IG_Config {
    
    // Pagination
    const DEFAULT_LIMIT = 20;
    const MAX_LIMIT = 100;
    const MIN_LIMIT = 5;
    
    // Cache durations (in seconds)
    const CACHE_COMPANY_DATA = HOUR_IN_SECONDS;
    const CACHE_TEMPLATES = DAY_IN_SECONDS;
    const CACHE_SETTINGS = HOUR_IN_SECONDS;
    
    // Rate limiting
    const RATE_LIMIT_REQUESTS = 10;
    const RATE_LIMIT_WINDOW = MINUTE_IN_SECONDS;
    
    // File upload
    const MAX_LOGO_SIZE = 2097152; // 2MB
    const ALLOWED_LOGO_TYPES = array('image/jpeg', 'image/png', 'image/gif');
    
    // Invoice defaults
    const DEFAULT_INVOICE_STATUS = 'draft';
    const DEFAULT_TAX_RATE = 0;
    const INVOICE_NUMBER_MIN_PADDING = 1;
    const INVOICE_NUMBER_MAX_PADDING = 10;
    
    // Capabilities
    const CAP_MANAGE_INVOICES = 'manage_invoices';
    const CAP_MANAGE_CLIENTS = 'manage_invoice_clients';
    const CAP_MANAGE_TEMPLATES = 'manage_invoice_templates';
    const CAP_MANAGE_SETTINGS = 'manage_invoice_settings';
    
    // Cache keys
    const CACHE_KEY_COMPANY = 'ipsit_ig_company_data';
    const CACHE_KEY_TEMPLATES = 'ipsit_ig_templates_list';
    const CACHE_KEY_SETTINGS = 'ipsit_ig_settings_cache';
    
    // Rate limit keys
    const RATE_LIMIT_PREFIX = 'ig_rate_limit_';
    
    // Database version
    const DB_VERSION = '1.2.0';
    
    /**
     * Get allowed HTML for template content
     */
    public static function get_template_allowed_html() {
        return array(
            'div' => array('class' => array(), 'style' => array(), 'id' => array()),
            'span' => array('class' => array(), 'style' => array()),
            'table' => array('class' => array(), 'style' => array(), 'cellpadding' => array(), 'cellspacing' => array(), 'border' => array()),
            'thead' => array('class' => array(), 'style' => array()),
            'tbody' => array('class' => array(), 'style' => array()),
            'tfoot' => array('class' => array(), 'style' => array()),
            'tr' => array('class' => array(), 'style' => array()),
            'td' => array('class' => array(), 'style' => array(), 'colspan' => array(), 'rowspan' => array()),
            'th' => array('class' => array(), 'style' => array(), 'colspan' => array(), 'rowspan' => array()),
            'p' => array('class' => array(), 'style' => array()),
            'h1' => array('class' => array(), 'style' => array()),
            'h2' => array('class' => array(), 'style' => array()),
            'h3' => array('class' => array(), 'style' => array()),
            'h4' => array('class' => array(), 'style' => array()),
            'img' => array('src' => array(), 'class' => array(), 'style' => array(), 'alt' => array(), 'width' => array(), 'height' => array()),
            'strong' => array('class' => array(), 'style' => array()),
            'em' => array('class' => array(), 'style' => array()),
            'b' => array('class' => array(), 'style' => array()),
            'i' => array('class' => array(), 'style' => array()),
            'u' => array('class' => array(), 'style' => array()),
            'br' => array(),
            'hr' => array('class' => array(), 'style' => array()),
            'ul' => array('class' => array(), 'style' => array()),
            'ol' => array('class' => array(), 'style' => array()),
            'li' => array('class' => array(), 'style' => array()),
            'a' => array('href' => array(), 'class' => array(), 'style' => array(), 'target' => array()),
        );
    }
    
    /**
     * Get invoice statuses
     */
    public static function get_invoice_statuses() {
        return array(
            'draft' => __('Draft', 'ipsit-invoice-generator'),
            'sent' => __('Sent', 'ipsit-invoice-generator'),
            'paid' => __('Paid', 'ipsit-invoice-generator'),
            'overdue' => __('Overdue', 'ipsit-invoice-generator'),
            'cancelled' => __('Cancelled', 'ipsit-invoice-generator'),
        );
    }
    
    /**
     * Get payment methods
     */
    public static function get_payment_methods() {
        return array(
            'bank_transfer' => __('Bank Transfer', 'ipsit-invoice-generator'),
            'paypal' => __('PayPal', 'ipsit-invoice-generator'),
            'stripe' => __('Stripe', 'ipsit-invoice-generator'),
            'cash' => __('Cash', 'ipsit-invoice-generator'),
            'check' => __('Check', 'ipsit-invoice-generator'),
            'other' => __('Other', 'ipsit-invoice-generator'),
        );
    }
}

