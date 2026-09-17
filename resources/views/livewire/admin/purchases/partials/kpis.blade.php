<section aria-label="{{ __('Purchase summary') }}">
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

        <x-stockpilot.kpi-card
            :label="__('Total purchases')"
            :value="number_format($totalPurchases)"
            :hint="__('Entire purchase history')"
            icon="shopping-cart"
            tone="primary"
        />

        <x-stockpilot.kpi-card
            :label="__('Completed')"
            :value="number_format($completedPurchases)"
            :hint="__('Stock received')"
            icon="check"
            tone="success"
        />

        <x-stockpilot.kpi-card
            :label="__('Cancelled')"
            :value="number_format($cancelledPurchases)"
            :hint="__('Stock reversed')"
            icon="x-circle"
            tone="danger"
        />

        <x-stockpilot.kpi-card
            :label="__('Completed value')"
            :value="'LKR ' . number_format((float) $completedPurchaseValue, 2)"
            :hint="__('Total received value')"
            icon="file-text"
            tone="info"
        />

    </div>
</section>