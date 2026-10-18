# Decisions

Settled decisions. Open items stay in `PLAN.md` until Odell answers them.

## 2026-10-14 — Ownership

Odell Duppins personally owns the code, tooling, and documentation in this repo. Duppins Technology is his business. RAHyman Restoration, Inc. is the client the work is for and does not own the source. Client-supplied case study content remains the client's or its customers'. Recorded in `OWNERSHIP.md`. No signed agreement transferring ownership is in the repo.

## License

Plugin code is GPL-2.0-or-later. The license grants copy, modification, and sharing rights. It does not transfer copyright.

## Content compatibility

Meta keys remain `_ra_location_city`, `_ra_location_state`, `_ra_case_type`, `_ra_repair_amount`, and `_ra_location_full` so existing client content still matches.

## Uninstall

Removing the plugin does not delete case study posts, taxonomy terms, or post meta.

## 2026-10-14 — Page data, client, and price

Odell settled three product questions:

- The case study page renders the saved data. City, state, and type show on that page. Repair amount shows with them because it is stored case study data, not a price for the plugin. "Public" means visitors can see those fields on a published case study. It does not mean a separate private mode. REST stays on so the page and Elementor can read the fields. Password-protected and unpublished case studies stay hidden from visitors who cannot read them.
- The plugin stays for RAHyman Restoration, Inc. Do not generalize it or rename `_ra_*` keys.
- There is no price, upgrade path, or license gate in the plugin. Any fee or access gate is settled outside the plugin, before installation. Do not add one.

## Blank fields

Saving a metabox field as blank stores an empty string. The public page then omits that row. That is intentional.

## Price

Job repair amounts are client content, not Duppins Technology's fee.

## Reviews

After engineering, department agents review in the order in `AGENTS.md`. `security-reviewer` runs after each of them. A new license, a new price, or an upload to a live site still waits for Odell. Page visibility, the RAHyman-only scope, and the absence of an in-plugin price are already decided.

## Architecture

One singleton class. No custom tables. No Composer framework. Version is read from the plugin header. Current release is 1.2.0. The single case study page and `[case_study_details]` render location, type, and repair amount.
