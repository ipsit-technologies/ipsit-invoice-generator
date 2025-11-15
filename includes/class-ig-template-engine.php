<?php
/**
 * Template Engine Class
 *
 * Handles template rendering and variable replacement
 */

if (!defined('ABSPATH')) {
    exit;
}

class IG_Template_Engine {
    
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
     * Render invoice template
     */
    public function render_invoice($invoice_id, $template_id = null) {
        $db = IG_Database::get_instance();
        $invoice = $db->get_invoice($invoice_id);
        
        if (!$invoice) {
            return '';
        }
        
        // Get client data
        $client = $db->get_client($invoice->client_id);
        
        // Get company data
        $company = $db->get_company();
        
        // Parse invoice items
        $items = json_decode($invoice->items, true);
        if (!is_array($items)) {
            $items = array();
        }
        
        // Format dates and currency
        $invoice_date = $invoice->invoice_date ? date_i18n(get_option('date_format'), strtotime($invoice->invoice_date)) : '';
        $due_date = $invoice->due_date ? date_i18n(get_option('date_format'), strtotime($invoice->due_date)) : '';
        $currency_symbol = get_option('ipsit_ig_currency_symbol', '$');
        
        // Get template
        if (!$template_id) {
            $template_id = $invoice->template_id;
        }
        
        // Handle empty string as null
        if ($template_id === '' || $template_id === '0') {
            $template_id = null;
        }
        
        // If template_id is provided, check if it's a file-based template or database template
        if ($template_id) {
            // First check if it's a file-based template (starts with 'file_')
            // This check must happen before converting to int
            if (is_string($template_id) && strpos($template_id, 'file_') === 0) {
                $template_key = str_replace('file_', '', $template_id);
                $template_names = array(
                    'default' => 'Default Template',
                    'template-1' => 'Modern Minimal',
                    'template-2' => 'Classic Professional',
                    'template-project' => 'Project Based',
                );
                $template_name = isset($template_names[$template_key]) ? $template_names[$template_key] : null;
                return $this->render_file_template($template_name, $invoice, $client, $company);
            }
            
            // Otherwise, try to get from database (convert to int if numeric)
            if (is_numeric($template_id)) {
                $template = $db->get_template(intval($template_id));
                if ($template) {
                    // Check if this is a prebuilt template - if so, use file-based rendering
                    // UNLESS it has been customized (has html_content or css_content in database)
                    // because PHP templates can't be stored/executed from database
                    $prebuilt_template_names = array('Default Template', 'Modern Minimal', 'Classic Professional', 'Project Based');
                    $is_prebuilt_name = in_array($template->name, $prebuilt_template_names);
                    
                    // If it's a prebuilt template name but has custom content, use database version
                    // Otherwise, if type is prebuilt and name matches, use file-based template
                    if ($is_prebuilt_name && $template->type === 'prebuilt' && 
                        empty($template->html_content) && empty($template->css_content)) {
                        // Use file-based template only if no custom content exists
                        return $this->render_file_template($template->name, $invoice, $client, $company);
                    }
                    
                    // For custom templates or prebuilt templates with custom content, use database content
                    // Prepare data for template
                    $data = array(
                        'invoice' => $invoice,
                        'client' => $client,
                        'company' => $company,
                        'items' => $items,
                    );
                    
                    // Check if this is a project template
                    $is_project_template = false;
                    if (stripos($template->name, 'project') !== false || 
                        (isset($template->settings) && !empty($template->settings))) {
                        $settings = json_decode(wp_unslash($template->settings), true);
                        if (isset($settings['is_project_template']) && $settings['is_project_template']) {
                            $is_project_template = true;
                        } elseif (stripos($template->html_content, 'Project Description') !== false) {
                            $is_project_template = true;
                        }
                    }
                    
                    // Replace variables in template
                    $html = $this->replace_variables($template->html_content, $data, false);
                    
                    // Add CSS
                    if (!empty($template->css_content)) {
                        $html = '<style>' . $template->css_content . '</style>' . $html;
                    }
                    
                    return $html;
                }
            }
        }
        
        // No template ID or template not found - use default file-based template
        return $this->render_file_template(null, $invoice, $client, $company);
    }
    
    /**
     * Render file-based template
     */
    private function render_file_template($template_name = null, $invoice, $client, $company) {
        $templates_dir = IPSIT_IG_PLUGIN_DIR . 'templates/';
        
        // Map template names to files
        $template_files = array(
            'Default Template' => 'template-default.php',
            'Modern Minimal' => 'template-1.php',
            'Classic Professional' => 'template-2.php',
            'Project Based' => 'template-project.php',
        );
        
        // Default to template-default.php if no name provided
        if (!$template_name || !isset($template_files[$template_name])) {
            $template_file = $templates_dir . 'template-default.php';
        } else {
            $template_file = $templates_dir . $template_files[$template_name];
        }
        
        // If file doesn't exist, use default
        if (!file_exists($template_file)) {
            $template_file = $templates_dir . 'template-default.php';
        }
        
        // Parse invoice items
        $items = json_decode($invoice->items, true);
        if (!is_array($items)) {
            $items = array();
        }
        
        // Format dates and currency
        $invoice_date = $invoice->invoice_date ? date_i18n(get_option('date_format'), strtotime($invoice->invoice_date)) : '';
        $due_date = $invoice->due_date ? date_i18n(get_option('date_format'), strtotime($invoice->due_date)) : '';
        $currency_symbol = get_option('ipsit_ig_currency_symbol', '$');
        
        // Render template file
        ob_start();
        include $template_file;
        return ob_get_clean();
    }
    
    /**
     * Get available file-based templates
     */
    public function get_file_templates() {
        $templates_dir = IPSIT_IG_PLUGIN_DIR . 'templates/';
        $templates = array();
        
        $file_templates = array(
            'default' => array('name' => 'Default Template', 'file' => 'template-default.php'),
            'template-1' => array('name' => 'Modern Minimal', 'file' => 'template-1.php'),
            'template-2' => array('name' => 'Classic Professional', 'file' => 'template-2.php'),
            'template-project' => array('name' => 'Project Based', 'file' => 'template-project.php'),
        );
        
        foreach ($file_templates as $key => $info) {
            if (file_exists($templates_dir . $info['file'])) {
                $templates[] = (object) array(
                    'id' => 'file_' . $key,
                    'name' => $info['name'],
                    'type' => 'file',
                    'is_default' => ($key === 'default' ? 1 : 0),
                );
            }
        }
        
        return $templates;
    }
    
    /**
     * Replace template variables
     */
    public function replace_variables($content, $data, $include_table_headers = true) {
        $invoice = $data['invoice'];
        $client = $data['client'];
        $company = $data['company'];
        $items = $data['items'];
        
        // Get client custom fields
        $client_custom_fields = array();
        if ($client) {
            $db = IG_Database::get_instance();
            $client_custom_fields_raw = $db->get_client_fields($client->id);
            foreach ($client_custom_fields_raw as $custom_field) {
                $field_key = 'client_custom_' . sanitize_key($custom_field->field_name);
                $client_custom_fields['{' . $field_key . '}'] = esc_html($custom_field->field_value);
            }
        }
        
        // Format dates
        $invoice_date = $invoice->invoice_date ? date_i18n(get_option('date_format'), strtotime($invoice->invoice_date)) : '';
        $due_date = $invoice->due_date ? date_i18n(get_option('date_format'), strtotime($invoice->due_date)) : '';
        
        // Format currency
        $currency_symbol = get_option('ipsit_ig_currency_symbol', '$');
        
        // Conditional placeholders for due date and tax
        $due_date_row = $due_date ? '<p><strong>' . esc_html__('Due Date:', 'ipsit-invoice-generator') . '</strong> ' . esc_html($due_date) . '</p>' : '';
        $tax_row = ($invoice->tax && floatval($invoice->tax) > 0) ? '<tr><td class="label">' . esc_html__('Tax:', 'ipsit-invoice-generator') . '</td><td class="amount">' . $currency_symbol . number_format($invoice->tax, 2) . '</td></tr>' : '';
        
        // Replace variables
        $replacements = array(
            '{invoice_number}' => $invoice->invoice_number,
            '{invoice_date}' => $invoice_date,
            '{due_date}' => $due_date,
            '{due_date_row}' => $due_date_row, // Full row with label, only shows if due_date exists
            '{status}' => ucfirst($invoice->status),
            '{subtotal}' => $currency_symbol . number_format($invoice->subtotal, 2),
            '{tax}' => $currency_symbol . number_format($invoice->tax, 2),
            '{tax_row}' => $tax_row, // Full row with label, only shows if tax > 0
            '{total}' => $currency_symbol . number_format($invoice->total, 2),
            '{notes}' => nl2br(esc_html($invoice->notes)),
        );
        
        // Company variables
        // Handle company name based on setting - hide if logo exists and setting is disabled
        // BUT: Always show company name in {company_name} for Bill From section
        // Use {company_name_conditional} for header area only
        $show_company_name_with_logo = get_option('ipsit_ig_show_company_name_with_logo', 0);
        $company_name_conditional = '';
        if ($company && $company->name) {
            // If logo exists and setting is disabled, don't show company name in header
            if ($company->logo && !$show_company_name_with_logo) {
                $company_name_conditional = '';
            } else {
                $company_name_conditional = esc_html($company->name);
            }
        }
        
        // Add company variables to replacements
        $replacements['{company_name}'] = $company ? esc_html($company->name) : ''; // Always show for Bill From
        $replacements['{company_name_conditional}'] = $company_name_conditional; // Conditional for header only
        $replacements['{company_email}'] = $company ? esc_html($company->email) : '';
        $replacements['{company_phone}'] = $company ? esc_html($company->phone) : '';
        $replacements['{company_address}'] = $company ? esc_html($company->address) : '';
        $replacements['{company_city}'] = $company ? esc_html($company->city) : '';
        $replacements['{company_state}'] = $company ? esc_html($company->state) : '';
        $replacements['{company_zip}'] = $company ? esc_html($company->zip) : '';
        $replacements['{company_country}'] = $company ? esc_html($company->country) : '';
        $replacements['{company_tax_id}'] = $company ? esc_html($company->tax_id) : '';
        $replacements['{company_website}'] = $company ? esc_html($company->website) : '';
        $replacements['{company_logo}'] = $company && $company->logo ? '<img src="' . esc_url($company->logo) . '" alt="Company Logo" />' : '';
        
        // Client variables
        $replacements['{client_name}'] = $client ? esc_html($client->name) : '';
        $replacements['{client_email}'] = $client ? esc_html($client->email) : '';
        $replacements['{client_phone}'] = $client ? esc_html($client->phone) : '';
        $replacements['{client_address}'] = $client ? esc_html($client->address) : '';
        $replacements['{client_city}'] = $client ? esc_html($client->city) : '';
        $replacements['{client_state}'] = $client ? esc_html($client->state) : '';
        $replacements['{client_zip}'] = $client ? esc_html($client->zip) : '';
        $replacements['{client_country}'] = $client ? esc_html($client->country) : '';
        
        // Check if this is a project template by examining content
        $is_project_template = false;
        if (stripos($content, 'Project Description') !== false || stripos($content, 'Project Price') !== false) {
            $is_project_template = true;
        }
        
        // Add items table and payment method replacements
        $replacements['{items_table}'] = $this->render_items_table($items, $currency_symbol, $include_table_headers, $is_project_template);
        $replacements['{payment_method}'] = $this->render_payment_method($invoice);
        
        // Add custom client fields
        if (!empty($client_custom_fields)) {
            $replacements = array_merge($replacements, $client_custom_fields);
        }
        
        $content = str_replace(array_keys($replacements), array_values($replacements), $content);
        
        return $content;
    }
    
    /**
     * Render payment method section
     */
    private function render_payment_method($invoice) {
        if ($invoice->payment_method !== 'bank_transfer' || 
            (!$invoice->bank_name && !$invoice->account_number && !$invoice->account_title && !$invoice->account_branch && !$invoice->iban && !$invoice->ifsc_code)) {
            return '';
        }
        
        $html = '<div class="payment-info">' .
            '<h3>' . esc_html__('Payment Method: Bank Transfer', 'ipsit-invoice-generator') . '</h3>';
        
        if ($invoice->bank_name) {
            $html .= '<p><strong>' . esc_html__('Bank Name:', 'ipsit-invoice-generator') . '</strong> ' . esc_html($invoice->bank_name) . '</p>';
        }
        if ($invoice->account_number) {
            $html .= '<p><strong>' . esc_html__('Account Number:', 'ipsit-invoice-generator') . '</strong> ' . esc_html($invoice->account_number) . '</p>';
        }
        if ($invoice->account_title) {
            $html .= '<p><strong>' . esc_html__('Account Title:', 'ipsit-invoice-generator') . '</strong> ' . esc_html($invoice->account_title) . '</p>';
        }
        if ($invoice->account_branch) {
            $html .= '<p><strong>' . esc_html__('Account Branch:', 'ipsit-invoice-generator') . '</strong> ' . esc_html($invoice->account_branch) . '</p>';
        }
        if ($invoice->iban) {
            $html .= '<p><strong>' . esc_html__('IBAN:', 'ipsit-invoice-generator') . '</strong> ' . esc_html($invoice->iban) . '</p>';
        }
        if ($invoice->ifsc_code) {
            $html .= '<p><strong>' . esc_html__('IFSC Code:', 'ipsit-invoice-generator') . '</strong> ' . esc_html($invoice->ifsc_code) . '</p>';
        }
        
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Render items table
     */
    private function render_items_table($items, $currency_symbol, $include_headers = true, $is_project_template = false) {
        if (empty($items)) {
            return '<p>' . __('No items', 'ipsit-invoice-generator') . '</p>';
        }
        
        $html = '';
        
        // Only add headers if requested (for custom templates that don't have headers)
        if ($include_headers) {
            $html .= '<table class="ig-items-table">';
            $html .= '<thead><tr>';
            if ($is_project_template) {
                $html .= '<th>' . __('Project Description', 'ipsit-invoice-generator') . '</th>';
                $html .= '<th>' . __('Project Price', 'ipsit-invoice-generator') . '</th>';
                $html .= '<th>' . __('Project Amount', 'ipsit-invoice-generator') . '</th>';
            } else {
                $html .= '<th>' . __('Description', 'ipsit-invoice-generator') . '</th>';
                $html .= '<th>' . __('Quantity', 'ipsit-invoice-generator') . '</th>';
                $html .= '<th>' . __('Price', 'ipsit-invoice-generator') . '</th>';
                $html .= '<th>' . __('Total', 'ipsit-invoice-generator') . '</th>';
            }
            $html .= '</tr></thead>';
            $html .= '<tbody>';
        }
        
        foreach ($items as $item) {
            $quantity = isset($item['quantity']) ? floatval($item['quantity']) : 0;
            $price = isset($item['price']) ? floatval($item['price']) : 0;
            
            if ($is_project_template) {
                $total = $price; // Amount equals price for projects
                $html .= '<tr>';
                $html .= '<td>' . esc_html($item['description']) . '</td>';
                $html .= '<td class="text-right">' . $currency_symbol . number_format($price, 2) . '</td>';
                $html .= '<td class="text-right">' . $currency_symbol . number_format($total, 2) . '</td>';
                $html .= '</tr>';
            } else {
                $total = $quantity * $price;
                $html .= '<tr>';
                $html .= '<td>' . esc_html($item['description']) . '</td>';
                $html .= '<td class="text-right">' . number_format($quantity, 2) . '</td>';
                $html .= '<td class="text-right">' . $currency_symbol . number_format($price, 2) . '</td>';
                $html .= '<td class="text-right">' . $currency_symbol . number_format($total, 2) . '</td>';
                $html .= '</tr>';
            }
        }
        
        if ($include_headers) {
            $html .= '</tbody></table>';
        }
        
        return $html;
    }
    
    
    /**
     * Get available template variables
     */
    public function get_template_variables() {
        return array(
            'invoice' => array(
                '{invoice_number}',
                '{invoice_date}',
                '{due_date}',
                '{status}',
                '{subtotal}',
                '{tax}',
                '{total}',
                '{notes}',
            ),
            'company' => array(
                '{company_name}',
                '{company_email}',
                '{company_phone}',
                '{company_address}',
                '{company_city}',
                '{company_state}',
                '{company_zip}',
                '{company_country}',
                '{company_tax_id}',
                '{company_website}',
                '{company_logo}',
            ),
            'client' => array(
                '{client_name}',
                '{client_email}',
                '{client_phone}',
                '{client_address}',
                '{client_city}',
                '{client_state}',
                '{client_zip}',
                '{client_country}',
            ),
            'items' => array(
                '{items_table}',
            ),
        );
    }
}

