<section aria-label="{{ __('Category summary') }}">

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">

        <x-stockpilot.kpi-card
            :label="__('Total Categories')"
            :value="number_format($totalCategories)"
            :hint="__('Entire category catalog')"
            icon="categories"
            tone="primary"
            action="$set('status', '')"
            :active="$status === ''"
        />

        <x-stockpilot.kpi-card
            :label="__('Active Categories')"
            :value="number_format($activeCategories)"
            :hint="__('Available for products')"
            icon="check"
            tone="success"
            action="$set('status', 'active')"
            :active="$status === 'active'"
        />

        <x-stockpilot.kpi-card
            :label="__('Inactive Categories')"
            :value="number_format($inactiveCategories)"
            :hint="__('Retained for history')"
            icon="info"
            tone="info"
            action="$set('status', 'inactive')"
            :active="$status === 'inactive'"
        />

    </div>

</section>