<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 24px;
            font-family: DejaVu Sans, sans-serif;
            font-size: 13px;
            line-height: 1.45;
            color: #1f2937;
            background: #f4f6fb;
        }
        .invoice-card {
            max-width: 860px;
            margin: 0 auto;
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            overflow: hidden;
        }
        .topbar {
            background: #0f172a;
            color: #ffffff;
            padding: 18px 24px;
        }
        .topbar table {
            width: 100%;
            border-collapse: collapse;
        }
        .brand-name {
            font-size: 18px;
            font-weight: 700;
            letter-spacing: .3px;
        }
        .invoice-title {
            font-size: 24px;
            font-weight: 700;
            margin: 0;
            text-align: right;
        }
        .content {
            padding: 22px 24px 26px 24px;
        }
        .meta-grid {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }
        .meta-grid td {
            vertical-align: top;
            width: 50%;
            padding: 0 0 14px 0;
        }
        .card {
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 12px 14px;
            background: #fafafa;
        }
        .label {
            font-size: 10px;
            letter-spacing: .5px;
            text-transform: uppercase;
            color: #6b7280;
            margin-bottom: 6px;
            display: block;
        }
        .value-strong {
            font-weight: 700;
            color: #111827;
        }
        .muted {
            color: #6b7280;
        }
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
            margin-bottom: 14px;
        }
        .details-table td {
            padding: 4px 0;
            border-bottom: 1px solid #f1f5f9;
        }
        .items {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }
        .items th {
            text-align: left;
            font-size: 10px;
            letter-spacing: .45px;
            text-transform: uppercase;
            padding: 10px 12px;
            background: #f3f4f6;
            color: #374151;
            border: 1px solid #e5e7eb;
        }
        .items td {
            padding: 11px 12px;
            border: 1px solid #e5e7eb;
            vertical-align: top;
        }
        .text-right { text-align: right; }
        .summary-wrap {
            width: 100%;
            margin-top: 14px;
        }
        .summary {
            width: 290px;
            margin-left: auto;
            border-collapse: collapse;
            font-size: 12px;
        }
        .summary td {
            padding: 8px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .summary .total-row td {
            border-top: 2px solid #111827;
            border-bottom: none;
            font-weight: 700;
            font-size: 14px;
            padding-top: 10px;
        }
        .footer-note {
            margin-top: 16px;
            padding-top: 12px;
            border-top: 1px dashed #d1d5db;
            font-size: 11px;
            color: #6b7280;
        }
        .status-badge {
            display: inline-block;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .45px;
            padding: 5px 8px;
            border-radius: 999px;
            background: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
        }
        .status-badge.pending {
            background: #fef3c7;
            color: #92400e;
            border-color: #fde68a;
        }
        .status-badge.failed {
            background: #fee2e2;
            color: #991b1b;
            border-color: #fecaca;
        }
    </style>
</head>
<body>
    @php
        $invoiceNumber = $lead->transaction_id ?: ('INV-' . $lead->id . '-' . $lead->created_at->format('Ymd'));
        $issueDate = optional($lead->created_at)->format('Y-m-d') ?: now()->format('Y-m-d');
        $customerName = trim(($lead->first_name ?? '') . ' ' . ($lead->last_name ?? '')) ?: ($lead->email ?: 'Customer');
        $sellerName = optional(optional($lead->landing)->workspace)->name ?: config('app.name', 'LandingBuilder');
        $currency = strtoupper((string) ($lead->currency ?: 'USD'));
        $amount = (float) ($lead->amount ?? 0);
        $status = strtolower((string) ($lead->status ?: 'paid'));
        $statusClass = in_array($status, ['failed', 'canceled', 'cancelled'], true)
            ? 'failed'
            : (in_array($status, ['pending', 'new'], true) ? 'pending' : '');
        $orderItems = is_array($lead->order_items) ? $lead->order_items : [];
        $firstItemName = is_array($orderItems) && isset($orderItems[0]['name']) ? (string) $orderItems[0]['name'] : null;
        $itemName = $firstItemName ?: 'Landing page offer';
        $itemDescription = 'Order from ' . (optional($lead->landing)->name ?: 'Landing');
    @endphp

    <div class="invoice-card">
        <div class="topbar">
            <table>
                <tr>
                    <td>
                        <div class="brand-name">{{ config('app.name', 'LandingBuilder') }}</div>
                    </td>
                    <td class="text-right">
                        <h1 class="invoice-title">INVOICE</h1>
                    </td>
                </tr>
            </table>
        </div>

        <div class="content">
            <table class="meta-grid">
                <tr>
                    <td style="padding-right: 8px;">
                        <div class="card">
                            <span class="label">Bill To</span>
                            <div class="value-strong">{{ $customerName }}</div>
                            <div class="muted">{{ $lead->email ?: '-' }}</div>
                            <div class="muted">{{ trim((string) $lead->address) !== '' ? $lead->address : '-' }}</div>
                            <div class="muted">
                                {{ trim((string) ($lead->city ?? '')) !== '' ? $lead->city : '-' }}
                                {{ trim((string) ($lead->zip ?? '')) !== '' ? ', ' . $lead->zip : '' }}
                            </div>
                            <div class="muted">{{ $lead->country ?: '-' }}</div>
                        </div>
                    </td>
                    <td style="padding-left: 8px;">
                        <div class="card">
                            <span class="label">Invoice Details</span>
                            <table class="details-table">
                                <tr><td class="muted">Invoice #</td><td class="text-right value-strong">{{ $invoiceNumber }}</td></tr>
                                <tr><td class="muted">Issue date</td><td class="text-right">{{ $issueDate }}</td></tr>
                                <tr><td class="muted">Seller</td><td class="text-right">{{ $sellerName }}</td></tr>
                                <tr>
                                    <td class="muted">Payment status</td>
                                    <td class="text-right">
                                        <span class="status-badge {{ $statusClass }}">{{ strtoupper($status) }}</span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </td>
                </tr>
            </table>

            <table class="items">
                <thead>
                    <tr>
                        <th style="width: 56%;">Description</th>
                        <th class="text-right" style="width: 14%;">Qty</th>
                        <th class="text-right" style="width: 15%;">Unit Price</th>
                        <th class="text-right" style="width: 15%;">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <div class="value-strong">{{ $itemName }}</div>
                            <div class="muted">{{ $itemDescription }}</div>
                        </td>
                        <td class="text-right">1</td>
                        <td class="text-right">{{ $currency }} {{ number_format($amount, 2) }}</td>
                        <td class="text-right">{{ $currency }} {{ number_format($amount, 2) }}</td>
                    </tr>
                </tbody>
            </table>

            <div class="summary-wrap">
                <table class="summary">
                    <tr>
                        <td class="muted">Subtotal</td>
                        <td class="text-right">{{ $currency }} {{ number_format($amount, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="muted">Tax</td>
                        <td class="text-right">{{ $currency }} 0.00</td>
                    </tr>
                    <tr class="total-row">
                        <td>Total</td>
                        <td class="text-right">{{ $currency }} {{ number_format($amount, 2) }}</td>
                    </tr>
                </table>
            </div>

            <div class="footer-note">
                This invoice was generated automatically by {{ config('app.name', 'LandingBuilder') }}.
            </div>
        </div>
    </div>
 </body>
</html>
