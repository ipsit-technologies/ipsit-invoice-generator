<?php
/**
 * Plugin Name: IPSIT Invoice Generator
 * Plugin URI: https://ipsittechnologies.com/
 * Description: A professional WordPress plugin for creating, managing, and sending invoices with PDF generation.
 * Version: 1.0.0
 * Author: Ipsit Technologies
 * Author URI: https://ipsittechnologies.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: ipsit-invoice-generator
 * Domain Path: /languages
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('IPSIT_IG_VERSION', '1.0.0');
define('IPSIT_IG_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('IPSIT_IG_PLUGIN_URL', plugin_dir_url(__FILE__));
define('IPSIT_IG_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Include Composer autoloader
require_once IPSIT_IG_PLUGIN_DIR . 'vendor/autoload.php';

/**
 * Main Plugin Class
 */
class IPSIT_Invoice_Generator {
    
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
        $this->init();
    }
    
    /**
     * Initialize plugin
     */
    private function init() {
        // Load plugin textdomain
        add_action('plugins_loaded', array($this, 'load_textdomain'));
        
        // Include required files
        $this->includes();
        
        // Initialize classes
        $this->init_classes();
        
        // Register activation and deactivation hooks
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }
    
    /**
     * Include required files
     */
    private function includes() {
        // Core utilities
        require_once IPSIT_IG_PLUGIN_DIR . 'includes/class-ig-config.php';
        require_once IPSIT_IG_PLUGIN_DIR . 'includes/class-ig-logger.php';
        require_once IPSIT_IG_PLUGIN_DIR . 'includes/class-ig-helper.php';
        require_once IPSIT_IG_PLUGIN_DIR . 'includes/class-ig-validator.php';
        
        // Main classes
        require_once IPSIT_IG_PLUGIN_DIR . 'includes/class-ig-database.php';
        require_once IPSIT_IG_PLUGIN_DIR . 'includes/class-ig-admin.php';
        require_once IPSIT_IG_PLUGIN_DIR . 'includes/class-ig-assets.php';
        require_once IPSIT_IG_PLUGIN_DIR . 'includes/class-ig-template-engine.php';
        require_once IPSIT_IG_PLUGIN_DIR . 'includes/class-ig-pdf.php';
        require_once IPSIT_IG_PLUGIN_DIR . 'includes/class-ig-email.php';
        require_once IPSIT_IG_PLUGIN_DIR . 'includes/class-ig-ajax.php';
    }
    
    /**
     * Initialize classes
     */
    private function init_classes() {
        IG_Database::get_instance();
        IG_Admin::get_instance();
        IG_Assets::get_instance();
        IG_Template_Engine::get_instance();
        IG_PDF::get_instance();
        IG_Email::get_instance();
        IG_Ajax::get_instance();
        
        // Handle PDF download
        add_action('admin_init', array($this, 'handle_pdf_download'));
        
        // Handle template reload
        add_action('admin_init', array($this, 'handle_template_reload'));
    }
    
    /**
     * Handle template reload
     */
    public function handle_template_reload() {
        if (isset($_GET['ipsit_ig_reload_templates']) && current_user_can('manage_options')) {
            check_admin_referer('ipsit_ig_reload_templates');
            $this->load_prebuilt_templates(true);
            wp_safe_redirect(admin_url('admin.php?page=ipsit-ig-templates&templates_reloaded=1'));
            exit;
        }
    }
    
    /**
     * Handle PDF download
     */
    public function handle_pdf_download() {
        if (isset($_GET['ipsit_ig_download_pdf']) && isset($_GET['invoice_id']) && current_user_can('manage_options')) {
            check_admin_referer('ipsit_ig_download_pdf', '_wpnonce');
            $invoice_id = intval(wp_unslash($_GET['invoice_id']));
            
            // Get template_id from URL or use invoice's saved template_id
            $template_id = null;
            if (isset($_GET['template_id']) && !empty($_GET['template_id'])) {
                $template_id = sanitize_text_field(wp_unslash($_GET['template_id']));
                // Convert numeric strings to int for database templates
                if (is_numeric($template_id)) {
                    $template_id = intval($template_id);
                }
            } else {
                // Get template_id from invoice record
                $db = IG_Database::get_instance();
                $invoice = $db->get_invoice($invoice_id);
                if ($invoice && !empty($invoice->template_id)) {
                    $template_id = $invoice->template_id;
                    // Convert numeric strings to int for database templates
                    if (is_numeric($template_id)) {
                        $template_id = intval($template_id);
                    }
                }
            }
            
            if ($invoice_id > 0) {
                $pdf = IG_PDF::get_instance();
                $pdf->download($invoice_id, $template_id);
            }
        }
    }
    
    /**
     * Load plugin textdomain
     * Note: When hosted on WordPress.org, translations are automatically loaded.
     * This is kept for local development only.
     */
    public function load_textdomain() {
        // Not needed for WordPress.org - WordPress automatically loads translations
        // load_plugin_textdomain('ipsit-invoice-generator', false, dirname(IPSIT_IG_PLUGIN_BASENAME) . '/languages');
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        // Create database tables
        IG_Database::get_instance()->create_tables();
        
        // Set default options
        $this->set_default_options();
        
        // Add custom capabilities
        $this->add_capabilities();
        
        // Load pre-built templates
        $this->load_prebuilt_templates();
        
        // Flush rewrite rules
        flush_rewrite_rules();
        
        IG_Logger::info('Plugin activated successfully');
    }
    
    /**
     * Load pre-built templates into database
     */
    private function load_prebuilt_templates($force_reload = false) {
        $db = IG_Database::get_instance();
        
        // Check if templates already exist (unless forcing reload)
        if (!$force_reload) {
            $existing_templates = $db->get_templates('prebuilt');
            // Check if we have all 3 pre-built templates
            $template_names = array('Default Template', 'Modern Minimal', 'Classic Professional', 'Project Based');
            $found_count = 0;
            foreach ($existing_templates as $t) {
                if (in_array($t->name, $template_names)) {
                    $found_count++;
                }
            }
            if ($found_count >= 4) {
                return; // All templates already loaded
            }
        } else {
            // Delete existing pre-built templates before reloading
            $existing_templates = $db->get_templates('prebuilt');
            foreach ($existing_templates as $t) {
                if (in_array($t->name, array('Default Template', 'Modern Minimal', 'Classic Professional', 'Project Based'))) {
                    $db->delete_template($t->id);
                }
            }
        }
        
        $templates_dir = IPSIT_IG_PLUGIN_DIR . 'templates/';
        
        // Load template-default.php
        $default_template_file = $templates_dir . 'template-default.php';
        if (file_exists($default_template_file)) {
            $default_content = file_get_contents($default_template_file);
            // Extract CSS and HTML body content
            preg_match('/<style>(.*?)<\/style>/s', $default_content, $css_matches);
            preg_match('/<body>(.*?)<\/body>/s', $default_content, $html_matches);
            
            $css = isset($css_matches[1]) ? trim($css_matches[1]) : '';
            $html = isset($html_matches[1]) ? trim($html_matches[1]) : '';
            
            if (!empty($html)) {
                // Check if template already exists
                $existing = $db->get_templates();
                $exists = false;
                foreach ($existing as $t) {
                    if ($t->name === 'Default Template' && $t->type === 'prebuilt') {
                        $db->update_template($t->id, array(
                            'html_content' => $html,
                            'css_content' => $css,
                            'is_default' => 1,
                        ));
                        $exists = true;
                        break;
                    }
                }
                if (!$exists) {
                    $db->insert_template(array(
                        'name' => 'Default Template',
                        'type' => 'prebuilt',
                        'html_content' => $html,
                        'css_content' => $css,
                        'is_default' => 1,
                    ));
                }
            }
        }
        
        // Load template-1.php
        $template1_file = $templates_dir . 'template-1.php';
        if (file_exists($template1_file)) {
            $template1_content = file_get_contents($template1_file);
            preg_match('/<style>(.*?)<\/style>/s', $template1_content, $css_matches);
            preg_match('/<body>(.*?)<\/body>/s', $template1_content, $html_matches);
            
            $css = isset($css_matches[1]) ? trim($css_matches[1]) : '';
            $html = isset($html_matches[1]) ? trim($html_matches[1]) : '';
            
            if (!empty($html)) {
                $existing = $db->get_templates();
                $exists = false;
                foreach ($existing as $t) {
                    if ($t->name === 'Modern Minimal' && $t->type === 'prebuilt') {
                        $db->update_template($t->id, array(
                            'html_content' => $html,
                            'css_content' => $css,
                        ));
                        $exists = true;
                        break;
                    }
                }
                if (!$exists) {
                    $db->insert_template(array(
                        'name' => 'Modern Minimal',
                        'type' => 'prebuilt',
                        'html_content' => $html,
                        'css_content' => $css,
                        'is_default' => 0,
                    ));
                }
            }
        }
        
        // Load template-2.php
        $template2_file = $templates_dir . 'template-2.php';
        if (file_exists($template2_file)) {
            $template2_content = file_get_contents($template2_file);
            preg_match('/<style>(.*?)<\/style>/s', $template2_content, $css_matches);
            preg_match('/<body>(.*?)<\/body>/s', $template2_content, $html_matches);
            
            $css = isset($css_matches[1]) ? trim($css_matches[1]) : '';
            $html = isset($html_matches[1]) ? trim($html_matches[1]) : '';
            
            if (!empty($html)) {
                $existing = $db->get_templates();
                $exists = false;
                foreach ($existing as $t) {
                    if ($t->name === 'Classic Professional' && $t->type === 'prebuilt') {
                        $db->update_template($t->id, array(
                            'html_content' => $html,
                            'css_content' => $css,
                        ));
                        $exists = true;
                        break;
                    }
                }
                if (!$exists) {
                    $db->insert_template(array(
                        'name' => 'Classic Professional',
                        'type' => 'prebuilt',
                        'html_content' => $html,
                        'css_content' => $css,
                        'is_default' => 0,
                    ));
                }
            }
        }
        
        // Load template-project.php
        $template_project_file = $templates_dir . 'template-project.php';
        if (file_exists($template_project_file)) {
            $template_project_content = file_get_contents($template_project_file);
            preg_match('/<style>(.*?)<\/style>/s', $template_project_content, $css_matches);
            preg_match('/<body>(.*?)<\/body>/s', $template_project_content, $html_matches);
            
            $css = isset($css_matches[1]) ? trim($css_matches[1]) : '';
            $html = isset($html_matches[1]) ? trim($html_matches[1]) : '';
            
            if (!empty($html)) {
                $existing = $db->get_templates();
                $exists = false;
                foreach ($existing as $t) {
                    if ($t->name === 'Project Based' && $t->type === 'prebuilt') {
                        $db->update_template($t->id, array(
                            'html_content' => $html,
                            'css_content' => $css,
                        ));
                        $exists = true;
                        break;
                    }
                }
                if (!$exists) {
                    $db->insert_template(array(
                        'name' => 'Project Based',
                        'type' => 'prebuilt',
                        'html_content' => $html,
                        'css_content' => $css,
                        'is_default' => 0,
                    ));
                }
            }
        }
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Clear all caches
        IG_Helper::clear_all_caches();
        
        // Flush rewrite rules
        flush_rewrite_rules();
        
        IG_Logger::info('Plugin deactivated');
    }
    
    /**
     * Add custom capabilities
     */
    private function add_capabilities() {
        $role = get_role('administrator');
        
        if ($role) {
            $role->add_cap(IG_Config::CAP_MANAGE_INVOICES);
            $role->add_cap(IG_Config::CAP_MANAGE_CLIENTS);
            $role->add_cap(IG_Config::CAP_MANAGE_TEMPLATES);
            $role->add_cap(IG_Config::CAP_MANAGE_SETTINGS);
        }
        
        // You can add capabilities to other roles here if needed
        $editor = get_role('editor');
        if ($editor) {
            $editor->add_cap(IG_Config::CAP_MANAGE_INVOICES);
            $editor->add_cap(IG_Config::CAP_MANAGE_CLIENTS);
        }
    }
    
    /**
     * Set default options
     */
    private function set_default_options() {
        $defaults = array(
            'ipsit_ig_invoice_number_prefix' => 'INV-',
            'ipsit_ig_invoice_number_suffix' => '',
            'ipsit_ig_invoice_number_padding' => 4,
            'ipsit_ig_currency' => 'USD',
            'ipsit_ig_currency_symbol' => '$',
            'ipsit_ig_date_format' => 'Y-m-d',
            'ipsit_ig_email_from_name' => get_bloginfo('name'),
            'ipsit_ig_email_from_email' => get_option('admin_email'),
            'ipsit_ig_email_subject' => 'Invoice #{invoice_number}',
        );
        
        foreach ($defaults as $key => $value) {
            if (get_option($key) === false) {
                add_option($key, $value);
            }
        }
    }
}

// Initialize the plugin
IPSIT_Invoice_Generator::get_instance();

