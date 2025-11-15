<?php
/**
 * Email Class
 *
 * Handles sending invoices via email
 */

if (!defined('ABSPATH')) {
    exit;
}

class IG_Email {
    
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
     * Send invoice via email
     */
    public function send_invoice($invoice_id, $to_email = null, $subject = null, $message = null) {
        $db = IG_Database::get_instance();
        $invoice = $db->get_invoice($invoice_id);
        
        if (!$invoice) {
            return new WP_Error('invoice_not_found', __('Invoice not found.', 'ipsit-invoice-generator'));
        }
        
        $client = $db->get_client($invoice->client_id);
        
        if (!$client) {
            return new WP_Error('client_not_found', __('Client not found.', 'ipsit-invoice-generator'));
        }
        
        // Get recipient email
        if (!$to_email) {
            $to_email = $client->email;
        }
        
        if (empty($to_email)) {
            return new WP_Error('no_email', __('Client email address is required.', 'ipsit-invoice-generator'));
        }
        
        // Get email subject
        if (!$subject) {
            $subject_template = get_option('ipsit_ig_email_subject', 'Invoice #{invoice_number}');
            $subject = str_replace('{invoice_number}', $invoice->invoice_number, $subject_template);
        }
        
        // Get email message
        if (!$message) {
            $message = $this->get_default_email_message($invoice, $client);
        }
        
        // Generate PDF
        $pdf = IG_PDF::get_instance();
        $pdf_content = $pdf->get_pdf_string($invoice_id);
        
        if (!$pdf_content) {
            return new WP_Error('pdf_generation_failed', __('Failed to generate PDF.', 'ipsit-invoice-generator'));
        }
        
        // Prepare email headers
        $from_name = get_option('ipsit_ig_email_from_name', get_bloginfo('name'));
        $from_email = get_option('ipsit_ig_email_from_email', get_option('admin_email'));
        
        $headers = array(
            'From: ' . $from_name . ' <' . $from_email . '>',
            'Content-Type: text/html; charset=UTF-8',
        );
        
        // Prepare attachments
        $upload_dir = wp_upload_dir();
        $temp_dir = $upload_dir['basedir'] . '/temp';
        
        if (!file_exists($temp_dir)) {
            wp_mkdir_p($temp_dir);
        }
        
        $filename = 'invoice-' . sanitize_file_name($invoice->invoice_number) . '.pdf';
        $filepath = $temp_dir . '/' . $filename;
        
        file_put_contents($filepath, $pdf_content);
        
        $attachments = array($filepath);
        
        // Send email
        $result = wp_mail($to_email, $subject, $message, $headers, $attachments);
        
        // Clean up temp file
        if (file_exists($filepath)) {
            wp_delete_file($filepath);
        }
        
        if ($result) {
            // Update invoice status to 'sent'
            $db->update_invoice($invoice_id, array('status' => 'sent'));
            return true;
        }
        
        return new WP_Error('email_failed', __('Failed to send email.', 'ipsit-invoice-generator'));
    }
    
    /**
     * Get default email message
     */
    private function get_default_email_message($invoice, $client) {
        $message = '<html><body>';
        /* translators: %s: Client name */
        $message .= '<p>' . sprintf(__('Dear %s,', 'ipsit-invoice-generator'), esc_html($client->name)) . '</p>';
        $message .= '<p>' . __('Please find attached invoice #', 'ipsit-invoice-generator') . esc_html($invoice->invoice_number) . '.</p>';
        
        if ($invoice->due_date) {
            $due_date = date_i18n(get_option('date_format'), strtotime($invoice->due_date));
            /* translators: %s: Due date */
            $message .= '<p>' . sprintf(__('Payment is due by %s.', 'ipsit-invoice-generator'), esc_html($due_date)) . '</p>';
        }
        
        $currency_symbol = get_option('ipsit_ig_currency_symbol', '$');
        $message .= '<p><strong>' . __('Total Amount:', 'ipsit-invoice-generator') . ' ' . $currency_symbol . number_format($invoice->total, 2) . '</strong></p>';
        
        if ($invoice->notes) {
            $message .= '<p>' . nl2br(esc_html($invoice->notes)) . '</p>';
        }
        
        $message .= '<p>' . __('Thank you for your business!', 'ipsit-invoice-generator') . '</p>';
        $message .= '</body></html>';
        
        return $message;
    }
}

