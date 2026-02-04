<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice</title>
    <style>
        body { font-family: sans-serif; }
        .invoice-box {
            max-width: 800px;
            margin: auto;
            padding: 30px;
            border: 1px solid #eee;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.15);
            font-size: 16px;
            line-height: 24px;
            color: #555;
        }
        .invoice-box table {
            width: 100%;
            line-height: inherit;
            text-align: left;
        }
        .invoice-box table td {
            padding: 5px;
            vertical-align: top;
        }
        .invoice-box table tr td:nth-child(2) {
            text-align: right;
        }
        .top { padding-bottom: 20px; }
        .top h1 { margin: 0; color: #333; }
        .information { padding-bottom: 40px; }
        .heading { background: #eee; border-bottom: 1px solid #ddd; font-weight: bold; }
        .item { border-bottom: 1px solid #eee; }
        .item.last { border-bottom: none; }
        .total { border-top: 2px solid #eee; font-weight: bold; }
    </style>
</head>
<body>
    <div class="invoice-box">
        <table cellpadding="0" cellspacing="0">
            <tr class="top">
                <td colspan="2">
                    <table>
                        <tr>
                            <td class="title">
                                <h1>INVOICE</h1>
                            </td>
                            <td>
                                Invoice #: {{ $lead->transaction_id }}<br>
                                Date: {{ $lead->created_at->format('Y-m-d') }}<br>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr class="information">
                <td colspan="2">
                    <table>
                        <tr>
                            <td>
                                <strong>Billed To:</strong><br>
                                {{ $lead->first_name }} {{ $lead->last_name }}<br>
                                {{ $lead->address }}<br>
                                {{ $lead->city }}, {{ $lead->zip }}<br>
                                {{ $lead->country }}
                            </td>
                            <td>
                                <strong>Seller:</strong><br>
                                {{ $lead->landing->workspace->name }}<br>
                                {{-- Add seller address if available in settings --}}
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr class="heading">
                <td>Item</td>
                <td>Price</td>
            </tr>
            <tr class="item">
                <td>{{ $lead->product->name ?? 'Product' }}</td>
                <td>{{ $lead->currency }} {{ number_format($lead->amount, 2) }}</td>
            </tr>
            <tr class="total">
                <td></td>
                <td>Total: {{ $lead->currency }} {{ number_format($lead->amount, 2) }}</td>
            </tr>
        </table>
    </div>
</body>
</html>
