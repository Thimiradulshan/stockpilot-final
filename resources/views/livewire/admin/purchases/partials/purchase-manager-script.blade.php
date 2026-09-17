<script>
    window.__stockPilotPurchaseProducts = @js(
        $products->map(fn ($product) => [
            'id' => $product->id,
            'name' => $product->name,
            'sku' => $product->sku,
            'cost_price' => (string) $product->cost_price,
            'quantity' => (string) $product->quantity,
        ])->values()
    );

    window.purchaseManager = function (products) {
        const normalizedProducts = (products || []).map((product) => ({
            id: Number(product.id),
            name: product.name ?? '',
            sku: product.sku ?? '',
            cost_price: Number(product.cost_price ?? 0),
            quantity: Number(product.quantity ?? 0),
        }));

        return {
            products: normalizedProducts,

            createOpen: false,
            viewOpen: false,
            cancelOpen: false,

            viewPurchase: {},
            cancelPurchase: {},

            createForm: {
                purchase_number: '',
                supplier_id: '',
                purchase_date: @js(now()->toDateString()),
                discount_amount: '0.00',
                tax_amount: '0.00',
                notes: '',
                items: [],
            },

            init() {
                this.resetCreateForm();

                this.$watch('createOpen', (open) => {
                    if (open) {
                        this.$nextTick(() => {
                            document.getElementById('purchase_number')?.focus();
                        });
                    }
                });
            },

            openCreate() {
                this.closeView();
                this.closeCancel();

                this.resetCreateForm();
                this.createOpen = true;
            },

            closeCreate() {
                this.createOpen = false;
            },

            resetCreateForm() {
                this.createForm = {
                    purchase_number: '',
                    supplier_id: '',
                    purchase_date: @js(now()->toDateString()),
                    discount_amount: '0.00',
                    tax_amount: '0.00',
                    notes: '',
                    items: [
                        this.makeItem(),
                    ],
                };
            },

            makeItem() {
                return {
                    key: `${Date.now()}-${Math.random().toString(36).slice(2)}`,
                    product_id: '',
                    quantity: '1',
                    unit_cost: '0.00',
                    discount_amount: '0.00',
                    tax_amount: '0.00',
                    line_total: '0.00',
                };
            },

            addItem() {
                this.createForm.items.push(this.makeItem());
            },

            removeItem(index) {
                if (this.createForm.items.length <= 1) {
                    return;
                }

                this.createForm.items.splice(index, 1);
            },

            findProduct(productId) {
                const numericId = Number(productId);

                return this.products.find(
                    (product) => product.id === numericId
                ) ?? null;
            },

            selectedProductLabel(productId) {
                const product = this.findProduct(productId);

                if (!product) {
                    return '';
                }

                const stockText = `${this.formatQuantity(product.quantity)} in stock`;

                return `${product.sku} · ${stockText}`;
            },

            syncItem(index) {
                const item = this.createForm.items[index];

                if (!item) {
                    return;
                }

                const product = this.findProduct(item.product_id);

                if (
                    product &&
                    (
                        !item.unit_cost ||
                        Number(item.unit_cost) <= 0
                    )
                ) {
                    item.unit_cost = product.cost_price.toFixed(2);
                }

                const quantity = this.toNumber(item.quantity);
                const unitCost = this.toNumber(item.unit_cost);
                const discount = this.toNumber(item.discount_amount);
                const tax = this.toNumber(item.tax_amount);

                const gross = quantity * unitCost;
                const total = Math.max(
                    0,
                    gross - discount + tax
                );

                item.line_total = total.toFixed(2);
            },

            purchaseSubtotal() {
                return this.createForm.items.reduce(
                    (total, item) => {
                        const quantity = this.toNumber(item.quantity);
                        const unitCost = this.toNumber(item.unit_cost);

                        return total + (quantity * unitCost);
                    },
                    0
                );
            },

            purchaseAdjustmentTotal() {
                const headerDiscount = this.toNumber(
                    this.createForm.discount_amount
                );

                const headerTax = this.toNumber(
                    this.createForm.tax_amount
                );

                return headerTax - headerDiscount;
            },

            purchaseTotal() {
                const subtotal = this.purchaseSubtotal();
                const discount = this.toNumber(
                    this.createForm.discount_amount
                );
                const tax = this.toNumber(
                    this.createForm.tax_amount
                );

                return Math.max(
                    0,
                    subtotal - discount + tax
                );
            },

            formatMoney(value) {
                const amount = this.toNumber(value);

                return amount.toLocaleString('en-LK', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2,
                });
            },

            formatQuantity(value) {
                const amount = this.toNumber(value);

                if (Number.isInteger(amount)) {
                    return amount.toLocaleString('en-LK');
                }

                return amount.toLocaleString('en-LK', {
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 3,
                });
            },

            formatDate(value) {
                if (!value) {
                    return '—';
                }

                const date = new Date(`${value}T00:00:00`);

                if (Number.isNaN(date.getTime())) {
                    return value;
                }

                return new Intl.DateTimeFormat('en-LK', {
                    day: '2-digit',
                    month: 'short',
                    year: 'numeric',
                }).format(date);
            },

            statusBadgeClasses(status) {
                switch (status) {
                    case 'completed':
                        return 'bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200';

                    case 'cancelled':
                        return 'bg-rose-50 text-rose-700 ring-1 ring-inset ring-rose-200';

                    default:
                        return 'bg-slate-50 text-slate-700 ring-1 ring-inset ring-slate-200';
                }
            },

            openView(purchase) {
                this.closeCreate();
                this.closeCancel();

                this.viewPurchase = purchase || {};
                this.viewOpen = true;
            },

            closeView() {
                this.viewOpen = false;
                this.viewPurchase = {};
            },

            openCancel(purchase) {
                if (!purchase || purchase.status !== 'completed') {
                    return;
                }

                this.closeCreate();
                this.closeView();

                this.cancelPurchase = purchase;
                this.cancelOpen = true;
            },

            closeCancel() {
                this.cancelOpen = false;
                this.cancelPurchase = {};
            },

            closeModals() {
                this.closeCreate();
                this.closeView();
                this.closeCancel();
            },

            toNumber(value) {
                const number = Number(value);

                return Number.isFinite(number)
                    ? number
                    : 0;
            },
        };
    };
</script>
