<section aria-label="{{ __('Sales summary') }}">
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

        <x-stockpilot.kpi-card
            :label="__('Total invoices')"
            :value="number_format($totalInvoices)"
            :hint="__('Entire sales history')"
            icon="receipt"
            tone="primary"
        />

        <x-stockpilot.kpi-card
            :label="__('Completed value')"
            :value="'Rs ' . number_format((float) $completedInvoiceValue, 2)"
            :hint="__('Total invoiced value')"
            icon="check"
            tone="success"
        />

        <x-stockpilot.kpi-card
            :label="__('Outstanding')"
            :value="'Rs ' . number_format((float) $outstandingBalance, 2)"
            :hint="__('Unpaid and partially paid')"
            icon="credit-card"
            tone="info"
        />

        <x-stockpilot.kpi-card
            :label="__('Voided')"
            :value="number_format($voidedInvoices)"
            :hint="__('Stock restored')"
            icon="x-circle"
            tone="danger"
        />

    </div>
</section>