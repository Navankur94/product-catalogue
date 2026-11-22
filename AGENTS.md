# AI coding agent instructions

## Goal
Build a small, maintainable product catalogue proof of concept using React + TypeScript and Laravel. Follow the challenge acceptance criteria; avoid speculative features.

## Architecture rules
- The browser must call only the Laravel API. Never call Fake Store API from React.
- Keep the upstream provider behind `App\Contracts\ProductProvider`; provider-specific HTTP and caching belong in its adapter.
- Keep filtering, featured-product selection and category composition in backend services, not UI components.
- Keep request validation in Laravel and return stable JSON response shapes.
- Keep email delivery behind Laravel Mail. Default local mail transport must not require an account.
- Do not add a database, authentication, state library, or paid dependency without a documented requirement.

## Quality rules
- TypeScript strict mode; avoid `any`.
- Add or update tests for acceptance-critical behaviour and failure cases.
- Handle loading, empty and error states in the UI.
- Never commit credentials. Update `.env.example` when adding configuration.
- Keep changes small and explain trade-offs in `DECISIONS.md`.
- Before finishing, run backend tests and frontend production build where the environment permits; report commands and actual outcomes honestly.
