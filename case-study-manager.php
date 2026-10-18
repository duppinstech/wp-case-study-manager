<?php
/**
 * Plugin Name:       Case Study Manager
 * Plugin URI:        https://github.com/duppinstech/wp-case-study-manager
 * Description:       A custom case study manager plugin for RAHyman Restoration, Inc.
 * Version:           1.2.0
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Author:            Duppins Technology
 * Author URI:        https://duppinstech.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       case-study-manager
 * Domain Path:       /languages
 * Update URI:        https://github.com/duppinstech/wp-case-study-manager
 *
 * @package DuppinsTech\CaseStudies
 */

/**
 * Copyright (C) 2026 Odell Duppins / Duppins Technology
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see https://www.gnu.org/licenses/.
 */

defined( 'ABSPATH' ) || exit;

$case_study_manager_headers = get_file_data(
	__FILE__,
	array(
		'Version' => 'Version',
	),
	'plugin'
);

define( 'CSM_VERSION', ! empty( $case_study_manager_headers['Version'] ) ? $case_study_manager_headers['Version'] : '1.2.0' );
define( 'CSM_PLUGIN_FILE', __FILE__ );
define( 'CSM_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'CSM_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once CSM_PLUGIN_DIR . 'includes/class-csm-github-updater.php';

// Updates from GitHub Releases. Booted at load so WP-Cron and background auto-updates see releases.
// Disable with define( 'CSM_DISABLE_GITHUB_UPDATER', true ).
CSM_GitHub_Updater::boot(
	array(
		'file'    => CSM_PLUGIN_FILE,
		'version' => CSM_VERSION,
		'owner'   => 'duppinstech',
		'repo'    => 'wp-case-study-manager',
		'prefix'  => 'csm',
		'asset'   => 'case-study-manager.zip',
	)
);

require_once CSM_PLUGIN_DIR . 'includes/class-case-study-manager-plugin-info.php';

// Plugins screen links and the View details modal. No settings screen and no public guide yet.
CSM_Plugin_Info::boot(
	array(
		'file'         => CSM_PLUGIN_FILE,
		'settings_url' => '',
		'help_url'     => '',
		'support_url'  => 'mailto:support@duppinstech.com',
	)
);

require_once CSM_PLUGIN_DIR . 'includes/class-case-study-manager-plugin.php';

/**
 * Bootstrap the plugin.
 */
function case_study_manager_run() {
	return DuppinsTech\CaseStudies\Plugin::instance();
}

case_study_manager_run();

register_activation_hook(
	CSM_PLUGIN_FILE,
	static function () {
		case_study_manager_run()->activate();
	}
);

register_deactivation_hook(
	CSM_PLUGIN_FILE,
	static function () {
		case_study_manager_run()->deactivate();
	}
);
