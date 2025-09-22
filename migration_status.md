# Migration Status vs. database_schema.md

This file compares the tables defined in `database_schema.md` with the actual migration files found in `database/migrations`.

## Schema Tables and Corresponding Migrations

### `activity_log`
- [x] 2023_01_01_000033_create_activity_log_table.php

### `activity_logs`
- [x] 2023_01_01_000034_create_activity_logs_table.php

### `audit_logs`
- [x] 2023_01_01_000035_create_audit_logs_table.php

### `banners`
- [x] 2023_01_01_000026_create_banners_table.php

### `cart_items`
- [x] 2023_01_01_000028_create_cart_items_table.php

### `carts`
- [x] 2023_01_01_000027_create_carts_table.php

### `category_parameters`
- [x] 2023_01_01_000025_create_category_parameters_table.php

### `company_profiles`
- [x] 2023_01_01_000009_create_company_profiles_table.php

### `customers`
- [x] 2023_01_01_000022_create_customers_table.php

### `e_invoices`
- [x] 2023_01_01_000057_create_e_invoices_table.php

### `failed_jobs`
- [x] 2023_01_01_000008_create_failed_jobs_table.php

### `media`
- [x] 2023_01_01_000024_create_media_table.php

### `message_templates`
- [x] 2023_01_01_000029_create_message_templates_table.php

### `migrations`
- [ ] No matching migration found (Managed by Laravel internally).

### `model_has_permissions`
- [x] 2023_01_01_000004_create_model_has_permissions_table.php

### `model_has_roles`
- [x] 2023_01_01_000005_create_model_has_roles_table.php

### `notification_logs`
- [x] 2023_01_01_000032_create_notification_logs_table.php

### `notification_receivers`
- [x] 2023_01_01_000031_create_notification_receivers_table.php

### `notification_settings`
- [x] 2023_01_01_000030_create_notification_settings_table.php

### `password_reset_tokens`
- [x] 2023_01_01_000036_create_password_reset_tokens_table.php

### `permissions`
- [x] 2023_01_01_000002_create_permissions_table.php

### `personal_access_tokens`
- [ ] No matching migration found (Likely uses Laravel default migration).

### `product_categories`
- [x] 2023_01_01_000012_create_product_categories_table.php

### `product_template_links`
- [x] 2023_01_01_000041_create_product_template_links_table.php

### `product_template_product`
- [x] 2023_01_01_000042_create_product_template_product_table.php

### `product_templates`
- [x] 2023_01_01_000040_create_product_templates_table.php

### `products`
- [x] 2023_01_01_000062_create_products_table.php

### `promotions`
- [x] 2023_01_01_000039_create_promotions_table.php

### `purchase_items`
- [x] 2023_01_01_000047_create_purchase_items_table.php

### `purchase_supplier_items`
- [x] 2023_01_01_000048_create_purchase_supplier_items_table.php

### `purchases`
- [x] 2023_01_01_000046_create_purchases_table.php

### `quality_inspection_items`
- [x] 2023_01_01_000050_create_quality_inspection_items_table.php

### `quality_inspections`
- [x] 2023_01_01_000049_create_quality_inspections_table.php

### `return_items`
- [x] 2023_01_01_000056_create_return_items_table.php

### `return_requests`
- [x] 2023_01_01_000055_create_return_requests_table.php

### `role_has_permissions`
- [x] 2023_01_01_000006_create_role_has_permissions_table.php

### `roles`
- [x] 2023_01_01_000003_create_roles_table.php

### `sales`
- [x] 2023_01_01_000053_create_sales_table.php

### `sales_items`
- [x] 2023_01_01_000054_create_sales_items_table.php

### `sales_order_items`
- [x] 2023_01_01_000052_create_sales_order_items_table.php

### `sales_orders`
- [x] 2023_01_01_000051_create_sales_orders_table.php

### `settings`
- [x] 2023_01_01_000007_create_settings_table.php

### `stock_movements`
- [x] 2023_01_01_000059_create_stock_movements_table.php

### `stock_transfer_items`
- [x] 2023_01_01_000061_create_stock_transfer_items_table.php

### `stock_transfers`
- [x] 2023_01_01_000060_create_stock_transfers_table.php

### `stocks`
- [x] 2023_01_01_000058_create_stocks_table.php

### `supplier_contacts`
- [x] 2023_01_01_000043_create_supplier_contacts_table.php

### `supplier_price_agreements`
- [x] 2023_01_01_000045_create_supplier_price_agreements_table.php

### `supplier_products`
- [x] 2023_01_01_000044_create_supplier_products_table.php

### `suppliers`
- [x] 2023_01_01_000011_create_suppliers_table.php

### `user_profiles`
- [x] 2023_01_01_000023_create_user_profiles_table.php

### `users`
- [x] 2023_01_01_000001_create_users_table.php

### `verification_codes`
- [x] 2023_01_01_000038_create_verification_codes_table.php

### `warehouses`
- [x] 2023_01_01_000010_create_warehouses_table.php

---

## Schema Tables Without Corresponding Migrations

- `migrations` (Managed by Laravel internally)
- `personal_access_tokens` (Uses Laravel default migration, the custom one `...000037...` was deleted)

---

## Existing Migration Files Without Corresponding Schema Table

- `2023_01_01_000013_create_brands_table.php` (`brands` table)
- `2023_01_01_000014_create_units_table.php` (`units` table)
- `2023_01_01_000015_create_taxes_table.php` (`taxes` table)
- `2023_01_01_000016_create_categories_table.php` (`categories` table - Note: Schema has `product_categories`)
- `2023_01_01_000011_create_vendors_table.php` (This file appears to still exist alongside `..._create_suppliers_table.php`? Needs verification) 