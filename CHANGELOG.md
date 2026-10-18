# Changelog

All notable changes to this project are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project uses the version in the plugin header.

## [Unreleased]

## [1.2.0]

### Added

- Location, type, and repair amount on the single case study page.
- `[case_study_details]` shortcode.
- Those details stay hidden on password-protected and unpublished case studies from visitors who cannot read them.
- GPL copyright notice and a translation template.

### Changed

- Registered case study fields are included on the REST response.
- Pricing and upgrade gates stay outside the plugin.

## [1.1.0]

### Added

- `uninstall.php`. Uninstall leaves posts, terms, and meta in place.
- `readme.txt`.
- Admin stylesheet for the details metabox.

### Changed

- Post meta is registered for REST (`register_post_meta`).
- The version comes from the plugin header.

## [1.0.0]

### Added

- Initial release: custom post type, taxonomy, metabox, admin columns, and Elementor support.
