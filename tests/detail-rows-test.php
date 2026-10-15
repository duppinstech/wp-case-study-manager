<?php
/**
 * Row and markup checks that do not need WordPress.
 *
 * Run: php tests/detail-rows-test.php
 *
 * @package DuppinsTech\CaseStudies
 */

define( 'ABSPATH', __DIR__ );

require dirname( __DIR__ ) . '/includes/class-case-study-manager-plugin.php';

use DuppinsTech\CaseStudies\Plugin;

$failed = 0;

/**
 * @param bool   $condition Condition.
 * @param string $message Message.
 */
function csm_assert( $condition, $message ) {
	global $failed;
	if ( $condition ) {
		echo "ok  $message\n";
		return;
	}
	$failed++;
	echo "fail $message\n";
}

csm_assert( 'Charlotte, NC' === Plugin::location_line( ' Charlotte ', ' NC ' ), 'city and state join with a comma' );
csm_assert( 'Charlotte' === Plugin::location_line( 'Charlotte', '' ), 'city alone has no comma' );
csm_assert( 'NC' === Plugin::location_line( '', 'NC' ), 'state alone has no comma' );
csm_assert( '' === Plugin::location_line( '  ', '' ), 'blank location is empty' );

$rows = Plugin::public_detail_rows( 'Charlotte', 'NC', ' Roofing ', '15000' );
csm_assert( 'Charlotte, NC' === $rows['location'], 'public row keeps location' );
csm_assert( 'Roofing' === $rows['type'], 'public row trims type' );
csm_assert( '$15,000.00' === $rows['repair_amount'], 'repair amount formats as dollars' );

$empty = Plugin::public_detail_rows( '', '', '   ', '' );
csm_assert( array() === $empty, 'empty fields produce no rows' );

$unsafe = Plugin::public_detail_rows( '<script>', '', '', 'not-a-number' );
csm_assert( '<script>' === $unsafe['location'], 'row values stay raw until markup escapes them' );
csm_assert( ! isset( $unsafe['repair_amount'] ), 'non-numeric repair amount is omitted' );

$markup = Plugin::details_markup( $unsafe );
csm_assert( false === strpos( $markup, '<script>' ), 'markup escapes location text' );
csm_assert( false !== strpos( $markup, '&lt;script&gt;' ), 'markup contains the escaped location' );
csm_assert( '' === Plugin::details_markup( array() ), 'no rows means no markup' );

exit( $failed > 0 ? 1 : 0 );
