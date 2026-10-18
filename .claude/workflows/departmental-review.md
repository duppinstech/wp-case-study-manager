# Departmental review

Run this after engineering has a security `clear` from `code-review.md`, or when Odell asks to review the current tree.

The parent session coordinates. Each agent only sees its own prompt, so include:

- Process name
- Files changed
- Diff summary
- Reminder: Odell Duppins / Duppins Technology owns the code; RAHyman Restoration, Inc. is the client

## Order

After each department, run `security-reviewer` on that department's output before continuing.

1. `test-writer` — then security
2. `product-reviewer` — then security
3. `privacy-reviewer` — then security
4. `legal-reviewer` — then security
5. `finance-reviewer` — then security
6. `support-reviewer` — then security
7. `marketing-reviewer` — then security
8. `accessibility-reviewer` — then security
9. `release-reviewer` — then security

`research` is optional. If it causes a file change, security reviews that change immediately.

## Stops

- Any `blocked` verdict stops the pipeline.
- License text, a price, public-versus-private case studies, meta-key renames, live deploys, and WordPress.org uploads wait for Odell even when the agent drafts wording.
- Do not mark `PLAN.md` checkboxes done from a draft. Mark them when the change is in the tree and security has cleared it.

## Record

Append a short entry to `.claude/memory/progress.md`: date, process, verdicts, and anything still waiting on Odell.
