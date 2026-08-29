======================================================
       🏗️ SUPERMARKET SYSTEM ARCHITECTURE MAP         
======================================================

--- [ 1. DOMAIN DRIVEN DESIGN (DDD) STRUCTURE ] ---
Domain: [ Auth ]
  ├── Actions (3 files)
  ├── DTOs (3 files)
  ├── Enums (3 files)
  ├── Models (4 files)
  ├── Policies (3 files)
  ├── Services (2 files)
Domain: [ CRM ]
  ├── Models (2 files)
Domain: [ Customers ]
  ├── Actions (3 files)
  ├── DTOs (2 files)
  ├── Enums (1 files)
  ├── Models (3 files)
  ├── Services (1 files)
Domain: [ Finance ]
  ├── Actions (6 files)
  ├── DTOs (4 files)
  ├── Enums (3 files)
  ├── Models (5 files)
  ├── Services (3 files)
Domain: [ Inventory ]
  ├── Actions (10 files)
  ├── DTOs (6 files)
  ├── Enums (4 files)
  ├── Models (10 files)
  ├── Services (3 files)
Domain: [ Organization ]
  ├── Actions (4 files)
  ├── DTOs (4 files)
  ├── Enums (3 files)
  ├── Models (5 files)
  ├── Services (1 files)
Domain: [ POS ]
  ├── Actions (7 files)
  ├── DTOs (6 files)
  ├── Enums (5 files)
  ├── Models (11 files)
  ├── Services (5 files)
Domain: [ Payments ]
  ├── Contracts (1 files)
  ├── Gateways (1 files)
  ├── Models (1 files)
Domain: [ Pricing ]
  ├── Actions (3 files)
  ├── DTOs (3 files)
  ├── Enums (2 files)
  ├── Models (5 files)
  ├── Services (2 files)
Domain: [ Purchases ]
  ├── Actions (3 files)
  ├── DTOs (3 files)
  ├── Enums (2 files)
  ├── Models (6 files)
  ├── Services (2 files)
Domain: [ Reporting ]
  ├── Actions (4 files)
  ├── DTOs (1 files)
  ├── Models (2 files)
  ├── Services (2 files)
Domain: [ Sales ]
  ├── Models (2 files)
Domain: [ Settings ]
  ├── Actions (2 files)
  ├── DTOs (2 files)
  ├── Enums (1 files)
  ├── Models (2 files)
  ├── Services (1 files)
Domain: [ System ]
  ├── Models (1 files)

--- [ 2. DATABASE SCHEMA (SQLite) ] ---
Table: migrations
  └─ Columns: id, migration, batch
Table: users
  └─ Columns: id, name, username, email, phone, email_verified_at, password, pin_code, is_active, remember_token, created_at, updated_at
Table: password_reset_tokens
  └─ Columns: email, token, created_at
Table: sessions
  └─ Columns: id, user_id, ip_address, user_agent, payload, last_activity
Table: cache
  └─ Columns: key, value, expiration
Table: cache_locks
  └─ Columns: key, owner, expiration
Table: jobs
  └─ Columns: id, queue, payload, attempts, reserved_at, available_at, created_at
Table: job_batches
  └─ Columns: id, name, total_jobs, pending_jobs, failed_jobs, failed_job_ids, options, cancelled_at, created_at, finished_at
Table: failed_jobs
  └─ Columns: id, uuid, connection, queue, payload, exception, failed_at
Table: personal_access_tokens
  └─ Columns: id, tokenable_type, tokenable_id, name, token, abilities, last_used_at, expires_at, created_at, updated_at
Table: companies
  └─ Columns: id, name, tax_number, phone, email, address, created_at, updated_at
Table: stores
  └─ Columns: id, company_id, name, code, phone, address, receipt_header, receipt_footer, status, created_at, updated_at
Table: warehouses
  └─ Columns: id, store_id, name, type, location, created_at, updated_at
Table: registers
  └─ Columns: id, store_id, name, code, status, created_at, updated_at
Table: store_users
  └─ Columns: store_id, user_id, role, created_at, updated_at
Table: audit_logs
  └─ Columns: id, user_id, store_id, action, auditable_type, auditable_id, old_values, new_values, reason, ip_address, created_at, updated_at
Table: permissions
  └─ Columns: id, name, guard_name, created_at, updated_at
Table: roles
  └─ Columns: id, name, guard_name, created_at, updated_at
Table: model_has_permissions
  └─ Columns: permission_id, model_type, model_uuid
Table: model_has_roles
  └─ Columns: role_id, model_type, model_uuid
Table: role_has_permissions
  └─ Columns: permission_id, role_id
Table: categories
  └─ Columns: id, parent_id, name, code, created_at, updated_at
Table: units
  └─ Columns: id, name, short_code, allow_decimal, created_at, updated_at
Table: products
  └─ Columns: id, category_id, unit_id, name, sku, cost_price, retail_price, alert_quantity, is_active, created_at, updated_at
Table: barcodes
  └─ Columns: id, product_id, code, type, packing_qty, created_at, updated_at
Table: batches
  └─ Columns: id, product_id, warehouse_id, batch_number, stock_qty, purchase_cost, expiry_date, created_at, updated_at
Table: stock_reservations
  └─ Columns: id, product_id, order_id, quantity, expires_at, created_at, updated_at
Table: stock_movements
  └─ Columns: id, batch_id, warehouse_id, type, quantity, created_at, updated_at
Table: stock_counts
  └─ Columns: id, warehouse_id, status, counted_at, created_at, updated_at
Table: stock_count_items
  └─ Columns: id, stock_count_id, product_id, counted_qty, system_qty, created_at, updated_at
Table: wastages
  └─ Columns: id, product_id, batch_id, quantity, reason, created_at, updated_at
Table: customer_groups
  └─ Columns: id, name, discount_percent, created_at, updated_at
Table: customers
  └─ Columns: id, customer_group_id, name, phone, total_debt, debt_limit, is_active, created_at, updated_at
Table: customer_addresses
  └─ Columns: id, customer_id, label, address_line, created_at, updated_at
Table: loyalty_accounts
  └─ Columns: id, customer_id, points_balance, created_at, updated_at
Table: loyalty_transactions
  └─ Columns: id, loyalty_account_id, points, type, created_at, updated_at
Table: suppliers
  └─ Columns: id, name, phone, email, total_balance, created_at, updated_at
Table: purchase_orders
  └─ Columns: id, supplier_id, po_number, total_amount, status, created_at, updated_at
Table: purchase_order_items
  └─ Columns: id, purchase_order_id, product_id, quantity, cost_price, created_at, updated_at
Table: goods_received_notes
  └─ Columns: id, purchase_order_id, grn_number, received_at, created_at, updated_at
Table: supplier_payments
  └─ Columns: id, supplier_id, amount, payment_method, created_at, updated_at
Table: supplier_ledger
  └─ Columns: id, supplier_id, entry_type, amount, running_balance, created_at, updated_at
Table: promotions
  └─ Columns: id, name, type, discount_value, is_active, starts_at, ends_at, created_at, updated_at, product_id
Table: price_rules
  └─ Columns: id, product_id, scope, min_qty, price, created_at, updated_at
Table: coupons
  └─ Columns: id, code, discount_value, type, expires_at, is_active, created_at, updated_at
Table: register_shifts
  └─ Columns: id, register_id, user_id, opening_cash, closing_cash, cash_difference, status, opened_at, closed_at, created_at, updated_at
Table: suspended_orders
  └─ Columns: id, register_id, user_id, cart_data, parked_at, created_at, updated_at
Table: offline_transactions
  └─ Columns: id, register_id, payload, sync_status, created_at, updated_at
Table: cash_transactions
  └─ Columns: id, register_shift_id, type, amount, reason, created_at, updated_at
Table: orders
  └─ Columns: id, invoice_number, store_id, register_shift_id, customer_id, user_id, subtotal, discount_amount, tax_amount, grand_total, paid_amount, change_due, status, created_at, updated_at, invoice_no, shift_id, discount, payment_method, payment_status, reference_no, customer_name, customer_phone, customer_address
Table: order_items
  └─ Columns: id, order_id, product_id, promotion_id, quantity, unit_price, total_price, created_at, updated_at
Table: stock_allocations
  └─ Columns: id, order_item_id, batch_id, quantity, unit_cost, created_at, updated_at
Table: payments
  └─ Columns: id, order_id, method, amount, currency, exchange_rate, reference_number, created_at, updated_at
Table: return_orders
  └─ Columns: id, order_id, user_id, refund_amount, status, created_at, updated_at
Table: return_order_items
  └─ Columns: id, return_order_id, product_id, quantity, refund_price, condition, created_at, updated_at
Table: accounts
  └─ Columns: id, code, name, type, created_at, updated_at
Table: journal_entries
  └─ Columns: id, order_id, purchase_order_id, source_type, posted_at, created_at, updated_at
Table: journal_entry_lines
  └─ Columns: id, journal_entry_id, account_id, debit, credit, created_at, updated_at
Table: expenses
  └─ Columns: id, store_id, account_id, amount, description, created_at, updated_at, category_id
Table: store_settings
  └─ Columns: id, store_id, key, value, created_at, updated_at
Table: currency_rates
  └─ Columns: id, currency_code, rate, effective_date, created_at, updated_at
Table: daily_sales_summaries
  └─ Columns: id, store_id, report_date, total_sales, total_gross_profit, total_transactions, created_at, updated_at
Table: product_profitability_snapshots
  └─ Columns: id, product_id, snapshot_date, margin_percent, units_sold, created_at, updated_at
Table: parties
  └─ Columns: id, name, phone, type, credit_limit, current_balance, account_id, created_at, updated_at
Table: party_payments
  └─ Columns: id, party_id, amount, payment_type, payment_method, notes, created_at, updated_at
Table: settings
  └─ Columns: key, value
Table: system_notifications
  └─ Columns: id, type, title, message, severity, is_read, created_at, updated_at
Table: payment_transactions
  └─ Columns: id, order_id, user_id, gateway, amount, currency, status, reference_no, gateway_transaction_id, qr_code_data, payload, created_at, updated_at
Table: expense_categories
  └─ Columns: id, name, created_at, updated_at

--- [ 3. HTTP CONTROLLERS ] ---
Controller: Admin\AccessControlController.php
Controller: Admin\AdvancedFeaturesController.php
Controller: Admin\DashboardController.php
Controller: Admin\DebtController.php
Controller: Admin\InventoryController.php
Controller: Admin\NotificationController.php
Controller: Admin\PosOrderController.php
Controller: Admin\ReportController.php
Controller: Admin\ShiftController.php
Controller: Admin\SmartPaymentController.php
Controller: Api\V1\Finance\FinanceController.php
Controller: Api\V1\Inventory\InventoryController.php
Controller: Api\V1\POS\POSController.php
Controller: Api\V1\Purchases\PurchasesController.php
Controller: Auth\AuthController.php
Controller: Controller.php

--- [ 4. ROUTES SUMMARY ] ---
Web Routes: 30 | API Routes: 11 | Other: 3
