<?php
/**
 * PDF Generation Class
 *
 * Handles PDF generation using dompdf
 */

if (!defined('ABSPATH')) {
    exit;
}

use Dompdf\Dompdf;
use Dompdf\Options;

class IG_PDF {
    
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
     * Generate PDF from invoice
     */
    public function generate($invoice_id, $template_id = null, $download = false) {
        $template_engine = IG_Template_Engine::get_instance();
        $html = $template_engine->render_invoice($invoice_id, $template_id);
        
        if (empty($html)) {
            wp_die(esc_html__('Unable to generate PDF. Invoice not found.', 'ipsit-invoice-generator'));
        }
        
        // Configure dompdf options
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('isPhpEnabled', false);
        $options->set('chroot', ABSPATH);
        
        // Create dompdf instance
        $dompdf = new Dompdf($options);
        
        // Load HTML
        $dompdf->loadHtml($html);
        
        // Set paper size
        $dompdf->setPaper('A4', 'portrait');
        
        // Render PDF
        $dompdf->render();
        
        // Output PDF
        if ($download) {
            $db = IG_Database::get_instance();
            $invoice = $db->get_invoice($invoice_id);
            $filename = 'invoice-' . sanitize_file_name($invoice->invoice_number) . '.pdf';
            $dompdf->stream($filename, array('Attachment' => 1));
        } else {
            return $dompdf->output();
        }
    }
    
    /**
     * Download PDF
     */
    public function download($invoice_id, $template_id = null) {
        $this->generate($invoice_id, $template_id, true);
        exit;
    }
    
    /**
     * Get PDF as string
     */
    public function get_pdf_string($invoice_id, $template_id = null) {
        return $this->generate($invoice_id, $template_id, false);
    }
    
    /**
     * Save PDF to file
     */
    public function save_to_file($invoice_id, $template_id = null, $filepath = null) {
        $pdf_content = $this->get_pdf_string($invoice_id, $template_id);
        
        if (!$filepath) {
            $upload_dir = wp_upload_dir();
            $invoice_dir = $upload_dir['basedir'] . '/invoices';
            
            if (!file_exists($invoice_dir)) {
                wp_mkdir_p($invoice_dir);
            }
            
            $db = IG_Database::get_instance();
            $invoice = $db->get_invoice($invoice_id);
            $filename = 'invoice-' . sanitize_file_name($invoice->invoice_number) . '-' . time() . '.pdf';
            $filepath = $invoice_dir . '/' . $filename;
        }
        
        file_put_contents($filepath, $pdf_content);
        
        return $filepath;
    }
}

