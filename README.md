# StockPilot — Advanced Sales & Inventory Management System

StockPilot is a full-featured retail/wholesale management application built with
Laravel 13, Livewire 3 and Tailwind CSS 4. It handles day-to-day purchasing,
sales, payments, stock control and reporting for a single-location store, with a
role-based access model for administrators, sales operators and warehouse staff.

## Features

- **Authentication & profiles** — email/password login, email verification,
  two-factor (TOTP) support, password confirmation, profile & appearance settings.
- **Role-based access control** — three roles enforced by middleware, Livewire
  authorization and per-model policies:
  - **Admin** — full access to every module.
  - **Sales user** — customers, sales, payments, receipts, customer statements
    and sales/outstanding/VAT reports.
  - **Inventory user** — categories, suppliers, products, purchases, stock
    adjustments and the purchase/valuation reports.
- **Master data** — categories, suppliers, products (SKU, cost/selling price,
  stock quantity, reorder level) and customers, each activating/deactivating.
- **Purchasing** — purchase orders with line items, completed/cancelled status,
  automatic supplier-to-product linking and inventory intake.
- **Sales & payments** — invoices with line items, discounts and VAT; completed /
  void / invoice lifecycle; paid / partially-paid / unpaid tracking; payments
  with idempotency keys; printable receipts; printable invoices.
- **Stock control** — every purchase, sale and manual adjustment records a
  stock movement, and the stock ledger shows running units-in / units-out /
  net balance per product.
- **Dashboard** — today's sales, all-time total sales, invoices today,
  outstanding credit, total products, total suppliers, stock value, low stock
  and out-of-stock counts, recent invoices and payments.
- **Reports** (CSV export on every section):
  - Sales report with period grouping and invoice-level detail.
  - Customer outstanding report per selected period.
  - Purchases report (with supplier filter and line detail).
  - Profit summary.
  - Stock valuation with per-product status (In Stock / Low Stock / Out of Stock).
  - VAT summary (output tax minus input tax).
- **Customer statements** — running outstanding balance per customer across a
  date range (date, invoice number, sales amount, payment amount, balance).

## Technology stack

| Layer      | Choice                                              |
|------------|-----------------------------------------------------|
| Framework  | Laravel 13 (PHP 8.4)                                |
| UI         | Livewire 3, Alpine.js, Tailwind CSS 4 (dark mode)   |
| Database   | MySQL 8+ (SQLite supported for tests)               |
| Testing    | PHPUnit 12 (336 tests)                              |
| Quality    | Laravel Pint (style), PHPStan (static analysis)     |

## Requirements

- PHP 8.4+ with required Laravel extensions
- Composer 2
- MySQL 8+ (or SQLite for development/testing)
- Node.js 20+ and npm (required to build front-end assets)

## Installation

1. Clone the repository and install dependencies:

   ```bash
   composer install
   npm install && npm run build
   ```

2. Create the environment file and generate an application key:

   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

   Configure your database connection in `.env` (a single MySQL/SQLite database
   is expected). The business identity printed on receipts is also configured
   here: `BUSINESS_NAME`, `BUSINESS_ADDRESS`, `BUSINESS_PHONE`,
   `BUSINESS_EMAIL`, `BUSINESS_TAX_NUMBER`.

3. Run migrations and seed the database:

   ```bash
   php artisan migrate --seed
   ```

   Seeding creates the default administrator. To also load sample master data
   (products, suppliers, customers and demo users), set `SEED_DEMO=true` before
   seeding.

4. Start the development server:

   ```bash
   php artisan dev
   ```

   StockPilot is tested and verified on **WAMP** (http://localhost/stockpilot)
   and the built-in Laravel server (http://localhost:8000).

## Demo credentials

| Role             | Email                  | Password   | Access                                       |
|------------------|------------------------|------------|----------------------------------------------|
| Administrator    | `admin@stockpilot.app` | `password` | All modules                                  |
| Sales user       | `sales@stockpilot.app` | `password` | Sales, payments, statements, sales reports   |
| Inventory user   | `stock@stockpilot.app` | `password` | Products, purchases, stock reports           |

Credentials come from configuration (`stockpilot.default_admin.email` /
`DEFAULT_ADMIN_EMAIL`). The admin and demo users are created by
`database/seeders`. Production deployments must override
`DEFAULT_ADMIN_PASSWORD` with a strong, unique value (the seeder refuses the
default password in `APP_ENV=production`).

> The "Inventory User" role is stored internally as `stock` (see
> `App\Enums\UserRole`) — the label shown in the UI is "Inventory User".

## Setting up a demo dataset

```bash
SEED_DEMO=true php artisan migrate:fresh --seed
```

This seeds 3 categories, 3 suppliers, 6 customers, 8 products with initial
stock movements, plus the admin, sales and inventory demo users shown above.

## Quality checks

```bash
composer ci:check        # config cache + Pint + PHPStan + full test suite
composer lint            # auto-fix code style with Pint
composer types:check     # PHPStan static analysis
```

The CI workflow (`.github/workflows/tests.yml`) runs `composer ci:check` on
push and pull request to `main`.

## Project structure

- `app/Enums` — role and stock-movement type enums.
- `app/Livewire/Admin/*` — Livewire components for every admin module
  (dashboard, users, categories, suppliers, products, customers, purchases,
  sales, reports, customer statements, stock ledger).
- `app/Models` — Eloquent models with casts and query scopes.
- `app/Policies` — granular module authorization per role.
- `resources/views/livewire/admin/*` — module blades.
- `resources/views/components/stockpilot/*` — shared UI components
  (page header, KPI cards, tabs, tables, filter bars, empty states, icons).
- `routes/web.php` — admin routes grouped by module with role middleware.
- `database/seeders` — idempotent admin and demo seeders.

## Documentation

- Database schema: [docs/ER-Diagram.md](docs/ER-Diagram.md)
