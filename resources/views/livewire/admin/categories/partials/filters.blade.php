<section
    class="relative overflow-hidden rounded-2xl border border-[#015B63]/25 bg-gradient-to-r from-[#DDEBE9] via-[#F5F5ED] to-[#F0E4F0] p-4 shadow-md dark:border-[#4FC3C7]/20 dark:from-[#1F3D3F] dark:via-[#3A3A3A] dark:to-[#33224A]"
>

    <div class="absolute inset-x-0 top-0 h-1.5 bg-gradient-to-r from-[#015B63] via-[#B98FC0] to-[#F5784E]"></div>

    <div class="relative flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">

        <div class="w-full lg:max-w-2xl">

            <label
                for="category-search"
                class="sr-only"
            >
                {{ __('Search categories') }}
            </label>


            <div class="relative">

                <div class="pointer-events-none absolute start-3.5 top-1/2 flex h-8 w-8 -translate-y-1/2 items-center justify-center rounded-lg bg-[#015B63]/10 text-[#015B63] dark:bg-[#4FC3C7]/10 dark:text-[#4FC3C7]">

                    <svg
                        class="h-4 w-4"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                    >
                        <circle cx="11" cy="11" r="7"/>
                        <path
                            stroke-linecap="round"
                            d="m20 20-4-4"
                        />
                    </svg>

                </div>


                <input
                    id="category-search"
                    type="search"
                    wire:model.live.debounce.400ms="search"
                    placeholder="{{ __('Search categories by name or description...') }}"
                    class="w-full rounded-xl border border-[#C7D6D2] bg-[#FBFCF7] py-3.5 pe-4 ps-14 text-sm font-semibold text-[#143732] shadow-sm outline-none transition focus:border-[#015B63] focus:ring-4 focus:ring-[#015B63]/10 dark:border-[#5A5A5A] dark:bg-[#454545] dark:text-[#EAEAEA] dark:placeholder:text-[#8A8A8A] dark:focus:border-[#4FC3C7]"
                />

            </div>

        </div>


        <div class="flex flex-wrap items-center gap-2">

            @if ($this->hasActiveFilters())

                <button
                    type="button"
                    wire:click="clearFilters"
                    class="rounded-xl border border-[#B98FC0]/30 bg-[#EFE0EE] px-4 py-3.5 text-sm font-extrabold text-[#633A6F] shadow-sm transition hover:bg-[#E7D6E7] dark:bg-[#33224A] dark:text-[#D9A9D6]"
                >
                    {{ __('Clear filters') }}
                </button>

            @endif

        </div>

    </div>

</section>
