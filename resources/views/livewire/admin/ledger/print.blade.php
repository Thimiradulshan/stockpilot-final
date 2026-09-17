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
                {{ __('Stock Ledger') }}
            </h1>
            <p class="mt-1 text-sm font-semibold text-sp-text-muted">
                {{ __('Period') }}: {{ \Carbon\Carbon::parse($from)->format('d M Y') }} - {{ \Carbon\Carbon::parse($to)->format('d M Y') }}
            </p>
            @if ($type !== '')
                <p class="mt-1 text-sm font-semibold text-sp-text-muted">
                    {{ __('Type') }}: {{ __(ucfirst($type)) }}
                </p>
            @endif
            @if ($search !== '')
                <p class="mt-1 text-sm font-semibold text-sp-text-muted">
                    {{ __('Product search') }}: {{ $search }}
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
        @php
            $ledgerKpis = [
                'movements' => $data['kpis']['movements'] ?? 0,
                'units_in' => $data['kpis']['units_in'] ?? 0,
                'units_out' => $data['kpis']['units_out'] ?? 0,
                'net' => $data['kpis']['net'] ?? 0,
            ];
        @endphp

        @forelse ($ledgerKpis as $metricName => $metricValue)
            <div class="rounded-2xl border border-sp-border-strong bg-sp-surface p-5 shadow-sm">
                <p class="text-[11px] font-extrabold uppercase tracking-[0.08em] text-sp-text-muted">
                    {{ __(ucwords(str_replace('_', ' ', (string) $metricName))) }}
                </p>
                <p class="mt-1 text-2xl font-extrabold tabular-nums text-sp-text">
                    {{ number_format((float) $metricValue, $metricName === 'movements' ? 0 : 2) }}
                </p>
            </div>
        @empty
            <div class="text-center py-8">
                <p class="text-sm font-semibold text-sp-text-muted">
                    {{ __('No movements yet') }}
                </p>
            </div>
        @endforelse
    </div>
@endif

@if ($data['rows'] ?? null)
    @php
        $ledgerRows = collect($data['rows'] ?? [])->map(
            fn ($row) => (array) $row,
        )->values()->toArray();

        $ledgerColumns = array_keys($ledgerRows[0] ?? []);

        $textColumns = [
            'period',
            'sku',
            'product',
            'type',
            'operator',
            'date',
        ];
    @endphp

    <div class="overflow-x-auto">
        <table class="min-w-full border-collapse">
            <thead>
                <tr class="border-b border-sp-border-strong">
                    @forelse ($ledgerColumns as $columnKey)
                        <th scope="col" class="px-4 py-3 text-left text-xs font-extrabold uppercase tracking-[0.10em] text-sp-text">
                            {{ __(ucwords(str_replace('_', ' ', (string) $columnKey))) }}
                        </th>
                    @empty
                        <th scope="col" class="px-4 py-3 text-left text-xs font-extrabold uppercase tracking-[0.10em] text-sp-text">
                            {{ __('Period') }}
                        </th>
                    @endforelse
                </tr>
            </thead>
            <tbody class="divide-y divide-sp-border">
                @foreach ($ledgerRows as $row)
                    <tr>
                        @foreach ($row as $cellKey => $cellValue)
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