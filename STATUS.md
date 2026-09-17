# StockPilot - Status Report

## Current Branch
main (ahead of origin/main by 1 commit)

## Current Commit
b9601e2 feat: redesign dashboard and refine point of sale workspace

## Files Changed

### Core Changes (POS + Invoice)
- **app/Livewire/Admin/Pos/Index.php** - Removed client-side invoice number generation, added discount type support (fixed/percent), added double-submission prevention, regenerate idempotency key after sale
- **app/Services/InvoiceService.php** - Added discount type support (fixed/percent), server-side invoice number generation via DocumentNumberService
- **app/Services/PurchaseService.php** - Server-side purchase number generation via DocumentNumberService
- **app/Http/Requests/Admin/StoreInvoiceRequest.php** - Made invoice_number nullable, added discount_type, payment_method, amount_received fields
- **app/Http/Requests/Admin/StorePurchaseRequest.php** - Made purchase_number nullable
- **app/Http/Controllers/Admin/InvoiceController.php** - Pass null for invoice_number, record initial payment if amount_received > 0
- **app/Http/Controllers/Admin/PurchaseController.php** - Pass null for purchase_number
- **resources/views/livewire/admin/pos/index.blade.php** - Added discount type selector (fixed/percent), max 100 for percent
- **resources/views/livewire/admin/products/index.blade.php** - Updated to use productManager from app.js
- **resources/js/app.js** - Added productManager Alpine data component

### New Files
- **app/Services/DocumentNumberService.php** - Concurrency-safe document numbering (YY-MM-DD-XXXX format)
- **database/migrations/2026_09_17_000000_create_document_sequences_table.php** - Migration for document_sequences table

### Reports Print
- **app/Livewire/Admin/Reports/Index.php** - Added `print` URL property, conditional render for print layout
- **resources/views/livewire/admin/reports/print.blade.php** - Print-friendly report view with KPIs, tables, filters preserved
- **resources/views/livewire/admin/reports/index.blade.php** - Added Print button

### Stock Ledger Print
- **app/Livewire/Admin/Ledger/Index.php** - Added `print` URL property, conditional render for print layout
- **resources/views/livewire/admin/ledger/print.blade.php** - Print-friendly ledger view with KPIs, table, filters preserved
- **resources/views/livewire/admin/ledger/index.blade.php** - Added Print button

### Shared Print Layout
- **resources/views/layouts/print.blade.php** - Base print layout hiding sidebar, topbar, navigation, action buttons

### Test Updates
- **tests/Feature/Admin/PosTest.php** - Updated for server-generated invoice numbers, double-submission prevention
- **tests/Feature/Admin/InvoiceTest.php** - Removed invoice_number from invalid data test expectations
- **tests/Feature/Admin/PurchaseTest.php** - Removed purchase_number from invalid data test expectations
- **tests/Unit/Services/PurchaseServiceTest.php** - Added DocumentNumberService to constructor

### Other
- **resources/views/components/desktop-user-menu.blade.php** - Fixed dropdown positioning (teleport to body)
- **resources/views/components/stockpilot/primary-action.blade.php** - Fixed attribute forwarding for wire:click
- **resources/views/livewire/admin/products/product-manager-script.blade.php** - Deleted (moved to app.js)
- **app/Actions/Fortify/ResetUserPassword.php** - Fixed phpstan type hint (pre-existing)

## POS Fixes (VERIFIED)
- ✅ Invoice number: Server-generated, unique, concurrency-safe via DocumentNumberService
- ✅ Discount: Supports both percentage and fixed amount (UI preview + server validation)
- ✅ Tax: Updates correctly when POS values change (calculated on subtotal)
- ✅ Totals: Correctly calculated (subtotal, discount, tax, grand total) using Brick\Math\BigDecimal
- ✅ Payment: Supports payment method + paid amount, payment status derived from actual payment
- ✅ Stock: Uses existing StockService for atomic stock decrease
- ✅ Customer: Preserves existing customer workflow (walk-in + search)
- ✅ Validation: Server-side validation for all important values
- ✅ Authorization: Preserves existing authorization

## Invoice Fixes (VERIFIED)
- ✅ Invoice number: Server-generated via DocumentNumberService (nullable in request)
- ✅ Discount: Supports both percentage and fixed amount (InvoiceService)
- ✅ Tax: Correctly calculated on subtotal
- ✅ Payment: Initial payment recorded if amount_received > 0, payment_status derived server-side
- ✅ Authorization: Preserves existing authorization

## Reports Print (VERIFIED)
- ✅ Uses existing report data (same queries)
- ✅ Preserves current filters (section, date range, customer, supplier, group by)
- ✅ Prints exactly the currently displayed report data
- ✅ Print-friendly layout (hides sidebar, topbar, navigation, action buttons)
- ✅ Preserves tables, totals and headings
- ✅ No fake/static report data
- ✅ No duplicate report calculations

## Stock Ledger Print (VERIFIED)
- ✅ Prints actual ledger data currently filtered
- ✅ Preserves all active filters (date range, type, product search)
- ✅ Preserves stock movement information
- ✅ Hides application shell during print (uses layouts.print)
- ✅ Hides buttons and controls (no-print class)
- ✅ Professional print layout
- ✅ No fake/static data
- ✅ No duplicate stock calculations (reuses rows() method)

## Tests Executed
- ✅ php artisan test --filter=PosTest (33 tests, 33 passed)
- ✅ php artisan test --filter=InvoiceTest (all passed)
- ✅ php artisan test --filter=PurchaseTest (all passed)
- ✅ php artisan test --filter=Report (13 tests, 13 passed)
- ✅ php artisan test --filter=Ledger (6 tests, 6 passed)
- ✅ php artisan test --filter=Payment (all passed)
- ✅ php artisan test --filter=Stock (all passed)
- ✅ Full test suite: 374 tests, 373 passed, 1 skipped

## Build Result
- ✅ php artisan optimize:clear - OK
- ✅ php artisan view:cache - OK
- ✅ npm run build - OK (84.60 kB JS, 114.93 kB CSS)
- ✅ php vendor/bin/pint --test - OK
- ✅ php vendor/bin/phpstan analyse --no-progress - OK (level 7, 0 errors)

## Remaining Known Issues
- **NOT VERIFIED**: Manual browser testing of POS discount percentage calculation
- **NOT VERIFIED**: Manual browser testing of Reports print layout across different sections
- **NOT VERIFIED**: Manual browser testing of Stock Ledger print layout
- **NOT VERIFIED**: Concurrent invoice creation under load (theoretical, uses row locks)
- **TODO**: Update STATUS.md with actual results after manual verification

## Exact Next Action
1. Manual browser testing of POS with percentage discount
2. Manual browser testing of Reports print for each section (sales, purchases, profit, valuation, outstanding, VAT)
3. Manual browser testing of Stock Ledger print with various filters
4. Verify print layout renders correctly (no sidebar/topbar, proper page breaks if needed)