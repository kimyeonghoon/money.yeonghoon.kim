-- MySQL dump 10.13  Distrib 8.0.43, for Linux (x86_64)
--
-- Host: localhost    Database: money_management
-- ------------------------------------------------------
-- Server version	8.0.43

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
-- Table structure for table `archive_summary_cache`
--

DROP TABLE IF EXISTS `archive_summary_cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `archive_summary_cache` (
  `archive_id` int NOT NULL,
  `cash_total` bigint DEFAULT '0',
  `cash_count` int DEFAULT '0',
  `investment_total` bigint DEFAULT '0',
  `investment_count` int DEFAULT '0',
  `pension_total` bigint DEFAULT '0',
  `pension_count` int DEFAULT '0',
  `total_assets` bigint GENERATED ALWAYS AS (((`cash_total` + `investment_total`) + `pension_total`)) STORED,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`archive_id`),
  CONSTRAINT `archive_summary_cache_ibfk_1` FOREIGN KEY (`archive_id`) REFERENCES `monthly_archives` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `assets_archive_data`
--

DROP TABLE IF EXISTS `assets_archive_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `assets_archive_data` (
  `id` int NOT NULL AUTO_INCREMENT,
  `archive_id` int NOT NULL,
  `asset_table` enum('cash_assets','investment_assets','pension_assets') COLLATE utf8mb4_unicode_ci NOT NULL,
  `asset_data` json NOT NULL COMMENT '원본 테이블 데이터 완전 보존',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_archive_table` (`archive_id`,`asset_table`),
  KEY `idx_archive_id` (`archive_id`),
  CONSTRAINT `assets_archive_data_ibfk_1` FOREIGN KEY (`archive_id`) REFERENCES `monthly_archives` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=859 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cash_assets`
--

DROP TABLE IF EXISTS `cash_assets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cash_assets` (
  `id` int NOT NULL AUTO_INCREMENT,
  `type` enum('현금','통장') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '자산 유형',
  `account_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '계좌명',
  `item_name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '항목명',
  `balance` int NOT NULL DEFAULT '0' COMMENT '잔액(원)',
  `display_order` int NOT NULL DEFAULT '0' COMMENT '표시 순서',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT '삭제일시 (NULL이면 활성)',
  PRIMARY KEY (`id`),
  KEY `idx_display_order` (`display_order`),
  KEY `idx_cash_assets_deleted` (`deleted_at`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cash_assets_archive`
--

DROP TABLE IF EXISTS `cash_assets_archive`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cash_assets_archive` (
  `id` int NOT NULL AUTO_INCREMENT,
  `archive_id` int NOT NULL COMMENT '월별 아카이브 ID',
  `type` enum('현금','통장') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '자산 유형',
  `account_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '계좌명',
  `item_name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '항목명',
  `balance` int NOT NULL DEFAULT '0' COMMENT '잔액(원)',
  `display_order` int NOT NULL DEFAULT '0' COMMENT '표시 순서',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_archive_id` (`archive_id`),
  CONSTRAINT `cash_assets_archive_ibfk_1` FOREIGN KEY (`archive_id`) REFERENCES `monthly_archives` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `daily_expenses`
--

DROP TABLE IF EXISTS `daily_expenses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `daily_expenses` (
  `id` int NOT NULL AUTO_INCREMENT,
  `expense_date` date NOT NULL COMMENT '지출일자',
  `total_amount` int NOT NULL DEFAULT '0' COMMENT '총 금액(원)',
  `food_cost` int DEFAULT '0' COMMENT '식비(원)',
  `necessities_cost` int DEFAULT '0' COMMENT '생필품비(원)',
  `transportation_cost` int DEFAULT '0' COMMENT '교통비(원)',
  `other_cost` int DEFAULT '0' COMMENT '기타(원)',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_expense_date` (`expense_date`,`deleted_at`),
  KEY `idx_daily_expenses_deleted` (`deleted_at`),
  KEY `idx_daily_expenses_date` (`expense_date`)
) ENGINE=InnoDB AUTO_INCREMENT=1119 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fixed_expenses`
--

DROP TABLE IF EXISTS `fixed_expenses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fixed_expenses` (
  `id` int NOT NULL AUTO_INCREMENT,
  `category` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '분류 (보험, 통신, 주거 등)',
  `item_name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '항목명',
  `amount` int NOT NULL COMMENT '금액(원)',
  `payment_date` int DEFAULT NULL COMMENT '매월 결제일 (1-31)',
  `payment_method` enum('신용','체크','현금') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '결제수단',
  `is_active` tinyint(1) DEFAULT '1' COMMENT '활성 여부',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_fixed_expenses_deleted` (`deleted_at`)
) ENGINE=InnoDB AUTO_INCREMENT=75 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fixed_expenses_archive`
--

DROP TABLE IF EXISTS `fixed_expenses_archive`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fixed_expenses_archive` (
  `id` int NOT NULL AUTO_INCREMENT,
  `archive_id` int NOT NULL COMMENT '월별 아카이브 ID',
  `category` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '카테고리',
  `item_name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '항목명',
  `amount` int NOT NULL DEFAULT '0' COMMENT '금액(원)',
  `payment_date` int DEFAULT NULL COMMENT '결제일(1-31)',
  `payment_method` enum('신용','체크','현금') COLLATE utf8mb4_unicode_ci DEFAULT '신용' COMMENT '결제수단',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_archive_id` (`archive_id`),
  CONSTRAINT `fixed_expenses_archive_ibfk_1` FOREIGN KEY (`archive_id`) REFERENCES `monthly_archives` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4381 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `investment_assets`
--

DROP TABLE IF EXISTS `investment_assets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `investment_assets` (
  `id` int NOT NULL AUTO_INCREMENT,
  `category` enum('저축','혼합','주식') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '자산 분류',
  `account_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '계좌명',
  `item_name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '종목명',
  `current_value` int NOT NULL DEFAULT '0' COMMENT '현재 평가금액(원)',
  `deposit_amount` int NOT NULL DEFAULT '0' COMMENT '납입 원금(원)',
  `display_order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_investment_assets_deleted` (`deleted_at`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `investment_assets_archive`
--

DROP TABLE IF EXISTS `investment_assets_archive`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `investment_assets_archive` (
  `id` int NOT NULL AUTO_INCREMENT,
  `archive_id` int NOT NULL COMMENT '월별 아카이브 ID',
  `category` enum('저축','혼합','주식') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '자산 분류',
  `account_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '계좌명',
  `item_name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '종목명',
  `current_value` int NOT NULL DEFAULT '0' COMMENT '현재 평가금액(원)',
  `deposit_amount` int NOT NULL DEFAULT '0' COMMENT '납입 원금(원)',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_archive_id` (`archive_id`),
  CONSTRAINT `investment_assets_archive_ibfk_1` FOREIGN KEY (`archive_id`) REFERENCES `monthly_archives` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `monthly_archives`
--

DROP TABLE IF EXISTS `monthly_archives`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `monthly_archives` (
  `id` int NOT NULL AUTO_INCREMENT,
  `archive_month` date NOT NULL COMMENT '아카이브 월 (YYYY-MM-01 형식)',
  `modification_notes` text COLLATE utf8mb4_unicode_ci COMMENT '수정 내역',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_modified` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_archive_month` (`archive_month`)
) ENGINE=InnoDB AUTO_INCREMENT=170 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pension_assets`
--

DROP TABLE IF EXISTS `pension_assets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pension_assets` (
  `id` int NOT NULL AUTO_INCREMENT,
  `type` enum('연금저축','퇴직연금') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '연금 유형',
  `item_name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '상품명',
  `current_value` int NOT NULL DEFAULT '0' COMMENT '평가금액(원)',
  `deposit_amount` int NOT NULL DEFAULT '0' COMMENT '납입잔액(원)',
  `display_order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_pension_assets_deleted` (`deleted_at`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pension_assets_archive`
--

DROP TABLE IF EXISTS `pension_assets_archive`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pension_assets_archive` (
  `id` int NOT NULL AUTO_INCREMENT,
  `archive_id` int NOT NULL COMMENT '월별 아카이브 ID',
  `type` enum('연금저축','퇴직연금') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '연금 유형',
  `item_name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '상품명',
  `current_value` int NOT NULL DEFAULT '0' COMMENT '평가금액(원)',
  `deposit_amount` int NOT NULL DEFAULT '0' COMMENT '납입잔액(원)',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_archive_id` (`archive_id`),
  CONSTRAINT `pension_assets_archive_ibfk_1` FOREIGN KEY (`archive_id`) REFERENCES `monthly_archives` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `prepaid_expenses`
--

DROP TABLE IF EXISTS `prepaid_expenses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `prepaid_expenses` (
  `id` int NOT NULL AUTO_INCREMENT,
  `item_name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '항목명',
  `amount` int NOT NULL COMMENT '금액(원)',
  `payment_date` int DEFAULT NULL COMMENT '결제일',
  `payment_method` enum('신용','체크','현금') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '결제수단',
  `expiry_date` date DEFAULT NULL COMMENT '만료일',
  `is_active` tinyint(1) DEFAULT '1' COMMENT '활성 여부',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_prepaid_expenses_deleted` (`deleted_at`)
) ENGINE=InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `prepaid_expenses_archive`
--

DROP TABLE IF EXISTS `prepaid_expenses_archive`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `prepaid_expenses_archive` (
  `id` int NOT NULL AUTO_INCREMENT,
  `archive_id` int NOT NULL COMMENT '월별 아카이브 ID',
  `item_name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '항목명',
  `amount` int NOT NULL DEFAULT '0' COMMENT '금액(원)',
  `payment_date` int DEFAULT NULL COMMENT '결제일(1-31)',
  `payment_method` enum('신용','체크','현금') COLLATE utf8mb4_unicode_ci DEFAULT '신용' COMMENT '결제수단',
  `expiry_date` date DEFAULT NULL COMMENT '만료일',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_archive_id` (`archive_id`),
  CONSTRAINT `prepaid_expenses_archive_ibfk_1` FOREIGN KEY (`archive_id`) REFERENCES `monthly_archives` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1054 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_sessions`
--

DROP TABLE IF EXISTS `user_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_sessions` (
  `session_id` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `session_data` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `expires_at` timestamp NOT NULL,
  `last_activity` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`session_id`),
  KEY `idx_user_email` (`user_email`),
  KEY `idx_expires_at` (`expires_at`),
  KEY `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping routines for database 'money_management'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-09-29 10:02:48
