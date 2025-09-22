## `activity_log`

| 列名 | 类型 | 可空 | 默认值 | Extra | 注释 |
|---|---|---|---|---|---|
| `id` | bigint UNSIGNED | No | *None* | AUTO_INCREMENT | *None* |
| `log_name` | varchar(255) | Yes | `NULL` |  | *None* |
| `description` | text | No | *None* |  | *None* |
| `subject_type` | varchar(255) | Yes | `NULL` |  | *None* |
| `event` | varchar(255) | Yes | `NULL` |  | *None* |
| `subject_id` | bigint UNSIGNED | Yes | `NULL` |  | *None* |
| `causer_type` | varchar(255) | Yes | `NULL` |  | *None* |
| `causer_id` | bigint UNSIGNED | Yes | `NULL` |  | *None* |
| `properties` | longtext | Yes | `NULL` |  | *None* |
| `batch_uuid` | char(36) | Yes | `NULL` |  | *None* |
| `created_at` | timestamp | Yes | `NULL` |  | *None* |
| `updated_at` | timestamp | Yes | `NULL` |  | *None* |

**索引:**

*   `PRIMARY`: (`id`)
*   `subject`: (`subject_type`, `subject_id`)
*   `causer`: (`causer_type`, `causer_id`)
*   `activity_log_log_name_index`: (`log_name`)

**外键:** *无*

---

## `activity_logs`

| 列名 | 类型 | 可空 | 默认值 | Extra | 注释 |
|---|---|---|---|---|---|
| `id` | bigint UNSIGNED | No | *None* | AUTO_INCREMENT | *None* |
| `user_id` | bigint UNSIGNED | No | *None* |  | *None* |
| `subject_type` | varchar(255) | No | *None* |  | *None* |
| `subject_id` | bigint UNSIGNED | No | *None* |  | *None* |
| `event` | varchar(255) | No | *None* |  | *None* |
| `description` | varchar(255) | No | *None* |  | *None* |
| `properties` | longtext | Yes | `NULL` |  | *None* |
| `ip_address` | varchar(45) | Yes | `NULL` |  | *None* |
| `user_agent` | varchar(255) | Yes | `NULL` |  | *None* |
| `created_at` | timestamp | Yes | `NULL` |  | *None* |
| `updated_at` | timestamp | Yes | `NULL` |  | *None* |

**索引:**

*   `PRIMARY`: (`id`)
*   `activity_logs_user_id_foreign`: (`user_id`)
*   `activity_logs_subject_type_subject_id_index`: (`subject_type`, `subject_id`)
*   `activity_logs_event_index`: (`event`)
*   `activity_logs_created_at_index`: (`created_at`)

**外键:**

*   `activity_logs_user_id_foreign`: (`user_id`) -> `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT

---

## `audit_logs`

| 列名 | 类型 | 可空 | 默认值 | Extra | 注释 |
|---|---|---|---|---|---|
| `id` | bigint UNSIGNED | No | *None* | AUTO_INCREMENT | *None* |
| `user_id` | bigint UNSIGNED | Yes | `NULL` |  | *None* |
| `event` | varchar(255) | No | *None* |  | *None* |
| `auditable_type` | varchar(255) | No | *None* |  | *None* |
| `auditable_id` | bigint UNSIGNED | No | *None* |  | *None* |
| `old_values` | text | Yes | `NULL` |  | *None* |
| `new_values` | text | Yes | `NULL` |  | *None* |
| `url` | varchar(255) | Yes | `NULL` |  | *None* |
| `ip_address` | varchar(45) | Yes | `NULL` |  | *None* |
| `user_agent` | varchar(255) | Yes | `NULL` |  | *None* |
| `created_at` | timestamp | Yes | `NULL` |  | *None* |
| `updated_at` | timestamp | Yes | `NULL` |  | *None* |

**索引:**

*   `PRIMARY`: (`id`)
*   `audit_logs_user_id_foreign`: (`user_id`)
*   `audit_logs_auditable_type_auditable_id_index`: (`auditable_type`, `auditable_id`)

**外键:**

*   `audit_logs_user_id_foreign`: (`user_id`) -> `users` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT

---

## `banners`

| 列名 | 类型 | 可空 | 默认值 | Extra | 注释 |
|---|---|---|---|---|---|
| `id` | bigint UNSIGNED | No | *None* | AUTO_INCREMENT | *None* |
| `title` | varchar(255) | Yes | `NULL` |  | *None* |
| `subtitle` | varchar(255) | Yes | `NULL` |  | *None* |
| `button_text` | varchar(255) | Yes | `NULL` |  | *None* |
| `button_link` | varchar(255) | Yes | `NULL` |  | *None* |
| `media_id` | bigint UNSIGNED | Yes | `NULL` |  | *None* |
| `order` | int UNSIGNED | No | `0` |  | *None* |
| `is_active` | tinyint(1) | No | `1` |  | *None* |
| `created_at` | timestamp | Yes | `NULL` |  | *None* |
| `updated_at` | timestamp | Yes | `NULL` |  | *None* |
| `deleted_at` | timestamp | Yes | `NULL` |  | *None* |

**索引:**

*   `PRIMARY`: (`id`)
*   `banners_media_id_foreign`: (`media_id`)

**外键:**

*   `banners_media_id_foreign`: (`media_id`) -> `media` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT

---


## `cart_items`

| 列名 | 类型 | 可空 | 默认值 | Extra | 注释 |
|---|---|---|---|---|---|
| `id` | bigint UNSIGNED | No | *None* | AUTO_INCREMENT | *None* |
| `cart_id` | bigint UNSIGNED | No | *None* |  | *None* |
| `product_id` | bigint UNSIGNED | No | *None* |  | *None* |
| `quantity` | int | No | `1` |  | *None* |
| `specifications` | longtext | Yes | `NULL` |  | *None* |
| `created_at` | timestamp | Yes | `NULL` |  | *None* |
| `updated_at` | timestamp | Yes | `NULL` |  | *None* |

**索引:**

*   `PRIMARY`: (`id`)
*   `cart_product`: (`cart_id`, `product_id`) (UNIQUE)
*   `cart_items_product_id_foreign`: (`product_id`)

**外键:**

*   `cart_items_cart_id_foreign`: (`cart_id`) -> `carts` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
*   `cart_items_product_id_foreign`: (`product_id`) -> `products` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT

---

## `carts`

| 列名 | 类型 | 可空 | 默认值 | Extra | 注释 |
|---|---|---|---|---|---|
| `id` | bigint UNSIGNED | No | *None* | AUTO_INCREMENT | *None* |
| `customer_id` | bigint UNSIGNED | No | *None* |  | *None* |
| `name` | varchar(255) | No | `Default Cart` |  | *None* |
| `type` | varchar(255) | No | `cart` |  | *None* |
| `is_default` | tinyint(1) | No | `0` |  | *None* |
| `store_id` | bigint UNSIGNED | Yes | `NULL` |  | *None* |
| `created_at` | timestamp | Yes | `NULL` |  | *None* |
| `updated_at` | timestamp | Yes | `NULL` |  | *None* |

**索引:**

*   `PRIMARY`: (`id`)
*   `carts_customer_id_index`: (`customer_id`)
*   `carts_store_id_foreign`: (`store_id`)
*   `customer_default_cart_unique`: (`customer_id`, `is_default`) (UNIQUE)
*   `carts_type_index`: (`type`)

**外键:**

*   `carts_customer_id_foreign`: (`customer_id`) -> `customers` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
*   `carts_store_id_foreign`: (`store_id`) -> `warehouses` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT

---

## `category_parameters`

| 列名 | 类型 | 可空 | 默认值 | Extra | 注释 |
|---|---|---|---|---|---|
| `id` | bigint UNSIGNED | No | *None* | AUTO_INCREMENT | *None* |
| `category_id` | bigint UNSIGNED | No | *None* |  | *None* |
| `name` | varchar(255) | No | *None* |  | Parameter name |
| `code` | varchar(255) | No | *None* |  | Parameter code |
| `type` | varchar(255) | No | *None* |  | Parameter type:text/number/select/radio/checkbox |
| `is_required` | tinyint(1) | No | `0` |  | Is it required |
| `options` | longtext | Yes | `NULL` |  | Options (forselect/radio/checkboxtype) |
| `validation_rules` | varchar(255) | Yes | `NULL` |  | Verification rules |
| `is_searchable` | tinyint(1) | No | `0` |  | Is it searchable |
| `is_active` | tinyint(1) | No | `1` |  | *None* |
| `sort_order` | int | No | `0` |  | Sort |
| `description` | text | Yes | `NULL` |  | Parameter description |
| `created_at` | timestamp | Yes | `NULL` |  | *None* |
| `updated_at` | timestamp | Yes | `NULL` |  | *None* |
| `deleted_at` | timestamp | Yes | `NULL` |  | *None* |

**索引:**

*   `PRIMARY`: (`id`)
*   `category_parameters_category_id_code_unique`: (`category_id`, `code`) (UNIQUE)

**外键:**

*   `category_parameters_category_id_foreign`: (`category_id`) -> `product_categories` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT

---

## `company_profiles`

| 列名 | 类型 | 可空 | 默认值 | Extra | 注释 |
|---|---|---|---|---|---|
| `id` | bigint UNSIGNED | No | *None* | AUTO_INCREMENT | *None* |
| `company_name` | varchar(255) | No | *None* |  | *None* |
| `description` | text | Yes | `NULL` |  | *None* |
| `address` | text | Yes | `NULL` |  | *None* |
| `city` | varchar(255) | Yes | `NULL` |  | *None* |
| `state` | varchar(255) | Yes | `NULL` |  | *None* |
| `postal_code` | varchar(255) | Yes | `NULL` |  | *None* |
| `country` | varchar(255) | Yes | `NULL` |  | *None* |
| `phone` | varchar(50) | Yes | `NULL` |  | *None* |
| `email` | varchar(255) | Yes | `NULL` |  | *None* |
| `registration_number` | varchar(100) | Yes | `NULL` |  | *None* |
| `tax_number` | varchar(100) | Yes | `NULL` |  | *None* |
| `website` | varchar(255) | Yes | `NULL` |  | *None* |
| `bank_name` | varchar(255) | Yes | `NULL` |  | *None* |
| `bank_account` | varchar(50) | Yes | `NULL` |  | *None* |
| `bank_holder` | varchar(255) | Yes | `NULL` |  | *None* |
| `logo_path` | varchar(255) | Yes | `NULL` |  | *None* |
| `business_hours` | varchar(255) | Yes | `NULL` |  | *None* |
| `facebook_url` | varchar(255) | Yes | `NULL` |  | *None* |
| `instagram_url` | varchar(255) | Yes | `NULL` |  | *None* |
| `currency` | varchar(10) | No | `MYR` |  | *None* |
| `timezone` | varchar(255) | No | `Asia/Kuala_Lumpur` |  | *None* |
| `created_at` | timestamp | Yes | `NULL` |  | *None* |
| `updated_at` | timestamp | Yes | `NULL` |  | *None* |

**索引:**

*   `PRIMARY`: (`id`)

**外键:** *无*

---

## `customers`

| 列名 | 类型 | 可空 | 默认值 | Extra | 注释 |
|---|---|---|---|---|---|
| `id` | bigint UNSIGNED | No | *None* | AUTO_INCREMENT | *None* |
| `ic_number` | varchar(255) | Yes | `NULL` |  | *None* |
| `name` | varchar(255) | No | *None* |  | *None* |
| `birthday` | date | Yes | `NULL` |  | *None* |
| `contact_number` | varchar(255) | Yes | `NULL` |  | *None* |
| `email` | varchar(255) | Yes | `NULL` |  | *None* |
| `customer_password` | varchar(255) | Yes | `NULL` |  | *None* |
| `address` | text | Yes | `NULL` |  | *None* |
| `points` | decimal(10, 2) | No | `0.00` |  | *None* |
| `last_login_ip` | varchar(45) | Yes | `NULL` |  | *None* |
| `remarks` | text | Yes | `NULL` |  | *None* |
| `tags` | longtext | Yes | `NULL` |  | *None* |
| `created_at` | timestamp | Yes | `NULL` |  | *None* |
| `updated_at` | timestamp | Yes | `NULL` |  | *None* |
| `deleted_at` | timestamp | Yes | `NULL` |  | *None* |
| `last_visit_at` | timestamp | Yes | `NULL` |  | *None* |
| `member_level` | enum('normal','silver','gold','platinum') | No | `normal` |  | *None* |
| `store_id` | bigint UNSIGNED | Yes | `NULL` |  | *None* |

**索引:**

*   `PRIMARY`: (`id`)
*   `customers_ic_number_unique`: (`ic_number`) (UNIQUE)
*   `customers_store_id_foreign`: (`store_id`)

**外键:**

*   `customers_store_id_foreign`: (`store_id`) -> `warehouses` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT

---

## `e_invoices`

| 列名 | 类型 | 可空 | 默认值 | Extra | 注释 |
|---|---|---|---|---|---|
| `id` | bigint UNSIGNED | No | *None* | AUTO_INCREMENT | *None* |
| `invoice_number` | varchar(255) | No | *None* |  | *None* |
| `sales_order_id` | bigint UNSIGNED | No | *None* |  | *None* |
| `customer_id` | bigint UNSIGNED | No | *None* |  | *None* |
| `store_id` | bigint UNSIGNED | No | *None* |  | *None* |
| `user_id` | bigint UNSIGNED | No | *None* |  | *None* |
| `invoice_date` | date | No | *None* |  | *None* |
| `due_date` | date | Yes | `NULL` |  | *None* |
| `subtotal` | decimal(10, 2) | No | *None* |  | *None* |
| `tax_amount` | decimal(10, 2) | No | *None* |  | *None* |
| `total_amount` | decimal(10, 2) | No | *None* |  | *None* |
| `status` | varchar(255) | No | `draft` |  | *None* |
| `submission_id` | varchar(255) | Yes | `NULL` |  | *None* |
| `qr_code` | varchar(255) | Yes | `NULL` |  | *None* |
| `pdf_path` | varchar(255) | Yes | `NULL` |  | *None* |
| `xml_data` | longtext | Yes | `NULL` |  | *None* |
| `response_data` | longtext | Yes | `NULL` |  | *None* |
| `error_message` | text | Yes | `NULL` |  | *None* |
| `submission_count` | int | No | `0` |  | *None* |
| `last_submitted_at` | timestamp | Yes | `NULL` |  | *None* |
| `terms_and_conditions` | text | Yes | `NULL` |  | *None* |
| `notes` | text | Yes | `NULL` |  | *None* |
| `currency` | varchar(255) | No | `MYR` |  | *None* |
| `created_at` | timestamp | Yes | `NULL` |  | *None* |
| `updated_at` | timestamp | Yes | `NULL` |  | *None* |
| `deleted_at` | timestamp | Yes | `NULL` |  | *None* |

**索引:**

*   `PRIMARY`: (`id`)
*   `e_invoices_invoice_number_unique`: (`invoice_number`) (UNIQUE)
*   `e_invoices_sales_order_id_foreign`: (`sales_order_id`)
*   `e_invoices_customer_id_foreign`: (`customer_id`)
*   `e_invoices_store_id_foreign`: (`store_id`)
*   `e_invoices_user_id_foreign`: (`user_id`)

**外键:**

*   `e_invoices_customer_id_foreign`: (`customer_id`) -> `customers` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
*   `e_invoices_sales_order_id_foreign`: (`sales_order_id`) -> `sales_orders` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
*   `e_invoices_store_id_foreign`: (`store_id`) -> `warehouses` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
*   `e_invoices_user_id_foreign`: (`user_id`) -> `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT

---

## `failed_jobs`

| 列名 | 类型 | 可空 | 默认值 | Extra | 注释 |
|---|---|---|---|---|---|
| `id` | bigint UNSIGNED | No | *None* | AUTO_INCREMENT | *None* |
| `uuid` | varchar(255) | No | *None* |  | *None* |
| `connection` | text | No | *None* |  | *None* |
| `queue` | text | No | *None* |  | *None* |
| `payload` | longtext | No | *None* |  | *None* |
| `exception` | longtext | No | *None* |  | *None* |
| `failed_at` | timestamp | No | `current_timestamp` |  | *None* |

**索引:**

*   `PRIMARY`: (`id`)
*   `failed_jobs_uuid_unique`: (`uuid`) (UNIQUE)

**外键:** *无*

---

## `media`

| 列名 | 类型 | 可空 | 默认值 | Extra | 注释 |
|---|---|---|---|---|---|
| `id` | bigint UNSIGNED | No | *None* | AUTO_INCREMENT | *None* |
| `temp_id` | varchar(255) | Yes | `NULL` |  | *None* |
| `model_type` | varchar(255) | No | *None* |  | *None* |
| `model_id` | bigint UNSIGNED | Yes | `NULL` |  | *None* |
| `collection_name` | varchar(255) | No | *None* |  | *None* |
| `name` | varchar(255) | No | *None* |  | *None* |
| `file_name` | varchar(255) | No | *None* |  | *None* |
| `mime_type` | varchar(255) | No | *None* |  | *None* |
| `disk` | varchar(255) | No | *None* |  | *None* |
| `path` | varchar(255) | No | *None* |  | *None* |
| `size` | bigint UNSIGNED | No | *None* |  | *None* |
| `custom_properties` | longtext | Yes | `NULL` |  | *None* |
| `created_at` | timestamp | Yes | `NULL` |  | *None* |
| `updated_at` | timestamp | Yes | `NULL` |  | *None* |

**索引:**

*   `PRIMARY`: (`id`)
*   `media_model_type_model_id_index`: (`model_type`, `model_id`)
*   `media_temp_id_model_type_index`: (`temp_id`, `model_type`)
*   `media_temp_id_index`: (`temp_id`)
*   `media_model_type_index`: (`model_type`)
*   `media_model_id_index`: (`model_id`)

**外键:** *无*

---

## `message_templates`

| 列名 | 类型 | 可空 | 默认值 | Extra | 注释 |
|---|---|---|---|---|---|
| `id` | bigint UNSIGNED | No | *None* | AUTO_INCREMENT | *None* |
| `name` | varchar(255) | No | *None* |  | *None* |
| `description` | varchar(255) | No | *None* |  | *None* |
| `channel` | varchar(255) | No | *None* |  | *None* |
| `type` | varchar(255) | Yes | `NULL` |  | *None* |
| `supplier_id` | bigint UNSIGNED | Yes | `NULL` |  | *None* |
| `subject` | varchar(255) | Yes | `NULL` |  | *None* |
| `content` | text | No | *None* |  | *None* |
| `status` | enum('active','inactive') | No | `active` |  | *None* |
| `is_default` | tinyint(1) | No | `0` |  | *None* |
| `created_at` | timestamp | Yes | `NULL` |  | *None* |
| `updated_at` | timestamp | Yes | `NULL` |  | *None* |

**索引:**

*   `PRIMARY`: (`id`)
*   `message_templates_name_channel_unique`: (`name`, `channel`) (UNIQUE)
*   `message_templates_channel_type_index`: (`channel`, `type`)
*   `message_templates_status_index`: (`status`)
*   `message_templates_supplier_id_foreign`: (`supplier_id`)
*   `message_templates_name_supplier_id_index`: (`name`, `supplier_id`)
*   `message_templates_is_default_name_channel_index`: (`is_default`, `name`, `channel`)

**外键:**

*   `message_templates_supplier_id_foreign`: (`supplier_id`) -> `suppliers` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT

---

## `migrations`

| 列名 | 类型 | 可空 | 默认值 | Extra | 注释 |
|---|---|---|---|---|---|
| `id` | int UNSIGNED | No | *None* | AUTO_INCREMENT | *None* |
| `migration` | varchar(255) | No | *None* |  | *None* |
| `batch` | int | No | *None* |  | *None* |

**索引:**

*   `PRIMARY`: (`id`)

**外键:** *无*

---

## `model_has_permissions`

| 列名 | 类型 | 可空 | 默认值 | Extra | 注释 |
|---|---|---|---|---|---|
| `permission_id` | bigint UNSIGNED | No | *None* |  | *None* |
| `model_type` | varchar(255) | No | *None* |  | *None* |
| `model_id` | bigint UNSIGNED | No | *None* |  | *None* |

**索引:**

*   `PRIMARY`: (`permission_id`, `model_id`, `model_type`)

**外键:**

*   `model_has_permissions_permission_id_foreign`: (`permission_id`) -> `permissions` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT

---

## `model_has_roles`

| 列名 | 类型 | 可空 | 默认值 | Extra | 注释 |
|---|---|---|---|---|---|
| `role_id` | bigint UNSIGNED | No | *None* |  | *None* |
| `model_type` | varchar(255) | No | *None* |  | *None* |
| `model_id` | bigint UNSIGNED | No | *None* |  | *None* |

**索引:**

*   `PRIMARY`: (`role_id`, `model_id`, `model_type`)

**外键:**

*   `model_has_roles_role_id_foreign`: (`role_id`) -> `roles` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT

---

## `notification_logs`

| 列名 | 类型 | 可空 | 默认值 | Extra | 注释 |
|---|---|---|---|---|---|
| `id` | bigint UNSIGNED | No | *None* | AUTO_INCREMENT | *None* |
| `type` | varchar(255) | No | *None* |  | 通知类型 |
| `recipient` | varchar(255) | No | *None* |  | 接收人邮箱 |
| `user_id` | bigint UNSIGNED | Yes | `NULL` |  | 关联的用户ID |
| `subject` | varchar(255) | Yes | `NULL` |  | 通知主题 |
| `content` | text | Yes | `NULL` |  | 通知内容 |
| `status` | varchar(255) | No | `pending` |  | 发送状态：pending, sent, failed |
| `message_template_id` | bigint UNSIGNED | Yes | `NULL` |  | 关联的消息模板ID |
| `error` | text | Yes | `NULL` |  | 错误信息 |
| `sent_at` | timestamp | Yes | `NULL` |  | 发送时间 |
| `created_at` | timestamp | Yes | `NULL` |  | *None* |
| `updated_at` | timestamp | Yes | `NULL` |  | *None* |

**索引:**

*   `PRIMARY`: (`id`)
*   `notification_logs_type_index`: (`type`)
*   `notification_logs_recipient_index`: (`recipient`)
*   `notification_logs_message_template_id_foreign`: (`message_template_id`)
*   `notification_logs_user_id_foreign`: (`user_id`)

**外键:**

*   `notification_logs_message_template_id_foreign`: (`message_template_id`) -> `message_templates` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT
*   `notification_logs_user_id_foreign`: (`user_id`) -> `users` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT

---

## `notification_receivers`

| 列名 | 类型 | 可空 | 默认值 | Extra | 注释 |
|---|---|---|---|---|---|
| `id` | bigint UNSIGNED | No | *None* | AUTO_INCREMENT | *None* |
| `notification_setting_id` | bigint UNSIGNED | No | *None* |  | *None* |
| `email` | varchar(255) | No | *None* |  | Email address of the notification receiver |
| `created_at` | timestamp | Yes | `NULL` |  | *None* |
| `updated_at` | timestamp | Yes | `NULL` |  | *None* |

**索引:**

*   `PRIMARY`: (`id`)
*   `notification_receivers_notification_setting_id_email_unique`: (`notification_setting_id`, `email`) (UNIQUE)

**外键:**

*   `notification_receivers_notification_setting_id_foreign`: (`notification_setting_id`) -> `notification_settings` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT

---

## `notification_settings`

| 列名 | 类型 | 可空 | 默认值 | Extra | 注释 |
|---|---|---|---|---|---|
| `id` | bigint UNSIGNED | No | *None* | AUTO_INCREMENT | *None* |
| `type` | varchar(50) | No | *None* |  | Notification type (e.g. new_product, homepage_updated) |
| `created_at` | timestamp | Yes | `NULL` |  | *None* |
| `updated_at` | timestamp | Yes | `NULL` |  | *None* |

**索引:**

*   `PRIMARY`: (`id`)
*   `notification_settings_type_unique`: (`type`) (UNIQUE)

**外键:** *无*

---

## `password_reset_tokens`

| 列名 | 类型 | 可空 | 默认值 | Extra | 注释 |
|---|---|---|---|---|---|
| `email` | varchar(255) | No | *None* |  | *None* |
| `token` | varchar(255) | No | *None* |  | *None* |
| `created_at` | timestamp | Yes | `NULL` |  | *None* |

**索引:**

*   `PRIMARY`: (`email`)

**外键:** *无*

---

## `permissions`

| 列名 | 类型 | 可空 | 默认值 | Extra | 注释 |
|---|---|---|---|---|---|
| `id` | bigint UNSIGNED | No | *None* | AUTO_INCREMENT | *None* |
| `name` | varchar(255) | No | *None* |  | *None* |
| `guard_name` | varchar(255) | No | *None* |  | *None* |
| `description` | varchar(255) | Yes | `NULL` |  | *None* |
| `created_at` | timestamp | Yes | `NULL` |  | *None* |
| `updated_at` | timestamp | Yes | `NULL` |  | *None* |

**索引:**

*   `PRIMARY`: (`id`)
*   `permissions_name_guard_name_unique`: (`name`, `guard_name`) (UNIQUE)

**外键:** *无*

---

## `personal_access_tokens`

| 列名 | 类型 | 可空 | 默认值 | Extra | 注释 |
|---|---|---|---|---|---|
| `id` | bigint UNSIGNED | No | *None* | AUTO_INCREMENT | *None* |
| `tokenable_type` | varchar(255) | No | *None* |  | *None* |
| `tokenable_id` | bigint UNSIGNED | No | *None* |  | *None* |
| `name` | varchar(255) | No | *None* |  | *None* |
| `token` | varchar(64) | No | *None* |  | *None* |
| `abilities` | text | Yes | `NULL` |  | *None* |
| `last_used_at` | timestamp | Yes | `NULL` |  | *None* |
| `expires_at` | timestamp | Yes | `NULL` |  | *None* |
| `created_at` | timestamp | Yes | `NULL` |  | *None* |
| `updated_at` | timestamp | Yes | `NULL` |  | *None* |

**索引:**

*   `PRIMARY`: (`id`)
*   `personal_access_tokens_token_unique`: (`token`) (UNIQUE)
*   `personal_access_tokens_tokenable_type_tokenable_id_index`: (`tokenable_type`, `tokenable_id`)

**外键:** *无*

---

## `product_categories`

| 列名 | 类型 | 可空 | 默认值 | Extra | 注释 |
|---|---|---|---|---|---|
| `id` | bigint UNSIGNED | No | *None* | AUTO_INCREMENT | *None* |
| `name` | varchar(255) | No | *None* |  | *None* |
| `code` | varchar(255) | No | *None* |  | *None* |
| `description` | text | Yes | `NULL` |  | *None* |
| `image` | varchar(255) | Yes | `NULL` |  | *None* |
| `is_active` | tinyint(1) | No | `1` |  | *None* |
| `created_at` | timestamp | Yes | `NULL` |  | *None* |
| `updated_at` | timestamp | Yes | `NULL` |  | *None* |
| `deleted_at` | timestamp | Yes | `NULL` |  | *None* |

**索引:**

*   `PRIMARY`: (`id`)
*   `product_categories_code_unique`: (`code`) (UNIQUE)

**外键:** *无*


## `product_template_links`

| 列名 | 类型 | 可空 | 默认值 | Extra | 注释 |
|---|---|---|---|---|---|
| `id` | bigint UNSIGNED | No | *None* | AUTO_INCREMENT | *None* |
| `created_at` | timestamp | Yes | `NULL` |  | *None* |
| `updated_at` | timestamp | Yes | `NULL` |  | *None* |

**索引:**

*   `PRIMARY`: (`id`)

**外键:** *无*

---

## `product_template_product`

| 列名 | 类型 | 可空 | 默认值 | Extra | 注释 |
|---|---|---|---|---|---|
| `id` | bigint UNSIGNED | No | *None* | AUTO_INCREMENT | *None* |
| `product_template_id` | bigint UNSIGNED | No | *None* |  | *None* |
| `product_id` | bigint UNSIGNED | No | *None* |  | *None* |
| `parameter_group` | varchar(255) | No | *None* |  | *None* |
| `created_at` | timestamp | Yes | `NULL` |  | *None* |
| `updated_at` | timestamp | Yes | `NULL` |  | *None* |

**索引:**

*   `PRIMARY`: (`id`)
*   `pt_param_group_unique`: (`product_template_id`, `parameter_group`) (UNIQUE)
*   `product_template_product_product_id_foreign`: (`product_id`)

**外键:**

*   `product_template_product_product_id_foreign`: (`product_id`) -> `products` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
*   `product_template_product_product_template_id_foreign`: (`product_template_id`) -> `product_templates` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT

---

## `product_templates`

| 列名 | 类型 | 可空 | 默认值 | Extra | 注释 |
|---|---|---|---|---|---|
| `id` | bigint UNSIGNED | No | *None* | AUTO_INCREMENT | *None* |
| `name` | varchar(255) | No | *None* |  | *None* |
| `description` | text | Yes | `NULL` |  | *None* |
| `parameter_options` | longtext | Yes | `NULL` |  | *None* |
| `images` | longtext | Yes | `NULL` |  | *None* |
| `parameters` | longtext | Yes | `NULL` |  | *None* |
| `is_active` | tinyint(1) | No | `1` |  | *None* |
| `is_featured` | tinyint(1) | No | `0` |  | *None* |
| `is_new_arrival` | tinyint(1) | No | `0` |  | *None* |
| `is_sale` | tinyint(1) | No | `0` |  | *None* |
| `promo_page_url` | varchar(255) | Yes | `NULL` |  | *None* |
| `template_gallery` | longtext | Yes | `NULL` |  | *None* |
| `category_id` | bigint UNSIGNED | Yes | `NULL` |  | *None* |
| `store_id` | bigint UNSIGNED | Yes | `NULL` |  | *None* |
| `created_at` | timestamp | Yes | `NULL` |  | *None* |
| `updated_at` | timestamp | Yes | `NULL` |  | *None* |
| `deleted_at` | timestamp | Yes | `NULL` |  | *None* |

**索引:**

*   `PRIMARY`: (`id`)
*   `product_templates_name_index`: (`name`)
*   `product_templates_category_id_index`: (`category_id`)
*   `product_templates_is_active_index`: (`is_active`)
*   `product_templates_store_id_foreign`: (`store_id`)

**外键:**

*   `product_templates_category_id_foreign`: (`category_id`) -> `product_categories` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT
*   `product_templates_store_id_foreign`: (`store_id`) -> `warehouses` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT

---

## `products`

| 列名 | 类型 | 可空 | 默认值 | Extra | 注释 |
|---|---|---|---|---|---|
| `id` | bigint UNSIGNED | No | *None* | AUTO_INCREMENT | *None* |
| `category_id` | bigint UNSIGNED | No | *None* |  | *None* |
| `name` | varchar(255) | No | *None* |  | *None* |
| `sku` | varchar(255) | No | *None* |  | *None* |
| `barcode` | varchar(255) | Yes | `NULL` |  | *None* |
| `brand` | varchar(255) | Yes | `NULL` |  | *None* |
| `model` | varchar(255) | Yes | `NULL` |  | *None* |
| `cost_price` | decimal(10, 2) | Yes | `NULL` |  | *None* |
| `selling_price` | decimal(10, 2) | No | *None* |  | *None* |
| `min_stock` | int | No | `0` |  | *None* |
| `inventory_count` | int | No | `0` |  | *None* |
| `description` | text | Yes | `NULL` |  | *None* |
| `images` | longtext | Yes | `NULL` |  | *None* |
| `parameters` | longtext | Yes | `NULL` |  | *None* |
| `is_active` | tinyint(1) | No | `1` |  | *None* |
| `created_at` | timestamp | Yes | `NULL` |  | *None* |
| `updated_at` | timestamp | Yes | `NULL` |  | *None* |
| `deleted_at` | timestamp | Yes | `NULL` |  | *None* |
| `featured_image_index` | int | No | `0` |  | 首页展示时使用的图片索引 |
| `is_new_arrival` | tinyint(1) | No | `0` |  | 是否手动设为新品 |
| `new_arrival_order` | int | Yes | `NULL` |  | *None* |
| `new_until_date` | date | Yes | `NULL` |  | *None* |
| `show_in_new_arrivals` | tinyint(1) | No | `1` |  | 是否在新品区域显示 |
| `new_arrival_image_index` | int | No | `0` |  | *None* |
| `discount_percentage` | decimal(5, 2) | No | `0.00` |  | 折扣百分比，如10表示打9折 |
| `template_id` | bigint UNSIGNED | Yes | `NULL` |  | *None* |
| `variant_options` | longtext | Yes | `NULL` |  | *None* |
| `price_adjustment` | decimal(10, 2) | No | `0.00` |  | *None* |
| `discount_start_date` | timestamp | Yes | `NULL` |  | 折扣开始日期 |
| `discount_end_date` | timestamp | Yes | `NULL` |  | 折扣结束日期 |
| `min_quantity_for_discount` | int | No | `0` |  | 享受折扣的最小数量 |
| `max_quantity_for_discount` | int | Yes | `NULL` |  | 享受折扣的最大数量 |
| `show_in_sale` | tinyint(1) | No | `0` |  | *None* |
| `is_sale` | tinyint(1) | No | `0` |  | 是否在促销区域显示 |
| `sale_image_index` | int | No | `0` |  | 促销区域展示时使用的图片索引 |
| `additional_images` | text | Yes | `NULL` |  | *None* |
| `default_image_index` | int UNSIGNED | Yes | `0` |  | *None* |
| `new_image_index` | int | No | `0` |  | 新品页面展示时使用的图片索引 |
| `is_featured` | tinyint(1) | No | `0` |  | 是否为特色产品 |
| `is_new` | tinyint(1) | No | `0` |  | 是否为新品 |
| `new_order` | int | Yes | `NULL` |  | 新品展示顺序 |
| `featured_order` | int | Yes | `NULL` |  | 特色产品展示顺序 |
| `sale_order` | int | Yes | `NULL` |  | 促销产品展示顺序 |
| `sale_until_date` | timestamp | Yes | `NULL` |  | *None* |

**索引:**

*   `PRIMARY`: (`id`)
*   `products_sku_unique`: (`sku`) (UNIQUE)
*   `products_barcode_unique`: (`barcode`) (UNIQUE)
*   `products_category_id_foreign`: (`category_id`)
*   `products_template_id_foreign`: (`template_id`)

**外键:**

*   `products_category_id_foreign`: (`category_id`) -> `product_categories` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
*   `products_template_id_foreign`: (`template_id`) -> `product_templates` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT

---

## `promotions`

| 列名 | 类型 | 可空 | 默认值 | Extra | 注释 |
|---|---|---|---|---|---|
| `id` | bigint UNSIGNED | No | *None* | AUTO_INCREMENT | *None* |
| `title` | varchar(255) | No | *None* |  | 活动标题 |
| `subtitle` | text | Yes | `NULL` |  | 活动副标题 |
| `description` | text | Yes | `NULL` |  | 活动描述 |
| `button_text` | varchar(50) | No | `查看详情` |  | 按钮文本 |
| `button_link` | varchar(255) | No | `#` |  | 按钮链接 |
| `background_color` | varchar(20) | No | `#FFFFFF` |  | 背景颜色 |
| `text_color` | varchar(20) | No | `#000000` |  | 文本颜色 |
| `start_date` | timestamp | Yes | `NULL` |  | 开始日期 |
| `end_date` | timestamp | Yes | `NULL` |  | 结束日期 |
| `is_active` | tinyint(1) | No | `1` |  | 是否激活 |
| `priority` | int | No | `0` |  | 显示优先级 |
| `image` | varchar(255) | Yes | `NULL` |  | 活动图片 |
| `created_at` | timestamp | Yes | `NULL` |  | *None* |
| `updated_at` | timestamp | Yes | `NULL` |  | *None* |

**索引:**

*   `PRIMARY`: (`id`)

**外键:** *无*

---

## `purchase_items`

| 列名 | 类型 | 可空 | 默认值 | Extra | 注释 |
|---|---|---|---|---|---|
| `id` | bigint UNSIGNED | No | *None* | AUTO_INCREMENT | *None* |
| `purchase_id` | bigint UNSIGNED | No | *None* |  | *None* |
| `product_id` | bigint UNSIGNED | No | *None* |  | *None* |
| `supplier_id` | bigint UNSIGNED | No | *None* |  | *None* |
| `quantity` | int | No | *None* |  | *None* |
| `unit_price` | decimal(10, 2) | No | *None* |  | *None* |
| `tax_rate` | decimal(5, 2) | No | `0.00` |  | *None* |
| `tax_amount` | decimal(10, 2) | No | `0.00` |  | *None* |
| `discount_amount` | decimal(10, 2) | No | `0.00` |  | *None* |
| `total_amount` | decimal(10, 2) | No | *None* |  | *None* |
| `received_quantity` | int | No | `0` |  | *None* |
| `expected_delivery_at` | datetime | Yes | `NULL` |  | *None* |
| `lead_time` | int | Yes | `NULL` |  | *None* |
| `notes` | text | Yes | `NULL` |  | *None* |
| `created_at` | timestamp | Yes | `NULL` |  | *None* |
| `updated_at` | timestamp | Yes | `NULL` |  | *None* |
| `deleted_at` | timestamp | Yes | `NULL` |  | *None* |

**索引:**

*   `PRIMARY`: (`id`)
*   `purchase_items_product_id_foreign`: (`product_id`)
*   `purchase_items_purchase_id_product_id_index`: (`purchase_id`, `product_id`)
*   `purchase_items_supplier_id_foreign`: (`supplier_id`)
*   `purchase_items_purchase_id_supplier_id_product_id_index`: (`purchase_id`, `supplier_id`, `product_id`)
*   `purchase_items_expected_delivery_at_index`: (`expected_delivery_at`)

**外键:**

*   `purchase_items_product_id_foreign`: (`product_id`) -> `products` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
*   `purchase_items_purchase_id_foreign`: (`purchase_id`) -> `purchases` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
*   `purchase_items_supplier_id_foreign`: (`supplier_id`) -> `suppliers` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT

---

## `purchase_supplier_items`

| 列名 | 类型 | 可空 | 默认值 | Extra | 注释 |
|---|---|---|---|---|---|
| `id` | bigint UNSIGNED | No | *None* | AUTO_INCREMENT | *None* |
| `purchase_id` | bigint UNSIGNED | No | *None* |  | *None* |
| `supplier_id` | bigint UNSIGNED | No | *None* |  | *None* |
| `total_amount` | decimal(10, 2) | No | `0.00` |  | *None* |
| `tax_amount` | decimal(10, 2) | No | `0.00` |  | *None* |
| `shipping_fee` | decimal(10, 2) | No | `0.00` |  | *None* |
| `final_amount` | decimal(10, 2) | No | `0.00` |  | *None* |
| `email_sent` | tinyint(1) | No | `0` |  | *None* |
| `email_sent_at` | timestamp | Yes | `NULL` |  | *None* |
| `notes` | text | Yes | `NULL` |  | *None* |
| `created_at` | timestamp | Yes | `NULL` |  | *None* |
| `updated_at` | timestamp | Yes | `NULL` |  | *None* |
| `deleted_at` | timestamp | Yes | `NULL` |  | *None* |

**索引:**

*   `PRIMARY`: (`id`)
*   `purchase_supplier_items_supplier_id_foreign`: (`supplier_id`)
*   `purchase_supplier_items_purchase_id_supplier_id_index`: (`purchase_id`, `supplier_id`)

**外键:**

*   `purchase_supplier_items_purchase_id_foreign`: (`purchase_id`) -> `purchases` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
*   `purchase_supplier_items_supplier_id_foreign`: (`supplier_id`) -> `suppliers` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT

---

## `purchases`

| 列名 | 类型 | 可空 | 默认值 | Extra | 注释 |
|---|---|---|---|---|---|
| `id` | bigint UNSIGNED | No | *None* | AUTO_INCREMENT | *None* |
| `purchase_number` | varchar(255) | No | *None* |  | *None* |
| `warehouse_id` | bigint UNSIGNED | No | *None* |  | *None* |
| `purchase_date` | datetime | No | *None* |  | *None* |
| `total_amount` | decimal(10, 2) | No | `0.00` |  | *None* |
| `tax_amount` | decimal(10, 2) | No | `0.00` |  | *None* |
| `shipping_fee` | decimal(10, 2) | No | `0.00` |  | *None* |
| `discount_amount` | decimal(10, 2) | No | `0.00` |  | *None* |
| `adjustment_amount` | decimal(10, 2) | No | `0.00` |  | *None* |
| `final_amount` | decimal(10, 2) | No | `0.00` |  | *None* |
| `payment_terms` | varchar(255) | Yes | `NULL` |  | *None* |
| `payment_method` | varchar(255) | Yes | `NULL` |  | *None* |
| `purchase_status` | varchar(255) | No | `draft` |  | *None* |
| `payment_status` | varchar(255) | No | `unpaid` |  | *None* |
| `inspection_status` | varchar(255) | Yes | `NULL` |  | *None* |
| `notes` | text | Yes | `NULL` |  | *None* |
| `approved_by` | bigint UNSIGNED | Yes | `NULL` |  | *None* |
| `approved_at` | timestamp | Yes | `NULL` |  | *None* |
| `rejected_by` | bigint UNSIGNED | Yes | `NULL` |  | *None* |
| `rejected_at` | timestamp | Yes | `NULL` |  | *None* |
| `received_at` | timestamp | Yes | `NULL` |  | *None* |
| `auto_generated` | tinyint(1) | No | `0` |  | 是否由系统自动生成 |
| `generated_by` | varchar(255) | Yes | `NULL` |  | 生成者（系统或用户ID） |
| `created_at` | timestamp | Yes | `NULL` |  | *None* |
| `updated_at` | timestamp | Yes | `NULL` |  | *None* |
| `deleted_at` | timestamp | Yes | `NULL` |  | *None* |

**索引:**

*   `PRIMARY`: (`id`)
*   `purchases_purchase_number_unique`: (`purchase_number`) (UNIQUE)
*   `purchases_warehouse_id_foreign`: (`warehouse_id`)
*   `purchases_approved_by_foreign`: (`approved_by`)
*   `purchases_rejected_by_foreign`: (`rejected_by`)
*   `purchases_purchase_number_index`: (`purchase_number`)
*   `purchases_purchase_date_index`: (`purchase_date`)
*   `purchases_payment_status_index`: (`payment_status`)
*   `purchases_purchase_status_index`: (`purchase_status`)
*   `purchases_inspection_status_index`: (`inspection_status`)

**外键:**

*   `purchases_approved_by_foreign`: (`approved_by`) -> `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
*   `purchases_rejected_by_foreign`: (`rejected_by`) -> `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
*   `purchases_warehouse_id_foreign`: (`warehouse_id`) -> `warehouses` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT

---

## `quality_inspection_items`

| 列名 | 类型 | 可空 | 默认值 | Extra | 注释 |
|---|---|---|---|---|---|
| `id` | bigint UNSIGNED | No | *None* | AUTO_INCREMENT | *None* |
| `quality_inspection_id` | bigint UNSIGNED | No | *None* |  | *None* |
| `purchase_item_id` | bigint UNSIGNED | No | *None* |  | *None* |
| `inspected_quantity` | decimal(10, 2) | No | *None* |  | *None* |
| `passed_quantity` | decimal(10, 2) | No | *None* |  | *None* |
| `failed_quantity` | decimal(10, 2) | No | *None* |  | *None* |
| `defect_description` | text | Yes | `NULL` |  | *None* |
| `created_at` | timestamp | Yes | `NULL` |  | *None* |
| `updated_at` | timestamp | Yes | `NULL` |  | *None* |
| `deleted_at` | timestamp | Yes | `NULL` |  | *None* |

**索引:**

*   `PRIMARY`: (`id`)
*   `quality_inspection_items_purchase_item_id_foreign`: (`purchase_item_id`)
*   `qi_items_inspection_item_index`: (`quality_inspection_id`, `purchase_item_id`)

**外键:**

*   `quality_inspection_items_purchase_item_id_foreign`: (`purchase_item_id`) -> `purchase_items` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
*   `quality_inspection_items_quality_inspection_id_foreign`: (`quality_inspection_id`) -> `quality_inspections` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT

---

## `quality_inspections`

| 列名 | 类型 | 可空 | 默认值 | Extra | 注释 |
|---|---|---|---|---|---|
| `id` | bigint UNSIGNED | No | *None* | AUTO_INCREMENT | *None* |
| `purchase_id` | bigint UNSIGNED | No | *None* |  | *None* |
| `inspector_id` | bigint UNSIGNED | No | *None* |  | *None* |
| `inspection_number` | varchar(255) | No | *None* |  | *None* |
| `inspection_date` | date | No | *None* |  | *None* |
| `status` | enum('pending','passed','failed') | No | `pending` |  | *None* |
| `is_partial` | tinyint(1) | No | `0` |  | Is it a partial test |
| `remarks` | text | Yes | `NULL` |  | *None* |
| `created_at` | timestamp | Yes | `NULL` |  | *None* |
| `updated_at` | timestamp | Yes | `NULL` |  | *None* |
| `deleted_at` | timestamp | Yes | `NULL` |  | *None* |

**索引:**

*   `PRIMARY`: (`id`)
*   `quality_inspections_inspection_number_unique`: (`inspection_number`) (UNIQUE)
*   `quality_inspections_purchase_id_foreign`: (`purchase_id`)
*   `quality_inspections_inspector_id_foreign`: (`inspector_id`)
*   `quality_inspections_inspection_number_index`: (`inspection_number`)
*   `quality_inspections_inspection_date_index`: (`inspection_date`)
*   `quality_inspections_status_index`: (`status`)

**外键:**

*   `quality_inspections_inspector_id_foreign`: (`inspector_id`) -> `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
*   `quality_inspections_purchase_id_foreign`: (`purchase_id`) -> `purchases` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT

---

## `return_items`

| 列名 | 类型 | 可空 | 默认值 | Extra | 注释 |
|---|---|---|---|---|---|
| `id` | bigint UNSIGNED | No | *None* | AUTO_INCREMENT | *None* |
| `return_request_id` | bigint UNSIGNED | No | *None* |  | *None* |
| `sales_order_item_id` | bigint UNSIGNED | No | *None* |  | *None* |
| `product_id` | bigint UNSIGNED | No | *None* |  | *None* |
| `quantity` | int | No | *None* |  | *None* |
| `price` | decimal(10, 2) | No | *None* |  | *None* |
| `total` | decimal(10, 2) | No | *None* |  | *None* |
| `reason` | varchar(255) | No | *None* |  | *None* |
| `created_at` | timestamp | Yes | `NULL` |  | *None* |
| `updated_at` | timestamp | Yes | `NULL` |  | *None* |

**索引:**

*   `PRIMARY`: (`id`)
*   `return_items_return_request_id_foreign`: (`return_request_id`)
*   `return_items_sales_order_item_id_foreign`: (`sales_order_item_id`)
*   `return_items_product_id_foreign`: (`product_id`)

**外键:**

*   `return_items_product_id_foreign`: (`product_id`) -> `products` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
*   `return_items_return_request_id_foreign`: (`return_request_id`) -> `return_requests` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
*   `return_items_sales_order_item_id_foreign`: (`sales_order_item_id`) -> `sales_order_items` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT

---

## `return_requests`

| 列名 | 类型 | 可空 | 默认值 | Extra | 注释 |
|---|---|---|---|---|---|
| `id` | bigint UNSIGNED | No | *None* | AUTO_INCREMENT | *None* |
| `return_number` | varchar(255) | No | *None* |  | *None* |
| `sales_order_id` | bigint UNSIGNED | No | *None* |  | *None* |
| `customer_id` | bigint UNSIGNED | No | *None* |  | *None* |
| `store_id` | bigint UNSIGNED | No | *None* |  | *None* |
| `user_id` | bigint UNSIGNED | No | *None* |  | *None* |
| `reason` | text | No | *None* |  | *None* |
| `status` | varchar(255) | No | *None* |  | *None* |
| `total_amount` | decimal(10, 2) | No | `0.00` |  | *None* |
| `processed_by` | bigint UNSIGNED | Yes | `NULL` |  | *None* |
| `processed_at` | timestamp | Yes | `NULL` |  | *None* |
| `notes` | text | Yes | `NULL` |  | *None* |
| `created_at` | timestamp | Yes | `NULL` |  | *None* |
| `updated_at` | timestamp | Yes | `NULL` |  | *None* |
| `deleted_at` | timestamp | Yes | `NULL` |  | *None* |

**索引:**

*   `PRIMARY`: (`id`)
*   `return_requests_return_number_unique`: (`return_number`) (UNIQUE)
*   `return_requests_sales_order_id_foreign`: (`sales_order_id`)
*   `return_requests_customer_id_foreign`: (`customer_id`)
*   `return_requests_store_id_foreign`: (`store_id`)
*   `return_requests_user_id_foreign`: (`user_id`)
*   `return_requests_processed_by_foreign`: (`processed_by`)

**外键:**

*   `return_requests_customer_id_foreign`: (`customer_id`) -> `customers` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
*   `return_requests_processed_by_foreign`: (`processed_by`) -> `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
*   `return_requests_sales_order_id_foreign`: (`sales_order_id`) -> `sales_orders` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
*   `return_requests_store_id_foreign`: (`store_id`) -> `warehouses` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
*   `return_requests_user_id_foreign`: (`user_id`) -> `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT

---

## `role_has_permissions`

| 列名 | 类型 | 可空 | 默认值 | Extra | 注释 |
|---|---|---|---|---|---|
| `permission_id` | bigint UNSIGNED | No | *None* |  | *None* |
| `role_id` | bigint UNSIGNED | No | *None* |  | *None* |

**索引:**

*   `PRIMARY`: (`permission_id`, `role_id`)
*   `role_has_permissions_role_id_foreign`: (`role_id`)

**外键:**

*   `role_has_permissions_permission_id_foreign`: (`permission_id`) -> `permissions` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
*   `role_has_permissions_role_id_foreign`: (`role_id`) -> `roles` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT

---

## `roles`

| 列名 | 类型 | 可空 | 默认值 | Extra | 注释 |
|---|---|---|---|---|---|
| `id` | bigint UNSIGNED | No | *None* | AUTO_INCREMENT | *None* |
| `name` | varchar(255) | No | *None* |  | *None* |
| `guard_name` | varchar(255) | No | *None* |  | *None* |
| `description` | varchar(255) | Yes | `NULL` |  | *None* |
| `created_at` | timestamp | Yes | `NULL` |  | *None* |
| `updated_at` | timestamp | Yes | `NULL` |  | *None* |

**索引:**

*   `PRIMARY`: (`id`)
*   `roles_name_guard_name_unique`: (`name`, `guard_name`) (UNIQUE)

**外键:** *无*

---

## `sales`

| 列名 | 类型 | 可空 | 默认值 | Extra | 注释 |
|---|---|---|---|---|---|
| `id` | bigint UNSIGNED | No | *None* | AUTO_INCREMENT | *None* |
| `store_id` | bigint UNSIGNED | Yes | `NULL` |  | *None* |
| `order_number` | varchar(255) | No | *None* |  | *None* |
| `customer_id` | bigint UNSIGNED | No | *None* |  | *None* |
| `user_id` | bigint UNSIGNED | No | *None* |  | *None* |
| `subtotal` | decimal(10, 2) | No | `0.00` |  | *None* |
| `tax_amount` | decimal(10, 2) | No | `0.00` |  | *None* |
| `discount_amount` | decimal(10, 2) | No | `0.00` |  | *None* |
| `total_amount` | decimal(10, 2) | No | `0.00` |  | *None* |
| `status` | varchar(255) | No | *None* |  | *None* |
| `payment_status` | varchar(255) | No | *None* |  | *None* |
| `payment_method` | varchar(255) | Yes | `NULL` |  | *None* |
| `paid_at` | timestamp | Yes | `NULL` |  | *None* |
| `remarks` | text | Yes | `NULL` |  | *None* |
| `additional_data` | longtext | Yes | `NULL` |  | *None* |
| `created_at` | timestamp | Yes | `NULL` |  | *None* |
| `updated_at` | timestamp | Yes | `NULL` |  | *None* |
| `deleted_at` | timestamp | Yes | `NULL` |  | *None* |

**索引:**

*   `PRIMARY`: (`id`)
*   `sales_order_number_unique`: (`order_number`) (UNIQUE)
*   `sales_store_id_foreign`: (`store_id`)
*   `sales_customer_id_foreign`: (`customer_id`)
*   `sales_user_id_foreign`: (`user_id`)

**外键:**

*   `sales_customer_id_foreign`: (`customer_id`) -> `customers` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
*   `sales_store_id_foreign`: (`store_id`) -> `warehouses` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT
*   `sales_user_id_foreign`: (`user_id`) -> `users` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT

---

## `sales_items`

| 列名 | 类型 | 可空 | 默认值 | Extra | 注释 |
|---|---|---|---|---|---|
| `id` | bigint UNSIGNED | No | *None* | AUTO_INCREMENT | *None* |
| `sale_id` | bigint UNSIGNED | No | *None* |  | *None* |
| `product_id` | bigint UNSIGNED | No | *None* |  | *None* |
| `quantity` | decimal(10, 2) | No | *None* |  | *None* |
| `unit_price` | decimal(10, 2) | No | *None* |  | *None* |
| `discount_amount` | decimal(10, 2) | No | `0.00` |  | *None* |
| `tax_amount` | decimal(10, 2) | No | `0.00` |  | *None* |
| `subtotal` | decimal(10, 2) | No | *None* |  | *None* |
| `remarks` | text | Yes | `NULL` |  | *None* |
| `additional_data` | longtext | Yes | `NULL` |  | *None* |
| `created_at` | timestamp | Yes | `NULL` |  | *None* |
| `updated_at` | timestamp | Yes | `NULL` |  | *None* |
| `deleted_at` | timestamp | Yes | `NULL` |  | *None* |

**索引:**

*   `PRIMARY`: (`id`)
*   `sales_items_sale_id_foreign`: (`sale_id`)
*   `sales_items_product_id_foreign`: (`product_id`)

**外键:**

*   `sales_items_product_id_foreign`: (`product_id`) -> `products` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
*   `sales_items_sale_id_foreign`: (`sale_id`) -> `sales` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT

---

## `sales_order_items`

| 列名 | 类型 | 可空 | 默认值 | Extra | 注释 |
|---|---|---|---|---|---|
| `id` | bigint UNSIGNED | No | *None* | AUTO_INCREMENT | *None* |
| `sales_order_id` | bigint UNSIGNED | No | *None* |  | *None* |
| `product_id` | bigint UNSIGNED | No | *None* |  | *None* |
| `product_name` | varchar(255) | No | *None* |  | Product Name |
| `product_sku` | varchar(255) | No | *None* |  | merchandiseSKU |
| `quantity` | int | No | *None* |  | quantity |
| `unit_price` | decimal(10, 2) | No | *None* |  | unit price |
| `discount_amount` | decimal(10, 2) | No | `0.00` |  | Discount amount |
| `tax_rate` | decimal(5, 2) | No | `0.00` |  | tax rate |
| `tax_amount` | decimal(10, 2) | No | `0.00` |  | tax |
| `subtotal` | decimal(10, 2) | No | *None* |  | Subtotal |
| `specifications` | longtext | Yes | `NULL` |  | Specification information |
| `remarks` | text | Yes | `NULL` |  | Remark |
| `created_at` | timestamp | Yes | `NULL` |  | *None* |
| `updated_at` | timestamp | Yes | `NULL` |  | *None* |

**索引:**

*   `PRIMARY`: (`id`)
*   `sales_order_items_sales_order_id_foreign`: (`sales_order_id`)
*   `sales_order_items_product_id_foreign`: (`product_id`)

**外键:**

*   `sales_order_items_product_id_foreign`: (`product_id`) -> `products` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
*   `sales_order_items_sales_order_id_foreign`: (`sales_order_id`) -> `sales_orders` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT

---

## `sales_orders`

| 列名 | 类型 | 可空 | 默认值 | Extra | 注释 |
|---|---|---|---|---|---|
| `id` | bigint UNSIGNED | No | *None* | AUTO_INCREMENT | *None* |
| `order_number` | varchar(255) | No | *None* |  | *None* |
| `customer_id` | bigint UNSIGNED | No | *None* |  | *None* |
| `user_id` | bigint UNSIGNED | No | *None* |  | *None* |
| `store_id` | bigint UNSIGNED | No | *None* |  | *None* |
| `subtotal` | decimal(10, 2) | No | *None* |  | Subtotal |
| `tax_amount` | decimal(10, 2) | No | *None* |  | tax |
| `discount_amount` | decimal(10, 2) | No | `0.00` |  | Discount amount |
| `total_amount` | decimal(10, 2) | No | *None* |  | lump sum |
| `status` | varchar(255) | No | `pending` |  | Order Status |
| `payment_status` | varchar(255) | No | `unpaid` |  | Payment Status |
| `paid_at` | timestamp | Yes | `NULL` |  | Payment time |
| `payment_method` | varchar(255) | Yes | `NULL` |  | Payment method |
| `remarks` | text | Yes | `NULL` |  | Remark |
| `additional_data` | longtext | Yes | `NULL` |  | Additional data |
| `created_at` | timestamp | Yes | `NULL` |  | *None* |
| `updated_at` | timestamp | Yes | `NULL` |  | *None* |
| `deleted_at` | timestamp | Yes | `NULL` |  | *None* |

**索引:**

*   `PRIMARY`: (`id`)
*   `sales_orders_order_number_unique`: (`order_number`) (UNIQUE)
*   `sales_orders_customer_id_foreign`: (`customer_id`)
*   `sales_orders_user_id_foreign`: (`user_id`)
*   `sales_orders_store_id_foreign`: (`store_id`)

**外键:**

*   `sales_orders_customer_id_foreign`: (`customer_id`) -> `customers` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
*   `sales_orders_store_id_foreign`: (`store_id`) -> `warehouses` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
*   `sales_orders_user_id_foreign`: (`user_id`) -> `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT

---

## `settings`

| 列名 | 类型 | 可空 | 默认值 | Extra | 注释 |
|---|---|---|---|---|---|
| `id` | bigint UNSIGNED | No | *None* | AUTO_INCREMENT | *None* |
| `key` | varchar(255) | No | *None* |  | Set key name |
| `value` | text | Yes | `NULL` |  | Set value |
| `type` | varchar(255) | No | `string` |  | Value Type |
| `group` | varchar(255) | No | `general` |  | Set up grouping |
| `label` | varchar(255) | No | *None* |  | display name |
| `description` | text | Yes | `NULL` |  | describe |
| `options` | longtext | Yes | `NULL` |  | Optional value |
| `is_public` | tinyint(1) | No | `0` |  | Whether it is public |
| `created_at` | timestamp | Yes | `NULL` |  | *None* |
| `updated_at` | timestamp | Yes | `NULL` |  | *None* |

**索引:**

*   `PRIMARY`: (`id`)
*   `settings_key_unique`: (`key`) (UNIQUE)

**外键:** *无*

---

## `stock_movements`

| 列名 | 类型 | 可空 | 默认值 | Extra | 注释 |
|---|---|---|---|---|---|
| `id` | bigint UNSIGNED | No | *None* | AUTO_INCREMENT | *None* |
| `product_id` | bigint UNSIGNED | No | *None* |  | *None* |
| `warehouse_id` | bigint UNSIGNED | No | *None* |  | *None* |
| `reference_type` | varchar(255) | No | *None* |  | *None* |
| `reference_id` | bigint UNSIGNED | No | *None* |  | *None* |
| `movement_type` | varchar(255) | No | *None* |  | *None* |
| `quantity` | decimal(10, 2) | No | *None* |  | *None* |
| `unit_cost` | decimal(10, 2) | No | *None* |  | *None* |
| `total_cost` | decimal(10, 2) | No | *None* |  | *None* |
| `batch_number` | varchar(255) | Yes | `NULL` |  | *None* |
| `notes` | text | Yes | `NULL` |  | *None* |
| `created_at` | timestamp | Yes | `NULL` |  | *None* |
| `updated_at` | timestamp | Yes | `NULL` |  | *None* |
| `deleted_at` | timestamp | Yes | `NULL` |  | *None* |

**索引:**

*   `PRIMARY`: (`id`)
*   `stock_movements_product_id_foreign`: (`product_id`)
*   `stock_movements_warehouse_id_foreign`: (`warehouse_id`)
*   `stock_movements_reference_type_reference_id_index`: (`reference_type`, `reference_id`)
*   `stock_movements_movement_type_index`: (`movement_type`)
*   `stock_movements_batch_number_index`: (`batch_number`)
*   `stock_movements_created_at_index`: (`created_at`)

**外键:**

*   `stock_movements_product_id_foreign`: (`product_id`) -> `products` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
*   `stock_movements_warehouse_id_foreign`: (`warehouse_id`) -> `warehouses` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT

---

## `stock_transfer_items`

| 列名 | 类型 | 可空 | 默认值 | Extra | 注释 |
|---|---|---|---|---|---|
| `id` | bigint UNSIGNED | No | *None* | AUTO_INCREMENT | *None* |
| `stock_transfer_id` | bigint UNSIGNED | No | *None* |  | *None* |
| `product_id` | bigint UNSIGNED | No | *None* |  | *None* |
| `quantity` | decimal(10, 2) | No | *None* |  | *None* |
| `notes` | text | Yes | `NULL` |  | *None* |
| `created_at` | timestamp | Yes | `NULL` |  | *None* |
| `updated_at` | timestamp | Yes | `NULL` |  | *None* |
| `deleted_at` | timestamp | Yes | `NULL` |  | *None* |

**索引:**

*   `PRIMARY`: (`id`)
*   `stock_transfer_items_product_id_foreign`: (`product_id`)
*   `stock_transfer_items_stock_transfer_id_product_id_index`: (`stock_transfer_id`, `product_id`)

**外键:**

*   `stock_transfer_items_product_id_foreign`: (`product_id`) -> `products` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
*   `stock_transfer_items_stock_transfer_id_foreign`: (`stock_transfer_id`) -> `stock_transfers` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT

---

## `stock_transfers`

| 列名 | 类型 | 可空 | 默认值 | Extra | 注释 |
|---|---|---|---|---|---|
| `id` | bigint UNSIGNED | No | *None* | AUTO_INCREMENT | *None* |
| `from_warehouse_id` | bigint UNSIGNED | No | *None* |  | *None* |
| `to_warehouse_id` | bigint UNSIGNED | No | *None* |  | *None* |
| `transfer_date` | date | No | *None* |  | *None* |
| `status` | varchar(255) | No | `pending` |  | *None* |
| `notes` | text | Yes | `NULL` |  | *None* |
| `created_at` | timestamp | Yes | `NULL` |  | *None* |
| `updated_at` | timestamp | Yes | `NULL` |  | *None* |
| `deleted_at` | timestamp | Yes | `NULL` |  | *None* |

**索引:**

*   `PRIMARY`: (`id`)
*   `stock_transfers_from_warehouse_id_foreign`: (`from_warehouse_id`)
*   `stock_transfers_to_warehouse_id_foreign`: (`to_warehouse_id`)
*   `stock_transfers_transfer_date_index`: (`transfer_date`)
*   `stock_transfers_status_index`: (`status`)

**外键:**

*   `stock_transfers_from_warehouse_id_foreign`: (`from_warehouse_id`) -> `warehouses` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
*   `stock_transfers_to_warehouse_id_foreign`: (`to_warehouse_id`) -> `warehouses` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT

---

## `stocks`

| 列名 | 类型 | 可空 | 默认值 | Extra | 注释 |
|---|---|---|---|---|---|
| `id` | bigint UNSIGNED | No | *None* | AUTO_INCREMENT | *None* |
| `store_id` | bigint UNSIGNED | Yes | `NULL` |  | *None* |
| `product_id` | bigint UNSIGNED | No | *None* |  | *None* |
| `quantity` | decimal(10, 2) | No | `0.00` |  | *None* |
| `minimum_quantity` | decimal(10, 2) | No | `0.00` |  | *None* |
| `maximum_quantity` | decimal(10, 2) | No | `0.00` |  | *None* |
| `location` | varchar(255) | Yes | `NULL` |  | *None* |
| `notes` | text | Yes | `NULL` |  | *None* |
| `created_at` | timestamp | Yes | `NULL` |  | *None* |
| `updated_at` | timestamp | Yes | `NULL` |  | *None* |
| `deleted_at` | timestamp | Yes | `NULL` |  | *None* |

**索引:**

*   `PRIMARY`: (`id`)
*   `stocks_store_id_product_id_unique`: (`store_id`, `product_id`) (UNIQUE)
*   `stocks_product_id_foreign`: (`product_id`)

**外键:**

*   `stocks_product_id_foreign`: (`product_id`) -> `products` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
*   `stocks_store_id_foreign`: (`store_id`) -> `warehouses` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT

---

## `supplier_contacts`

| 列名 | 类型 | 可空 | 默认值 | Extra | 注释 |
|---|---|---|---|---|---|
| `id` | bigint UNSIGNED | No | *None* | AUTO_INCREMENT | *None* |
| `supplier_id` | bigint UNSIGNED | No | *None* |  | *None* |
| `name` | varchar(255) | No | *None* |  | Contact name |
| `position` | varchar(255) | Yes | `NULL` |  | Position |
| `phone` | varchar(255) | Yes | `NULL` |  | Contact number |
| `email` | varchar(255) | Yes | `NULL` |  | Email |
| `is_primary` | tinyint(1) | No | `0` |  | Is the main contact person |
| `remarks` | text | Yes | `NULL` |  | Remark |
| `created_at` | timestamp | Yes | `NULL` |  | *None* |
| `updated_at` | timestamp | Yes | `NULL` |  | *None* |
| `deleted_at` | timestamp | Yes | `NULL` |  | *None* |

**索引:**

*   `PRIMARY`: (`id`)
*   `supplier_contacts_supplier_id_foreign`: (`supplier_id`)

**外键:**

*   `supplier_contacts_supplier_id_foreign`: (`supplier_id`) -> `suppliers` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT

---

## `supplier_price_agreements`

| 列名 | 类型 | 可空 | 默认值 | Extra | 注释 |
|---|---|---|---|---|---|
| `id` | bigint UNSIGNED | No | *None* | AUTO_INCREMENT | *None* |
| `supplier_id` | bigint UNSIGNED | No | *None* |  | *None* |
| `product_id` | bigint UNSIGNED | No | *None* |  | *None* |
| `price` | decimal(10, 2) | Yes | `NULL` |  | Fixed price |
| `start_date` | date | No | *None* |  | Effective Date |
| `end_date` | date | Yes | `NULL` |  | Expiration date |
| `min_quantity` | int | No | `1` |  | Minimum quantity |
| `discount_rate` | decimal(5, 2) | Yes | `NULL` |  | Discount rate |
| `discount_type` | enum('fixed_price','discount_rate') | No | *None* |  | Discount type: fixed price/Discount rate |
| `terms` | text | Yes | `NULL` |  | Terms of the Agreement |
| `created_at` | timestamp | Yes | `NULL` |  | *None* |
| `updated_at` | timestamp | Yes | `NULL` |  | *None* |
| `deleted_at` | timestamp | Yes | `NULL` |  | *None* |

**索引:**

*   `PRIMARY`: (`id`)
*   `supplier_price_agreements_supplier_id_foreign`: (`supplier_id`)
*   `supplier_price_agreements_product_id_foreign`: (`product_id`)

**外键:**

*   `supplier_price_agreements_product_id_foreign`: (`product_id`) -> `products` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
*   `supplier_price_agreements_supplier_id_foreign`: (`supplier_id`) -> `suppliers` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT

---

## `supplier_products`

| 列名 | 类型 | 可空 | 默认值 | Extra | 注释 |
|---|---|---|---|---|---|
| `id` | bigint UNSIGNED | No | *None* | AUTO_INCREMENT | *None* |
| `supplier_id` | bigint UNSIGNED | No | *None* |  | *None* |
| `product_id` | bigint UNSIGNED | No | *None* |  | *None* |
| `supplier_product_code` | varchar(255) | Yes | `NULL` |  | Supplier product number |
| `purchase_price` | decimal(10, 2) | No | *None* |  | Purchase price |
| `min_order_quantity` | int | No | `1` |  | Minimum order quantity |
| `lead_time` | int | No | `7` |  | Delivery cycle(day) |
| `is_preferred` | tinyint(1) | No | `0` |  | Is the preferred supplier? |
| `tax_rate` | decimal(5, 2) | No | `0.00` |  | tax rate |
| `remarks` | text | Yes | `NULL` |  | Remark |
| `created_at` | timestamp | Yes | `NULL` |  | *None* |
| `updated_at` | timestamp | Yes | `NULL` |  | *None* |
| `deleted_at` | timestamp | Yes | `NULL` |  | *None* |

**索引:**

*   `PRIMARY`: (`id`)
*   `supplier_products_supplier_id_product_id_unique`: (`supplier_id`, `product_id`) (UNIQUE)
*   `supplier_products_product_id_foreign`: (`product_id`)

**外键:**

*   `supplier_products_product_id_foreign`: (`product_id`) -> `products` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
*   `supplier_products_supplier_id_foreign`: (`supplier_id`) -> `suppliers` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT

---

## `suppliers`

| 列名 | 类型 | 可空 | 默认值 | Extra | 注释 |
|---|---|---|---|---|---|
| `id` | bigint UNSIGNED | No | *None* | AUTO_INCREMENT | *None* |
| `code` | varchar(255) | No | *None* |  | Supplier number |
| `name` | varchar(255) | No | *None* |  | Supplier name |
| `contact_person` | varchar(255) | Yes | `NULL` |  | Contact |
| `phone` | varchar(255) | Yes | `NULL` |  | Contact number |
| `email` | varchar(255) | Yes | `NULL` |  | Email |
| `address` | varchar(255) | Yes | `NULL` |  | address |
| `credit_limit` | decimal(10, 2) | Yes | `0.00` |  | Credit limit |
| `payment_term` | int | Yes | `30` |  | Accounting period(day) |
| `remarks` | text | Yes | `NULL` |  | Remark |
| `is_active` | tinyint(1) | No | `1` |  | Whether to enable |
| `created_at` | timestamp | Yes | `NULL` |  | *None* |
| `updated_at` | timestamp | Yes | `NULL` |  | *None* |
| `deleted_at` | timestamp | Yes | `NULL` |  | *None* |

**索引:**

*   `PRIMARY`: (`id`)
*   `suppliers_code_unique`: (`code`) (UNIQUE)

**外键:** *无*

---

## `users`

| 列名 | 类型 | 可空 | 默认值 | Extra | 注释 |
|---|---|---|---|---|---|
| `id` | bigint UNSIGNED | No | *None* | AUTO_INCREMENT | *None* |
| `name` | varchar(255) | No | *None* |  | *None* |
| `email` | varchar(255) | No | *None* |  | *None* |
| `phone` | varchar(255) | Yes | `NULL` |  | *None* |
| `employee_id` | varchar(255) | Yes | `NULL` |  | *None* |
| `email_verified_at` | timestamp | Yes | `NULL` |  | *None* |
| `password` | varchar(255) | No | *None* |  | *None* |
| `is_active` | tinyint(1) | No | `1` |  | *None* |
| `avatar` | varchar(255) | Yes | `NULL` |  | *None* |
| `settings` | longtext | Yes | `NULL` |  | *None* |
| `last_login_at` | timestamp | Yes | `NULL` |  | *None* |
| `last_login_ip` | varchar(255) | Yes | `NULL` |  | *None* |
| `remember_token` | varchar(100) | Yes | `NULL` |  | *None* |
| `created_at` | timestamp | Yes | `NULL` |  | *None* |
| `updated_at` | timestamp | Yes | `NULL` |  | *None* |
| `deleted_at` | timestamp | Yes | `NULL` |  | *None* |

**索引:**

*   `PRIMARY`: (`id`)
*   `users_email_unique`: (`email`) (UNIQUE)
*   `users_employee_id_unique`: (`employee_id`) (UNIQUE)

**外键:** *无*

---

## `verification_codes`

| 列名 | 类型 | 可空 | 默认值 | Extra | 注释 |
|---|---|---|---|---|---|
| `id` | bigint UNSIGNED | No | *None* | AUTO_INCREMENT | *None* |
| `email` | varchar(255) | No | *None* |  | *None* |
| `code` | varchar(255) | No | *None* |  | *None* |
| `expires_at` | timestamp | No | `current_timestamp` | on update current_timestamp() | *None* |
| `is_used` | tinyint(1) | No | `0` |  | *None* |
| `created_at` | timestamp | Yes | `NULL` |  | *None* |
| `updated_at` | timestamp | Yes | `NULL` |  | *None* |

**索引:**

*   `PRIMARY`: (`id`)
*   `verification_codes_email_index`: (`email`)

**外键:** *无*

---

## `warehouses`

| 列名 | 类型 | 可空 | 默认值 | Extra | 注释 |
|---|---|---|---|---|---|
| `id` | bigint UNSIGNED | No | *None* | AUTO_INCREMENT | *None* |
| `code` | varchar(50) | No | *None* |  | *None* |
| `name` | varchar(255) | No | *None* |  | *None* |
| `address` | varchar(255) | Yes | `NULL` |  | *None* |
| `contact_person` | varchar(50) | Yes | `NULL` |  | *None* |
| `contact_phone` | varchar(20) | Yes | `NULL` |  | *None* |
| `status` | tinyint(1) | No | `1` |  | *None* |
| `is_default` | tinyint(1) | No | `0` |  | *None* |
| `notes` | text | Yes | `NULL` |  | *None* |
| `created_at` | timestamp | Yes | `NULL` |  | *None* |
| `updated_at` | timestamp | Yes | `NULL` |  | *None* |
| `deleted_at` | timestamp | Yes | `NULL` |  | *None* |
| `is_store` | tinyint(1) | No | `0` |  | Is it a store or not |

**索引:**

*   `PRIMARY`: (`id`)
*   `warehouses_code_unique`: (`code`) (UNIQUE)

**外键:** *无*

---