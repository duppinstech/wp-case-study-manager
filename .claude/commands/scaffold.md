---
description: Add a case study field, column, or taxonomy term path using this plugin's patterns
argument-hint: "[what to add]"
---

Scaffold: $ARGUMENTS

1. Read `OWNERSHIP.md`, `INTENT.md`, and `.claude/rules/api-conventions.md`.
2. Extend `DuppinsTech\CaseStudies\Plugin`. Do not add a second plugin class for a field.
3. A new stored field needs a meta key constant, `register_post_meta`, a metabox input with a nonce already on the box, sanitize-on-save, and an admin column only if editors need it in the list.
4. Keep existing `_ra_*` keys. A new key needs an explicit name Odell has accepted. Do not rename old keys in the same change.
5. Escape output and check `edit_post` on save.
6. Mention the field in `readme.txt` and `README.md` if editors or integrators must know it.
7. Copyright and author text stay Odell Duppins / Duppins Technology.
8. Hand the result to `test-writer`, then `security-reviewer`, then `.claude/workflows/departmental-review.md`.
