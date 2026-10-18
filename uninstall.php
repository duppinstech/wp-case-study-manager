<?php
/**
 * Uninstall handler.
 *
 * Copyright (C) 2026 Odell Duppins / Duppins Technology
 * License: GPL-2.0-or-later
 *
 * Case Study Manager does not delete case study posts, taxonomy terms, or post meta when the plugin
 * is removed. Content remains in the database. The GitHub updater cache option is removed.
 *
 * @package DuppinsTech\CaseStudies
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

delete_site_option( 'csm_github_release' );

// Intentionally no deletion of posts, terms, or post meta.
