-- MySQL dump 10.13  Distrib 8.4.3, for Win64 (x86_64)
--
-- Host: localhost    Database: fleet
-- ------------------------------------------------------
-- Server version	8.4.3

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `cache`
--

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

--
-- Table structure for table `cache_locks`
--

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

--
-- Table structure for table `clients`
--

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
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `companies`
--

DROP TABLE IF EXISTS `companies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `companies` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `slug` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'carrier',
  `reg_nr` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vat_nr` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `post_code` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `banks_json` json DEFAULT NULL,
  `is_system` tinyint(1) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `companies_slug_unique` (`slug`),
  KEY `companies_type_index` (`type`),
  KEY `companies_reg_nr_index` (`reg_nr`),
  KEY `companies_vat_nr_index` (`vat_nr`),
  KEY `companies_is_system_index` (`is_system`),
  KEY `companies_is_active_index` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `drivers`
--

DROP TABLE IF EXISTS `drivers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `drivers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint unsigned DEFAULT NULL,
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
  `login_pin` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
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
  PRIMARY KEY (`id`),
  KEY `drivers_user_id_foreign` (`user_id`),
  KEY `drivers_company_id_index` (`company_id`),
  CONSTRAINT `drivers_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL,
  CONSTRAINT `drivers_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `failed_jobs`
--

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

--
-- Table structure for table `invoice_payments`
--

DROP TABLE IF EXISTS `invoice_payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `invoice_payments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `invoice_id` bigint unsigned NOT NULL,
  `paid_at` date NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `currency` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'EUR',
  `method` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reference` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `note` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `invoice_payments_invoice_id_paid_at_index` (`invoice_id`,`paid_at`),
  CONSTRAINT `invoice_payments_invoice_id_foreign` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `invoices`
--

DROP TABLE IF EXISTS `invoices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `invoices` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `trip_id` bigint unsigned NOT NULL,
  `trip_cargo_id` bigint unsigned NOT NULL,
  `invoice_no` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `issued_at` timestamp NULL DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `payer_type_id` tinyint unsigned DEFAULT NULL,
  `payer_client_id` bigint unsigned DEFAULT NULL,
  `currency` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'EUR',
  `subtotal` decimal(12,2) NOT NULL DEFAULT '0.00',
  `tax_percent` decimal(8,2) DEFAULT NULL,
  `tax_total` decimal(12,2) NOT NULL DEFAULT '0.00',
  `total` decimal(12,2) NOT NULL DEFAULT '0.00',
  `pdf_file` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `invoices_trip_cargo_id_unique` (`trip_cargo_id`),
  KEY `invoices_trip_id_foreign` (`trip_id`),
  KEY `invoices_payer_client_id_foreign` (`payer_client_id`),
  KEY `invoices_invoice_no_index` (`invoice_no`),
  CONSTRAINT `invoices_payer_client_id_foreign` FOREIGN KEY (`payer_client_id`) REFERENCES `clients` (`id`) ON DELETE SET NULL,
  CONSTRAINT `invoices_trip_cargo_id_foreign` FOREIGN KEY (`trip_cargo_id`) REFERENCES `trip_cargos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `invoices_trip_id_foreign` FOREIGN KEY (`trip_id`) REFERENCES `trips` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `job_batches`
--

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

--
-- Table structure for table `jobs`
--

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

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=65 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `odometer_events`
--

DROP TABLE IF EXISTS `odometer_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `odometer_events` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `truck_id` bigint unsigned NOT NULL,
  `vehicle_run_id` bigint unsigned DEFAULT NULL,
  `trip_id` bigint unsigned DEFAULT NULL,
  `trip_step_id` bigint unsigned DEFAULT NULL,
  `event_type` tinyint unsigned NOT NULL,
  `event_at` datetime NOT NULL,
  `can_odom_km` decimal(12,1) DEFAULT NULL,
  `can_at` datetime DEFAULT NULL,
  `source` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'can',
  `is_stale` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `odometer_events_truck_id_event_at_index` (`truck_id`,`event_at`),
  KEY `odometer_events_vehicle_run_id_event_type_index` (`vehicle_run_id`,`event_type`),
  KEY `odometer_events_trip_id_event_type_index` (`trip_id`,`event_type`),
  KEY `odometer_events_trip_step_id_event_type_index` (`trip_step_id`,`event_type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `password_reset_tokens`
--

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

--
-- Table structure for table `push_subscriptions`
--

DROP TABLE IF EXISTS `push_subscriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `push_subscriptions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `subscribable_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subscribable_id` bigint unsigned NOT NULL,
  `endpoint` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `public_key` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `auth_token` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content_encoding` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `push_subscriptions_endpoint_unique` (`endpoint`),
  KEY `push_subscriptions_subscribable_morph_idx` (`subscribable_type`,`subscribable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sessions`
--

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

--
-- Table structure for table `trailers`
--

DROP TABLE IF EXISTS `trailers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `trailers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int unsigned DEFAULT NULL,
  `brand` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `plate` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type_id` tinyint unsigned NOT NULL DEFAULT '1',
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
  UNIQUE KEY `trailers_vin_unique` (`vin`),
  KEY `trailers_type_id_index` (`type_id`)
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `trip_cargo_items`
--

DROP TABLE IF EXISTS `trip_cargo_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `trip_cargo_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `trip_cargo_id` bigint unsigned NOT NULL,
  `description` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `customs_code` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `packages` int unsigned NOT NULL DEFAULT '0',
  `pallets` int DEFAULT NULL,
  `units` int DEFAULT NULL,
  `gross_weight` decimal(10,2) DEFAULT NULL,
  `net_weight` decimal(10,2) DEFAULT NULL,
  `tonnes` decimal(10,3) DEFAULT NULL,
  `volume` decimal(10,2) NOT NULL DEFAULT '0.00',
  `loading_meters` decimal(10,2) DEFAULT NULL,
  `hazmat` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `temperature` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `stackable` tinyint(1) NOT NULL DEFAULT '0',
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
  KEY `tci_cargo_idx` (`trip_cargo_id`),
  CONSTRAINT `tci_cargo_fk` FOREIGN KEY (`trip_cargo_id`) REFERENCES `trip_cargos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `trip_cargo_items_trip_cargo_id_foreign` FOREIGN KEY (`trip_cargo_id`) REFERENCES `trip_cargos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=65 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `trip_cargo_step`
--

DROP TABLE IF EXISTS `trip_cargo_step`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `trip_cargo_step` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `trip_step_id` bigint unsigned NOT NULL,
  `role` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'loading|unloading',
  `trip_cargo_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `trip_cargo_step_trip_step_id_trip_cargo_id_unique` (`trip_step_id`,`trip_cargo_id`),
  KEY `trip_cargo_step_trip_cargo_id_foreign` (`trip_cargo_id`),
  CONSTRAINT `trip_cargo_step_trip_cargo_id_foreign` FOREIGN KEY (`trip_cargo_id`) REFERENCES `trip_cargos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `trip_cargo_step_trip_step_id_foreign` FOREIGN KEY (`trip_step_id`) REFERENCES `trip_steps` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=129 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `trip_cargos`
--

DROP TABLE IF EXISTS `trip_cargos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `trip_cargos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `trip_id` bigint unsigned NOT NULL,
  `customer_id` bigint unsigned DEFAULT NULL,
  `shipper_id` bigint unsigned DEFAULT NULL,
  `consignee_id` bigint unsigned DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `total_tax_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `price_with_tax` decimal(10,2) NOT NULL DEFAULT '0.00',
  `currency` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'EUR',
  `payment_terms` date DEFAULT NULL,
  `payer_type_id` tinyint unsigned DEFAULT NULL,
  `supplier_invoice_nr` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `supplier_invoice_amount` decimal(12,2) DEFAULT NULL,
  `items_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
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
  `tax_percent` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `trip_cargos_trip_id_foreign` (`trip_id`),
  KEY `trip_cargos_shipper_id_foreign` (`shipper_id`),
  KEY `trip_cargos_consignee_id_foreign` (`consignee_id`),
  KEY `trip_cargos_customer_id_foreign` (`customer_id`),
  KEY `tc_customer_idx` (`customer_id`),
  KEY `tc_shipper_idx` (`shipper_id`),
  KEY `tc_consignee_idx` (`consignee_id`),
  KEY `trip_cargos_trip_id_idx` (`trip_id`),
  CONSTRAINT `tc_consignee_id_fk` FOREIGN KEY (`consignee_id`) REFERENCES `clients` (`id`) ON DELETE SET NULL,
  CONSTRAINT `tc_customer_id_fk` FOREIGN KEY (`customer_id`) REFERENCES `clients` (`id`) ON DELETE SET NULL,
  CONSTRAINT `tc_shipper_id_fk` FOREIGN KEY (`shipper_id`) REFERENCES `clients` (`id`) ON DELETE SET NULL,
  CONSTRAINT `trip_cargos_trip_id_foreign` FOREIGN KEY (`trip_id`) REFERENCES `trips` (`id`) ON DELETE CASCADE,
  CONSTRAINT `trip_cargos_chk_1` CHECK (json_valid(`items_json`))
) ENGINE=InnoDB AUTO_INCREMENT=52 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `trip_documents`
--

DROP TABLE IF EXISTS `trip_documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `trip_documents` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `trip_id` bigint unsigned NOT NULL,
  `step_id` bigint unsigned DEFAULT NULL,
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
  KEY `trip_documents_step_id_foreign` (`step_id`),
  CONSTRAINT `trip_documents_step_id_foreign` FOREIGN KEY (`step_id`) REFERENCES `trip_steps` (`id`) ON DELETE SET NULL,
  CONSTRAINT `trip_documents_trip_id_foreign` FOREIGN KEY (`trip_id`) REFERENCES `trips` (`id`) ON DELETE CASCADE,
  CONSTRAINT `trip_documents_uploaded_by_foreign` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `trip_expenses`
--

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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `trip_status_history`
--

DROP TABLE IF EXISTS `trip_status_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `trip_status_history` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `trip_id` bigint unsigned NOT NULL,
  `driver_id` bigint unsigned NOT NULL,
  `status` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `time` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `trip_status_history_trip_id_foreign` (`trip_id`),
  KEY `trip_status_history_driver_id_foreign` (`driver_id`),
  CONSTRAINT `trip_status_history_driver_id_foreign` FOREIGN KEY (`driver_id`) REFERENCES `drivers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `trip_status_history_trip_id_foreign` FOREIGN KEY (`trip_id`) REFERENCES `trips` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=110 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `trip_step_documents`
--

DROP TABLE IF EXISTS `trip_step_documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `trip_step_documents` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `trip_step_id` bigint unsigned NOT NULL,
  `trip_id` bigint unsigned DEFAULT NULL,
  `cargo_id` bigint unsigned DEFAULT NULL,
  `uploader_user_id` bigint unsigned DEFAULT NULL,
  `uploader_driver_id` bigint unsigned DEFAULT NULL,
  `type` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_path` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `original_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `comment` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `trip_steps`
--

DROP TABLE IF EXISTS `trip_steps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `trip_steps` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `trip_id` bigint unsigned NOT NULL,
  `type` enum('loading','unloading') COLLATE utf8mb4_unicode_ci NOT NULL,
  `client_id` bigint unsigned DEFAULT NULL,
  `country_id` int unsigned DEFAULT NULL,
  `city_id` int unsigned DEFAULT NULL,
  `address` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date` date DEFAULT NULL,
  `time` time DEFAULT NULL,
  `order` int unsigned DEFAULT NULL COMMENT 'Порядок шага в маршруте, задаваемый админом',
  `status` tinyint unsigned NOT NULL DEFAULT '1',
  `started_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `trip_steps_trip_id_foreign` (`trip_id`),
  KEY `trip_steps_order_index` (`order`),
  KEY `ts_client_idx` (`client_id`),
  CONSTRAINT `trip_steps_trip_id_foreign` FOREIGN KEY (`trip_id`) REFERENCES `trips` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ts_client_fk` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=114 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `trips`
--

DROP TABLE IF EXISTS `trips`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `trips` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `carrier_company_id` bigint unsigned DEFAULT NULL,
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
  `vehicle_run_id` bigint unsigned DEFAULT NULL,
  `trailer_id` bigint unsigned DEFAULT NULL,
  `cont_nr` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `seal_nr` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `currency` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'EUR',
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'planned',
  `started_at` timestamp NULL DEFAULT NULL,
  `ended_at` timestamp NULL DEFAULT NULL,
  `odo_start_km` int unsigned DEFAULT NULL,
  `odo_end_km` int unsigned DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `trips_driver_id_foreign` (`driver_id`),
  KEY `trips_truck_id_foreign` (`truck_id`),
  KEY `trips_trailer_id_foreign` (`trailer_id`),
  KEY `trips_vehicle_run_id_index` (`vehicle_run_id`),
  KEY `trips_carrier_company_id_fk` (`carrier_company_id`),
  CONSTRAINT `trips_carrier_company_id_fk` FOREIGN KEY (`carrier_company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL,
  CONSTRAINT `trips_driver_id_foreign` FOREIGN KEY (`driver_id`) REFERENCES `drivers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `trips_trailer_id_foreign` FOREIGN KEY (`trailer_id`) REFERENCES `trailers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `trips_truck_id_foreign` FOREIGN KEY (`truck_id`) REFERENCES `trucks` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `truck_odometer_events`
--

DROP TABLE IF EXISTS `truck_odometer_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `truck_odometer_events` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `truck_id` bigint unsigned NOT NULL,
  `driver_id` bigint unsigned DEFAULT NULL,
  `type` tinyint unsigned NOT NULL DEFAULT '1',
  `odometer_km` decimal(10,1) DEFAULT NULL,
  `source` tinyint unsigned NOT NULL DEFAULT '1',
  `occurred_at` datetime NOT NULL,
  `mapon_at` datetime DEFAULT NULL,
  `is_stale` tinyint(1) NOT NULL DEFAULT '0',
  `stale_minutes` int unsigned DEFAULT NULL,
  `raw` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `note` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `truck_odometer_events_driver_id_foreign` (`driver_id`),
  KEY `truck_odometer_events_truck_id_occurred_at_index` (`truck_id`,`occurred_at`),
  KEY `toe_truck_driver_type_date_idx` (`truck_id`,`driver_id`,`type`,`occurred_at`),
  CONSTRAINT `truck_odometer_events_chk_1` CHECK (json_valid(`raw`))
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `trucks`
--

DROP TABLE IF EXISTS `trucks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `trucks` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int unsigned DEFAULT NULL,
  `brand` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `plate` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `license_number` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `license_issued` date DEFAULT NULL,
  `license_expired` date DEFAULT NULL,
  `mapon_box_id` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mapon_unit_id` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `can_available` tinyint(1) NOT NULL DEFAULT '0',
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
  UNIQUE KEY `trucks_vin_unique` (`vin`),
  KEY `trucks_mapon_box_id_index` (`mapon_box_id`),
  KEY `trucks_mapon_unit_id_index` (`mapon_unit_id`)
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint unsigned DEFAULT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'driver',
  `phone` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `1` (`company_id`),
  CONSTRAINT `1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `vehicle_runs`
--

DROP TABLE IF EXISTS `vehicle_runs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `vehicle_runs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `truck_id` bigint unsigned NOT NULL,
  `driver_id` bigint unsigned DEFAULT NULL,
  `started_at` datetime NOT NULL,
  `ended_at` datetime DEFAULT NULL,
  `start_can_odom_km` decimal(12,1) DEFAULT NULL,
  `end_can_odom_km` decimal(12,1) DEFAULT NULL,
  `start_engine_hours` decimal(12,1) DEFAULT NULL,
  `end_engine_hours` decimal(12,1) DEFAULT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'open',
  `close_reason` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'manual',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `vehicle_runs_truck_id_status_index` (`truck_id`,`status`),
  KEY `vehicle_runs_driver_id_status_index` (`driver_id`,`status`),
  KEY `vehicle_runs_started_at_index` (`started_at`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-02-27 16:23:49
