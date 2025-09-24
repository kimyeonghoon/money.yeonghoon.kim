-- 샘플 데이터 초기화 스크립트

-- 결제수단 기본 데이터
INSERT INTO money_payment_methods (name) VALUES
('현금'),
('신용카드'),
('체크카드'),
('계좌이체'),
('간편결제');

-- 카테고리 기본 데이터 (대분류)
INSERT INTO money_categories (name, parent_id, sort_order) VALUES
('식비', NULL, 1),
('교통비', NULL, 2),
('생활용품', NULL, 3),
('의료비', NULL, 4),
('문화/여가', NULL, 5),
('교육', NULL, 6),
('의류', NULL, 7),
('통신비', NULL, 8),
('공과금', NULL, 9),
('기타', NULL, 99);

-- 카테고리 소분류
INSERT INTO money_categories (name, parent_id, sort_order) VALUES
('외식', 1, 1),
('마트/편의점', 1, 2),
('배달음식', 1, 3),
('대중교통', 2, 1),
('택시', 2, 2),
('주유비', 2, 3),
('영화', 5, 1),
('도서', 5, 2),
('게임', 5, 3);

-- 자산 샘플 데이터
INSERT INTO money_assets (type, account_name, item_name, balance, asset_group) VALUES
('현금성', '신한은행', '주거래통장', 5000000.00, '현금성자산'),
('저축/투자', '신한은행', '정기예금', 10000000.00, '저축자산'),
('저축/투자', '삼성증권', 'S&P500 ETF', 3000000.00, '투자자산'),
('연금', '국민연금공단', '국민연금', 15000000.00, '연금자산');

-- 고정지출 샘플 데이터
INSERT INTO money_fixed_expenses (name, monthly_amount, payment_date, payment_method_id, maturity) VALUES
('휴대폰 요금', 55000.00, 25, 2, '해당없음'),
('인터넷 요금', 35000.00, 1, 2, '해당없음'),
('전기요금', 80000.00, 15, 3, '해당없음'),
('가스요금', 45000.00, 20, 3, '해당없음'),
('생명보험', 150000.00, 28, 2, '2030-12-31');

-- 현재 월 예산 샘플 데이터
INSERT INTO money_budgets (category_id, month, budget_amount, remaining_amount) VALUES
(1, DATE_FORMAT(NOW(), '%Y-%m'), 500000.00, 500000.00),
(2, DATE_FORMAT(NOW(), '%Y-%m'), 150000.00, 150000.00),
(3, DATE_FORMAT(NOW(), '%Y-%m'), 100000.00, 100000.00),
(5, DATE_FORMAT(NOW(), '%Y-%m'), 200000.00, 200000.00);

-- 지출 기록 샘플 데이터 (이번 달)
INSERT INTO money_expenses (date, category_id, amount, payment_method_id, description) VALUES
(CURDATE(), 11, 15000.00, 2, '점심식사'),
(DATE_SUB(CURDATE(), INTERVAL 1 DAY), 12, 35000.00, 3, '주간 장보기'),
(DATE_SUB(CURDATE(), INTERVAL 2 DAY), 4, 2500.00, 1, '지하철 요금'),
(DATE_SUB(CURDATE(), INTERVAL 3 DAY), 13, 22000.00, 2, '치킨 배달'),
(DATE_SUB(CURDATE(), INTERVAL 5 DAY), 16, 12000.00, 2, '영화 관람');