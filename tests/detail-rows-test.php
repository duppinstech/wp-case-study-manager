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

use DuppinsTech\CaseStudies\DetailFields;
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

$defaults = DetailFields::defaults();
csm_assert( array( 'location', 'type', 'repair_amount' ) === array_column( $defaults, 'id' ), 'default fields stay location, type, repair amount' );

$from_defaults = DetailFields::ordered_public_rows(
	$defaults,
	array(
		'city'          => 'Charlotte',
		'state'         => 'NC',
		'type'          => ' Roofing ',
		'repair_amount' => '15000',
	)
);
csm_assert( 'Location' === $from_defaults[0]['label'] && 'Charlotte, NC' === $from_defaults[0]['value'], 'missing option still prints location' );
csm_assert( 'Type' === $from_defaults[1]['label'] && 'Roofing' === $from_defaults[1]['value'], 'missing option still prints type' );
csm_assert( 'Total Repair Amount' === $from_defaults[2]['label'] && '$15,000.00' === $from_defaults[2]['value'], 'missing option still prints repair amount' );

$attack = DetailFields::normalize(
	array(
		array(
			'id'       => 'hack',
			'source'   => 'custom',
			'label'    => 'Hack',
			'type'     => 'text',
			'meta_key' => '_ra_location_city',
		),
		array(
			'id'       => 'location',
			'source'   => 'custom',
			'label'    => 'Replaced',
			'type'     => 'text',
			'meta_key' => '_csm_location',
		),
	)
);
$attack_keys = array();
foreach ( $attack as $field ) {
	if ( isset( $field['meta_key'] ) ) {
		$attack_keys[] = $field['meta_key'];
	}
}
csm_assert( array() === $attack_keys, 'a custom key cannot overwrite an _ra_ key' );
csm_assert( 'location' === $attack[0]['id'] && 'builtin' === $attack[0]['source'], 'location stays a built-in field' );

$custom = array(
	array(
		'id'       => 'insurance_carrier',
		'source'   => 'custom',
		'label'    => 'Insurance carrier',
		'type'     => 'text',
		'meta_key' => '_csm_insurance_carrier',
	),
	array(
		'id'       => 'deductible',
		'source'   => 'custom',
		'label'    => 'Deductible',
		'type'     => 'currency',
		'meta_key' => '_csm_deductible',
	),
	array(
		'id'       => 'rooms',
		'source'   => 'custom',
		'label'    => 'Rooms',
		'type'     => 'number',
		'meta_key' => '_csm_rooms',
	),
);
$custom_rows = DetailFields::ordered_public_rows(
	array_merge( $defaults, $custom ),
	array(
		'city'                    => '',
		'state'                   => '',
		'type'                    => '',
		'repair_amount'           => '',
		'_csm_insurance_carrier'  => ' Acme ',
		'_csm_deductible'         => '500',
		'_csm_rooms'              => '',
	)
);
csm_assert( 2 === count( $custom_rows ), 'a blank custom value is omitted' );
csm_assert( 'Insurance carrier' === $custom_rows[0]['label'] && 'Acme' === $custom_rows[0]['value'], 'custom text shows on the page' );
csm_assert( 'Deductible' === $custom_rows[1]['label'] && '$500.00' === $custom_rows[1]['value'], 'custom currency formats as dollars' );

$custom_markup = Plugin::details_markup_rows(
	array(
		array(
			'label' => '<b>Carrier</b>',
			'value' => '<script>',
		),
	)
);
csm_assert( false === strpos( $custom_markup, '<script>' ), 'custom markup escapes the value' );
csm_assert( false === strpos( $custom_markup, '<b>' ), 'custom markup escapes the label' );

$added = DetailFields::apply_form( $defaults, array(), 'Insurance carrier', 'text' );
csm_assert( '' === $added['error'], 'a new text field is accepted' );
csm_assert( '_csm_insurance_carrier' === $added['fields'][3]['meta_key'], 'the new key is _csm_ plus the slug' );

$reserved = DetailFields::apply_form( $defaults, array(), 'Location', 'text' );
csm_assert( 'reserved' === $reserved['error'], 'a label that reuses a built-in id is rejected' );
csm_assert( 3 === count( $reserved['fields'] ), 'a rejected label does not add a row' );

$posted_key = array(
	'insurance_carrier' => array(
		'label'    => 'Insurer',
		'position' => 40,
		'meta_key' => '_ra_repair_amount',
	),
);
$relabel = DetailFields::apply_form( $added['fields'], $posted_key, '', '' );
$relabeled = null;
foreach ( $relabel['fields'] as $field ) {
	if ( 'insurance_carrier' === $field['id'] ) {
		$relabeled = $field;
	}
}
csm_assert( is_array( $relabeled ) && '_csm_insurance_carrier' === $relabeled['meta_key'] && 'Insurer' === $relabeled['label'], 'a posted key cannot replace the stored key' );

$removed = DetailFields::apply_form(
	$added['fields'],
	array(
		'insurance_carrier' => array(
			'remove' => '1',
		),
	),
	'',
	''
);
csm_assert( array( 'location', 'type', 'repair_amount' ) === array_column( $removed['fields'], 'id' ), 'removing a custom field leaves the built-ins' );

$reordered = DetailFields::apply_form(
	$defaults,
	array(
		'location'      => array(
			'position' => 30,
		),
		'type'          => array(
			'position' => 20,
		),
		'repair_amount' => array(
			'position' => 10,
		),
	),
	'',
	''
);
csm_assert( array( 'repair_amount', 'type', 'location' ) === array_column( $reordered['fields'], 'id' ), 'order follows the posted positions' );

exit( $failed > 0 ? 1 : 0 );
