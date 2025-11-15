<?php
/**
 * Templates List View
 */

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- These are local variables in a view template, not global variables

if (!defined('ABSPATH')) {
    exit;
}

$db = IG_Database::get_instance();
$templates = $db->get_templates();
?>
<div class="wrap">
    <div class="ig-page-header">
        <h1 class="wp-heading-inline"><?php echo esc_html__('Templates', 'ipsit-invoice-generator'); ?></h1>
        <div class="ig-page-actions">
            <a href="<?php echo esc_url(admin_url('admin.php?page=ipsit-ig-templates&action=builder')); ?>" class="page-title-action ig-button-primary"><?php echo esc_html__('Create Template', 'ipsit-invoice-generator'); ?></a>
            <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=ipsit-ig-templates&ipsit_ig_reload_templates=1'), 'ipsit_ig_reload_templates')); ?>" class="page-title-action"><?php echo esc_html__('Reload Pre-built Templates', 'ipsit-invoice-generator'); ?></a>
        </div>
    </div>
    
    <?php
    // phpcs:disable WordPress.Security.NonceVerification.Recommended -- GET parameter used for display only
    if (isset($_GET['templates_reloaded'])):
    // phpcs:enable WordPress.Security.NonceVerification.Recommended
    ?>
        <div class="notice notice-success is-dismissible">
            <p><?php echo esc_html__('Pre-built templates have been reloaded successfully.', 'ipsit-invoice-generator'); ?></p>
        </div>
    <?php endif; ?>
    
    <!-- Main Content Card -->
    <div class="ig-content-card">
        <!-- Table Container -->
        <div class="ig-table-container">
        <table class="wp-list-table widefat fixed striped ig-enhanced-table">
        <thead>
            <tr>
                <th><?php echo esc_html__('Name', 'ipsit-invoice-generator'); ?></th>
                <th><?php echo esc_html__('Type', 'ipsit-invoice-generator'); ?></th>
                <th><?php echo esc_html__('Default', 'ipsit-invoice-generator'); ?></th>
                <th><?php echo esc_html__('Created', 'ipsit-invoice-generator'); ?></th>
                <th><?php echo esc_html__('Actions', 'ipsit-invoice-generator'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($templates)): ?>
                <?php foreach ($templates as $template): ?>
                    <tr>
                        <td><strong><?php echo esc_html($template->name); ?></strong></td>
                        <td><?php echo esc_html(ucfirst($template->type)); ?></td>
                        <td><?php echo $template->is_default ? esc_html__('Yes', 'ipsit-invoice-generator') : esc_html__('No', 'ipsit-invoice-generator'); ?></td>
                        <td><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($template->created_at))); ?></td>
                        <td>
                            <div class="ig-action-buttons">
                                <a href="<?php echo esc_url(admin_url('admin.php?page=ipsit-ig-templates&action=builder&id=' . $template->id)); ?>" class="ig-action-button ig-action-edit" title="<?php echo esc_attr__('Edit Template', 'ipsit-invoice-generator'); ?>">
                                    <span class="dashicons dashicons-edit"></span>
                                </a>
                                <button type="button" class="ig-action-button ig-action-delete ig-delete-template" data-template-id="<?php echo esc_attr($template->id); ?>" title="<?php echo esc_attr__('Delete Template', 'ipsit-invoice-generator'); ?>">
                                    <span class="dashicons dashicons-trash"></span>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5"><?php echo esc_html__('No templates found.', 'ipsit-invoice-generator'); ?></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
        </div>
    </div>
    
    <div class="ig-content-card">
        <h2><?php echo esc_html__('Pre-built Templates', 'ipsit-invoice-generator'); ?></h2>
        <p><?php echo esc_html__('The plugin includes several pre-built templates that you can use as starting points.', 'ipsit-invoice-generator'); ?></p>
    </div>
</div>

