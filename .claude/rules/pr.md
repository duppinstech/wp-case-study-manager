---
paths:
  - "**/*.php"
  - "**/*.md"
  - ".github/**"
---

# Pull requests

A change is ready to open when the parent session has finished the code and `.claude/workflows/departmental-review.md` has a `clear` verdict from security after each department that the change affects.

## Description

- What changed and why, in terms of `INTENT.md`.
- Files and meta keys touched.
- Departments run, and each verdict.
- Tests run, or "not run" with the reason.
- Human decisions still open (public data, product scope, price).

## Ownership

- Author and copyright stay Odell Duppins / Duppins Technology.
- RAHyman Restoration, Inc. appears only as the client the work is for.
- Do not present the client as owner, author, or copyright holder.

## Release files

If the change is user-visible, update `readme.txt` changelog and keep the stable tag aligned with the plugin header when cutting a release. Do not bump the version only in one file.

## Do not merge while blocked

Security `blocked`, a missing nonce or capability check, a meta-key rename without a migration, or an unapproved license or price change stays unmerged.
