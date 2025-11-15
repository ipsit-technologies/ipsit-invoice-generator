<?php
/**
 * Admin Interface Class
 *
 * Handles admin menu registration and page rendering
 */

if (!defined('ABSPATH')) {
    exit;
}

class IG_Admin {
    
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
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'handle_form_submissions'));
        add_action('admin_init', array($this, 'ensure_database_columns'));
    }
    
    /**
     * Ensure database columns exist
     */
    public function ensure_database_columns() {
        $db = IG_Database::get_instance();
        $db->ensure_template_settings_column();
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        $menu_slug = 'ipsit-invoice-generator';
        
        add_menu_page(
            __('IPSIT Invoice Generator', 'ipsit-invoice-generator'),
            __('IPSIT Invoice Generator', 'ipsit-invoice-generator'),
            'manage_options',
            $menu_slug,
            array($this, 'render_dashboard'),
            'dashicons-media-document',
            30
        );
        
        add_submenu_page(
            $menu_slug,
            __('Dashboard', 'ipsit-invoice-generator'),
            __('Dashboard', 'ipsit-invoice-generator'),
            'manage_options',
            $menu_slug,
            array($this, 'render_dashboard')
        );
        
        add_submenu_page(
            $menu_slug,
            __('Invoices', 'ipsit-invoice-generator'),
            __('Invoices', 'ipsit-invoice-generator'),
            'manage_options',
            'ipsit-ig-invoices',
            array($this, 'render_invoices_page')
        );
        
        add_submenu_page(
            $menu_slug,
            __('Clients', 'ipsit-invoice-generator'),
            __('Clients', 'ipsit-invoice-generator'),
            'manage_options',
            'ipsit-ig-clients',
            array($this, 'render_clients_page')
        );
        
        add_submenu_page(
            $menu_slug,
            __('Company Settings', 'ipsit-invoice-generator'),
            __('Company Settings', 'ipsit-invoice-generator'),
            'manage_options',
            'ipsit-ig-company',
            array($this, 'render_company_page')
        );
        
        add_submenu_page(
            $menu_slug,
            __('Templates', 'ipsit-invoice-generator'),
            __('Templates', 'ipsit-invoice-generator'),
            'manage_options',
            'ipsit-ig-templates',
            array($this, 'render_templates_page')
        );
        
        add_submenu_page(
            $menu_slug,
            __('Settings', 'ipsit-invoice-generator'),
            __('Settings', 'ipsit-invoice-generator'),
            'manage_options',
            'ipsit-ig-settings',
            array($this, 'render_settings_page')
        );
    }
    
    /**
     * Handle form submissions
     */
    public function handle_form_submissions() {
        // Handle non-AJAX form submissions if needed
    }
    
    /**
     * Render dashboard page
     */
    public function render_dashboard() {
        if (!file_exists(IPSIT_IG_PLUGIN_DIR . 'admin/views/dashboard.php')) {
            echo '<div class="wrap"><h1>' . esc_html__('Dashboard', 'ipsit-invoice-generator') . '</h1><p>Dashboard view coming soon...</p></div>';
            return;
        }
        include IPSIT_IG_PLUGIN_DIR . 'admin/views/dashboard.php';
    }
    
    /**
     * Render invoices page
     */
    public function render_invoices_page() {
        $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'list';
        $invoice_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        
        if ($action === 'edit' || $action === 'add') {
            if (!file_exists(IPSIT_IG_PLUGIN_DIR . 'admin/views/invoice-form.php')) {
                echo '<div class="wrap"><h1>' . esc_html__('Invoice Form', 'ipsit-invoice-generator') . '</h1><p>Invoice form view coming soon...</p></div>';
                return;
            }
            include IPSIT_IG_PLUGIN_DIR . 'admin/views/invoice-form.php';
        } else {
            if (!file_exists(IPSIT_IG_PLUGIN_DIR . 'admin/views/invoices-list.php')) {
                echo '<div class="wrap"><h1>' . esc_html__('Invoices', 'ipsit-invoice-generator') . '</h1><p>Invoices list view coming soon...</p></div>';
                return;
            }
            include IPSIT_IG_PLUGIN_DIR . 'admin/views/invoices-list.php';
        }
    }
    
    /**
     * Render clients page
     */
    public function render_clients_page() {
        $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'list';
        $client_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        
        if ($action === 'edit' || $action === 'add') {
            if (!file_exists(IPSIT_IG_PLUGIN_DIR . 'admin/views/client-form.php')) {
                echo '<div class="wrap"><h1>' . esc_html__('Client Form', 'ipsit-invoice-generator') . '</h1><p>Client form view coming soon...</p></div>';
                return;
            }
            include IPSIT_IG_PLUGIN_DIR . 'admin/views/client-form.php';
        } else {
            if (!file_exists(IPSIT_IG_PLUGIN_DIR . 'admin/views/clients-list.php')) {
                echo '<div class="wrap"><h1>' . esc_html__('Clients', 'ipsit-invoice-generator') . '</h1><p>Clients list view coming soon...</p></div>';
                return;
            }
            include IPSIT_IG_PLUGIN_DIR . 'admin/views/clients-list.php';
        }
    }
    
    /**
     * Render company page
     */
    public function render_company_page() {
        if (!file_exists(IPSIT_IG_PLUGIN_DIR . 'admin/views/company-settings.php')) {
            echo '<div class="wrap"><h1>' . esc_html__('Company Settings', 'ipsit-invoice-generator') . '</h1><p>Company settings view coming soon...</p></div>';
            return;
        }
        include IPSIT_IG_PLUGIN_DIR . 'admin/views/company-settings.php';
    }
    
    /**
     * Render templates page
     */
    public function render_templates_page() {
        $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'list';
        
        if ($action === 'builder') {
            if (!file_exists(IPSIT_IG_PLUGIN_DIR . 'admin/views/template-builder.php')) {
                echo '<div class="wrap"><h1>' . esc_html__('Template Builder', 'ipsit-invoice-generator') . '</h1><p>Template builder view coming soon...</p></div>';
                return;
            }
            include IPSIT_IG_PLUGIN_DIR . 'admin/views/template-builder.php';
        } else {
            if (!file_exists(IPSIT_IG_PLUGIN_DIR . 'admin/views/templates-list.php')) {
                echo '<div class="wrap"><h1>' . esc_html__('Templates', 'ipsit-invoice-generator') . '</h1><p>Templates list view coming soon...</p></div>';
                return;
            }
            include IPSIT_IG_PLUGIN_DIR . 'admin/views/templates-list.php';
        }
    }
    
    /**
     * Render settings page
     */
    public function render_settings_page() {
        if (!file_exists(IPSIT_IG_PLUGIN_DIR . 'admin/views/settings.php')) {
            echo '<div class="wrap"><h1>' . esc_html__('Settings', 'ipsit-invoice-generator') . '</h1><p>Settings view coming soon...</p></div>';
            return;
        }
        include IPSIT_IG_PLUGIN_DIR . 'admin/views/settings.php';
    }
}

