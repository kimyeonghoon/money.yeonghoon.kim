-- 월별 아카이브 시스템 스키마
-- 기존 테이블은 그대로 유지하고 새로운 테이블만 추가

-- 월별 아카이브 메인 테이블
CREATE TABLE monthly_archives (
    id INT PRIMARY KEY AUTO_INCREMENT,
    archive_month DATE NOT NULL COMMENT '아카이브 월 (YYYY-MM-01)',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_modified TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    modification_notes TEXT COMMENT '수정 사유/내역',

    UNIQUE KEY unique_month (archive_month),
    INDEX idx_archive_month (archive_month)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='월별 아카이브 메인 관리 테이블';

-- 범용 자산 아카이브 데이터
CREATE TABLE assets_archive_data (
    id INT PRIMARY KEY AUTO_INCREMENT,
    archive_id INT NOT NULL,
    asset_table ENUM('cash_assets', 'investment_assets', 'pension_assets') NOT NULL,
    asset_data JSON NOT NULL COMMENT '원본 테이블 데이터 완전 보존',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (archive_id) REFERENCES monthly_archives(id) ON DELETE CASCADE,
    INDEX idx_archive_table (archive_id, asset_table),
    INDEX idx_archive_id (archive_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='자산 아카이브 데이터 저장 (JSON)';

-- 빠른 조회용 집계 캐시 (선택적)
CREATE TABLE archive_summary_cache (
    archive_id INT PRIMARY KEY,
    cash_total BIGINT DEFAULT 0,
    cash_count INT DEFAULT 0,
    investment_total BIGINT DEFAULT 0,
    investment_count INT DEFAULT 0,
    pension_total BIGINT DEFAULT 0,
    pension_count INT DEFAULT 0,
    total_assets BIGINT AS (cash_total + investment_total + pension_total) STORED,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (archive_id) REFERENCES monthly_archives(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='아카이브 데이터 집계 캐시';