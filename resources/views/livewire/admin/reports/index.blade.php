<x-stockpilot.admin-page>

        <x-stockpilot.page-header
            section-label="{{ __('Operations') }}"
            section-current="{{ __('Reports') }}"
            :title="$reportTitle"
            :subtitle="__('Performance summaries across sales, purchasing, margins, stock valuation and VAT.')"
            :description="__('All amounts are shown in the store currency for the selected period.')"
        >

            <x-slot:actions>

                <x-stockpilot.primary-action
                    wire:click="exportCsv"
                    :label="__('Download CSV')"
                    icon="file-text"
                />

                <a
                    href="{{ route('admin.reports.index', array_merge(request()->query(), ['print' => '1'])) }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex items-center justify-center gap-2 rounded-xl border border-sp-border-strong bg-sp-surface px-4 py-2.5 text-sm font-bold text-sp-text transition hover:bg-sp-surface-muted"
                >
                    <x-stockpilot.icon name="printer" class="h-4 w-4" />
                    {{ __('Print') }}
                </a>

            </x-slot:actions>

        </x-stockpilot.page-header>

        <x-stockpilot.toast-alerts />

        @php

            $tabDefinitions = [
                ['key' => 'sales', 'label' => __('Sales'), 'icon' => 'reports'],
                ['key' => 'outstanding', 'label' => __('Outstanding'), 'icon' => 'credit-card'],
                ['key' => 'purchases', 'label' => __('Purchases'), 'icon' => 'purchasing'],
                ['key' => 'profit', 'label' => __('Profit'), 'icon' => 'reports'],
                ['key' => 'valuation', 'label' => __('Stock valuation'), 'icon' => 'products'],
                ['key' => 'vat', 'label' => __('VAT'), 'icon' => 'reports'],
            ];

            $tabs = collect($tabDefinitions)
                ->filter(fn (array $tab) => in_array($tab['key'], $allowed, true))
                ->map(fn (array $tab) => [
                    'label' => $tab['label'],
                    'icon' => $tab['icon'],
                    'href' => route('admin.reports.index', ['section' => $tab['key']]),
                    'active' => $section === $tab['key'],
                ])
                ->values()
                ->all();

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

        <x-stockpilot.tabs :items="$tabs" aria-label="{{ __('Report sections') }}" />

        <x-stockpilot.filter-bar aria-label="{{ __('Report filters') }}">

            <label for="reports_from" class="text-sm font-bold text-sp-text">{{ __('From') }}</label>

            <input
                id="reports_from"
                type="date"
                wire:model.blur="from"
                class="block w-full rounded-xl border border-sp-border bg-sp-surface px-3.5 py-2.5 text-sm text-sp-text outline-none transition focus:border-sp-primary focus:ring-4 focus:ring-sp-primary/10 dark:border-sp-border dark:bg-sp-surface-soft dark:text-sp-text dark:focus:border-sp-success"
            />

            <label for="reports_to" class="text-sm font-bold text-sp-text">{{ __('To') }}</label>

            <input
                id="reports_to"
                type="date"
                wire:model.blur="to"
                class="block w-full rounded-xl border border-sp-border bg-sp-surface px-3.5 py-2.5 text-sm text-sp-text outline-none transition focus:border-sp-primary focus:ring-4 focus:ring-sp-primary/10 dark:border-sp-border dark:bg-sp-surface-soft dark:text-sp-text dark:focus:border-sp-success"
            />

            @if ($section === 'sales')

                <label for="reports_customer" class="text-sm font-bold text-sp-text">{{ __('Customer') }}</label>

                <select
                    id="reports_customer"
                    wire:model.live="customer"
                    class="block w-full rounded-xl border border-sp-border bg-sp-surface px-3.5 py-2.5 text-sm text-sp-text outline-none transition focus:border-sp-primary focus:ring-4 focus:ring-sp-primary/10 dark:border-sp-border dark:bg-sp-surface-soft dark:text-sp-text dark:focus:border-sp-success"
                >
                    <option value="">{{ __('All customers') }}</option>

                    @foreach ($customers as $customerOption)
                        <option value="{{ $customerOption->id }}">{{ $customerOption->name }}</option>
                    @endforeach

                </select>

                <label for="reports_group" class="text-sm font-bold text-sp-text">{{ __('Group by') }}</label>

                <select
                    id="reports_group"
                    wire:model.live="groupBy"
                    class="block w-full rounded-xl border border-sp-border bg-sp-surface px-3.5 py-2.5 text-sm text-sp-text outline-none transition focus:border-sp-primary focus:ring-4 focus:ring-sp-primary/10 dark:border-sp-border dark:bg-sp-surface-soft dark:text-sp-text dark:focus:border-sp-success"
                >
                    <option value="day">{{ __('Day') }}</option>
                    <option value="week">{{ __('Week') }}</option>
                    <option value="month">{{ __('Month') }}</option>
                </select>

            @elseif ($section === 'purchases')

                <label for="reports_supplier" class="text-sm font-bold text-sp-text">{{ __('Supplier') }}</label>

                <select
                    id="reports_supplier"
                    wire:model.live="supplier"
                    class="block w-full rounded-xl border border-sp-border bg-sp-surface px-3.5 py-2.5 text-sm text-sp-text outline-none transition focus:border-sp-primary focus:ring-4 focus:ring-sp-primary/10 dark:border-sp-border dark:bg-sp-surface-soft dark:text-sp-text dark:focus:border-sp-success"
                >
                    <option value="">{{ __('All suppliers') }}</option>

                    @foreach ($suppliers as $supplierOption)
                        <option value="{{ $supplierOption->id }}">{{ $supplierOption->name }}</option>
                    @endforeach

                </select>

            @endif

        </x-stockpilot.filter-bar>

        
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

            @forelse ($data['kpis'] ?? [] as $metricName => $kpi)

                <x-stockpilot.kpi-card
                    :label="__(ucwords(str_replace('_', ' ', (string) $metricName)))"
                    :value="number_format((float) $kpi, 2)"
                    :hint="__('Selected period')"
                    icon="reports"
                    tone="primary"
                />

            @empty

                <x-stockpilot.empty-state
                    icon="reports"
                    :title="__('No data in this period')"
                    :message="__('Try widening the date range or selecting another section.')"
                />

            @endforelse

        </div>

        @if ($section === 'sales')

            
            <section aria-label="{{ __('Sales invoice detail') }}">

                <h2 class="mb-3 text-lg font-extrabold tracking-tight text-sp-text">
                    {{ __('Invoice detail') }}
                </h2>

                <x-stockpilot.page-table mobile="lg" aria-label="{{ __('Sales invoice detail') }}">

                    <table class="min-w-full border-collapse">

                        <thead>

                            <tr class="border-b border-sp-border-strong dark:border-sp-border-strong">
                                <th scope="col" class="px-6 py-4 text-left text-xs font-extrabold uppercase tracking-[0.10em] text-sp-text">{{ __('Invoice') }}</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-extrabold uppercase tracking-[0.10em] text-sp-text">{{ __('Date') }}</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-extrabold uppercase tracking-[0.10em] text-sp-text">{{ __('Customer') }}</th>
                                <th scope="col" class="px-6 py-4 text-right text-xs font-extrabold uppercase tracking-[0.10em] text-sp-text">{{ __('Total') }}</th>
                                <th scope="col" class="px-6 py-4 text-right text-xs font-extrabold uppercase tracking-[0.10em] text-sp-text">{{ __('Paid') }}</th>
                                <th scope="col" class="px-6 py-4 text-right text-xs font-extrabold uppercase tracking-[0.10em] text-sp-text">{{ __('Balance') }}</th>
                            </tr>

                        </thead>

                        <tbody class="divide-y divide-sp-border dark:divide-sp-border">

                            @forelse ($salesDetail as $row)

                                <tr class="transition-colors hover:bg-sp-surface-muted dark:hover:bg-sp-surface-muted">
                                    <td class="px-6 py-4 text-sm font-bold text-sp-primary dark:text-sp-success">{{ $row['invoice_number'] }}</td>
                                    <td class="px-6 py-4 text-sm text-sp-text">{{ $row['invoice_date'] }}</td>
                                    <td class="px-6 py-4 text-sm text-sp-text">{{ $row['customer'] }}</td>
                                    <td class="px-6 py-4 text-right text-sm font-semibold text-sp-text">{{ number_format($row['total_amount'], 2) }}</td>
                                    <td class="px-6 py-4 text-right text-sm font-semibold text-sp-text">{{ number_format($row['paid_amount'], 2) }}</td>
                                    <td class="px-6 py-4 text-right text-sm font-bold {{ $row['balance'] > 0 ? 'text-sp-warning' : 'text-sp-success' }}">
                                        {{ number_format($row['balance'], 2) }}
                                    </td>
                                </tr>

                            @empty

                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center">
                                        <x-stockpilot.empty-state
                                            icon="receipt"
                                            :title="__('No invoices in this period')"
                                            :message="__('Try widening the date range or clearing the customer filter.')"
                                        />
                                    </td>
                                </tr>

                            @endforelse

                        </tbody>

                    </table>

                </x-stockpilot.page-table>

            </section>

            
            @if ($data['rows'] ?? null)

                <section aria-label="{{ __('Sales trend') }}" class="mt-8">

                    <h2 class="mb-3 text-lg font-extrabold tracking-tight text-sp-text">
                        {{ __('Sales trend') }}
                    </h2>

                    <x-stockpilot.page-table mobile="lg" aria-label="{{ __('Sales trend') }}">

                        <table class="min-w-full border-collapse">

                            <thead>

                                <tr class="border-b border-sp-border-strong dark:border-sp-border-strong">

                                    @php
                                        $trendColumns = array_keys($data['rows'][0] ?? []);
                                    @endphp

                                    @forelse ($trendColumns as $columnKey)

                                        <th scope="col" class="px-6 py-4 text-left text-xs font-extrabold uppercase tracking-[0.10em] text-sp-text">
                                            {{ __(ucwords(str_replace('_', ' ', (string) $columnKey))) }}
                                        </th>

                                    @empty

                                        <th scope="col" class="px-6 py-4 text-left text-xs font-extrabold uppercase tracking-[0.10em] text-sp-text">{{ __('Period') }}</th>

                                    @endforelse

                                </tr>

                            </thead>

                            <tbody class="divide-y divide-sp-border dark:divide-sp-border">

                                @foreach ($data['rows'] as $row)

                                    <tr class="transition-colors hover:bg-sp-surface-muted dark:hover:bg-sp-surface-muted">

                                        @foreach ($row as $cellKey => $cellValue)

                                            <td class="px-6 py-4 text-sm text-sp-text">
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

                    </x-stockpilot.page-table>

                </section>

            @endif

        @else

            
            @if ($data['rows'] ?? null)

                @php

                    $reportRows = collect($data['rows'] ?? [])->map(
                        fn ($row) => (array) $row,
                    )->values()->toArray();

                    $reportColumns = array_keys($reportRows[0] ?? []);

                @endphp

                <x-stockpilot.page-table mobile="lg" aria-label="{{ __('Report results') }}">

                    <table class="min-w-full border-collapse">

                        <thead>

                            <tr class="border-b border-sp-border-strong dark:border-sp-border-strong">

                                @forelse ($reportColumns as $columnKey)

                                    <th scope="col" class="px-6 py-4 text-left text-xs font-extrabold uppercase tracking-[0.10em] text-sp-text">
                                        {{ __(ucwords(str_replace('_', ' ', (string) $columnKey))) }}
                                    </th>

                                @empty

                                    <th scope="col" class="px-6 py-4 text-left text-xs font-extrabold uppercase tracking-[0.10em] text-sp-text">
                                        {{ __('Period') }}
                                    </th>

                                @endforelse

                            </tr>

                        </thead>

                        <tbody class="divide-y divide-sp-border dark:divide-sp-border">

                            @foreach (($data['rows'] ?? []) as $row)

                                <tr class="transition-colors hover:bg-sp-surface-muted dark:hover:bg-sp-surface-muted">

                                    @foreach (($row ?? []) as $cellKey => $cellValue)

                                        <td class="px-6 py-4 text-sm text-sp-text">
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

                </x-stockpilot.page-table>

            @elseif ($section !== 'vat')

                <x-stockpilot.empty-state
                    icon="reports"
                    :title="__('No data in this period')"
                    :message="__('Try widening the date range or selecting another section.')"
                />

            @endif

        @endif

    </x-stockpilot.admin-page>
