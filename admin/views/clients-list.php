<?php
/**
 * Clients List View
 */
if (!defined('ABSPATH')) {
    exit;
}

$db = IG_Database::get_instance();
$clients = $db->get_clients(100);
?>
<div class="wrap">
    <div class="ig-page-header">
        <h1 class="wp-heading-inline"><?php echo esc_html__('Clients', 'ipsit-invoice-generator'); ?></h1>
        <a href="<?php echo admin_url('admin.php?page=ipsit-ig-clients&action=add'); ?>" class="page-title-action ig-button-primary"><?php echo esc_html__('Add New', 'ipsit-invoice-generator'); ?></a>
    </div>
    
    <!-- Main Content Card -->
    <div class="ig-content-card">
        <!-- Table Container -->
        <div class="ig-table-container">
        <table class="wp-list-table widefat fixed striped ig-enhanced-table">
        <thead>
            <tr>
                <th><?php echo esc_html__('Name', 'ipsit-invoice-generator'); ?></th>
                <th><?php echo esc_html__('Email', 'ipsit-invoice-generator'); ?></th>
                <th><?php echo esc_html__('Phone', 'ipsit-invoice-generator'); ?></th>
                <th><?php echo esc_html__('Address', 'ipsit-invoice-generator'); ?></th>
                <th><?php echo esc_html__('Actions', 'ipsit-invoice-generator'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($clients)): ?>
                <?php foreach ($clients as $client): ?>
                    <tr>
                        <td><strong><?php echo esc_html($client->name); ?></strong></td>
                        <td><?php echo esc_html($client->email); ?></td>
                        <td><?php echo esc_html($client->phone); ?></td>
                        <td>
                            <?php
                            $address_parts = array();
                            if ($client->address) $address_parts[] = $client->address;
                            if ($client->city) $address_parts[] = $client->city;
                            if ($client->state) $address_parts[] = $client->state;
                            if ($client->zip) $address_parts[] = $client->zip;
                            echo esc_html(implode(', ', $address_parts));
                            ?>
                        </td>
                        <td>
                            <div class="ig-action-buttons">
                                <a href="<?php echo admin_url('admin.php?page=ipsit-ig-clients&action=edit&id=' . $client->id); ?>" class="ig-action-button ig-action-edit" title="<?php echo esc_attr__('Edit Client', 'ipsit-invoice-generator'); ?>">
                                    <span class="dashicons dashicons-edit"></span>
                                </a>
                                <button type="button" class="ig-action-button ig-action-delete ig-delete-client" data-client-id="<?php echo esc_attr($client->id); ?>" title="<?php echo esc_attr__('Delete Client', 'ipsit-invoice-generator'); ?>">
                                    <span class="dashicons dashicons-trash"></span>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5"><?php echo esc_html__('No clients found.', 'ipsit-invoice-generator'); ?></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
        </div>
    </div>
</div>

