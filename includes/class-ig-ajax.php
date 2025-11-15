<?php
/**
 * AJAX Handlers Class
 *
 * Handles all AJAX requests for the plugin
 */

if (!defined('ABSPATH')) {
    exit;
}

class IG_Ajax {
    
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
        // Invoice AJAX handlers
        add_action('wp_ajax_ig_save_invoice', array($this, 'save_invoice'));
        add_action('wp_ajax_ig_delete_invoice', array($this, 'delete_invoice'));
        add_action('wp_ajax_ig_send_email', array($this, 'send_email'));
        
        // Client AJAX handlers
        add_action('wp_ajax_ig_save_client', array($this, 'save_client'));
        add_action('wp_ajax_ig_delete_client', array($this, 'delete_client'));
        add_action('wp_ajax_ig_save_client_field', array($this, 'save_client_field'));
        add_action('wp_ajax_ig_delete_client_field', array($this, 'delete_client_field'));
        
        // Company AJAX handlers
        add_action('wp_ajax_ig_save_company', array($this, 'save_company'));
        
        // Template AJAX handlers
        add_action('wp_ajax_ig_save_template', array($this, 'save_template'));
        add_action('wp_ajax_ig_delete_template', array($this, 'delete_template'));
        add_action('wp_ajax_ig_preview_template', array($this, 'preview_template'));
        
        // Settings AJAX handlers
        add_action('wp_ajax_ig_save_settings', array($this, 'save_settings'));
    }
    
    /**
     * Verify nonce and permissions
     */
    private function verify_nonce(string $required_capability = 'manage_options') {
        // Check nonce
        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Nonce is verified, not sanitized
        if (!isset($_POST['nonce']) || !wp_verify_nonce(wp_unslash($_POST['nonce']), 'ig_admin_nonce')) {
            IG_Logger::warning('Nonce verification failed', array(
                'user_id' => get_current_user_id(),
                'ip' => IG_Helper::get_user_ip()
            ));
            wp_send_json_error(array('message' => __('Security check failed.', 'ipsit-invoice-generator')));
        }
        
        // Check capability
        $has_permission = false;
        switch ($required_capability) {
            case IG_Config::CAP_MANAGE_INVOICES:
                $has_permission = IG_Helper::current_user_can_manage_invoices();
                break;
            case IG_Config::CAP_MANAGE_CLIENTS:
                $has_permission = IG_Helper::current_user_can_manage_clients();
                break;
            case IG_Config::CAP_MANAGE_TEMPLATES:
                $has_permission = IG_Helper::current_user_can_manage_templates();
                break;
            case IG_Config::CAP_MANAGE_SETTINGS:
                $has_permission = IG_Helper::current_user_can_manage_settings();
                break;
            default:
                $has_permission = current_user_can($required_capability);
        }
        
        if (!$has_permission) {
            IG_Logger::warning('Permission denied', array(
                'user_id' => get_current_user_id(),
                'required_cap' => $required_capability
            ));
            wp_send_json_error(array('message' => __('You do not have permission to perform this action.', 'ipsit-invoice-generator')));
        }
    }
    
    /**
     * Save invoice
     */
    public function save_invoice() {
        $this->verify_nonce(IG_Config::CAP_MANAGE_INVOICES);
        
        // phpcs:disable WordPress.Security.NonceVerification.Missing -- Nonce verified in verify_nonce() above
        
        // Rate limiting
        if (!IG_Helper::check_rate_limit('save_invoice')) {
            wp_send_json_error(array('message' => __('Too many requests. Please try again later.', 'ipsit-invoice-generator')));
        }
        
        $db = IG_Database::get_instance();
        
        $invoice_id = isset($_POST['invoice_id']) ? intval(wp_unslash($_POST['invoice_id'])) : 0;
        
        // Parse items first for validation
        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Array is unslashed and sanitized in loop
        $items = isset($_POST['items']) ? wp_unslash($_POST['items']) : array();
        $items_array = array();
        $subtotal = 0;
        
        if (is_array($items)) {
            foreach ($items as $item) {
                $description = isset($item['description']) ? sanitize_text_field(wp_unslash($item['description'])) : '';
                $quantity = isset($item['quantity']) ? floatval(wp_unslash($item['quantity'])) : 0;
                $price = isset($item['price']) ? floatval(wp_unslash($item['price'])) : 0;
                
                if (!empty($description)) {
                    $items_array[] = array(
                        'description' => $description,
                        'quantity' => $quantity,
                        'price' => $price,
                    );
                    $subtotal += $quantity * $price;
                }
            }
        }
        
        $tax_rate = isset($_POST['tax_rate']) ? floatval(wp_unslash($_POST['tax_rate'])) : 0;
        $tax = $subtotal * ($tax_rate / 100);
        $total = $subtotal + $tax;
        
        $template_id = isset($_POST['template_id']) ? sanitize_text_field(wp_unslash($_POST['template_id'])) : null;
        if ($template_id === '' || $template_id === '0') {
            $template_id = null;
        }
        
        // Payment method fields
        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Only checked for equality, not used in output
        $payment_method = isset($_POST['enable_payment_method']) && wp_unslash($_POST['enable_payment_method']) === '1' ? 'bank_transfer' : null;
        
        // Prepare data
        $data = array(
            'client_id' => isset($_POST['client_id']) ? intval(wp_unslash($_POST['client_id'])) : 0,
            'invoice_date' => isset($_POST['invoice_date']) ? sanitize_text_field(wp_unslash($_POST['invoice_date'])) : '',
            'due_date' => (isset($_POST['due_date']) && !empty($_POST['due_date'])) ? sanitize_text_field(wp_unslash($_POST['due_date'])) : null,
            'status' => isset($_POST['status']) ? sanitize_text_field(wp_unslash($_POST['status'])) : 'draft',
            'items' => json_encode($items_array),
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $total,
            'notes' => isset($_POST['notes']) ? sanitize_textarea_field(wp_unslash($_POST['notes'])) : '',
            'template_id' => $template_id,
            'payment_method' => $payment_method,
            'bank_name' => isset($_POST['bank_name']) ? sanitize_text_field(wp_unslash($_POST['bank_name'])) : null,
            'account_number' => isset($_POST['account_number']) ? sanitize_text_field(wp_unslash($_POST['account_number'])) : null,
            'account_title' => isset($_POST['account_title']) ? sanitize_text_field(wp_unslash($_POST['account_title'])) : null,
            'account_branch' => isset($_POST['account_branch']) ? sanitize_text_field(wp_unslash($_POST['account_branch'])) : null,
            'iban' => isset($_POST['iban']) ? sanitize_text_field(wp_unslash($_POST['iban'])) : null,
            'ifsc_code' => isset($_POST['ifsc_code']) ? sanitize_text_field(wp_unslash($_POST['ifsc_code'])) : null,
        );
        
        // Validate invoice data
        $validation_errors = IG_Validator::validate_invoice($data);
        if (!empty($validation_errors)) {
            IG_Logger::info('Invoice validation failed', array('errors' => $validation_errors));
            wp_send_json_error(array(
                'message' => __('Validation failed', 'ipsit-invoice-generator'),
                'errors' => $validation_errors
            ));
        }
        
        try {
        if ($invoice_id > 0) {
            // Update existing invoice
            $result = $db->update_invoice($invoice_id, $data);
            if ($result !== false) {
                    wp_send_json_success(array(
                        'message' => __('Invoice updated successfully.', 'ipsit-invoice-generator'),
                        'invoice_id' => $invoice_id
                    ));
            } else {
                    throw new Exception('Failed to update invoice in database');
            }
        } else {
            // Create new invoice
            $invoice_number = $db->get_next_invoice_number();
            $data['invoice_number'] = $invoice_number;
            $invoice_id = $db->insert_invoice($data);
            
            if ($invoice_id) {
                    wp_send_json_success(array(
                        'message' => __('Invoice created successfully.', 'ipsit-invoice-generator'),
                        'invoice_id' => $invoice_id
                    ));
            } else {
                    throw new Exception('Failed to create invoice in database');
                }
            }
        } catch (Exception $e) {
            IG_Logger::error('Failed to save invoice', array(
                'error' => $e->getMessage(),
                'invoice_id' => $invoice_id
            ));
            wp_send_json_error(array('message' => __('Failed to save invoice.', 'ipsit-invoice-generator')));
            }
        // phpcs:enable WordPress.Security.NonceVerification.Missing
    }
    
    /**
     * Delete invoice
     */
    public function delete_invoice() {
        $this->verify_nonce(IG_Config::CAP_MANAGE_INVOICES);
        // phpcs:disable WordPress.Security.NonceVerification.Missing -- Nonce verified in verify_nonce() above
        
        // Rate limiting
        if (!IG_Helper::check_rate_limit('delete_invoice')) {
            wp_send_json_error(array('message' => __('Too many requests. Please try again later.', 'ipsit-invoice-generator')));
        }
        
        $invoice_id = isset($_POST['invoice_id']) ? intval(wp_unslash($_POST['invoice_id'])) : 0;
        
        if ($invoice_id <= 0) {
            wp_send_json_error(array('message' => __('Invalid invoice ID.', 'ipsit-invoice-generator')));
        }
        
        $db = IG_Database::get_instance();
        
        try {
        $result = $db->delete_invoice($invoice_id);
        
        if ($result !== false) {
            wp_send_json_success(array('message' => __('Invoice deleted successfully.', 'ipsit-invoice-generator')));
        } else {
                throw new Exception('Failed to delete invoice from database');
            }
        } catch (Exception $e) {
            IG_Logger::error('Failed to delete invoice', array(
                'error' => $e->getMessage(),
                'invoice_id' => $invoice_id
            ));
            wp_send_json_error(array('message' => __('Failed to delete invoice.', 'ipsit-invoice-generator')));
        }
        // phpcs:enable WordPress.Security.NonceVerification.Missing
    }
    
    /**
     * Send email
     */
    public function send_email() {
        $this->verify_nonce();
        // phpcs:disable WordPress.Security.NonceVerification.Missing -- Nonce verified in verify_nonce() above
        
        $invoice_id = isset($_POST['invoice_id']) ? intval(wp_unslash($_POST['invoice_id'])) : 0;
        $to_email = isset($_POST['to_email']) ? sanitize_email(wp_unslash($_POST['to_email'])) : null;
        $subject = isset($_POST['subject']) ? sanitize_text_field(wp_unslash($_POST['subject'])) : null;
        $message = isset($_POST['message']) ? wp_kses_post(wp_unslash($_POST['message'])) : null;
        
        if ($invoice_id <= 0) {
            wp_send_json_error(array('message' => __('Invalid invoice ID.', 'ipsit-invoice-generator')));
        }
        
        $email = IG_Email::get_instance();
        $result = $email->send_invoice($invoice_id, $to_email, $subject, $message);
        
        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()));
        } else {
            wp_send_json_success(array('message' => __('Invoice sent successfully.', 'ipsit-invoice-generator')));
        }
        // phpcs:enable WordPress.Security.NonceVerification.Missing
    }
    
    /**
     * Save client
     */
    public function save_client() {
        $this->verify_nonce(IG_Config::CAP_MANAGE_CLIENTS);
        // phpcs:disable WordPress.Security.NonceVerification.Missing -- Nonce verified in verify_nonce() above
        
        // Rate limiting
        if (!IG_Helper::check_rate_limit('save_client')) {
            wp_send_json_error(array('message' => __('Too many requests. Please try again later.', 'ipsit-invoice-generator')));
        }
        
        $db = IG_Database::get_instance();
        
        $client_id = isset($_POST['client_id']) ? intval($_POST['client_id']) : 0;
        
        // Sanitize data
        $data = IG_Validator::sanitize_client($_POST);
        
        // Validate client data
        $validation_errors = IG_Validator::validate_client($data);
        if (!empty($validation_errors)) {
            IG_Logger::info('Client validation failed', array('errors' => $validation_errors));
            wp_send_json_error(array(
                'message' => __('Validation failed', 'ipsit-invoice-generator'),
                'errors' => $validation_errors
            ));
        }
        
        try {
        if ($client_id > 0) {
            $result = $db->update_client($client_id, $data);
            if ($result !== false) {
                // Handle custom fields
                $this->save_client_custom_fields($client_id);
                    wp_send_json_success(array(
                        'message' => __('Client updated successfully.', 'ipsit-invoice-generator'),
                        'client_id' => $client_id
                    ));
            } else {
                    throw new Exception('Failed to update client in database');
            }
        } else {
            $client_id = $db->insert_client($data);
            if ($client_id) {
                // Handle custom fields
                $this->save_client_custom_fields($client_id);
                    wp_send_json_success(array(
                        'message' => __('Client created successfully.', 'ipsit-invoice-generator'),
                        'client_id' => $client_id
                    ));
            } else {
                    throw new Exception('Failed to create client in database');
                }
            }
        } catch (Exception $e) {
            IG_Logger::error('Failed to save client', array(
                'error' => $e->getMessage(),
                'client_id' => $client_id
            ));
            wp_send_json_error(array('message' => __('Failed to save client.', 'ipsit-invoice-generator')));
            }
        // phpcs:enable WordPress.Security.NonceVerification.Missing
    }
    
    /**
     * Save client custom fields
     * phpcs:disable WordPress.Security.NonceVerification.Missing -- Nonce verified in parent method save_client()
     */
    private function save_client_custom_fields($client_id) {
        $db = IG_Database::get_instance();
        
        // Get existing field IDs for this client
        $existing_fields = $db->get_client_fields($client_id);
        $existing_field_ids = array();
        foreach ($existing_fields as $field) {
            $existing_field_ids[] = $field->id;
        }
        
        // Process custom fields from form
        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Array is unslashed and sanitized in loop
        $custom_fields = isset($_POST['custom_fields']) ? wp_unslash($_POST['custom_fields']) : array();
        $processed_field_ids = array();
        
        if (is_array($custom_fields)) {
            foreach ($custom_fields as $key => $field_data) {
                $field_id = isset($field_data['field_id']) ? intval(wp_unslash($field_data['field_id'])) : 0;
                $field_name = isset($field_data['field_name']) ? sanitize_text_field(wp_unslash($field_data['field_name'])) : '';
                $field_type = isset($field_data['field_type']) ? sanitize_text_field(wp_unslash($field_data['field_type'])) : 'text';
                $field_value = isset($field_data['field_value']) ? sanitize_textarea_field(wp_unslash($field_data['field_value'])) : '';
                
                // Skip if field name is empty
                if (empty($field_name)) {
                    continue;
                }
                
                $field_data_to_save = array(
                    'client_id' => $client_id,
                    'field_name' => $field_name,
                    'field_type' => $field_type,
                    'field_value' => $field_value,
                );
                
                if ($field_id > 0 && in_array($field_id, $existing_field_ids)) {
                    // Update existing field
                    $db->update_client_field($field_id, $field_data_to_save);
                    $processed_field_ids[] = $field_id;
                } else {
                    // Insert new field
                    $new_field_id = $db->insert_client_field($field_data_to_save);
                    if ($new_field_id) {
                        $processed_field_ids[] = $new_field_id;
                    }
                }
            }
        }
        
        // Delete fields that were removed from the form
        $fields_to_delete = array_diff($existing_field_ids, $processed_field_ids);
        foreach ($fields_to_delete as $field_id) {
            $db->delete_client_field($field_id);
        }
        // phpcs:enable WordPress.Security.NonceVerification.Missing
    }
    
    /**
     * Delete client
     */
    public function delete_client() {
        $this->verify_nonce(IG_Config::CAP_MANAGE_CLIENTS);
        // phpcs:disable WordPress.Security.NonceVerification.Missing -- Nonce verified in verify_nonce() above
        
        // Rate limiting
        if (!IG_Helper::check_rate_limit('delete_client')) {
            wp_send_json_error(array('message' => __('Too many requests. Please try again later.', 'ipsit-invoice-generator')));
        }
        
        $client_id = isset($_POST['client_id']) ? intval($_POST['client_id']) : 0;
        
        if ($client_id <= 0) {
            wp_send_json_error(array('message' => __('Invalid client ID.', 'ipsit-invoice-generator')));
        }
        
        $db = IG_Database::get_instance();
        
        try {
            // The delete_client method now handles transaction and custom fields deletion
        $result = $db->delete_client($client_id);
        
        if ($result !== false) {
            wp_send_json_success(array('message' => __('Client deleted successfully.', 'ipsit-invoice-generator')));
        } else {
                throw new Exception('Failed to delete client from database');
            }
        } catch (Exception $e) {
            IG_Logger::error('Failed to delete client', array(
                'error' => $e->getMessage(),
                'client_id' => $client_id
            ));
            wp_send_json_error(array('message' => __('Failed to delete client.', 'ipsit-invoice-generator')));
        }
        // phpcs:enable WordPress.Security.NonceVerification.Missing
    }
    
    /**
     * Save client field
     */
    public function save_client_field() {
        $this->verify_nonce();
        // phpcs:disable WordPress.Security.NonceVerification.Missing -- Nonce verified in verify_nonce() above
        
        $db = IG_Database::get_instance();
        
        $field_id = isset($_POST['field_id']) ? intval(wp_unslash($_POST['field_id'])) : 0;
        $client_id = isset($_POST['client_id']) ? intval(wp_unslash($_POST['client_id'])) : 0;
        $field_name = isset($_POST['field_name']) ? sanitize_text_field(wp_unslash($_POST['field_name'])) : '';
        $field_type = isset($_POST['field_type']) ? sanitize_text_field(wp_unslash($_POST['field_type'])) : 'text';
        $field_value = isset($_POST['field_value']) ? sanitize_textarea_field(wp_unslash($_POST['field_value'])) : '';
        
        if ($client_id <= 0 || empty($field_name)) {
            wp_send_json_error(array('message' => __('Client ID and field name are required.', 'ipsit-invoice-generator')));
        }
        
        $data = array(
            'client_id' => $client_id,
            'field_name' => $field_name,
            'field_type' => $field_type,
            'field_value' => $field_value,
        );
        
        if ($field_id > 0) {
            $result = $db->update_client_field($field_id, $data);
            if ($result !== false) {
                wp_send_json_success(array('message' => __('Field updated successfully.', 'ipsit-invoice-generator')));
            } else {
                wp_send_json_error(array('message' => __('Failed to update field.', 'ipsit-invoice-generator')));
            }
        } else {
            $field_id = $db->insert_client_field($data);
            if ($field_id) {
                wp_send_json_success(array('message' => __('Field added successfully.', 'ipsit-invoice-generator'), 'field_id' => $field_id));
            } else {
                wp_send_json_error(array('message' => __('Failed to add field.', 'ipsit-invoice-generator')));
            }
        }
        // phpcs:enable WordPress.Security.NonceVerification.Missing
    }
    
    /**
     * Delete client field
     */
    public function delete_client_field() {
        $this->verify_nonce();
        // phpcs:disable WordPress.Security.NonceVerification.Missing -- Nonce verified in verify_nonce() above
        
        $field_id = isset($_POST['field_id']) ? intval(wp_unslash($_POST['field_id'])) : 0;
        
        if ($field_id <= 0) {
            wp_send_json_error(array('message' => __('Invalid field ID.', 'ipsit-invoice-generator')));
        }
        
        $db = IG_Database::get_instance();
        $result = $db->delete_client_field($field_id);
        
        if ($result !== false) {
            wp_send_json_success(array('message' => __('Field deleted successfully.', 'ipsit-invoice-generator')));
        } else {
            wp_send_json_error(array('message' => __('Failed to delete field.', 'ipsit-invoice-generator')));
        }
        // phpcs:enable WordPress.Security.NonceVerification.Missing
    }
    
    /**
     * Save company
     */
    public function save_company() {
        $this->verify_nonce(IG_Config::CAP_MANAGE_SETTINGS);
        // phpcs:disable WordPress.Security.NonceVerification.Missing -- Nonce verified in verify_nonce() above
        
        // Rate limiting
        if (!IG_Helper::check_rate_limit('save_company')) {
            wp_send_json_error(array('message' => __('Too many requests. Please try again later.', 'ipsit-invoice-generator')));
        }
        
        $db = IG_Database::get_instance();
        
        // Sanitize data
        $data = IG_Validator::sanitize_company($_POST);
        
        // Validate company data
        $validation_errors = IG_Validator::validate_company($data);
        if (!empty($validation_errors)) {
            IG_Logger::info('Company validation failed', array('errors' => $validation_errors));
            wp_send_json_error(array(
                'message' => __('Validation failed', 'ipsit-invoice-generator'),
                'errors' => $validation_errors
            ));
        }
        
        // Handle logo upload
        if (!empty($_FILES['logo']['name'])) {
            // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- $_FILES is validated by validate_logo_upload()
            $upload_errors = IG_Helper::validate_logo_upload($_FILES['logo']);
            if (!empty($upload_errors)) {
                wp_send_json_error(array(
                    'message' => __('File upload validation failed', 'ipsit-invoice-generator'),
                    'errors' => $upload_errors
                ));
            }
            
            require_once(ABSPATH . 'wp-admin/includes/file.php');
            // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- $_FILES is validated and handled by wp_handle_upload()
            $uploaded_file = wp_handle_upload($_FILES['logo'], array('test_form' => false));
            
            if ($uploaded_file && !isset($uploaded_file['error'])) {
                $data['logo'] = $uploaded_file['url'];
            } else {
                IG_Logger::error('Logo upload failed', array('error' => $uploaded_file['error']));
                wp_send_json_error(array('message' => __('Failed to upload logo.', 'ipsit-invoice-generator')));
            }
        }
        
        try {
        $result = $db->update_company($data);
        
        if ($result !== false) {
            wp_send_json_success(array('message' => __('Company settings saved successfully.', 'ipsit-invoice-generator')));
        } else {
                throw new Exception('Failed to update company settings in database');
            }
        } catch (Exception $e) {
            IG_Logger::error('Failed to save company settings', array('error' => $e->getMessage()));
            wp_send_json_error(array('message' => __('Failed to save company settings.', 'ipsit-invoice-generator')));
        }
        // phpcs:enable WordPress.Security.NonceVerification.Missing
    }
    
    /**
     * Save template
     */
    public function save_template() {
        $this->verify_nonce(IG_Config::CAP_MANAGE_TEMPLATES);
        // phpcs:disable WordPress.Security.NonceVerification.Missing -- Nonce verified in verify_nonce() above
        
        // Rate limiting
        if (!IG_Helper::check_rate_limit('save_template')) {
            wp_send_json_error(array('message' => __('Too many requests. Please try again later.', 'ipsit-invoice-generator')));
        }
        
        $db = IG_Database::get_instance();
        
        // Ensure settings column exists before saving
        $db->ensure_template_settings_column();
        
        $template_id = isset($_POST['template_id']) ? intval(wp_unslash($_POST['template_id'])) : 0;
        
        // Get existing template to preserve type if updating
        $existing_template = null;
        if ($template_id > 0) {
            $existing_template = $db->get_template($template_id);
        }
        
        $data = array(
            'name' => isset($_POST['name']) ? sanitize_text_field(wp_unslash($_POST['name'])) : '',
            'type' => isset($_POST['type']) ? sanitize_text_field(wp_unslash($_POST['type'])) : ($existing_template ? $existing_template->type : 'custom'),
            // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitized by IG_Helper::sanitize_template_html()
            'html_content' => isset($_POST['html_content']) ? IG_Helper::sanitize_template_html(wp_unslash($_POST['html_content'])) : '',
            // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitized by IG_Helper::sanitize_css()
            'css_content' => isset($_POST['css_content']) ? IG_Helper::sanitize_css(wp_unslash($_POST['css_content'])) : '',
            'is_default' => isset($_POST['is_default']) ? intval(wp_unslash($_POST['is_default'])) : 0,
        );
        
        // Validate template data
        $validation_errors = IG_Validator::validate_template($data);
        if (!empty($validation_errors)) {
            IG_Logger::info('Template validation failed', array('errors' => $validation_errors));
            wp_send_json_error(array(
                'message' => __('Validation failed', 'ipsit-invoice-generator'),
                'errors' => $validation_errors
            ));
        }
        
        // Preserve template type if updating and type wasn't explicitly changed
        // BUT: if editing a prebuilt template, change type to 'custom' so it uses database version
        if ($template_id > 0 && $existing_template) {
            if (!isset($_POST['type'])) {
                // If it's a prebuilt template being edited (has html_content or css_content), change to custom
                $prebuilt_template_names = array('Default Template', 'Modern Minimal', 'Classic Professional', 'Project Based');
                if ($existing_template->type === 'prebuilt' && in_array($existing_template->name, $prebuilt_template_names)) {
                    // Check if template has custom content
                    $has_custom_content = !empty($data['html_content']) || !empty($data['css_content']);
                    if ($has_custom_content) {
                        $data['type'] = 'custom'; // Change to custom so it uses database version
                    } else {
                        $data['type'] = $existing_template->type; // Keep as prebuilt if no custom content
                    }
                } else {
                    $data['type'] = $existing_template->type; // Preserve type for other templates
                }
            }
        }
        
        // Save template settings if provided
        if (isset($_POST['template_settings']) && !empty($_POST['template_settings'])) {
            // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- JSON is validated and sanitized below
            $settings_raw = wp_unslash($_POST['template_settings']); // Unslash first
            $settings = json_decode($settings_raw, true);
            if (is_array($settings) && json_last_error() === JSON_ERROR_NONE) {
                $data['settings'] = wp_slash(json_encode($settings)); // Slash for database
            }
        }
        
        try {
        if ($template_id > 0) {
            $result = $db->update_template($template_id, $data);
            if ($result !== false) {
                wp_send_json_success(array(
                    'message' => __('Template updated successfully.', 'ipsit-invoice-generator'),
                    'template_id' => $template_id
                ));
            } else {
                    throw new Exception('Failed to update template in database');
            }
        } else {
            $template_id = $db->insert_template($data);
            if ($template_id) {
                wp_send_json_success(array(
                    'message' => __('Template saved successfully.', 'ipsit-invoice-generator'),
                    'template_id' => $template_id
                ));
            } else {
                    throw new Exception('Failed to save template in database');
                }
            }
        } catch (Exception $e) {
            IG_Logger::error('Failed to save template', array(
                'error' => $e->getMessage(),
                'template_id' => $template_id
            ));
                wp_send_json_error(array('message' => __('Failed to save template.', 'ipsit-invoice-generator')));
            }
        // phpcs:enable WordPress.Security.NonceVerification.Missing
    }
    
    /**
     * Delete template
     */
    public function delete_template() {
        $this->verify_nonce(IG_Config::CAP_MANAGE_TEMPLATES);
        // phpcs:disable WordPress.Security.NonceVerification.Missing -- Nonce verified in verify_nonce() above
        
        // Rate limiting
        if (!IG_Helper::check_rate_limit('delete_template')) {
            wp_send_json_error(array('message' => __('Too many requests. Please try again later.', 'ipsit-invoice-generator')));
        }
        
        $template_id = isset($_POST['template_id']) ? intval($_POST['template_id']) : 0;
        
        if ($template_id <= 0) {
            wp_send_json_error(array('message' => __('Invalid template ID.', 'ipsit-invoice-generator')));
        }
        
        $db = IG_Database::get_instance();
        
        try {
        $result = $db->delete_template($template_id);
        
        if ($result !== false) {
            wp_send_json_success(array('message' => __('Template deleted successfully.', 'ipsit-invoice-generator')));
        } else {
                throw new Exception('Failed to delete template from database');
            }
        } catch (Exception $e) {
            IG_Logger::error('Failed to delete template', array(
                'error' => $e->getMessage(),
                'template_id' => $template_id
            ));
            wp_send_json_error(array('message' => __('Failed to delete template.', 'ipsit-invoice-generator')));
        }
        // phpcs:enable WordPress.Security.NonceVerification.Missing
    }
    
    /**
     * Preview template
     */
    public function preview_template() {
        $this->verify_nonce();
        // phpcs:disable WordPress.Security.NonceVerification.Missing -- Nonce verified in verify_nonce() above
        
        $template_id = isset($_POST['template_id']) ? sanitize_text_field(wp_unslash($_POST['template_id'])) : null;
        $invoice_id = isset($_POST['invoice_id']) ? intval(wp_unslash($_POST['invoice_id'])) : 0;
        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitized by wp_kses_post()
        $html_content = isset($_POST['html_content']) ? wp_kses_post(wp_unslash($_POST['html_content'])) : '';
        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitized by wp_strip_all_tags()
        $css_content = isset($_POST['css_content']) ? wp_strip_all_tags(wp_unslash($_POST['css_content'])) : '';
        
        if ($invoice_id <= 0) {
            wp_send_json_error(array('message' => __('Please select an invoice to preview.', 'ipsit-invoice-generator')));
        }
        
        // If HTML content is provided, use it for preview
        if (!empty($html_content)) {
            $db = IG_Database::get_instance();
            $invoice = $db->get_invoice($invoice_id);
            $client = $db->get_client($invoice->client_id);
            $company = $db->get_company();
            $items = json_decode($invoice->items, true);
            
            $template_engine = IG_Template_Engine::get_instance();
            $data = array(
                'invoice' => $invoice,
                'client' => $client,
                'company' => $company,
                'items' => $items,
            );
            
            $html = $template_engine->replace_variables($html_content, $data, false);
            if (!empty($css_content)) {
                $html = '<style>' . $css_content . '</style>' . $html;
            }
        } else {
            $template_engine = IG_Template_Engine::get_instance();
            $html = '';
            
            if ($template_id) {
                // Convert numeric string to int for database templates
                if (is_numeric($template_id)) {
                    $template_id = intval($template_id);
                }
                $html = $template_engine->render_invoice($invoice_id, $template_id);
            }
        }
        
        wp_send_json_success(array('html' => $html));
        // phpcs:enable WordPress.Security.NonceVerification.Missing
    }
    
    /**
     * Save settings
     */
    public function save_settings() {
        $this->verify_nonce(IG_Config::CAP_MANAGE_SETTINGS);
        // phpcs:disable WordPress.Security.NonceVerification.Missing -- Nonce verified in verify_nonce() above
        
        // Rate limiting
        if (!IG_Helper::check_rate_limit('save_settings')) {
            wp_send_json_error(array('message' => __('Too many requests. Please try again later.', 'ipsit-invoice-generator')));
        }
        
        $settings = array(
            'ipsit_ig_invoice_number_prefix' => isset($_POST['invoice_number_prefix']) ? sanitize_text_field(wp_unslash($_POST['invoice_number_prefix'])) : '',
            'ipsit_ig_invoice_number_suffix' => isset($_POST['invoice_number_suffix']) ? sanitize_text_field(wp_unslash($_POST['invoice_number_suffix'])) : '',
            'ipsit_ig_invoice_number_padding' => isset($_POST['invoice_number_padding']) ? intval(wp_unslash($_POST['invoice_number_padding'])) : 4,
            'ipsit_ig_currency' => isset($_POST['currency']) ? sanitize_text_field(wp_unslash($_POST['currency'])) : 'USD',
            'ipsit_ig_currency_symbol' => isset($_POST['currency_symbol']) ? sanitize_text_field(wp_unslash($_POST['currency_symbol'])) : '$',
            'ipsit_ig_date_format' => isset($_POST['date_format']) ? sanitize_text_field(wp_unslash($_POST['date_format'])) : 'Y-m-d',
            'ipsit_ig_email_from_name' => isset($_POST['email_from_name']) ? sanitize_text_field(wp_unslash($_POST['email_from_name'])) : '',
            'ipsit_ig_email_from_email' => isset($_POST['email_from_email']) ? sanitize_email(wp_unslash($_POST['email_from_email'])) : '',
            'ipsit_ig_email_subject' => isset($_POST['email_subject']) ? sanitize_text_field(wp_unslash($_POST['email_subject'])) : '',
            'ipsit_ig_default_template' => isset($_POST['default_template']) ? intval(wp_unslash($_POST['default_template'])) : 0,
            'ipsit_ig_show_company_name_with_logo' => isset($_POST['show_company_name_with_logo']) ? 1 : 0,
            'ipsit_ig_design_primary_color' => isset($_POST['design_primary_color']) ? sanitize_hex_color(wp_unslash($_POST['design_primary_color'])) : '',
            'ipsit_ig_design_secondary_color' => isset($_POST['design_secondary_color']) ? sanitize_hex_color(wp_unslash($_POST['design_secondary_color'])) : '',
            'ipsit_ig_design_success_color' => isset($_POST['design_success_color']) ? sanitize_hex_color(wp_unslash($_POST['design_success_color'])) : '',
            'ipsit_ig_design_error_color' => isset($_POST['design_error_color']) ? sanitize_hex_color(wp_unslash($_POST['design_error_color'])) : '',
            'ipsit_ig_design_warning_color' => isset($_POST['design_warning_color']) ? sanitize_hex_color(wp_unslash($_POST['design_warning_color'])) : '',
        );
        
        foreach ($settings as $key => $value) {
            update_option($key, $value);
        }
        
        wp_send_json_success(array('message' => __('Settings saved successfully.', 'ipsit-invoice-generator')));
        // phpcs:enable WordPress.Security.NonceVerification.Missing
    }
}

