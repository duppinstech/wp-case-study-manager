---
name: pr-description
description: Writes a Case Study Manager pull request description with ownership, test status, and department verdicts. Use when opening or editing a pull request.
---

# Pull request description

Follow `.claude/rules/pr.md`. Write the description. Do not open the pull request unless Odell asked.

## Shape

```markdown
## Summary
<what changed and why>

## Ownership
Code copyright remains Odell Duppins / Duppins Technology. Client named only as who the work is for: RAHyman Restoration, Inc.

## Test plan
- [ ] <behavior to click or assert>
- Tests run: <command and result, or not run>

## Department verdicts
| Department | Verdict | Security after |
|------------|---------|----------------|
```

Leave a row blank rather than marking `clear` for a department that did not run.

If a price, license, or public-visibility decision is still open, say so under Summary.
