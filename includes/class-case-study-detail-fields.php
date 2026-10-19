<?php
/**
 * Ordered case study detail fields.
 *
 * Copyright (C) 2026 Odell Duppins / Duppins Technology
 * License: GPL-2.0-or-later
 *
 * Built-in location, type, and repair amount stay on their `_ra_*` keys.
 * Custom rows use a generated `_csm_*` key.
 *
 * @package DuppinsTech\CaseStudies
 */

namespace DuppinsTech\CaseStudies;

defined( 'ABSPATH' ) || exit;

/**
 * Field-list rules that do not need WordPress.
 */
final class DetailFields {

	public const OPTION       = 'csm_detail_fields';
	public const PAGE_SLUG    = 'csm-detail-fields';
	public const NONCE_ACTION = 'csm_detail_fields_save';
	public const NONCE_NAME   = 'csm_detail_fields_nonce';

	/**
	 * Location, type, and repair amount, in the 1.2.0 order.
	 *
	 * @return array<int, array<string, string>>
	 */
	public static function defaults(): array {
		return array(
			array(
				'id'     => 'location',
				'source' => 'builtin',
				'label'  => 'Location',
				'type'   => 'location',
			),
			array(
				'id'     => 'type',
				'source' => 'builtin',
				'label'  => 'Type',
				'type'   => 'text',
			),
			array(
				'id'     => 'repair_amount',
				'source' => 'builtin',
				'label'  => 'Total Repair Amount',
				'type'   => 'currency',
			),
		);
	}

	/**
	 * @param mixed $key Meta key.
	 * @return bool
	 */
	public static function is_reserved_meta_key( $key ): bool {
		return in_array(
			(string) $key,
			array(
				'_ra_location_city',
				'_ra_location_state',
				'_ra_case_type',
				'_ra_repair_amount',
				'_ra_location_full',
			),
			true
		);
	}

	/**
	 * @param mixed $key Meta key.
	 * @return bool
	 */
	public static function is_custom_meta_key( $key ): bool {
		if ( ! is_string( $key ) || self::is_reserved_meta_key( $key ) ) {
			return false;
		}
		return 1 === preg_match( '/^_csm_[a-z0-9_]+$/', $key );
	}

	/**
	 * Key created once from a label. Empty when the label has no usable slug.
	 *
	 * @param mixed $label Field label.
	 * @return string
	 */
	public static function meta_key_from_label( $label ): string {
		$slug = strtolower( self::plain_text( $label ) );
		if ( function_exists( 'remove_accents' ) ) {
			$slug = remove_accents( $slug );
		}
		$slug = preg_replace( '/[^a-z0-9]+/', '_', $slug );
		if ( ! is_string( $slug ) ) {
			return '';
		}
		$slug = trim( $slug, '_' );
		if ( strlen( $slug ) > 40 ) {
			$slug = trim( substr( $slug, 0, 40 ), '_' );
		}
		if ( '' === $slug ) {
			return '';
		}
		$key = '_csm_' . $slug;
		if ( ! self::is_custom_meta_key( $key ) ) {
			return '';
		}
		return $key;
	}

	/**
	 * Drop invalid rows and put any missing built-in field back.
	 *
	 * @param mixed $raw Stored or posted list.
	 * @return array<int, array<string, string>>
	 */
	public static function normalize( $raw ): array {
		$defaults    = self::defaults();
		$builtin_ids = array();
		foreach ( $defaults as $field ) {
			$builtin_ids[ $field['id'] ] = $field;
		}

		$clean     = array();
		$seen_ids  = array();
		$seen_keys = array();
		if ( is_array( $raw ) ) {
			foreach ( $raw as $item ) {
				if ( ! is_array( $item ) ) {
					continue;
				}
				$id = isset( $item['id'] ) ? self::field_id( $item['id'] ) : '';
				if ( '' === $id || isset( $seen_ids[ $id ] ) ) {
					continue;
				}
				if ( isset( $builtin_ids[ $id ] ) ) {
					$field = $builtin_ids[ $id ];
					$label = isset( $item['label'] ) ? self::plain_text( $item['label'] ) : '';
					if ( '' !== $label ) {
						$field['label'] = $label;
					}
					$clean[]         = $field;
					$seen_ids[ $id ] = true;
					continue;
				}
				$custom = self::custom_from_item( $item, $id );
				if ( null === $custom || isset( $seen_keys[ $custom['meta_key'] ] ) ) {
					continue;
				}
				$clean[]                          = $custom;
				$seen_ids[ $id ]                  = true;
				$seen_keys[ $custom['meta_key'] ] = true;
			}
		}

		foreach ( $defaults as $field ) {
			if ( ! isset( $seen_ids[ $field['id'] ] ) ) {
				$clean[] = $field;
			}
		}
		return $clean;
	}

	/**
	 * Apply the detail-fields form. Built-in keys and custom keys already stored are not taken from the request.
	 *
	 * @param array<int, array<string, string>> $current    Current list.
	 * @param mixed                              $posted     Posted rows keyed by field id.
	 * @param mixed                              $new_label  Label for a field to add.
	 * @param mixed                              $new_type   text, number, or currency.
	 * @return array{fields: array<int, array<string, string>>, error: string}
	 */
	public static function apply_form( array $current, $posted, $new_label, $new_type ): array {
		$current = self::normalize( $current );
		$by_id   = array();
		foreach ( $current as $field ) {
			$by_id[ $field['id'] ] = $field;
		}

		$rows  = array();
		$index = 0;
		if ( is_array( $posted ) ) {
			foreach ( $posted as $posted_id => $row ) {
				$id = self::field_id( $posted_id );
				if ( '' === $id || ! isset( $by_id[ $id ] ) || ! is_array( $row ) ) {
					++$index;
					continue;
				}
				if ( self::is_remove_request( $row ) && 'custom' === $by_id[ $id ]['source'] ) {
					unset( $by_id[ $id ] );
					++$index;
					continue;
				}
				$field = $by_id[ $id ];
				$label = isset( $row['label'] ) ? self::plain_text( $row['label'] ) : '';
				if ( '' !== $label ) {
					$field['label'] = $label;
				}
				$field['_position'] = isset( $row['position'] ) && is_numeric( $row['position'] ) ? (int) $row['position'] : $index;
				$field['_index']    = $index;
				$rows[]             = $field;
				++$index;
			}
		}

		usort(
			$rows,
			static function ( $first, $second ) {
				if ( $first['_position'] === $second['_position'] ) {
					return $first['_index'] <=> $second['_index'];
				}
				return $first['_position'] <=> $second['_position'];
			}
		);

		$clean = array();
		$seen  = array();
		foreach ( $rows as $field ) {
			unset( $field['_position'], $field['_index'] );
			$clean[]              = $field;
			$seen[ $field['id'] ] = true;
		}
		foreach ( self::defaults() as $builtin ) {
			if ( isset( $seen[ $builtin['id'] ] ) ) {
				continue;
			}
			$clean[]                = isset( $by_id[ $builtin['id'] ] ) ? $by_id[ $builtin['id'] ] : $builtin;
			$seen[ $builtin['id'] ] = true;
		}

		$error     = '';
		$new_label = self::plain_text( $new_label );
		$new_type  = is_string( $new_type ) ? $new_type : '';
		if ( '' !== $new_label ) {
			$added = self::added_field( $clean, $new_label, $new_type );
			$error = $added['error'];
			if ( '' === $error ) {
				$clean[] = $added['field'];
			}
		}

		return array(
			'fields' => self::normalize( $clean ),
			'error'  => $error,
		);
	}

	/**
	 * Public rows in field order. Blank values are left out.
	 *
	 * @param array<int, array<string, string>> $fields Field list.
	 * @param array<string, mixed>              $values city, state, type, repair_amount, and custom meta keys.
	 * @return array<int, array<string, string>>
	 */
	public static function ordered_public_rows( array $fields, array $values ): array {
		$rows = array();
		foreach ( self::normalize( $fields ) as $field ) {
			$value = self::display_value( $field, $values );
			if ( '' === $value ) {
				continue;
			}
			$rows[] = array(
				'id'    => $field['id'],
				'label' => $field['label'],
				'value' => $value,
			);
		}
		return $rows;
	}

	/**
	 * @param mixed $value Raw value.
	 * @return string
	 */
	public static function plain_text( $value ): string {
		if ( is_array( $value ) || is_object( $value ) ) {
			return '';
		}
		$value = trim( (string) $value );
		if ( function_exists( 'sanitize_text_field' ) ) {
			$value = sanitize_text_field( $value );
		} else {
			$value = trim( (string) preg_replace( '/[\r\n\t]+/', ' ', strip_tags( $value ) ) );
		}
		if ( strlen( $value ) > 120 ) {
			$value = function_exists( 'mb_substr' ) ? mb_substr( $value, 0, 120 ) : substr( $value, 0, 120 );
		}
		return $value;
	}

	/**
	 * @param mixed $amount Stored number.
	 * @return string
	 */
	public static function plain_number( $amount ): string {
		if ( null === $amount || false === $amount || '' === $amount || ! is_numeric( $amount ) ) {
			return '';
		}
		return trim( (string) $amount );
	}

	/**
	 * @param mixed $id Raw id.
	 * @return string
	 */
	private static function field_id( $id ): string {
		$id = strtolower( trim( (string) $id ) );
		if ( 1 === preg_match( '/^[a-z0-9_]+$/', $id ) ) {
			return $id;
		}
		return '';
	}

	/**
	 * @param array<string, mixed> $item Row.
	 * @param string               $id   Field id.
	 * @return array<string, string>|null
	 */
	private static function custom_from_item( array $item, $id ): ?array {
		$source   = isset( $item['source'] ) ? (string) $item['source'] : '';
		$type     = isset( $item['type'] ) ? (string) $item['type'] : '';
		$meta_key = isset( $item['meta_key'] ) ? (string) $item['meta_key'] : '';
		if ( 'custom' !== $source || ! in_array( $type, array( 'text', 'number', 'currency' ), true ) ) {
			return null;
		}
		if ( ! self::is_custom_meta_key( $meta_key ) || substr( $meta_key, 5 ) !== $id ) {
			return null;
		}
		$label = isset( $item['label'] ) ? self::plain_text( $item['label'] ) : '';
		if ( '' === $label ) {
			return null;
		}
		return array(
			'id'       => $id,
			'source'   => 'custom',
			'label'    => $label,
			'type'     => $type,
			'meta_key' => $meta_key,
		);
	}

	/**
	 * @param array<string, mixed> $row Posted row.
	 * @return bool
	 */
	private static function is_remove_request( array $row ): bool {
		if ( ! isset( $row['remove'] ) ) {
			return false;
		}
		return true === $row['remove'] || 1 === $row['remove'] || '1' === (string) $row['remove'];
	}

	/**
	 * @param array<int, array<string, string>> $fields   Current fields.
	 * @param string                            $label    New label.
	 * @param string                            $type     New type.
	 * @return array{field: array<string, string>, error: string}
	 */
	private static function added_field( array $fields, $label, $type ): array {
		$empty = array(
			'id'       => '',
			'source'   => 'custom',
			'label'    => '',
			'type'     => '',
			'meta_key' => '',
		);
		if ( ! in_array( $type, array( 'text', 'number', 'currency' ), true ) ) {
			return array(
				'field' => $empty,
				'error' => 'bad_type',
			);
		}
		$key = self::meta_key_from_label( $label );
		if ( '' === $key ) {
			return array(
				'field' => $empty,
				'error' => 'empty_key',
			);
		}
		$id = substr( $key, 5 );
		foreach ( self::defaults() as $builtin ) {
			if ( $builtin['id'] === $id ) {
				return array(
					'field' => $empty,
					'error' => 'reserved',
				);
			}
		}
		foreach ( $fields as $field ) {
			$same_id  = $field['id'] === $id;
			$same_key = isset( $field['meta_key'] ) && $field['meta_key'] === $key;
			if ( $same_id || $same_key ) {
				return array(
					'field' => $empty,
					'error' => 'duplicate',
				);
			}
		}
		return array(
			'field' => array(
				'id'       => $id,
				'source'   => 'custom',
				'label'    => $label,
				'type'     => $type,
				'meta_key' => $key,
			),
			'error' => '',
		);
	}

	/**
	 * @param array<string, string> $field  Field definition.
	 * @param array<string, mixed>  $values Stored values.
	 * @return string
	 */
	private static function display_value( array $field, array $values ): string {
		if ( 'builtin' === $field['source'] && 'location' === $field['id'] ) {
			return Plugin::location_line(
				isset( $values['city'] ) ? $values['city'] : '',
				isset( $values['state'] ) ? $values['state'] : ''
			);
		}
		if ( 'builtin' === $field['source'] && 'type' === $field['id'] ) {
			return trim( (string) ( isset( $values['type'] ) ? $values['type'] : '' ) );
		}
		if ( 'builtin' === $field['source'] && 'repair_amount' === $field['id'] ) {
			return Plugin::repair_amount_display( isset( $values['repair_amount'] ) ? $values['repair_amount'] : '' );
		}
		if ( 'custom' !== $field['source'] || ! isset( $field['meta_key'] ) ) {
			return '';
		}
		$raw = isset( $values[ $field['meta_key'] ] ) ? $values[ $field['meta_key'] ] : '';
		if ( 'currency' === $field['type'] ) {
			return Plugin::repair_amount_display( $raw );
		}
		if ( 'number' === $field['type'] ) {
			return self::plain_number( $raw );
		}
		return trim( (string) $raw );
	}
}
