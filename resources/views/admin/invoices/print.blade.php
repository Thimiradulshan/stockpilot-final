<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $invoice->invoice_number }} | {{ config('stockpilot.business.name') }}</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 13px;
            line-height: 1.5;
            color: #1e293b;
            background: #e2e8f0;
            padding: 24px;
        }

        .toolbar {
            margin: 0 auto 16px;
            max-width: 760px;
            display: flex;
            gap: 8px;
            justify-content: flex-end;
        }

        .btn {
            border: 1px solid #cbd5e1;
            background: #ffffff;
            color: #1e293b;
            font-weight: 600;
            font-size: 13px;
            padding: 9px 16px;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary {
            background: #0f766e;
            border-color: #0f766e;
            color: #ffffff;
        }

        .receipt {
            max-width: 760px;
            margin: 0 auto;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 40px 44px;
        }

        .brand {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #0f766e;
            padding-bottom: 20px;
        }

        .brand h1 {
            font-size: 22px;
            color: #0f766e;
            letter-spacing: 0.5px;
        }

        .brand-identity {
            color: #475569;
            font-size: 12px;
            margin-top: 4px;
        }

        .doc-title {
            text-align: right;
        }

        .doc-title .t {
            font-size: 26px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 3px;
            color: #1e293b;
        }

        .doc-title .no {
            font-size: 13px;
            font-weight: 700;
            color: #0f766e;
            margin-top: 2px;
        }

        .meta-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            padding: 24px 0;
        }

        .block h2 {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: #94a3b8;
            margin-bottom: 6px;
        }

        .block p {
            color: #1e293b;
            margin-bottom: 2px;
        }

        .items {
            width: 100%;
            border-collapse: collapse;
            font-size: 12.5px;
        }

        .items thead th {
            border-bottom: 2px solid #e2e8f0;
            color: #64748b;
            text-transform: uppercase;
            font-size: 10px;
            letter-spacing: 1.5px;
            text-align: left;
            padding: 8px 10px;
        }

        .items thead th.num,
        .items tbody td.num {
            text-align: right;
        }

        .items tbody td {
            border-bottom: 1px solid #f1f5f9;
            padding: 10px;
            vertical-align: top;
        }

        .items tbody tr:last-child td {
            border-bottom: none;
        }

        .items .pn {
            color: #0f766e;
            font-size: 11px;
        }

        .summary {
            margin-top: 20px;
            border-top: 2px solid #e2e8f0;
            padding-top: 14px;
            display: flex;
            justify-content: flex-end;
        }

        .totals {
            width: 300px;
        }

        .totals .row {
            display: flex;
            justify-content: space-between;
            padding: 4px 0;
            color: #475569;
        }

        .totals .grand {
            background: #f0fdfa;
            border: 1px solid #99f6e4;
            border-radius: 8px;
            color: #0f766e;
            font-weight: 800;
            font-size: 15px;
            padding: 10px 12px;
            margin-top: 6px;
        }

        .totals .paid {
            color: #16a34a;
            font-weight: 700;
        }

        .totals .due {
            color: #dc2626;
            font-weight: 700;
        }

        .notes {
            margin-top: 24px;
            border-top: 1px dashed #cbd5e1;
            padding-top: 14px;
            color: #475569;
            font-size: 12px;
        }

        .footer {
            margin-top: 24px;
            text-align: center;
            color: #94a3b8;
            font-size: 11px;
            border-top: 1px solid #f1f5f9;
            padding-top: 14px;
        }

        @media print {
            body {
                background: #ffffff;
                padding: 0;
            }

            .toolbar {
                display: none;
            }

            .receipt {
                border: none;
                border-radius: 0;
                box-shadow: none;
                max-width: 100%;
                padding: 0;
            }

            .brand {
                border-bottom-color: #000000;
            }

            .brand h1 {
                color: #000000;
            }
        }
    </style>
</head>

<body onload="window.print()">

    <div class="toolbar">
        <a class="btn" href="{{ route('admin.sales.index') }}">&larr; {{ __('Back to sales') }}</a>
        <button class="btn btn-primary" type="button" onclick="window.print()">{{ __('Print') }}</button>
    </div>

    <article class="receipt">

        <header class="brand">
            <div>
                <h1>{{ config('stockpilot.business.name') }}</h1>

                @if (config('stockpilot.business.address'))
                    <div class="brand-identity">{{ config('stockpilot.business.address') }}</div>
                @endif

                @if (config('stockpilot.business.phone'))
                    <div class="brand-identity">{{ __('Phone:') }} {{ config('stockpilot.business.phone') }}</div>
                @endif

                @if (config('stockpilot.business.email'))
                    <div class="brand-identity">{{ config('stockpilot.business.email') }}</div>
                @endif

                @if (config('stockpilot.business.tax_number'))
                    <div class="brand-identity">{{ __('Tax No:') }} {{ config('stockpilot.business.tax_number') }}</div>
                @endif
            </div>

            <div class="doc-title">
                <div class="t">{{ $invoice->status === 'voided' ? __('Void Invoice') : __('Invoice') }}</div>
                <div class="no">{{ $invoice->invoice_number }}</div>
            </div>
        </header>

        <section class="meta-grid">

            <div class="block">
                <h2>{{ __('Billed to') }}</h2>

                @if ($invoice->customer)
                    <p class="customer-name">{{ $invoice->customer->name }}</p>

                    @if ($invoice->customer->address)
                        <p>{{ $invoice->customer->address }}</p>
                    @endif

                    @if ($invoice->customer->phone)
                        <p>{{ __('Phone:') }} {{ $invoice->customer->phone }}</p>
                    @endif

                    @if ($invoice->customer->email)
                        <p>{{ $invoice->customer->email }}</p>
                    @endif
                @else
                    <p>{{ __('Cash customer') }}</p>
                @endif
            </div>

            <div class="block">
                <h2>{{ __('Document details') }}</h2>

                <p>{{ __('Invoice date:') }} {{ $invoice->invoice_date->format('Y-m-d') }}</p>
                <p>{{ __('Status:') }} {{ ucwords(str_replace('_', ' ', $invoice->status)) }}</p>
                <p>{{ __('Payment:') }} {{ ucwords(str_replace('_', ' ', $invoice->payment_status)) }}</p>

                @if ($invoice->createdBy)
                    <p>{{ __('Prepared by:') }} {{ $invoice->createdBy->name }}</p>
                @endif
            </div>

        </section>

        <table class="items" aria-label="{{ __('Items') }}">
            <thead>
                <tr>
                    <th>{{ __('Item') }}</th>
                    <th class="num">{{ __('Qty') }}</th>
                    <th class="num">{{ __('Unit price') }}</th>
                    <th class="num">{{ __('Amount') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($invoice->items as $item)
                    <tr>
                        <td>
                            <span class="pn">{{ $item->product?->sku ?? '—' }}</span>
                            <br>
                            {{ $item->product?->name ?? __('Item') }}
                        </td>
                        <td class="num">{{ number_format((float) $item->quantity, 3) }}</td>
                        <td class="num">{{ number_format((float) $item->unit_price, 2) }}</td>
                        <td class="num">{{ number_format((float) $item->line_total, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">{{ __('No items on this document.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="summary">

            @if ($invoice->status !== 'voided')
                <div class="totals">
                    <div class="row">
                        <span>{{ __('Subtotal') }}</span>
                        <span>{{ number_format((float) $invoice->subtotal, 2) }}</span>
                    </div>

                    @if ((float) $invoice->discount_amount > 0)
                        <div class="row">
                            <span>{{ __('Discount') }}</span>
                            <span>- {{ number_format((float) $invoice->discount_amount, 2) }}</span>
                        </div>
                    @endif

                    @if ((float) $invoice->tax_amount > 0)
                        <div class="row">
                            <span>{{ __('Tax') }} ({{ number_format((float) $invoice->tax_rate, 2) }}%)</span>
                            <span>{{ number_format((float) $invoice->tax_amount, 2) }}</span>
                        </div>
                    @endif

                    <div class="row grand">
                        <span>{{ __('Total') }}</span>
                        <span>{{ number_format((float) $invoice->total_amount, 2) }}</span>
                    </div>

                    <div class="row paid">
                        <span>{{ __('Paid') }}</span>
                        <span>{{ number_format($paidTotal, 2) }}</span>
                    </div>

                    <div class="row due">
                        <span>{{ __('Balance due') }}</span>
                        <span>{{ number_format(max((float) $invoice->total_amount - $paidTotal, 0), 2) }}</span>
                    </div>
                </div>
            @else
                <div class="totals">
                    <div class="row grand">
                        <span>{{ __('Voided') }}</span>
                        <span>{{ __('This invoice has been voided.') }}</span>
                    </div>
                </div>
            @endif

        </div>

        @if ($invoice->payments->isNotEmpty())
            <div class="notes">
                <h2 style="font-size:10px;text-transform:uppercase;letter-spacing:1.5px;color:#94a3b8;margin-bottom:6px;">
                    {{ __('Payments received') }}
                </h2>

                @foreach ($invoice->payments as $payment)
                    <p>
                        {{ $payment->payment_date?->format('Y-m-d H:i') ?? '—' }}
                        &middot;
                        {{ ucwords(str_replace('_', ' ', $payment->payment_method)) }}
                        &middot;
                        {{ number_format((float) $payment->amount, 2) }}
                        @if ($payment->reference)
                            &middot; {{ $payment->reference }}
                        @endif
                    </p>
                @endforeach
            </div>
        @endif

        @if ($invoice->notes)
            <div class="notes">
                <h2 style="font-size:10px;text-transform:uppercase;letter-spacing:1.5px;color:#94a3b8;margin-bottom:6px;">
                    {{ __('Notes') }}
                </h2>
                <p>{{ $invoice->notes }}</p>
            </div>
        @endif

        <footer class="footer">
            {{ __('Printed on') }} {{ now()->format('Y-m-d H:i') }}
            @if (auth()->user())
                {{ __('by') }} {{ auth()->user()->name }}
            @endif
            &middot; {{ __('Thank you for your business.') }}
        </footer>

    </article>

</body>

</html>