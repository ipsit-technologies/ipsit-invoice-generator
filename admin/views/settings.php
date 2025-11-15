<?php
/**
 * Settings View
 */

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- These are local variables in a view template, not global variables

if (!defined('ABSPATH')) {
    exit;
}

$db = IG_Database::get_instance();
$templates = $db->get_templates();
?>
<div class="wrap">
    <h1><?php echo esc_html__('Invoice Generator Settings', 'ipsit-invoice-generator'); ?></h1>
    
    <form id="ig-settings-form" method="post">
        <?php wp_nonce_field('ig_admin_nonce', 'nonce'); ?>
        
        <h2><?php echo esc_html__('Invoice Numbering', 'ipsit-invoice-generator'); ?></h2>
        <table class="form-table">
            <tr>
                <th><label for="invoice_number_prefix"><?php echo esc_html__('Prefix', 'ipsit-invoice-generator'); ?></label></th>
                <td>
                    <input type="text" name="invoice_number_prefix" id="invoice_number_prefix" value="<?php echo esc_attr(get_option('ipsit_ig_invoice_number_prefix', 'INV-')); ?>" class="regular-text">
                    <p class="description"><?php echo esc_html__('Prefix for invoice numbers (e.g., INV-)', 'ipsit-invoice-generator'); ?></p>
                </td>
            </tr>
            <tr>
                <th><label for="invoice_number_suffix"><?php echo esc_html__('Suffix', 'ipsit-invoice-generator'); ?></label></th>
                <td>
                    <input type="text" name="invoice_number_suffix" id="invoice_number_suffix" value="<?php echo esc_attr(get_option('ipsit_ig_invoice_number_suffix', '')); ?>" class="regular-text">
                    <p class="description"><?php echo esc_html__('Suffix for invoice numbers (e.g., -2024)', 'ipsit-invoice-generator'); ?></p>
                </td>
            </tr>
            <tr>
                <th><label for="invoice_number_padding"><?php echo esc_html__('Number Padding', 'ipsit-invoice-generator'); ?></label></th>
                <td>
                    <input type="number" name="invoice_number_padding" id="invoice_number_padding" value="<?php echo esc_attr(get_option('ipsit_ig_invoice_number_padding', 4)); ?>" min="1" max="10" class="small-text">
                    <p class="description"><?php echo esc_html__('Number of digits for invoice numbers (e.g., 4 = 0001, 5 = 00001)', 'ipsit-invoice-generator'); ?></p>
                </td>
            </tr>
        </table>
        
        <h2><?php echo esc_html__('Currency Settings', 'ipsit-invoice-generator'); ?></h2>
        <table class="form-table">
            <tr>
                <th><label for="currency"><?php echo esc_html__('Currency Code', 'ipsit-invoice-generator'); ?></label></th>
                <td>
                    <select name="currency" id="currency" class="regular-text">
                        <option value="USD" <?php selected(get_option('ipsit_ig_currency', 'USD'), 'USD'); ?>>USD - US Dollar</option>
                        <option value="EUR" <?php selected(get_option('ipsit_ig_currency', 'USD'), 'EUR'); ?>>EUR - Euro</option>
                        <option value="GBP" <?php selected(get_option('ipsit_ig_currency', 'USD'), 'GBP'); ?>>GBP - British Pound</option>
                        <option value="JPY" <?php selected(get_option('ipsit_ig_currency', 'USD'), 'JPY'); ?>>JPY - Japanese Yen</option>
                        <option value="AUD" <?php selected(get_option('ipsit_ig_currency', 'USD'), 'AUD'); ?>>AUD - Australian Dollar</option>
                        <option value="CAD" <?php selected(get_option('ipsit_ig_currency', 'USD'), 'CAD'); ?>>CAD - Canadian Dollar</option>
                        <option value="CHF" <?php selected(get_option('ipsit_ig_currency', 'USD'), 'CHF'); ?>>CHF - Swiss Franc</option>
                        <option value="CNY" <?php selected(get_option('ipsit_ig_currency', 'USD'), 'CNY'); ?>>CNY - Chinese Yuan</option>
                        <option value="INR" <?php selected(get_option('ipsit_ig_currency', 'USD'), 'INR'); ?>>INR - Indian Rupee</option>
                        <option value="SGD" <?php selected(get_option('ipsit_ig_currency', 'USD'), 'SGD'); ?>>SGD - Singapore Dollar</option>
                        <option value="NZD" <?php selected(get_option('ipsit_ig_currency', 'USD'), 'NZD'); ?>>NZD - New Zealand Dollar</option>
                        <option value="MXN" <?php selected(get_option('ipsit_ig_currency', 'USD'), 'MXN'); ?>>MXN - Mexican Peso</option>
                        <option value="BRL" <?php selected(get_option('ipsit_ig_currency', 'USD'), 'BRL'); ?>>BRL - Brazilian Real</option>
                        <option value="ZAR" <?php selected(get_option('ipsit_ig_currency', 'USD'), 'ZAR'); ?>>ZAR - South African Rand</option>
                        <option value="RUB" <?php selected(get_option('ipsit_ig_currency', 'USD'), 'RUB'); ?>>RUB - Russian Ruble</option>
                        <option value="KRW" <?php selected(get_option('ipsit_ig_currency', 'USD'), 'KRW'); ?>>KRW - South Korean Won</option>
                        <option value="TRY" <?php selected(get_option('ipsit_ig_currency', 'USD'), 'TRY'); ?>>TRY - Turkish Lira</option>
                        <option value="AED" <?php selected(get_option('ipsit_ig_currency', 'USD'), 'AED'); ?>>AED - UAE Dirham</option>
                        <option value="SAR" <?php selected(get_option('ipsit_ig_currency', 'USD'), 'SAR'); ?>>SAR - Saudi Riyal</option>
                    </select>
                    <p class="description"><?php echo esc_html__('ISO currency code', 'ipsit-invoice-generator'); ?></p>
                </td>
            </tr>
            <tr>
                <th><label for="currency_symbol"><?php echo esc_html__('Currency Symbol', 'ipsit-invoice-generator'); ?></label></th>
                <td>
                    <select name="currency_symbol" id="currency_symbol" class="regular-text">
                        <option value="$" <?php selected(get_option('ipsit_ig_currency_symbol', '$'), '$'); ?>>$ - Dollar</option>
                        <option value="€" <?php selected(get_option('ipsit_ig_currency_symbol', '$'), '€'); ?>>€ - Euro</option>
                        <option value="£" <?php selected(get_option('ipsit_ig_currency_symbol', '$'), '£'); ?>>£ - Pound</option>
                        <option value="¥" <?php selected(get_option('ipsit_ig_currency_symbol', '$'), '¥'); ?>>¥ - Yen/Yuan</option>
                        <option value="₹" <?php selected(get_option('ipsit_ig_currency_symbol', '$'), '₹'); ?>>₹ - Rupee</option>
                        <option value="A$" <?php selected(get_option('ipsit_ig_currency_symbol', '$'), 'A$'); ?>>A$ - Australian Dollar</option>
                        <option value="C$" <?php selected(get_option('ipsit_ig_currency_symbol', '$'), 'C$'); ?>>C$ - Canadian Dollar</option>
                        <option value="CHF" <?php selected(get_option('ipsit_ig_currency_symbol', '$'), 'CHF'); ?>>CHF - Swiss Franc</option>
                        <option value="R" <?php selected(get_option('ipsit_ig_currency_symbol', '$'), 'R'); ?>>R - Rand</option>
                        <option value="R$" <?php selected(get_option('ipsit_ig_currency_symbol', '$'), 'R$'); ?>>R$ - Brazilian Real</option>
                        <option value="₽" <?php selected(get_option('ipsit_ig_currency_symbol', '$'), '₽'); ?>>₽ - Ruble</option>
                        <option value="₩" <?php selected(get_option('ipsit_ig_currency_symbol', '$'), '₩'); ?>>₩ - Won</option>
                        <option value="₺" <?php selected(get_option('ipsit_ig_currency_symbol', '$'), '₺'); ?>>₺ - Lira</option>
                        <option value="د.إ" <?php selected(get_option('ipsit_ig_currency_symbol', '$'), 'د.إ'); ?>>د.إ - Dirham</option>
                        <option value="ر.س" <?php selected(get_option('ipsit_ig_currency_symbol', '$'), 'ر.س'); ?>>ر.س - Riyal</option>
                        <option value="S$" <?php selected(get_option('ipsit_ig_currency_symbol', '$'), 'S$'); ?>>S$ - Singapore Dollar</option>
                        <option value="NZ$" <?php selected(get_option('ipsit_ig_currency_symbol', '$'), 'NZ$'); ?>>NZ$ - New Zealand Dollar</option>
                        <option value="Mex$" <?php selected(get_option('ipsit_ig_currency_symbol', '$'), 'Mex$'); ?>>Mex$ - Mexican Peso</option>
                    </select>
                    <p class="description"><?php echo esc_html__('Currency symbol to display', 'ipsit-invoice-generator'); ?></p>
                </td>
            </tr>
        </table>
        
        <h2><?php echo esc_html__('Date Format', 'ipsit-invoice-generator'); ?></h2>
        <table class="form-table">
            <tr>
                <th><label for="date_format"><?php echo esc_html__('Date Format', 'ipsit-invoice-generator'); ?></label></th>
                <td>
                    <input type="text" name="date_format" id="date_format" value="<?php echo esc_attr(get_option('ipsit_ig_date_format', 'Y-m-d')); ?>" class="regular-text">
                    <p class="description"><?php echo esc_html__('PHP date format (e.g., Y-m-d for 2024-01-15)', 'ipsit-invoice-generator'); ?></p>
                </td>
            </tr>
        </table>
        
        <h2><?php echo esc_html__('Email Settings', 'ipsit-invoice-generator'); ?></h2>
        <table class="form-table">
            <tr>
                <th><label for="email_from_name"><?php echo esc_html__('From Name', 'ipsit-invoice-generator'); ?></label></th>
                <td>
                    <input type="text" name="email_from_name" id="email_from_name" value="<?php echo esc_attr(get_option('ipsit_ig_email_from_name', get_bloginfo('name'))); ?>" class="regular-text">
                    <p class="description"><?php echo esc_html__('Name to use as sender', 'ipsit-invoice-generator'); ?></p>
                </td>
            </tr>
            <tr>
                <th><label for="email_from_email"><?php echo esc_html__('From Email', 'ipsit-invoice-generator'); ?></label></th>
                <td>
                    <input type="email" name="email_from_email" id="email_from_email" value="<?php echo esc_attr(get_option('ipsit_ig_email_from_email', get_option('admin_email'))); ?>" class="regular-text">
                    <p class="description"><?php echo esc_html__('Email address to use as sender', 'ipsit-invoice-generator'); ?></p>
                </td>
            </tr>
            <tr>
                <th><label for="email_subject"><?php echo esc_html__('Email Subject Template', 'ipsit-invoice-generator'); ?></label></th>
                <td>
                    <input type="text" name="email_subject" id="email_subject" value="<?php echo esc_attr(get_option('ipsit_ig_email_subject', 'Invoice #{invoice_number}')); ?>" class="large-text">
                    <p class="description"><?php echo esc_html__('Use {invoice_number} as placeholder', 'ipsit-invoice-generator'); ?></p>
                </td>
            </tr>
        </table>
        
        <h2><?php echo esc_html__('Design & Colors', 'ipsit-invoice-generator'); ?></h2>
        <p class="description"><?php echo esc_html__('Customize the color scheme of the admin interface. Changes will be applied immediately.', 'ipsit-invoice-generator'); ?></p>
        <table class="form-table">
            <tr>
                <th><label for="design_primary_color"><?php echo esc_html__('Primary Color', 'ipsit-invoice-generator'); ?></label></th>
                <td>
                    <input type="color" name="design_primary_color" id="design_primary_color" value="<?php echo esc_attr(get_option('ipsit_ig_design_primary_color', '#2271b1')); ?>" class="regular-text">
                    <p class="description"><?php echo esc_html__('Main brand color used for buttons, links, and accents', 'ipsit-invoice-generator'); ?></p>
                </td>
            </tr>
            <tr>
                <th><label for="design_secondary_color"><?php echo esc_html__('Secondary Color', 'ipsit-invoice-generator'); ?></label></th>
                <td>
                    <input type="color" name="design_secondary_color" id="design_secondary_color" value="<?php echo esc_attr(get_option('ipsit_ig_design_secondary_color', '#646970')); ?>" class="regular-text">
                    <p class="description"><?php echo esc_html__('Secondary accent color', 'ipsit-invoice-generator'); ?></p>
                </td>
            </tr>
            <tr>
                <th><label for="design_success_color"><?php echo esc_html__('Success Color', 'ipsit-invoice-generator'); ?></label></th>
                <td>
                    <input type="color" name="design_success_color" id="design_success_color" value="<?php echo esc_attr(get_option('ipsit_ig_design_success_color', '#00a32a')); ?>" class="regular-text">
                    <p class="description"><?php echo esc_html__('Color for success messages and paid status', 'ipsit-invoice-generator'); ?></p>
                </td>
            </tr>
            <tr>
                <th><label for="design_error_color"><?php echo esc_html__('Error Color', 'ipsit-invoice-generator'); ?></label></th>
                <td>
                    <input type="color" name="design_error_color" id="design_error_color" value="<?php echo esc_attr(get_option('ipsit_ig_design_error_color', '#d63638')); ?>" class="regular-text">
                    <p class="description"><?php echo esc_html__('Color for error messages and overdue status', 'ipsit-invoice-generator'); ?></p>
                </td>
            </tr>
            <tr>
                <th><label for="design_warning_color"><?php echo esc_html__('Warning Color', 'ipsit-invoice-generator'); ?></label></th>
                <td>
                    <input type="color" name="design_warning_color" id="design_warning_color" value="<?php echo esc_attr(get_option('ipsit_ig_design_warning_color', '#f0b849')); ?>" class="regular-text">
                    <p class="description"><?php echo esc_html__('Color for warning messages', 'ipsit-invoice-generator'); ?></p>
                </td>
            </tr>
            <tr>
                <th></th>
                <td>
                    <button type="button" id="ig-reset-colors" class="button"><?php echo esc_html__('Reset to Defaults', 'ipsit-invoice-generator'); ?></button>
                </td>
            </tr>
        </table>
        
        <h2><?php echo esc_html__('Template Settings', 'ipsit-invoice-generator'); ?></h2>
        <table class="form-table">
            <tr>
                <th><label for="default_template"><?php echo esc_html__('Default Template', 'ipsit-invoice-generator'); ?></label></th>
                <td>
                    <select name="default_template" id="default_template">
                        <option value="0"><?php echo esc_html__('System Default', 'ipsit-invoice-generator'); ?></option>
                        <?php foreach ($templates as $template): ?>
                            <option value="<?php echo esc_attr($template->id); ?>" <?php selected(get_option('ipsit_ig_default_template', 0), $template->id); ?>>
                                <?php echo esc_html($template->name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="description"><?php echo esc_html__('Default template to use for new invoices', 'ipsit-invoice-generator'); ?></p>
                </td>
            </tr>
            <tr>
                <th><label for="show_company_name_with_logo"><?php echo esc_html__('Show Company Name with Logo', 'ipsit-invoice-generator'); ?></label></th>
                <td>
                    <label>
                        <input type="checkbox" name="show_company_name_with_logo" id="show_company_name_with_logo" value="1" <?php checked(get_option('ipsit_ig_show_company_name_with_logo', 0), 1); ?>>
                        <?php echo esc_html__('Show company name below logo when logo exists', 'ipsit-invoice-generator'); ?>
                    </label>
                    <p class="description"><?php echo esc_html__('If unchecked, company name will be hidden when a logo is present', 'ipsit-invoice-generator'); ?></p>
                </td>
            </tr>
        </table>
        
        <p class="submit">
            <button type="submit" class="button button-primary"><?php echo esc_html__('Save Settings', 'ipsit-invoice-generator'); ?></button>
        </p>
    </form>
</div>

