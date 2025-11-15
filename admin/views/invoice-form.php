<?php

/**
 * Invoice Form View
 */
if (!defined('ABSPATH')) {
    exit;
}

$db = IG_Database::get_instance();
$template_engine = IG_Template_Engine::get_instance();
$invoice_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$invoice = $invoice_id > 0 ? $db->get_invoice($invoice_id) : null;
$company = $db->get_company(); // Get company for default payment details
$clients = $db->get_clients(100);
$db_templates = $db->get_templates();
$file_templates = $template_engine->get_file_templates();

// Filter out file-based templates that have the same name as database templates
// This prevents duplicates when templates are loaded into the database
$db_template_names = array();
foreach ($db_templates as $db_template) {
    $db_template_names[] = strtolower($db_template->name);
}

$filtered_file_templates = array();
foreach ($file_templates as $file_template) {
    // Only include file template if no database template with same name exists
    if (!in_array(strtolower($file_template->name), $db_template_names)) {
        $filtered_file_templates[] = $file_template;
    }
}

// Merge: database templates first, then file-based templates (without duplicates)
$templates = array_merge($db_templates, $filtered_file_templates);

$items = $invoice ? json_decode($invoice->items, true) : array();
if (!is_array($items)) {
    $items = array();
}
if (empty($items)) {
    $items = array(array('description' => '', 'quantity' => 1, 'price' => 0));
}

$currency_symbol = get_option('ipsit_ig_currency_symbol', '$');
?>
<div class="wrap">
    <script type="text/javascript">
        var igCurrencySymbol = '<?php echo esc_js($currency_symbol); ?>';
    </script>
    <span id="ig-currency-symbol" data-symbol="<?php echo esc_attr($currency_symbol); ?>" class="ig-hidden"></span>
    <h1><?php echo $invoice_id > 0 ? esc_html__('Edit Invoice', 'ipsit-invoice-generator') : esc_html__('Create Invoice', 'ipsit-invoice-generator'); ?></h1>

    <form id="ig-invoice-form" method="post">
        <?php wp_nonce_field('ig_admin_nonce', 'nonce'); ?>
        <input type="hidden" name="invoice_id" value="<?php echo esc_attr($invoice_id); ?>">

        <div class="ig-form-row">
            <div class="ig-form-col">
                <label for="client_id"><?php echo esc_html__('Client', 'ipsit-invoice-generator'); ?> <span class="required">*</span></label>
                <select name="client_id" id="client_id" required>
                    <option value=""><?php echo esc_html__('Select Client', 'ipsit-invoice-generator'); ?></option>
                    <?php foreach ($clients as $client): ?>
                        <option value="<?php echo esc_attr($client->id); ?>" <?php selected($invoice && $invoice->client_id == $client->id); ?>>
                            <?php echo esc_html($client->name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="ig-form-col">
                <label for="invoice_date"><?php echo esc_html__('Invoice Date', 'ipsit-invoice-generator'); ?></label>
                <input type="date" name="invoice_date" id="invoice_date" value="<?php echo $invoice ? esc_attr($invoice->invoice_date) : esc_attr(date('Y-m-d')); ?>" required>
            </div>
            <div class="ig-form-col">
                <label for="due_date"><?php echo esc_html__('Due Date', 'ipsit-invoice-generator'); ?></label>
                <input type="date" name="due_date" id="due_date" value="<?php echo $invoice && $invoice->due_date ? esc_attr($invoice->due_date) : ''; ?>">
            </div>
            <div class="ig-form-col">
                <label for="status"><?php echo esc_html__('Status', 'ipsit-invoice-generator'); ?></label>
                <select name="status" id="status">
                    <option value="draft" <?php selected($invoice && $invoice->status === 'draft'); ?>><?php echo esc_html__('Draft', 'ipsit-invoice-generator'); ?></option>
                    <option value="sent" <?php selected($invoice && $invoice->status === 'sent'); ?>><?php echo esc_html__('Sent', 'ipsit-invoice-generator'); ?></option>
                    <option value="paid" <?php selected($invoice && $invoice->status === 'paid'); ?>><?php echo esc_html__('Paid', 'ipsit-invoice-generator'); ?></option>
                    <option value="overdue" <?php selected($invoice && $invoice->status === 'overdue'); ?>><?php echo esc_html__('Overdue', 'ipsit-invoice-generator'); ?></option>
                </select>
            </div>
        </div>

        <div class="ig-form-row">
            <label for="template_id"><?php echo esc_html__('Template', 'ipsit-invoice-generator'); ?></label>
            <select name="template_id" id="template_id">
                <option value=""><?php echo esc_html__('Default Template', 'ipsit-invoice-generator'); ?></option>
                <?php foreach ($templates as $template): ?>
                    <?php
                    // Determine the display name and value
                    $display_name = esc_html($template->name);
                    $template_value = '';

                    if (isset($template->type) && $template->type === 'file') {
                        // File-based template
                        $template_value = esc_attr($template->id);
                        $display_name .= ' (' . esc_html__('Pre-built', 'ipsit-invoice-generator') . ')';
                    } else {
                        // Database template
                        $template_value = esc_attr($template->id);
                    }
                    ?>
                    <option value="<?php echo $template_value; ?>" <?php selected($invoice && $invoice->template_id == $template->id); ?>>
                        <?php echo $display_name; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <h2><?php echo esc_html__('Invoice Items', 'ipsit-invoice-generator'); ?></h2>
        <table id="ig-items-table" class="widefat ig-items-table">
            <thead>
                <tr>
                    <th><?php echo esc_html__('Description', 'ipsit-invoice-generator'); ?></th>
                    <th width="100"><?php echo esc_html__('Quantity', 'ipsit-invoice-generator'); ?></th>
                    <th width="120"><?php echo esc_html__('Price', 'ipsit-invoice-generator'); ?></th>
                    <th width="120"><?php echo esc_html__('Total', 'ipsit-invoice-generator'); ?></th>
                    <th width="50"></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $index => $item): ?>
                    <tr class="ig-item-row">
                        <td><textarea name="items[<?php echo esc_attr($index); ?>][description]" rows="3" class="regular-text" required><?php echo esc_textarea($item['description']); ?></textarea></td>
                        <td><input type="number" name="items[<?php echo esc_attr($index); ?>][quantity]" value="<?php echo esc_attr($item['quantity']); ?>" step="0.01" min="0" class="ig-item-qty" required></td>
                        <td><input type="number" name="items[<?php echo esc_attr($index); ?>][price]" value="<?php echo esc_attr($item['price']); ?>" step="0.01" min="0" class="ig-item-price" required></td>
                        <td class="ig-item-total"><?php echo esc_html($currency_symbol . number_format($item['quantity'] * $item['price'], 2)); ?></td>
                        <td><button type="button" class="button ig-remove-item"><?php echo esc_html__('Remove', 'ipsit-invoice-generator'); ?></button></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="5">
                        <div class="custom-form-row">
                            <div class="ig-form-col">
                                <label for="tax_rate"><?php echo esc_html__('Tax Rate (%)', 'ipsit-invoice-generator'); ?></label>
                                <input type="number" name="tax_rate" id="tax_rate" value="0" step="0.01" min="0" max="100">
                            </div>
                            <div class="ig-form-col">
                                <button type="button" id="ig-add-item" class="button"><?php echo esc_html__('Add Item', 'ipsit-invoice-generator'); ?></button>
                            </div>
                        </div>

                    </td>
                </tr>
            </tfoot>
        </table>

     

        <div class="ig-totals">
            <table>
                <tr>
                    <td><strong><?php echo esc_html__('Subtotal:', 'ipsit-invoice-generator'); ?></strong></td>
                    <td id="ig-subtotal"><?php echo esc_html($currency_symbol . '0.00'); ?></td>
                </tr>
                <tr>
                    <td><strong><?php echo esc_html__('Tax:', 'ipsit-invoice-generator'); ?></strong></td>
                    <td id="ig-tax"><?php echo esc_html($currency_symbol . '0.00'); ?></td>
                </tr>
                <tr>
                    <td><strong><?php echo esc_html__('Total:', 'ipsit-invoice-generator'); ?></strong></td>
                    <td id="ig-total"><strong><?php echo esc_html($currency_symbol . '0.00'); ?></strong></td>
                </tr>
            </table>
        </div>

        <div class="ig-form-row">
            <label for="notes"><?php echo esc_html__('Notes / Terms', 'ipsit-invoice-generator'); ?></label>
            <textarea name="notes" id="notes" rows="5" class="large-text"><?php echo $invoice ? esc_textarea($invoice->notes) : ''; ?></textarea>
        </div>

        <h2><?php echo esc_html__('Payment Method', 'ipsit-invoice-generator'); ?></h2>
        <div class="ig-form-row">
            <label for="payment_method">
                <input type="checkbox" name="enable_payment_method" id="enable_payment_method" value="1" <?php
                                                                                                            // Check if invoice has payment method OR company has default payment method
                                                                                                            $has_payment = ($invoice && $invoice->payment_method === 'bank_transfer') ||
                                                                                                                (!$invoice && $company && $company->default_payment_method === 'bank_transfer');
                                                                                                            checked($has_payment);
                                                                                                            ?>>
                <?php echo esc_html__('Bank Transfer', 'ipsit-invoice-generator'); ?>
            </label>
        </div>

        <div id="payment-method-fields" class="<?php echo $has_payment ? '' : 'ig-hidden'; ?>">
            <div class="ig-form-row">
                <div class="ig-form-col">
                    <label for="bank_name"><?php echo esc_html__('Bank Name', 'ipsit-invoice-generator'); ?></label>
                    <input type="text" name="bank_name" id="bank_name" value="<?php
                                                                                echo $invoice ? esc_attr($invoice->bank_name) : ($company ? esc_attr($company->default_bank_name) : '');
                                                                                ?>" class="regular-text">
                </div>
                <div class="ig-form-col">
                    <label for="account_number"><?php echo esc_html__('Account Number', 'ipsit-invoice-generator'); ?></label>
                    <input type="text" name="account_number" id="account_number" value="<?php
                                                                                        echo $invoice ? esc_attr($invoice->account_number) : ($company ? esc_attr($company->default_account_number) : '');
                                                                                        ?>" class="regular-text">
                </div>
                <div class="ig-form-col">
                    <label for="account_title"><?php echo esc_html__('Account Title', 'ipsit-invoice-generator'); ?></label>
                    <input type="text" name="account_title" id="account_title" value="<?php
                                                                                        echo $invoice ? esc_attr($invoice->account_title) : ($company ? esc_attr($company->default_account_title) : '');
                                                                                        ?>" class="regular-text">
                </div>
                <div class="ig-form-col">
                    <label for="account_branch"><?php echo esc_html__('Account Branch', 'ipsit-invoice-generator'); ?></label>
                    <input type="text" name="account_branch" id="account_branch" value="<?php
                                                                                        echo $invoice ? esc_attr($invoice->account_branch) : ($company ? esc_attr($company->default_account_branch) : '');
                                                                                        ?>" class="regular-text">
                </div>
                <div class="ig-form-col">
                    <label for="iban"><?php echo esc_html__('IBAN', 'ipsit-invoice-generator'); ?></label>
                    <input type="text" name="iban" id="iban" value="<?php
                                                                    echo $invoice ? esc_attr($invoice->iban) : ($company ? esc_attr($company->default_iban) : '');
                                                                    ?>" class="regular-text">
                </div>
                <div class="ig-form-col">
                    <label for="ifsc_code"><?php echo esc_html__('IFSC Code', 'ipsit-invoice-generator'); ?></label>
                    <input type="text" name="ifsc_code" id="ifsc_code" value="<?php
                                                                                echo $invoice ? esc_attr($invoice->ifsc_code) : ($company ? esc_attr($company->default_ifsc_code) : '');
                                                                                ?>" class="regular-text">
                </div>
            </div>

        </div>

        <p class="submit">
            <button type="submit" class="button button-primary"><?php echo esc_html__('Save Invoice', 'ipsit-invoice-generator'); ?></button>
            <?php if ($invoice_id > 0): ?>
                <a href="<?php echo admin_url('admin.php?ipsit_ig_download_pdf=1&invoice_id=' . $invoice_id . '&_wpnonce=' . wp_create_nonce('ipsit_ig_download_pdf')); ?>" class="ig-action-button ig-action-pdf" title="<?php echo esc_attr__('Download PDF', 'ipsit-invoice-generator'); ?>">
                    <span class="dashicons dashicons-media-document"></span>
                </a>
            <?php endif; ?>
            <a href="<?php echo admin_url('admin.php?page=ipsit-ig-invoices'); ?>" class="button"><?php echo esc_html__('Cancel', 'ipsit-invoice-generator'); ?></a>
        </p>
    </form>

    <?php if ($invoice_id > 0): ?>
    <!-- Email Section (Collapsible) -->
    <div class="ig-accordion-section">
        <div class="ig-accordion-header" id="ig-email-accordion-header">
            <h2>
                <span class="dashicons dashicons-email"></span>
                <?php echo esc_html__('Send Invoice via Email', 'ipsit-invoice-generator'); ?>
            </h2>
            <span class="ig-accordion-toggle dashicons dashicons-arrow-down-alt2"></span>
        </div>
        <div class="ig-accordion-content" id="ig-email-accordion-content">
            <form id="ig-email-form">
                <?php wp_nonce_field('ig_admin_nonce', 'email_nonce'); ?>
                <input type="hidden" name="invoice_id" id="email_invoice_id" value="<?php echo esc_attr($invoice_id); ?>">
                
                <div class="ig-form-field">
                    <label for="to_email"><?php echo esc_html__('To Email', 'ipsit-invoice-generator'); ?> <span class="required">*</span></label>
                    <input type="email" name="to_email" id="to_email" class="regular-text" required>
                </div>

                <div class="ig-form-field">
                    <label for="email_subject"><?php echo esc_html__('Subject', 'ipsit-invoice-generator'); ?> <span class="required">*</span></label>
                    <input type="text" name="subject" id="email_subject" class="large-text" required>
                </div>

                <div class="ig-form-field">
                    <label for="email_message"><?php echo esc_html__('Message', 'ipsit-invoice-generator'); ?></label>
                    <textarea name="message" id="email_message" rows="5" class="large-text"></textarea>
                </div>

                <p class="submit">
                    <button type="submit" class="button button-primary"><?php echo esc_html__('Send Email', 'ipsit-invoice-generator'); ?></button>
                </p>
            </form>
        </div>
    </div>
    <?php endif; ?>
</div>