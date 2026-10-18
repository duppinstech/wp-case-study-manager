---
description: Review the current change, then run security review before calling it done
argument-hint: "[optional focus]"
---

Review the current Case Study Manager change. Focus: $ARGUMENTS

1. Read `OWNERSHIP.md`, `INTENT.md`, and `.claude/rules/code-style.md`, `api-conventions.md`, and `pr.md`.
2. Follow `.claude/skills/code-review/SKILL.md`.
3. Delegate to `security-reviewer` with the files changed and the diff summary.
4. If security returns `changes-requested` or `blocked`, fix only what that review required and run security again.
5. Report the security verdict. Do not claim the departmental gate is finished. Tell Odell that `/departmental-review` is the next step when he wants the other departments.
