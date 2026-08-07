# FlowForge — ERP Automation Platform

A production-style mini ERP with a **Visual Workflow Automation Engine**. FlowForge lets companies manage business data (customers, products, purchase requests, invoices) and automate business processes through a drag-and-drop workflow builder that actually executes.

## Stack

- **Backend:** Laravel 12 · PHP 8.4 · MySQL · Redis · Queues · Events · Scheduler · Sanctum · Notifications
- **Frontend:** React · TypeScript · Vite · Tailwind CSS · shadcn/ui · React Flow
- **Architecture:** Clean / modular monolith · Service layer · Repository · DDD concepts · REST API

## Modules

1. Mini ERP simulation (customers, products & inventory, purchase requests, invoices)
2. Workflow management system
3. Visual workflow builder (React Flow)
4. Workflow execution engine
5. Approval system + notifications
6. Analytics dashboard
7. Advanced (templates, versioning, audit logs, real-time monitoring with Reverb)
8. AI workflow generator

## Prerequisites

- PHP 8.2+ with MySQL driver
- Composer
- Node.js 18+ & npm
- MySQL 8 running with an empty database `flowforge`

## Install

```bash
composer install
cp .env.example .env
php artisan key:generate
# set DB_* in .env to your MySQL server
php artisan migrate --seed

npm install
php artisan serve --no-reload
npm run dev
```

> **Windows note:** run the dev server with `--no-reload`. Laravel's default env-filtering breaks the PHP built-in server socket on some Windows/PHP builds:
> `php artisan serve --no-reload`

## Demo accounts

| Role     | Email                  | Password   |
|----------|------------------------|------------|
| Admin    | admin@flowforge.dev    | `password` |
| Manager  | manager@flowforge.dev  | `password` |
| Finance  | finance@flowforge.dev  | `password` |
| Employee | employee@flowforge.dev | `password` |

Reset demo data at any time:

```bash
php artisan demo:reset --fresh
```

## API base

All endpoints live under `/api/v1` and use Sanctum bearer tokens (`Authorization: Bearer <token>`).

```
POST   /api/v1/auth/register      Register (default: Employee role)
POST   /api/v1/auth/login          Login → { token, user }
POST   /api/v1/auth/logout          Revoke current token      [auth:sanctum]
GET    /api/v1/auth/me              Current user              [auth:sanctum]
GET    /api/v1/users                List users (users.view)   [permission]
PUT    /api/v1/users/{user}/roles   Assign roles              [permission]
```

Responses use a consistent envelope: `{ success, message, data, meta? }`.
Paginators expose `meta: { current_page, per_page, total, last_page }`.

### Example

```bash
curl -X POST http://127.0.0.1:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@flowforge.dev","password":"password"}'
```

## Tests

```bash
php artisan test
```

Tests run against an in-memory SQLite database, so they don't touch your MySQL data.

## Run the queue worker

Workflow jobs use the queue. In development:

```bash
php artisan queue:work --tries=3
```

## Project layout

```
app/
  Console/Commands/Demo/        demo:reset command
  Domain/                       modular monolith (DDD-ish)
    User/                       user, roles, permissions, auth service
    Customer/  Product/  Invoice/  PurchaseRequest/  Approval/  Workflow/  Audit/  (Phase 2+)
  Http/Controllers/Api/V1/      API v1 controllers
  Support/                      ApiResponse helper
```

## License

Open-source (MIT).