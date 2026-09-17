<section class="relative overflow-hidden rounded-2xl border border-[#015B63]/20 bg-gradient-to-r from-[#DCEAE9] via-[#FBFCF7] to-[#EFE0EE] shadow-lg dark:border-[#4FC3C7]/20 dark:from-[#1F3D3F] dark:via-[#2E2E2E] dark:to-[#33224A]">

    
    <div class="pointer-events-none absolute -right-16 -top-16 h-48 w-48 rounded-full bg-[#015B63]/15 blur-3xl"></div>

    <div class="pointer-events-none absolute right-[18%] bottom-[-70px] h-40 w-40 rounded-full bg-[#F5784E]/15 blur-3xl"></div>

    <div class="pointer-events-none absolute -left-10 bottom-[-80px] h-48 w-48 rounded-full bg-[#B98FC0]/15 blur-3xl"></div>


    
    <div class="absolute inset-y-0 start-0 w-1.5 bg-gradient-to-b from-[#015B63] via-[#4FC3C7] to-[#F5784E]"></div>


    <div class="relative px-6 py-7 sm:px-8 lg:px-9 lg:py-8">

        <div class="flex flex-col gap-7 lg:flex-row lg:items-center lg:justify-between">

            <div class="min-w-0">

                <div class="mb-3 flex flex-wrap items-center gap-2 text-[11px] font-bold uppercase tracking-[0.16em] text-[#4E635E] dark:text-[#B8B8B8]">

                    <span class="h-2.5 w-2.5 rounded-full bg-[#015B63] dark:bg-[#4FC3C7]"></span>

                    <span>
                        {{ __('Inventory Management') }}
                    </span>

                    <span class="text-[#7E8F8A] dark:text-[#8A8A8A]">
                        /
                    </span>

                    <span class="text-[#015B63] dark:text-[#4FC3C7]">
                        {{ __('Categories') }}
                    </span>

                </div>


                <h1 class="text-[34px] font-extrabold tracking-tight text-[#143732] dark:text-[#EAEAEA]">
                    {{ __('Categories') }}
                </h1>


                <p class="mt-2 text-[15px] font-bold text-[#4E635E] dark:text-[#B8B8B8]">
                    {{ __('Category catalog') }}
                </p>


                <p class="mt-1.5 max-w-2xl text-sm leading-6 text-[#4E635E] dark:text-[#B8B8B8]">
                    {{ __('Organize your products with clear category structure, status control, and easy catalog management.') }}
                </p>


                <div class="mt-5 flex flex-wrap gap-2">

                    <span class="inline-flex items-center gap-2 rounded-full bg-[#015B63] px-3.5 py-1.5 text-xs font-bold text-white shadow-sm">
                        <span class="h-1.5 w-1.5 rounded-full bg-white"></span>
                        {{ __('Catalog') }}
                    </span>

                    <span class="inline-flex items-center gap-2 rounded-full bg-[#DBEBE0] px-3.5 py-1.5 text-xs font-bold text-[#1D5B40] dark:bg-[#1A3A2E] dark:text-[#6FC49A]">
                        <span class="h-1.5 w-1.5 rounded-full bg-[#2F7D5B]"></span>
                        {{ __('Active') }}
                    </span>

                </div>

            </div>


            <div class="shrink-0">

                @can('create', \App\Models\Category::class)

                    <button
                        type="button"
                        @click="openCreate()"
                        class="group inline-flex items-center gap-2.5 rounded-xl bg-[#015B63] px-5 py-3.5 text-sm font-bold text-white shadow-lg shadow-[#015B63]/25 transition duration-200 hover:-translate-y-0.5 hover:bg-[#01474E] hover:shadow-xl hover:shadow-[#015B63]/30 focus:outline-none focus:ring-4 focus:ring-[#015B63]/20 dark:bg-[#4FC3C7] dark:text-[#0A0A0A] dark:hover:bg-[#6FD3D6]"
                    >

                        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-white/15">
                            <svg
                                class="h-4 w-4 transition-transform duration-200 group-hover:rotate-90"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path
                                    stroke-linecap="round"
                                    d="M12 5v14M5 12h14"
                                />
                            </svg>
                        </span>

                        {{ __('New Category') }}

                    </button>

                @endcan

            </div>

        </div>

    </div>

</section>

