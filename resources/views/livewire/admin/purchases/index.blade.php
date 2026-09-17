<x-stockpilot.admin-page
    x-data="purchaseManager(window.__stockPilotPurchaseProducts)"
    escapeAction="closeModals()"
>

    @include('livewire.admin.purchases.partials.header')

    <x-stockpilot.toast-alerts />

    @include('livewire.admin.purchases.partials.kpis')

    @include('livewire.admin.purchases.partials.filters')

    @include('livewire.admin.purchases.partials.desktop-table')

    @include('livewire.admin.purchases.partials.mobile-cards')

    @can('create', \App\Models\Purchase::class)
        @include('livewire.admin.purchases.partials.create-modal')
    @endcan

    @include('livewire.admin.purchases.partials.details-modal')

    @include('livewire.admin.purchases.partials.cancel-modal')

    @include('livewire.admin.purchases.partials.purchase-manager-script')

</x-stockpilot.admin-page>