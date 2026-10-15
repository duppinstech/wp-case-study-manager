# Bug fix

Use this when behavior disagrees with `INTENT.md` or with the documented install path.

1. Read `OWNERSHIP.md` and reproduce the failure from the code. Do not use a live customer case study as the only evidence.
2. Fix the cause. Do not rename `_ra_*` keys and do not delete stored content.
3. If the bug exposed or hid location or repair amount, note the privacy decision in `PLAN.md` and ask Odell before changing visibility.
4. Run `.claude/workflows/code-review.md`.
5. Run `.claude/workflows/departmental-review.md`. Support and marketing review the docs if the fix changes what an editor should do.
6. Record the fix in `.claude/memory/progress.md`.
7. A vulnerability fix stays private until Odell follows `SECURITY.md`. Do not open a public issue for it.
8. Do not commit or push unless Odell asks.
