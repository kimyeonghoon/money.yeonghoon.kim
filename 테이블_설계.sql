-- 자산 정보 테이블
CREATE TABLE money_assets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type VARCHAR(50) NOT NULL COMMENT '자산 유형 (현금성, 저축/투자, 연금 등)',
    account_name VARCHAR(100) NOT NULL COMMENT '계좌명/기관명',
    item_name VARCHAR(100) NOT NULL COMMENT '상품명',
    balance DECIMAL(15,2) NOT NULL DEFAULT 0 COMMENT '잔액',
    ratio DECIMAL(10,6) DEFAULT NULL COMMENT '자산 비율',
    asset_group VARCHAR(50) DEFAULT NULL COMMENT '자산 그룹',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL COMMENT '논리삭제 일시'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 카테고리 테이블 (계층형 구조)
CREATE TABLE money_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL COMMENT '카테고리명',
    parent_id INT NULL COMMENT '상위 카테고리 ID',
    sort_order INT DEFAULT 0 COMMENT '정렬 순서',
    is_active BOOLEAN DEFAULT TRUE COMMENT '활성화 여부',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES money_categories(id) ON DELETE SET NULL,
    INDEX idx_parent_id (parent_id),
    INDEX idx_sort_order (sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 결제수단 테이블
CREATE TABLE money_payment_methods (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE COMMENT '결제수단명',
    is_active BOOLEAN DEFAULT TRUE COMMENT '활성화 여부',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 지출 기록 테이블
CREATE TABLE money_expenses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    date DATE NOT NULL COMMENT '지출 날짜',
    type VARCHAR(50) DEFAULT NULL COMMENT '지출 유형',
    category_id INT NOT NULL COMMENT '카테고리 ID',
    amount DECIMAL(15,2) NOT NULL COMMENT '지출 금액',
    payment_method_id INT NOT NULL COMMENT '결제수단 ID',
    description VARCHAR(255) DEFAULT NULL COMMENT '지출 내역',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES money_categories(id),
    FOREIGN KEY (payment_method_id) REFERENCES money_payment_methods(id),
    INDEX idx_date (date),
    INDEX idx_category_date (category_id, date),
    INDEX idx_payment_method (payment_method_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 고정지출/보험 등 테이블
CREATE TABLE money_fixed_expenses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL COMMENT '고정지출명',
    monthly_amount DECIMAL(15,2) NOT NULL COMMENT '월 납입금액',
    payment_date INT NOT NULL COMMENT '납입일 (1-31)',
    payment_method_id INT NOT NULL COMMENT '결제수단 ID',
    maturity VARCHAR(50) DEFAULT NULL COMMENT '만기',
    is_active BOOLEAN DEFAULT TRUE COMMENT '활성화 여부',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (payment_method_id) REFERENCES money_payment_methods(id),
    INDEX idx_payment_date (payment_date),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 예산 관리 테이블
CREATE TABLE money_budgets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL COMMENT '카테고리 ID',
    month CHAR(7) NOT NULL COMMENT '대상 월 (YYYY-MM)',
    budget_amount DECIMAL(15,2) NOT NULL COMMENT '예산 금액',
    remaining_amount DECIMAL(15,2) NOT NULL DEFAULT 0 COMMENT '잔여 예산',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES money_categories(id),
    UNIQUE KEY uk_category_month (category_id, month),
    INDEX idx_month (month)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
