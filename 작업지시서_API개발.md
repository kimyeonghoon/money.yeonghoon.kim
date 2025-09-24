# 작업 지시서

## 기본 정보
- **작업 제목**: 자산 관리 REST API 개발 ✅ **완료**
- **작업 유형**: [x] 신규 개발 [ ] 기능 수정 [ ] 버그 수정 [ ] 리팩토링 [ ] 문서화
- **우선순위**: [x] 높음 [ ] 보통 [ ] 낮음
- **실제 소요시간**: 1일 (2025-09-24 완료)

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
- [x] 모든 API 엔드포인트가 정상 동작
- [x] 에러 처리가 적절하게 구현
- [x] 데이터베이스에서 데이터 CRUD 정상 확인
- [x] API 응답 시간 1초 이내
- [x] 한글 인코딩 문제 없음

### 테스트 완료 결과 (2025-09-24)
**✅ 기본 CRUD 작업 테스트 완료:**
- GET /api/cash-assets - 목록 조회 ✓
- GET /api/cash-assets/{id} - 단일 조회 ✓
- POST /api/cash-assets - 생성 ✓
- PUT /api/cash-assets/{id} - 수정 ✓
- DELETE /api/cash-assets/{id} - 논리삭제 ✓

**✅ 고급 기능 테스트 완료:**
- 대시보드 API: 통합 자산/지출 데이터 제공 ✓
- 일별 지출 API: 7일간 샘플 데이터 출력 ✓
- 투자/연금 자산 API: 수익률 계산 포함 ✓
- 페이징 시스템: total, page, limit 정상 작동 ✓

**✅ 에러 처리 테스트 완료:**
- 유효성 검사: 잘못된 입력시 한국어 에러메시지 ✓
- 404 에러: 존재하지 않는 ID 조회시 적절한 응답 ✓
- 논리삭제: 삭제된 항목은 조회되지 않음 ✓

**📊 실제 데이터 계산 결과:**
- 총 자산: 52,133,111원
- 투자 수익률: 5.81%
- 연금 수익률: 44.23%
- 월 고정지출: 662,873원
- 선납지출: 127,579원

## 추가 정보
### 참고 자료
- 기획서.md - 전체 프로젝트 요구사항
- 테이블_설계.sql - 데이터베이스 구조
- CLAUDE.md - 개발 가이드라인

### 개발 환경 설정
**Docker 구성:**
```bash
# 컨테이너 실행
cd docker && docker compose up -d

# 서비스 확인
- API 서버: http://localhost:8080
- phpMyAdmin: http://localhost:8081 (선택사항)
- MySQL: localhost:3306
```

**테스트 예시:**
```bash
# 현금성 자산 목록 조회
curl "http://localhost:8080/api/cash-assets"

# 대시보드 요약 데이터
curl "http://localhost:8080/api/dashboard"

# 새 자산 추가
curl -X POST "http://localhost:8080/api/cash-assets" \
  -H "Content-Type: application/json" \
  -d '{"type": "현금", "item_name": "테스트", "balance": 100000}'
```

### 다음 단계
- **프론트엔드 개발**: jQuery 기반 2000년대 스타일 UI
- **로그인 기능**: SHA-512 해시 + 텔레그램 알림
- **프로덕션 배포**: Nginx 리버스 프록시 설정