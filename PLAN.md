# Plan

Version 1.3.0 shows location, type, and repair amount on the published case study page and through `[case_study_details]`, and lets an administrator add more detail rows. Ownership is in `OWNERSHIP.md`. Product decisions from 2026-10-14 are settled.

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
- [x] Local WordPress is Docker (`case-study-manager-local` in `CLAUDE.local.md`), not XAMPP. Claude Code hooks call Git `sh` (`C:\Program Files\Git\bin\sh.exe`). `validate-code.sh` uses host `php` when it is on `PATH` and allows the edit when it is not, so the hooks do not depend on `C:\xampp\php\php.exe`.

## Done in 1.3.0

- [x] Administrators add, rename, reorder, and remove custom detail fields under **Case Studies → Detail fields**. The public page, the shortcode, and the metabox use that list. Built-in `_ra_*` keys stay. With no saved list, the page matches 1.2.0.

### What stays fixed

- Built-in keys stay `_ra_location_city`, `_ra_location_state`, `_ra_case_type`, `_ra_repair_amount`, and derived `_ra_location_full`. They are not renamed, deleted, or replaced by a generated key.
- Location stays two inputs and one public line (`City, State`). Repair amount stays a number shown as currency and stays the sortable list column.
- With no saved field list, the page, shortcode, metabox, and list behave exactly as they do in 1.2.0.
- Custom fields belong to `case_study` only. This does not turn the plugin into a general custom-fields product.
- A blank value still stores an empty string and the public page still omits that row.
- Uninstall still leaves posts, terms, post meta, and the field-list option. Deleting a custom field from the list hides it. It does not delete values already stored on case studies.

### Field list

One ordered list in the option `csm_detail_fields`. Each item is a definition:

| Piece | Rule |
| --- | --- |
| `id` | Stable id. Built-ins: `location`, `type`, `repair_amount`. Custom: slug of the label at creation. |
| `source` | `builtin` or `custom`. |
| `label` | Public and metabox label. Built-in labels can be edited. Keys cannot. |
| `type` | `location` (builtin only), `text`, `number`, or `currency`. |
| `meta_key` | Custom only. Generated once as `_csm_` plus the slug. Must match `_csm_[a-z0-9_]+`. Reject anything that collides with `_ra_*` or an existing key. Immutable after create. |

Default order, used when the option is missing: Location, Type, Total Repair Amount. An administrator (`manage_options`) reorders, renames labels, and adds or removes custom rows under **Case Studies → Detail fields**. A case study editor fills values in the existing metabox and cannot change the list.

Add form: label and type (`text`, `number`, or `currency`). The screen shows the key it will store and states that a published case study page and the REST API will show the value, same as city, type, and repair amount. There is no private-field switch in this design.

### How the page uses the list

`register_post_meta` keeps registering the five built-in keys as it does now. It also registers each custom key: string, single, `show_in_rest`, the existing `edit_post` auth callback, `sanitize_text_field` for text, and the repair-amount float-or-empty sanitizer for `number` and `currency`.

The metabox walks the list. Location still renders city and state. Text is a text input. Number and currency are number inputs. Save still checks the current nonce, post type, and `edit_post`, then writes only keys present in the list.

The public page and `[case_study_details]` walk the same list. Location uses the existing city/state line. Currency uses the existing `$` formatting. Text and number print the sanitized value. Empty rows are skipped. Markup stays a definition list. List columns stay the three built-in columns; custom fields do not add columns in this pass.

### Proof

- [x] Missing option: `tests/detail-rows-test.php` still expects location, type, and repair amount, and `_ra_location_full` is still derived on save.
- [x] A custom text field and a custom currency field are included in the public rows. A blank custom value is omitted. Covered by `tests/detail-rows-test.php`.
- [x] The field list saves only with `manage_options` and a valid nonce. Case study values still save only with the metabox nonce, the case study post type, and `edit_post`. A subscriber does not get the Detail fields screen.
- [x] A custom key cannot overwrite an `_ra_*` key. A posted key cannot replace a stored `_csm_*` key. Covered by `tests/detail-rows-test.php`.
- [x] Each custom key is registered with `show_in_rest` and `meta_auth_can_edit_post`, the same auth callback as the built-in keys.

## Not in this release

- A block-editor sidebar in place of the classic metabox. The metabox still works, and the page render covers the visitor view.
- Renaming `_ra_*` keys, a general-purpose product, or any payment or upgrade code.
- Uploading the plugin to a site or to WordPress.org. That waits for Odell.

## Review order

Follow `.claude/workflows/departmental-review.md`. Security runs after each department.
