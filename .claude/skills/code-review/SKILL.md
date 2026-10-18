---
name: code-review
description: Reviews Case Study Manager diffs for WordPress security, escaping, ownership, and project conventions. Use on a finished edit, pull request, or /review.
---

# Code review

Review the diff. Do not expand the task into new features.

Read `OWNERSHIP.md` and `.claude/rules/code-style.md`, `api-conventions.md`, and `pr.md`.

## Check

- Direct access guards still present on touched PHP files.
- Metabox and any new write path: nonce, `edit_post`, no autosave or revision write.
- Input sanitized. Output escaped. Repair amount stays numeric or empty.
- REST `auth_callback` still requires `edit_post` for meta writes.
- `_ra_*` keys unchanged unless the task is an approved migration.
- Uninstall still does not delete posts, terms, or meta.
- Author and copyright lines name Odell Duppins / Duppins Technology.
- The client is named only as who the work is for.
- No secrets, no remote calls, no new GPL-incompatible dependency.

## Report

Use the roster format in `AGENTS.md`. Severity, file, and the specific fix. Hand off to `security-reviewer`.
