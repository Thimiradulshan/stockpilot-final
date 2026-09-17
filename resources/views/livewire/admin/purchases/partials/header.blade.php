<x-stockpilot.page-header
    :section-label="__('Purchasing')"
    :section-current="__('Purchases')"
    :title="__('Purchases')"
    :subtitle="__('Purchasing')"
    :description="__('Record stock purchases and manage incoming inventory.')"
    :tags="[
        ['label' => __('Purchasing'), 'tone' => 'primary'],
        ['label' => __('Incoming stock'), 'tone' => 'success'],
    ]"
>
    <x-slot:actions>
        @can('create', \App\Models\Purchase::class)
            <x-stockpilot.primary-action
                :label="__('New purchase')"
                icon="shopping-cart"
                click="openCreate()"
            />
        @endcan
    </x-slot:actions>
</x-stockpilot.page-header>