<div class="space-y-3 md:hidden">

    @forelse ($categories as $category)

        <div class="rounded-2xl border border-[#E2E2E2] bg-[#FBFCF7] p-4 shadow-sm dark:border-[#3F3F3F] dark:bg-[#2E2E2E]">

            <div class="flex items-start justify-between gap-3">

                <div class="flex min-w-0 items-center gap-3">

                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#DCEAE9] text-sm font-bold text-[#015B63] dark:bg-[#1F3D3F] dark:text-[#4FC3C7]">
                        {{ strtoupper(substr($category->name, 0, 1)) }}
                    </div>

                    <div class="min-w-0">
                        <h3 class="truncate text-sm font-bold text-[#143732] dark:text-[#EAEAEA]">
                            {{ $category->name }}
                        </h3>

                        <p class="mt-0.5 text-xs text-[#7E8F8A] dark:text-[#8A8A8A]">
                            Category #{{ $category->id }}
                        </p>
                    </div>

                </div>

                @if ($category->status === 'active')
                    <span class="inline-flex shrink-0 items-center gap-1 rounded-full bg-[#DBEBE0] px-2.5 py-1 text-[11px] font-bold text-[#2F7D5B] dark:bg-[#1A3A2E] dark:text-[#6FC49A]">
                        <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                        Active
                    </span>
                @else
                    <span class="inline-flex shrink-0 items-center gap-1 rounded-full bg-[#EFE0EE] px-2.5 py-1 text-[11px] font-bold text-[#7A4E86] dark:bg-[#33224A] dark:text-[#D9A9D6]">
                        <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                        Inactive
                    </span>
                @endif

            </div>

            <div class="mt-4 rounded-xl bg-[#F4F5EC] p-3 dark:bg-[#3A3A3A]">
                <p class="text-xs font-semibold uppercase tracking-wide text-[#7E8F8A] dark:text-[#8A8A8A]">
                    Description
                </p>

                <p class="mt-1 text-sm leading-6 text-[#4E635E] dark:text-[#B8B8B8]">
                    {{ $category->description ?: 'No description provided.' }}
                </p>
            </div>

            <div class="mt-4 flex items-center justify-between border-t border-[#E2E2E2] pt-3 dark:border-[#3F3F3F]">

                <div>
                    <p class="text-xs font-medium text-[#7E8F8A] dark:text-[#8A8A8A]">
                        Products
                    </p>

                    <p class="mt-0.5 text-lg font-bold text-[#143732] dark:text-[#EAEAEA]">
                        {{ $category->products_count }}
                    </p>
                </div>

                <div class="flex items-center gap-2">

                    <button
                        type="button"
                        @click='openEdit(@js([
                            "id" => $category->id,
                            "name" => $category->name,
                            "description" => $category->description,
                            "status" => $category->status,
                        ]))'
                        class="inline-flex items-center gap-1.5 rounded-lg border border-[#E2E2E2] bg-white px-3 py-2 text-xs font-bold text-[#143732] dark:border-[#3F3F3F] dark:bg-[#2E2E2E] dark:text-[#EAEAEA]"
                    >
                        <svg
                            class="h-3.5 w-3.5"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="m16.5 4.5 3 3M5 19l1-4 10.5-10.5a2.121 2.121 0 0 1 3 3L9 18 5 19Z"/>
                        </svg>

                        Edit
                    </button>

                    @if ($category->status === 'active')
                        <button
                            type="button"
                            @click='requestDeactivate(@js([
                                "id" => $category->id,
                                "name" => $category->name,
                                "description" => $category->description,
                                "status" => $category->status,
                            ]))'
                            class="inline-flex items-center rounded-lg bg-[#FADFDD] px-3 py-2 text-xs font-bold text-[#C93F39] dark:bg-[#3E1F1D] dark:text-[#F2706A]"
                        >
                            Deactivate
                        </button>
                    @endif

                </div>

            </div>

        </div>

    @empty

        <div class="rounded-2xl border border-[#E2E2E2] bg-[#FBFCF7] px-6 py-14 text-center shadow-sm dark:border-[#3F3F3F] dark:bg-[#2E2E2E]">

            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-[#F4F5EC] text-[#7E8F8A] dark:bg-[#454545] dark:text-[#8A8A8A]">
                <svg
                    class="h-7 w-7"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.6"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 7.5A2.5 2.5 0 0 1 6.5 5h11A2.5 2.5 0 0 1 20 7.5v9a2.5 2.5 0 0 1-2.5 2.5h-11A2.5 2.5 0 0 1 4 16.5v-9Z"/>
                    <path stroke-linecap="round" d="M8 9h8M8 13h5"/>
                </svg>
            </div>

            <h3 class="mt-4 text-base font-bold text-[#143732] dark:text-[#EAEAEA]">
                No categories found
            </h3>

            <p class="mt-1 text-sm text-[#7E8F8A] dark:text-[#8A8A8A]">
                Try changing your search or filters.
            </p>

            @if ($this->hasActiveFilters())
                <button
                    type="button"
                    wire:click="clearFilters"
                    class="mt-4 text-sm font-bold text-[#015B63] hover:underline dark:text-[#4FC3C7]"
                >
                    Clear filters
                </button>
            @endif

        </div>

    @endforelse

</div>
