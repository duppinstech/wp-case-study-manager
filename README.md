# Case Study Manager

A custom [WordPress](https://wordpress.org/) plugin that registers a **Case Study** custom post type, hierarchical categories, and structured meta fields (location, project type, and total repair amount). It is built for **RAHyman Restoration, Inc.** and works with the block editor REST API and **Elementor**.

**Author:** [Odell Duppins Jr.](https://duppinstech.com) · **Company:** Duppins Technology · **Website:** [duppinstech.com](https://duppinstech.com)

The plugin code is personally owned by Odell Duppins / Duppins Technology. RAHyman Restoration, Inc. is the client this work is for and does not own the source. See [OWNERSHIP.md](OWNERSHIP.md).

## Features

- Public `case_study` post type with archive (`/case-studies/`) and REST support  
- Hierarchical taxonomy `case_study_category`  
- Admin metabox: city, state, type, total repair amount; derived `_ra_location_full` for builders  
- Admin list columns (location, type, repair amount) and sortable repair amount  
- Registered post meta for REST (`register_post_meta`) with auth and sanitize callbacks  
- Elementor editor support for the post type  
- Single case study pages show location, type, and repair amount under the content  
- Shortcode `[case_study_details]` for layouts that do not print the post content  

## On the case study page

A published case study prints **location** (city and state), **type**, and **total repair amount** under the post content when those fields have a value. City, state, and type are the descriptive fields. The repair amount is included because it is part of the saved case study, not because it is a price for this plugin.

Layouts that do not output the post content, including some Elementor templates, can place:

```text
[case_study_details]
```

`[case_study_details id="123"]` renders another published case study. Password-protected and unpublished case studies stay hidden from visitors who cannot read them.

## Commercial terms

This plugin has no price, upgrade path, or license check. Any fee or access gate is handled outside the plugin, before it is installed.

## Requirements

- WordPress **5.8** or newer  
- PHP **7.4** or newer  

## Installation

1. Download or clone this repository.  
2. Copy the **`case-study-manager`** folder into your site’s `wp-content/plugins/` directory (or zip that folder and use **Plugins → Add New → Upload Plugin**).  
3. Activate **Case Study Manager** under **Plugins**.  
4. If single posts or archives return **404**, open **Settings → Permalinks** and click **Save Changes** once.  

WordPress-packaged details (stable tag, FAQ, uninstall notes) live in [`readme.txt`](readme.txt).

## Repository layout

| Path | Purpose |
|------|--------|
| [`case-study-manager.php`](case-study-manager.php) | Plugin bootstrap and hooks |
| [`includes/class-case-study-manager-plugin.php`](includes/class-case-study-manager-plugin.php) | Main plugin class |
| [`assets/css/admin-metabox.css`](assets/css/admin-metabox.css) | Admin metabox styles |
| [`assets/css/case-study-details.css`](assets/css/case-study-details.css) | Front-end case study details |
| [`languages/case-study-manager.pot`](languages/case-study-manager.pot) | Translation template |
| [`uninstall.php`](uninstall.php) | Uninstall behavior (no automatic deletion of posts or meta) |
| [`OWNERSHIP.md`](OWNERSHIP.md) | Copyright owner and client for this repo |
| [`CLAUDE.md`](CLAUDE.md) | Claude Code project instructions |
| [`INTENT.md`](INTENT.md) | Why this plugin exists |
| [`PLAN.md`](PLAN.md) | Work still required |

## Meta keys

Stored post meta (unchanged from earlier snippet-based setups):

- `_ra_location_city`, `_ra_location_state`, `_ra_case_type`, `_ra_repair_amount`  
- `_ra_location_full` (combined city/state for display and integrations)  

## License

This project is licensed under the **GNU General Public License v2.0 or later** (GPL-2.0-or-later). See [`LICENSE`](LICENSE) for the full license text.

Copyright notice for the plugin code: **Copyright © Odell Duppins Jr. / Duppins Technology.** Distribution and modification are governed by the GPL as published by the Free Software Foundation.

## Disclaimer

This software is provided “as is,” without warranty of any kind. Use at your own risk. RAHyman Restoration, Inc. is named as the intended client for the plugin description; it is not responsible for third-party hosting or customization unless separately agreed.
