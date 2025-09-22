-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: 127.0.0.1    Database: optical_shop
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `activity_log`
--

DROP TABLE IF EXISTS `activity_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `activity_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `log_name` varchar(255) DEFAULT NULL,
  `description` text NOT NULL,
  `subject_type` varchar(255) DEFAULT NULL,
  `event` varchar(255) DEFAULT NULL,
  `subject_id` bigint(20) unsigned DEFAULT NULL,
  `causer_type` varchar(255) DEFAULT NULL,
  `causer_id` bigint(20) unsigned DEFAULT NULL,
  `properties` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`properties`)),
  `batch_uuid` char(36) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `subject` (`subject_type`,`subject_id`),
  KEY `causer` (`causer_type`,`causer_id`),
  KEY `activity_log_log_name_index` (`log_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activity_log`
--

LOCK TABLES `activity_log` WRITE;
/*!40000 ALTER TABLE `activity_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `activity_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `activity_logs`
--

DROP TABLE IF EXISTS `activity_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `activity_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `subject_type` varchar(255) NOT NULL,
  `subject_id` bigint(20) unsigned NOT NULL,
  `event` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `properties` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`properties`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `activity_logs_user_id_foreign` (`user_id`),
  KEY `activity_logs_subject_type_subject_id_index` (`subject_type`,`subject_id`),
  KEY `activity_logs_event_index` (`event`),
  KEY `activity_logs_created_at_index` (`created_at`),
  CONSTRAINT `activity_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activity_logs`
--

LOCK TABLES `activity_logs` WRITE;
/*!40000 ALTER TABLE `activity_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `activity_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `audit_logs`
--

DROP TABLE IF EXISTS `audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `audit_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `event` varchar(255) NOT NULL,
  `auditable_type` varchar(255) NOT NULL,
  `auditable_id` bigint(20) unsigned NOT NULL,
  `old_values` text DEFAULT NULL,
  `new_values` text DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `audit_logs_user_id_foreign` (`user_id`),
  KEY `audit_logs_auditable_type_auditable_id_index` (`auditable_type`,`auditable_id`),
  CONSTRAINT `audit_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit_logs`
--

LOCK TABLES `audit_logs` WRITE;
/*!40000 ALTER TABLE `audit_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `audit_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `brands`
--

DROP TABLE IF EXISTS `brands`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `brands` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `code` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `logo_path` varchar(255) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `contact_person` varchar(255) DEFAULT NULL,
  `contact_email` varchar(255) DEFAULT NULL,
  `contact_phone` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `display_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `brands_code_unique` (`code`),
  KEY `brands_code_index` (`code`),
  KEY `brands_is_active_index` (`is_active`),
  KEY `brands_display_order_index` (`display_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `brands`
--

LOCK TABLES `brands` WRITE;
/*!40000 ALTER TABLE `brands` DISABLE KEYS */;
/*!40000 ALTER TABLE `brands` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `category_parameters`
--

DROP TABLE IF EXISTS `category_parameters`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `category_parameters` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `category_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL COMMENT '参数名称',
  `code` varchar(255) NOT NULL COMMENT '参数代码',
  `type` varchar(255) NOT NULL COMMENT '参数类型：text/number/select/radio/checkbox',
  `is_required` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否必填',
  `options` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT '选项（用于select/radio/checkbox类型）' CHECK (json_valid(`options`)),
  `validation_rules` varchar(255) DEFAULT NULL COMMENT '验证规则',
  `is_searchable` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否可搜索',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0 COMMENT '排序',
  `description` text DEFAULT NULL COMMENT '参数描述',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `category_parameters_category_id_code_unique` (`category_id`,`code`),
  CONSTRAINT `category_parameters_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `product_categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `category_parameters`
--

LOCK TABLES `category_parameters` WRITE;
/*!40000 ALTER TABLE `category_parameters` DISABLE KEYS */;
/*!40000 ALTER TABLE `category_parameters` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `company_profiles`
--

DROP TABLE IF EXISTS `company_profiles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `company_profiles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `address` text NOT NULL,
  `phone` varchar(50) NOT NULL,
  `email` varchar(255) NOT NULL,
  `registration_number` varchar(100) DEFAULT NULL,
  `tax_number` varchar(100) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `bank_name` varchar(255) DEFAULT NULL,
  `bank_account` varchar(50) DEFAULT NULL,
  `bank_holder` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `company_profiles`
--

LOCK TABLES `company_profiles` WRITE;
/*!40000 ALTER TABLE `company_profiles` DISABLE KEYS */;
/*!40000 ALTER TABLE `company_profiles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customers`
--

DROP TABLE IF EXISTS `customers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `customers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `ic_number` varchar(255) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `birthday` date DEFAULT NULL,
  `contact_number` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `whatsapp` varchar(255) DEFAULT NULL,
  `points` decimal(10,2) NOT NULL DEFAULT 0.00,
  `remarks` text DEFAULT NULL,
  `tags` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`tags`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `last_visit_at` timestamp NULL DEFAULT NULL,
  `member_level` enum('normal','silver','gold','platinum') NOT NULL DEFAULT 'normal',
  PRIMARY KEY (`id`),
  UNIQUE KEY `customers_ic_number_unique` (`ic_number`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customers`
--

LOCK TABLES `customers` WRITE;
/*!40000 ALTER TABLE `customers` DISABLE KEYS */;
INSERT INTO `customers` VALUES (1,'123456789012','Zhang San','2009-09-01','0123456789','zhangsan@example.com','Test address',NULL,0.00,NULL,NULL,'2025-02-06 07:33:35','2025-02-06 07:33:54','2025-02-06 07:33:54',NULL,'normal'),(2,'098765431210','Li Si','1991-02-12','0987654321','lisi@example.com','Test address',NULL,0.00,NULL,NULL,'2025-02-06 07:33:42','2025-02-06 07:33:42',NULL,NULL,'gold'),(3,'123456789010','King five',NULL,'4567891234',NULL,'Original address',NULL,0.00,NULL,NULL,'2025-02-06 07:33:49','2025-02-06 07:33:52',NULL,NULL,'gold'),(4,'789123456789','Zhao Liu',NULL,'7891234567',NULL,NULL,NULL,0.00,NULL,NULL,'2025-02-06 07:33:53','2025-02-06 07:33:53',NULL,NULL,'normal');
/*!40000 ALTER TABLE `customers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inventory`
--

DROP TABLE IF EXISTS `inventory`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `inventory` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint(20) unsigned NOT NULL,
  `batch_number` varchar(255) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `expiry_date` date DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'available',
  `additional_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`additional_data`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `inventory_product_id_foreign` (`product_id`),
  CONSTRAINT `inventory_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inventory`
--

LOCK TABLES `inventory` WRITE;
/*!40000 ALTER TABLE `inventory` DISABLE KEYS */;
/*!40000 ALTER TABLE `inventory` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inventory_check_items`
--

DROP TABLE IF EXISTS `inventory_check_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `inventory_check_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `inventory_check_id` bigint(20) unsigned NOT NULL,
  `product_id` bigint(20) unsigned NOT NULL,
  `system_count` int(11) NOT NULL,
  `actual_count` int(11) NOT NULL,
  `difference` int(11) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `inventory_check_items_inventory_check_id_foreign` (`inventory_check_id`),
  KEY `inventory_check_items_product_id_foreign` (`product_id`),
  CONSTRAINT `inventory_check_items_inventory_check_id_foreign` FOREIGN KEY (`inventory_check_id`) REFERENCES `inventory_checks` (`id`) ON DELETE CASCADE,
  CONSTRAINT `inventory_check_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inventory_check_items`
--

LOCK TABLES `inventory_check_items` WRITE;
/*!40000 ALTER TABLE `inventory_check_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `inventory_check_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inventory_checks`
--

DROP TABLE IF EXISTS `inventory_checks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `inventory_checks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `check_number` varchar(255) NOT NULL,
  `check_date` date NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `inventory_checks_check_number_unique` (`check_number`),
  KEY `inventory_checks_user_id_foreign` (`user_id`),
  CONSTRAINT `inventory_checks_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inventory_checks`
--

LOCK TABLES `inventory_checks` WRITE;
/*!40000 ALTER TABLE `inventory_checks` DISABLE KEYS */;
/*!40000 ALTER TABLE `inventory_checks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inventory_loss_items`
--

DROP TABLE IF EXISTS `inventory_loss_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `inventory_loss_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `inventory_loss_id` bigint(20) unsigned NOT NULL,
  `product_id` bigint(20) unsigned NOT NULL,
  `quantity` int(11) NOT NULL,
  `cost_price` decimal(10,2) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `inventory_loss_items_inventory_loss_id_foreign` (`inventory_loss_id`),
  KEY `inventory_loss_items_product_id_foreign` (`product_id`),
  CONSTRAINT `inventory_loss_items_inventory_loss_id_foreign` FOREIGN KEY (`inventory_loss_id`) REFERENCES `inventory_losses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `inventory_loss_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inventory_loss_items`
--

LOCK TABLES `inventory_loss_items` WRITE;
/*!40000 ALTER TABLE `inventory_loss_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `inventory_loss_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inventory_losses`
--

DROP TABLE IF EXISTS `inventory_losses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `inventory_losses` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `loss_number` varchar(255) NOT NULL,
  `loss_date` date NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `approved_by` bigint(20) unsigned DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `reason` text NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `inventory_losses_loss_number_unique` (`loss_number`),
  KEY `inventory_losses_user_id_foreign` (`user_id`),
  KEY `inventory_losses_approved_by_foreign` (`approved_by`),
  CONSTRAINT `inventory_losses_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`),
  CONSTRAINT `inventory_losses_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inventory_losses`
--

LOCK TABLES `inventory_losses` WRITE;
/*!40000 ALTER TABLE `inventory_losses` DISABLE KEYS */;
/*!40000 ALTER TABLE `inventory_losses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inventory_records`
--

DROP TABLE IF EXISTS `inventory_records`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `inventory_records` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint(20) unsigned NOT NULL,
  `quantity` int(11) NOT NULL COMMENT '数量',
  `type` varchar(255) NOT NULL COMMENT '类型：in-入库，out-出库',
  `source_type` varchar(255) DEFAULT NULL COMMENT '来源类型：purchase-采购，sale-销售，loss-报损，check-盘点',
  `source_id` bigint(20) unsigned DEFAULT NULL COMMENT '来源ID',
  `batch_number` varchar(255) DEFAULT NULL COMMENT '批次号',
  `expiry_date` date DEFAULT NULL COMMENT '有效期',
  `location` varchar(255) DEFAULT NULL COMMENT '库位',
  `status` varchar(255) NOT NULL DEFAULT 'available' COMMENT '状态：available-可用，locked-锁定',
  `additional_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT '附加数据' CHECK (json_valid(`additional_data`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `inventory_records_source_type_source_id_index` (`source_type`,`source_id`),
  KEY `inventory_records_product_id_batch_number_index` (`product_id`,`batch_number`),
  KEY `inventory_records_product_id_status_index` (`product_id`,`status`),
  CONSTRAINT `inventory_records_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inventory_records`
--

LOCK TABLES `inventory_records` WRITE;
/*!40000 ALTER TABLE `inventory_records` DISABLE KEYS */;
/*!40000 ALTER TABLE `inventory_records` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `invoices`
--

DROP TABLE IF EXISTS `invoices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `invoices` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `invoice_number` varchar(255) NOT NULL,
  `sales_order_id` bigint(20) unsigned NOT NULL,
  `customer_id` bigint(20) unsigned NOT NULL,
  `invoice_date` date NOT NULL,
  `due_date` date DEFAULT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `tax_amount` decimal(10,2) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'draft',
  `payment_status` varchar(255) NOT NULL DEFAULT 'unpaid',
  `company_details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT '公司信息' CHECK (json_valid(`company_details`)),
  `customer_details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT '客户信息' CHECK (json_valid(`customer_details`)),
  `terms_and_conditions` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `currency` varchar(255) NOT NULL DEFAULT 'MYR',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `invoices_invoice_number_unique` (`invoice_number`),
  KEY `invoices_sales_order_id_foreign` (`sales_order_id`),
  KEY `invoices_customer_id_foreign` (`customer_id`),
  CONSTRAINT `invoices_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  CONSTRAINT `invoices_sales_order_id_foreign` FOREIGN KEY (`sales_order_id`) REFERENCES `sales_orders` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `invoices`
--

LOCK TABLES `invoices` WRITE;
/*!40000 ALTER TABLE `invoices` DISABLE KEYS */;
/*!40000 ALTER TABLE `invoices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=370 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (329,'2014_10_12_000000_create_users_table',1),(330,'2014_10_12_100000_create_password_reset_tokens_table',1),(331,'2019_08_19_000000_create_failed_jobs_table',1),(332,'2019_12_14_000001_create_personal_access_tokens_table',1),(333,'2024_01_17_000001_create_activity_logs_table',1),(334,'2024_01_17_000001_create_company_profiles_table',1),(335,'2024_01_17_000001_create_permission_tables',1),(336,'2024_01_17_000002_create_audit_logs_table',1),(337,'2024_01_17_000002_create_permissions',1),(338,'2024_01_17_000003_assign_roles_to_users',1),(339,'2024_01_17_000003_create_customers_table',1),(340,'2024_01_17_000003_create_warehouses_table',1),(341,'2024_01_17_000004_create_prescriptions_table',1),(342,'2024_01_17_000010_create_product_categories_table',1),(343,'2024_01_17_000011_create_products_table',1),(344,'2024_01_17_000012_create_brands_table',1),(345,'2024_01_17_000012_create_stock_movements_table',1),(346,'2024_01_17_000014_create_suppliers_table',1),(347,'2024_01_17_000015_create_purchases_table',1),(348,'2024_01_17_000015_create_whatsapp_notifications_table',1),(349,'2024_01_17_000016_create_category_parameters_table',1),(350,'2024_01_17_000016_create_payment_plans_table',1),(351,'2024_01_17_000016_create_purchase_items_table',1),(352,'2024_01_17_000017_create_quality_inspections_table',1),(353,'2024_01_17_000018_create_quality_inspection_items_table',1),(354,'2024_01_17_000020_create_inventory_checks_table',1),(355,'2024_01_17_000020_create_inventory_table',1),(356,'2024_01_17_000021_create_sales_orders_table',1),(357,'2024_01_17_000022_create_sales_order_items_table',1),(358,'2024_01_17_000023_create_payments_table',1),(359,'2024_01_17_000024_create_invoices_table',1),(360,'2024_01_19_000003_create_inventory_records_table',1),(361,'2025_01_17_142117_add_last_visit_at_to_customers_table',1),(362,'2025_01_17_145203_add_member_level_to_customers_table',1),(363,'2025_01_20_154849_modify_cost_price_in_products_table',1),(364,'2025_01_22_071914_create_stock_transfers_table',1),(365,'2025_01_22_071922_create_stock_transfer_items_table',1),(366,'2025_02_01_172018_create_activity_log_table',1),(367,'2025_02_01_172019_add_event_column_to_activity_log_table',1),(368,'2025_02_01_172020_add_batch_uuid_column_to_activity_log_table',1),(369,'2025_02_05_151754_update_prescriptions_table_add_power_fields',1);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `model_has_permissions`
--

DROP TABLE IF EXISTS `model_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `model_has_permissions` (
  `permission_id` bigint(20) unsigned NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `model_has_permissions`
--

LOCK TABLES `model_has_permissions` WRITE;
/*!40000 ALTER TABLE `model_has_permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `model_has_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `model_has_roles`
--

DROP TABLE IF EXISTS `model_has_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `model_has_roles` (
  `role_id` bigint(20) unsigned NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `model_has_roles`
--

LOCK TABLES `model_has_roles` WRITE;
/*!40000 ALTER TABLE `model_has_roles` DISABLE KEYS */;
INSERT INTO `model_has_roles` VALUES (4,'App\\Models\\User',1);
/*!40000 ALTER TABLE `model_has_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payment_plans`
--

DROP TABLE IF EXISTS `payment_plans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payment_plans` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `purchase_id` bigint(20) unsigned NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `due_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` enum('pending','partially_paid','completed','cancelled','overdue') NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payment_plans_purchase_id_foreign` (`purchase_id`),
  KEY `payment_plans_status_due_date_index` (`status`,`due_date`),
  CONSTRAINT `payment_plans_purchase_id_foreign` FOREIGN KEY (`purchase_id`) REFERENCES `purchases` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payment_plans`
--

LOCK TABLES `payment_plans` WRITE;
/*!40000 ALTER TABLE `payment_plans` DISABLE KEYS */;
/*!40000 ALTER TABLE `payment_plans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `payment_number` varchar(255) NOT NULL,
  `payable_type` varchar(255) NOT NULL,
  `payable_id` bigint(20) unsigned NOT NULL,
  `customer_id` bigint(20) unsigned NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `transaction_id` varchar(255) DEFAULT NULL,
  `payment_details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payment_details`)),
  `remarks` text DEFAULT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payments_payment_number_unique` (`payment_number`),
  KEY `payments_payable_type_payable_id_index` (`payable_type`,`payable_id`),
  KEY `payments_customer_id_foreign` (`customer_id`),
  CONSTRAINT `payments_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payments`
--

LOCK TABLES `payments` WRITE;
/*!40000 ALTER TABLE `payments` DISABLE KEYS */;
/*!40000 ALTER TABLE `payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `permissions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=77 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permissions`
--

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
INSERT INTO `permissions` VALUES (1,'view_products','web',NULL,'2025-02-06 07:33:24','2025-02-06 07:33:24'),(2,'create_products','web',NULL,'2025-02-06 07:33:24','2025-02-06 07:33:24'),(3,'edit_products','web',NULL,'2025-02-06 07:33:24','2025-02-06 07:33:24'),(4,'delete_products','web',NULL,'2025-02-06 07:33:24','2025-02-06 07:33:24'),(5,'manage_products','web',NULL,'2025-02-06 07:33:24','2025-02-06 07:33:24'),(6,'view_customers','web',NULL,'2025-02-06 07:33:24','2025-02-06 07:33:24'),(7,'create_customers','web',NULL,'2025-02-06 07:33:24','2025-02-06 07:33:24'),(8,'edit_customers','web',NULL,'2025-02-06 07:33:24','2025-02-06 07:33:24'),(9,'delete_customers','web',NULL,'2025-02-06 07:33:24','2025-02-06 07:33:24'),(10,'manage_customers','web',NULL,'2025-02-06 07:33:24','2025-02-06 07:33:24'),(11,'view_prescriptions','web',NULL,'2025-02-06 07:33:24','2025-02-06 07:33:24'),(12,'create_prescriptions','web',NULL,'2025-02-06 07:33:24','2025-02-06 07:33:24'),(13,'edit_prescriptions','web',NULL,'2025-02-06 07:33:24','2025-02-06 07:33:24'),(14,'delete_prescriptions','web',NULL,'2025-02-06 07:33:24','2025-02-06 07:33:24'),(15,'manage_prescriptions','web',NULL,'2025-02-06 07:33:24','2025-02-06 07:33:24'),(16,'view users','web',NULL,'2025-02-06 07:33:26','2025-02-06 07:33:26'),(17,'create users','web',NULL,'2025-02-06 07:33:26','2025-02-06 07:33:26'),(18,'edit users','web',NULL,'2025-02-06 07:33:26','2025-02-06 07:33:26'),(19,'delete users','web',NULL,'2025-02-06 07:33:26','2025-02-06 07:33:26'),(20,'view customers','web',NULL,'2025-02-06 07:33:26','2025-02-06 07:33:26'),(21,'create customers','web',NULL,'2025-02-06 07:33:26','2025-02-06 07:33:26'),(22,'edit customers','web',NULL,'2025-02-06 07:33:26','2025-02-06 07:33:26'),(23,'delete customers','web',NULL,'2025-02-06 07:33:26','2025-02-06 07:33:26'),(24,'view prescriptions','web',NULL,'2025-02-06 07:33:26','2025-02-06 07:33:26'),(25,'create prescriptions','web',NULL,'2025-02-06 07:33:26','2025-02-06 07:33:26'),(26,'edit prescriptions','web',NULL,'2025-02-06 07:33:26','2025-02-06 07:33:26'),(27,'delete prescriptions','web',NULL,'2025-02-06 07:33:26','2025-02-06 07:33:26'),(28,'view products','web',NULL,'2025-02-06 07:33:26','2025-02-06 07:33:26'),(29,'create products','web',NULL,'2025-02-06 07:33:26','2025-02-06 07:33:26'),(30,'edit products','web',NULL,'2025-02-06 07:33:26','2025-02-06 07:33:26'),(31,'delete products','web',NULL,'2025-02-06 07:33:26','2025-02-06 07:33:26'),(32,'view sales','web',NULL,'2025-02-06 07:33:26','2025-02-06 07:33:26'),(33,'create sales','web',NULL,'2025-02-06 07:33:26','2025-02-06 07:33:26'),(34,'edit sales','web',NULL,'2025-02-06 07:33:26','2025-02-06 07:33:26'),(35,'delete sales','web',NULL,'2025-02-06 07:33:26','2025-02-06 07:33:26'),(36,'manage inventory','web',NULL,'2025-02-06 07:33:26','2025-02-06 07:33:26'),(37,'view inventory','web',NULL,'2025-02-06 07:33:26','2025-02-06 07:33:26'),(38,'create inventory check','web',NULL,'2025-02-06 07:33:26','2025-02-06 07:33:26'),(39,'complete inventory check','web',NULL,'2025-02-06 07:33:26','2025-02-06 07:33:26'),(40,'create inventory loss','web',NULL,'2025-02-06 07:33:26','2025-02-06 07:33:26'),(41,'approve inventory loss','web',NULL,'2025-02-06 07:33:26','2025-02-06 07:33:26'),(42,'view inventory alerts','web',NULL,'2025-02-06 07:33:26','2025-02-06 07:33:26'),(43,'view suppliers','web',NULL,'2025-02-06 07:33:26','2025-02-06 07:33:26'),(44,'create suppliers','web',NULL,'2025-02-06 07:33:26','2025-02-06 07:33:26'),(45,'edit suppliers','web',NULL,'2025-02-06 07:33:26','2025-02-06 07:33:26'),(46,'delete suppliers','web',NULL,'2025-02-06 07:33:26','2025-02-06 07:33:26'),(47,'manage supplier products','web',NULL,'2025-02-06 07:33:26','2025-02-06 07:33:26'),(48,'manage price agreements','web',NULL,'2025-02-06 07:33:26','2025-02-06 07:33:26'),(49,'view warehouses','web',NULL,'2025-02-06 07:33:26','2025-02-06 07:33:26'),(50,'create warehouses','web',NULL,'2025-02-06 07:33:26','2025-02-06 07:33:26'),(51,'edit warehouses','web',NULL,'2025-02-06 07:33:26','2025-02-06 07:33:26'),(52,'delete warehouses','web',NULL,'2025-02-06 07:33:26','2025-02-06 07:33:26'),(53,'view purchases','web',NULL,'2025-02-06 07:33:26','2025-02-06 07:33:26'),(54,'create purchases','web',NULL,'2025-02-06 07:33:26','2025-02-06 07:33:26'),(55,'edit purchases','web',NULL,'2025-02-06 07:33:26','2025-02-06 07:33:26'),(56,'delete purchases','web',NULL,'2025-02-06 07:33:26','2025-02-06 07:33:26'),(57,'approve purchases','web',NULL,'2025-02-06 07:33:26','2025-02-06 07:33:26'),(58,'reject purchases','web',NULL,'2025-02-06 07:33:26','2025-02-06 07:33:26'),(59,'confirm purchases','web',NULL,'2025-02-06 07:33:26','2025-02-06 07:33:26'),(60,'cancel purchases','web',NULL,'2025-02-06 07:33:26','2025-02-06 07:33:26'),(61,'view purchase returns','web',NULL,'2025-02-06 07:33:26','2025-02-06 07:33:26'),(62,'create purchase returns','web',NULL,'2025-02-06 07:33:26','2025-02-06 07:33:26'),(63,'edit purchase returns','web',NULL,'2025-02-06 07:33:26','2025-02-06 07:33:26'),(64,'delete purchase returns','web',NULL,'2025-02-06 07:33:26','2025-02-06 07:33:26'),(65,'approve purchase returns','web',NULL,'2025-02-06 07:33:26','2025-02-06 07:33:26'),(66,'reject purchase returns','web',NULL,'2025-02-06 07:33:26','2025-02-06 07:33:26'),(67,'complete purchase returns','web',NULL,'2025-02-06 07:33:26','2025-02-06 07:33:26'),(68,'view purchase refunds','web',NULL,'2025-02-06 07:33:26','2025-02-06 07:33:26'),(69,'create purchase refunds','web',NULL,'2025-02-06 07:33:26','2025-02-06 07:33:26'),(70,'delete purchase refunds','web',NULL,'2025-02-06 07:33:26','2025-02-06 07:33:26'),(71,'view quality inspections','web',NULL,'2025-02-06 07:33:26','2025-02-06 07:33:26'),(72,'create quality inspections','web',NULL,'2025-02-06 07:33:26','2025-02-06 07:33:26'),(73,'edit quality inspections','web',NULL,'2025-02-06 07:33:26','2025-02-06 07:33:26'),(74,'delete quality inspections','web',NULL,'2025-02-06 07:33:26','2025-02-06 07:33:26'),(75,'approve quality inspections','web',NULL,'2025-02-06 07:33:26','2025-02-06 07:33:26'),(76,'reject quality inspections','web',NULL,'2025-02-06 07:33:26','2025-02-06 07:33:26');
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personal_access_tokens`
--

LOCK TABLES `personal_access_tokens` WRITE;
/*!40000 ALTER TABLE `personal_access_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `personal_access_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `prescriptions`
--

DROP TABLE IF EXISTS `prescriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `prescriptions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `examination_date` date NOT NULL COMMENT '验光日期',
  `right_sphere` decimal(5,2) DEFAULT NULL COMMENT '右眼球镜',
  `right_cylinder` decimal(5,2) DEFAULT NULL COMMENT '右眼柱镜',
  `right_axis` decimal(5,2) DEFAULT NULL COMMENT '右眼轴向',
  `right_pd` decimal(5,2) DEFAULT NULL COMMENT '右眼PD值',
  `right_va` decimal(5,2) DEFAULT NULL COMMENT '右眼视力',
  `left_sphere` decimal(5,2) DEFAULT NULL COMMENT '左眼球镜',
  `left_cylinder` decimal(5,2) DEFAULT NULL COMMENT '左眼柱镜',
  `left_axis` decimal(5,2) DEFAULT NULL COMMENT '左眼轴向',
  `left_pd` decimal(5,2) DEFAULT NULL COMMENT '左眼PD值',
  `left_va` decimal(5,2) DEFAULT NULL COMMENT '左眼视力',
  `right_add_power` decimal(5,2) DEFAULT NULL COMMENT '加入度',
  `left_add_power` decimal(8,2) DEFAULT NULL COMMENT 'Left eye additional degree (ADD)',
  `total_pd` decimal(5,2) DEFAULT NULL COMMENT '总PD',
  `remarks` text DEFAULT NULL COMMENT '备注',
  `additional_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT '额外数据' CHECK (json_valid(`additional_data`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `prescriptions_customer_id_foreign` (`customer_id`),
  KEY `prescriptions_user_id_foreign` (`user_id`),
  CONSTRAINT `prescriptions_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `prescriptions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `prescriptions`
--

LOCK TABLES `prescriptions` WRITE;
/*!40000 ALTER TABLE `prescriptions` DISABLE KEYS */;
/*!40000 ALTER TABLE `prescriptions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_categories`
--

DROP TABLE IF EXISTS `product_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `code` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_categories_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_categories`
--

LOCK TABLES `product_categories` WRITE;
/*!40000 ALTER TABLE `product_categories` DISABLE KEYS */;
/*!40000 ALTER TABLE `product_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `products` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `category_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `sku` varchar(255) NOT NULL,
  `barcode` varchar(255) DEFAULT NULL,
  `brand` varchar(255) DEFAULT NULL,
  `model` varchar(255) DEFAULT NULL,
  `cost_price` decimal(10,2) DEFAULT NULL,
  `selling_price` decimal(10,2) NOT NULL,
  `min_stock` int(11) NOT NULL DEFAULT 0,
  `inventory_count` int(11) NOT NULL DEFAULT 0,
  `description` text DEFAULT NULL,
  `images` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`images`)),
  `parameters` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`parameters`)),
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `products_sku_unique` (`sku`),
  UNIQUE KEY `products_barcode_unique` (`barcode`),
  KEY `products_category_id_foreign` (`category_id`),
  CONSTRAINT `products_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `product_categories` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `purchase_items`
--

DROP TABLE IF EXISTS `purchase_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `purchase_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `purchase_id` bigint(20) unsigned NOT NULL,
  `product_id` bigint(20) unsigned NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `tax_rate` decimal(5,2) NOT NULL DEFAULT 0.00,
  `tax_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `discount_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(10,2) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `purchase_items_product_id_foreign` (`product_id`),
  KEY `purchase_items_purchase_id_product_id_index` (`purchase_id`,`product_id`),
  CONSTRAINT `purchase_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  CONSTRAINT `purchase_items_purchase_id_foreign` FOREIGN KEY (`purchase_id`) REFERENCES `purchases` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `purchase_items`
--

LOCK TABLES `purchase_items` WRITE;
/*!40000 ALTER TABLE `purchase_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `purchase_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `purchases`
--

DROP TABLE IF EXISTS `purchases`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `purchases` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `purchase_number` varchar(255) NOT NULL,
  `supplier_id` bigint(20) unsigned NOT NULL,
  `warehouse_id` bigint(20) unsigned NOT NULL,
  `purchase_date` date NOT NULL,
  `expected_delivery_date` date DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `tax_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `shipping_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `discount_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `adjustment_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `final_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `payment_terms` varchar(255) DEFAULT NULL,
  `payment_method` varchar(255) DEFAULT NULL,
  `purchase_status` varchar(255) NOT NULL DEFAULT 'draft',
  `payment_status` varchar(255) NOT NULL DEFAULT 'unpaid',
  `inspection_status` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `approved_by` bigint(20) unsigned DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `rejected_by` bigint(20) unsigned DEFAULT NULL,
  `rejected_at` timestamp NULL DEFAULT NULL,
  `received_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `purchases_purchase_number_unique` (`purchase_number`),
  KEY `purchases_supplier_id_foreign` (`supplier_id`),
  KEY `purchases_warehouse_id_foreign` (`warehouse_id`),
  KEY `purchases_approved_by_foreign` (`approved_by`),
  KEY `purchases_rejected_by_foreign` (`rejected_by`),
  KEY `purchases_purchase_number_index` (`purchase_number`),
  KEY `purchases_purchase_date_index` (`purchase_date`),
  KEY `purchases_expected_delivery_date_index` (`expected_delivery_date`),
  KEY `purchases_payment_status_index` (`payment_status`),
  KEY `purchases_purchase_status_index` (`purchase_status`),
  KEY `purchases_inspection_status_index` (`inspection_status`),
  CONSTRAINT `purchases_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`),
  CONSTRAINT `purchases_rejected_by_foreign` FOREIGN KEY (`rejected_by`) REFERENCES `users` (`id`),
  CONSTRAINT `purchases_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`),
  CONSTRAINT `purchases_warehouse_id_foreign` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `purchases`
--

LOCK TABLES `purchases` WRITE;
/*!40000 ALTER TABLE `purchases` DISABLE KEYS */;
/*!40000 ALTER TABLE `purchases` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `quality_inspection_items`
--

DROP TABLE IF EXISTS `quality_inspection_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quality_inspection_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `quality_inspection_id` bigint(20) unsigned NOT NULL,
  `purchase_item_id` bigint(20) unsigned NOT NULL,
  `inspected_quantity` decimal(10,2) NOT NULL,
  `passed_quantity` decimal(10,2) NOT NULL,
  `failed_quantity` decimal(10,2) NOT NULL,
  `defect_description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `quality_inspection_items_purchase_item_id_foreign` (`purchase_item_id`),
  KEY `qi_items_inspection_item_index` (`quality_inspection_id`,`purchase_item_id`),
  CONSTRAINT `quality_inspection_items_purchase_item_id_foreign` FOREIGN KEY (`purchase_item_id`) REFERENCES `purchase_items` (`id`),
  CONSTRAINT `quality_inspection_items_quality_inspection_id_foreign` FOREIGN KEY (`quality_inspection_id`) REFERENCES `quality_inspections` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `quality_inspection_items`
--

LOCK TABLES `quality_inspection_items` WRITE;
/*!40000 ALTER TABLE `quality_inspection_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `quality_inspection_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `quality_inspections`
--

DROP TABLE IF EXISTS `quality_inspections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quality_inspections` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `purchase_id` bigint(20) unsigned NOT NULL,
  `inspector_id` bigint(20) unsigned NOT NULL,
  `inspection_number` varchar(255) NOT NULL,
  `inspection_date` date NOT NULL,
  `status` enum('pending','passed','failed') NOT NULL DEFAULT 'pending',
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `quality_inspections_inspection_number_unique` (`inspection_number`),
  KEY `quality_inspections_purchase_id_foreign` (`purchase_id`),
  KEY `quality_inspections_inspector_id_foreign` (`inspector_id`),
  KEY `quality_inspections_inspection_number_index` (`inspection_number`),
  KEY `quality_inspections_inspection_date_index` (`inspection_date`),
  KEY `quality_inspections_status_index` (`status`),
  CONSTRAINT `quality_inspections_inspector_id_foreign` FOREIGN KEY (`inspector_id`) REFERENCES `users` (`id`),
  CONSTRAINT `quality_inspections_purchase_id_foreign` FOREIGN KEY (`purchase_id`) REFERENCES `purchases` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `quality_inspections`
--

LOCK TABLES `quality_inspections` WRITE;
/*!40000 ALTER TABLE `quality_inspections` DISABLE KEYS */;
/*!40000 ALTER TABLE `quality_inspections` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_has_permissions`
--

DROP TABLE IF EXISTS `role_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `role_has_permissions` (
  `permission_id` bigint(20) unsigned NOT NULL,
  `role_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`),
  CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_has_permissions`
--

LOCK TABLES `role_has_permissions` WRITE;
/*!40000 ALTER TABLE `role_has_permissions` DISABLE KEYS */;
INSERT INTO `role_has_permissions` VALUES (1,1),(1,3),(2,1),(3,1),(4,1),(5,1),(6,1),(6,3),(7,1),(7,3),(8,1),(9,1),(10,1),(11,1),(11,3),(12,1),(12,3),(13,1),(14,1),(15,1),(16,2),(16,4),(17,4),(18,4),(19,4),(20,2),(20,4),(20,5),(20,6),(20,7),(21,2),(21,4),(21,5),(21,6),(22,2),(22,4),(22,5),(23,4),(24,2),(24,4),(24,5),(24,6),(25,2),(25,4),(25,5),(26,2),(26,4),(26,5),(27,4),(28,2),(28,4),(28,5),(28,6),(28,7),(28,8),(29,2),(29,4),(30,2),(30,4),(31,4),(32,2),(32,4),(32,5),(32,6),(33,2),(33,4),(33,5),(33,6),(34,2),(34,4),(35,4),(36,2),(36,4),(36,8),(37,2),(37,4),(37,6),(37,7),(37,8),(38,2),(38,4),(38,8),(39,2),(39,4),(39,8),(40,2),(40,4),(40,8),(41,2),(41,4),(42,2),(42,4),(42,8),(43,2),(43,4),(43,6),(43,7),(43,8),(44,2),(44,4),(44,8),(45,2),(45,4),(45,8),(46,2),(46,4),(47,2),(47,4),(47,8),(48,2),(48,4),(48,8),(49,2),(49,4),(49,8),(50,2),(50,4),(50,8),(51,2),(51,4),(51,8),(52,2),(52,4),(53,2),(53,4),(53,8),(54,2),(54,4),(55,2),(55,4),(56,4),(57,2),(57,4),(58,2),(58,4),(59,2),(59,4),(60,2),(60,4),(61,2),(61,4),(62,4),(63,4),(64,4),(65,4),(66,4),(67,4),(68,2),(68,4),(69,4),(70,4),(71,2),(71,4),(71,8),(72,2),(72,4),(72,8),(73,2),(73,4),(73,8),(74,4),(75,2),(75,4),(76,2),(76,4);
/*!40000 ALTER TABLE `role_has_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'admin','web',NULL,'2025-02-06 07:33:24','2025-02-06 07:33:24'),(2,'manager','web',NULL,'2025-02-06 07:33:24','2025-02-06 07:33:24'),(3,'staff','web',NULL,'2025-02-06 07:33:24','2025-02-06 07:33:24'),(4,'super-admin','web',NULL,'2025-02-06 07:33:26','2025-02-06 07:33:26'),(5,'optometrist','web',NULL,'2025-02-06 07:33:26','2025-02-06 07:33:26'),(6,'sales','web',NULL,'2025-02-06 07:33:26','2025-02-06 07:33:26'),(7,'cashier','web',NULL,'2025-02-06 07:33:26','2025-02-06 07:33:26'),(8,'stockkeeper','web',NULL,'2025-02-06 07:33:26','2025-02-06 07:33:26');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sales_order_items`
--

DROP TABLE IF EXISTS `sales_order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sales_order_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sales_order_id` bigint(20) unsigned NOT NULL,
  `product_id` bigint(20) unsigned NOT NULL,
  `product_name` varchar(255) NOT NULL COMMENT '商品名称',
  `product_sku` varchar(255) NOT NULL COMMENT '商品SKU',
  `quantity` int(11) NOT NULL COMMENT '数量',
  `unit_price` decimal(10,2) NOT NULL COMMENT '单价',
  `discount_amount` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '折扣金额',
  `tax_rate` decimal(5,2) NOT NULL DEFAULT 0.00 COMMENT '税率',
  `tax_amount` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '税额',
  `subtotal` decimal(10,2) NOT NULL COMMENT '小计',
  `specifications` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT '规格信息' CHECK (json_valid(`specifications`)),
  `remarks` text DEFAULT NULL COMMENT '备注',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sales_order_items_sales_order_id_foreign` (`sales_order_id`),
  KEY `sales_order_items_product_id_foreign` (`product_id`),
  CONSTRAINT `sales_order_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  CONSTRAINT `sales_order_items_sales_order_id_foreign` FOREIGN KEY (`sales_order_id`) REFERENCES `sales_orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sales_order_items`
--

LOCK TABLES `sales_order_items` WRITE;
/*!40000 ALTER TABLE `sales_order_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `sales_order_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sales_orders`
--

DROP TABLE IF EXISTS `sales_orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sales_orders` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_number` varchar(255) NOT NULL,
  `customer_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `prescription_id` bigint(20) unsigned DEFAULT NULL,
  `subtotal` decimal(10,2) NOT NULL COMMENT '小计',
  `tax_amount` decimal(10,2) NOT NULL COMMENT '税额',
  `discount_amount` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '折扣金额',
  `total_amount` decimal(10,2) NOT NULL COMMENT '总金额',
  `status` varchar(255) NOT NULL DEFAULT 'pending' COMMENT '订单状态',
  `payment_status` varchar(255) NOT NULL DEFAULT 'unpaid' COMMENT '支付状态',
  `paid_at` timestamp NULL DEFAULT NULL COMMENT '支付时间',
  `payment_method` varchar(255) DEFAULT NULL COMMENT '支付方式',
  `remarks` text DEFAULT NULL COMMENT '备注',
  `additional_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT '额外数据' CHECK (json_valid(`additional_data`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sales_orders_order_number_unique` (`order_number`),
  KEY `sales_orders_customer_id_foreign` (`customer_id`),
  KEY `sales_orders_user_id_foreign` (`user_id`),
  KEY `sales_orders_prescription_id_foreign` (`prescription_id`),
  CONSTRAINT `sales_orders_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  CONSTRAINT `sales_orders_prescription_id_foreign` FOREIGN KEY (`prescription_id`) REFERENCES `prescriptions` (`id`),
  CONSTRAINT `sales_orders_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sales_orders`
--

LOCK TABLES `sales_orders` WRITE;
/*!40000 ALTER TABLE `sales_orders` DISABLE KEYS */;
/*!40000 ALTER TABLE `sales_orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stock_movements`
--

DROP TABLE IF EXISTS `stock_movements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stock_movements` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint(20) unsigned NOT NULL,
  `warehouse_id` bigint(20) unsigned NOT NULL,
  `reference_type` varchar(255) NOT NULL,
  `reference_id` bigint(20) unsigned NOT NULL,
  `movement_type` varchar(255) NOT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `unit_cost` decimal(10,2) NOT NULL,
  `total_cost` decimal(10,2) NOT NULL,
  `batch_number` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `stock_movements_product_id_foreign` (`product_id`),
  KEY `stock_movements_warehouse_id_foreign` (`warehouse_id`),
  KEY `stock_movements_reference_type_reference_id_index` (`reference_type`,`reference_id`),
  KEY `stock_movements_movement_type_index` (`movement_type`),
  KEY `stock_movements_batch_number_index` (`batch_number`),
  KEY `stock_movements_created_at_index` (`created_at`),
  CONSTRAINT `stock_movements_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  CONSTRAINT `stock_movements_warehouse_id_foreign` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stock_movements`
--

LOCK TABLES `stock_movements` WRITE;
/*!40000 ALTER TABLE `stock_movements` DISABLE KEYS */;
/*!40000 ALTER TABLE `stock_movements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stock_transfer_items`
--

DROP TABLE IF EXISTS `stock_transfer_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stock_transfer_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `stock_transfer_id` bigint(20) unsigned NOT NULL,
  `product_id` bigint(20) unsigned NOT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `stock_transfer_items_product_id_foreign` (`product_id`),
  KEY `stock_transfer_items_stock_transfer_id_product_id_index` (`stock_transfer_id`,`product_id`),
  CONSTRAINT `stock_transfer_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  CONSTRAINT `stock_transfer_items_stock_transfer_id_foreign` FOREIGN KEY (`stock_transfer_id`) REFERENCES `stock_transfers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stock_transfer_items`
--

LOCK TABLES `stock_transfer_items` WRITE;
/*!40000 ALTER TABLE `stock_transfer_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `stock_transfer_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stock_transfers`
--

DROP TABLE IF EXISTS `stock_transfers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stock_transfers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `from_warehouse_id` bigint(20) unsigned NOT NULL,
  `to_warehouse_id` bigint(20) unsigned NOT NULL,
  `transfer_date` date NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `stock_transfers_from_warehouse_id_foreign` (`from_warehouse_id`),
  KEY `stock_transfers_to_warehouse_id_foreign` (`to_warehouse_id`),
  KEY `stock_transfers_transfer_date_index` (`transfer_date`),
  KEY `stock_transfers_status_index` (`status`),
  CONSTRAINT `stock_transfers_from_warehouse_id_foreign` FOREIGN KEY (`from_warehouse_id`) REFERENCES `warehouses` (`id`),
  CONSTRAINT `stock_transfers_to_warehouse_id_foreign` FOREIGN KEY (`to_warehouse_id`) REFERENCES `warehouses` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stock_transfers`
--

LOCK TABLES `stock_transfers` WRITE;
/*!40000 ALTER TABLE `stock_transfers` DISABLE KEYS */;
/*!40000 ALTER TABLE `stock_transfers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `supplier_contacts`
--

DROP TABLE IF EXISTS `supplier_contacts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `supplier_contacts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `supplier_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL COMMENT '联系人姓名',
  `position` varchar(255) DEFAULT NULL COMMENT '职位',
  `phone` varchar(255) DEFAULT NULL COMMENT '联系电话',
  `email` varchar(255) DEFAULT NULL COMMENT '电子邮箱',
  `is_primary` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否主要联系人',
  `remarks` text DEFAULT NULL COMMENT '备注',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `supplier_contacts_supplier_id_foreign` (`supplier_id`),
  CONSTRAINT `supplier_contacts_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `supplier_contacts`
--

LOCK TABLES `supplier_contacts` WRITE;
/*!40000 ALTER TABLE `supplier_contacts` DISABLE KEYS */;
/*!40000 ALTER TABLE `supplier_contacts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `supplier_price_agreements`
--

DROP TABLE IF EXISTS `supplier_price_agreements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `supplier_price_agreements` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `supplier_id` bigint(20) unsigned NOT NULL,
  `product_id` bigint(20) unsigned NOT NULL,
  `price` decimal(10,2) NOT NULL COMMENT '协议价格',
  `start_date` date NOT NULL COMMENT '生效日期',
  `end_date` date DEFAULT NULL COMMENT '失效日期',
  `min_quantity` int(11) NOT NULL DEFAULT 1 COMMENT '最小数量',
  `discount_rate` decimal(5,2) DEFAULT NULL COMMENT '折扣率',
  `terms` text DEFAULT NULL COMMENT '协议条款',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `supplier_price_agreements_supplier_id_foreign` (`supplier_id`),
  KEY `supplier_price_agreements_product_id_foreign` (`product_id`),
  CONSTRAINT `supplier_price_agreements_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `supplier_price_agreements_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `supplier_price_agreements`
--

LOCK TABLES `supplier_price_agreements` WRITE;
/*!40000 ALTER TABLE `supplier_price_agreements` DISABLE KEYS */;
/*!40000 ALTER TABLE `supplier_price_agreements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `supplier_products`
--

DROP TABLE IF EXISTS `supplier_products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `supplier_products` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `supplier_id` bigint(20) unsigned NOT NULL,
  `product_id` bigint(20) unsigned NOT NULL,
  `supplier_product_code` varchar(255) DEFAULT NULL COMMENT '供应商产品编号',
  `purchase_price` decimal(10,2) NOT NULL COMMENT '采购价格',
  `min_order_quantity` int(11) NOT NULL DEFAULT 1 COMMENT '最小订购数量',
  `lead_time` int(11) NOT NULL DEFAULT 7 COMMENT '交货周期(天)',
  `is_preferred` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否首选供应商',
  `tax_rate` decimal(5,2) NOT NULL DEFAULT 0.00 COMMENT '税率',
  `remarks` text DEFAULT NULL COMMENT '备注',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `supplier_products_supplier_id_product_id_unique` (`supplier_id`,`product_id`),
  KEY `supplier_products_product_id_foreign` (`product_id`),
  CONSTRAINT `supplier_products_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `supplier_products_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `supplier_products`
--

LOCK TABLES `supplier_products` WRITE;
/*!40000 ALTER TABLE `supplier_products` DISABLE KEYS */;
/*!40000 ALTER TABLE `supplier_products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `suppliers`
--

DROP TABLE IF EXISTS `suppliers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `suppliers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(255) NOT NULL COMMENT '供应商编号',
  `name` varchar(255) NOT NULL COMMENT '供应商名称',
  `contact_person` varchar(255) DEFAULT NULL COMMENT '联系人',
  `phone` varchar(255) DEFAULT NULL COMMENT '联系电话',
  `email` varchar(255) DEFAULT NULL COMMENT '电子邮箱',
  `address` varchar(255) DEFAULT NULL COMMENT '地址',
  `credit_limit` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '信用额度',
  `payment_term` int(11) NOT NULL DEFAULT 30 COMMENT '账期(天)',
  `remarks` text DEFAULT NULL COMMENT '备注',
  `is_active` tinyint(1) NOT NULL DEFAULT 1 COMMENT '是否启用',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `suppliers_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `suppliers`
--

LOCK TABLES `suppliers` WRITE;
/*!40000 ALTER TABLE `suppliers` DISABLE KEYS */;
/*!40000 ALTER TABLE `suppliers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `employee_id` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `avatar` varchar(255) DEFAULT NULL,
  `settings` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`settings`)),
  `last_login_at` timestamp NULL DEFAULT NULL,
  `last_login_ip` varchar(255) DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_employee_id_unique` (`employee_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Admin','optik@gmail.com',NULL,'EMP001',NULL,'$2y$12$3PXLDQ/4QpwXjs2nPjlmUuCUQQePDR7ioWBQQBsgZvG.UrmXn.YaS',1,NULL,NULL,NULL,NULL,NULL,'2025-02-06 07:33:27','2025-02-06 07:33:27',NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `warehouses`
--

DROP TABLE IF EXISTS `warehouses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `warehouses` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL,
  `name` varchar(255) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `contact_person` varchar(50) DEFAULT NULL,
  `contact_phone` varchar(20) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `warehouses_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `warehouses`
--

LOCK TABLES `warehouses` WRITE;
/*!40000 ALTER TABLE `warehouses` DISABLE KEYS */;
/*!40000 ALTER TABLE `warehouses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `whatsapp_notifications`
--

DROP TABLE IF EXISTS `whatsapp_notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `whatsapp_notifications` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `notifiable_type` varchar(255) NOT NULL,
  `notifiable_id` bigint(20) unsigned NOT NULL,
  `template_name` varchar(255) NOT NULL,
  `phone_number` varchar(255) NOT NULL,
  `message_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`message_data`)),
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `message_id` varchar(255) DEFAULT NULL,
  `error_message` text DEFAULT NULL,
  `sent_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `whatsapp_notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `whatsapp_notifications`
--

LOCK TABLES `whatsapp_notifications` WRITE;
/*!40000 ALTER TABLE `whatsapp_notifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `whatsapp_notifications` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-02-07  0:05:57
