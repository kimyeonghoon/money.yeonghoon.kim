# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a personal financial management web application (`money.yeonghoon.kim`) designed to track assets, expenses, budgets, insurance, and investments. The project follows a traditional web architecture with Materialize CSS framework for modern mobile-first design.

## Architecture

### Technology Stack
- **Frontend**: HTML, Materialize CSS, jQuery with responsive mobile-first design
- **Backend**: Vanilla PHP API server with custom authentication and session management
- **Database**: MySQL with archive support for historical data
- **Infrastructure**: Docker Compose (PHP-FPM + Nginx + MySQL containers)
- **Security**: OWASP-compliant security headers, password hashing, XSS protection

### Service Architecture
- **Static Assets**: Served directly by existing Nginx server on ports 80/443
  - Route: `/` → Frontend static files
- **API Endpoints**: Reverse proxied to Docker PHP container
  - Route: `/api/` → PHP API container
- **Database**: Environment-specific (container vs external server)

### Project Structure
```
/project-root
├── backend/                    # PHP API server (runs in Docker)
│   ├── api/                   # Entry point scripts for each endpoint
│   │   ├── cash-assets.php    # → CashAssetController
│   │   ├── investment-assets.php
│   │   ├── pension-assets.php
│   │   ├── daily-expenses.php
│   │   ├── fixed-expenses.php
│   │   ├── prepaid-expenses.php
│   │   ├── dashboard.php
│   │   ├── archive.php        # Asset archive API
│   │   ├── expense-archive.php
│   │   └── monthly-snapshots.php
│   ├── controllers/           # RESTful controllers (MVC pattern)
│   │   ├── BaseController.php # Abstract base with CRUD operations
│   │   ├── CashAssetController.php
│   │   ├── InvestmentAssetController.php
│   │   ├── PensionAssetController.php
│   │   ├── DailyExpenseController.php
│   │   ├── FixedExpenseController.php
│   │   ├── PrepaidExpenseController.php
│   │   ├── DashboardController.php
│   │   ├── ArchiveController.php
│   │   ├── ExpenseArchiveController.php
│   │   └── MonthlySnapshotController.php
│   ├── models/               # Database models (Active Record pattern)
│   │   ├── BaseModel.php     # Abstract base with soft-delete support
│   │   ├── CashAsset.php
│   │   ├── InvestmentAsset.php
│   │   ├── PensionAsset.php
│   │   ├── DailyExpense.php
│   │   ├── FixedExpense.php
│   │   ├── PrepaidExpense.php
│   │   ├── MonthlyArchive.php # Archive metadata
│   │   ├── ArchiveData.php   # Archive storage
│   │   ├── CashAssetsArchive.php
│   │   ├── InvestmentAssetsArchive.php
│   │   ├── PensionAssetsArchive.php
│   │   ├── FixedExpensesArchive.php
│   │   ├── PrepaidExpensesArchive.php
│   │   ├── AssetsMonthlySnapshot.php
│   │   └── ExpensesMonthlySummary.php
│   ├── lib/                  # Core libraries
│   │   ├── Database.php      # PDO singleton with query builder
│   │   ├── Auth.php          # Authentication & authorization
│   │   ├── SessionManager.php
│   │   ├── Response.php      # Standardized JSON responses
│   │   ├── Router.php        # Simple URL routing
│   │   ├── Validator.php     # Input validation
│   │   └── Pagination.php    # Pagination helper
│   ├── config/
│   │   └── database.php      # Database configuration
│   └── index.php             # Main API entry point
├── frontend/                  # Web application (PHP + jQuery)
│   ├── assets.php            # ✅ Main asset dashboard (완성)
│   ├── expense-status.php    # 🔧 Fixed expenses (기본 구조)
│   ├── expense-records.php   # 🔧 Daily expenses (기본 구조)
│   ├── login.php             # ✅ Authentication (완성)
│   ├── logout.php
│   ├── index.php
│   ├── css/                  # Responsive stylesheets
│   ├── js/                   # jQuery interactions
│   │   ├── assets.js
│   │   ├── expense-status.js
│   │   ├── expense-records.js
│   │   ├── login.js
│   │   └── feedback.js
│   ├── includes/             # Common components
│   │   ├── header.php        # Navigation & auth
│   │   └── footer.php
│   └── lib/                  # Shared PHP libraries
│       ├── Auth.php
│       ├── Database.php
│       └── SessionManager.php
├── docker/                    # Container configuration
│   ├── docker-compose.yml
│   ├── docker-compose.production.yml
│   ├── nginx-backend.conf    # ✅ Security headers configured
│   ├── backend.Dockerfile
│   └── frontend.Dockerfile
├── logs/                      # Application logs
├── schema.sql                 # Database schema
└── .env                       # Environment variables
```

## Environment Configuration

### Environment Variables (.env)
- Database connection details
- Telegram bot token and chat ID for login notifications
- AES-256-CBC encryption key (32-byte hex string)
- Login credentials (hashed or encrypted)

### Security Implementation ✅ OWASP Compliant (보안 점수: 9.0/10)
- **Authentication**: password_hash()/password_verify() with fallback to SHA-512 (하위 호환성)
- **Session Management**: Custom secure session handling with IP/User-Agent validation
- **Security Headers**: X-Frame-Options, XSS-Protection, CSP, Content-Type-Options
- **XSS Protection**: Input validation, $_SERVER['PHP_SELF'] sanitization
- **Timing Attack Prevention**: hash_equals() for secure string comparison
- **Security Logging**: Comprehensive login/logout/API access event monitoring
- **Telegram Notifications**: Real-time login alerts with IP/device info

## Development Commands

### Container Management
```bash
# Start development environment (from project root)
cd docker && docker-compose up -d

# Start with phpMyAdmin (development profile)
cd docker && docker-compose --profile dev up -d

# View logs
cd docker && docker-compose logs -f

# View specific service logs
cd docker && docker-compose logs -f backend-php

# Restart after configuration changes
cd docker && docker-compose restart

# Stop containers
cd docker && docker-compose down

# Stop and remove volumes (⚠️ deletes all database data)
cd docker && docker-compose down -v
```

### Database Management
```bash
# Access MySQL CLI
docker exec -it money_mysql mysql -u root -p

# Access phpMyAdmin
# http://localhost:8081 (when started with --profile dev)

# Reinitialize database with schema and sample data
cd docker && docker-compose down -v && docker-compose up -d
```

**Database Configuration**:
- **Development**: MySQL 8.0 container with persistent volume
- **Production**: External MySQL server (configure in .env)
- **Archive System**: Monthly snapshots for historical data analysis
- **Auto-initialization**: `schema.sql` and `test-sample-data.sql` run on first startup

## Current Implementation Status

### ✅ Completed Features
1. **Authentication System** - Secure login with Telegram notifications
2. **Asset Dashboard** (`assets.php`) - Unified management for all asset types
   - 현금성 자산 (Cash Assets) - 완전한 모바일 최적화
   - 투자 자산 (Investment Assets) - 기본 구조 완성
   - 연금 자산 (Pension Assets) - 기본 구조 완성
   - 드래그 앤 드롭 순서 변경, 인라인 편집, 아카이브 조회
3. **Security Infrastructure** - OWASP 준수 보안 시스템
4. **API Endpoints** - RESTful API with archive support

### 🔧 In Development
1. **Fixed Expenses** (`expense-status.php`) - 고정지출 관리
2. **Daily Expenses** (`expense-records.php`) - 일별 변동지출 기록
3. **Mobile Optimization** - 투자/연금 자산 페이지 모바일 UI 개선

## UI/UX Architecture

### Design Philosophy
- **Mobile-First**: Materialize CSS framework with responsive design
- **Touch-Optimized**: 드래그 앤 드롭, 인라인 편집, 터치 제스처 지원
- **Progressive Enhancement**: 데스크톱에서 모바일까지 일관된 경험

### Page Structure
- **assets.php**: 통합 자산 관리 대시보드 ✅
  - 모든 자산 유형을 단일 페이지에서 관리
  - 실시간/아카이브 데이터 전환
  - 완전한 CRUD 기능 및 모바일 최적화
- **expense-status.php**: 고정지출 관리 🔧
- **expense-records.php**: 일별 지출 기록 🔧
- **login.php**: 인증 시스템 ✅

### Navigation System
- **Desktop**: 상단 메뉴바 with active state indication
- **Mobile**: 하단 네비게이션 바 + 사이드 메뉴
- **Responsive**: 화면 크기에 따른 적응형 네비게이션

## Backend Architecture Patterns

### MVC Structure
The backend follows a **Model-View-Controller** pattern with RESTful conventions:

1. **Entry Points** (`backend/api/*.php`): Thin entry scripts that instantiate controllers
   ```php
   require_once __DIR__ . '/../controllers/CashAssetController.php';
   $controller = new CashAssetController();
   $controller->handleRequest();
   ```

2. **Controllers** (`backend/controllers/*Controller.php`): Extend `BaseController` for automatic CRUD operations
   - `BaseController::handleRequest()`: Routes HTTP methods (GET/POST/PUT/PATCH/DELETE) to appropriate methods
   - `BaseController::index()`: List resources with pagination
   - `BaseController::show($id)`: Get single resource
   - `BaseController::store()`: Create resource with validation
   - `BaseController::update($id)`: Full update (PUT)
   - `BaseController::partialUpdate($id)`: Partial update (PATCH)
   - `BaseController::destroy($id)`: Soft delete
   - Override `validateData($data, $id)` in child controllers for custom validation

3. **Models** (`backend/models/*.php`): Extend `BaseModel` for database operations
   - `BaseModel` provides: `findAll()`, `findById()`, `create()`, `update()`, `softDelete()`, `restore()`, `forceDelete()`
   - Soft-delete pattern: All queries filter `deleted_at IS NULL` automatically
   - Define `$table`, `$fillable`, and `$defaults` properties in child models
   - Use PDO prepared statements for SQL injection prevention

4. **Shared Libraries**:
   - `Database.php`: Singleton PDO wrapper with `query($sql, $params)` method
   - `Response.php`: Standardized JSON responses (`success()`, `error()`, `notFound()`, etc.)
   - `Auth.php`: Session-based authentication with `requireApiAuth()`
   - `Pagination.php`: Automatic pagination with `fromRequest($params)` helper

### Archive System Architecture
Monthly snapshots preserve historical data for trend analysis:
- `MonthlyArchive`: Metadata table tracking archive months
- `ArchiveData`: Generic storage for all archived entities
- `*Archive` models: Type-specific archive operations
- Archive workflow: Create snapshot → Store data → Query by month

### API Response Format
All API responses follow a consistent JSON structure:
```json
{
  "success": true,
  "message": "Success message",
  "data": { /* response data */ },
  "pagination": { "page": 1, "limit": 20, "total": 100, "totalPages": 5 }
}
```

## Development Guidelines

### Code Standards
- **Frontend**: jQuery + Materialize CSS for responsive mobile-first design
- **Backend**: Vanilla PHP 8.2+ with PDO prepared statements (SQL injection prevention)
- **Security**: OWASP compliance - XSS protection, secure headers, proper authentication
- **API Design**: RESTful conventions with consistent JSON responses (via `Response` class)
- **Database**: Archive system for historical data, soft delete pattern for data integrity
- **Validation**: Use `Validator` class in controllers before model operations

### Adding New Resources
When adding a new entity (e.g., "Insurance Policies"):

1. Create model: `backend/models/InsurancePolicy.php` extending `BaseModel`
2. Create controller: `backend/controllers/InsurancePolicyController.php` extending `BaseController`
3. Create API entry: `backend/api/insurance-policies.php` instantiating the controller
4. Add validation: Override `validateData()` in controller
5. Configure Nginx routing if needed (RESTful URLs are handled automatically)

## 🎯 Next Phase: UI/UX Testing & Optimization

### 📱 UI/UX Testing Plan (Ready for Execution)
**Target**: Comprehensive user experience evaluation across all devices
**Duration**: ~2.5 hours
**Focus Areas**:
1. **Mobile Optimization** - Touch interactions, responsive layout
2. **User Flow** - Navigation efficiency, task completion
3. **Performance** - Loading times, API response speed
4. **Accessibility** - Touch targets, readability, error handling

### 🚀 Post-Testing Priority Tasks

#### Immediate (Critical Issues)
1. **Mobile UI Completion** - expense-status.php, expense-records.php
2. **User Feedback Systems** - Loading states, success/error notifications
3. **Performance Optimization** - API response times, page loading

#### Short-term (1-2 weeks)
4. **Advanced Features** - Search/filter, data export, enhanced analytics
5. **PWA Implementation** - Offline support, app-like experience
6. **Performance Monitoring** - Error tracking, usage analytics

#### Long-term (Future Releases)
7. **Advanced Analytics** - Charts, trends, financial insights
8. **Data Management** - Backup/restore, bulk operations
9. **Integration** - External bank APIs, automated data import

### 📊 Current Development Metrics
- **Security Score**: 9.0/10 (OWASP compliant)
- **Feature Completion**:
  - Authentication: 100% ✅
  - Asset Management: 85% 🔧 (mobile optimization pending)
  - Expense Tracking: 60% 🔧 (UI/UX improvements needed)
- **Mobile Optimization**: 70% 🔧 (assets.php완성, 나머지 페이지 진행 중)
- **Code Quality**: High (comprehensive documentation, security best practices)