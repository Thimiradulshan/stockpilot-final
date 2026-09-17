<script>
    window.__stockPilotSaleProducts = @js(
        $products->map(fn ($product) => [
            'id' => $product->id,
            'name' => $product->name,
            'sku' => $product->sku,
            'selling_price' => (string) $product->selling_price,
            'quantity' => (string) $product->quantity,
        ])->values()
    );

    window.salesManager = function (products) {
        const normalizedProducts = (products || []).map((product) => ({
            id: Number(product.id),
            name: product.name ?? '',
            sku: product.sku ?? '',
            selling_price: Number(product.selling_price ?? 0),
            quantity: Number(product.quantity ?? 0),
        }));

        return {
            products: normalizedProducts,

            createOpen: false,
            viewOpen: false,
            voidOpen: false,
            paymentOpen: false,

            viewInvoice: {},
            voidInvoice: {},
            paymentInvoice: {},

            paymentIdempotencyKey: @js(\Illuminate\Support\Str::uuid()),

            createForm: {
                invoice_number: '',
                customer_id: '',
                invoice_date: @js(now()->toDateString()),
                discount_amount: '0.00',
                tax_rate: '0.00',
                notes: '',
                items: [],
            },

            paymentForm: {
                amount: '',
                payment_method: '',
            },

            init() {
                this.resetCreateForm();

                this.$watch('createOpen', (open) => {
                    if (open) {
                        this.$nextTick(() => {
                            document.getElementById('invoice_number')?.focus();
                        });
                    }
                });
            },

            openCreate() {
                this.closeView();
                this.closeVoid();
                this.closePayment();

                this.resetCreateForm();
                this.createOpen = true;
            },

            closeCreate() {
                this.createOpen = false;
            },

            resetCreateForm() {
                this.createForm = {
                    invoice_number: '',
                    customer_id: '',
                    invoice_date: @js(now()->toDateString()),
                    discount_amount: '0.00',
                    tax_rate: '0.00',
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
                    unit_price: '0.00',
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
                        !item.unit_price ||
                        Number(item.unit_price) <= 0
                    )
                ) {
                    item.unit_price = product.selling_price.toFixed(2);
                }

                const quantity = this.toNumber(item.quantity);
                const unitPrice = this.toNumber(item.unit_price);
                const discount = this.toNumber(item.discount_amount);
                const tax = this.toNumber(item.tax_amount);

                const gross = quantity * unitPrice;
                const total = Math.max(
                    0,
                    gross - discount + tax
                );

                item.line_total = total.toFixed(2);
            },

            salesSubtotal() {
                return this.createForm.items.reduce(
                    (total, item) => {
                        const quantity = this.toNumber(item.quantity);
                        const unitPrice = this.toNumber(item.unit_price);

                        return total + (quantity * unitPrice);
                    },
                    0
                );
            },

            salesTaxAmount() {
                const subtotal = this.salesSubtotal();
                const rate = this.toNumber(this.createForm.tax_rate);

                if (rate <= 0) {
                    return 0;
                }

                return Math.round(
                    (subtotal * rate) / 100 * 100
                ) / 100;
            },

            salesTotal() {
                const subtotal = this.salesSubtotal();
                const discount = this.toNumber(
                    this.createForm.discount_amount
                );
                const tax = this.salesTaxAmount();

                return Math.max(
                    0,
                    subtotal - discount + tax
                );
            },

            paymentTotal() {
                return (this.viewInvoice.payments || []).reduce(
                    (total, payment) =>
                        total + this.toNumber(payment.amount),
                    0
                );
            },

            balanceDue() {
                const total = this.toNumber(this.viewInvoice.total_amount);
                const paid = this.paymentTotal();

                return Math.max(0, total - paid);
            },

            paymentBalance() {
                const total = this.toNumber(this.paymentInvoice.total_amount);

                const paid = (this.paymentInvoice.payments || []).reduce(
                    (sum, payment) => sum + this.toNumber(payment.amount),
                    0
                );

                return Math.max(0, total - paid);
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

                const date = new Date(value.includes('T') ? value : `${value}T00:00:00`);

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

                    case 'voided':
                        return 'bg-rose-50 text-rose-700 ring-1 ring-inset ring-rose-200';

                    default:
                        return 'bg-slate-50 text-slate-700 ring-1 ring-inset ring-slate-200';
                }
            },

            paymentStatusBadgeClasses(paymentStatus) {
                switch (paymentStatus) {
                    case 'paid':
                        return 'bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200';

                    case 'partially_paid':
                        return 'bg-sky-50 text-sky-700 ring-1 ring-inset ring-sky-200';

                    case 'unpaid':
                        return 'bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-200';

                    default:
                        return 'bg-slate-50 text-slate-700 ring-1 ring-inset ring-slate-200';
                }
            },

            openView(invoice) {
                this.closeCreate();
                this.closeVoid();
                this.closePayment();

                this.viewInvoice = invoice || {};
                this.viewOpen = true;
            },

            closeView() {
                this.viewOpen = false;
                this.viewInvoice = {};
            },

            openVoid(invoice) {
                if (!invoice || invoice.status !== 'completed') {
                    return;
                }

                this.closeCreate();
                this.closeView();
                this.closePayment();

                this.voidInvoice = invoice;
                this.voidOpen = true;
            },

            closeVoid() {
                this.voidOpen = false;
                this.voidInvoice = {};
            },

            openPayment(invoice) {
                if (!invoice || invoice.status !== 'completed') {
                    return;
                }

                this.closeCreate();
                this.closeView();
                this.closeVoid();

                this.paymentInvoice = invoice;
                this.paymentForm = {
                    amount: this.paymentBalance().toFixed(2),
                    payment_method: '',
                };
                this.paymentIdempotencyKey = @js(\Illuminate\Support\Str::uuid());
                this.paymentOpen = true;
            },

            closePayment() {
                this.paymentOpen = false;
                this.paymentInvoice = {};
                this.paymentForm = {
                    amount: '',
                    payment_method: '',
                };
            },

            closeModals() {
                this.closeCreate();
                this.closeView();
                this.closeVoid();
                this.closePayment();
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