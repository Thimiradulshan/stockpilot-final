@if ($categories->hasPages())
    <div class="rounded-2xl border border-[#E2E2E2] bg-[#FBFCF7] px-4 py-3 shadow-sm dark:border-[#3F3F3F] dark:bg-[#2E2E2E] sm:px-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">

            <p class="text-xs text-[#7E8F8A] dark:text-[#8A8A8A]">
                Showing
                <span class="font-bold text-[#143732] dark:text-[#EAEAEA]">
                    {{ $categories->firstItem() }}
                </span>
                to
                <span class="font-bold text-[#143732] dark:text-[#EAEAEA]">
                    {{ $categories->lastItem() }}
                </span>
                of
                <span class="font-bold text-[#143732] dark:text-[#EAEAEA]">
                    {{ $categories->total() }}
                </span>
                categories
            </p>

            <div>
                {{ $categories->links() }}
            </div>

        </div>
    </div>
@endif
