# Northstar — Product Catalogue PoC

A small, extensible product catalogue built for the AI-Native Engineer take-home challenge. The client is **React + TypeScript** and the backend is **PHP 8.3 + Laravel 12**. Product data flows through Laravel; the browser never calls Fake Store API.

## What is included

- Home page with five featured products, selected deterministically by highest rating.
- Category browsing with the five highest-rated products followed by the remaining products.
- Backend search across product title, description and category.
- Product detail page.
- Enquiry form with server-side validation and a sales email using Laravel Mail.
- Provider interface and Fake Store adapter, isolated from catalogue business logic.
- Six Laravel feature tests covering health, search, category results, featured ranking, product 404, and enquiry validation/email.
- Docker Compose for local setup.

## Architecture

```text
React + TypeScript (localhost:5173)
           | HTTP / JSON
           v
Laravel REST API (localhost:8000)
    |                     |
    v                     v
ProductProvider       EnquiryController
    |                     |
FakeStore adapter      Laravel Mail
    |                     |
Fake Store API         Sales inbox
```

`App\Contracts\ProductProvider` is the boundary for product data. `FakeStoreProductProvider` owns upstream HTTP calls and a five-minute cache. `CatalogueService` owns filtering, category selection and featured-product ranking. The React app knows only the Laravel API contract.

## Prerequisites

- Docker Desktop or Docker Engine with the Compose plugin.
- Git.
- Internet access on first start so Composer/npm can download dependencies and the backend can reach Fake Store API.

No paid service, API key, database, or external account is required.

## Run locally (under 10 minutes)

From the repository root:

```bash
cp backend/.env.example backend/.env
docker compose up --build
```

Then open:

- Web app: http://localhost:5173
- API health: http://localhost:8000/api/health
- Products API: http://localhost:8000/api/products

The first run installs Composer and npm dependencies inside containers. Keep the terminal open. Stop with `Ctrl+C`, then run `docker compose down`.

### Email behaviour

The default `MAIL_MAILER=log` writes the generated email to the Laravel log; this makes the enquiry flow demonstrable without credentials or a paid provider. Inspect it with:

```bash
docker compose exec backend sh -lc 'tail -n 100 storage/logs/laravel.log'
```

To send real email, configure a reachable SMTP service in `backend/.env` and set `MAIL_MAILER=smtp`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_FROM_ADDRESS`, and `SALES_INBOX`. Do not commit the real `.env` file. SMTP is intentionally not required for local review.

### Useful commands

```bash
# Backend tests
docker compose exec backend php artisan test

# Frontend production build
docker compose exec frontend npm run build

# View logs
docker compose logs -f backend frontend

# Stop and remove containers (named node_modules volume is retained)
docker compose down

# Full clean restart, including installed frontend dependencies
docker compose down -v
docker compose up --build
```

## API contract

| Method | Endpoint | Purpose |
|---|---|---|
| GET | `/api/health` | Liveness check |
| GET | `/api/products` | All products; optional `?q=` and `?category=` filters |
| GET | `/api/products/featured` | Five highest-rated products; optional `?category=` |
| GET | `/api/categories` | Unique categories |
| GET | `/api/categories/{category}/products` | `featured` and all `products` for a category |
| GET | `/api/products/{id}` | Product details |
| POST | `/api/enquiries` | Validate and send a product enquiry |

Example enquiry body:

```json
{
  "product_id": 1,
  "product_title": "Example product",
  "name": "Alex Customer",
  "email": "alex@example.com",
  "phone": "+1 555 0100",
  "message": "Please contact me about availability."
}
```

The enquiry endpoint returns HTTP `201` and a thank-you message on success, `422` for invalid input, and `503` if email delivery fails. Product endpoints return JSON and use `503` for upstream provider failures.

## Configuration

Backend settings are documented in `backend/.env.example`; frontend API base URL is in `frontend/.env.example`. The default Vite URL is `http://localhost:8000/api`. If you change ports or hostnames, update the frontend API URL and Laravel CORS allowed origin (`backend/config/cors.php`).

## Tests and quality checks

```bash
docker compose exec backend php artisan test
docker compose exec frontend npm run build
```

The backend tests fake upstream HTTP and email; they do not depend on Fake Store availability or send real email. Frontend manual acceptance checks:

1. Home loads exactly five featured products.
2. Category link shows up to five top-rated products and the rest of that category.
3. Search for a term returns only products matching title, description or category.
4. Product tile opens a detail page.
5. Valid enquiry shows a thank-you message and creates a logged email; invalid email is rejected by the API.

## Repository / PR workflow

See the Git commands in `docs/GIT_WORKFLOW.md`. Build on a feature branch and leave the pull request open against `main`, as requested by the challenge. The PR description should include scope, architecture, decisions, tests actually run, and known limitations.

## Challenge notes

- `DECISIONS.md`: assumptions, deliberate simplifications, evolution path and next-day plan.
- `AI_USAGE.md`: AI-assisted workflow, prompt log template and review checklist. Fill in the exact tools/models and actual time spent after your own Claude Code session; do not claim a command or test ran unless it did.
- `AGENTS.md`: persistent instructions for Claude Code and other coding agents.

## Known limitations

This is an intentionally time-boxed PoC. It does not include authentication, admin product editing, pagination, enquiry persistence, rate limiting, or a production SMTP integration. The product source is a third-party demo API and its metadata/rating quality is not guaranteed. See `DECISIONS.md` for the rationale and next steps.
