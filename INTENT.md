# Intent

Case Study Manager exists so RAHyman Restoration, Inc. can publish restoration case studies on its WordPress site: a public archive, categories, and structured fields for city, state, job type, and total repair amount. The single case study page renders the saved values.

Odell Duppins / Duppins Technology owns the plugin code and maintains it. The client uses it on its sites under its agreement with Duppins Technology and under GPL-2.0-or-later. The client does not own the source. Copy, photos, locations, and repair figures that the client enters stay the client's or its customers'.

## Outcome

A site editor can add a case study in wp-admin, file it under a category, and store location, type, and repair amount. A published case study page shows location (city and state), type, and repair amount under the content. `[case_study_details]` does the same when a layout does not print the post content. The block editor, the REST API, and Elementor can read those fields. Existing content that uses the `_ra_` meta keys keeps working. Removing the plugin does not delete that content.

## Users

- Editors at RAHyman Restoration, Inc. who publish case studies.
- Visitors reading a published case study page.
- Odell Duppins, who changes the plugin.

## Constraints

- This plugin stays for RAHyman Restoration, Inc. It is not being generalized.
- Meta keys stay `_ra_location_city`, `_ra_location_state`, `_ra_case_type`, `_ra_repair_amount`, and derived `_ra_location_full`.
- Uninstall does not delete posts, terms, or meta.
- Authorship and copyright name Odell Duppins / Duppins Technology.
- The client name appears only as who the work is for.
- Distribution stays GPL-2.0-or-later unless Odell signs a different written agreement.
- No price, upgrade path, or license gate lives in the plugin. Any fee or access gate is settled outside the plugin, before it is installed.
- Published pages show the saved fields. Password-protected and unpublished case studies stay hidden from visitors who cannot read them.

## Done means

- The behavior above still holds.
- `PLAN.md` has no open item that Odell still wants in the release.
- Each department in `AGENTS.md` has returned `clear` for that release.
- `security-reviewer` has returned `clear` after each of those departments.
- The plugin header, `readme.txt` stable tag, and changelog name the same version.

## Settled

Ownership is in `OWNERSHIP.md`. The 2026-10-14 product decisions are in `.claude/memory/decisions.md`.

## Outside this intent

The plugin does not host the client's case study library, bill their customers, or replace their theme. Repair amounts are job figures the client enters. They are not Duppins Technology's price.
