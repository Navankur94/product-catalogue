# Git commands

Run from the project root. Replace the remote URL with your own GitHub/GitLab/Bitbucket repository.

```bash
# Initialise and create main (skip init if already cloned from your empty remote)
git init
git branch -M main
git add .
git commit -m "chore: scaffold product catalogue challenge"

# Add your remote
git remote add origin https://github.com/YOUR_USERNAME/product-catalogue.git
git push -u origin main

# Work on a feature branch and keep main clean
git checkout -b feature/product-catalogue-mvp
# implement / review / test

git status --short
git diff --check
git add .
git commit -m "feat: build extensible product catalogue MVP"
git push -u origin feature/product-catalogue-mvp
```

Open a pull request from `feature/product-catalogue-mvp` into `main` and **leave it open**. The challenge specifically asks for a PR, not only a pushed branch.

## Suggested PR description

```markdown
## Summary
- React + TypeScript catalogue UI
- Laravel API with replaceable product provider
- Fake Store adapter, featured/category/search/detail flows
- Validated enquiry email flow (log mailer by default)
- Docker Compose setup and feature tests

## Architecture
The browser calls Laravel only. Laravel owns product-provider integration, catalogue rules, validation and email dispatch. See README and DECISIONS.md.

## Assumptions and trade-offs
See DECISIONS.md.

## Verification
- [ ] `docker compose up --build`
- [ ] `docker compose exec backend php vendor/bin/phpunit`
- [ ] `docker compose exec frontend npm run build`
- [ ] Manually tested all acceptance criteria

## Known limitations
No DB-backed enquiry history, auth, pagination, queue or production SMTP credentials. See DECISIONS.md.
```

## Before pushing

```bash
git status --short
# Ensure no .env, credentials, vendor, node_modules, or build output is staged
git diff --cached --name-only
git diff --check
```
