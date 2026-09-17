<div class="hidden overflow-hidden rounded-2xl border border-[#015B63]/20 bg-[#FBFCF7] shadow-lg dark:border-[#4FC3C7]/15 dark:bg-[#2E2E2E] md:block">

    
    <div class="h-1.5 bg-gradient-to-r from-[#015B63] via-[#4FC3C7] via-[#B98FC0] to-[#F5784E]"></div>


    <div class="overflow-x-auto">

        <table class="min-w-full border-collapse">


            
            <thead class="bg-gradient-to-r from-[#CFE5E5] via-[#F4F5EC] to-[#E8D4E8] dark:from-[#1F3D3F] dark:via-[#3A3A3A] dark:to-[#33224A]">

                <tr class="border-b border-[#C7D6D2] dark:border-[#5A5A5A]">

                    <th
                        scope="col"
                        class="px-6 py-4 text-left text-xs font-extrabold uppercase tracking-[0.10em] text-[#143732] dark:text-[#EAEAEA]"
                    >
                        {{ __('Category') }}
                    </th>

                    <th
                        scope="col"
                        class="px-6 py-4 text-left text-xs font-extrabold uppercase tracking-[0.10em] text-[#143732] dark:text-[#EAEAEA]"
                    >
                        {{ __('Description') }}
                    </th>

                    <th
                        scope="col"
                        class="px-6 py-4 text-center text-xs font-extrabold uppercase tracking-[0.10em] text-[#143732] dark:text-[#EAEAEA]"
                    >
                        {{ __('Products') }}
                    </th>

                    <th
                        scope="col"
                        class="px-6 py-4 text-center text-xs font-extrabold uppercase tracking-[0.10em] text-[#143732] dark:text-[#EAEAEA]"
                    >
                        {{ __('Status') }}
                    </th>

                    <th
                        scope="col"
                        class="w-[320px] px-6 py-4 text-left text-xs font-extrabold uppercase tracking-[0.10em] text-[#143732] dark:text-[#EAEAEA]"
                    >
                        {{ __('Actions') }}
                    </th>

                </tr>

            </thead>


            
            <tbody class="divide-y divide-[#E2E2E2] dark:divide-[#3F3F3F]">

                @forelse ($categories as $category)

                    <tr
                        wire:key="category-row-{{ $category->id }}"
                        class="group transition duration-150 odd:bg-[#FBFCF7] even:bg-[#F7F8F2] hover:bg-[#E4F0EF] dark:odd:bg-[#2E2E2E] dark:even:bg-[#333333] dark:hover:bg-[#1F3D3F]"
                    >


                        
                        <td class="px-6 py-5">

                            <div class="flex min-w-0 items-center gap-3">

                                <div class="relative flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-gradient-to-br from-[#CFE5E5] to-[#E8D4E8] text-[#015B63] shadow-sm dark:from-[#1F3D3F] dark:to-[#33224A] dark:text-[#4FC3C7]">

                                    <span class="absolute -right-2 -top-2 h-7 w-7 rounded-full bg-[#F5784E]/25"></span>

                                    <span class="relative text-sm font-extrabold">
                                        {{ strtoupper(mb_substr($category->name, 0, 1)) }}
                                    </span>

                                </div>


                                <div class="min-w-0">

                                    <p class="truncate text-[15px] font-extrabold text-[#143732] dark:text-[#EAEAEA]">
                                        {{ $category->name }}
                                    </p>

                                    <p class="mt-1 text-xs font-semibold text-[#7E8F8A] dark:text-[#8A8A8A]">
                                        {{ __('Category') }} #{{ $category->id }}
                                    </p>

                                </div>

                            </div>

                        </td>


                        
                        <td class="max-w-[480px] px-6 py-5">

                            <p class="line-clamp-2 text-sm leading-6 text-[#4E635E] dark:text-[#B8B8B8]">
                                {{ $category->description ?: __('No description provided.') }}
                            </p>

                        </td>


                        
                        <td class="px-6 py-5 text-center">

                            <div class="inline-flex flex-col items-center">

                                <span class="inline-flex min-w-12 items-center justify-center rounded-xl bg-[#DCEAE9] px-3 py-2 text-sm font-extrabold text-[#015B63] shadow-sm dark:bg-[#1F3D3F] dark:text-[#4FC3C7]">
                                    {{ number_format($category->products_count) }}
                                </span>

                                <span class="mt-1 text-[11px] font-semibold text-[#7E8F8A] dark:text-[#8A8A8A]">
                                    {{ __('assigned') }}
                                </span>

                            </div>

                        </td>


                        
                        <td class="px-6 py-5 text-center">

                            @if ($category->status === 'active')

                                <span class="inline-flex items-center gap-2 rounded-full bg-[#DBEBE0] px-3.5 py-2 text-xs font-extrabold text-[#1D5B40] shadow-sm dark:bg-[#1A3A2E] dark:text-[#6FC49A]">

                                    <span class="h-2 w-2 rounded-full bg-[#2F7D5B] dark:bg-[#6FC49A]"></span>

                                    {{ __('Active') }}

                                </span>

                            @else

                                <span class="inline-flex items-center gap-2 rounded-full bg-[#EFE0EE] px-3.5 py-2 text-xs font-extrabold text-[#633A6F] shadow-sm dark:bg-[#33224A] dark:text-[#D9A9D6]">

                                    <span class="h-2 w-2 rounded-full bg-[#7A4E86] dark:bg-[#D9A9D6]"></span>

                                    {{ __('Inactive') }}

                                </span>

                            @endif

                        </td>


                        
                        <td class="px-6 py-5">

                            <div class="flex items-center justify-start gap-2">

                                @can('update', $category)

                                    
                                    <button
                                        type="button"
                                        @click="openEdit(@js([
                                            'id' => $category->id,
                                            'name' => $category->name,
                                            'description' => $category->description,
                                            'status' => $category->status,
                                        ]))"
                                        class="inline-flex items-center gap-2 rounded-xl border border-[#BFD0CB] bg-[#FBFCF7] px-3.5 py-2.5 text-sm font-extrabold text-[#143732] shadow-sm transition hover:border-[#015B63]/30 hover:bg-[#DCEAE9] hover:text-[#015B63] focus:outline-none focus:ring-2 focus:ring-[#015B63]/20 dark:border-[#5A5A5A] dark:bg-[#3A3A3A] dark:text-[#EAEAEA] dark:hover:bg-[#1F3D3F] dark:hover:text-[#4FC3C7]"
                                    >

                                        <svg
                                            class="h-4 w-4"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="1.8"
                                        >
                                            <path d="M12 20h9"/>
                                            <path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L8 18l-4 1 1-4Z"/>
                                        </svg>

                                        {{ __('Edit') }}

                                    </button>


                                    
                                    @if ($category->status === 'active')

                                        <button
                                            type="button"
                                            @click="requestDeactivate(@js([
                                                'id' => $category->id,
                                                'name' => $category->name,
                                                'description' => $category->description,
                                                'status' => $category->status,
                                            ]))"
                                            class="inline-flex items-center gap-2 rounded-xl border border-[#F25C56]/25 bg-[#FADFDD] px-3.5 py-2.5 text-sm font-extrabold text-[#C93F39] transition hover:bg-[#F8D0CD] focus:outline-none focus:ring-2 focus:ring-[#C93F39]/20 dark:bg-[#3E1F1D] dark:text-[#F2706A]"
                                        >

                                            <svg
                                                class="h-4 w-4"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="1.8"
                                            >
                                                <path d="M6 7h12"/>
                                                <path d="M9 7V5h6v2"/>
                                                <path d="M8 7l1 12h6l1-12"/>
                                                <path d="M10 11v5"/>
                                                <path d="M14 11v5"/>
                                            </svg>

                                            {{ __('Deactivate') }}

                                        </button>

                                    @endif


                                    
                                    @if ($category->status === 'inactive')

                                        <button
                                            type="button"
                                            @click="requestActivate(@js([
                                                'id' => $category->id,
                                                'name' => $category->name,
                                                'description' => $category->description,
                                                'status' => $category->status,
                                            ]))"
                                            class="inline-flex items-center gap-2 rounded-xl border border-[#2F7D5B]/25 bg-[#DBEBE0] px-3.5 py-2.5 text-sm font-extrabold text-[#1D5B40] transition hover:bg-[#CDE6D7] focus:outline-none focus:ring-2 focus:ring-[#2F7D5B]/20 dark:bg-[#1A3A2E] dark:text-[#6FC49A]"
                                        >

                                            <svg
                                                class="h-4 w-4"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="1.9"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    d="m5 12.5 4.5 4.5L19 7.5"
                                                />
                                            </svg>

                                            {{ __('Activate') }}

                                        </button>

                                    @endif

                                @endcan

                            </div>

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td
                            colspan="5"
                            class="px-6 py-20 text-center"
                        >

                            <div class="mx-auto flex max-w-md flex-col items-center">

                                <div class="relative flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-[#CFE5E5] to-[#E8D4E8] text-[#015B63]">

                                    <span class="absolute -right-1 -top-1 h-5 w-5 rounded-full bg-[#F5784E]/30"></span>

                                    <svg
                                        class="h-7 w-7"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.7"
                                    >
                                        <rect x="4" y="4" width="16" height="16" rx="2"/>
                                        <path d="M8 8h8"/>
                                        <path d="M8 12h8"/>
                                        <path d="M8 16h5"/>
                                    </svg>

                                </div>


                                <h3 class="mt-5 text-lg font-extrabold text-sp-text">

                                    @if ($this->hasActiveFilters())
                                        {{ __('No matching categories') }}
                                    @else
                                        {{ __('No categories yet') }}
                                    @endif

                                </h3>


                                <p class="mt-2 text-sm leading-6 text-sp-text-muted">

                                    @if ($this->hasActiveFilters())
                                        {{ __('Try changing your search or status filter.') }}
                                    @else
                                        {{ __('Create your first category to organize the product catalog.') }}
                                    @endif

                                </p>


                                <div class="mt-5 flex flex-wrap justify-center gap-2">

                                    @if ($this->hasActiveFilters())

                                        <button
                                            type="button"
                                            wire:click="clearFilters"
                                            class="rounded-xl border border-[#BFD0CB] bg-[#FBFCF7] px-4 py-2.5 text-sm font-bold text-[#143732] transition hover:bg-[#F4F5EC]"
                                        >
                                            {{ __('Clear filters') }}
                                        </button>

                                    @endif


                                    @can('create', \App\Models\Category::class)

                                        <button
                                            type="button"
                                            @click="openCreate()"
                                            class="rounded-xl bg-[#015B63] px-4 py-2.5 text-sm font-bold text-white shadow-md transition hover:bg-[#01474E]"
                                        >
                                            {{ __('Add category') }}
                                        </button>

                                    @endcan

                                </div>

                            </div>

                        </td>

                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

</div>

