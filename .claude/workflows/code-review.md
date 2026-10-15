# Code review

Engineering review before the other departments.

1. Read the diff.
2. Apply `.claude/skills/code-review/SKILL.md`.
3. Delegate to `security-reviewer` with the process name `engineering` and the file list.
4. If the verdict is `changes-requested`, the parent fixes and this workflow runs again.
5. If the verdict is `blocked`, stop.
6. A `clear` verdict here clears engineering only. Departments in `departmental-review.md` still run when the work should be release-ready.
