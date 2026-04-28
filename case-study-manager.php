<?php
/**
 * Plugin Name:       Case Study Manager
 * Plugin URI:        https://duppinstech.com
 * Description:       A custom case study manager plugin for RAHyman Restoration, Inc.
 * Version:           1.1.0
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Author:            Odell Duppins Jr.
 * Author URI:        https://duppinstech.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       case-study-manager
 * Domain Path:       /languages
 *
 * @package DuppinsTech\CaseStudies
 */

defined( 'ABSPATH' ) || exit;

$case_study_manager_headers = get_file_data(
	__FILE__,
	array(
		'Version' => 'Version',
	),
	'plugin'
);

define( 'CSM_VERSION', ! empty( $case_study_manager_headers['Version'] ) ? $case_study_manager_headers['Version'] : '1.1.0' );
define( 'CSM_PLUGIN_FILE', __FILE__ );
define( 'CSM_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'CSM_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

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
