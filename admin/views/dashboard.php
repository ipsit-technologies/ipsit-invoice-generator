<?php
/**
 * Dashboard View
 */
if (!defined('ABSPATH')) {
    exit;
}

$db = IG_Database::get_instance();
$invoices = $db->get_invoices(5);
$clients = $db->get_clients(5);

// Get statistics
$total_invoices = $db->get_total_invoices_count();
$total_clients = $db->get_total_clients_count();
$total_revenue = $db->get_total_revenue('all');
$month_revenue = $db->get_total_revenue('month');
$today_invoices = $db->get_today_invoices_count();
$pending_invoices = $db->get_pending_invoices_count();
$paid_invoices = $db->get_total_invoices_count('paid');

$currency_symbol = get_option('ipsit_ig_currency_symbol', '$');
?>
<div class="wrap">
    <h1><?php echo esc_html__('Invoice Generator Dashboard', 'ipsit-invoice-generator'); ?></h1>
    
    <!-- Statistics Cards -->
    <div class="ig-stats-cards">
        <div class="ig-stat-card ig-stat-card-primary">
            <div class="ig-stat-card-icon">
                <span class="dashicons dashicons-media-document"></span>
            </div>
            <div class="ig-stat-card-content">
                <div class="ig-stat-card-value"><?php echo esc_html(number_format($total_invoices)); ?></div>
                <div class="ig-stat-card-label"><?php echo esc_html__('Total Invoices', 'ipsit-invoice-generator'); ?></div>
            </div>
        </div>
        
        <div class="ig-stat-card ig-stat-card-success">
            <div class="ig-stat-card-icon">
                <span class="dashicons dashicons-money-alt"></span>
            </div>
            <div class="ig-stat-card-content">
                <div class="ig-stat-card-value"><?php echo esc_html($currency_symbol . number_format($month_revenue, 2)); ?></div>
                <div class="ig-stat-card-label"><?php echo esc_html__('Revenue This Month', 'ipsit-invoice-generator'); ?></div>
            </div>
        </div>
        
        <div class="ig-stat-card ig-stat-card-info">
            <div class="ig-stat-card-icon">
                <span class="dashicons dashicons-groups"></span>
            </div>
            <div class="ig-stat-card-content">
                <div class="ig-stat-card-value"><?php echo esc_html(number_format($total_clients)); ?></div>
                <div class="ig-stat-card-label"><?php echo esc_html__('Total Clients', 'ipsit-invoice-generator'); ?></div>
            </div>
        </div>
        
        <div class="ig-stat-card ig-stat-card-warning">
            <div class="ig-stat-card-icon">
                <span class="dashicons dashicons-clock"></span>
            </div>
            <div class="ig-stat-card-content">
                <div class="ig-stat-card-value"><?php echo esc_html(number_format($pending_invoices)); ?></div>
                <div class="ig-stat-card-label"><?php echo esc_html__('Pending Invoices', 'ipsit-invoice-generator'); ?></div>
            </div>
        </div>
    </div>
    
    <div class="ig-dashboard-widgets">
        <div class="ig-widget">
            <div class="ig-widget-header">
                <h2><?php echo esc_html__('Recent Invoices', 'ipsit-invoice-generator'); ?></h2>
                <a href="<?php echo admin_url('admin.php?page=ipsit-ig-invoices'); ?>" class="button button-small"><?php echo esc_html__('View All', 'ipsit-invoice-generator'); ?></a>
            </div>
            <?php if (!empty($invoices)): ?>
                <table class="wp-list-table widefat fixed striped ig-enhanced-table">
                    <thead>
                        <tr>
                            <th><?php echo esc_html__('Invoice #', 'ipsit-invoice-generator'); ?></th>
                            <th><?php echo esc_html__('Client', 'ipsit-invoice-generator'); ?></th>
                            <th><?php echo esc_html__('Date', 'ipsit-invoice-generator'); ?></th>
                            <th><?php echo esc_html__('Total', 'ipsit-invoice-generator'); ?></th>
                            <th><?php echo esc_html__('Status', 'ipsit-invoice-generator'); ?></th>
                            <th><?php echo esc_html__('Actions', 'ipsit-invoice-generator'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($invoices as $invoice): ?>
                            <?php $client = $db->get_client($invoice->client_id); ?>
                            <tr>
                                <td><?php echo esc_html($invoice->invoice_number); ?></td>
                                <td><?php echo $client ? esc_html($client->name) : '-'; ?></td>
                                <td><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($invoice->invoice_date))); ?></td>
                                <td><?php echo esc_html(get_option('ipsit_ig_currency_symbol', '$') . number_format($invoice->total, 2)); ?></td>
                                <td><span class="ig-status ig-status-<?php echo esc_attr($invoice->status); ?>"><?php echo esc_html(ucfirst($invoice->status)); ?></span></td>
                                <td>
                                    <a href="<?php echo admin_url('admin.php?page=ipsit-ig-invoices&action=edit&id=' . $invoice->id); ?>" class="ig-action-button ig-action-edit" title="<?php echo esc_attr__('Edit Invoice', 'ipsit-invoice-generator'); ?>">
                                        <span class="dashicons dashicons-edit"></span>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p><?php echo esc_html__('No invoices found.', 'ipsit-invoice-generator'); ?></p>
            <?php endif; ?>
            <div class="ig-widget-footer">
                <a href="<?php echo admin_url('admin.php?page=ipsit-ig-invoices&action=add'); ?>" class="button button-primary"><?php echo esc_html__('Create New Invoice', 'ipsit-invoice-generator'); ?></a>
            </div>
        </div>
        
        <div class="ig-widget">
            <div class="ig-widget-header">
                <h2><?php echo esc_html__('Recent Clients', 'ipsit-invoice-generator'); ?></h2>
                <a href="<?php echo admin_url('admin.php?page=ipsit-ig-clients'); ?>" class="button button-small"><?php echo esc_html__('View All', 'ipsit-invoice-generator'); ?></a>
            </div>
            <?php if (!empty($clients)): ?>
                <table class="wp-list-table widefat fixed striped ig-enhanced-table">
                    <thead>
                        <tr>
                            <th><?php echo esc_html__('Name', 'ipsit-invoice-generator'); ?></th>
                            <th><?php echo esc_html__('Email', 'ipsit-invoice-generator'); ?></th>
                            <th><?php echo esc_html__('Phone', 'ipsit-invoice-generator'); ?></th>
                            <th><?php echo esc_html__('Actions', 'ipsit-invoice-generator'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($clients as $client): ?>
                            <tr>
                                <td><?php echo esc_html($client->name); ?></td>
                                <td><?php echo esc_html($client->email); ?></td>
                                <td><?php echo esc_html($client->phone); ?></td>
                                <td>
                                    <a href="<?php echo admin_url('admin.php?page=ipsit-ig-clients&action=edit&id=' . $client->id); ?>" class="ig-action-button ig-action-edit" title="<?php echo esc_attr__('Edit Client', 'ipsit-invoice-generator'); ?>">
                                        <span class="dashicons dashicons-edit"></span>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p><?php echo esc_html__('No clients found.', 'ipsit-invoice-generator'); ?></p>
            <?php endif; ?>
            <div class="ig-widget-footer">
                <a href="<?php echo admin_url('admin.php?page=ipsit-ig-clients&action=add'); ?>" class="button button-primary"><?php echo esc_html__('Add New Client', 'ipsit-invoice-generator'); ?></a>
            </div>
        </div>
    </div>
</div>

