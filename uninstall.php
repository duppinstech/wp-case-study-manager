<?php
/**
 * Uninstall handler.
 *
 * Copyright (C) 2026 Odell Duppins / Duppins Technology
 * License: GPL-2.0-or-later
 *
 * Case Study Manager does not delete case study posts, taxonomy terms, or post meta when the plugin
 * is removed. Content remains in the database. Add optional cleanup here only if you introduce
 * plugin-specific options or transient data that must be removed on uninstall.
 *
 * @package DuppinsTech\CaseStudies
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

// Intentionally no deletion of posts, terms, or post meta.
