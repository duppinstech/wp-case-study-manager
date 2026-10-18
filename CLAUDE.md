# Case Study Manager

WordPress plugin. Author and copyright owner: Odell Duppins / Duppins Technology. Client the work is for: RAHyman Restoration, Inc. Version comes from the `Version` header in `case-study-manager.php` (currently 1.2.0).

## Read first

- `OWNERSHIP.md` — Odell Duppins owns the work. The client does not.
- `INTENT.md` — why this plugin exists.
- `PLAN.md` — work still required.
- `AGENTS.md` — who reviews after development.

## What this is

Public `case_study` post type, hierarchical `case_study_category` taxonomy, and meta for city, state, job type, and total repair amount. The single case study page and `[case_study_details]` render those values. Admin columns, REST meta, and Elementor support. License: GPL-2.0-or-later. No price or upgrade gate in the plugin. GPL grants sharing rights and does not transfer ownership.

## Stack

- PHP 7.4+, WordPress 5.8+ (`readme.txt` tested up to 6.9)
- Namespace `DuppinsTech\CaseStudies`
- Bootstrap: `case-study-manager.php`
- Logic: `includes/class-case-study-manager-plugin.php`
- Admin CSS: `assets/css/admin-metabox.css`
- Text domain: `case-study-manager`
- Composer is dev-only (`composer test`, `composer lint`). No production dependencies.

## Hard rules

- `defined( 'ABSPATH' ) || exit;` on PHP entry points. Uninstall uses `WP_UNINSTALL_PLUGIN`.
- Keep meta keys stable: `_ra_location_city`, `_ra_location_state`, `_ra_case_type`, `_ra_repair_amount`, `_ra_location_full`. The `_ra_` prefix is compatibility with existing client content, not an ownership mark.
- Uninstall must leave case study posts, terms, and meta in place. That content belongs to the client or its customers.
- Escape output, sanitize input, verify nonces, and check `current_user_can` before writes.
- WordPress style: tabs, `array()`, Yoda conditions, text domain on translator functions.
- Copyright and `Author` fields name Odell Duppins / Duppins Technology. "Odell Duppins Jr." in existing headers is the same person.
- Use the client name only to say who the work is for.
- Do not add a price, upgrade path, or license gate. Commercial terms stay outside the plugin.
- Do not invent a signed client agreement or present department reviews as legal advice.
- Do not commit `CLAUDE.local.md`, `.claude/settings.local.json`, secrets, or `.env` files.
- Do not commit or push unless Odell asks.

## After development

Saving the edit does not finish the work. Follow `.claude/workflows/departmental-review.md`. Security review runs after development and again after every other department. A process is clear only when `security-reviewer` returns `clear` for that process.

## Where detail lives

- Rules, on demand: `.claude/rules/`
- Slash commands: `.claude/commands/` (`/review`, `/deploy`, `/scaffold`, `/departmental-review`)
- Skills: `.claude/skills/`
- Subagents: `.claude/agents/`
- Hooks: `.claude/hooks/` (enforced; exit 2 blocks)
- Memory: `.claude/memory/`
- Workflows: `.claude/workflows/`
- Personal overrides: `CLAUDE.local.md` (gitignored)

## Safety

Published case study pages show city, state, type, and repair amount. REST stays on so the page and Elementor can read those fields. Password-protected and unpublished posts stay hidden from visitors who cannot read them. Report vulnerabilities privately to odell@duppinstech.com. Do not open a public issue for a vulnerability.
