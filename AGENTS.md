# Agents

Roster for Claude Code. The parent session is engineering. It implements the change, then delegates. Subagents live in `.claude/agents/` and only see their own file plus the prompt you send, so name the process, the files touched, and the ownership rule in every handoff.

Read `OWNERSHIP.md` before delegating. Odell Duppins / Duppins Technology owns this work. RAHyman Restoration, Inc. is the client the work is for and does not own the source.

## Plugin facts

- Local site: Docker, http://127.0.0.1:8093 (compose project case-study-manager-local; details and credentials in CLAUDE.local.md)

## Parent session

Engineering. Implements features and fixes, applies approved edits, and does not mark the task done until the departmental workflow finishes or Odell says to stop after code.

## Departments

| Order | Department | Agent | Job |
|------:|------------|-------|-----|
| 1 | Quality | `test-writer` | What the change must prove, and the tests that do not exist yet |
| 2 | Product | `product-reviewer` | Matches `INTENT.md` without breaking meta keys or uninstall behavior |
| 3 | Privacy | `privacy-reviewer` | Public case studies, REST meta, and client-supplied content |
| 4 | Legal | `legal-reviewer` | License, disclaimer, copyright, and client naming |
| 5 | Finance | `finance-reviewer` | Whether any price or commercial term is now wrong or missing |
| 6 | Support | `support-reviewer` | Install, permalinks, uninstall, and FAQ still match the code |
| 7 | Marketing | `marketing-reviewer` | Public claims match the code and the ownership line |
| 8 | Accessibility | `accessibility-reviewer` | Admin metabox labels, contrast, and inputs |
| 9 | Release | `release-reviewer` | Version, changelog, and zip contents |
| — | Research | `research` | External facts on demand. No policy decisions |
| after each | Security | `security-reviewer` | Gate after development and after every department above |

## Handoff

1. Send the department the process name, the diff summary, and the files changed.
2. Wait for `clear`, `changes-requested`, or `blocked`.
3. If the verdict is `changes-requested`, the parent applies the fix and runs that department again.
4. Invoke `security-reviewer` on that process's output before the next department starts.
5. `blocked` stops the pipeline. A license change, a new price, a visibility change, a meta-key rename, a live deploy, or a WordPress.org upload waits for Odell.

Security's first pass after development reviews the code. Later passes review that department's delta and any advice that would weaken escaping, capabilities, or ownership.

## Output every agent returns

```text
Department:
Process reviewed:
Verdict: clear | changes-requested | blocked
Findings:
Files to update:
Human approval needed:
Handoff:
```

Each finding names a severity (`blocker`, `high`, `medium`, `low`), the file or behavior, and the owner of the fix.

## Commands

From this repository:

```text
composer install
composer lint
composer test
```

`composer lint` runs `phpcs`. `composer test` runs `php tests/detail-rows-test.php`. There is no build command. `composer format` runs `phpcbf`.

## Conventions

WordPress PHP: tabs, `array()`, Yoda conditions, and the `case-study-manager` text domain. `phpcs.xml.dist` uses WordPress-Core.

## Do not touch

- Meta keys `_ra_location_city`, `_ra_location_state`, `_ra_case_type`, `_ra_repair_amount`, and `_ra_location_full`.
- Case study posts, terms, and meta on uninstall.
- Price, upgrade path, or license gate inside the plugin.
- `.env` files, `CLAUDE.local.md`, and `.claude/settings.local.json`.
