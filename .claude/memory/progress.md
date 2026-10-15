# Progress

## Released

- 1.0.0 — post type, taxonomy, metabox, admin columns, Elementor support.
- 1.1.0 — REST `register_post_meta`, `uninstall.php`, version read from the plugin header, admin stylesheet, `readme.txt`.
- 1.2.0 — published case study page and `[case_study_details]` render location, type, and repair amount. GPL copyright notice. Translation template. No in-plugin price or upgrade.

## 2026-10-14 — Decisions

Odell settled the open product questions. The page renders the saved fields (city, state, type, and the stored repair amount). The plugin stays for RAHyman Restoration, Inc. Pricing and access gates stay outside the plugin, before installation.

## 2026-10-14 — Department review of 1.2.0

Security ran after each department. Upload to a site was not requested, so release did not package or ship.

| Department | Verdict | Security after | Notes |
|------------|---------|----------------|-------|
| Quality | clear | clear | `php tests/detail-rows-test.php` passed (13 checks). WordPress nonce, REST, and uninstall tests remain open. |
| Product | clear | clear | Page output matches the settled intent. Still for RAHyman. `_ra_*` keys unchanged. |
| Privacy | clear | clear | Published pages show the fields. Password-protected and unpublished posts are omitted unless the visitor can read the post. |
| Legal | clear | clear | Copyright notices name Odell Duppins / Duppins Technology. GPL unchanged. Client named only as who the work is for. |
| Finance | clear | clear | No price, upgrade, or license check added. Repair amount documented as a job figure. |
| Support | clear | clear | README and `readme.txt` describe the page, the shortcode, uninstall, and permalinks. |
| Marketing | clear | clear | Claims match the page output. No paid-tier claim. |
| Accessibility | clear | clear | Details use a definition list with text labels. Admin help text `#666` on white is about 5.7:1. |
| Release | clear | clear | Header, fallback, stable tag, and changelog are 1.2.0. No upload. |

## 2026-10-15 — WordPress in Docker

WordPress 7.1.3. The published page and `[case_study_details]` showed location, type, and `$15,000.00`. A missing metabox nonce, a subscriber, a draft, and a password-protected post did not expose or change those fields. REST reads the registered meta. A subscriber REST write returned 403 and an admin write returned 200. Deactivate and the uninstall script left the case studies in place. REST needed `custom-fields` support on the post type, which is now registered.

## Next

Packaging or installing 1.2.0 waits until Odell asks. WordPress integration tests are covered by the Docker pass above; they are not a checked-in PHPUnit suite.
