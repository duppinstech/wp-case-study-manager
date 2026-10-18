---
paths:
  - "**/*.php"
---

# API conventions

The public surface is WordPress: one post type, one taxonomy, five meta keys, and the hooks already in `Plugin::hooks()`.

## Identifiers

- Post type: `case_study`. Archive slug: `case-studies`.
- Taxonomy: `case_study_category`. Rewrite slug: `case-study-category`.
- Meta: `_ra_location_city`, `_ra_location_state`, `_ra_case_type`, `_ra_repair_amount`, `_ra_location_full`.
- Nonce action: `ra_case_study_details_save`. Nonce field: `ra_case_study_details_nonce`.
- POST fields: `ra_location_city`, `ra_location_state`, `ra_case_type`, `ra_repair_amount`.

`_ra_location_full` is derived on save as `City, State`. Callers do not post it.

## REST

`register_post_meta` for the case study post type, `show_in_rest` true, `auth_callback` requires `edit_post`. String fields use `sanitize_text_field`. Repair amount uses `sanitize_meta_repair_amount` (empty string, or float).

Changing `show_in_rest`, `public`, or `auth_callback` is a privacy and security change. Record it against the open decision in `PLAN.md`.

## Saves

`save_post_case_study` bails on autosave, revision, bad nonce, wrong post type, or missing `edit_post`. Then it sanitizes and updates meta.

## Capabilities

`capability_type` is `post` with `map_meta_cap`. Do not invent a custom capability unless the task asks for one.

## What not to add

No custom REST routes or custom tables unless `INTENT.md` changes. No remote requests. No deletion of client content on uninstall.
