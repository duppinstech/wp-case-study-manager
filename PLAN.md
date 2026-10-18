# Plan

Version 1.2.0 shows location, type, and repair amount on the published case study page and through `[case_study_details]`. Ownership is in `OWNERSHIP.md`. Product decisions from 2026-10-14 are settled.

## Decisions

- [x] **Page data.** "Public" here means the case study page renders the saved fields. City, state, and type show on that page. The repair amount shows too, because it is part of the saved case study. It is not a plugin price. REST stays available so the page and Elementor can read the fields. Password-protected and unpublished case studies stay hidden from visitors who cannot read them.
- [x] **Client.** The plugin stays for RAHyman Restoration, Inc. Meta keys stay `_ra_*`.
- [x] **Price.** No price, upgrade path, or license gate in the plugin. Any fee or access gate is settled outside the plugin, before installation.

## Done in 1.2.0

- [x] Single case study page renders the saved details.
- [x] Shortcode `[case_study_details]`.
- [x] GPL copyright notice on the PHP entry files, naming Odell Duppins / Duppins Technology.
- [x] Department review recorded in `.claude/memory/progress.md`. Security reviewed the page output, the docs, and the copyright notice.
- [x] README, `readme.txt`, and the plugin header agree on ownership, the client, GPL, and the absence of an in-plugin price.
- [x] `languages/case-study-manager.pot`.
- [x] Row checks in `tests/detail-rows-test.php`, and CI for syntax, those checks, and `phpcs`.
- [x] WordPress coding standards in `phpcs.xml.dist`. `composer lint` runs them. The post-edit hook runs `phpcbf` when `vendor/bin/phpcbf` is installed. `CSM_*` and `_ra_*` names stay as they are; the global-prefix sniff is excluded because those names are already shipped.
- [x] Plugin header, `CSM_VERSION` fallback, `readme.txt` stable tag, and changelog are 1.2.0.
- [x] Pull request template.
- [x] Blank metabox fields save as empty strings, so the page omits that row. That is intentional.
- [x] Admin help text `#666` on white is about 5.7:1. No change.
- [x] Zip contents are listed in `.claude/agents/release-reviewer.md`. A zip is built when Odell asks to package a release. It is not stored in git.
- [x] WordPress 7.1.3 in Docker: metabox save, nonce, sanitizing, subscriber denial, draft and password hiding, shortcode, REST read, REST write permissions, deactivate, and uninstall leaving content in place.

## Still open

- [ ] Confirm Claude Code hooks on this PC. `php` resolves to `C:\xampp\php\php.exe`. `sh` is `C:\Program Files\Git\bin\sh.exe` and is not on the PowerShell `PATH`.

## Not in this release

- A block-editor sidebar in place of the classic metabox. The metabox still works, and the page render covers the visitor view.
- Renaming `_ra_*` keys, a general-purpose product, or any payment or upgrade code.
- Uploading the plugin to a site or to WordPress.org. That waits for Odell.

## Review order

Follow `.claude/workflows/departmental-review.md`. Security runs after each department.
