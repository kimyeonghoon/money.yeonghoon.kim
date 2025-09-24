# API 테스팅 가이드

## 개요
자산 관리 시스템의 REST API 테스팅을 위한 가이드입니다.

## 사전 준비

### 1. 데이터베이스 설정
```bash
# MySQL에 접속하여 데이터베이스 생성
mysql -u root -p
CREATE DATABASE money_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE money_management;

# 스키마 실행
source /path/to/backend/schema.sql;

# 샘플 데이터 삽입 (선택사항)
source /path/to/backend/test-sample-data.sql;
```

### 2. 환경 변수 설정
```bash
# .env 파일 생성
cp backend/.env.example backend/.env

# .env 파일 수정하여 데이터베이스 정보 입력
```

## API 엔드포인트 목록

### Base URL
```
http://localhost/api/
```

## 1. 현금성 자산 API (`/api/cash-assets`)

### 기본 CRUD
```bash
# 목록 조회
curl -X GET "http://localhost/api/cash-assets.php"

# 페이징된 목록 조회
curl -X GET "http://localhost/api/cash-assets.php?page=1&limit=10"

# 단일 조회
curl -X GET "http://localhost/api/cash-assets.php/1"

# 생성
curl -X POST "http://localhost/api/cash-assets.php" \
  -H "Content-Type: application/json" \
  -d '{
    "type": "현금",
    "account_name": "테스트 계좌",
    "item_name": "테스트 현금",
    "balance": 100000.00
  }'

# 수정
curl -X PUT "http://localhost/api/cash-assets.php/1" \
  -H "Content-Type: application/json" \
  -d '{
    "type": "통장",
    "account_name": "수정된 계좌",
    "item_name": "수정된 항목",
    "balance": 150000.00
  }'

# 삭제 (논리적 삭제)
curl -X DELETE "http://localhost/api/cash-assets.php/1"
```

### 추가 기능
```bash
# 요약 정보
curl -X GET "http://localhost/api/cash-assets.php/summary"

# 유형별 조회
curl -X GET "http://localhost/api/cash-assets.php/by-type?type=현금"

# 검색
curl -X GET "http://localhost/api/cash-assets.php/search?q=테스트"

# 삭제된 항목 조회
curl -X GET "http://localhost/api/cash-assets.php/deleted"

# 복원
curl -X POST "http://localhost/api/cash-assets.php/restore/1"
```

## 2. 저축/투자 자산 API (`/api/investment-assets`)

### 기본 CRUD
```bash
# 목록 조회
curl -X GET "http://localhost/api/investment-assets.php"

# 생성
curl -X POST "http://localhost/api/investment-assets.php" \
  -H "Content-Type: application/json" \
  -d '{
    "category": "주식",
    "account_name": "KB증권",
    "item_name": "테스트 ETF",
    "current_value": 1200000.00,
    "deposit_amount": 1000000.00
  }'
```

### 추가 기능
```bash
# 요약 정보 (수익률 포함)
curl -X GET "http://localhost/api/investment-assets.php/summary"

# 카테고리별 조회
curl -X GET "http://localhost/api/investment-assets.php/by-category?category=주식"

# 수익률 조회
curl -X GET "http://localhost/api/investment-assets.php/return-rate"
```

## 3. 연금 자산 API (`/api/pension-assets`)

### 기본 CRUD
```bash
# 목록 조회
curl -X GET "http://localhost/api/pension-assets.php"

# 생성
curl -X POST "http://localhost/api/pension-assets.php" \
  -H "Content-Type: application/json" \
  -d '{
    "type": "연금저축",
    "item_name": "테스트 연금상품",
    "current_value": 5000000.00,
    "deposit_amount": 4500000.00
  }'
```

### 추가 기능
```bash
# 요약 정보
curl -X GET "http://localhost/api/pension-assets.php/summary"

# 유형별 조회
curl -X GET "http://localhost/api/pension-assets.php/by-type?type=연금저축"

# 수익률 조회
curl -X GET "http://localhost/api/pension-assets.php/return-rate"
```

## 4. 일별 지출 API (`/api/daily-expenses`)

### 기본 CRUD
```bash
# 목록 조회
curl -X GET "http://localhost/api/daily-expenses.php"

# 생성
curl -X POST "http://localhost/api/daily-expenses.php" \
  -H "Content-Type: application/json" \
  -d '{
    "expense_date": "2024-09-24",
    "total_amount": 50000.00,
    "food_cost": 30000.00,
    "necessities_cost": 10000.00,
    "transportation_cost": 5000.00,
    "other_cost": 5000.00
  }'
```

### 추가 기능
```bash
# 특정 날짜 조회
curl -X GET "http://localhost/api/daily-expenses.php/by-date?date=2024-09-24"

# 월별 조회
curl -X GET "http://localhost/api/daily-expenses.php/by-month?year=2024&month=9"

# 월별 합계
curl -X GET "http://localhost/api/daily-expenses.php/monthly-total?year=2024&month=9"

# 연별 합계
curl -X GET "http://localhost/api/daily-expenses.php/yearly-total?year=2024"

# 평균 계산
curl -X GET "http://localhost/api/daily-expenses.php/average?start_date=2024-09-01&end_date=2024-09-30"

# 최근 지출
curl -X GET "http://localhost/api/daily-expenses.php/recent?days=7"

# 카테고리별 분석
curl -X GET "http://localhost/api/daily-expenses.php/category-breakdown?start_date=2024-09-01&end_date=2024-09-30"
```

## 5. 고정 지출 API (`/api/fixed-expenses`)

### 기본 CRUD
```bash
# 목록 조회
curl -X GET "http://localhost/api/fixed-expenses.php"

# 생성
curl -X POST "http://localhost/api/fixed-expenses.php" \
  -H "Content-Type: application/json" \
  -d '{
    "category": "구독",
    "item_name": "테스트 구독",
    "amount": 9900.00,
    "payment_date": 15,
    "payment_method": "신용",
    "is_active": true
  }'
```

### 추가 기능
```bash
# 활성 항목만 조회
curl -X GET "http://localhost/api/fixed-expenses.php/active"

# 요약 정보
curl -X GET "http://localhost/api/fixed-expenses.php/summary"

# 카테고리별 조회
curl -X GET "http://localhost/api/fixed-expenses.php/by-category"
curl -X GET "http://localhost/api/fixed-expenses.php/by-category?category=구독"

# 결제일별 조회
curl -X GET "http://localhost/api/fixed-expenses.php/by-payment-date?date=15"

# 결제수단별 조회
curl -X GET "http://localhost/api/fixed-expenses.php/by-payment-method?method=신용"

# 다가오는 결제 (7일 이내)
curl -X GET "http://localhost/api/fixed-expenses.php/upcoming?days=7"

# 활성/비활성 토글
curl -X POST "http://localhost/api/fixed-expenses.php/toggle-active/1"
```

## 6. 선납 지출 API (`/api/prepaid-expenses`)

### 기본 CRUD
```bash
# 목록 조회
curl -X GET "http://localhost/api/prepaid-expenses.php"

# 생성
curl -X POST "http://localhost/api/prepaid-expenses.php" \
  -H "Content-Type: application/json" \
  -d '{
    "item_name": "테스트 보험",
    "amount": 50000.00,
    "payment_date": 10,
    "payment_method": "현금",
    "expiry_date": "2025-12-31",
    "is_active": true
  }'
```

### 추가 기능
```bash
# 활성 항목만 조회
curl -X GET "http://localhost/api/prepaid-expenses.php/active"

# 요약 정보
curl -X GET "http://localhost/api/prepaid-expenses.php/summary"

# 곧 만료되는 항목 (30일 이내)
curl -X GET "http://localhost/api/prepaid-expenses.php/expiring-soon?days=30"

# 만료된 항목
curl -X GET "http://localhost/api/prepaid-expenses.php/expired"

# 만료 상태 요약
curl -X GET "http://localhost/api/prepaid-expenses.php/expiry-status"

# 만료일 갱신
curl -X PUT "http://localhost/api/prepaid-expenses.php/renew/1" \
  -H "Content-Type: application/json" \
  -d '{"expiry_date": "2026-12-31"}'

# 다가오는 결제
curl -X GET "http://localhost/api/prepaid-expenses.php/upcoming?days=7"

# 활성/비활성 토글
curl -X POST "http://localhost/api/prepaid-expenses.php/toggle-active/1"
```

## 7. 대시보드 API (`/api/dashboard`)

```bash
# 전체 요약
curl -X GET "http://localhost/api/dashboard.php"
curl -X GET "http://localhost/api/dashboard.php/summary"

# 자산 요약
curl -X GET "http://localhost/api/dashboard.php/assets"

# 지출 요약 (현재 월)
curl -X GET "http://localhost/api/dashboard.php/expenses"

# 지출 요약 (특정 월)
curl -X GET "http://localhost/api/dashboard.php/expenses?year=2024&month=9"

# 월별 개요 (연간)
curl -X GET "http://localhost/api/dashboard.php/monthly-overview"
curl -X GET "http://localhost/api/dashboard.php/monthly-overview?year=2024"
```

## 응답 형식

### 성공 응답
```json
{
  "success": true,
  "message": "Success",
  "data": { ... },
  "pagination": {
    "total": 100,
    "page": 1,
    "limit": 20,
    "pages": 5,
    "has_next": true,
    "has_previous": false
  }
}
```

### 에러 응답
```json
{
  "success": false,
  "message": "Error message",
  "error_code": 400,
  "details": { ... }
}
```

## 테스트 순서 추천

1. **데이터베이스 스키마 생성**
2. **샘플 데이터 삽입**
3. **기본 CRUD 테스트** (각 엔터티별)
4. **고급 기능 테스트** (검색, 필터링, 통계)
5. **대시보드 API 테스트**
6. **논리적 삭제/복원 테스트**

## 에러 상황 테스트

```bash
# 잘못된 데이터 형식
curl -X POST "http://localhost/api/cash-assets.php" \
  -H "Content-Type: application/json" \
  -d '{"type": "잘못된타입", "balance": "문자열"}'

# 존재하지 않는 ID
curl -X GET "http://localhost/api/cash-assets.php/99999"

# 필수 필드 누락
curl -X POST "http://localhost/api/cash-assets.php" \
  -H "Content-Type: application/json" \
  -d '{"type": "현금"}'
```

이 가이드를 참고하여 API의 모든 기능을 체계적으로 테스트할 수 있습니다.