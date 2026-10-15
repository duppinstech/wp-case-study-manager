# Project context

Case Study Manager is a WordPress plugin in this repository root (not nested in a second `case-study-manager/` directory).

## Owner and client

- Owner and author: Odell Duppins / Duppins Technology (https://duppinstech.com).
- Headers also say Odell Duppins Jr. Same person.
- Client: RAHyman Restoration, Inc. Uses the plugin on its sites. Does not own the source.
- Source of truth: `OWNERSHIP.md`.

## Runtime

- PHP 7.4+, WordPress 5.8+. `readme.txt` says tested up to 6.9.
- Bootstrap `case-study-manager.php` defines `CSM_VERSION`, `CSM_PLUGIN_FILE`, `CSM_PLUGIN_DIR`, `CSM_PLUGIN_URL`, then loads the class.
- `DuppinsTech\CaseStudies\Plugin` is a singleton. `case_study_manager_run()` returns it. Activation and deactivation flush rewrite rules.
- Version constant is read from the plugin header, with fallback `1.2.0`.
- A published single case study appends location, type, and repair amount to the content. `[case_study_details]` renders the same markup. Password-protected and unpublished posts are omitted for visitors who cannot read them.

## Data model

- Post type `case_study`, archive slug `case-studies`, REST enabled, Elementor support.
- Taxonomy `case_study_category`, hierarchical.
- Meta `_ra_location_city`, `_ra_location_state`, `_ra_case_type`, `_ra_repair_amount`, derived `_ra_location_full`.
- The `_ra_` prefix matches older snippet data. It is not an ownership mark.
- Admin UI: metabox "Case Study Details", list columns, repair amount sortable as a number.
- Styles: `assets/css/admin-metabox.css`, enqueued only on case study `post.php` and `post-new.php`.

## Distribution

- GPL-2.0-or-later. Full text in `LICENSE`.
- `uninstall.php` intentionally does not delete posts, terms, or meta.
- No Composer, Node, CI, or PHPUnit suite.
- Claude Code project files live in `CLAUDE.md` and `.claude/`. Personal overrides are gitignored.

## Security contact

odell@duppinstech.com. See `SECURITY.md`.
