-- Money Management Database Schema
-- 개인 재무 관리 시스템 테이블 구조

-- 데이터베이스 생성 및 사용
CREATE DATABASE IF NOT EXISTS money_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE money_management;

-- 현금성 자산 테이블
DROP TABLE IF EXISTS `cash_assets`;
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
  KEY `idx_cash_assets_deleted` (`deleted_at`),
  KEY `idx_display_order` (`display_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 투자 자산 테이블
DROP TABLE IF EXISTS `investment_assets`;
CREATE TABLE `investment_assets` (
  `id` int NOT NULL AUTO_INCREMENT,
  `category` enum('저축','혼합','주식') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '자산 분류',
  `account_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '계좌명',
  `item_name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '종목명',
  `current_value` int NOT NULL DEFAULT '0' COMMENT '현재 평가금액(원)',
  `deposit_amount` int NOT NULL DEFAULT '0' COMMENT '납입 원금(원)',
  `display_order` int DEFAULT '0' COMMENT '표시 순서',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_investment_assets_deleted` (`deleted_at`),
  KEY `idx_display_order` (`display_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 연금 자산 테이블
DROP TABLE IF EXISTS `pension_assets`;
CREATE TABLE `pension_assets` (
  `id` int NOT NULL AUTO_INCREMENT,
  `type` enum('연금저축','퇴직연금') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '연금 유형',
  `account_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '계좌명',
  `item_name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '상품명',
  `current_value` int NOT NULL DEFAULT '0' COMMENT '평가금액(원)',
  `deposit_amount` int NOT NULL DEFAULT '0' COMMENT '납입잔액(원)',
  `display_order` int DEFAULT '1' COMMENT '표시 순서',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_pension_assets_deleted` (`deleted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 일간 지출 테이블
DROP TABLE IF EXISTS `daily_expenses`;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 고정 지출 테이블
DROP TABLE IF EXISTS `fixed_expenses`;
CREATE TABLE `fixed_expenses` (
  `id` int NOT NULL AUTO_INCREMENT,
  `category` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '분류 (보험, 통신, 주거 등)',
  `item_name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '항목명',
  `amount` int NOT NULL COMMENT '금액(원)',
  `payment_date` int DEFAULT NULL COMMENT '결제일',
  `payment_method` enum('신용','체크','현금') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '결제수단',
  `is_active` tinyint(1) DEFAULT '1' COMMENT '활성 여부',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_fixed_expenses_deleted` (`deleted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 선납 지출 테이블
DROP TABLE IF EXISTS `prepaid_expenses`;
CREATE TABLE `prepaid_expenses` (
  `id` int NOT NULL AUTO_INCREMENT,
  `item_name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '항목명',
  `amount` int NOT NULL COMMENT '금액(원)',
  `payment_date` int NOT NULL COMMENT '결제일',
  `payment_method` enum('신용','체크','현금') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '결제수단',
  `expiry_date` date DEFAULT NULL COMMENT '만료일',
  `is_active` tinyint(1) DEFAULT '1' COMMENT '활성 여부',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_prepaid_expenses_deleted` (`deleted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 월말 자산 현황 스냅샷 테이블
DROP TABLE IF EXISTS `assets_monthly_snapshot`;
CREATE TABLE `assets_monthly_snapshot` (
  `id` int NOT NULL AUTO_INCREMENT,
  `snapshot_month` date NOT NULL COMMENT '스냅샷 월 (YYYY-MM-01 형식)',
  `asset_type` enum('현금성','투자','연금') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '자산 분류',
  `total_amount` bigint NOT NULL DEFAULT '0' COMMENT '총 자산 금액(원)',
  `total_count` int NOT NULL DEFAULT '0' COMMENT '자산 항목 수',
  `cash_amount` bigint DEFAULT '0' COMMENT '현금성 자산 금액',
  `savings_amount` bigint DEFAULT '0' COMMENT '저축성 자산 금액',
  `investment_amount` bigint DEFAULT '0' COMMENT '투자성 자산 금액',
  `pension_amount` bigint DEFAULT '0' COMMENT '연금 자산 금액',
  `snapshot_created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '스냅샷 생성 시간',
  `notes` text COLLATE utf8mb4_unicode_ci COMMENT '메모',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_snapshot_month_type` (`snapshot_month`,`asset_type`),
  KEY `idx_snapshot_month` (`snapshot_month`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='월말 자산 현황 스냅샷';

-- 월말 지출 집계 아카이브 테이블
DROP TABLE IF EXISTS `expenses_monthly_summary`;
CREATE TABLE `expenses_monthly_summary` (
  `id` int NOT NULL AUTO_INCREMENT,
  `summary_month` date NOT NULL COMMENT '집계 월 (YYYY-MM-01 형식)',
  `total_expenses` bigint NOT NULL DEFAULT '0' COMMENT '월 총 지출 금액(원)',
  `total_days` int NOT NULL DEFAULT '0' COMMENT '지출이 있었던 총 일수',
  `avg_daily_expense` int NOT NULL DEFAULT '0' COMMENT '일평균 지출 금액(원)',
  `food_total` bigint DEFAULT '0' COMMENT '식비 총액',
  `necessities_total` bigint DEFAULT '0' COMMENT '생필품비 총액',
  `transportation_total` bigint DEFAULT '0' COMMENT '교통비 총액',
  `other_total` bigint DEFAULT '0' COMMENT '기타 총액',
  `max_daily_expense` int DEFAULT '0' COMMENT '최대 일일 지출',
  `max_expense_date` date DEFAULT NULL COMMENT '최대 지출 날짜',
  `min_daily_expense` int DEFAULT '0' COMMENT '최소 일일 지출 (0 제외)',
  `min_expense_date` date DEFAULT NULL COMMENT '최소 지출 날짜',
  `snapshot_created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '집계 생성 시간',
  `notes` text COLLATE utf8mb4_unicode_ci COMMENT '메모',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_summary_month` (`summary_month`),
  KEY `idx_summary_month` (`summary_month`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='월말 지출 집계 아카이브';