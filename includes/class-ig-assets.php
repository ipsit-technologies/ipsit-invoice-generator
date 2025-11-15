<?php
/**
 * Assets Management Class
 *
 * Handles enqueuing of CSS and JavaScript files
 */

if (!defined('ABSPATH')) {
    exit;
}

class IG_Assets {
    
    /**
     * Instance of this class
     */
    private static $instance = null;
    
    /**
     * Get instance of this class
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
    }
    
    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook) {
        // Only load on our plugin pages
        if (strpos($hook, 'ipsit-invoice-generator') === false && strpos($hook, 'ipsit-ig-') === false) {
            return;
        }
        
        // Ensure Dashicons are loaded for icons
        wp_enqueue_style('dashicons');
        
        // Enqueue CSS files in correct order
        // Variables first
        wp_enqueue_style(
            'ig-variables',
            IPSIT_IG_PLUGIN_URL . 'assets/css/variables.css',
            array(),
            IPSIT_IG_VERSION
        );
        
        // Base styles
        wp_enqueue_style(
            'ig-base',
            IPSIT_IG_PLUGIN_URL . 'assets/css/base.css',
            array('ig-variables'),
            IPSIT_IG_VERSION
        );
        
        // Components
        wp_enqueue_style(
            'ig-components',
            IPSIT_IG_PLUGIN_URL . 'assets/css/components.css',
            array('ig-base'),
            IPSIT_IG_VERSION
        );
        
        // Utilities
        wp_enqueue_style(
            'ig-utilities',
            IPSIT_IG_PLUGIN_URL . 'assets/css/utilities.css',
            array('ig-components'),
            IPSIT_IG_VERSION
        );
        
        // Main admin styles (imports all above, but we're loading separately for better control)
        wp_enqueue_style(
            'ig-admin-style',
            IPSIT_IG_PLUGIN_URL . 'assets/css/admin.css',
            array('ig-utilities'),
            IPSIT_IG_VERSION
        );
        
        // Inject custom CSS variables from settings as inline style
        $this->inject_custom_css_variables('ig-admin-style');
        
        // Enqueue JavaScript
        wp_enqueue_script(
            'ig-admin-script',
            IPSIT_IG_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery'),
            IPSIT_IG_VERSION,
            true
        );
        
        // Localize script for AJAX
        wp_localize_script('ig-admin-script', 'igAdmin', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('ig_admin_nonce'),
            'strings' => array(
                'confirmDelete' => __('Are you sure you want to delete this item?', 'ipsit-invoice-generator'),
                'saving' => __('Saving...', 'ipsit-invoice-generator'),
                'saved' => __('Saved successfully!', 'ipsit-invoice-generator'),
                'error' => __('An error occurred. Please try again.', 'ipsit-invoice-generator'),
            ),
        ));
        
        // Enqueue specific page assets
        $screen = get_current_screen();
        if ($screen) {
            $screen_id = $screen->id;
            
            // Template builder assets
            // phpcs:disable WordPress.Security.NonceVerification.Recommended -- GET parameter used for navigation/routing only
            if (strpos($screen_id, 'ipsit-ig-templates') !== false && isset($_GET['action']) && $_GET['action'] === 'builder') {
                wp_enqueue_script(
                    'ig-template-builder',
                    IPSIT_IG_PLUGIN_URL . 'assets/js/template-builder.js',
                    array('jquery', 'jquery-ui-sortable', 'jquery-ui-draggable', 'jquery-ui-droppable'),
                    IPSIT_IG_VERSION,
                    true
                );
                
                wp_enqueue_style(
                    'ig-template-builder-style',
                    IPSIT_IG_PLUGIN_URL . 'assets/css/template-builder.css',
                    array('ig-admin-style'),
                    IPSIT_IG_VERSION
                );
            }
            // phpcs:enable WordPress.Security.NonceVerification.Recommended
            
            // Invoice form assets
            // phpcs:disable WordPress.Security.NonceVerification.Recommended -- GET parameter used for navigation/routing only
            if (strpos($screen_id, 'ipsit-ig-invoices') !== false && (isset($_GET['action']) && ($_GET['action'] === 'add' || $_GET['action'] === 'edit'))) {
                wp_enqueue_script(
                    'ig-invoice-form',
                    IPSIT_IG_PLUGIN_URL . 'assets/js/invoice-form.js',
                    array('jquery'),
                    IPSIT_IG_VERSION,
                    true
                );
            }
            // phpcs:enable WordPress.Security.NonceVerification.Recommended
            
            // Client form assets
            // phpcs:disable WordPress.Security.NonceVerification.Recommended -- GET parameter used for navigation/routing only
            if (strpos($screen_id, 'ipsit-ig-clients') !== false && (isset($_GET['action']) && ($_GET['action'] === 'add' || $_GET['action'] === 'edit'))) {
                wp_enqueue_script(
                    'ig-clients',
                    IPSIT_IG_PLUGIN_URL . 'assets/js/clients.js',
                    array('jquery'),
                    IPSIT_IG_VERSION,
                    true
                );
            }
            // phpcs:enable WordPress.Security.NonceVerification.Recommended
            
            // Settings page assets
            if (strpos($screen_id, 'ipsit-ig-settings') !== false) {
                wp_enqueue_script(
                    'ig-settings',
                    IPSIT_IG_PLUGIN_URL . 'assets/js/settings.js',
                    array('jquery'),
                    IPSIT_IG_VERSION,
                    true
                );
            }
            
            // Company settings assets
            if (strpos($screen_id, 'ipsit-ig-company') !== false) {
                wp_enqueue_script(
                    'ig-settings',
                    IPSIT_IG_PLUGIN_URL . 'assets/js/settings.js',
                    array('jquery'),
                    IPSIT_IG_VERSION,
                    true
                );
            }
        }
    }
    
    /**
     * Inject custom CSS variables from settings
     */
    private function inject_custom_css_variables($handle = 'ig-admin-style') {
        $primary_color = get_option('ipsit_ig_design_primary_color', '');
        $secondary_color = get_option('ipsit_ig_design_secondary_color', '');
        $success_color = get_option('ipsit_ig_design_success_color', '');
        $error_color = get_option('ipsit_ig_design_error_color', '');
        $warning_color = get_option('ipsit_ig_design_warning_color', '');
        
        if (empty($primary_color) && empty($secondary_color) && empty($success_color) && empty($error_color) && empty($warning_color)) {
            return; // No custom colors set
        }
        
        $css = ':root {';
        
        if (!empty($primary_color)) {
            $css .= '--ig-color-primary: ' . esc_attr($primary_color) . ';';
            // Calculate darker shade (reduce lightness by 10%)
            $css .= '--ig-color-primary-dark: ' . esc_attr($this->darken_color($primary_color, 10)) . ';';
        }
        
        if (!empty($secondary_color)) {
            $css .= '--ig-color-secondary: ' . esc_attr($secondary_color) . ';';
        }
        
        if (!empty($success_color)) {
            $css .= '--ig-color-success: ' . esc_attr($success_color) . ';';
        }
        
        if (!empty($error_color)) {
            $css .= '--ig-color-error: ' . esc_attr($error_color) . ';';
        }
        
        if (!empty($warning_color)) {
            $css .= '--ig-color-warning: ' . esc_attr($warning_color) . ';';
        }
        
        $css .= '}';
        
        // Use WordPress's wp_add_inline_style instead of echo
        wp_add_inline_style($handle, $css);
    }
    
    /**
     * Darken a hex color by a percentage
     */
    private function darken_color($hex, $percent) {
        $hex = str_replace('#', '', $hex);
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        
        $r = (int) max(0, min(255, $r - ($r * $percent / 100)));
        $g = (int) max(0, min(255, $g - ($g * $percent / 100)));
        $b = (int) max(0, min(255, $b - ($b * $percent / 100)));
        
        return '#' . str_pad(dechex($r), 2, '0', STR_PAD_LEFT) . 
                   str_pad(dechex($g), 2, '0', STR_PAD_LEFT) . 
                   str_pad(dechex($b), 2, '0', STR_PAD_LEFT);
    }
}

