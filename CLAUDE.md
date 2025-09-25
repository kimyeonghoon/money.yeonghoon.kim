# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a personal financial management web application (`money.yeonghoon.kim`) designed to track assets, expenses, budgets, insurance, and investments. The project follows a traditional web architecture with early 2000s styling.

## Architecture

### Technology Stack
- **Frontend**: Traditional HTML, CSS, jQuery served as static files directly from Nginx
- **Backend**: PHP API server (CodeIgniter or vanilla PHP) in Docker container
- **Database**: MySQL (containerized for development, external for production)
- **Infrastructure**: Docker Compose managing PHP-FPM + Nginx + MySQL containers

### Service Architecture
- **Static Assets**: Served directly by existing Nginx server on ports 80/443
  - Route: `/` → Frontend static files
- **API Endpoints**: Reverse proxied to Docker PHP container
  - Route: `/api/` → PHP API container
- **Database**: Environment-specific (container vs external server)

### Project Structure (Planned)
```
/project-root
├── backend/          # PHP API server (runs in Docker)
│   ├── api/
│   ├── config/
│   ├── lib/
│   ├── models/
│   ├── controllers/
│   └── index.php
├── frontend/         # Static web assets
│   ├── css/
│   ├── js/
│   ├── img/
│   └── index.html
├── docker/           # Docker configuration
│   ├── backend.Dockerfile
│   └── docker-compose.yml
└── .env             # Environment variables
```

## Environment Configuration

### Environment Variables (.env)
- Database connection details
- Telegram bot token and chat ID for login notifications
- AES-256-CBC encryption key (32-byte hex string)
- Login credentials (hashed or encrypted)

### Security Implementation
- Login authentication: SHA-512 hash or AES-256-CBC symmetric encryption
- Telegram webhook notifications on successful login
- Environment-based credential management using PHP `getenv()`

## Development Commands

Since this is an early-stage project without existing build tools:
- **Docker Development**: Use `docker-compose up` to start PHP + Nginx + MySQL containers
- **Database Management**: Configure via `.env` for development (container) vs production (external)
- **Static Files**: Deploy directly to existing Nginx server document root

## Key Features to Implement

1. **Dashboard View**: Single centralized page displaying all financial data (cash, investments, pensions, expenses, budgets, insurance)
2. **Asset Management**: Individual pages for CRUD operations with soft delete for cash, savings, investments, pensions
3. **Expense Tracking**: Individual page for recording and managing expense history
4. **Budget Planning**: Individual page for budget creation and management
5. **Insurance/Fixed Expenses**: Individual page for managing recurring costs
6. **Authentication**: Single-user login with Telegram notifications

## UI/UX Architecture

### Page Structure
- **Dashboard (/)**: Primary view showing comprehensive financial overview
  - Consolidated display of all asset types, recent expenses, budget status, and insurance summaries
  - Read-only view with navigation links to individual management pages
- **Individual Management Pages**: Dedicated CRUD interfaces
  - `/cash-assets`: Cash and savings account management
  - `/investment-assets`: Investment portfolio management
  - `/pension-assets`: Pension and retirement account management
  - `/daily-expenses`: Expense tracking and history
  - `/fixed-expenses`: Insurance and recurring cost management
  - `/prepaid-expenses`: Prepaid expense management

### Navigation Flow
- Users primarily interact with the dashboard for data viewing
- Management operations (create, update, delete) are performed on dedicated pages
- Each management page provides full CRUD functionality for its respective data type

## Development Guidelines

- Use traditional jQuery-based frontend patterns for consistency with 2000s styling
- Follow RESTful API conventions for backend endpoints
- Implement proper environment separation between development and production databases
- Ensure Docker container resource optimization (stop unused MySQL container when using external DB)
- Maintain security best practices for environment variable handling

## Next Priority Tasks (Tomorrow's Work)

### Immediate Priority (High)
1. **투자자산 페이지 모바일 최적화** - Apply cash-assets mobile optimization to investment-assets.php
   - 드래그 앤 드롭 순서 변경 기능
   - 모바일 친화적 카드 레이아웃
   - 인라인 잔액 편집 기능
   - 모달을 통한 전체 정보 편집

2. **연금자산 페이지 모바일 최적화** - Apply same optimizations to pension-assets.php
   - 현금성 자산과 동일한 UX 패턴 적용
   - 터치 친화적 인터랙션 구현

3. **일별지출 페이지 개선** - Enhance daily-expenses.php functionality
   - 날짜별 지출 내역 관리
   - 카테고리별 분류 및 필터링
   - 모바일 최적화된 입력 폼

### Medium Priority
4. **대시보드 통합 개선** - Enhance dashboard.php with all asset types
   - 모든 자산 유형 실시간 데이터 표시
   - 자산 분포 시각화 (차트/그래프)
   - 월별/연별 총계 및 증감률 표시

5. **고정지출/선납지출 페이지 완성** - Complete remaining asset management pages
   - fixed-expenses.php 모바일 최적화
   - prepaid-expenses.php 기능 개선

### Future Enhancements (Low Priority)
6. **로그인 인증 시스템** - Implement authentication with Telegram notifications
7. **PWA 기능 추가** - Add Progressive Web App capabilities for mobile usage
8. **데이터 백업/복원** - Implement export/import functionality for data management
9. **고급 차트 및 분석** - Add comprehensive financial analytics and reporting

### Current Status
- ✅ Cash Assets: Complete (mobile-optimized, drag & drop, inline editing)
- 🔧 Investment Assets: Basic structure ready, needs mobile optimization
- 🔧 Pension Assets: Basic structure ready, needs mobile optimization
- 🔧 Daily Expenses: Basic structure ready, needs functionality enhancement
- 🔧 Fixed/Prepaid Expenses: Basic structure ready, needs completion
- 📊 Dashboard: Partially complete, needs full integration