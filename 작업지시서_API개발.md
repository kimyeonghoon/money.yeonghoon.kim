# 작업 지시서

## 기본 정보
- **작업 제목**: 자산 관리 REST API 개발
- **작업 유형**: [x] 신규 개발 [ ] 기능 수정 [ ] 버그 수정 [ ] 리팩토링 [ ] 문서화
- **우선순위**: [x] 높음 [ ] 보통 [ ] 낮음
- **예상 소요시간**: 2-3일

## 작업 내용
### 목표
자산 관리 웹 애플리케이션의 백엔드 REST API를 개발하여 프론트엔드에서 CRUD 작업을 수행할 수 있도록 구현

### 상세 요구사항
1. **현금성 자산 관리 API**
   - 현금성 자산 목록 조회 (GET /api/cash-assets)
   - 현금성 자산 상세 조회 (GET /api/cash-assets/{id})
   - 현금성 자산 생성 (POST /api/cash-assets)
   - 현금성 자산 수정 (PUT /api/cash-assets/{id})
   - 현금성 자산 삭제 (DELETE /api/cash-assets/{id}) - 논리삭제

2. **저축/투자 자산 관리 API**
   - 저축/투자 자산 목록 조회 (GET /api/investment-assets)
   - 저축/투자 자산 생성 (POST /api/investment-assets)
   - 저축/투자 자산 수정 (PUT /api/investment-assets/{id})
   - 저축/투자 자산 삭제 (DELETE /api/investment-assets/{id}) - 논리삭제

3. **연금 자산 관리 API**
   - 연금 자산 목록 조회 (GET /api/pension-assets)
   - 연금 자산 생성 (POST /api/pension-assets)
   - 연금 자산 수정 (PUT /api/pension-assets/{id})
   - 연금 자산 삭제 (DELETE /api/pension-assets/{id}) - 논리삭제

4. **일별 지출 관리 API**
   - 일별 지출 목록 조회 (GET /api/daily-expenses)
   - 일별 지출 생성 (POST /api/daily-expenses)
   - 일별 지출 수정 (PUT /api/daily-expenses/{id})
   - 일별 지출 삭제 (DELETE /api/daily-expenses/{id}) - 논리삭제

5. **고정 지출 관리 API**
   - 고정 지출 목록 조회 (GET /api/fixed-expenses)
   - 고정 지출 생성 (POST /api/fixed-expenses)
   - 고정 지출 수정 (PUT /api/fixed-expenses/{id})
   - 고정 지출 삭제 (DELETE /api/fixed-expenses/{id}) - 논리삭제

6. **선납 지출 관리 API**
   - 선납 지출 목록 조회 (GET /api/prepaid-expenses)
   - 선납 지출 생성 (POST /api/prepaid-expenses)
   - 선납 지출 수정 (PUT /api/prepaid-expenses/{id})
   - 선납 지출 삭제 (DELETE /api/prepaid-expenses/{id}) - 논리삭제

7. **대시보드/통계 API**
   - 자산 현황 요약 (GET /api/dashboard/summary)
   - 지출 통계 (GET /api/dashboard/expenses)
   - 자산 분포 (GET /api/dashboard/assets)

### 제약사항 및 고려사항
- 2000년대 초반 스타일 UI와 호환되는 간단한 JSON 응답 구조
- RESTful API 원칙 준수
- 에러 처리 및 유효성 검증 포함
- 페이징 지원 (limit, offset)
- 논리삭제 구현 (deleted_at 활용)

## 기술적 세부사항
### 관련 파일/경로
```
backend/
├── api/
│   ├── cash-assets.php       # 현금성 자산 API
│   ├── investment-assets.php # 저축/투자 자산 API
│   ├── pension-assets.php    # 연금 자산 API
│   ├── daily-expenses.php    # 일별 지출 API
│   ├── fixed-expenses.php    # 고정 지출 API
│   ├── prepaid-expenses.php  # 선납 지출 API
│   └── dashboard.php         # 대시보드/통계 API
├── models/
│   ├── BaseModel.php         # 공통 모델 (soft delete 포함)
│   ├── CashAsset.php
│   ├── InvestmentAsset.php
│   ├── PensionAsset.php
│   ├── DailyExpense.php
│   ├── FixedExpense.php
│   └── PrepaidExpense.php
├── controllers/
│   ├── BaseController.php    # 공통 컨트롤러
│   ├── CashAssetController.php
│   ├── InvestmentAssetController.php
│   ├── PensionAssetController.php
│   ├── DailyExpenseController.php
│   ├── FixedExpenseController.php
│   ├── PrepaidExpenseController.php
│   └── DashboardController.php
└── lib/
    ├── Database.php          # 데이터베이스 연결
    ├── Validator.php         # 유효성 검증
    ├── Pagination.php        # 페이징 헬퍼
    └── Response.php          # JSON 응답 헬퍼
```

### 사용할 기술/라이브러리
- PHP 8.2 + PDO
- MySQL 8.0
- 순수 PHP (프레임워크 없이)
- JSON 응답 형식

### API/데이터 구조
**공통 응답 형식:**
```json
{
  "success": true,
  "message": "Success",
  "data": {...},
  "pagination": {
    "total": 100,
    "page": 1,
    "limit": 20,
    "pages": 5
  }
}
```

**에러 응답:**
```json
{
  "success": false,
  "message": "Error message",
  "error_code": 400
}
```

## 검증 방법
### 테스트 시나리오
1. **API 엔드포인트 테스트**
   - curl 명령어로 각 API 동작 확인
   - 정상 케이스와 에러 케이스 모두 테스트

2. **데이터 무결성 테스트**
   - 외래키 제약조건 확인
   - 유효성 검증 동작 확인

3. **페이징 테스트**
   - limit, offset 파라미터 동작 확인
   - 전체 페이지 수 계산 정확성

### 성공 기준
- [ ] 모든 API 엔드포인트가 정상 동작
- [ ] 에러 처리가 적절하게 구현
- [ ] phpMyAdmin에서 데이터 CRUD 정상 확인
- [ ] API 응답 시간 1초 이내
- [ ] 한글 인코딩 문제 없음

## 추가 정보
### 참고 자료
- 기획서.md - 전체 프로젝트 요구사항
- 테이블_설계.sql - 데이터베이스 구조
- CLAUDE.md - 개발 가이드라인

### 기타 메모
- 로그인 기능은 추후 개발 예정
- 텔레그램 알림 기능도 추후 구현
- 프론트엔드 개발 전에 API부터 완성
- 샘플 데이터로 테스트 후 실제 데이터 구조 확정