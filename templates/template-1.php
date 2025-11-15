<?php
/**
 * Template 1 - Modern Minimal
 */
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            margin: 0;
            padding: 40px;
            color: #2c3e50;
            background: #f8f9fa;
        }
        .invoice-container {
            background: #fff;
            padding: 50px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            border-bottom: 3px solid #3498db;
            padding-bottom: 30px;
            margin-bottom: 40px;
        }
        .company-logo {
            max-height: 80px;
            margin-bottom: 20px;
        }
        .invoice-title {
            font-size: 36px;
            color: #3498db;
            margin: 0;
            font-weight: 300;
        }
        .invoice-meta {
            margin-top: 20px;
            color: #7f8c8d;
        }
        .two-columns {
            display: flex;
            gap: 40px;
            margin-bottom: 40px;
        }
        .column {
            flex: 1;
        }
        .section-label {
            font-size: 11px;
            text-transform: uppercase;
            color: #95a5a6;
            margin-bottom: 10px;
            letter-spacing: 1px;
        }
        .section-content {
            font-size: 14px;
            line-height: 1.8;
        }
        .section-content strong {
            display: block;
            font-size: 16px;
            margin-bottom: 5px;
            color: #2c3e50;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 30px 0;
        }
        table th {
            background: #ecf0f1;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            font-size: 12px;
            text-transform: uppercase;
            color: #7f8c8d;
        }
        table td {
            padding: 15px;
            border-bottom: 1px solid #ecf0f1;
        }
        .text-right {
            text-align: right;
        }
        .totals-wrapper {
            display: flex;
            justify-content: space-between;
            margin: 30px 0;
            width: 100%;
        }
        .payment-info, .totals-section {
            width: 48%;
            display: inline-block;
            vertical-align: top;
            margin-top: 30px;
            padding-top: 30px;
            border-top: 1px solid #ecf0f1;
        }
        .payment-info h3 {
            margin: 0 0 10px 0;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            color: #7f8c8d;
            letter-spacing: 1px;
            border-bottom: none;
        }
        .payment-info p {
            margin: 5px 0;
            font-size: 13px;
            color: #2c3e50;
        }
        .totals-section {
            text-align: right;
        }
        .totals-section table {
            margin: 0;
        }
        .totals-section .total-row {
            background: #3498db;
            color: #fff;
            font-size: 18px;
            font-weight: 600;
        }
        .totals-section .total-row td {
            border: none;
            padding: 20px 15px;
        }
        .notes-section {
            margin-top: 40px;
            padding-top: 30px;
            border-top: 1px solid #ecf0f1;
            color: #7f8c8d;
            font-size: 13px;
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <div class="header">
            <?php if ($company && $company->logo): ?>
                <img src="<?php echo esc_url($company->logo); ?>" class="company-logo" alt="Logo" />
            <?php endif; ?>
            <h1 class="invoice-title"><?php echo esc_html__('INVOICE', 'ipsit-invoice-generator'); ?></h1>
            <div class="invoice-meta">
                <div><strong>#<?php echo esc_html($invoice->invoice_number); ?></strong></div>
                <div>Date: <?php echo esc_html($invoice_date); ?></div>
                <?php if ($due_date): ?>
                    <div>Due: <?php echo esc_html($due_date); ?></div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="two-columns">
            <div class="column">
                <div class="section-label"><?php echo esc_html__('From', 'ipsit-invoice-generator'); ?></div>
                <div class="section-content">
                    <?php if ($company): ?>
                        <strong><?php echo esc_html($company->name); ?></strong>
                        <?php if ($company->address): ?><br><?php echo esc_html($company->address); ?><?php endif; ?>
                        <?php if ($company->city): ?><br><?php echo esc_html($company->city); ?>, <?php echo esc_html($company->state); ?> <?php echo esc_html($company->zip); ?><?php endif; ?>
                        <?php if ($company->phone): ?><br><?php echo esc_html($company->phone); ?><?php endif; ?>
                        <?php if ($company->email): ?><br><?php echo esc_html($company->email); ?><?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
            <div class="column">
                <div class="section-label"><?php echo esc_html__('Bill To', 'ipsit-invoice-generator'); ?></div>
                <div class="section-content">
                    <?php if ($client): ?>
                        <strong><?php echo esc_html($client->name); ?></strong>
                        <?php if ($client->address): ?><br><?php echo esc_html($client->address); ?><?php endif; ?>
                        <?php if ($client->city): ?><br><?php echo esc_html($client->city); ?>, <?php echo esc_html($client->state); ?> <?php echo esc_html($client->zip); ?><?php endif; ?>
                        <?php if ($client->phone): ?><br><?php echo esc_html($client->phone); ?><?php endif; ?>
                        <?php if ($client->email): ?><br><?php echo esc_html($client->email); ?><?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th><?php echo esc_html__('Description', 'ipsit-invoice-generator'); ?></th>
                    <th class="text-right"><?php echo esc_html__('Qty', 'ipsit-invoice-generator'); ?></th>
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
                        <td class="text-right"><?php echo $currency_symbol . number_format($price, 2); ?></td>
                        <td class="text-right"><?php echo $currency_symbol . number_format($total, 2); ?></td>
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
            
            <div class="totals-section">
                <table>
                    <tr>
                        <td class="text-right"><?php echo esc_html__('Subtotal', 'ipsit-invoice-generator'); ?></td>
                        <td class="text-right"><?php echo $currency_symbol . number_format($invoice->subtotal, 2); ?></td>
                    </tr>
                    <?php if ($invoice->tax > 0): ?>
                        <tr>
                            <td class="text-right"><?php echo esc_html__('Tax', 'ipsit-invoice-generator'); ?></td>
                            <td class="text-right"><?php echo $currency_symbol . number_format($invoice->tax, 2); ?></td>
                        </tr>
                    <?php endif; ?>
                    <tr class="total-row">
                        <td class="text-right"><?php echo esc_html__('Total', 'ipsit-invoice-generator'); ?></td>
                        <td class="text-right"><?php echo $currency_symbol . number_format($invoice->total, 2); ?></td>
                    </tr>
                </table>
            </div>
        </div>
        
        <?php if ($invoice->notes): ?>
            <div class="notes-section">
                <strong><?php echo esc_html__('Notes', 'ipsit-invoice-generator'); ?>:</strong><br>
                <?php echo nl2br(esc_html($invoice->notes)); ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>

