<x-stockpilot.admin-page>

    <x-stockpilot.page-header
        section-label="{{ __('Operations') }}"
        section-current="{{ __('Stock ledger') }}"
        :title="__('Stock Ledger')"
        :subtitle="__('A complete, immutable history of stock movements across your inventory.')"
        :description="__('Every purchase, sale, adjustment and return is recorded here as it happens.')"
    >

        <x-slot:actions>

            <x-stockpilot.primary-action
                wire:click="exportCsv"
                :label="__('Download CSV')"
                icon="file-text"
            />

            <a
                href="{{ route('admin.ledger.index', array_merge(request()->query(), ['print' => '1'])) }}"
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

    <x-stockpilot.filter-bar aria-label="{{ __('Ledger filters') }}">

        <label for="ledger_from" class="text-sm font-bold text-sp-text">{{ __('From') }}</label>

        <input
            id="ledger_from"
            type="date"
            wire:model.blur="from"
            class="block w-full rounded-xl border border-sp-border bg-sp-surface px-3.5 py-2.5 text-sm text-sp-text outline-none transition focus:border-sp-primary focus:ring-4 focus:ring-sp-primary/10 dark:border-sp-border dark:bg-sp-surface-soft dark:text-sp-text dark:focus:border-sp-success"
        />

        <label for="ledger_to" class="text-sm font-bold text-sp-text">{{ __('To') }}</label>

        <input
            id="ledger_to"
            type="date"
            wire:model.blur="to"
            class="block w-full rounded-xl border border-sp-border bg-sp-surface px-3.5 py-2.5 text-sm text-sp-text outline-none transition focus:border-sp-primary focus:ring-4 focus:ring-sp-primary/10 dark:border-sp-border dark:bg-sp-surface-soft dark:text-sp-text dark:focus:border-sp-success"
        />

        <label for="ledger_type" class="text-sm font-bold text-sp-text">{{ __('Type') }}</label>

        <select
            id="ledger_type"
            wire:model.live="movementType"
            class="block w-full rounded-xl border border-sp-border bg-sp-surface px-3.5 py-2.5 text-sm text-sp-text outline-none transition focus:border-sp-primary focus:ring-4 focus:ring-sp-primary/10 dark:border-sp-border dark:bg-sp-surface-soft dark:text-sp-text dark:focus:border-sp-success"
        >
            <option value="">{{ __('All types') }}</option>

            @foreach (['purchase', 'sale', 'adjustment', 'return', 'correction'] as $type)

                <option value="{{ $type }}">{{ __(ucfirst($type)) }}</option>

            @endforeach

        </select>

        <label for="ledger_product" class="text-sm font-bold text-sp-text">{{ __('Product') }}</label>

        <input
            id="ledger_product"
            type="search"
            wire:model.live.debounce.300ms="search"
            placeholder="{{ __('Search by name or SKU…') }}"
            class="block w-full rounded-xl border border-sp-border bg-sp-surface px-3.5 py-2.5 text-sm text-sp-text outline-none transition focus:border-sp-primary focus:ring-4 focus:ring-sp-primary/10 dark:border-sp-border dark:bg-sp-surface-soft dark:text-sp-text dark:focus:border-sp-success"
        />

    </x-stockpilot.filter-bar>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

        @php

            $ledgerKpis = [
                'movements' => $data['kpis']['movements'] ?? 0,
                'units_in' => $data['kpis']['units_in'] ?? 0,
                'units_out' => $data['kpis']['units_out'] ?? 0,
                'net' => $data['kpis']['net'] ?? 0,
            ];

        @endphp

        @forelse ($ledgerKpis as $metricName => $metricValue)

            <x-stockpilot.kpi-card
                :label="__(ucwords(str_replace('_', ' ', (string) $metricName)))"
                :value="number_format((float) $metricValue, $metricName === 'movements' ? 0 : 2)"
                :hint="__('Current period')"
                :icon="'ledger'"
            />

        @empty

            <x-stockpilot.empty-state
                icon="ledger"
                :title="__('No movements yet')"
                :message="__('Stock movements will appear here as they are recorded.')"
            />

        @endforelse

    </div>

    @if ($data['rows'] ?? null)

        @php

            $ledgerRows = collect($data['rows'] ?? [])->map(
                fn ($row) => (array) $row,
            )->values()->toArray();

            $ledgerColumns = array_keys($ledgerRows[0] ?? []);

        @endphp

        <x-stockpilot.page-table mobile="lg" aria-label="{{ __('Ledger results') }}">

            <table class="min-w-full border-collapse">

                <thead>

                    <tr class="border-b border-sp-border-strong dark:border-sp-border-strong">

                        @forelse ($ledgerColumns as $columnKey)

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

                    @foreach ($ledgerRows as $row)

                        <tr class="transition-colors hover:bg-sp-surface-muted dark:hover:bg-sp-surface-muted">

                            @foreach ($row as $cellKey => $cellValue)

                                <td class="px-6 py-4 text-sm text-sp-text">
                                    {{ $cellKey === 'period' || $cellKey === 'sku' || $cellKey === 'product' || $cellKey === 'type' || $cellKey === 'reference' || $cellKey === 'operator' || $cellKey === 'date' ? $cellValue : number_format((float) $cellValue, 2) }}
                                </td>

                            @endforeach

                        </tr>

                    @endforeach

                </tbody>

            </table>

        </x-stockpilot.page-table>

    @endif

</x-stockpilot.admin-page>
