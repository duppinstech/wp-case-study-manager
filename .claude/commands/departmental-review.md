---
description: Run every department review, with security after each one
---

Run the post-development gate in `.claude/workflows/departmental-review.md`.

The parent session stays the coordinator. Invoke each agent in order. Pass the process name, files changed, and diff summary. After each department, invoke `security-reviewer` before the next department.

Stop on `blocked`. For `changes-requested`, apply the fix, rerun that department, then rerun security.

Do not invent a price. Do not move copyright to RAHyman Restoration, Inc. Do not deploy.

End with a table of department, verdict, and whether security cleared that step.
