---
paths:
  - "tests/**"
  - "**/*Test.php"
  - "**/phpunit*.xml*"
---

# Testing standard

`php tests/detail-rows-test.php` checks location lines, public rows, and escaping without WordPress. Metabox nonce, REST auth, and uninstall behavior still need the WordPress test library. Absence of those tests is a gap in `PLAN.md`, not a passing run.

Smoke test, when a check needs WordPress: http://127.0.0.1:8093 (compose project `case-study-manager-local`). Start with `docker compose up -d`, then stop with `docker compose down`. Credentials are in `CLAUDE.local.md`. Do not run `docker compose down -v` without asking.

When tests are added:

- Use PHPUnit with the WordPress test suite, PHP 7.4 compatible.
- Cover metabox save: valid nonce and `edit_post` persists sanitized values; missing nonce, autosave, and a user who cannot edit do not write.
- Cover `_ra_location_full` for city only, state only, and `City, State`.
- Cover repair amount: blank stays `''`, numeric input is stored as a float, non-numeric input does not inject markup.
- Cover REST meta auth: a user who cannot `edit_post` cannot update the meta.
- Cover uninstall: `uninstall.php` does not delete posts, terms, or meta.
- Do not require live client content, a live site, or secrets.
- Do not claim a test passed unless it was executed in this session.

Until the suite exists, reviewers state "not run" and name the behavior that lacks a test. They do not invent a green check.
