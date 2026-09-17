<x-stockpilot.filter-bar aria-label="{{ __('Purchase filters') }}">

    <div class="w-full lg:max-w-2xl">
        <x-stockpilot.search-input
            id="purchase-search"
            wire:model.live.debounce.300ms="search"
            :placeholder="__('Search by purchase number or supplier name...')"
        />
    </div>

    <div class="flex flex-wrap items-center gap-2">

        <select
            wire:model.live="status"
            class="rounded-xl border border-sp-border-strong bg-sp-surface px-4 py-3.5 text-sm font-bold text-sp-text shadow-sm outline-none transition focus:border-sp-primary focus:ring-4 focus:ring-sp-primary/10 dark:border-sp-border-strong dark:bg-sp-surface-soft dark:text-sp-text dark:focus:border-sp-success"
        >
            <option value="">{{ __('All statuses') }}</option>
            <option value="completed">{{ __('Completed') }}</option>
            <option value="cancelled">{{ __('Cancelled') }}</option>
        </select>

        @if ($this->hasActiveFilters())
            <button
                type="button"
                wire:click="clearFilters"
                class="rounded-xl border border-sp-info/30 bg-sp-info-soft px-4 py-3.5 text-sm font-extrabold text-sp-info-foreground shadow-sm transition hover:bg-sp-info-soft dark:bg-sp-info-soft dark:text-sp-info"
            >
                {{ __('Clear filters') }}
            </button>
        @endif

    </div>

</x-stockpilot.filter-bar>