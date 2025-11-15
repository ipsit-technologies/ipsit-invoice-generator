<?php
/**
 * Validation Helper Class
 *
 * Handles data validation throughout the plugin
 */

if (!defined('ABSPATH')) {
    exit;
}

class IG_Validator {
    
    /**
     * Validate invoice data
     */
    public static function validate_invoice(array $data): array {
        $errors = array();
        
        // Client ID
        if (empty($data['client_id']) || !is_numeric($data['client_id']) || $data['client_id'] <= 0) {
            $errors[] = __('Valid client is required.', 'ipsit-invoice-generator');
        }
        
        // Invoice date
        if (empty($data['invoice_date'])) {
            $errors[] = __('Invoice date is required.', 'ipsit-invoice-generator');
        } elseif (!self::is_valid_date($data['invoice_date'])) {
            $errors[] = __('Invalid invoice date format.', 'ipsit-invoice-generator');
        }
        
        // Due date (optional but must be valid if provided)
        if (!empty($data['due_date']) && !self::is_valid_date($data['due_date'])) {
            $errors[] = __('Invalid due date format.', 'ipsit-invoice-generator');
        }
        
        // Status
        $valid_statuses = array_keys(IG_Config::get_invoice_statuses());
        if (!in_array($data['status'], $valid_statuses)) {
            $errors[] = __('Invalid invoice status.', 'ipsit-invoice-generator');
        }
        
        // Financial validation
        if (!is_numeric($data['subtotal']) || $data['subtotal'] < 0) {
            $errors[] = __('Invalid subtotal amount.', 'ipsit-invoice-generator');
        }
        
        if (!is_numeric($data['tax']) || $data['tax'] < 0) {
            $errors[] = __('Invalid tax amount.', 'ipsit-invoice-generator');
        }
        
        if (!is_numeric($data['total']) || $data['total'] < 0) {
            $errors[] = __('Invalid total amount.', 'ipsit-invoice-generator');
        }
        
        // Items validation
        if (empty($data['items']) || $data['items'] === '[]') {
            $errors[] = __('At least one item is required.', 'ipsit-invoice-generator');
        } else {
            $items = json_decode($data['items'], true);
            if (!is_array($items)) {
                $errors[] = __('Invalid items format.', 'ipsit-invoice-generator');
            } elseif (empty($items)) {
                $errors[] = __('At least one item is required.', 'ipsit-invoice-generator');
            }
        }
        
        return $errors;
    }
    
    /**
     * Validate client data
     */
    public static function validate_client(array $data): array {
        $errors = array();
        
        // Name is required
        if (empty($data['name']) || trim($data['name']) === '') {
            $errors[] = __('Client name is required.', 'ipsit-invoice-generator');
        } elseif (strlen($data['name']) > 255) {
            $errors[] = __('Client name is too long (max 255 characters).', 'ipsit-invoice-generator');
        }
        
        // Email validation (optional but must be valid if provided)
        if (!empty($data['email']) && !is_email($data['email'])) {
            $errors[] = __('Invalid email address.', 'ipsit-invoice-generator');
        }
        
        // Phone validation (optional, basic format check)
        if (!empty($data['phone']) && !self::is_valid_phone($data['phone'])) {
            $errors[] = __('Invalid phone number format.', 'ipsit-invoice-generator');
        }
        
        // ZIP code validation (optional, basic check)
        if (!empty($data['zip']) && strlen($data['zip']) > 20) {
            $errors[] = __('ZIP code is too long.', 'ipsit-invoice-generator');
        }
        
        return $errors;
    }
    
    /**
     * Validate company data
     */
    public static function validate_company(array $data): array {
        $errors = array();
        
        // Name is required
        if (empty($data['name']) || trim($data['name']) === '') {
            $errors[] = __('Company name is required.', 'ipsit-invoice-generator');
        }
        
        // Email validation (optional but must be valid if provided)
        if (!empty($data['email']) && !is_email($data['email'])) {
            $errors[] = __('Invalid email address.', 'ipsit-invoice-generator');
        }
        
        // Website validation (optional but must be valid if provided)
        if (!empty($data['website']) && !filter_var($data['website'], FILTER_VALIDATE_URL)) {
            $errors[] = __('Invalid website URL.', 'ipsit-invoice-generator');
        }
        
        return $errors;
    }
    
    /**
     * Validate template data
     */
    public static function validate_template(array $data): array {
        $errors = array();
        
        // Name is required
        if (empty($data['name']) || trim($data['name']) === '') {
            $errors[] = __('Template name is required.', 'ipsit-invoice-generator');
        }
        
        // HTML content is required
        if (empty($data['html_content']) || trim($data['html_content']) === '') {
            $errors[] = __('Template HTML content is required.', 'ipsit-invoice-generator');
        }
        
        // Type validation
        $valid_types = array('custom', 'prebuilt');
        if (!in_array($data['type'], $valid_types)) {
            $errors[] = __('Invalid template type.', 'ipsit-invoice-generator');
        }
        
        return $errors;
    }
    
    /**
     * Validate date format
     */
    private static function is_valid_date(string $date, string $format = 'Y-m-d'): bool {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }
    
    /**
     * Validate phone number (basic check)
     */
    private static function is_valid_phone(string $phone): bool {
        // Allow numbers, spaces, dashes, parentheses, and plus sign
        return preg_match('/^[\d\s\-\(\)\+]+$/', $phone) === 1;
    }
    
    /**
     * Sanitize invoice data
     */
    public static function sanitize_invoice(array $data): array {
        return array(
            'invoice_number' => isset($data['invoice_number']) ? sanitize_text_field($data['invoice_number']) : '',
            'client_id' => isset($data['client_id']) ? absint($data['client_id']) : 0,
            'invoice_date' => isset($data['invoice_date']) ? sanitize_text_field($data['invoice_date']) : '',
            'due_date' => isset($data['due_date']) ? sanitize_text_field($data['due_date']) : null,
            'status' => isset($data['status']) ? sanitize_text_field($data['status']) : IG_Config::DEFAULT_INVOICE_STATUS,
            'items' => isset($data['items']) ? $data['items'] : '[]',
            'subtotal' => isset($data['subtotal']) ? floatval($data['subtotal']) : 0,
            'tax' => isset($data['tax']) ? floatval($data['tax']) : 0,
            'total' => isset($data['total']) ? floatval($data['total']) : 0,
            'notes' => isset($data['notes']) ? sanitize_textarea_field($data['notes']) : '',
            'template_id' => isset($data['template_id']) ? $data['template_id'] : null,
            'payment_method' => isset($data['payment_method']) ? sanitize_text_field($data['payment_method']) : null,
            'bank_name' => isset($data['bank_name']) ? sanitize_text_field($data['bank_name']) : null,
            'account_number' => isset($data['account_number']) ? sanitize_text_field($data['account_number']) : null,
            'account_title' => isset($data['account_title']) ? sanitize_text_field($data['account_title']) : null,
            'account_branch' => isset($data['account_branch']) ? sanitize_text_field($data['account_branch']) : null,
            'iban' => isset($data['iban']) ? sanitize_text_field($data['iban']) : null,
            'ifsc_code' => isset($data['ifsc_code']) ? sanitize_text_field($data['ifsc_code']) : null,
        );
    }
    
    /**
     * Sanitize client data
     */
    public static function sanitize_client(array $data): array {
        return array(
            'name' => isset($data['name']) ? sanitize_text_field($data['name']) : '',
            'email' => isset($data['email']) ? sanitize_email($data['email']) : '',
            'phone' => isset($data['phone']) ? sanitize_text_field($data['phone']) : '',
            'address' => isset($data['address']) ? sanitize_textarea_field($data['address']) : '',
            'city' => isset($data['city']) ? sanitize_text_field($data['city']) : '',
            'state' => isset($data['state']) ? sanitize_text_field($data['state']) : '',
            'zip' => isset($data['zip']) ? sanitize_text_field($data['zip']) : '',
            'country' => isset($data['country']) ? sanitize_text_field($data['country']) : '',
        );
    }
    
    /**
     * Sanitize company data
     */
    public static function sanitize_company(array $data): array {
        return array(
            'name' => isset($data['name']) ? sanitize_text_field($data['name']) : '',
            'email' => isset($data['email']) ? sanitize_email($data['email']) : '',
            'phone' => isset($data['phone']) ? sanitize_text_field($data['phone']) : '',
            'address' => isset($data['address']) ? sanitize_textarea_field($data['address']) : '',
            'city' => isset($data['city']) ? sanitize_text_field($data['city']) : '',
            'state' => isset($data['state']) ? sanitize_text_field($data['state']) : '',
            'zip' => isset($data['zip']) ? sanitize_text_field($data['zip']) : '',
            'country' => isset($data['country']) ? sanitize_text_field($data['country']) : '',
            'tax_id' => isset($data['tax_id']) ? sanitize_text_field($data['tax_id']) : '',
            'website' => isset($data['website']) ? esc_url_raw($data['website']) : '',
            'default_payment_method' => isset($data['default_payment_method']) ? sanitize_text_field($data['default_payment_method']) : null,
            'default_bank_name' => isset($data['default_bank_name']) ? sanitize_text_field($data['default_bank_name']) : null,
            'default_account_number' => isset($data['default_account_number']) ? sanitize_text_field($data['default_account_number']) : null,
            'default_account_title' => isset($data['default_account_title']) ? sanitize_text_field($data['default_account_title']) : null,
            'default_account_branch' => isset($data['default_account_branch']) ? sanitize_text_field($data['default_account_branch']) : null,
            'default_iban' => isset($data['default_iban']) ? sanitize_text_field($data['default_iban']) : null,
            'default_ifsc_code' => isset($data['default_ifsc_code']) ? sanitize_text_field($data['default_ifsc_code']) : null,
        );
    }
}

