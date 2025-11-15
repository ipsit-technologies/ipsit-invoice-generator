<?php
/**
 * Client Form View
 */

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- These are local variables in a view template, not global variables

if (!defined('ABSPATH')) {
    exit;
}

$db = IG_Database::get_instance();
// phpcs:disable WordPress.Security.NonceVerification.Recommended -- GET parameter used for navigation/routing only
$client_id = isset($_GET['id']) ? intval(wp_unslash($_GET['id'])) : 0;
// phpcs:enable WordPress.Security.NonceVerification.Recommended
$client = $client_id > 0 ? $db->get_client($client_id) : null;
$custom_fields = $client_id > 0 ? $db->get_client_fields($client_id) : array();
?>
<div class="wrap">
    <h1><?php echo $client_id > 0 ? esc_html__('Edit Client', 'ipsit-invoice-generator') : esc_html__('Add New Client', 'ipsit-invoice-generator'); ?></h1>
    
    <form id="ig-client-form" method="post">
        <?php wp_nonce_field('ig_admin_nonce', 'nonce'); ?>
        <input type="hidden" name="client_id" value="<?php echo esc_attr($client_id); ?>">
        
        <h2><?php echo esc_html__('Basic Information', 'ipsit-invoice-generator'); ?></h2>
        
        <table class="form-table">
            <tr>
                <th><label for="name"><?php echo esc_html__('Name', 'ipsit-invoice-generator'); ?> <span class="required">*</span></label></th>
                <td><input type="text" name="name" id="name" value="<?php echo $client ? esc_attr($client->name ?? '') : ''; ?>" class="regular-text" required></td>
            </tr>
            <tr>
                <th><label for="email"><?php echo esc_html__('Email', 'ipsit-invoice-generator'); ?></label></th>
                <td><input type="email" name="email" id="email" value="<?php echo $client ? esc_attr($client->email ?? '') : ''; ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th><label for="phone"><?php echo esc_html__('Phone', 'ipsit-invoice-generator'); ?></label></th>
                <td><input type="text" name="phone" id="phone" value="<?php echo $client ? esc_attr($client->phone ?? '') : ''; ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th><label for="address"><?php echo esc_html__('Address', 'ipsit-invoice-generator'); ?></label></th>
                <td><textarea name="address" id="address" rows="3" class="large-text"><?php echo $client ? esc_textarea($client->address ?? '') : ''; ?></textarea></td>
            </tr>
            <tr>
                <th><label for="city"><?php echo esc_html__('City', 'ipsit-invoice-generator'); ?></label></th>
                <td><input type="text" name="city" id="city" value="<?php echo $client ? esc_attr($client->city ?? '') : ''; ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th><label for="state"><?php echo esc_html__('State/Province', 'ipsit-invoice-generator'); ?></label></th>
                <td><input type="text" name="state" id="state" value="<?php echo $client ? esc_attr($client->state ?? '') : ''; ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th><label for="zip"><?php echo esc_html__('ZIP/Postal Code', 'ipsit-invoice-generator'); ?></label></th>
                <td><input type="text" name="zip" id="zip" value="<?php echo $client ? esc_attr($client->zip ?? '') : ''; ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th><label for="country"><?php echo esc_html__('Country', 'ipsit-invoice-generator'); ?></label></th>
                <td><input type="text" name="country" id="country" value="<?php echo $client ? esc_attr($client->country ?? '') : ''; ?>" class="regular-text"></td>
            </tr>
        </table>
        
        <h2><?php echo esc_html__('Custom Fields', 'ipsit-invoice-generator'); ?></h2>
        <div id="ig-custom-fields">
            <?php if (!empty($custom_fields)): ?>
                <?php foreach ($custom_fields as $field): ?>
                    <div class="ig-custom-field-row">
                        <input type="hidden" name="custom_fields[<?php echo esc_attr($field->id); ?>][field_id]" value="<?php echo esc_attr($field->id); ?>">
                        <select name="custom_fields[<?php echo esc_attr($field->id); ?>][field_type]" class="ig-field-type">
                            <option value="text" <?php selected($field->field_type, 'text'); ?>><?php echo esc_html__('Text', 'ipsit-invoice-generator'); ?></option>
                            <option value="number" <?php selected($field->field_type, 'number'); ?>><?php echo esc_html__('Number', 'ipsit-invoice-generator'); ?></option>
                            <option value="email" <?php selected($field->field_type, 'email'); ?>><?php echo esc_html__('Email', 'ipsit-invoice-generator'); ?></option>
                            <option value="date" <?php selected($field->field_type, 'date'); ?>><?php echo esc_html__('Date', 'ipsit-invoice-generator'); ?></option>
                            <option value="textarea" <?php selected($field->field_type, 'textarea'); ?>><?php echo esc_html__('Textarea', 'ipsit-invoice-generator'); ?></option>
                            <option value="dropdown" <?php selected($field->field_type, 'dropdown'); ?>><?php echo esc_html__('Dropdown', 'ipsit-invoice-generator'); ?></option>
                            <option value="checkbox" <?php selected($field->field_type, 'checkbox'); ?>><?php echo esc_html__('Checkbox', 'ipsit-invoice-generator'); ?></option>
                        </select>
                        <input type="text" name="custom_fields[<?php echo esc_attr($field->id); ?>][field_name]" value="<?php echo esc_attr($field->field_name); ?>" placeholder="<?php echo esc_attr__('Field Name', 'ipsit-invoice-generator'); ?>" class="regular-text">
                        <input type="text" name="custom_fields[<?php echo esc_attr($field->id); ?>][field_value]" value="<?php echo esc_attr($field->field_value); ?>" placeholder="<?php echo esc_attr__('Field Value', 'ipsit-invoice-generator'); ?>" class="regular-text">
                        <button type="button" class="button ig-remove-field"><?php echo esc_html__('Remove', 'ipsit-invoice-generator'); ?></button>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <p>
            <button type="button" id="ig-add-custom-field" class="button"><?php echo esc_html__('Add Custom Field', 'ipsit-invoice-generator'); ?></button>
        </p>
        
        <p class="submit">
            <button type="submit" class="button button-primary"><?php echo esc_html__('Save Client', 'ipsit-invoice-generator'); ?></button>
            <a href="<?php echo esc_url(admin_url('admin.php?page=ipsit-ig-clients')); ?>" class="button"><?php echo esc_html__('Cancel', 'ipsit-invoice-generator'); ?></a>
        </p>
    </form>
</div>

<script type="text/html" id="ig-custom-field-template">
    <div class="ig-custom-field-row">
        <select name="custom_fields[new][field_type]" class="ig-field-type">
            <option value="text"><?php echo esc_html__('Text', 'ipsit-invoice-generator'); ?></option>
            <option value="number"><?php echo esc_html__('Number', 'ipsit-invoice-generator'); ?></option>
            <option value="email"><?php echo esc_html__('Email', 'ipsit-invoice-generator'); ?></option>
            <option value="date"><?php echo esc_html__('Date', 'ipsit-invoice-generator'); ?></option>
            <option value="textarea"><?php echo esc_html__('Textarea', 'ipsit-invoice-generator'); ?></option>
            <option value="dropdown"><?php echo esc_html__('Dropdown', 'ipsit-invoice-generator'); ?></option>
            <option value="checkbox"><?php echo esc_html__('Checkbox', 'ipsit-invoice-generator'); ?></option>
        </select>
        <input type="text" name="custom_fields[new][field_name]" placeholder="<?php echo esc_attr__('Field Name', 'ipsit-invoice-generator'); ?>" class="regular-text">
        <input type="text" name="custom_fields[new][field_value]" placeholder="<?php echo esc_attr__('Field Value', 'ipsit-invoice-generator'); ?>" class="regular-text">
        <button type="button" class="button ig-remove-field"><?php echo esc_html__('Remove', 'ipsit-invoice-generator'); ?></button>
    </div>
</script>

