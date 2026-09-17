# StockPilot — Entity Relationship Diagram

The database is a single business schema with three logical areas: master
data, purchasing and sales/payments, plus the stock movement audit trail.

Roles are enforced at the application layer (`App\Enums\UserRole`, policies and
`role:` middleware); users.payments.idempotency_key guards against duplicate
payment submissions.

```mermaid
erDiagram
    users {
        bigint id PK
        string name
        string email UK
        string password
        timestamp email_verified_at
        string two_factor_secret
        string role "admin | sales | stock"
        string status "active | inactive"
        string profile_photo_path
        timestamp created_at
        timestamp updated_at
    }

    categories {
        bigint id PK
        string name UK
        text description
        string status
        timestamp created_at
        timestamp updated_at
    }

    suppliers {
        bigint id PK
        string name
        string company
        string phone
        string email
        text address
        string status
        timestamp created_at
        timestamp updated_at
    }

    products {
        bigint id PK
        bigint category_id FK
        string name
        string sku UK
        decimal cost_price
        decimal selling_price
        decimal quantity
        decimal reorder_level
        text description
        string status
        timestamp created_at
        timestamp updated_at
    }

    product_supplier {
        bigint product_id PK,FK
        bigint supplier_id PK,FK
        string supplier_product_code
        decimal last_cost
        boolean is_preferred
        timestamp created_at
        timestamp updated_at
    }

    customers {
        bigint id PK
        string name
        string phone
        string email
        text address
        string status
        timestamp created_at
        timestamp updated_at
    }

    purchases {
        bigint id PK
        string purchase_number UK
        bigint supplier_id FK
        date purchase_date
        decimal subtotal
        decimal discount_amount
        decimal tax_rate
        decimal tax_amount
        decimal total_amount
        string status "completed | cancelled"
        bigint created_by FK
        timestamp created_at
        timestamp updated_at
    }

    purchase_items {
        bigint id PK
        bigint purchase_id FK
        bigint product_id FK
        decimal quantity
        decimal unit_cost
        decimal line_total
        timestamp created_at
        timestamp updated_at
    }

    invoices {
        bigint id PK
        string invoice_number UK
        bigint customer_id FK "nullable"
        date invoice_date
        decimal subtotal
        decimal discount_amount
        decimal tax_rate
        decimal tax_amount
        decimal total_amount
        string status "completed | voided"
        string payment_status "paid | partially_paid | unpaid"
        text notes
        bigint created_by FK
        timestamp created_at
        timestamp updated_at
    }

    invoice_items {
        bigint id PK
        bigint invoice_id FK
        bigint product_id FK
        decimal quantity
        decimal unit_price
        decimal discount_amount
        decimal tax_amount
        decimal line_total
        timestamp created_at
        timestamp updated_at
    }

    payments {
        bigint id PK
        bigint invoice_id FK
        datetime payment_date
        decimal amount
        string payment_method
        string reference
        text notes
        bigint received_by FK
        string idempotency_key UK "nullable"
        timestamp created_at
        timestamp updated_at
    }

    stock_movements {
        bigint id PK
        bigint product_id FK
        string movement_type "purchase | sale | adjustment | cancellation | void"
        decimal quantity "negative for outbound"
        decimal quantity_before
        decimal quantity_after
        string reason
        bigint created_by FK
        timestamp created_at
        timestamp updated_at
    }

    categories ||--o{ products : "contains"
    products ||--o{ product_supplier : "sourced from"
    suppliers ||--o{ product_supplier : "supplies"
    customers ||--o{ invoices : "is billed"
    suppliers ||--o{ purchases : "receives orders"
    purchases ||--o{ purchase_items : "lined with"
    products ||--o{ purchase_items : "purchased"
    invoices ||--o{ invoice_items : "lined with"
    products ||--o{ invoice_items : "sold"
    invoices ||--o{ payments : "settled by"
    users ||--o{ invoices : "created by"
    users ||--o{ payments : "received by"
    users ||--o{ purchases : "raised by"
    products ||--o{ stock_movements : "audited by"
    users ||--o{ stock_movements : "recorded by"
```

## Notes

- **Movements**: every purchase, sale, manual stock adjustment, cancelled
  purchase and voided invoice appends a `stock_movements` row kept in sync with
  `products.quantity` inside database transactions.
- **Financial state**: invoice money state is derived from `invoices.payment_status`
  plus the sum of `payments.amount`, and customer statements / outstanding
  reports recompute a running balance from invoices and payments.
- **Soft deletion**: financial and stock documents (`invoices`, `purchases`,
  `payments`, `stock_movements`) are never deleted; master data rows are
  deactivated via their `status` column instead.
- **References**: all `created_by` / `received_by` foreign keys point to `users.id`;
  document dates may not be backdated beyond
  `stockpilot.document_date_lookback_days` (default 365) or set in the future.