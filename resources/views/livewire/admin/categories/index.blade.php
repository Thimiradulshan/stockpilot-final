<div class="relative min-h-[calc(100vh-4.75rem)] overflow-hidden bg-sp-background">

    
    @include('livewire.admin.categories.partials.background')


    
    <div
        x-data="categoryManager()"
        @keydown.escape.window="closeOnEscape()"
        class="relative z-10 mx-auto w-full max-w-[1500px] space-y-5 px-4 py-6 sm:px-6 lg:px-8"
    >

        @include('livewire.admin.categories.partials.header')

        <x-stockpilot.master-data-tabs />

        @include('livewire.admin.categories.partials.alerts')

        @include('livewire.admin.categories.partials.kpis')

        @include('livewire.admin.categories.partials.filters')

        @include('livewire.admin.categories.partials.desktop-table')

        @include('livewire.admin.categories.partials.mobile-cards')

        @include('livewire.admin.categories.partials.pagination')

        @include('livewire.admin.categories.partials.create-modal')

        @include('livewire.admin.categories.partials.edit-modal')

        @include('livewire.admin.categories.partials.category-manager-script')

    </div>

</div>

