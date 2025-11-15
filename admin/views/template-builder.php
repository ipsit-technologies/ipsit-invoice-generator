<?php
/**
 * Template Builder View - Visual Field Manager
 */

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- These are local variables in a view template, not global variables

if (!defined('ABSPATH')) {
    exit;
}

$db = IG_Database::get_instance();
// phpcs:disable WordPress.Security.NonceVerification.Recommended -- GET parameter used for navigation/routing only
$template_id = isset($_GET['id']) ? intval(wp_unslash($_GET['id'])) : 0;
// phpcs:enable WordPress.Security.NonceVerification.Recommended

// Ensure settings column exists
$db->ensure_template_settings_column();

$template = $template_id > 0 ? $db->get_template($template_id) : null;
$invoices = $db->get_invoices(10);
$preview_invoice_id = !empty($invoices) ? $invoices[0]->id : 0;

// Check if this is a project template
$is_project_template = false;
if ($template) {
    $is_project_template = (stripos($template->name, 'project') !== false || $template->type === 'prebuilt' && $template->name === 'Project Based');
    // Also check HTML content for project headers
    if (!$is_project_template && !empty($template->html_content)) {
        $is_project_template = (stripos($template->html_content, 'Project Description') !== false || stripos($template->html_content, 'Project Price') !== false);
    }
}

// Get all clients to fetch custom fields
$clients = $db->get_clients(100);
$all_custom_fields = array();
foreach ($clients as $client) {
    $custom_fields = $db->get_client_fields($client->id);
    foreach ($custom_fields as $field) {
        // Store unique custom fields by name
        $field_key = 'custom_' . sanitize_key($field->field_name);
        if (!isset($all_custom_fields[$field_key])) {
            $all_custom_fields[$field_key] = array(
                'name' => $field->field_name,
                'type' => $field->field_type,
                'variable' => '{client_custom_' . sanitize_key($field->field_name) . '}',
            );
        }
    }
}

// Parse template settings if exists
$template_settings = array();
if ($template && isset($template->settings) && !empty($template->settings)) {
    $settings_json = $template->settings;
    // Handle slashed JSON from database
    $settings_json = wp_unslash($settings_json);
    $template_settings = json_decode($settings_json, true);
    if (!is_array($template_settings) || json_last_error() !== JSON_ERROR_NONE) {
        $template_settings = array();
    }
}

// Default field configurations
$default_bill_from_fields = isset($template_settings['bill_from_fields']) ? $template_settings['bill_from_fields'] : array(
    array('field' => 'company_name', 'label' => 'Company Name', 'enabled' => true),
    array('field' => 'company_address', 'label' => 'Address', 'enabled' => true),
    array('field' => 'company_city', 'label' => 'City', 'enabled' => true),
    array('field' => 'company_state', 'label' => 'State', 'enabled' => true),
    array('field' => 'company_zip', 'label' => 'ZIP', 'enabled' => true),
    array('field' => 'company_phone', 'label' => 'Phone', 'enabled' => true),
    array('field' => 'company_email', 'label' => 'Email', 'enabled' => true),
);

$default_bill_to_fields = isset($template_settings['bill_to_fields']) ? $template_settings['bill_to_fields'] : array(
    array('field' => 'client_name', 'label' => 'Client Name', 'enabled' => true),
    array('field' => 'client_email', 'label' => 'Email', 'enabled' => true),
    array('field' => 'client_phone', 'label' => 'Phone', 'enabled' => true),
    array('field' => 'client_address', 'label' => 'Address', 'enabled' => true),
    array('field' => 'client_city', 'label' => 'City', 'enabled' => false),
    array('field' => 'client_state', 'label' => 'State', 'enabled' => false),
    array('field' => 'client_zip', 'label' => 'ZIP', 'enabled' => false),
);

// Available fields
$available_company_fields = array(
    'company_name' => 'Company Name',
    'company_email' => 'Email',
    'company_phone' => 'Phone',
    'company_address' => 'Address',
    'company_city' => 'City',
    'company_state' => 'State',
    'company_zip' => 'ZIP',
    'company_country' => 'Country',
    'company_tax_id' => 'Tax ID',
    'company_website' => 'Website',
);

$available_client_fields = array(
    'client_name' => 'Client Name',
    'client_email' => 'Email',
    'client_phone' => 'Phone',
    'client_address' => 'Address',
    'client_city' => 'City',
    'client_state' => 'State',
    'client_zip' => 'ZIP',
    'client_country' => 'Country',
);
?>
<div class="wrap">
    <h1><?php echo $template_id > 0 ? esc_html__('Edit Template', 'ipsit-invoice-generator') : esc_html__('Create Template', 'ipsit-invoice-generator'); ?></h1>
    
    <form id="ig-template-form" method="post">
        <?php wp_nonce_field('ig_admin_nonce', 'nonce'); ?>
        <input type="hidden" name="template_id" value="<?php echo esc_attr($template_id); ?>">
        <input type="hidden" name="html_content" id="generated_html_content" value="<?php echo $template ? esc_attr($template->html_content) : ''; ?>">
        <input type="hidden" name="css_content" id="generated_css_content" value="<?php echo $template ? esc_attr($template->css_content) : ''; ?>">
        <input type="hidden" name="template_settings" id="template_settings" value="<?php echo esc_attr(json_encode($template_settings)); ?>">
        <input type="hidden" id="is_project_template" value="<?php echo $is_project_template ? '1' : '0'; ?>">
        
        <div class="ig-form-row">
            <label for="template_name"><?php echo esc_html__('Template Name', 'ipsit-invoice-generator'); ?></label>
            <input type="text" name="name" id="template_name" value="<?php echo $template ? esc_attr($template->name) : ''; ?>" class="regular-text" required>
        </div>
        
        <div class="ig-form-row">
            <label>
                <input type="checkbox" name="is_default" value="1" <?php checked($template && $template->is_default); ?>>
                <?php echo esc_html__('Set as default template', 'ipsit-invoice-generator'); ?>
            </label>
        </div>
        
        <h2><?php echo esc_html__('Bill From Section', 'ipsit-invoice-generator'); ?></h2>
        <p class="description"><?php echo esc_html__('Select which fields to display and their order', 'ipsit-invoice-generator'); ?></p>
        
        <div class="ig-field-manager" id="bill-from-fields">
            <div class="ig-available-fields">
                <h3><?php echo esc_html__('Available Fields', 'ipsit-invoice-generator'); ?></h3>
                <ul class="ig-field-list" id="bill-from-available">
                    <?php foreach ($available_company_fields as $field_key => $field_label): ?>
                        <?php 
                        $field_exists = false;
                        // Only check if field is enabled (exists and enabled = true)
                        foreach ($default_bill_from_fields as $existing_field) {
                            if ($existing_field['field'] === $field_key && isset($existing_field['enabled']) && $existing_field['enabled']) {
                                $field_exists = true;
                                break;
                            }
                        }
                        if (!$field_exists):
                        ?>
                            <li class="ig-field-item" data-field="<?php echo esc_attr($field_key); ?>" data-label="<?php echo esc_attr($field_label); ?>">
                                <span class="dashicons dashicons-plus-alt"></span>
                                <?php echo esc_html($field_label); ?>
                            </li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
            </div>
            
            <div class="ig-selected-fields">
                <h3><?php echo esc_html__('Selected Fields (Drag to reorder)', 'ipsit-invoice-generator'); ?></h3>
                <ul class="ig-field-list ig-sortable" id="bill-from-selected">
                    <?php foreach ($default_bill_from_fields as $index => $field): ?>
                        <?php if (isset($field['enabled']) && $field['enabled']): ?>
                            <?php 
                            $field_key = isset($field['field']) ? $field['field'] : '';
                            $field_label = isset($field['label']) ? $field['label'] : '';
                            ?>
                            <li class="ig-field-item ig-selected" data-field="<?php echo esc_attr($field_key); ?>" data-label="<?php echo esc_attr($field_label); ?>">
                                <span class="dashicons dashicons-menu"></span>
                                <input type="text" class="ig-field-label" value="<?php echo esc_attr($field_label); ?>" placeholder="Field Label">
                                <span class="ig-field-name"><?php echo esc_html($field_key); ?></span>
                                <button type="button" class="button button-small ig-remove-field"><?php echo esc_html__('Remove', 'ipsit-invoice-generator'); ?></button>
                            </li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        
        <h2><?php echo esc_html__('Bill To Section', 'ipsit-invoice-generator'); ?></h2>
        <p class="description"><?php echo esc_html__('Select which fields to display and their order', 'ipsit-invoice-generator'); ?></p>
        
        <div class="ig-field-manager" id="bill-to-fields">
            <div class="ig-available-fields">
                <h3><?php echo esc_html__('Available Fields', 'ipsit-invoice-generator'); ?></h3>
                <ul class="ig-field-list" id="bill-to-available">
                    <?php foreach ($available_client_fields as $field_key => $field_label): ?>
                        <?php 
                        $field_exists = false;
                        // Only check if field is enabled (exists and enabled = true)
                        foreach ($default_bill_to_fields as $existing_field) {
                            if ($existing_field['field'] === $field_key && isset($existing_field['enabled']) && $existing_field['enabled']) {
                                $field_exists = true;
                                break;
                            }
                        }
                        if (!$field_exists):
                        ?>
                            <li class="ig-field-item" data-field="<?php echo esc_attr($field_key); ?>" data-label="<?php echo esc_attr($field_label); ?>">
                                <span class="dashicons dashicons-plus-alt"></span>
                                <?php echo esc_html($field_label); ?>
                            </li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    
                    <?php if (!empty($all_custom_fields)): ?>
                        <h4 class="ig-section-header"><?php echo esc_html__('Custom Fields', 'ipsit-invoice-generator'); ?></h4>
                        <?php foreach ($all_custom_fields as $custom_field): ?>
                            <?php 
                            $field_key = 'custom_' . sanitize_key($custom_field['name']);
                            $field_exists = false;
                            // Only check if field is enabled (exists and enabled = true)
                            foreach ($default_bill_to_fields as $existing_field) {
                                if (isset($existing_field['field']) && $existing_field['field'] === $field_key && isset($existing_field['enabled']) && $existing_field['enabled']) {
                                    $field_exists = true;
                                    break;
                                }
                            }
                            if (!$field_exists):
                            ?>
                                <li class="ig-field-item ig-custom-field" data-field="<?php echo esc_attr($field_key); ?>" data-label="<?php echo esc_attr($custom_field['name']); ?>" data-variable="<?php echo esc_attr($custom_field['variable']); ?>">
                                    <span class="dashicons dashicons-plus-alt"></span>
                                    <?php echo esc_html($custom_field['name']); ?> <small>(<?php echo esc_html($custom_field['type']); ?>)</small>
                                </li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </div>
            
            <div class="ig-selected-fields">
                <h3><?php echo esc_html__('Selected Fields (Drag to reorder)', 'ipsit-invoice-generator'); ?></h3>
                <ul class="ig-field-list ig-sortable" id="bill-to-selected">
                    <?php foreach ($default_bill_to_fields as $index => $field): ?>
                        <?php if (isset($field['enabled']) && $field['enabled']): ?>
                            <?php 
                            $field_key = isset($field['field']) ? $field['field'] : '';
                            $field_label = isset($field['label']) ? $field['label'] : '';
                            $field_variable = isset($field['variable']) ? $field['variable'] : '';
                            ?>
                            <li class="ig-field-item ig-selected" data-field="<?php echo esc_attr($field_key); ?>" data-label="<?php echo esc_attr($field_label); ?>" <?php if ($field_variable): ?>data-variable="<?php echo esc_attr($field_variable); ?>"<?php endif; ?>>
                                <span class="dashicons dashicons-menu"></span>
                                <input type="text" class="ig-field-label" value="<?php echo esc_attr($field_label); ?>" placeholder="Field Label">
                                <span class="ig-field-name"><?php echo esc_html($field_key); ?></span>
                                <button type="button" class="button button-small ig-remove-field"><?php echo esc_html__('Remove', 'ipsit-invoice-generator'); ?></button>
                            </li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        
        <p class="submit">
            <button type="submit" class="button button-primary"><?php echo esc_html__('Save Template', 'ipsit-invoice-generator'); ?></button>
            <?php if ($preview_invoice_id > 0): ?>
                <button type="button" id="ig-preview-template" class="button" data-invoice-id="<?php echo esc_attr($preview_invoice_id); ?>"><?php echo esc_html__('Preview', 'ipsit-invoice-generator'); ?></button>
            <?php endif; ?>
            <a href="<?php echo esc_url(admin_url('admin.php?page=ipsit-ig-templates')); ?>" class="button"><?php echo esc_html__('Cancel', 'ipsit-invoice-generator'); ?></a>
        </p>
    </form>
</div>

<div id="ig-preview-modal" class="ig-modal">
    <div class="ig-modal-content ig-modal-large">
        <span class="ig-modal-close">&times;</span>
        <h2><?php echo esc_html__('Template Preview', 'ipsit-invoice-generator'); ?></h2>
        <div id="ig-preview-modal-content"></div>
    </div>
</div>

<style>
.ig-modal {
    display: none;
    position: fixed;
    z-index: 100000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0,0,0,0.4);
}
.ig-modal-content {
    background-color: #fefefe;
    margin: 5% auto;
    padding: 20px;
    border: 1px solid #888;
    width: 90%;
    max-width: 800px;
    position: relative;
}
.ig-modal-close {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
    position: absolute;
    right: 15px;
    top: 10px;
}
.ig-modal-close:hover,
.ig-modal-close:focus {
    color: #000;
}
#ig-preview-modal-content {
    margin-top: 20px;
    padding: 20px;
    background: #fff;
    border: 1px solid #ddd;
}
</style>
