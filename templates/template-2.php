<?php
/**
 * Template 2 - Classic Professional
 */

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- These are local variables in a template file, not global variables

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: 'Times New Roman', serif;
            margin: 0;
            padding: 30px;
            color: #000;
        }
        .invoice-wrapper {
            max-width: 800px;
            margin: 0 auto;
            border: 2px solid #000;
            padding: 40px;
        }
        .header-section {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header-section h1 {
            font-size: 32px;
            margin: 10px 0;
            text-transform: uppercase;
            letter-spacing: 3px;
        }
        .company-info {
            margin-top: 15px;
            font-size: 14px;
        }
        .invoice-details {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        .invoice-number {
            font-size: 18px;
            font-weight: bold;
        }
        .billing-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        .billing-box {
            width: 48%;
            border: 1px solid #000;
            padding: 15px;
        }
        .billing-box h3 {
            margin: 0 0 10px 0;
            font-size: 14px;
            text-transform: uppercase;
            border-bottom: 1px solid #000;
            padding-bottom: 5px;
        }
        .items-header {
            background: #000;
            color: #fff;
            padding: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .items-table th {
            background: #000;
            color: #fff;
            padding: 12px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #000;
        }
        .items-table td {
            padding: 10px 12px;
            border: 1px solid #000;
        }
        .text-right {
            text-align: right;
        }
        .totals-wrapper {
            display: flex;
            justify-content: space-between;
            margin: 20px 0;
            width: 100%;
        }
        .payment-info, .totals-box {
            width: 48%;
            display: inline-block;
            vertical-align: top;
            margin-top: 20px;
        }
        .payment-info {
            padding: 15px;
            border: 1px solid #000;
        }
        .payment-info h3 {
            margin: 0 0 10px 0;
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
            border-bottom: none;
            padding-bottom: 5px;
        }
        .payment-info p {
            margin: 5px 0;
            font-size: 12px;
        }
        .totals-box {
            text-align: right;
        }
        .totals-box table {
            width: 100%;
            border-collapse: collapse;
        }
        .totals-box td {
            padding: 8px 12px;
            border: 1px solid #000;
        }
        .totals-box .label {
            text-align: right;
            font-weight: bold;
        }
        .totals-box .total-row {
            background: #f0f0f0;
            font-weight: bold;
            font-size: 16px;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #000;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="invoice-wrapper">
        <div class="header-section">
            <?php if ($company && $company->logo): ?>
                <img src="<?php echo esc_url($company->logo); ?>" alt="Logo" style="max-height: 60px; margin-bottom: 10px;" />
            <?php endif; ?>
            <h1><?php echo esc_html__('INVOICE', 'ipsit-invoice-generator'); ?></h1>
            <?php if ($company): ?>
                <div class="company-info">
                    <?php echo esc_html($company->name); ?><br>
                    <?php if ($company->address): ?><?php echo esc_html($company->address); ?><br><?php endif; ?>
                    <?php if ($company->city): ?><?php echo esc_html($company->city); ?>, <?php echo esc_html($company->state); ?> <?php echo esc_html($company->zip); ?><?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="invoice-details">
            <div class="invoice-number">
                <?php echo esc_html__('Invoice Number:', 'ipsit-invoice-generator'); ?> <?php echo esc_html($invoice->invoice_number); ?>
            </div>
            <div>
                <?php echo esc_html__('Date:', 'ipsit-invoice-generator'); ?> <?php echo esc_html($invoice_date); ?><br>
                <?php if ($due_date): ?>
                    <?php echo esc_html__('Due Date:', 'ipsit-invoice-generator'); ?> <?php echo esc_html($due_date); ?>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="billing-section">
            <div class="billing-box">
                <h3><?php echo esc_html__('Bill From', 'ipsit-invoice-generator'); ?></h3>
                <?php if ($company): ?>
                    <div>
                        <?php echo esc_html($company->name); ?><br>
                        <?php if ($company->address): ?><?php echo esc_html($company->address); ?><br><?php endif; ?>
                        <?php if ($company->city): ?><?php echo esc_html($company->city); ?>, <?php echo esc_html($company->state); ?> <?php echo esc_html($company->zip); ?><?php endif; ?>
                        <?php if ($company->phone): ?><br><?php echo esc_html($company->phone); ?><?php endif; ?>
                        <?php if ($company->email): ?><br><?php echo esc_html($company->email); ?><?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="billing-box">
                <h3><?php echo esc_html__('Bill To', 'ipsit-invoice-generator'); ?></h3>
                <?php if ($client): ?>
                    <div>
                        <?php echo esc_html($client->name); ?><br>
                        <?php if ($client->address): ?><?php echo esc_html($client->address); ?><br><?php endif; ?>
                        <?php if ($client->city): ?><?php echo esc_html($client->city); ?>, <?php echo esc_html($client->state); ?> <?php echo esc_html($client->zip); ?><?php endif; ?>
                        <?php if ($client->phone): ?><br><?php echo esc_html($client->phone); ?><?php endif; ?>
                        <?php if ($client->email): ?><br><?php echo esc_html($client->email); ?><?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <table class="items-table">
            <thead>
                <tr>
                    <th><?php echo esc_html__('Description', 'ipsit-invoice-generator'); ?></th>
                    <th class="text-right"><?php echo esc_html__('Quantity', 'ipsit-invoice-generator'); ?></th>
                    <th class="text-right"><?php echo esc_html__('Unit Price', 'ipsit-invoice-generator'); ?></th>
                    <th class="text-right"><?php echo esc_html__('Amount', 'ipsit-invoice-generator'); ?></th>
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
            
            <div class="totals-box">
                <table>
                    <tr>
                        <td class="label"><?php echo esc_html__('Subtotal:', 'ipsit-invoice-generator'); ?></td>
                        <td class="text-right"><?php echo esc_html($currency_symbol) . number_format($invoice->subtotal, 2); ?></td>
                    </tr>
                    <?php if ($invoice->tax > 0): ?>
                        <tr>
                            <td class="label"><?php echo esc_html__('Tax:', 'ipsit-invoice-generator'); ?></td>
                            <td class="text-right"><?php echo esc_html($currency_symbol) . number_format($invoice->tax, 2); ?></td>
                        </tr>
                    <?php endif; ?>
                    <tr class="total-row">
                        <td class="label"><?php echo esc_html__('Total:', 'ipsit-invoice-generator'); ?></td>
                        <td class="text-right"><?php echo esc_html($currency_symbol) . number_format($invoice->total, 2); ?></td>
                    </tr>
                </table>
            </div>
        </div>
        
        <?php if ($invoice->notes): ?>
            <div class="footer">
                <strong><?php echo esc_html__('Notes:', 'ipsit-invoice-generator'); ?></strong><br>
                <?php echo nl2br(esc_html($invoice->notes)); ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>

