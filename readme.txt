=== Case Study Manager ===
Contributors: odellduppins
Tags: case-study, custom-post-type, elementor
Requires at least: 5.8
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.2.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Custom post type, taxonomy, and meta fields for case studies (locations, type, repair amount).

== Description ==

Case Study Manager registers a public **Case Study** post type with categories, a details metabox (city, state, type, total repair amount), admin list columns, and sortable repair amount. A published case study page shows location, type, and repair amount under the content. The shortcode `[case_study_details]` is available when a layout does not print the post content. The post type supports the block editor REST API and Elementor.

The plugin has no price, upgrade path, or license check. Commercial terms are handled outside the plugin, before it is installed. It is built for RAHyman Restoration, Inc. Odell Duppins / Duppins Technology owns the code.

Meta keys remain compatible with earlier snippet-based setups: `_ra_location_city`, `_ra_location_state`, `_ra_case_type`, `_ra_repair_amount`, and derived `_ra_location_full`.

== Installation ==

1. Upload the `case-study-manager` folder to `/wp-content/plugins/`, or upload the plugin ZIP via **Plugins &rarr; Add New &rarr; Upload Plugin**.
2. Activate **Case Study Manager** through the **Plugins** menu in WordPress.
3. Visit **Settings &rarr; Permalinks** and click **Save Changes** once if archives or single URLs return 404 (activation normally flushes rewrite rules).

== Frequently Asked Questions ==

= What happens if I uninstall the plugin? =

Uninstalling does **not** delete your case study posts, categories, or post meta. Your content stays in the database unless you remove it manually or use another tool. See `uninstall.php` in the plugin directory for details.

= Where do the details show on the site? =

On a single published case study, under the content: location (city and state), type, and total repair amount, when those values are filled in. Use `[case_study_details]` if the template does not print the post content. Password-protected and unpublished case studies are not shown to visitors who cannot read them.

= Does this plugin include a price or an upgrade? =

No. Any fee or access gate is settled outside the plugin, before installation.

= Where is case study data stored? =

In WordPress posts of type `case_study`, taxonomy `case_study_category`, and the post meta keys listed in the plugin description.

== Changelog ==

= 1.2.0 =
* Show location, type, and repair amount on the single case study page.
* Expose the registered case study fields on the REST response by supporting custom fields.
* Add the `[case_study_details]` shortcode.
* Hide those details on password-protected and unpublished case studies from visitors who cannot read them.
* State that pricing and upgrade gates stay outside the plugin.
* Add the GPL copyright notice and a translation template.

= 1.1.0 =
* Register post meta for REST API consistency (`register_post_meta`).
* Document uninstall behavior; add `uninstall.php`.
* Single version source from the plugin header; enqueue admin stylesheet for the details metabox.
* Add `readme.txt`.

= 1.0.0 =
* Initial release: custom post type, taxonomy, metabox, admin columns, Elementor support.
