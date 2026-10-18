---
name: testing-patterns
description: Describes how to test Case Study Manager behavior when a change touches save, REST meta, location fields, or uninstall. Use when writing or reviewing tests.
---

# Testing patterns

This plugin stores editor input as post meta and derives one display string. Tests should target that behavior. The suite does not exist yet; see `.claude/rules/testing-standard.md`.

## Patterns

- **Denied save.** No nonce, autosave, revision, or a user without `edit_post`. Meta is unchanged.
- **Accepted save.** City `Charlotte`, state `NC`, type `Roofing`, amount `15000` stores the four keys and `_ra_location_full` as `Charlotte, NC`.
- **Partial location.** City only, or state only, does not leave a stray comma.
- **Amount.** Blank stores `''`. `15000.5` stores a float. A value with markup does not get stored as markup.
- **REST.** `show_in_rest` read on a published post is a product decision. A REST write still requires `edit_post`.
- **Uninstall.** Including `uninstall.php` under `WP_UNINSTALL_PLUGIN` adds no `delete_post_meta` or post deletion.

Propose tests in the reply when the task asked for tests. During a review, name the missing coverage and stop there.

Do not point tests at a live RAHyman site or at real customer addresses.
