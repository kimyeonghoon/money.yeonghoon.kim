-- 자산 관리 시스템 데이터베이스 스키마
-- 모든 테이블에 soft delete (deleted_at) 구현

-- 1. 현금성 자산
CREATE TABLE cash_assets (
    id INT PRIMARY KEY AUTO_INCREMENT,
    type ENUM('현금', '통장') NOT NULL COMMENT '자산 유형',
    account_name VARCHAR(100) COMMENT '계좌명',
    item_name VARCHAR(200) NOT NULL COMMENT '항목명',
    balance INT NOT NULL DEFAULT 0 COMMENT '잔액(원)',
    display_order INT NOT NULL DEFAULT 0 COMMENT '표시 순서',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL COMMENT '삭제일시 (NULL이면 활성)',
    INDEX idx_display_order (display_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. 저축/투자 자산
CREATE TABLE investment_assets (
    id INT PRIMARY KEY AUTO_INCREMENT,
    category ENUM('저축', '혼합', '주식') NOT NULL COMMENT '자산 분류',
    account_name VARCHAR(100) COMMENT '계좌명',
    item_name VARCHAR(200) NOT NULL COMMENT '종목명',
    current_value INT NOT NULL DEFAULT 0 COMMENT '현재 평가금액(원)',
    deposit_amount INT NOT NULL DEFAULT 0 COMMENT '납입 원금(원)',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. 연금 자산
CREATE TABLE pension_assets (
    id INT PRIMARY KEY AUTO_INCREMENT,
    type ENUM('연금저축', '퇴직연금') NOT NULL COMMENT '연금 유형',
    item_name VARCHAR(200) NOT NULL COMMENT '상품명',
    current_value INT NOT NULL DEFAULT 0 COMMENT '평가금액(원)',
    deposit_amount INT NOT NULL DEFAULT 0 COMMENT '납입잔액(원)',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. 일별 지출
CREATE TABLE daily_expenses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    expense_date DATE NOT NULL COMMENT '지출일자',
    total_amount INT NOT NULL DEFAULT 0 COMMENT '총 금액(원)',
    food_cost INT DEFAULT 0 COMMENT '식비(원)',
    necessities_cost INT DEFAULT 0 COMMENT '생필품비(원)',
    transportation_cost INT DEFAULT 0 COMMENT '교통비(원)',
    other_cost INT DEFAULT 0 COMMENT '기타(원)',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    UNIQUE KEY unique_expense_date (expense_date, deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. 고정 지출
CREATE TABLE fixed_expenses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    category VARCHAR(50) COMMENT '분류 (보험, 통신, 주거 등)',
    item_name VARCHAR(200) NOT NULL COMMENT '항목명',
    amount INT NOT NULL COMMENT '금액(원)',
    payment_date INT NULL COMMENT '매월 결제일 (1-31)',
    payment_method ENUM('신용', '체크', '현금') NOT NULL COMMENT '결제수단',
    is_active BOOLEAN DEFAULT TRUE COMMENT '활성 여부',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. 선납 지출 (보험 등)
CREATE TABLE prepaid_expenses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    item_name VARCHAR(200) NOT NULL COMMENT '항목명',
    amount INT NOT NULL COMMENT '금액(원)',
    payment_date INT NULL COMMENT '결제일',
    payment_method ENUM('신용', '체크', '현금') NOT NULL COMMENT '결제수단',
    expiry_date DATE COMMENT '만료일',
    is_active BOOLEAN DEFAULT TRUE COMMENT '활성 여부',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 인덱스 추가
CREATE INDEX idx_cash_assets_deleted ON cash_assets(deleted_at);
CREATE INDEX idx_investment_assets_deleted ON investment_assets(deleted_at);
CREATE INDEX idx_pension_assets_deleted ON pension_assets(deleted_at);
CREATE INDEX idx_daily_expenses_deleted ON daily_expenses(deleted_at);
CREATE INDEX idx_daily_expenses_date ON daily_expenses(expense_date);
CREATE INDEX idx_fixed_expenses_deleted ON fixed_expenses(deleted_at);
CREATE INDEX idx_prepaid_expenses_deleted ON prepaid_expenses(deleted_at);