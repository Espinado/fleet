/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `clients`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `clients` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `reg_nr` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `representative` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `jur_country_id` int unsigned DEFAULT NULL,
  `jur_city_id` int unsigned DEFAULT NULL,
  `jur_address` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `jur_post_code` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fiz_country_id` int unsigned DEFAULT NULL,
  `fiz_city_id` int unsigned DEFAULT NULL,
  `fiz_address` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fiz_post_code` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `swift` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `drivers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `drivers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company` int unsigned DEFAULT NULL,
  `first_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `pers_code` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `citizenship_id` smallint unsigned DEFAULT NULL,
  `declared_country_id` smallint unsigned DEFAULT NULL,
  `declared_city_id` smallint unsigned DEFAULT NULL,
  `actual_country_id` smallint unsigned DEFAULT NULL,
  `actual_city_id` smallint unsigned DEFAULT NULL,
  `declared_street` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `declared_building` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `declared_room` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `declared_postcode` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `actual_street` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `actual_building` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `actual_room` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `actual_postcode` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `license_number` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `license_issued` date NOT NULL,
  `license_end` date NOT NULL,
  `code95_issued` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `code95_end` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `permit_issued` date DEFAULT NULL,
  `permit_expired` date DEFAULT NULL,
  `medical_issued` date DEFAULT NULL,
  `medical_expired` date DEFAULT NULL,
  `declaration_issued` date DEFAULT NULL,
  `declaration_expired` date DEFAULT NULL,
  `photo` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `license_photo` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `medical_certificate_photo` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `medical_exam_passed` date DEFAULT NULL,
  `medical_exam_expired` date DEFAULT NULL,
  `status` tinyint NOT NULL DEFAULT '1',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `trailers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `trailers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company` int unsigned DEFAULT NULL,
  `brand` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `plate` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `year` year NOT NULL,
  `vin` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tech_passport_nr` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tech_passport_issued` date DEFAULT NULL,
  `tech_passport_expired` date DEFAULT NULL,
  `tech_passport_photo` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `inspection_issued` date DEFAULT NULL,
  `inspection_expired` date DEFAULT NULL,
  `insurance_number` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `insurance_company` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `insurance_issued` date DEFAULT NULL,
  `insurance_expired` date DEFAULT NULL,
  `tir_issued` date DEFAULT NULL,
  `tir_expired` date DEFAULT NULL,
  `status` int NOT NULL DEFAULT '1',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `trailers_plate_unique` (`plate`),
  UNIQUE KEY `trailers_vin_unique` (`vin`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `trip_cargo_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `trip_cargo_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `trip_cargo_id` bigint unsigned NOT NULL,
  `description` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `packages` int unsigned NOT NULL DEFAULT '0',
  `cargo_paletes` decimal(10,2) NOT NULL DEFAULT '0.00',
  `cargo_tonnes` decimal(10,2) NOT NULL DEFAULT '0.00',
  `weight` decimal(10,2) NOT NULL DEFAULT '0.00',
  `cargo_netto_weight` decimal(10,2) NOT NULL DEFAULT '0.00',
  `volume` decimal(10,2) NOT NULL DEFAULT '0.00',
  `price` decimal(12,2) NOT NULL DEFAULT '0.00',
  `tax_percent` decimal(5,2) NOT NULL DEFAULT '0.00',
  `tax_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `price_with_tax` decimal(12,2) NOT NULL DEFAULT '0.00',
  `instructions` text COLLATE utf8mb4_unicode_ci,
  `remarks` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `trip_cargo_items_trip_cargo_id_foreign` (`trip_cargo_id`),
  CONSTRAINT `trip_cargo_items_trip_cargo_id_foreign` FOREIGN KEY (`trip_cargo_id`) REFERENCES `trip_cargos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `trip_cargos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `trip_cargos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `trip_id` bigint unsigned NOT NULL,
  `customer_id` bigint unsigned DEFAULT NULL,
  `shipper_id` bigint unsigned DEFAULT NULL,
  `consignee_id` bigint unsigned DEFAULT NULL,
  `loading_country_id` smallint unsigned DEFAULT NULL,
  `loading_city_id` smallint unsigned DEFAULT NULL,
  `loading_address` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `loading_date` date DEFAULT NULL,
  `unloading_country_id` smallint unsigned DEFAULT NULL,
  `unloading_city_id` smallint unsigned DEFAULT NULL,
  `unloading_address` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `unloading_date` date DEFAULT NULL,
  `cargo_description` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cargo_packages` int DEFAULT NULL,
  `cargo_weight` decimal(10,2) DEFAULT NULL,
  `cargo_volume` decimal(10,2) DEFAULT NULL,
  `cargo_marks` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cargo_instructions` text COLLATE utf8mb4_unicode_ci,
  `cargo_remarks` text COLLATE utf8mb4_unicode_ci,
  `price` decimal(10,2) DEFAULT NULL,
  `total_tax_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `price_with_tax` decimal(10,2) NOT NULL DEFAULT '0.00',
  `currency` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'EUR',
  `payment_terms` date DEFAULT NULL,
  `payer_type_id` tinyint unsigned DEFAULT NULL,
  `items_json` json DEFAULT NULL,
  `cmr_file` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cmr_nr` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `order_file` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `order_created_at` timestamp NULL DEFAULT NULL,
  `order_nr` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cmr_created_at` timestamp NULL DEFAULT NULL,
  `inv_nr` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `inv_file` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `inv_created_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `cargo_paletes` int DEFAULT NULL,
  `cargo_netto_weight` decimal(10,2) DEFAULT NULL,
  `cargo_tonnes` decimal(10,2) DEFAULT NULL,
  `tax_percent` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `trip_cargos_trip_id_foreign` (`trip_id`),
  KEY `trip_cargos_shipper_id_foreign` (`shipper_id`),
  KEY `trip_cargos_consignee_id_foreign` (`consignee_id`),
  KEY `trip_cargos_customer_id_foreign` (`customer_id`),
  CONSTRAINT `trip_cargos_consignee_id_foreign` FOREIGN KEY (`consignee_id`) REFERENCES `clients` (`id`) ON DELETE SET NULL,
  CONSTRAINT `trip_cargos_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `clients` (`id`) ON DELETE SET NULL,
  CONSTRAINT `trip_cargos_shipper_id_foreign` FOREIGN KEY (`shipper_id`) REFERENCES `clients` (`id`) ON DELETE SET NULL,
  CONSTRAINT `trip_cargos_trip_id_foreign` FOREIGN KEY (`trip_id`) REFERENCES `trips` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `trip_documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `trip_documents` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `trip_id` bigint unsigned NOT NULL,
  `type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_path` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `uploaded_by` bigint unsigned DEFAULT NULL,
  `uploaded_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `trip_documents_trip_id_foreign` (`trip_id`),
  KEY `trip_documents_uploaded_by_foreign` (`uploaded_by`),
  KEY `trip_documents_type_index` (`type`),
  CONSTRAINT `trip_documents_trip_id_foreign` FOREIGN KEY (`trip_id`) REFERENCES `trips` (`id`) ON DELETE CASCADE,
  CONSTRAINT `trip_documents_uploaded_by_foreign` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `trip_expenses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `trip_expenses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `trip_id` bigint unsigned NOT NULL,
  `category` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'EUR',
  `file_path` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `expense_date` date DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `trip_expenses_trip_id_foreign` (`trip_id`),
  KEY `trip_expenses_created_by_foreign` (`created_by`),
  KEY `trip_expenses_category_index` (`category`),
  CONSTRAINT `trip_expenses_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `trip_expenses_trip_id_foreign` FOREIGN KEY (`trip_id`) REFERENCES `trips` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `trips`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `trips` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `expeditor_id` bigint unsigned NOT NULL,
  `expeditor_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expeditor_reg_nr` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `expeditor_country` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `expeditor_city` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `expeditor_address` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `expeditor_post_code` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `expeditor_email` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `expeditor_phone` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `expeditor_bank_id` tinyint unsigned DEFAULT NULL,
  `expeditor_bank` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `expeditor_iban` varchar(34) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `expeditor_bic` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `driver_id` bigint unsigned NOT NULL,
  `truck_id` bigint unsigned NOT NULL,
  `trailer_id` bigint unsigned DEFAULT NULL,
  `shipper_id` bigint unsigned DEFAULT NULL,
  `consignee_id` bigint unsigned DEFAULT NULL,
  `origin_country_id` smallint unsigned DEFAULT NULL,
  `origin_city_id` smallint unsigned DEFAULT NULL,
  `origin_address` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `destination_country_id` smallint unsigned DEFAULT NULL,
  `destination_city_id` smallint unsigned DEFAULT NULL,
  `destination_address` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `cargo_description` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cargo_packages` int DEFAULT NULL,
  `cargo_weight` decimal(10,2) DEFAULT NULL,
  `cargo_volume` decimal(10,2) DEFAULT NULL,
  `cargo_marks` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cargo_instructions` text COLLATE utf8mb4_unicode_ci,
  `cargo_remarks` text COLLATE utf8mb4_unicode_ci,
  `price` decimal(10,2) DEFAULT NULL,
  `currency` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'EUR',
  `payment_terms` date DEFAULT NULL,
  `payer_type_id` tinyint unsigned DEFAULT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'planned',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `trips_driver_id_foreign` (`driver_id`),
  KEY `trips_truck_id_foreign` (`truck_id`),
  KEY `trips_trailer_id_foreign` (`trailer_id`),
  KEY `trips_shipper_id_foreign` (`shipper_id`),
  KEY `trips_consignee_id_foreign` (`consignee_id`),
  CONSTRAINT `trips_consignee_id_foreign` FOREIGN KEY (`consignee_id`) REFERENCES `clients` (`id`) ON DELETE SET NULL,
  CONSTRAINT `trips_driver_id_foreign` FOREIGN KEY (`driver_id`) REFERENCES `drivers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `trips_shipper_id_foreign` FOREIGN KEY (`shipper_id`) REFERENCES `clients` (`id`) ON DELETE SET NULL,
  CONSTRAINT `trips_trailer_id_foreign` FOREIGN KEY (`trailer_id`) REFERENCES `trailers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `trips_truck_id_foreign` FOREIGN KEY (`truck_id`) REFERENCES `trucks` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `trucks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `trucks` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company` int unsigned DEFAULT NULL,
  `brand` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `plate` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `year` year NOT NULL,
  `vin` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tech_passport_nr` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tech_passport_issued` date DEFAULT NULL,
  `tech_passport_expired` date DEFAULT NULL,
  `tech_passport_photo` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `inspection_issued` date DEFAULT NULL,
  `inspection_expired` date DEFAULT NULL,
  `insurance_number` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `insurance_company` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `insurance_issued` date DEFAULT NULL,
  `insurance_expired` date DEFAULT NULL,
  `status` int NOT NULL DEFAULT '1',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `trucks_plate_unique` (`plate`),
  UNIQUE KEY `trucks_vin_unique` (`vin`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (1,'0001_01_01_000000_create_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (2,'0001_01_01_000001_create_cache_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (3,'0001_01_01_000002_create_jobs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (4,'2025_10_25_170902_refactor_trips_table_structure',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (5,'2025_10_29_212423_create_drivers_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (6,'2025_10_29_212524_create_trucks_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (7,'2025_10_29_212604_create_trailers_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (8,'2025_10_29_212716_create_clients_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (9,'2025_10_29_213034_create_trips_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (10,'2025_10_29_213139_create_trip_cargos_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (11,'2025_10_30_223437_refactor_trips_table_structure_fix',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (12,'2025_11_01_160526_add_fields_to_trip_cargos_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (13,'2025_11_01_171128_add_tax_fields_to_trip_cargos_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (14,'2025_11_01_172127_add_tax_additional_fields_to_trip_cargos_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (15,'2025_11_01_183050_create_trip_cargo_items_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (16,'2025_11_02_205921_add_order_and_cmr_fields_to_trip_cargos_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (17,'2025_11_03_091749_add_customer_id_to_trip_cargos_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (18,'2025_11_04_204536_add_invoice_fields_to_trip_cargos_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (19,'2025_11_09_102728_create_trip_documents_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (20,'2025_11_09_102737_create_trip_expenses_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (21,'2025_11_09_150948_add_expeditor_bank_fields_to_trips_table',3);
