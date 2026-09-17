# ATI-Caraga System

Web-Based Trainee Profiling, Document Status Tracking, and Competency Gap
Analysis System for the Agricultural Training Institute (ATI-Caraga).

A Laravel 10 REST API backend (MySQL via XAMPP) with a React + TypeScript +
Tailwind CSS single-page frontend. Three roles — **Administrator**,
**Project Officer**, and **Trainee** — each get their own module. All
trainee, training, document, and confirmation data lives in MySQL and is
read/written through the API; nothing is hardcoded in the UI. The only
seeded data is genuine reference/lookup data (the 5 fixed chain levels, the
6 required post-training document types, a Caraga-region province/city/LGU
income-class list, and one bootstrap admin account).

## Tech Stack

| Layer | Technology |
|---|---|
| Backend | PHP 8.2, Laravel 10, Laravel Sanctum (token auth) |
| Database | MySQL 8 / MariaDB (via XAMPP) |
| Frontend | React 18, TypeScript, Vite, Tailwind CSS 4 |
| Data fetching | TanStack Query, Axios |
| Reports | CSV export, PDF via barryvdh/laravel-dompdf |

## Prerequisites

- [XAMPP](https://www.apachefriends.org/) (MySQL + Apache) — only MySQL is required to be running; Apache is not used
- PHP 8.2+ and [Composer](https://getcomposer.org/)
- Node.js 20+ and npm

## Setup

### 1. Start MySQL

Start MySQL from the XAMPP Control Panel (or run `mysql_start.bat` in your
XAMPP folder). The backend expects it on `127.0.0.1:3306` with the default
`root` user and no password (standard XAMPP defaults).

### 2. Create the database

```bash
mysql -u root -e "CREATE DATABASE IF NOT EXISTS ati_caraga CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

(Or create a database named `ati_caraga` via phpMyAdmin.)

### 3. Backend (Laravel API)

```bash
cd backend
composer install
cp .env.example .env      # already pre-configured for XAMPP MySQL + CORS
php artisan key:generate
php artisan migrate --seed
php artisan serve          # http://127.0.0.1:8000
```

The seeder creates one bootstrap administrator account:

- **Username:** `admin`
- **Password:** `Admin@12345`

Log in as this admin first to create Project Officer and Trainee accounts
from the Users module — no other accounts are pre-seeded.

### 4. Frontend (React SPA)

```bash
cd frontend
npm install
cp .env.example .env       # already pre-configured to point at :8000
npm run dev                # http://localhost:5173
```

Open http://localhost:5173 and log in with the admin account above.

## Running Tests

```bash
cd backend
php artisan test
```

Tests run against an in-memory SQLite database (configured in
`phpunit.xml`) — they never touch your MySQL dev data. Coverage includes
the Competency Gap Analysis and Document Status Computation algorithms
(reproducing the capstone document's own Table 5.0 / Table 6.0 examples),
role-based access control, and the trainee self-service endpoints.

## Project Structure

```
ati/
  backend/    Laravel 10 API
    app/Http/Controllers/Api/   REST controllers (role-scoped)
    app/Services/                CompetencyGapAnalysisService, DocumentComplianceService
    app/Models/                  Eloquent models
    database/migrations/         Schema
    database/seeders/            Reference data only (no fake trainees)
    routes/api.php                All /api/v1 routes
    tests/                       Feature + Unit tests
  frontend/   Vite + React + TypeScript SPA
    src/api/          One module per API resource (Axios)
    src/context/       AuthContext (Sanctum token session)
    src/routes/         Role-guarded routing + layouts
    src/pages/admin/    Administrator Module
    src/pages/officer/  Project Officer Module
    src/pages/trainee/  Trainee Module
    src/pages/shared/   Dashboard & Reports (shared by admin/officer)
    src/components/     Shared UI kit
```

## Key Features (mapped to the capstone objectives)

- **Comprehensive Trainee Profiling** — full CRUD with demographic fields
- **Training History Records** — per-trainee attendance tied to chain levels
- **Document Status Tracking** — Yes/No per required document, automatic
  Passed/Failed overall status
- **Competency Gap Analysis** — automatic comparison of completed vs.
  required chain levels (Production, Post-Harvest, Processing, Marketing,
  Consumption)
- **Advanced Search & Filtering** — sex, age, province, city/municipality,
  LGU income class, year last attended, organization, educational level
- **Upcoming Training Confirmation** — trainees confirm/decline scheduled
  trainings
- **Activity History Search** — full printable (PDF) record per trainee
- **Data Export & Reports** — CSV exports for attendance, compliance,
  competency gaps, and demographics
- **Admin Audit Log** — every mutating action is recorded automatically
