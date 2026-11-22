# Decisions and assumptions

## Assumptions

1. **Top rated means highest numeric `rating.rate`.** The brief says the entrepreneur has chosen five top-rated products but provides no product IDs or admin workflow. For this PoC, select the five highest rated products dynamically; break ties by ascending product ID for deterministic output. This is easy to replace with a curated ID list or admin-managed featured flag later.
2. **Category page means both a featured subset and the full category.** Return the five highest-rated products in that category and all category products. The UI renders the featured subset first and excludes those IDs from the remaining-products section to avoid duplicates.
3. **Search scope.** A case-insensitive substring match across title, description and category is sufficient for a small catalogue. Search runs in Laravel, never in the browser against Fake Store.
4. **Email delivery.** A real sales mailbox cannot be assumed in a take-home environment. The app uses Laravel Mail and defaults to the `log` transport. This proves the email composition/delivery boundary locally without credentials. Configure SMTP using environment variables for a real inbox.
5. **Product enquiry fields.** Name and email are required; phone and message are optional. The form includes the product ID and title. The server validates all fields and limits lengths.
6. **Product source.** Fake Store API is the temporary source of truth as explicitly permitted by the brief. Product IDs and ratings are treated as provider data, not durable business identifiers.
7. **No persistence requirement.** The acceptance criteria do not require storing products or enquiries. Product data is cached in-process for five minutes; enquiries are sent by email and not stored.

## Deliberate simplifications / dropped scope

- **No admin interface or curated-featured editor:** no acceptance criterion or product metadata process was supplied. Deterministic rating sort demonstrates the experience without inventing an admin workflow.
- **No database:** avoids schema and migration work for data the external API already supplies. Add a database when the business needs managed catalogue data, auditability, or enquiry history.
- **No queue:** synchronous email keeps the PoC easy to run. Production email should be queued and retried.
- **No authentication, payments, cart, inventory, variants or checkout:** not requested and would dilute the critical path.
- **No pagination:** Fake Store's small demo catalogue is sufficient for a PoC. Add API pagination before using a large provider.
- **No UI design system or global state library:** a small set of reusable components and local React state is enough at this scale. Brand styling is deliberately provisional because final creative work is pending.
- **No paid email provider:** default log transport is free and requires no reviewer account. SMTP remains configurable.

## How the design can grow

- Replace `FakeStoreProductProvider` with another implementation of `ProductProvider` (database, commerce platform, or internal service) and bind it in `AppServiceProvider`. Controllers and React API contract need not know the upstream source.
- Move featured-product selection to a persisted `is_featured` field or editorial configuration when merchandising decisions become explicit.
- Add pagination, sorting, availability, currency and localized pricing at the API boundary as requirements emerge.
- Add a database-backed enquiry record and queue email delivery; use an outbox/retry strategy to avoid losing enquiries when email is unavailable.
- Replace visual styling through component tokens and a design system without changing API responsibilities.
- Add API versioning and contract tests before external consumers depend on the API.
- Deploy the stateless Laravel API and static React build to AWS (for example, container hosting plus object storage/CDN); inject secrets through AWS-managed configuration rather than images or source control.

## What I would do with another day

1. Confirm whether featured products are editorially selected or rating-driven and add a persisted merchandising model if required.
2. Add pagination and stable API resource/DTO shapes, plus contract tests for the provider adapter.
3. Persist enquiries with status and timestamps; dispatch email via a queue with retry and failure monitoring.
4. Add rate limiting, request correlation IDs, structured logs, security headers and stricter CORS/environment configuration.
5. Add frontend component tests and end-to-end tests for browse → detail → enquiry.
6. Add CI (Composer tests, TypeScript build, linting) and an AWS deployment pipeline.
7. Replace provisional styling with the supplied creative direction and accessibility review.

## Trade-offs to defend

- **Dynamic top-five vs hard-coded five:** dynamic ranking avoids inventing product IDs and reflects the wording “top rated”; a production merchandising decision may prefer explicit curation.
- **Cache vs live fetch per request:** five-minute cache reduces upstream latency and dependency pressure; invalidation is intentionally simple because the source is demo data.
- **Log mailer vs actual inbox:** makes reviewer setup deterministic and account-free; real delivery is a configuration change, not a rewrite.
- **No database:** minimizes scope and setup, but means enquiries are not durable. This is an explicit PoC limitation, not a production recommendation.
