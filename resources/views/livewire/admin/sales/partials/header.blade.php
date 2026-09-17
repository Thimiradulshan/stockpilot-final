<x-stockpilot.page-header
    :section-label="__('Sales')"
    :section-current="__('Invoices')"
    :title="__('Sales')"
    :subtitle="__('Invoicing')"
    :description="__('Record customer sales, track invoices and manage outgoing stock.')"
    :tags="[
        ['label' => __('Invoicing'), 'tone' => 'primary'],
        ['label' => __('Outgoing stock'), 'tone' => 'success'],
    ]"
>
    <x-slot:actions>
        @can('create', \App\Models\Invoice::class)
            <x-stockpilot.primary-action
                :label="__('New sale')"
                icon="receipt"
                click="openCreate()"
            />
        @endcan
    </x-slot:actions>
</x-stockpilot.page-header>