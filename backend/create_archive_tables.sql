-- 아카이브 시스템을 위한 테이블들
-- 월별 아카이브 메타 정보
CREATE TABLE IF NOT EXISTS monthly_archives (
    id INT PRIMARY KEY AUTO_INCREMENT,
    archive_month DATE NOT NULL COMMENT '아카이브 월 (YYYY-MM-01 형식)',
    modification_notes TEXT COMMENT '수정 내역',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_archive_month (archive_month)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 고정지출 아카이브
CREATE TABLE IF NOT EXISTS fixed_expenses_archive (
    id INT PRIMARY KEY AUTO_INCREMENT,
    archive_id INT NOT NULL COMMENT '월별 아카이브 ID',
    category VARCHAR(50) COMMENT '카테고리',
    item_name VARCHAR(200) NOT NULL COMMENT '항목명',
    amount INT NOT NULL DEFAULT 0 COMMENT '금액(원)',
    payment_date INT COMMENT '결제일(1-31)',
    payment_method ENUM('신용', '체크', '현금') DEFAULT '신용' COMMENT '결제수단',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (archive_id) REFERENCES monthly_archives(id) ON DELETE CASCADE,
    INDEX idx_archive_id (archive_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 선납지출 아카이브
CREATE TABLE IF NOT EXISTS prepaid_expenses_archive (
    id INT PRIMARY KEY AUTO_INCREMENT,
    archive_id INT NOT NULL COMMENT '월별 아카이브 ID',
    item_name VARCHAR(200) NOT NULL COMMENT '항목명',
    amount INT NOT NULL DEFAULT 0 COMMENT '금액(원)',
    payment_date INT COMMENT '결제일(1-31)',
    payment_method ENUM('신용', '체크', '현금') DEFAULT '신용' COMMENT '결제수단',
    expiry_date DATE COMMENT '만료일',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (archive_id) REFERENCES monthly_archives(id) ON DELETE CASCADE,
    INDEX idx_archive_id (archive_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 현금성 자산 아카이브
CREATE TABLE IF NOT EXISTS cash_assets_archive (
    id INT PRIMARY KEY AUTO_INCREMENT,
    archive_id INT NOT NULL COMMENT '월별 아카이브 ID',
    type ENUM('현금', '통장') NOT NULL COMMENT '자산 유형',
    account_name VARCHAR(100) COMMENT '계좌명',
    item_name VARCHAR(200) NOT NULL COMMENT '항목명',
    balance INT NOT NULL DEFAULT 0 COMMENT '잔액(원)',
    display_order INT NOT NULL DEFAULT 0 COMMENT '표시 순서',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (archive_id) REFERENCES monthly_archives(id) ON DELETE CASCADE,
    INDEX idx_archive_id (archive_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 투자자산 아카이브
CREATE TABLE IF NOT EXISTS investment_assets_archive (
    id INT PRIMARY KEY AUTO_INCREMENT,
    archive_id INT NOT NULL COMMENT '월별 아카이브 ID',
    category ENUM('저축', '혼합', '주식') NOT NULL COMMENT '자산 분류',
    account_name VARCHAR(100) COMMENT '계좌명',
    item_name VARCHAR(200) NOT NULL COMMENT '종목명',
    current_value INT NOT NULL DEFAULT 0 COMMENT '현재 평가금액(원)',
    deposit_amount INT NOT NULL DEFAULT 0 COMMENT '납입 원금(원)',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (archive_id) REFERENCES monthly_archives(id) ON DELETE CASCADE,
    INDEX idx_archive_id (archive_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 연금자산 아카이브
CREATE TABLE IF NOT EXISTS pension_assets_archive (
    id INT PRIMARY KEY AUTO_INCREMENT,
    archive_id INT NOT NULL COMMENT '월별 아카이브 ID',
    type ENUM('연금저축', '퇴직연금') NOT NULL COMMENT '연금 유형',
    item_name VARCHAR(200) NOT NULL COMMENT '상품명',
    current_value INT NOT NULL DEFAULT 0 COMMENT '평가금액(원)',
    deposit_amount INT NOT NULL DEFAULT 0 COMMENT '납입잔액(원)',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (archive_id) REFERENCES monthly_archives(id) ON DELETE CASCADE,
    INDEX idx_archive_id (archive_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;