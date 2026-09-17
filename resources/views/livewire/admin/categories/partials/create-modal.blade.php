<div
    x-show="createOpen"
    x-cloak
    x-transition.opacity
    class="fixed inset-0 z-50 overflow-y-auto"
    aria-modal="true"
    role="dialog"
>
    <div class="flex min-h-full items-center justify-center bg-[#143732]/50 p-4 backdrop-blur-sm dark:bg-black/70">

        <div
            x-show="createOpen"
            x-transition:enter="transition duration-200 ease-out"
            x-transition:enter-start="scale-95 opacity-0"
            x-transition:enter-end="scale-100 opacity-100"
            class="w-full max-w-lg overflow-hidden rounded-2xl border border-[#E2E2E2] bg-[#FBFCF7] shadow-2xl dark:border-[#3F3F3F] dark:bg-[#2E2E2E]"
            @click.outside="closeCreate()"
        >

            <div class="border-b border-[#E2E2E2] px-6 py-5 dark:border-[#3F3F3F]">

                <div class="flex items-start justify-between gap-4">

                    <div>
                        <h2 class="text-lg font-bold text-[#143732] dark:text-[#EAEAEA]">
                            Create Category
                        </h2>

                        <p class="mt-1 text-sm text-[#7E8F8A] dark:text-[#8A8A8A]">
                            Add a new product category to StockPilot.
                        </p>
                    </div>

                    <button
                        type="button"
                        @click="closeCreate()"
                        class="rounded-lg p-2 text-[#7E8F8A] transition hover:bg-[#F4F5EC] hover:text-[#143732] dark:text-[#8A8A8A] dark:hover:bg-[#3A3A3A] dark:hover:text-[#EAEAEA]"
                    >
                        <svg
                            class="h-5 w-5"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                        >
                            <path stroke-linecap="round" d="M6 6l12 12M18 6 6 18"/>
                        </svg>
                    </button>

                </div>

            </div>

            <form
                method="POST"
                action="{{ route('admin.categories.store') }}"
            >
                @csrf

                <div class="space-y-5 px-6 py-6">

                    <div>
                        <label
                            for="create_category_name"
                            class="mb-2 block text-sm font-semibold text-[#143732] dark:text-[#EAEAEA]"
                        >
                            Category Name
                        </label>

                        <input
                            id="create_category_name"
                            name="name"
                            type="text"
                            value="{{ old('name') }}"
                            required
                            maxlength="255"
                            placeholder="e.g. Beverages"
                            class="w-full rounded-xl border border-[#E2E2E2] bg-white px-4 py-3 text-sm text-[#143732] placeholder:text-[#7E8F8A] focus:border-[#015B63] focus:outline-none focus:ring-4 focus:ring-[#015B63]/10 dark:border-[#3F3F3F] dark:bg-[#3A3A3A] dark:text-[#EAEAEA] dark:placeholder:text-[#8A8A8A] dark:focus:border-[#4FC3C7]"
                        />
                    </div>

                    <div>
                        <label
                            for="create_category_description"
                            class="mb-2 block text-sm font-semibold text-[#143732] dark:text-[#EAEAEA]"
                        >
                            Description
                        </label>

                        <textarea
                            id="create_category_description"
                            name="description"
                            rows="4"
                            placeholder="Describe this category..."
                            class="w-full resize-none rounded-xl border border-[#E2E2E2] bg-white px-4 py-3 text-sm text-[#143732] placeholder:text-[#7E8F8A] focus:border-[#015B63] focus:outline-none focus:ring-4 focus:ring-[#015B63]/10 dark:border-[#3F3F3F] dark:bg-[#3A3A3A] dark:text-[#EAEAEA] dark:placeholder:text-[#8A8A8A] dark:focus:border-[#4FC3C7]"
                        >{{ old('description') }}</textarea>
                    </div>

                    <div>
                        <label
                            for="create_category_status"
                            class="mb-2 block text-sm font-semibold text-[#143732] dark:text-[#EAEAEA]"
                        >
                            Status
                        </label>

                        <select
                            id="create_category_status"
                            name="status"
                            required
                            class="w-full rounded-xl border border-[#E2E2E2] bg-white px-4 py-3 text-sm text-[#143732] focus:border-[#015B63] focus:outline-none focus:ring-4 focus:ring-[#015B63]/10 dark:border-[#3F3F3F] dark:bg-[#3A3A3A] dark:text-[#EAEAEA] dark:focus:border-[#4FC3C7]"
                        >
                            <option value="active" @selected(old('status', 'active') === 'active')>
                                Active
                            </option>

                            <option value="inactive" @selected(old('status') === 'inactive')>
                                Inactive
                            </option>
                        </select>
                    </div>

                </div>

                <div class="flex flex-col-reverse gap-3 border-t border-[#E2E2E2] bg-[#F4F5EC] px-6 py-4 sm:flex-row sm:justify-end dark:border-[#3F3F3F] dark:bg-[#3A3A3A]">

                    <button
                        type="button"
                        @click="closeCreate()"
                        class="rounded-xl border border-[#E2E2E2] bg-white px-4 py-2.5 text-sm font-bold text-[#4E635E] transition hover:bg-[#F4F5EC] dark:border-[#3F3F3F] dark:bg-[#2E2E2E] dark:text-[#B8B8B8]"
                    >
                        Cancel
                    </button>

                    <button
                        type="submit"
                        class="rounded-xl bg-[#015B63] px-4 py-2.5 text-sm font-bold text-white transition hover:bg-[#01474E] focus:outline-none focus:ring-4 focus:ring-[#015B63]/15 dark:bg-[#4FC3C7] dark:text-[#0A0A0A] dark:hover:bg-[#6FD3D6]"
                    >
                        Create Category
                    </button>

                </div>

            </form>

        </div>

    </div>
</div>
