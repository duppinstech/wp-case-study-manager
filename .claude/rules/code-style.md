---
paths:
  - "**/*.php"
  - "**/*.css"
---

# Code style

WordPress PHP for this plugin. Match `includes/class-case-study-manager-plugin.php`.

- Tabs for indentation. `array()`, not short array syntax, in PHP that already uses `array()`.
- Yoda conditions: `'title' === $key`, `null === self::$instance`.
- Namespace `DuppinsTech\CaseStudies` for plugin classes. Functions in the bootstrap file stay global and prefixed `case_study_manager_`.
- Text domain `case-study-manager` on every translator call.
- Escape on output: `esc_html`, `esc_attr`, `esc_html_e`, `esc_attr_e`. Sanitize on input.
- Direct access guard: `defined( 'ABSPATH' ) || exit;`
- CSS classes prefixed `csm-`. Form ids prefixed `duppins_`. Meta keys stay `_ra_*` for content compatibility.
- Copyright lines name Odell Duppins / Duppins Technology. Do not put the client in a copyright line.
- No new dependency unless `PLAN.md` records it and the license stays GPL-compatible.

Admin CSS stays in `assets/css/admin-metabox.css` and loads only on case study edit screens.
