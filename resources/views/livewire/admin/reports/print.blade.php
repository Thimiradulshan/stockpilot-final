<div class="no-print mb-6 text-center">
    <button
        onclick="window.print()"
        class="inline-flex items-center justify-center gap-2 rounded-xl bg-sp-primary px-4 py-2.5 text-sm font-bold text-sp-primary-foreground shadow-lg shadow-sp-primary/20 transition hover:-translate-y-0.5 hover:bg-sp-primary-hover focus:outline-none focus:ring-4 focus:ring-sp-primary/15"
    >
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
        </svg>
        {{ __('Print') }}
    </button>
</div>

<div class="print-header mb-8 border-b-2 border-sp-text pb-4">
    <div class="flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold tracking-tight text-sp-text">
                {{ $reportTitle }}
            </h1>
            <p class="mt-1 text-sm font-semibold text-sp-text-muted">
                {{ __('Period') }}: {{ \Carbon\Carbon::parse($from)->format('d M Y') }} - {{ \Carbon\Carbon::parse($to)->format('d M Y') }}
            </p>
            @if ($section === 'sales' && $customer !== '')
                <p class="mt-1 text-sm font-semibold text-sp-text-muted">
                    {{ __('Customer') }}: {{ $customers->where('id', $customer)->first()?->name ?? '' }}
                </p>
            @endif
            @if ($section === 'purchases' && $supplier !== '')
                <p class="mt-1 text-sm font-semibold text-sp-text-muted">
                    {{ __('Supplier') }}: {{ $suppliers->where('id', $supplier)->first()?->name ?? '' }}
                </p>
            @endif
            @if ($section === 'sales')
                <p class="mt-1 text-sm font-semibold text-sp-text-muted">
                    {{ __('Group by') }}: {{ ucfirst($groupBy) }}
                </p>
            @endif
        </div>
        <div class="text-right text-sm text-sp-text-muted">
            <p>{{ config('stockpilot.business.name') }}</p>
            <p>{{ config('stockpilot.business.address') }}</p>
            <p>{{ __('Printed on') }} {{ \Carbon\Carbon::now()->format('d M Y H:i') }}</p>
        </div>
    </div>
</div>

@if ($data['kpis'] ?? null)
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4 mb-8">
        @foreach ($data['kpis'] as $metricName => $kpi)
            <div class="rounded-2xl border border-sp-border-strong bg-sp-surface p-5 shadow-sm">
                <p class="text-[11px] font-extrabold uppercase tracking-[0.08em] text-sp-text-muted">
                    {{ __(ucwords(str_replace('_', ' ', (string) $metricName))) }}
                </p>
                <p class="mt-1 text-2xl font-extrabold tabular-nums text-sp-text">
                    {{ number_format((float) $kpi, 2) }}
                </p>
            </div>
        @endforeach
    </div>
@endif

@if ($section === 'sales')
    @if ($salesDetail ?? null)
        <section aria-label="{{ __('Sales invoice detail') }}" class="mb-8">
            <h2 class="mb-3 text-lg font-extrabold tracking-tight text-sp-text">
                {{ __('Invoice detail') }}
            </h2>

            <div class="overflow-x-auto">
                <table class="min-w-full border-collapse">
                    <thead>
                        <tr class="border-b border-sp-border-strong">
                            <th scope="col" class="px-4 py-3 text-left text-xs font-extrabold uppercase tracking-[0.10em] text-sp-text">{{ __('Invoice') }}</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-extrabold uppercase tracking-[0.10em] text-sp-text">{{ __('Date') }}</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-extrabold uppercase tracking-[0.10em] text-sp-text">{{ __('Customer') }}</th>
                            <th scope="col" class="px-4 py-3 text-right text-xs font-extrabold uppercase tracking-[0.10em] text-sp-text">{{ __('Total') }}</th>
                            <th scope="col" class="px-4 py-3 text-right text-xs font-extrabold uppercase tracking-[0.10em] text-sp-text">{{ __('Paid') }}</th>
                            <th scope="col" class="px-4 py-3 text-right text-xs font-extrabold uppercase tracking-[0.10em] text-sp-text">{{ __('Balance') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-sp-border">
                        @forelse ($salesDetail as $row)
                            <tr>
                                <td class="px-4 py-3 text-sm font-bold text-sp-primary">{{ $row['invoice_number'] }}</td>
                                <td class="px-4 py-3 text-sm text-sp-text">{{ $row['invoice_date'] }}</td>
                                <td class="px-4 py-3 text-sm text-sp-text">{{ $row['customer'] }}</td>
                                <td class="px-4 py-3 text-right text-sm font-semibold text-sp-text">{{ number_format($row['total_amount'], 2) }}</td>
                                <td class="px-4 py-3 text-right text-sm font-semibold text-sp-text">{{ number_format($row['paid_amount'], 2) }}</td>
                                <td class="px-4 py-3 text-right text-sm font-bold {{ $row['balance'] > 0 ? 'text-sp-warning' : 'text-sp-success' }}">
                                    {{ number_format($row['balance'], 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-sm font-semibold text-sp-text-muted">
                                    {{ __('No invoices in this period') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    @endif

    @if ($data['rows'] ?? null)
        <section aria-label="{{ __('Sales trend') }}" class="mb-8">
            <h2 class="mb-3 text-lg font-extrabold tracking-tight text-sp-text">
                {{ __('Sales trend') }}
            </h2>

            <div class="overflow-x-auto">
                <table class="min-w-full border-collapse">
                    <thead>
                        <tr class="border-b border-sp-border-strong">
                            @php
                                $trendColumns = array_keys($data['rows'][0] ?? []);
                            @endphp
                            @forelse ($trendColumns as $columnKey)
                                <th scope="col" class="px-4 py-3 text-left text-xs font-extrabold uppercase tracking-[0.10em] text-sp-text">
                                    {{ __(ucwords(str_replace('_', ' ', (string) $columnKey))) }}
                                </th>
                            @empty
                                <th scope="col" class="px-4 py-3 text-left text-xs font-extrabold uppercase tracking-[0.10em] text-sp-text">{{ __('Period') }}</th>
                            @endforelse
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-sp-border">
                        @foreach ($data['rows'] as $row)
                            <tr>
                                @foreach ($row as $cellKey => $cellValue)
                                    <td class="px-4 py-3 text-sm text-sp-text">
                                        @if ($cellKey === 'period')
                                            {{ $cellValue }}
                                        @else
                                            {{ number_format((float) $cellValue, 2) }}
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    @endif

@else
    @if ($data['rows'] ?? null)
        @php
            $reportRows = collect($data['rows'] ?? [])->map(
                fn ($row) => (array) $row,
            )->values()->toArray();

            $reportColumns = array_keys($reportRows[0] ?? []);

            $textColumns = [
                'period',
                'name',
                'sku',
                'category',
                'customer',
                'supplier',
                'purchase_number',
                'purchase_date',
                'invoice_number',
                'invoice_date',
                'status',
            ];
        @endphp

        <div class="overflow-x-auto">
            <table class="min-w-full border-collapse">
                <thead>
                    <tr class="border-b border-sp-border-strong">
                        @forelse ($reportColumns as $columnKey)
                            <th scope="col" class="px-4 py-3 text-left text-xs font-extrabold uppercase tracking-[0.10em] text-sp-text">
                                {{ __(ucwords(str_replace('_', ' ', (string) $columnKey))) }}
                            </th>
                        @empty
                            <th scope="col" class="px-4 py-3 text-left text-xs font-extrabold uppercase tracking-[0.10em] text-sp-text">{{ __('Period') }}</th>
                        @endforelse
                    </tr>
                </thead>
                <tbody class="divide-y divide-sp-border">
                    @foreach (($data['rows'] ?? []) as $row)
                        <tr>
                            @foreach (($row ?? []) as $cellKey => $cellValue)
                                <td class="px-4 py-3 text-sm text-sp-text">
                                    @if (in_array((string) $cellKey, $textColumns, true))
                                        {{ $cellValue }}
                                    @else
                                        {{ number_format((float) $cellValue, 2) }}
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @elseif ($section !== 'vat')
        <div class="text-center py-8">
            <p class="text-sm font-semibold text-sp-text-muted">
                {{ __('No data in this period') }}
            </p>
        </div>
    @endif

@endif

<div class="no-print mt-8 text-center">
    <button
        onclick="window.print()"
        class="inline-flex items-center justify-center gap-2 rounded-xl bg-sp-primary px-4 py-2.5 text-sm font-bold text-sp-primary-foreground shadow-lg shadow-sp-primary/20 transition hover:-translate-y-0.5 hover:bg-sp-primary-hover focus:outline-none focus:ring-4 focus:ring-sp-primary/15"
    >
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
        </svg>
        {{ __('Print') }}
    </button>
</div>