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