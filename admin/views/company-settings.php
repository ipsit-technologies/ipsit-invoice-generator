<?php
/**
 * Company Settings View
 */

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- These are local variables in a view template, not global variables

if (!defined('ABSPATH')) {
    exit;
}

$db = IG_Database::get_instance();
$company = $db->get_company();
?>
<div class="wrap">
    <h1><?php echo esc_html__('Company Settings', 'ipsit-invoice-generator'); ?></h1>
    
    <form id="ig-company-form" method="post" enctype="multipart/form-data">
        <?php wp_nonce_field('ig_admin_nonce', 'nonce'); ?>
        
        <h2><?php echo esc_html__('Company Information', 'ipsit-invoice-generator'); ?></h2>
        
        <table class="form-table">
            <tr>
                <th><label for="name"><?php echo esc_html__('Company Name', 'ipsit-invoice-generator'); ?> <span class="required">*</span></label></th>
                <td><input type="text" name="name" id="name" value="<?php echo $company ? esc_attr($company->name ?? '') : ''; ?>" class="regular-text" required></td>
            </tr>
            <tr>
                <th><label for="email"><?php echo esc_html__('Email', 'ipsit-invoice-generator'); ?></label></th>
                <td><input type="email" name="email" id="email" value="<?php echo $company ? esc_attr($company->email ?? '') : ''; ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th><label for="phone"><?php echo esc_html__('Phone', 'ipsit-invoice-generator'); ?></label></th>
                <td><input type="text" name="phone" id="phone" value="<?php echo $company ? esc_attr($company->phone ?? '') : ''; ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th><label for="address"><?php echo esc_html__('Address', 'ipsit-invoice-generator'); ?></label></th>
                <td><textarea name="address" id="address" rows="3" class="large-text"><?php echo $company ? esc_textarea($company->address ?? '') : ''; ?></textarea></td>
            </tr>
            <tr>
                <th><label for="city"><?php echo esc_html__('City', 'ipsit-invoice-generator'); ?></label></th>
                <td><input type="text" name="city" id="city" value="<?php echo $company ? esc_attr($company->city ?? '') : ''; ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th><label for="state"><?php echo esc_html__('State/Province', 'ipsit-invoice-generator'); ?></label></th>
                <td><input type="text" name="state" id="state" value="<?php echo $company ? esc_attr($company->state ?? '') : ''; ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th><label for="zip"><?php echo esc_html__('ZIP/Postal Code', 'ipsit-invoice-generator'); ?></label></th>
                <td><input type="text" name="zip" id="zip" value="<?php echo $company ? esc_attr($company->zip ?? '') : ''; ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th><label for="country"><?php echo esc_html__('Country', 'ipsit-invoice-generator'); ?></label></th>
                <td><input type="text" name="country" id="country" value="<?php echo $company ? esc_attr($company->country ?? '') : ''; ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th><label for="tax_id"><?php echo esc_html__('Tax ID', 'ipsit-invoice-generator'); ?></label></th>
                <td><input type="text" name="tax_id" id="tax_id" value="<?php echo $company ? esc_attr($company->tax_id ?? '') : ''; ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th><label for="website"><?php echo esc_html__('Website', 'ipsit-invoice-generator'); ?></label></th>
                <td><input type="url" name="website" id="website" value="<?php echo $company ? esc_attr($company->website ?? '') : ''; ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th><label for="logo"><?php echo esc_html__('Logo', 'ipsit-invoice-generator'); ?></label></th>
                <td>
                    <?php if ($company && $company->logo): ?>
                        <img src="<?php echo esc_url($company->logo); ?>" alt="Logo" class="ig-logo-preview">
                    <?php endif; ?>
                    <input type="file" name="logo" id="logo" accept="image/*">
                    <p class="description"><?php echo esc_html__('Upload your company logo. Recommended size: 200x100px', 'ipsit-invoice-generator'); ?></p>
                </td>
            </tr>
        </table>
        
        <h2><?php echo esc_html__('Default Payment Method', 'ipsit-invoice-generator'); ?></h2>
        <p class="description"><?php echo esc_html__('Set default bank transfer details. These will be pre-filled when creating invoices, but can be changed per invoice.', 'ipsit-invoice-generator'); ?></p>
        
        <table class="form-table">
            <tr>
                <th><label for="default_payment_method"><?php echo esc_html__('Enable Bank Transfer', 'ipsit-invoice-generator'); ?></label></th>
                <td>
                    <label>
                        <input type="checkbox" name="default_payment_method" id="default_payment_method" value="bank_transfer" <?php checked($company && $company->default_payment_method === 'bank_transfer'); ?>>
                        <?php echo esc_html__('Use Bank Transfer as default payment method', 'ipsit-invoice-generator'); ?>
                    </label>
                </td>
            </tr>
        </table>
        
        <div id="company-payment-method-fields" class="<?php echo ($company && $company->default_payment_method === 'bank_transfer') ? '' : 'ig-hidden'; ?>">
            <table class="form-table">
                <tr>
                    <th><label for="default_bank_name"><?php echo esc_html__('Bank Name', 'ipsit-invoice-generator'); ?></label></th>
                    <td><input type="text" name="default_bank_name" id="default_bank_name" value="<?php echo $company ? esc_attr($company->default_bank_name ?? '') : ''; ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th><label for="default_account_number"><?php echo esc_html__('Account Number', 'ipsit-invoice-generator'); ?></label></th>
                    <td><input type="text" name="default_account_number" id="default_account_number" value="<?php echo $company ? esc_attr($company->default_account_number ?? '') : ''; ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th><label for="default_account_title"><?php echo esc_html__('Account Title', 'ipsit-invoice-generator'); ?></label></th>
                    <td><input type="text" name="default_account_title" id="default_account_title" value="<?php echo $company ? esc_attr($company->default_account_title ?? '') : ''; ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th><label for="default_account_branch"><?php echo esc_html__('Account Branch', 'ipsit-invoice-generator'); ?></label></th>
                    <td><input type="text" name="default_account_branch" id="default_account_branch" value="<?php echo $company ? esc_attr($company->default_account_branch ?? '') : ''; ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th><label for="default_iban"><?php echo esc_html__('IBAN', 'ipsit-invoice-generator'); ?></label></th>
                    <td><input type="text" name="default_iban" id="default_iban" value="<?php echo $company ? esc_attr($company->default_iban ?? '') : ''; ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th><label for="default_ifsc_code"><?php echo esc_html__('IFSC Code', 'ipsit-invoice-generator'); ?></label></th>
                    <td><input type="text" name="default_ifsc_code" id="default_ifsc_code" value="<?php echo $company ? esc_attr($company->default_ifsc_code ?? '') : ''; ?>" class="regular-text"></td>
                </tr>
            </table>
        </div>
        
        <p class="submit">
            <button type="submit" class="button button-primary"><?php echo esc_html__('Save Settings', 'ipsit-invoice-generator'); ?></button>
        </p>
    </form>
</div>

