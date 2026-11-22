# Claude Code: planning-to-delivery workflow

Start Claude Code from the repository root so it can read `AGENTS.md`.

## Step 1 — Plan mode

Use Claude Code plan mode (or ask it not to edit files yet):

```text
Read README.md, DECISIONS.md, AGENTS.md and the challenge brief I provide. First inspect the repository. Do not modify files. Produce a concise implementation plan mapped to every acceptance criterion and technical constraint. Flag missing Laravel bootstrap/configuration, CORS, Docker, test and security concerns. Stay within a 2–3 hour time-box and do not add speculative scope.
```

Paste the challenge brief into the session or save it as `docs/CHALLENGE_BRIEF.md` before starting.

## Step 2 — Implement in small slices

```text
Implement the backend first. Ensure all product data comes from Laravel. Add a ProductProvider interface and FakeStore adapter; add catalogue service, routes, request validation, mail class and feature tests. Run the tests and fix failures. Summarize files and decisions.
```

Then:

```text
Implement the React TypeScript frontend against the existing Laravel API only. Add home, category, search, product details and enquiry experiences. Include loading, empty and error states, responsive styling and accessible form labels. Do not call Fake Store API from the browser. Run the TypeScript production build.
```

## Step 3 — Review, don't blindly accept

```text
Act as a skeptical senior reviewer. Compare the code with each acceptance criterion and each technical constraint. Check Laravel route registration, CORS, mail config, Docker startup from a clean checkout, provider isolation, validation, error handling and test coverage. Make only necessary corrections. Report any command that could not be run.
```

## Step 4 — Submission

```text
Review the complete Git diff. Check for secrets and generated files. Update README, DECISIONS.md and AI_USAGE.md with actual facts only. Do not invent test results, time spent, prompts or AI mistakes. Provide a final checklist and PR description.
```

## Suggested time-box

- 20 minutes: read brief, plan, assumptions.
- 70 minutes: backend and frontend critical path.
- 25 minutes: tests and debugging.
- 15 minutes: Docker/README/AI notes/review.

This is a suggested budget, not a claim about actual time spent. Record your real time in `AI_USAGE.md`.
