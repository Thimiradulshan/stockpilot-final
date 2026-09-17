<x-stockpilot.admin-page>

    <x-stockpilot.page-header
        section-label="{{ __('Operations') }}"
        section-current="{{ __('Statements') }}"
        :title="__('Customer statement')"
        :subtitle="__('Per-customer account activity')"
        :description="__('Review invoices, payments and the running outstanding balance for any customer within a date range.')"
        :tags="[
            ['label' => __('Sales'), 'tone' => 'primary'],
            ['label' => __('Live'), 'tone' => 'info'],
        ]"
    />

    <x-stockpilot.toast-alerts />

    <x-stockpilot.filter-bar aria-label="{{ __('Statement filters') }}">

        <label for="statement_customer" class="text-sm font-bold text-sp-text">{{ __('Customer') }}</label>

        <select
            id="statement_customer"
            wire:model.live="customer"
            class="block w-full rounded-xl border border-sp-border bg-sp-surface px-3.5 py-2.5 text-sm text-sp-text outline-none transition focus:border-sp-primary focus:ring-4 focus:ring-sp-primary/10 dark:border-sp-border dark:bg-sp-surface-soft dark:text-sp-text dark:focus:border-sp-success"
        >
            <option value="">{{ __('Select a customer...') }}</option>

            @foreach ($customers as $customer)
                <option value="{{ $customer->id }}">{{ $customer->name }}</option>
            @endforeach

        </select>

        <label for="statement_from" class="text-sm font-bold text-sp-text">{{ __('From') }}</label>

        <input
            id="statement_from"
            type="date"
            wire:model.blur="from"
            class="block w-full rounded-xl border border-sp-border bg-sp-surface px-3.5 py-2.5 text-sm text-sp-text outline-none transition focus:border-sp-primary focus:ring-4 focus:ring-sp-primary/10 dark:border-sp-border dark:bg-sp-surface-soft dark:text-sp-text dark:focus:border-sp-success"
        />

        <label for="statement_to" class="text-sm font-bold text-sp-text">{{ __('To') }}</label>

        <input
            id="statement_to"
            type="date"
            wire:model.blur="to"
            class="block w-full rounded-xl border border-sp-border bg-sp-surface px-3.5 py-2.5 text-sm text-sp-text outline-none transition focus:border-sp-primary focus:ring-4 focus:ring-sp-primary/10 dark:border-sp-border dark:bg-sp-surface-soft dark:text-sp-text dark:focus:border-sp-success"
        />

    </x-stockpilot.filter-bar>

    @if (! $data['selected'])

        <x-stockpilot.empty-state
            icon="customers"
            :title="__('Choose a customer')"
            :message="__('Select a customer above to preview their invoice and payment statement.')"
        />

    @else

        <section aria-label="{{ __('Statement summary') }}">

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

                <x-stockpilot.kpi-card
                    :label="__('Sales amount')"
                    :value="'Rs. ' . number_format($data['sales_total'], 2)"
                    :hint="$data['customer']?->name ?? ''"
                    icon="receipt"
                    tone="primary"
                />

                <x-stockpilot.kpi-card
                    :label="__('Payment amount')"
                    :value="'Rs. ' . number_format($data['paid_total'], 2)"
                    :hint="__('Received in this period')"
                    icon="credit-card"
                    tone="success"
                />

                <x-stockpilot.kpi-card
                    :label="__('Outstanding balance')"
                    :value="'Rs. ' . number_format($data['closing_balance'], 2)"
                    :hint="__('Running balance at period end')"
                    icon="alert-triangle"
                    tone="warning"
                />

                <x-stockpilot.kpi-card
                    :label="__('Invoices')"
                    :value="number_format($data['invoice_count'])"
                    :hint="__('Completed invoices')"
                    icon="file-text"
                    tone="info"
                />

            </div>

        </section>

        <x-stockpilot.page-table mobile="lg" aria-label="{{ __('Customer statement') }}">

            <table class="min-w-full border-collapse">

                <thead>

                    <tr class="border-b border-sp-border-strong dark:border-sp-border-strong">
                        <th scope="col" class="px-6 py-4 text-left text-xs font-extrabold uppercase tracking-[0.10em] text-sp-text">{{ __('Date') }}</th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-extrabold uppercase tracking-[0.10em] text-sp-text">{{ __('Invoice #') }}</th>
                        <th scope="col" class="px-6 py-4 text-right text-xs font-extrabold uppercase tracking-[0.10em] text-sp-text">{{ __('Sales amount') }}</th>
                        <th scope="col" class="px-6 py-4 text-right text-xs font-extrabold uppercase tracking-[0.10em] text-sp-text">{{ __('Payment amount') }}</th>
                        <th scope="col" class="px-6 py-4 text-right text-xs font-extrabold uppercase tracking-[0.10em] text-sp-text">{{ __('Outstanding balance') }}</th>
                    </tr>

                </thead>

                <tbody class="divide-y divide-sp-border dark:divide-sp-border">

                    @forelse ($data['rows'] as $row)

                        <tr class="transition-colors hover:bg-sp-surface-muted dark:hover:bg-sp-surface-muted">
                            <td class="px-6 py-4 text-sm text-sp-text">{{ $row['date'] }}</td>
                            <td class="px-6 py-4 text-sm font-bold text-sp-primary dark:text-sp-success">{{ $row['invoice_number'] }}</td>
                            <td class="px-6 py-4 text-right text-sm font-semibold text-sp-text">
                                @if ($row['type'] === 'invoice')
                                    Rs. {{ number_format($row['sales_amount'], 2) }}
                                @else
                                    <span class="text-sp-text-muted">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right text-sm font-semibold text-sp-success">
                                @if ($row['type'] === 'payment')
                                    Rs. {{ number_format($row['payment_amount'], 2) }}
                                @else
                                    <span class="text-sp-text-muted">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right text-sm font-extrabold {{ $row['outstanding_balance'] > 0 ? 'text-sp-warning' : 'text-sp-success' }}">
                                Rs. {{ number_format($row['outstanding_balance'], 2) }}
                            </td>
                        </tr>

                    @empty

                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <x-stockpilot.empty-state
                                    icon="receipt"
                                    :title="__('No activity in this period')"
                                    :message="__('Try widening the date range for this customer.')"
                                />
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </x-stockpilot.page-table>

    @endif

</x-stockpilot.admin-page>