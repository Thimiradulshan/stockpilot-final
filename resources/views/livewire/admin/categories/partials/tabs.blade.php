<section
    class="relative overflow-hidden rounded-2xl border border-[#015B63]/20 bg-gradient-to-r from-[#DCEAE9] via-[#F7F5EE] to-[#EFE0EE] p-1.5 shadow-md dark:border-[#4FC3C7]/20 dark:from-[#1F3D3F] dark:via-[#2E2E2E] dark:to-[#33224A]"
    aria-label="{{ __('Category status') }}"
>

    <div class="grid grid-cols-3 gap-1.5">

        
        <button
            type="button"
            wire:click="$set('status', '')"
            @class([
                'relative overflow-hidden rounded-xl px-4 py-3.5 text-sm font-extrabold transition-all duration-200',
                'bg-gradient-to-r from-[#015B63] to-[#19757C] text-white shadow-lg shadow-[#015B63]/25' => $status === '',
                'bg-white/60 text-[#4E635E] hover:bg-white hover:text-[#015B63] dark:bg-white/[0.04] dark:text-[#B8B8B8] dark:hover:bg-white/[0.08] dark:hover:text-[#EAEAEA]' => $status !== '',
            ])
        >

            @if ($status === '')
                <span class="absolute inset-x-0 top-0 h-1 bg-[#F5784E]"></span>
            @endif

            <span class="relative inline-flex items-center gap-2">

                <span
                    @class([
                        'h-2 w-2 rounded-full',
                        'bg-white' => $status === '',
                        'bg-[#015B63] dark:bg-[#4FC3C7]' => $status !== '',
                    ])
                ></span>

                {{ __('All Categories') }}

                <span
                    class="rounded-full px-2 py-0.5 text-[11px]"
                    @class([
                        'bg-white/15 text-white' => $status === '',
                        'bg-[#015B63]/10 text-[#015B63] dark:bg-[#4FC3C7]/10 dark:text-[#4FC3C7]' => $status !== '',
                    ])
                >
                    {{ $totalCategories }}
                </span>

            </span>

        </button>


        
        <button
            type="button"
            wire:click="$set('status', 'active')"
            @class([
                'relative overflow-hidden rounded-xl px-4 py-3.5 text-sm font-extrabold transition-all duration-200',
                'bg-gradient-to-r from-[#2F7D5B] to-[#3C966D] text-white shadow-lg shadow-[#2F7D5B]/25' => $status === 'active',
                'bg-white/60 text-[#4E635E] hover:bg-[#DBEBE0] hover:text-[#1D5B40] dark:bg-white/[0.04] dark:text-[#B8B8B8] dark:hover:bg-[#1A3A2E] dark:hover:text-[#6FC49A]' => $status !== 'active',
            ])
        >

            @if ($status === 'active')
                <span class="absolute inset-x-0 top-0 h-1 bg-[#6FC49A]"></span>
            @endif

            <span class="relative inline-flex items-center gap-2">

                <span
                    @class([
                        'h-2 w-2 rounded-full',
                        'bg-white' => $status === 'active',
                        'bg-[#2F7D5B] dark:bg-[#6FC49A]' => $status !== 'active',
                    ])
                ></span>

                {{ __('Active') }}

                <span
                    class="rounded-full px-2 py-0.5 text-[11px]"
                    @class([
                        'bg-white/15 text-white' => $status === 'active',
                        'bg-[#2F7D5B]/10 text-[#1D5B40] dark:bg-[#6FC49A]/10 dark:text-[#6FC49A]' => $status !== 'active',
                    ])
                >
                    {{ $activeCategories }}
                </span>

            </span>

        </button>


        
        <button
            type="button"
            wire:click="$set('status', 'inactive')"
            @class([
                'relative overflow-hidden rounded-xl px-4 py-3.5 text-sm font-extrabold transition-all duration-200',
                'bg-gradient-to-r from-[#7A4E86] to-[#9864A5] text-white shadow-lg shadow-[#7A4E86]/25' => $status === 'inactive',
                'bg-white/60 text-[#4E635E] hover:bg-[#EFE0EE] hover:text-[#633A6F] dark:bg-white/[0.04] dark:text-[#B8B8B8] dark:hover:bg-[#33224A] dark:hover:text-[#D9A9D6]' => $status !== 'inactive',
            ])
        >

            @if ($status === 'inactive')
                <span class="absolute inset-x-0 top-0 h-1 bg-[#F5784E]"></span>
            @endif

            <span class="relative inline-flex items-center gap-2">

                <span
                    @class([
                        'h-2 w-2 rounded-full',
                        'bg-white' => $status === 'inactive',
                        'bg-[#7A4E86] dark:bg-[#D9A9D6]' => $status !== 'inactive',
                    ])
                ></span>

                {{ __('Inactive') }}

                <span
                    class="rounded-full px-2 py-0.5 text-[11px]"
                    @class([
                        'bg-white/15 text-white' => $status === 'inactive',
                        'bg-[#7A4E86]/10 text-[#633A6F] dark:bg-[#D9A9D6]/10 dark:text-[#D9A9D6]' => $status !== 'inactive',
                    ])
                >
                    {{ $inactiveCategories }}
                </span>

            </span>

        </button>

    </div>

</section>

