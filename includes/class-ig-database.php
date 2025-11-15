<?php
/**
 * Database Operations Class
 *
 * Handles all database operations with security, caching, and performance improvements
 */

// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery -- This class is designed to make direct database queries for custom tables
// phpcs:disable WordPress.DB.DirectDatabaseQuery.NoCaching -- Caching is handled at a higher level where appropriate
// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange -- Schema changes are necessary for table creation and updates
// phpcs:disable PluginCheck.Security.DirectDB.UnescapedDBParameter -- Table names use $wpdb->prefix (safe), column names are from hardcoded arrays, and all parameters are properly sanitized

if (!defined('ABSPATH')) {
    exit;
}

class IG_Database {
    
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
        // Private constructor to enforce singleton
    }
    
    /**
     * Create all database tables
     */
    public function create_tables(): void {
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
            payment_method varchar(50) DEFAULT NULL,
            bank_name varchar(255) DEFAULT NULL,
            account_number varchar(100) DEFAULT NULL,
            account_title varchar(255) DEFAULT NULL,
            account_branch varchar(255) DEFAULT NULL,
            iban varchar(100) DEFAULT NULL,
            ifsc_code varchar(50) DEFAULT NULL,
            created_by bigint(20) DEFAULT NULL,
            modified_by bigint(20) DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY invoice_number (invoice_number),
            KEY client_id (client_id),
            KEY status (status),
            KEY invoice_date (invoice_date),
            KEY created_by (created_by)
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
            KEY email (email),
            KEY name (name)
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
            default_payment_method varchar(50) DEFAULT NULL,
            default_bank_name varchar(255) DEFAULT NULL,
            default_account_number varchar(100) DEFAULT NULL,
            default_account_title varchar(255) DEFAULT NULL,
            default_account_branch varchar(255) DEFAULT NULL,
            default_iban varchar(100) DEFAULT NULL,
            default_ifsc_code varchar(50) DEFAULT NULL,
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
        
        // Suppress output during activation
        ob_start();
        $results = array();
        $results[] = dbDelta($sql_invoices);
        $results[] = dbDelta($sql_clients);
        $results[] = dbDelta($sql_company);
        $results[] = dbDelta($sql_templates);
        $results[] = dbDelta($sql_client_fields);
        ob_end_clean();
        
        IG_Logger::info('Database tables created/updated successfully');
    }
    
    /**
     * INVOICE METHODS
     */
    
    /**
     * Get invoice by ID
     */
    public function get_invoice(int $id): ?object {
        global $wpdb;
        $table = $wpdb->prefix . 'ipsit_ig_invoices';
        
        $result = $wpdb->get_row($wpdb->prepare(
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name is safe (using $wpdb->prefix)
            "SELECT * FROM $table WHERE id = %d",
            $id
        ));
        
        if ($wpdb->last_error) {
            IG_Logger::log_db_error('get_invoice', $wpdb);
        }
        
        return $result ?: null;
    }
    
    /**
     * Get invoices with optional client data (JOIN query)
     */
    public function get_invoices(int $limit = null, int $offset = 0, string $status = ''): array {
        global $wpdb;
        
        if ($limit === null) {
            $limit = IG_Config::DEFAULT_LIMIT;
        }
        
        // Enforce limits
        $limit = min($limit, IG_Config::MAX_LIMIT);
        $limit = max($limit, IG_Config::MIN_LIMIT);
        
        $invoices_table = $wpdb->prefix . 'ipsit_ig_invoices';
        $clients_table = $wpdb->prefix . 'ipsit_ig_clients';
        
        $sql = "SELECT 
                    i.*, 
                    c.name as client_name, 
                    c.email as client_email 
                FROM $invoices_table i 
                LEFT JOIN $clients_table c ON i.client_id = c.id";
        
        $where_conditions = array();
        $prepare_values = array();
        
        if (!empty($status)) {
            $where_conditions[] = "i.status = %s";
            $prepare_values[] = $status;
        }
        
        if (!empty($where_conditions)) {
            $sql .= " WHERE " . implode(' AND ', $where_conditions);
        }
        
        $sql .= " ORDER BY i.created_at DESC LIMIT %d OFFSET %d";
        $prepare_values[] = $limit;
        $prepare_values[] = $offset;
        
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Table names are safe, uses $wpdb->prefix
        $results = $wpdb->get_results($wpdb->prepare($sql, ...$prepare_values));
        
        if ($wpdb->last_error) {
            IG_Logger::log_db_error('get_invoices', $wpdb);
            return array();
        }
        
        return $results ?: array();
    }
    
    /**
     * Insert invoice
     */
    public function insert_invoice(array $data): int {
        global $wpdb;
        $table = $wpdb->prefix . 'ipsit_ig_invoices';
        
        // Add created_by
        $data['created_by'] = get_current_user_id();
        
        $wpdb->insert($table, $data);
        
        if ($wpdb->last_error) {
            IG_Logger::log_db_error('insert_invoice', $wpdb);
            return 0;
        }
        
        $invoice_id = $wpdb->insert_id;
        
        if ($invoice_id) {
            IG_Logger::info('Invoice created', array('invoice_id' => $invoice_id));
        }
        
        return $invoice_id;
    }
    
    /**
     * Update invoice
     */
    public function update_invoice(int $id, array $data): bool {
        global $wpdb;
        $table = $wpdb->prefix . 'ipsit_ig_invoices';
        
        // Add modified_by
        $data['modified_by'] = get_current_user_id();
        
        $result = $wpdb->update($table, $data, array('id' => $id));
        
        if ($wpdb->last_error) {
            IG_Logger::log_db_error('update_invoice', $wpdb);
            return false;
        }
        
        if ($result !== false) {
            IG_Logger::info('Invoice updated', array('invoice_id' => $id));
        }
        
        return $result !== false;
    }
    
    /**
     * Delete invoice
     */
    public function delete_invoice(int $id): bool {
        global $wpdb;
        $table = $wpdb->prefix . 'ipsit_ig_invoices';
        
        $result = $wpdb->delete($table, array('id' => $id), array('%d'));
        
        if ($wpdb->last_error) {
            IG_Logger::log_db_error('delete_invoice', $wpdb);
            return false;
        }
        
        if ($result !== false) {
            IG_Logger::info('Invoice deleted', array('invoice_id' => $id));
        }
        
        return $result !== false;
    }
    
    /**
     * CLIENT METHODS
     */
    
    /**
     * Get client by ID
     */
    public function get_client(int $id): ?object {
        global $wpdb;
        $table = $wpdb->prefix . 'ipsit_ig_clients';
        
        $result = $wpdb->get_row($wpdb->prepare(
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name is safe (using $wpdb->prefix)
            "SELECT * FROM $table WHERE id = %d",
            $id
        ));
        
        if ($wpdb->last_error) {
            IG_Logger::log_db_error('get_client', $wpdb);
        }
        
        return $result ?: null;
    }
    
    /**
     * Get all clients
     */
    public function get_clients(int $limit = null, int $offset = 0): array {
        global $wpdb;
        $table = $wpdb->prefix . 'ipsit_ig_clients';
        
        if ($limit === null) {
            $limit = IG_Config::MAX_LIMIT;
        }
        
        $results = $wpdb->get_results($wpdb->prepare(
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name is safe (using $wpdb->prefix)
            "SELECT * FROM $table ORDER BY name ASC LIMIT %d OFFSET %d",
            $limit,
            $offset
        ));
        
        if ($wpdb->last_error) {
            IG_Logger::log_db_error('get_clients', $wpdb);
            return array();
        }
        
        return $results ?: array();
    }
    
    /**
     * Insert client
     */
    public function insert_client(array $data): int {
        global $wpdb;
        $table = $wpdb->prefix . 'ipsit_ig_clients';
        
        $wpdb->insert($table, $data);
        
        if ($wpdb->last_error) {
            IG_Logger::log_db_error('insert_client', $wpdb);
            return 0;
        }
        
        $client_id = $wpdb->insert_id;
        
        if ($client_id) {
            IG_Logger::info('Client created', array('client_id' => $client_id));
        }
        
        return $client_id;
    }
    
    /**
     * Update client
     */
    public function update_client(int $id, array $data): bool {
        global $wpdb;
        $table = $wpdb->prefix . 'ipsit_ig_clients';
        
        $result = $wpdb->update($table, $data, array('id' => $id));
        
        if ($wpdb->last_error) {
            IG_Logger::log_db_error('update_client', $wpdb);
            return false;
        }
        
        if ($result !== false) {
            IG_Logger::info('Client updated', array('client_id' => $id));
        }
        
        return $result !== false;
    }
    
    /**
     * Delete client
     */
    public function delete_client(int $id): bool {
        global $wpdb;
        $table = $wpdb->prefix . 'ipsit_ig_clients';
        
        // Start transaction
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Transaction control is necessary for data integrity
        $wpdb->query('START TRANSACTION');
        
        try {
            // Delete client fields first
            $this->delete_client_fields($id);
            
            // Delete client
            $result = $wpdb->delete($table, array('id' => $id), array('%d'));
            
            if ($result === false) {
                throw new Exception('Failed to delete client');
            }
            
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Transaction control is necessary for data integrity
            $wpdb->query('COMMIT');
            
            IG_Logger::info('Client deleted', array('client_id' => $id));
            
            return true;
            
        } catch (Exception $e) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Transaction control is necessary for data integrity
            $wpdb->query('ROLLBACK');
            IG_Logger::error('Failed to delete client', array(
                'client_id' => $id,
                'error' => $e->getMessage()
            ));
            return false;
        }
    }
    
    /**
     * COMPANY METHODS
     */
    
    /**
     * Get company details (with caching)
     */
    public function get_company(): object {
        // Check cache first
        $company = IG_Helper::get_cache(IG_Config::CACHE_KEY_COMPANY);
        
        if ($company !== false) {
            return $company;
        }
        
        // Get from database
        global $wpdb;
        $table = $wpdb->prefix . 'ipsit_ig_company';
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name is safe (using $wpdb->prefix)
        $company = $wpdb->get_row("SELECT * FROM $table LIMIT 1");
        
        if (!$company) {
            // Create default company record
            $wpdb->insert($table, array('name' => get_bloginfo('name')));
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name is safe (using $wpdb->prefix)
            $company = $wpdb->get_row("SELECT * FROM $table LIMIT 1");
        }
        
        // Cache the result
        if ($company) {
            IG_Helper::set_cache(IG_Config::CACHE_KEY_COMPANY, $company, IG_Config::CACHE_COMPANY_DATA);
        }
        
        return $company;
    }
    
    /**
     * Update company details
     */
    public function update_company(array $data): bool {
        global $wpdb;
        $table = $wpdb->prefix . 'ipsit_ig_company';
        
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name is safe (using $wpdb->prefix)
        $existing = $wpdb->get_row("SELECT * FROM $table LIMIT 1");
        
        $result = false;
        if ($existing) {
            $result = $wpdb->update($table, $data, array('id' => $existing->id));
        } else {
            $wpdb->insert($table, $data);
            $result = $wpdb->insert_id;
        }
        
        if ($wpdb->last_error) {
            IG_Logger::log_db_error('update_company', $wpdb);
            return false;
        }
        
        // Clear cache
        IG_Helper::delete_cache(IG_Config::CACHE_KEY_COMPANY);
        
        IG_Logger::info('Company settings updated');
        
        return $result !== false;
    }
    
    /**
     * TEMPLATE METHODS
     */
    
    /**
     * Get template by ID
     */
    public function get_template(int $id): ?object {
        global $wpdb;
        $table = $wpdb->prefix . 'ipsit_ig_templates';
        
        $result = $wpdb->get_row($wpdb->prepare(
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name is safe (using $wpdb->prefix)
            "SELECT * FROM $table WHERE id = %d",
            $id
        ));
        
        if ($wpdb->last_error) {
            IG_Logger::log_db_error('get_template', $wpdb);
        }
        
        return $result ?: null;
    }
    
    /**
     * Get all templates (with caching)
     */
    public function get_templates(string $type = ''): array {
        // Check cache
        $cache_key = IG_Config::CACHE_KEY_TEMPLATES . ($type ? "_$type" : '');
        $templates = IG_Helper::get_cache($cache_key);
        
        if ($templates !== false) {
            return $templates;
        }
        
        global $wpdb;
        $table = $wpdb->prefix . 'ipsit_ig_templates';
        
        $sql = "SELECT * FROM $table";
        $prepare_values = array();
        
        if (!empty($type)) {
            $sql .= " WHERE type = %s";
            $prepare_values[] = $type;
        }
        
        $sql .= " ORDER BY is_default DESC, name ASC";
        
        if (!empty($prepare_values)) {
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Table name is safe, uses $wpdb->prefix
            $results = $wpdb->get_results($wpdb->prepare($sql, ...$prepare_values));
        } else {
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Table name is safe, uses $wpdb->prefix
            $results = $wpdb->get_results($wpdb->prepare($sql));
        }
        
        if ($wpdb->last_error) {
            IG_Logger::log_db_error('get_templates', $wpdb);
            return array();
        }
        
        $templates = $results ?: array();
        
        // Cache the results
        IG_Helper::set_cache($cache_key, $templates, IG_Config::CACHE_TEMPLATES);
        
        return $templates;
    }
    
    /**
     * Insert template
     */
    public function insert_template(array $data): int {
        global $wpdb;
        $table = $wpdb->prefix . 'ipsit_ig_templates';
        
        $wpdb->insert($table, $data);
        
        if ($wpdb->last_error) {
            IG_Logger::log_db_error('insert_template', $wpdb);
            return 0;
        }
        
        $template_id = $wpdb->insert_id;
        
        if ($template_id) {
            // Clear template cache
            $this->clear_template_cache();
            IG_Logger::info('Template created', array('template_id' => $template_id));
        }
        
        return $template_id;
    }
    
    /**
     * Update template
     */
    public function update_template(int $id, array $data): bool {
        global $wpdb;
        $table = $wpdb->prefix . 'ipsit_ig_templates';
        
        $result = $wpdb->update($table, $data, array('id' => $id));
        
        if ($wpdb->last_error) {
            IG_Logger::log_db_error('update_template', $wpdb);
            return false;
        }
        
        if ($result !== false) {
            // Clear template cache
            $this->clear_template_cache();
            IG_Logger::info('Template updated', array('template_id' => $id));
        }
        
        return $result !== false;
    }
    
    /**
     * Delete template
     */
    public function delete_template(int $id): bool {
        global $wpdb;
        $table = $wpdb->prefix . 'ipsit_ig_templates';
        
        $result = $wpdb->delete($table, array('id' => $id), array('%d'));
        
        if ($wpdb->last_error) {
            IG_Logger::log_db_error('delete_template', $wpdb);
            return false;
        }
        
        if ($result !== false) {
            // Clear template cache
            $this->clear_template_cache();
            IG_Logger::info('Template deleted', array('template_id' => $id));
        }
        
        return $result !== false;
    }
    
    /**
     * Clear template cache
     */
    private function clear_template_cache(): void {
        IG_Helper::delete_cache(IG_Config::CACHE_KEY_TEMPLATES);
        IG_Helper::delete_cache(IG_Config::CACHE_KEY_TEMPLATES . '_custom');
        IG_Helper::delete_cache(IG_Config::CACHE_KEY_TEMPLATES . '_prebuilt');
    }
    
    /**
     * CLIENT FIELDS METHODS
     */
    
    /**
     * Get client custom fields
     */
    public function get_client_fields(int $client_id): array {
        global $wpdb;
        $table = $wpdb->prefix . 'ipsit_ig_client_fields';
        
        $results = $wpdb->get_results($wpdb->prepare(
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name is safe (using $wpdb->prefix)
            "SELECT * FROM $table WHERE client_id = %d",
            $client_id
        ));
        
        if ($wpdb->last_error) {
            IG_Logger::log_db_error('get_client_fields', $wpdb);
            return array();
        }
        
        return $results ?: array();
    }
    
    /**
     * Insert client field
     */
    public function insert_client_field(array $data): int {
        global $wpdb;
        $table = $wpdb->prefix . 'ipsit_ig_client_fields';
        
        $wpdb->insert($table, $data);
        
        if ($wpdb->last_error) {
            IG_Logger::log_db_error('insert_client_field', $wpdb);
            return 0;
        }
        
        return $wpdb->insert_id;
    }
    
    /**
     * Update client field
     */
    public function update_client_field(int $id, array $data): bool {
        global $wpdb;
        $table = $wpdb->prefix . 'ipsit_ig_client_fields';
        
        $result = $wpdb->update($table, $data, array('id' => $id));
        
        if ($wpdb->last_error) {
            IG_Logger::log_db_error('update_client_field', $wpdb);
            return false;
        }
        
        return $result !== false;
    }
    
    /**
     * Delete client field
     */
    public function delete_client_field(int $id): bool {
        global $wpdb;
        $table = $wpdb->prefix . 'ipsit_ig_client_fields';
        
        $result = $wpdb->delete($table, array('id' => $id), array('%d'));
        
        if ($wpdb->last_error) {
            IG_Logger::log_db_error('delete_client_field', $wpdb);
            return false;
        }
        
        return $result !== false;
    }
    
    /**
     * Delete all client fields for a client
     */
    public function delete_client_fields(int $client_id): bool {
        global $wpdb;
        $table = $wpdb->prefix . 'ipsit_ig_client_fields';
        
        $result = $wpdb->delete($table, array('client_id' => $client_id), array('%d'));
        
        if ($wpdb->last_error) {
            IG_Logger::log_db_error('delete_client_fields', $wpdb);
            return false;
        }
        
        return $result !== false;
    }
    
    /**
     * STATISTICS METHODS
     */
    
    /**
     * Get next invoice number
     */
    public function get_next_invoice_number(): string {
        global $wpdb;
        $table = $wpdb->prefix . 'ipsit_ig_invoices';
        
        $prefix = get_option('ipsit_ig_invoice_number_prefix', 'INV-');
        $suffix = get_option('ipsit_ig_invoice_number_suffix', '');
        $padding = (int) get_option('ipsit_ig_invoice_number_padding', 4);
        
        // Get the last invoice number
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name is safe (using $wpdb->prefix)
        $last_invoice = $wpdb->get_var("SELECT invoice_number FROM $table ORDER BY id DESC LIMIT 1");
        
        $next_number = 1;
        
        if ($last_invoice) {
            if (!empty($suffix)) {
                $prefix_clean = rtrim($prefix, '-');
                $pattern_new = '/^' . preg_quote($prefix_clean, '/') . '-' . preg_quote($suffix, '/') . '-(\d+)$/';
                if (preg_match($pattern_new, $last_invoice, $matches)) {
                    $next_number = intval($matches[1]) + 1;
                } else {
                    $pattern_old = '/^' . preg_quote($prefix, '/') . '(\d+)' . preg_quote($suffix, '/') . '$/';
                    if (preg_match($pattern_old, $last_invoice, $matches)) {
                        $next_number = intval($matches[1]) + 1;
                    }
                }
            } else {
                $pattern = '/^' . preg_quote($prefix, '/') . '(\d+)$/';
                if (preg_match($pattern, $last_invoice, $matches)) {
                    $next_number = intval($matches[1]) + 1;
                }
            }
        }
        
        if (!empty($suffix)) {
            $prefix_clean = rtrim($prefix, '-');
            return $prefix_clean . '-' . $suffix . '-' . str_pad($next_number, $padding, '0', STR_PAD_LEFT);
        } else {
            return $prefix . str_pad($next_number, $padding, '0', STR_PAD_LEFT);
        }
    }
    
    /**
     * Get total invoice count
     */
    public function get_total_invoices_count(string $status = ''): int {
        global $wpdb;
        $table = $wpdb->prefix . 'ipsit_ig_invoices';
        
        if (!empty($status)) {
            $count = $wpdb->get_var($wpdb->prepare(
                // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name is safe (using $wpdb->prefix)
                "SELECT COUNT(*) FROM $table WHERE status = %s",
                $status
            ));
        } else {
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name is safe (using $wpdb->prefix)
            $count = $wpdb->get_var("SELECT COUNT(*) FROM $table");
        }
        
        return (int) $count;
    }
    
    /**
     * Get total clients count
     */
    public function get_total_clients_count(): int {
        global $wpdb;
        $table = $wpdb->prefix . 'ipsit_ig_clients';
        
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name is safe (using $wpdb->prefix)
        $count = $wpdb->get_var("SELECT COUNT(*) FROM $table");
        
        return (int) $count;
    }
    
    /**
     * Get total revenue
     */
    public function get_total_revenue(string $period = 'all'): float {
        global $wpdb;
        $table = $wpdb->prefix . 'ipsit_ig_invoices';
        
        $sql = "SELECT COALESCE(SUM(total), 0) FROM $table WHERE status = %s";
        $prepare_values = array('paid');
        
        if ($period === 'month') {
            $sql .= " AND MONTH(invoice_date) = MONTH(CURRENT_DATE()) AND YEAR(invoice_date) = YEAR(CURRENT_DATE())";
        } elseif ($period === 'today') {
            $sql .= " AND DATE(invoice_date) = CURRENT_DATE()";
        }
        
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Table name is safe, uses $wpdb->prefix
        $total = $wpdb->get_var($wpdb->prepare($sql, ...$prepare_values));
        
        return floatval($total);
    }
    
    /**
     * Get pending invoices count
     */
    public function get_pending_invoices_count(): int {
        global $wpdb;
        $table = $wpdb->prefix . 'ipsit_ig_invoices';
        
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name is safe (using $wpdb->prefix)
        $count = $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE status IN ('draft', 'sent')");
        
        return (int) $count;
    }
    
    /**
     * Get today's invoices count
     */
    public function get_today_invoices_count(): int {
        global $wpdb;
        $table = $wpdb->prefix . 'ipsit_ig_invoices';
        
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name is safe (using $wpdb->prefix)
        $count = $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE DATE(invoice_date) = CURRENT_DATE()");
        
        return (int) $count;
    }
    
    /**
     * HELPER METHODS
     */
    
    /**
     * Ensure template settings column exists
     */
    public function ensure_template_settings_column(): bool {
        global $wpdb;
        $table = $wpdb->prefix . 'ipsit_ig_templates';
        
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- INFORMATION_SCHEMA queries are necessary for column existence checks
        $column_exists = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = 'settings'",
            DB_NAME, $table
        ));
        
        if ($column_exists == 0) {
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange -- Table name is safe (using $wpdb->prefix), schema changes are necessary
            $result = $wpdb->query("ALTER TABLE `$table` ADD COLUMN `settings` longtext DEFAULT NULL");
            if ($result === false) {
                IG_Logger::error('Failed to add settings column to ' . $table, array('error' => $wpdb->last_error));
                return false;
            }
        }
        return true;
    }
}

