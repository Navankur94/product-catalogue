# AI usage notes

> This repository scaffold and its initial source files were generated in this ChatGPT conversation using GPT-6. Claude Code has **not** been run against the repository from this environment. The prompts below are a workflow for your next step, not a record of prompts already sent to Claude Code. Update this file with your real Claude Code session, actual model/version, tests and time before submission.

## Tools and models

- Initial code/documentation generation: ChatGPT, GPT-6 (this conversation).
- Claude Code: not yet run; add the exact CLI version and model after you run it locally.
- Other tools: container-based file generation and PHP/JSON/YAML static syntax checks. Docker was unavailable in the generation environment; npm dependency installation did not complete. No claim is made that the app or PHPUnit suite has run successfully.
- Human review owner: repository author; review every file before submission.

## Agent instruction files

- `CLAUDE.md` and `AGENTS.md` contain repository-wide constraints for AI coding agents: preserve backend provider boundary, keep Fake Store calls server-side, validate inputs, test critical paths, and avoid unrequested complexity.

## Plan / spec

The implementation plan is recorded in `DECISIONS.md` and this README. The critical path is:

1. Establish React + TypeScript and Laravel API boundaries.
2. Implement the provider interface and Fake Store adapter.
3. Implement featured/category/search/detail API behaviour.
4. Implement validated enquiry and configurable Laravel Mail delivery.
5. Build browse, search, detail and enquiry UI states.
6. Add backend feature tests and Docker-based local setup.
7. Review the generated code, run tests/build, document actual outcomes and raise a PR.

## Key prompts / moments (record what you actually used)

The following are suggested prompts to use with Claude Code. Replace or annotate them with the exact prompts from your session.

1. **Plan before coding**
   > Read the entire challenge brief and repository. Do not code yet. Propose a 2–3 hour implementation plan, architecture, API contract, assumptions, risks and test plan. Respect React TypeScript + Laravel, ensure the browser never calls Fake Store API, and keep the product provider replaceable. Ask no clarification questions; record reasonable assumptions.

2. **Scaffold and boundaries**
   > Implement the Laravel API and React TypeScript application. Keep Fake Store integration behind a ProductProvider interface and adapter. Keep catalogue rules in a service. Use Laravel validation and stable JSON responses. Do not add a database or paid services unless necessary. Explain each changed file.

3. **Acceptance criteria**
   > Implement the five acceptance paths: five featured products on home; category top-rated plus remaining products; backend text search; product details; validated enquiry that sends a Laravel Mailable and returns a thank-you response. Handle upstream and mail failures.

4. **Testing and adversarial review**
   > Review the implementation against every acceptance criterion and technical constraint. Identify bugs, security issues, hidden assumptions and overengineering. Add tests for ranking, category filtering, search, product-not-found, invalid enquiry and successful email dispatch. Do not claim tests passed unless you run them.

5. **Submission readiness**
   > Verify Docker Compose setup from a clean state, README commands, environment examples, no committed secrets, and a clean Git diff. Draft DECISIONS.md, AI_USAGE.md and PR description. Clearly distinguish checks actually run from checks not run.

## Where AI was wrong / corrections

Record real examples from your session. If none occurred, write “No material AI errors observed” rather than inventing one.

| AI suggestion/output | Why it was wrong or risky | How it was caught and corrected |
|---|---|---|
| [Fill in] | [Fill in] | [Fill in] |

## Human review checklist

- [ ] Read every generated source file and understand the implementation.
- [ ] Confirm Fake Store is called only by Laravel.
- [ ] Confirm product provider interface is the only catalogue dependency on upstream data.
- [ ] Confirm validation and error responses are sensible.
- [ ] Confirm no credentials or personal data are committed.
- [ ] Run backend tests and frontend production build; record exact outcomes.
- [ ] Manually exercise all five acceptance criteria.
- [ ] Review Git diff and PR description.

## Actual time spent

The generation environment did not record a reliable task-specific wall-clock split. Fill this in honestly for your own work and Claude Code session before submission. Suggested categories:

| Activity | Actual time |
|---|---:|
| Planning / reading brief | [ ] minutes |
| Implementation | [ ] minutes |
| Testing / debugging | [ ] minutes |
| Review / documentation / PR | [ ] minutes |
| **Total** | **[ ] minutes** |
