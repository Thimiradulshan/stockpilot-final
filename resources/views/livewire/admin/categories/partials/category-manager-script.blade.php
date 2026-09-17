@once
    <script>
        document.addEventListener('alpine:init', () => {
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

                    const form = document.createElement('form');

                    form.method = 'POST';
                    form.action = `/admin/categories/${category.id}`;

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
                    nameInput.value = category.name ?? '';

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
                    statusInput.value = 'inactive';

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
        });
    </script>
@endonce
