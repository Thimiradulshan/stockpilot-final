import Swal from 'sweetalert2';

const STOCKPILOT_THEMES = ['light', 'dark', 'system'];

const systemMediaQuery = window.matchMedia(
    '(prefers-color-scheme: dark)'
);

const getStoredTheme = () => {
    const stored = localStorage.getItem('stockpilot-theme');

    return STOCKPILOT_THEMES.includes(stored)
        ? stored
        : 'system';
};

const resolveTheme = (theme) => {
    if (theme === 'system') {
        return systemMediaQuery.matches
            ? 'dark'
            : 'light';
    }

    return theme;
};

const applyTheme = (theme = getStoredTheme()) => {
    const resolved = resolveTheme(theme);

    document.documentElement.classList.toggle(
        'dark',
        resolved === 'dark'
    );

    document.documentElement.dataset.theme = resolved;
};

const stockPilotSwal = {
    base(options = {}) {
        return Swal.fire({
            buttonsStyling: false,

            customClass: {
                popup: 'stockpilot-swal-popup',
                title: 'stockpilot-swal-title',
                htmlContainer: 'stockpilot-swal-html',
                confirmButton: 'stockpilot-swal-confirm',
                cancelButton: 'stockpilot-swal-cancel',
                denyButton: 'stockpilot-swal-cancel',
            },

            ...options,
        });
    },

    success(title, text = '') {
        return this.base({
            icon: 'success',
            title,
            text,
            confirmButtonText: 'OK',
        });
    },

    error(title, text = '') {
        return this.base({
            icon: 'error',
            title,
            text,
            confirmButtonText: 'OK',
        });
    },

    warning(title, text = '') {
        return this.base({
            icon: 'warning',
            title,
            text,
            confirmButtonText: 'OK',
        });
    },

    info(title, text = '') {
        return this.base({
            icon: 'info',
            title,
            text,
            confirmButtonText: 'OK',
        });
    },

    confirmDeactivate(categoryName) {
        const safeName = this.escapeHtml(categoryName);

        return this.base({
            icon: 'warning',
            title: 'Deactivate category?',
            html: `
                <div style="line-height:1.7">
                    You are about to deactivate
                    <strong>${safeName}</strong>.
                    <br>
                    The category will remain in the database,
                    but it will no longer be active.
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Deactivate',
            cancelButtonText: 'Cancel',
            reverseButtons: true,
        });
    },

    confirmActivate(categoryName) {
        const safeName = this.escapeHtml(categoryName);

        return this.base({
            icon: 'question',
            title: 'Activate category?',
            html: `
                <div style="line-height:1.7">
                    <strong>${safeName}</strong>
                    will become active again and can be used
                    for product management.
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Activate',
            cancelButtonText: 'Cancel',
            reverseButtons: true,
        });
    },

    toast(icon, title) {
        return Swal.fire({
            toast: true,
            position: 'top-end',
            icon,
            title,
            showConfirmButton: false,
            timer: 3200,
            timerProgressBar: true,
        });
    },

    escapeHtml(value) {
        return String(value ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    },
};

window.Swal = Swal;
window.StockPilotSwal = stockPilotSwal;

document.addEventListener('alpine:init', () => {



    Alpine.data('stockPilotShell', () => ({
        sidebarCollapsed:
            localStorage.getItem('stockpilot-sidebar') === 'collapsed',

        mobileSidebarOpen: false,

        sidebarIsExpanded() {
            if (window.innerWidth < 1024) {
                return this.mobileSidebarOpen;
            }

            return !this.sidebarCollapsed;
        },

        toggleSidebar() {
            this.sidebarCollapsed =
                !this.sidebarCollapsed;

            localStorage.setItem(
                'stockpilot-sidebar',
                this.sidebarCollapsed
                    ? 'collapsed'
                    : 'expanded'
            );
        },

        openMobileSidebar() {
            this.mobileSidebarOpen = true;
        },

        closeMobileSidebar() {
            this.mobileSidebarOpen = false;
        },
    }));



    Alpine.data('categoryManager', () => ({
        createOpen: false,

        editOpen: false,

        editingCategory: null,

        openCreate() {
            this.closeAll();

            this.createOpen = true;

            this.$nextTick(() => {
                document
                    .getElementById('create_category_name')
                    ?.focus();
            });
        },

        openEdit(category) {
            this.closeAll();

            this.editingCategory = {
                id: category.id,
                name: category.name ?? '',
                description: category.description ?? '',
                status: category.status ?? 'active',
            };

            this.editOpen = true;

            this.$nextTick(() => {
                document
                    .getElementById('edit_category_name')
                    ?.focus();
            });
        },

        async requestDeactivate(category) {
            const result =
                await window.StockPilotSwal.confirmDeactivate(
                    category.name
                );

            if (!result.isConfirmed) {
                return;
            }

            this.submitStatusChange(
                category,
                'inactive'
            );
        },

        async requestActivate(category) {
            const result =
                await window.StockPilotSwal.confirmActivate(
                    category.name
                );

            if (!result.isConfirmed) {
                return;
            }

            this.submitStatusChange(
                category,
                'active'
            );
        },

        submitStatusChange(category, status) {
            const form =
                document.createElement('form');

            form.method = 'POST';

            form.action =
                `/admin/categories/${category.id}`;

            const csrfToken =
                document
                    .querySelector('meta[name="csrf-token"]')
                    ?.getAttribute('content');

            const csrfInput =
                document.createElement('input');

            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = csrfToken ?? '';

            const methodInput =
                document.createElement('input');

            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'PATCH';

            const nameInput =
                document.createElement('input');

            nameInput.type = 'hidden';
            nameInput.name = 'name';
            nameInput.value =
                category.name ?? '';

            const descriptionInput =
                document.createElement('input');

            descriptionInput.type = 'hidden';
            descriptionInput.name = 'description';
            descriptionInput.value =
                category.description ?? '';

            const statusInput =
                document.createElement('input');

            statusInput.type = 'hidden';
            statusInput.name = 'status';
            statusInput.value = status;

            form.appendChild(csrfInput);
            form.appendChild(methodInput);
            form.appendChild(nameInput);
            form.appendChild(descriptionInput);
            form.appendChild(statusInput);

            document.body.appendChild(form);

            form.submit();
        },

        closeCreate() {
            this.createOpen = false;
        },

        closeEdit() {
            this.editOpen = false;
            this.editingCategory = null;
        },

        closeAll() {
            this.createOpen = false;
            this.editOpen = false;
            this.editingCategory = null;
        },

        closeOnEscape() {
            this.closeAll();
        },
    }));



    Alpine.data('productManager', (initial = {}) => ({
        createProductOpen: initial.createProductOpen ?? false,

        editProductOpen: false,

        stockAdjustOpen: false,

        editingProduct: null,

        stockAdjustDirection: 'receive',

        stockAdjustQuantity: 0,

        init() {
            this.$watch('createProductOpen', (open) => {
                if (open) {
                    this.$nextTick(() => {
                        document
                            .getElementById('create_product_name')
                            ?.focus();
                    });
                }
            });
        },

        openCreateProduct() {
            this.editProductOpen = false;
            this.stockAdjustOpen = false;
            this.createProductOpen = true;
        },

        closeCreateProduct() {
            this.createProductOpen = false;
        },

        openEditProduct(product) {
            this.createProductOpen = false;
            this.stockAdjustOpen = false;
            this.editingProduct = product;
            this.editProductOpen = true;
        },

        closeEditProduct() {
            this.editProductOpen = false;
            this.editingProduct = null;
        },

        openStockAdjust(product) {
            this.createProductOpen = false;
            this.editProductOpen = false;
            this.editingProduct = product;
            this.stockAdjustDirection = 'receive';
            this.stockAdjustQuantity = 0;
            this.stockAdjustOpen = true;
        },

        closeStockAdjust() {
            this.stockAdjustOpen = false;
            this.editingProduct = null;
        },

        closeProducts() {
            this.closeCreateProduct();
            this.closeEditProduct();
            this.closeStockAdjust();
        },
    }));



    Alpine.store('theme', {
        theme: getStoredTheme(),

        systemDark:
            systemMediaQuery.matches,

        get dark() {
            return (
                this.theme === 'dark'
                ||
                (
                    this.theme === 'system'
                    &&
                    this.systemDark
                )
            );
        },

        set(value) {
            if (!STOCKPILOT_THEMES.includes(value)) {
                return;
            }

            this.theme = value;

            localStorage.setItem(
                'stockpilot-theme',
                value
            );

            this.apply();
        },

        apply() {
            applyTheme(this.theme);
        },

        init() {
            this.apply();
        },
    });

});

systemMediaQuery.addEventListener(
    'change',
    (event) => {

        if (
            getStoredTheme() !== 'system'
        ) {
            return;
        }

        document.documentElement.classList.toggle(
            'dark',
            event.matches
        );
    }
);

document.addEventListener(
    'livewire:navigating',
    () => {
        applyTheme();
    }
);

document.addEventListener(
    'livewire:navigated',
    () => {
        applyTheme();
    }
);

applyTheme();
