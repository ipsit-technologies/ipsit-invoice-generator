<?php
/**
 * Invoices List View
 */
if (!defined('ABSPATH')) {
    exit;
}

$db = IG_Database::get_instance();
$status_filter = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
$invoices = $db->get_invoices(50, 0, $status_filter);
?>
<div class="wrap">
    <div class="ig-page-header">
        <h1 class="wp-heading-inline"><?php echo esc_html__('Invoices', 'ipsit-invoice-generator'); ?></h1>
        <a href="<?php echo admin_url('admin.php?page=ipsit-ig-invoices&action=add'); ?>" class="page-title-action ig-button-primary"><?php echo esc_html__('Add New', 'ipsit-invoice-generator'); ?></a>
    </div>
    
    <!-- Main Content Card -->
    <div class="ig-content-card">
        <!-- Filter Buttons -->
        <div class="ig-filter-bar">
            <div class="ig-filter-buttons">
                <a href="<?php echo admin_url('admin.php?page=ipsit-ig-invoices'); ?>" class="ig-filter-button <?php echo empty($status_filter) ? 'ig-filter-active' : ''; ?>">
                    <?php echo esc_html__('All', 'ipsit-invoice-generator'); ?>
                </a>
                <a href="<?php echo admin_url('admin.php?page=ipsit-ig-invoices&status=draft'); ?>" class="ig-filter-button <?php echo $status_filter === 'draft' ? 'ig-filter-active' : ''; ?>">
                    <?php echo esc_html__('Draft', 'ipsit-invoice-generator'); ?>
                </a>
                <a href="<?php echo admin_url('admin.php?page=ipsit-ig-invoices&status=sent'); ?>" class="ig-filter-button <?php echo $status_filter === 'sent' ? 'ig-filter-active' : ''; ?>">
                    <?php echo esc_html__('Sent', 'ipsit-invoice-generator'); ?>
                </a>
                <a href="<?php echo admin_url('admin.php?page=ipsit-ig-invoices&status=paid'); ?>" class="ig-filter-button <?php echo $status_filter === 'paid' ? 'ig-filter-active' : ''; ?>">
                    <?php echo esc_html__('Paid', 'ipsit-invoice-generator'); ?>
                </a>
            </div>
        </div>
        
        <!-- Table Container -->
        <div class="ig-table-container">
        <table class="wp-list-table widefat fixed striped ig-enhanced-table">
        <thead>
            <tr>
                <th><?php echo esc_html__('Invoice #', 'ipsit-invoice-generator'); ?></th>
                <th><?php echo esc_html__('Client', 'ipsit-invoice-generator'); ?></th>
                <th><?php echo esc_html__('Date', 'ipsit-invoice-generator'); ?></th>
                <th><?php echo esc_html__('Due Date', 'ipsit-invoice-generator'); ?></th>
                <th><?php echo esc_html__('Total', 'ipsit-invoice-generator'); ?></th>
                <th><?php echo esc_html__('Status', 'ipsit-invoice-generator'); ?></th>
                <th><?php echo esc_html__('Actions', 'ipsit-invoice-generator'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($invoices)): ?>
                <?php foreach ($invoices as $invoice): ?>
                    <?php $client = $db->get_client($invoice->client_id); ?>
                    <tr>
                        <td><strong><?php echo esc_html($invoice->invoice_number); ?></strong></td>
                        <td><?php echo $client ? esc_html($client->name) : '-'; ?></td>
                        <td><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($invoice->invoice_date))); ?></td>
                        <td><?php 
                            if ($invoice->due_date && $invoice->due_date !== '0000-00-00' && strtotime($invoice->due_date) !== false) {
                                echo esc_html(date_i18n(get_option('date_format'), strtotime($invoice->due_date)));
                            } else {
                                echo '<span style="color: #999; font-style: italic;">—</span>';
                            }
                        ?></td>
                        <td><?php echo esc_html(get_option('ipsit_ig_currency_symbol', '$') . number_format($invoice->total, 2)); ?></td>
                        <td><span class="ig-status ig-status-<?php echo esc_attr($invoice->status); ?>"><?php echo esc_html(ucfirst($invoice->status)); ?></span></td>
                        <td>
                            <div class="ig-action-buttons">
                                <a href="<?php echo admin_url('admin.php?page=ipsit-ig-invoices&action=edit&id=' . $invoice->id); ?>" class="ig-action-button ig-action-edit" title="<?php echo esc_attr__('Edit Invoice', 'ipsit-invoice-generator'); ?>">
                                    <span class="dashicons dashicons-edit"></span>
                                </a>
                                <a href="<?php echo admin_url('admin.php?ipsit_ig_download_pdf=1&invoice_id=' . $invoice->id . '&_wpnonce=' . wp_create_nonce('ipsit_ig_download_pdf')); ?>" class="ig-action-button ig-action-pdf" title="<?php echo esc_attr__('Download PDF', 'ipsit-invoice-generator'); ?>">
                                    <span class="dashicons dashicons-media-document"></span>
                                </a>
                                <button type="button" class="ig-action-button ig-action-delete ig-delete-invoice" data-invoice-id="<?php echo esc_attr($invoice->id); ?>" title="<?php echo esc_attr__('Delete Invoice', 'ipsit-invoice-generator'); ?>">
                                    <span class="dashicons dashicons-trash"></span>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7"><?php echo esc_html__('No invoices found.', 'ipsit-invoice-generator'); ?></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
        </div>
    </div>
</div>

