<?php
/**
 * Database Operations Class
 *
 * Handles all database operations for the Invoice Generator plugin
 */

if (!defined('ABSPATH')) {
    exit;
}

class IG_Database {
    
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
        // Constructor is private to prevent direct instantiation
    }
    
    /**
     * Create all database tables
     */
    public function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Table: wp_ipsit_ig_invoices
        $table_invoices = $wpdb->prefix . 'ipsit_ig_invoices';
        $sql_invoices = "CREATE TABLE IF NOT EXISTS $table_invoices (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            invoice_number varchar(100) NOT NULL,
            client_id bigint(20) NOT NULL,
            invoice_date date NOT NULL,
            due_date date DEFAULT NULL,
            status varchar(50) DEFAULT 'draft',
            items longtext NOT NULL,
            subtotal decimal(10,2) DEFAULT 0.00,
            tax decimal(10,2) DEFAULT 0.00,
            total decimal(10,2) DEFAULT 0.00,
            notes text DEFAULT NULL,
            template_id varchar(100) DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY invoice_number (invoice_number),
            KEY client_id (client_id),
            KEY status (status)
        ) $charset_collate;";
        
        // Table: wp_ipsit_ig_clients
        $table_clients = $wpdb->prefix . 'ipsit_ig_clients';
        $sql_clients = "CREATE TABLE IF NOT EXISTS $table_clients (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            email varchar(255) DEFAULT NULL,
            phone varchar(50) DEFAULT NULL,
            address text DEFAULT NULL,
            city varchar(100) DEFAULT NULL,
            state varchar(100) DEFAULT NULL,
            zip varchar(20) DEFAULT NULL,
            country varchar(100) DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY email (email)
        ) $charset_collate;";
        
        // Table: wp_ipsit_ig_company
        $table_company = $wpdb->prefix . 'ipsit_ig_company';
        $sql_company = "CREATE TABLE IF NOT EXISTS $table_company (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            email varchar(255) DEFAULT NULL,
            phone varchar(50) DEFAULT NULL,
            address text DEFAULT NULL,
            city varchar(100) DEFAULT NULL,
            state varchar(100) DEFAULT NULL,
            zip varchar(20) DEFAULT NULL,
            country varchar(100) DEFAULT NULL,
            logo varchar(255) DEFAULT NULL,
            tax_id varchar(100) DEFAULT NULL,
            website varchar(255) DEFAULT NULL,
            settings longtext DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";
        
        // Table: wp_ipsit_ig_templates
        $table_templates = $wpdb->prefix . 'ipsit_ig_templates';
        $sql_templates = "CREATE TABLE IF NOT EXISTS $table_templates (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            type varchar(50) DEFAULT 'custom',
            html_content longtext NOT NULL,
            css_content longtext DEFAULT NULL,
            settings longtext DEFAULT NULL,
            is_default tinyint(1) DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY type (type),
            KEY is_default (is_default)
        ) $charset_collate;";
        
        // Add settings column if it doesn't exist
        $this->add_template_settings_column();
        
        // Table: wp_ipsit_ig_client_fields
        $table_client_fields = $wpdb->prefix . 'ipsit_ig_client_fields';
        $sql_client_fields = "CREATE TABLE IF NOT EXISTS $table_client_fields (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            client_id bigint(20) NOT NULL,
            field_name varchar(255) NOT NULL,
            field_type varchar(50) NOT NULL,
            field_value longtext DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY client_id (client_id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_invoices);
        dbDelta($sql_clients);
        dbDelta($sql_company);
        dbDelta($sql_templates);
        dbDelta($sql_client_fields);
        
        // Add payment method columns if they don't exist
        $this->add_payment_method_columns();
        
        // Add payment method columns to company table if they don't exist
        $this->add_company_payment_method_columns();
    }
    
    /**
     * Add payment method columns to company table
     */
    private function add_company_payment_method_columns() {
        global $wpdb;
        $table = $wpdb->prefix . 'ipsit_ig_company';
        
        $columns = array(
            'default_payment_method' => 'varchar(50) DEFAULT NULL',
            'default_bank_name' => 'varchar(255) DEFAULT NULL',
            'default_account_number' => 'varchar(100) DEFAULT NULL',
            'default_account_title' => 'varchar(255) DEFAULT NULL',
            'default_account_branch' => 'varchar(255) DEFAULT NULL',
            'default_iban' => 'varchar(100) DEFAULT NULL',
            'default_ifsc_code' => 'varchar(50) DEFAULT NULL',
        );
        
        foreach ($columns as $column => $definition) {
            $column_exists = $wpdb->get_results($wpdb->prepare(
                "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS 
                WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = %s",
                DB_NAME, $table, $column
            ));
            
            if (empty($column_exists)) {
                $wpdb->query("ALTER TABLE $table ADD COLUMN $column $definition");
            }
        }
    }
    
    /**
     * Add template settings column if it doesn't exist
     */
    private function add_template_settings_column() {
        global $wpdb;
        $table = $wpdb->prefix . 'ipsit_ig_templates';
        
        // Check if column exists using a simpler query
        $column_exists = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = 'settings'",
            DB_NAME, $table
        ));
        
        if ($column_exists == 0) {
            $result = $wpdb->query("ALTER TABLE `$table` ADD COLUMN `settings` longtext DEFAULT NULL");
            if ($result === false) {
                // Log error but don't break
                error_log('Failed to add settings column to ' . $table . ': ' . $wpdb->last_error);
            }
        }
    }
    
    /**
     * Ensure template settings column exists (public method for admin_init hook)
     */
    public function ensure_template_settings_column() {
        global $wpdb;
        $table = $wpdb->prefix . 'ipsit_ig_templates';
        
        // Check if column exists using a simpler query
        $column_exists = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = 'settings'",
            DB_NAME, $table
        ));
        
        if ($column_exists == 0) {
            $result = $wpdb->query("ALTER TABLE `$table` ADD COLUMN `settings` longtext DEFAULT NULL");
            if ($result !== false) {
                // Column added successfully
                return true;
            } else {
                // Log error
                error_log('Failed to add settings column to ' . $table . ': ' . $wpdb->last_error);
                return false;
            }
        }
        return true;
    }
    
    /**
     * Add payment method columns to invoices table
     */
    private function add_payment_method_columns() {
        global $wpdb;
        $table = $wpdb->prefix . 'ipsit_ig_invoices';
        
        $columns = array(
            'payment_method' => 'varchar(50) DEFAULT NULL',
            'bank_name' => 'varchar(255) DEFAULT NULL',
            'account_number' => 'varchar(100) DEFAULT NULL',
            'account_title' => 'varchar(255) DEFAULT NULL',
            'account_branch' => 'varchar(255) DEFAULT NULL',
            'iban' => 'varchar(100) DEFAULT NULL',
            'ifsc_code' => 'varchar(50) DEFAULT NULL',
        );
        
        foreach ($columns as $column => $definition) {
            $column_exists = $wpdb->get_results($wpdb->prepare(
                "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS 
                WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = %s",
                DB_NAME, $table, $column
            ));
            
            if (empty($column_exists)) {
                $wpdb->query("ALTER TABLE $table ADD COLUMN $column $definition");
            }
        }
    }
    
    /**
     * Get invoice by ID
     */
    public function get_invoice($id) {
        global $wpdb;
        $table = $wpdb->prefix . 'ipsit_ig_invoices';
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $id));
    }
    
    /**
     * Get all invoices
     */
    public function get_invoices($limit = 20, $offset = 0, $status = '') {
        global $wpdb;
        $table = $wpdb->prefix . 'ipsit_ig_invoices';
        
        $where = '';
        if (!empty($status)) {
            $where = $wpdb->prepare(" WHERE status = %s", $status);
        }
        
        return $wpdb->get_results("SELECT * FROM $table $where ORDER BY created_at DESC LIMIT $limit OFFSET $offset");
    }
    
    /**
     * Insert invoice
     */
    public function insert_invoice($data) {
        global $wpdb;
        $table = $wpdb->prefix . 'ipsit_ig_invoices';
        
        $defaults = array(
            'invoice_number' => '',
            'client_id' => 0,
            'invoice_date' => current_time('mysql'),
            'due_date' => null,
            'status' => 'draft',
            'items' => '[]',
            'subtotal' => 0,
            'tax' => 0,
            'total' => 0,
            'notes' => '',
            'template_id' => null,
        );
        
        $data = wp_parse_args($data, $defaults);
        
        $wpdb->insert($table, $data);
        return $wpdb->insert_id;
    }
    
    /**
     * Update invoice
     */
    public function update_invoice($id, $data) {
        global $wpdb;
        $table = $wpdb->prefix . 'ipsit_ig_invoices';
        return $wpdb->update($table, $data, array('id' => $id));
    }
    
    /**
     * Delete invoice
     */
    public function delete_invoice($id) {
        global $wpdb;
        $table = $wpdb->prefix . 'ipsit_ig_invoices';
        return $wpdb->delete($table, array('id' => $id));
    }
    
    /**
     * Get client by ID
     */
    public function get_client($id) {
        global $wpdb;
        $table = $wpdb->prefix . 'ipsit_ig_clients';
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $id));
    }
    
    /**
     * Get all clients
     */
    public function get_clients($limit = 100, $offset = 0) {
        global $wpdb;
        $table = $wpdb->prefix . 'ipsit_ig_clients';
        return $wpdb->get_results("SELECT * FROM $table ORDER BY name ASC LIMIT $limit OFFSET $offset");
    }
    
    /**
     * Insert client
     */
    public function insert_client($data) {
        global $wpdb;
        $table = $wpdb->prefix . 'ipsit_ig_clients';
        
        $defaults = array(
            'name' => '',
            'email' => '',
            'phone' => '',
            'address' => '',
            'city' => '',
            'state' => '',
            'zip' => '',
            'country' => '',
        );
        
        $data = wp_parse_args($data, $defaults);
        
        $wpdb->insert($table, $data);
        return $wpdb->insert_id;
    }
    
    /**
     * Update client
     */
    public function update_client($id, $data) {
        global $wpdb;
        $table = $wpdb->prefix . 'ipsit_ig_clients';
        return $wpdb->update($table, $data, array('id' => $id));
    }
    
    /**
     * Delete client
     */
    public function delete_client($id) {
        global $wpdb;
        $table = $wpdb->prefix . 'ipsit_ig_clients';
        return $wpdb->delete($table, array('id' => $id));
    }
    
    /**
     * Get company details
     */
    public function get_company() {
        global $wpdb;
        $table = $wpdb->prefix . 'ipsit_ig_company';
        $company = $wpdb->get_row("SELECT * FROM $table LIMIT 1");
        
        if (!$company) {
            // Create default company record
            $wpdb->insert($table, array('name' => get_bloginfo('name')));
            $company = $wpdb->get_row("SELECT * FROM $table LIMIT 1");
        }
        
        return $company;
    }
    
    /**
     * Update company details
     */
    public function update_company($data) {
        global $wpdb;
        $table = $wpdb->prefix . 'ipsit_ig_company';
        
        $existing = $wpdb->get_row("SELECT * FROM $table LIMIT 1");
        
        if ($existing) {
            return $wpdb->update($table, $data, array('id' => $existing->id));
        } else {
            $wpdb->insert($table, $data);
            return $wpdb->insert_id;
        }
    }
    
    /**
     * Get template by ID
     */
    public function get_template($id) {
        global $wpdb;
        $table = $wpdb->prefix . 'ipsit_ig_templates';
        
        // Ensure settings column exists before querying
        $this->ensure_template_settings_column();
        
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $id));
    }
    
    /**
     * Get all templates
     */
    public function get_templates($type = '') {
        global $wpdb;
        $table = $wpdb->prefix . 'ipsit_ig_templates';
        
        $where = '';
        if (!empty($type)) {
            $where = $wpdb->prepare(" WHERE type = %s", $type);
        }
        
        return $wpdb->get_results("SELECT * FROM $table $where ORDER BY is_default DESC, name ASC");
    }
    
    /**
     * Insert template
     */
    public function insert_template($data) {
        global $wpdb;
        $table = $wpdb->prefix . 'ipsit_ig_templates';
        
        $defaults = array(
            'name' => '',
            'type' => 'custom',
            'html_content' => '',
            'css_content' => '',
            'is_default' => 0,
        );
        
        $data = wp_parse_args($data, $defaults);
        
        $wpdb->insert($table, $data);
        return $wpdb->insert_id;
    }
    
    /**
     * Update template
     */
    public function update_template($id, $data) {
        global $wpdb;
        $table = $wpdb->prefix . 'ipsit_ig_templates';
        return $wpdb->update($table, $data, array('id' => $id));
    }
    
    /**
     * Delete template
     */
    public function delete_template($id) {
        global $wpdb;
        $table = $wpdb->prefix . 'ipsit_ig_templates';
        return $wpdb->delete($table, array('id' => $id));
    }
    
    /**
     * Get client custom fields
     */
    public function get_client_fields($client_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'ipsit_ig_client_fields';
        return $wpdb->get_results($wpdb->prepare("SELECT * FROM $table WHERE client_id = %d", $client_id));
    }
    
    /**
     * Insert client field
     */
    public function insert_client_field($data) {
        global $wpdb;
        $table = $wpdb->prefix . 'ipsit_ig_client_fields';
        
        $defaults = array(
            'client_id' => 0,
            'field_name' => '',
            'field_type' => 'text',
            'field_value' => '',
        );
        
        $data = wp_parse_args($data, $defaults);
        
        $wpdb->insert($table, $data);
        return $wpdb->insert_id;
    }
    
    /**
     * Update client field
     */
    public function update_client_field($id, $data) {
        global $wpdb;
        $table = $wpdb->prefix . 'ipsit_ig_client_fields';
        return $wpdb->update($table, $data, array('id' => $id));
    }
    
    /**
     * Delete client field
     */
    public function delete_client_field($id) {
        global $wpdb;
        $table = $wpdb->prefix . 'ipsit_ig_client_fields';
        return $wpdb->delete($table, array('id' => $id));
    }
    
    /**
     * Delete all client fields for a client
     */
    public function delete_client_fields($client_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'ipsit_ig_client_fields';
        return $wpdb->delete($table, array('client_id' => $client_id));
    }
    
    /**
     * Get next invoice number
     */
    public function get_next_invoice_number() {
        global $wpdb;
        $table = $wpdb->prefix . 'ipsit_ig_invoices';
        
        $prefix = get_option('ipsit_ig_invoice_number_prefix', 'INV-');
        $suffix = get_option('ipsit_ig_invoice_number_suffix', '');
        $padding = get_option('ipsit_ig_invoice_number_padding', 4);
        
        // Get the last invoice number
        $last_invoice = $wpdb->get_var("SELECT invoice_number FROM $table ORDER BY id DESC LIMIT 1");
        
        $next_number = 1;
        
        if ($last_invoice) {
            // Extract number from last invoice
            // Format: Prefix-Suffix-Number (e.g., PSIT-2025-0001)
            // Try new format first (Prefix-Suffix-Number)
            if (!empty($suffix)) {
                $prefix_clean = rtrim($prefix, '-');
                $pattern_new = '/^' . preg_quote($prefix_clean, '/') . '-' . preg_quote($suffix, '/') . '-(\d+)$/';
                if (preg_match($pattern_new, $last_invoice, $matches)) {
                    $next_number = intval($matches[1]) + 1;
                } else {
                    // Try old format (Prefix-Number-Suffix) for backward compatibility
                    $pattern_old = '/^' . preg_quote($prefix, '/') . '(\d+)' . preg_quote($suffix, '/') . '$/';
                    if (preg_match($pattern_old, $last_invoice, $matches)) {
                        $next_number = intval($matches[1]) + 1;
                    }
                }
            } else {
                // No suffix, try Prefix-Number format
                $pattern = '/^' . preg_quote($prefix, '/') . '(\d+)$/';
                if (preg_match($pattern, $last_invoice, $matches)) {
                    $next_number = intval($matches[1]) + 1;
                }
            }
        }
        
        // Format: Prefix-Suffix-Number
        // If suffix exists, add separator between suffix and number
        if (!empty($suffix)) {
            // Ensure proper separator - prefix might already have dash
            $prefix_clean = rtrim($prefix, '-');
            return $prefix_clean . '-' . $suffix . '-' . str_pad($next_number, $padding, '0', STR_PAD_LEFT);
        } else {
            // If no suffix, just Prefix-Number
            return $prefix . str_pad($next_number, $padding, '0', STR_PAD_LEFT);
        }
    }
    
    /**
     * Get total invoice count
     */
    public function get_total_invoices_count($status = '') {
        global $wpdb;
        $table = $wpdb->prefix . 'ipsit_ig_invoices';
        
        $where = '';
        if (!empty($status)) {
            $where = $wpdb->prepare(" WHERE status = %s", $status);
        }
        
        return (int) $wpdb->get_var("SELECT COUNT(*) FROM $table $where");
    }
    
    /**
     * Get total clients count
     */
    public function get_total_clients_count() {
        global $wpdb;
        $table = $wpdb->prefix . 'ipsit_ig_clients';
        return (int) $wpdb->get_var("SELECT COUNT(*) FROM $table");
    }
    
    /**
     * Get total revenue (sum of all paid invoices)
     */
    public function get_total_revenue($period = 'all') {
        global $wpdb;
        $table = $wpdb->prefix . 'ipsit_ig_invoices';
        
        $where = " WHERE status = 'paid'";
        
        if ($period === 'month') {
            $where .= " AND MONTH(invoice_date) = MONTH(CURRENT_DATE()) AND YEAR(invoice_date) = YEAR(CURRENT_DATE())";
        } elseif ($period === 'today') {
            $where .= " AND DATE(invoice_date) = CURRENT_DATE()";
        }
        
        $total = $wpdb->get_var("SELECT COALESCE(SUM(total), 0) FROM $table $where");
        return floatval($total);
    }
    
    /**
     * Get pending invoices count (draft or sent status)
     */
    public function get_pending_invoices_count() {
        global $wpdb;
        $table = $wpdb->prefix . 'ipsit_ig_invoices';
        return (int) $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE status IN ('draft', 'sent')");
    }
    
    /**
     * Get today's invoices count
     */
    public function get_today_invoices_count() {
        global $wpdb;
        $table = $wpdb->prefix . 'ipsit_ig_invoices';
        return (int) $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE DATE(invoice_date) = CURRENT_DATE()");
    }
}

