<?php
/**
 * Default Invoice Template
 */

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- These are local variables in a template file, not global variables

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
            font-size: 12px;
        }
        .invoice-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #eee;
        }
        .company-info {
            display: flex;
            flex-direction: column;
        }
        .company-info img {
            max-width: 150px;
            max-height: 80px;
            margin-bottom: 10px;
        }
        .company-info h1 {
            margin: 0 0 10px 0;
            font-size: 24px;
            color: #2c3e50;
        }
        .invoice-info {
            text-align: right;
        }
        .invoice-info h2 {
            margin: 0 0 15px 0;
            font-size: 28px;
            color: #2c3e50;
        }
        .invoice-info .invoice-details-info {
            margin-top: 15px;
        }
        .invoice-info .invoice-details-info p {
            margin: 5px 0;
        }
        .invoice-details {
            display: flex;
            justify-content: space-between;
            margin: 30px 0;
            width: 100%;
        }
        .company-details, .client-details {
            width: 48%;
            display: inline-block;
            vertical-align: top;
        }
        .section-title {
            margin: 0 0 15px 0;
            color: #2c3e50;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
            font-weight: bold;
            font-size: 14px;
        }
        .company-details p, .client-details p {
            margin: 5px 0;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 30px 0;
        }
        .items-table th,
        .items-table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        .items-table th {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #2c3e50;
        }
        .items-table tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .items-table .text-right {
            text-align: right;
        }
        .totals-wrapper {
            display: flex;
            justify-content: space-between;
            margin: 30px 0;
            width: 100%;
        }
        .payment-info, .totals {
            width: 48%;
            display: inline-block;
            vertical-align: top;
            border-top: 2px solid #eee;
            padding-top: 20px;
        }
        .payment-info h3 {
            margin: 0 0 15px 0;
            color: #2c3e50;
            border-bottom: none;
            padding-bottom: 5px;
            font-weight: bold;
            font-size: 14px;
        }
        .payment-info p {
            margin: 5px 0;
            font-size: 12px;
        }
        .totals {
            text-align: right;
        }
        .totals table {
            width: 100%;
            border-collapse: collapse;
        }
        .totals td {
            padding: 8px 12px;
        }
        .totals .label {
            text-align: right;
            font-weight: bold;
        }
        .totals .amount {
            text-align: right;
        }
        .total-row {
            font-size: 18px;
            font-weight: bold;
            color: #2c3e50;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .total-row .amount {
            color: #2c3e50;
        }
        .invoice-footer {
            margin-top: 50px;
            text-align: center;
            color: #666;
            border-top: 1px solid #eee;
            padding-top: 20px;
            font-size: 14px;
        }
        .invoice-footer p {
            margin: 5px 0;
        }
        .notes {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
        }
    </style>
</head>
<body>
    <div class="invoice-header">
        <div class="company-info">
            <?php if ($company && $company->logo): ?>
                <img src="<?php echo esc_url($company->logo); ?>" alt="Logo" />
            <?php else: ?>
                <?php if ($company && $company->name): ?>
                    <h1><?php echo esc_html($company->name); ?></h1>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        <div class="invoice-info">
            <h2><?php echo esc_html__('INVOICE', 'ipsit-invoice-generator'); ?></h2>
            <div class="invoice-details-info">
                <p><strong><?php echo esc_html__('Invoice #:', 'ipsit-invoice-generator'); ?></strong> <?php echo esc_html($invoice->invoice_number); ?></p>
                <p><strong><?php echo esc_html__('Date:', 'ipsit-invoice-generator'); ?></strong> <?php echo esc_html($invoice_date); ?></p>
                <?php if ($due_date): ?>
                    <p><strong><?php echo esc_html__('Due Date:', 'ipsit-invoice-generator'); ?></strong> <?php echo esc_html($due_date); ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="invoice-details">
        <div class="company-details">
            <div class="section-title"><?php echo esc_html__('Bill From:', 'ipsit-invoice-generator'); ?></div>
            <?php if ($company): ?>
                <p><strong><?php echo esc_html($company->name); ?></strong></p>
                <?php if ($company->address): ?><p><?php echo nl2br(esc_html($company->address)); ?></p><?php endif; ?>
                <?php if ($company->city || $company->state || $company->zip): ?>
                    <p><?php 
                        $address_parts = array();
                        if ($company->city) $address_parts[] = $company->city;
                        if ($company->state) $address_parts[] = $company->state;
                        if ($company->zip) $address_parts[] = $company->zip;
                        echo esc_html(implode(', ', $address_parts));
                    ?></p>
                <?php endif; ?>
                <?php if ($company->phone): ?><p><?php echo esc_html__('Tel:', 'ipsit-invoice-generator'); ?> <?php echo esc_html($company->phone); ?></p><?php endif; ?>
                <?php if ($company->email): ?><p><?php echo esc_html__('E-Mail:', 'ipsit-invoice-generator'); ?> <?php echo esc_html($company->email); ?></p><?php endif; ?>
            <?php endif; ?>
        </div>
        <div class="client-details">
            <div class="section-title"><?php echo esc_html__('Bill To:', 'ipsit-invoice-generator'); ?></div>
            <?php if ($client): ?>
                <p><strong><?php echo esc_html($client->name); ?></strong></p>
                <?php if ($client->email): ?><p><?php echo esc_html($client->email); ?></p><?php endif; ?>
                <?php if ($client->phone): ?><p><?php echo esc_html__('Tel:', 'ipsit-invoice-generator'); ?> <?php echo esc_html($client->phone); ?></p><?php endif; ?>
                <?php if ($client->address): ?><p><?php echo esc_html($client->address); ?></p><?php endif; ?>
                <?php if ($client->city || $client->state || $client->zip): ?>
                    <p><?php 
                        $address_parts = array();
                        if ($client->city) $address_parts[] = $client->city;
                        if ($client->state) $address_parts[] = $client->state;
                        if ($client->zip) $address_parts[] = $client->zip;
                        echo esc_html(implode(', ', $address_parts));
                    ?></p>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <table class="items-table">
        <thead>
            <tr>
                <th><?php echo esc_html__('Description', 'ipsit-invoice-generator'); ?></th>
                <th class="text-right"><?php echo esc_html__('Quantity', 'ipsit-invoice-generator'); ?></th>
                <th class="text-right"><?php echo esc_html__('Price', 'ipsit-invoice-generator'); ?></th>
                <th class="text-right"><?php echo esc_html__('Total', 'ipsit-invoice-generator'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $item): ?>
                <?php
                $quantity = isset($item['quantity']) ? floatval($item['quantity']) : 0;
                $price = isset($item['price']) ? floatval($item['price']) : 0;
                $total = $quantity * $price;
                ?>
                <tr>
                    <td><?php echo esc_html($item['description']); ?></td>
                    <td class="text-right"><?php echo number_format($quantity, 2); ?></td>
                    <td class="text-right"><?php echo esc_html($currency_symbol) . number_format($price, 2); ?></td>
                    <td class="text-right"><?php echo esc_html($currency_symbol) . number_format($total, 2); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <div class="totals-wrapper">
        <?php if ($invoice->payment_method === 'bank_transfer' && ($invoice->bank_name || $invoice->account_number || $invoice->account_title || $invoice->account_branch || $invoice->iban || $invoice->ifsc_code)): ?>
            <div class="payment-info">
                <h3><?php echo esc_html__('Payment Method: Bank Transfer', 'ipsit-invoice-generator'); ?></h3>
                <?php if ($invoice->bank_name): ?>
                    <p><strong><?php echo esc_html__('Bank Name:', 'ipsit-invoice-generator'); ?></strong> <?php echo esc_html($invoice->bank_name); ?></p>
                <?php endif; ?>
                <?php if ($invoice->account_number): ?>
                    <p><strong><?php echo esc_html__('Account Number:', 'ipsit-invoice-generator'); ?></strong> <?php echo esc_html($invoice->account_number); ?></p>
                <?php endif; ?>
                <?php if ($invoice->account_title): ?>
                    <p><strong><?php echo esc_html__('Account Title:', 'ipsit-invoice-generator'); ?></strong> <?php echo esc_html($invoice->account_title); ?></p>
                <?php endif; ?>
                <?php if ($invoice->account_branch): ?>
                    <p><strong><?php echo esc_html__('Account Branch:', 'ipsit-invoice-generator'); ?></strong> <?php echo esc_html($invoice->account_branch); ?></p>
                <?php endif; ?>
                <?php if ($invoice->iban): ?>
                    <p><strong><?php echo esc_html__('IBAN:', 'ipsit-invoice-generator'); ?></strong> <?php echo esc_html($invoice->iban); ?></p>
                <?php endif; ?>
                <?php if ($invoice->ifsc_code): ?>
                    <p><strong><?php echo esc_html__('IFSC Code:', 'ipsit-invoice-generator'); ?></strong> <?php echo esc_html($invoice->ifsc_code); ?></p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <div class="totals">
            <table>
                <tr>
                    <td class="label"><?php echo esc_html__('Subtotal:', 'ipsit-invoice-generator'); ?></td>
                    <td class="amount"><?php echo esc_html($currency_symbol) . number_format($invoice->subtotal, 2); ?></td>
                </tr>
                <?php if ($invoice->tax > 0): ?>
                    <tr>
                        <td class="label"><?php echo esc_html__('Tax:', 'ipsit-invoice-generator'); ?></td>
                        <td class="amount"><?php echo esc_html($currency_symbol) . number_format($invoice->tax, 2); ?></td>
                    </tr>
                <?php endif; ?>
                <tr class="total-row">
                    <td class="label"><?php echo esc_html__('Total:', 'ipsit-invoice-generator'); ?></td>
                    <td class="amount"><?php echo esc_html($currency_symbol) . number_format($invoice->total, 2); ?></td>
                </tr>
            </table>
        </div>
    </div>
    
    <?php if ($invoice->notes): ?>
        <div class="notes">
            <strong><?php echo esc_html__('Notes:', 'ipsit-invoice-generator'); ?></strong>
            <div><?php echo nl2br(esc_html($invoice->notes)); ?></div>
        </div>
    <?php endif; ?>
    
    <div class="invoice-footer">
        <p><?php echo esc_html__('Thank you for your business!', 'ipsit-invoice-generator'); ?></p>
        
    </div>
</body>
</html>

