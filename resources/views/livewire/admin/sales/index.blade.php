<x-stockpilot.admin-page
    x-data="salesManager(window.__stockPilotSaleProducts)"
    escapeAction="closeModals()"
>

    @include('livewire.admin.sales.partials.header')

    <x-stockpilot.toast-alerts />

    @include('livewire.admin.sales.partials.kpis')

    @include('livewire.admin.sales.partials.filters')

    @include('livewire.admin.sales.partials.desktop-table')

    @include('livewire.admin.sales.partials.mobile-cards')

    @can('create', \App\Models\Invoice::class)
        @include('livewire.admin.sales.partials.create-modal')
    @endcan

    @include('livewire.admin.sales.partials.details-modal')

    @include('livewire.admin.sales.partials.void-modal')

    @include('livewire.admin.sales.partials.payment-modal')

    @include('livewire.admin.sales.partials.sales-manager-script')

</x-stockpilot.admin-page>