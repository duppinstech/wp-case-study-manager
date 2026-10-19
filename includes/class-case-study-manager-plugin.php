<?php
/**
 * Core plugin class.
 *
 * Copyright (C) 2026 Odell Duppins / Duppins Technology
 * License: GPL-2.0-or-later
 *
 * @package DuppinsTech\CaseStudies
 */

namespace DuppinsTech\CaseStudies;

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/class-case-study-detail-fields.php';

/**
 * Singleton handling registration and hooks.
 */
final class Plugin {

	/**
	 * Post type slug.
	 */
	public const POST_TYPE = 'case_study';

	/**
	 * Taxonomy slug.
	 */
	public const TAXONOMY = 'case_study_category';

	/**
	 * Meta keys (unchanged from snippet compatibility).
	 */
	public const META_LOCATION_CITY  = '_ra_location_city';
	public const META_LOCATION_STATE = '_ra_location_state';
	public const META_CASE_TYPE      = '_ra_case_type';
	public const META_REPAIR_AMOUNT  = '_ra_repair_amount';
	public const META_LOCATION_FULL  = '_ra_location_full';

	private const NONCE_ACTION = 'ra_case_study_details_save';
	private const NONCE_NAME   = 'ra_case_study_details_nonce';

	/**
	 * @var self|null
	 */
	private static $instance = null;

	/**
	 * @return self
	 */
	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		$this->hooks();
	}

	private function hooks(): void {
		add_action( 'init', array( $this, 'register_post_type_and_taxonomy' ), 5 );
		add_action( 'init', array( $this, 'register_post_meta_fields' ), 11 );
		add_action( 'init', array( $this, 'add_elementor_support' ), 20 );

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_public_assets' ) );
		add_filter( 'the_content', array( $this, 'append_details_to_content' ), 12 );
		add_shortcode( 'case_study_details', array( $this, 'shortcode_details' ) );

		add_action( 'add_meta_boxes', array( $this, 'register_meta_box' ) );
		add_action( 'save_post_' . self::POST_TYPE, array( $this, 'save_meta_box' ), 10, 2 );
		add_action( 'admin_menu', array( $this, 'register_detail_fields_page' ) );

		add_filter( 'manage_edit-' . self::POST_TYPE . '_columns', array( $this, 'edit_columns' ) );
		add_action( 'manage_' . self::POST_TYPE . '_posts_custom_column', array( $this, 'render_column' ), 10, 2 );
		add_filter( 'manage_edit-' . self::POST_TYPE . '_sortable_columns', array( $this, 'sortable_columns' ) );
		add_action( 'pre_get_posts', array( $this, 'sort_by_repair_amount' ) );
	}

	public function activate(): void {
		$this->register_post_type_and_taxonomy();
		flush_rewrite_rules();
	}

	public function deactivate(): void {
		flush_rewrite_rules();
	}

	/**
	 * Register post meta for REST API and explicit auth/sanitize handling.
	 */
	public function register_post_meta_fields(): void {
		$string_args = array(
			'type'              => 'string',
			'single'            => true,
			'show_in_rest'      => true,
			'auth_callback'     => array( $this, 'meta_auth_can_edit_post' ),
			'sanitize_callback' => 'sanitize_text_field',
		);

		register_post_meta( self::POST_TYPE, self::META_LOCATION_CITY, $string_args );
		register_post_meta( self::POST_TYPE, self::META_LOCATION_STATE, $string_args );
		register_post_meta( self::POST_TYPE, self::META_CASE_TYPE, $string_args );
		register_post_meta( self::POST_TYPE, self::META_LOCATION_FULL, $string_args );

		register_post_meta(
			self::POST_TYPE,
			self::META_REPAIR_AMOUNT,
			array(
				'type'              => 'string',
				'single'            => true,
				'show_in_rest'      => true,
				'auth_callback'     => array( $this, 'meta_auth_can_edit_post' ),
				'sanitize_callback' => array( $this, 'sanitize_meta_repair_amount' ),
			)
		);

		foreach ( $this->detail_fields() as $field ) {
			if ( 'custom' !== $field['source'] ) {
				continue;
			}
			$args = $string_args;
			if ( 'text' !== $field['type'] ) {
				$args['sanitize_callback'] = array( $this, 'sanitize_meta_repair_amount' );
			}
			register_post_meta( self::POST_TYPE, $field['meta_key'], $args );
		}
	}

	/**
	 * @param bool   $allowed Whether meta can be edited.
	 * @param string $meta_key Meta key.
	 * @param int    $object_id Post ID.
	 * @return bool
	 */
	public function meta_auth_can_edit_post( $allowed, $meta_key, $object_id ) {
		return (bool) current_user_can( 'edit_post', (int) $object_id );
	}

	/**
	 * Match metabox save behavior: empty string or float.
	 *
	 * @param mixed  $value Raw meta value.
	 * @param string $meta_key Meta key.
	 * @param string $object_type Object type.
	 * @param string $post_type Post type (object subtype).
	 * @return float|string
	 */
	public function sanitize_meta_repair_amount( $value, $meta_key = '', $object_type = '', $post_type = '' ) {
		if ( '' === $value || null === $value ) {
			return '';
		}
		if ( is_numeric( $value ) ) {
			return floatval( $value );
		}
		$value = is_string( $value ) ? trim( $value ) : '';
		return '' === $value ? '' : floatval( $value );
	}

	/**
	 * Enqueue admin styles only on case study edit screens.
	 *
	 * @param string $hook_suffix Current admin page.
	 */
	public function enqueue_admin_assets( $hook_suffix ): void {
		if ( ! in_array( $hook_suffix, array( 'post.php', 'post-new.php' ), true ) ) {
			return;
		}

		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen || self::POST_TYPE !== $screen->post_type ) {
			return;
		}

		wp_enqueue_style(
			'csm-admin-metabox',
			CSM_PLUGIN_URL . 'assets/css/admin-metabox.css',
			array(),
			CSM_VERSION
		);
	}

	/**
	 * Front-end styles for the single case study page and the shortcode.
	 */
	public function enqueue_public_assets(): void {
		$load = is_singular( self::POST_TYPE );
		if ( ! $load ) {
			$post = get_post();
			$load = $post && has_shortcode( (string) $post->post_content, 'case_study_details' );
		}
		if ( ! $load ) {
			return;
		}

		wp_enqueue_style(
			'csm-case-study-details',
			CSM_PLUGIN_URL . 'assets/css/case-study-details.css',
			array(),
			CSM_VERSION
		);
	}

	/**
	 * Append saved details when the theme prints the case study content.
	 *
	 * @param string $content Post content.
	 * @return string
	 */
	public function append_details_to_content( $content ) {
		if ( ! is_singular( self::POST_TYPE ) || ! in_the_loop() || ! is_main_query() ) {
			return $content;
		}
		if ( has_shortcode( (string) $content, 'case_study_details' ) ) {
			return $content;
		}
		$post_id = (int) get_the_ID();
		if ( post_password_required( $post_id ) ) {
			return $content;
		}
		return $content . $this->render_details_for_post( $post_id );
	}

	/**
	 * Shortcode for Elementor or any layout that does not print post content.
	 *
	 * @param array<string, string>|string $atts Attributes.
	 * @return string
	 */
	public function shortcode_details( $atts ) {
		$atts    = shortcode_atts(
			array(
				'id' => 0,
			),
			$atts,
			'case_study_details'
		);
		$post_id = (int) $atts['id'];
		if ( $post_id <= 0 ) {
			$post_id = (int) get_the_ID();
		}
		return $this->render_details_for_post( $post_id );
	}

	/**
	 * Details for a case study the current visitor is allowed to read.
	 *
	 * @param int $post_id Post ID.
	 * @return string
	 */
	public function render_details_for_post( $post_id ) {
		$post_id = (int) $post_id;
		$post    = get_post( $post_id );
		if ( ! $post instanceof \WP_Post || self::POST_TYPE !== $post->post_type ) {
			return '';
		}
		if ( post_password_required( $post ) ) {
			return '';
		}
		if ( 'publish' !== $post->post_status && ! current_user_can( 'read_post', $post->ID ) ) {
			return '';
		}

		return self::details_markup_rows( $this->public_rows_for_post( $post->ID ) );
	}

	/**
	 * Saved field list, or the built-in list when the option is missing.
	 *
	 * @return array<int, array<string, string>>
	 */
	public function detail_fields(): array {
		if ( ! function_exists( 'get_option' ) ) {
			return DetailFields::defaults();
		}
		$stored = get_option( DetailFields::OPTION, null );
		if ( null === $stored || false === $stored ) {
			return DetailFields::defaults();
		}
		return DetailFields::normalize( $stored );
	}

	/**
	 * Public rows for one case study, in the saved field order.
	 *
	 * @param int $post_id Post ID.
	 * @return array<int, array<string, string>>
	 */
	public function public_rows_for_post( $post_id ): array {
		$post_id = (int) $post_id;
		$fields  = $this->detail_fields();
		$values  = array(
			'city'          => get_post_meta( $post_id, self::META_LOCATION_CITY, true ),
			'state'         => get_post_meta( $post_id, self::META_LOCATION_STATE, true ),
			'type'          => get_post_meta( $post_id, self::META_CASE_TYPE, true ),
			'repair_amount' => get_post_meta( $post_id, self::META_REPAIR_AMOUNT, true ),
		);
		foreach ( $fields as $field ) {
			if ( 'custom' === $field['source'] ) {
				$values[ $field['meta_key'] ] = get_post_meta( $post_id, $field['meta_key'], true );
			}
		}
		$rows = DetailFields::ordered_public_rows( $fields, $values );
		foreach ( $rows as $index => $row ) {
			$rows[ $index ]['label'] = self::translate_known_label( $row['label'] );
		}
		return $rows;
	}

	/**
	 * City and state on one line. Blank parts are left out.
	 *
	 * @param mixed $city City.
	 * @param mixed $state State.
	 * @return string
	 */
	public static function location_line( $city, $state ): string {
		$city  = trim( (string) $city );
		$state = trim( (string) $state );
		$line  = $city;
		if ( '' !== $city && '' !== $state ) {
			$line .= ', ';
		}
		$line .= $state;
		return trim( $line );
	}

	/**
	 * Repair amount for display, or an empty string when there is nothing to show.
	 *
	 * @param mixed $amount Stored amount.
	 * @return string
	 */
	public static function repair_amount_display( $amount ): string {
		if ( null === $amount || false === $amount || '' === $amount ) {
			return '';
		}
		if ( ! is_numeric( $amount ) ) {
			return '';
		}
		$formatted = function_exists( 'number_format_i18n' )
			? number_format_i18n( (float) $amount, 2 )
			: number_format( (float) $amount, 2 );
		return '$' . $formatted;
	}

	/**
	 * Rows the public page shows: location, type, and repair amount when present.
	 *
	 * @param mixed $city City.
	 * @param mixed $state State.
	 * @param mixed $type Job type.
	 * @param mixed $amount Repair amount.
	 * @return array<string, string>
	 */
	public static function public_detail_rows( $city, $state, $type, $amount ): array {
		$rows     = array();
		$location = self::location_line( $city, $state );
		if ( '' !== $location ) {
			$rows['location'] = $location;
		}
		$type = trim( (string) $type );
		if ( '' !== $type ) {
			$rows['type'] = $type;
		}
		$amount_display = self::repair_amount_display( $amount );
		if ( '' !== $amount_display ) {
			$rows['repair_amount'] = $amount_display;
		}
		return $rows;
	}

	/**
	 * @param array<string, string> $rows Detail rows.
	 * @return string
	 */
	public static function details_markup( array $rows ): string {
		$labels = array(
			'location'      => self::translate( 'Location' ),
			'type'          => self::translate( 'Type' ),
			'repair_amount' => self::translate( 'Total Repair Amount' ),
		);
		$list   = array();
		foreach ( $labels as $key => $label ) {
			if ( ! isset( $rows[ $key ] ) || '' === (string) $rows[ $key ] ) {
				continue;
			}
			$list[] = array(
				'label' => $label,
				'value' => (string) $rows[ $key ],
			);
		}
		return self::details_markup_rows( $list );
	}

	/**
	 * @param array<int, array<string, string>> $rows Rows with label and value.
	 * @return string
	 */
	public static function details_markup_rows( array $rows ): string {
		$items = '';
		foreach ( $rows as $row ) {
			if ( ! is_array( $row ) || ! isset( $row['label'], $row['value'] ) || '' === (string) $row['value'] ) {
				continue;
			}
			$items .= '<dt>' . self::escape_text( (string) $row['label'] ) . '</dt>';
			$items .= '<dd>' . self::escape_text( (string) $row['value'] ) . '</dd>';
		}
		if ( '' === $items ) {
			return '';
		}
		$label = self::escape_text( self::translate( 'Case study details' ) );
		return '<section class="csm-details" aria-label="' . $label . '"><dl>' . $items . '</dl></section>';
	}

	/**
	 * Translate the three built-in labels. A renamed or custom label is returned as stored.
	 *
	 * @param string $label Field label.
	 * @return string
	 */
	private static function translate_known_label( $label ): string {
		if ( 'Location' === $label ) {
			return self::translate( 'Location' );
		}
		if ( 'Type' === $label ) {
			return self::translate( 'Type' );
		}
		if ( 'Total Repair Amount' === $label ) {
			return self::translate( 'Total Repair Amount' );
		}
		return $label;
	}

	/**
	 * @param string $text Source string.
	 * @return string
	 */
	private static function translate( $text ): string {
		if ( function_exists( '__' ) ) {
			// Callers pass string literals. This wrapper exists so tests can run without WordPress.
			return __( $text, 'case-study-manager' ); // phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText
		}
		return $text;
	}

	/**
	 * @param string $value Text.
	 * @return string
	 */
	private static function escape_text( $value ): string {
		if ( function_exists( 'esc_html' ) ) {
			return esc_html( $value );
		}
		return htmlspecialchars( (string) $value, ENT_QUOTES, 'UTF-8' );
	}

	public function register_post_type_and_taxonomy(): void {
		$labels = array(
			'name'               => __( 'Case Studies', 'case-study-manager' ),
			'singular_name'      => __( 'Case Study', 'case-study-manager' ),
			'menu_name'          => __( 'Case Studies', 'case-study-manager' ),
			'add_new'            => __( 'Add New', 'case-study-manager' ),
			'add_new_item'       => __( 'Add New Case Study', 'case-study-manager' ),
			'edit_item'          => __( 'Edit Case Study', 'case-study-manager' ),
			'new_item'           => __( 'New Case Study', 'case-study-manager' ),
			'view_item'          => __( 'View Case Study', 'case-study-manager' ),
			'search_items'       => __( 'Search Case Studies', 'case-study-manager' ),
			'not_found'          => __( 'No case studies found', 'case-study-manager' ),
			'not_found_in_trash' => __( 'No case studies found in Trash', 'case-study-manager' ),
			'all_items'          => __( 'All Case Studies', 'case-study-manager' ),
		);

		register_post_type(
			self::POST_TYPE,
			array(
				'labels'            => $labels,
				'public'            => true,
				'show_in_rest'      => true,
				'has_archive'       => true,
				'rewrite'           => array( 'slug' => 'case-studies' ),
				'menu_icon'         => 'dashicons-hammer',
				'supports'          => array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions', 'custom-fields' ),
				'hierarchical'      => false,
				'show_in_nav_menus' => true,
				'capability_type'   => 'post',
				'map_meta_cap'      => true,
			)
		);

		register_taxonomy(
			self::TAXONOMY,
			array( self::POST_TYPE ),
			array(
				'labels'            => array(
					'name'          => __( 'Case Study Categories', 'case-study-manager' ),
					'singular_name' => __( 'Case Study Category', 'case-study-manager' ),
					'search_items'  => __( 'Search Categories', 'case-study-manager' ),
					'all_items'     => __( 'All Categories', 'case-study-manager' ),
					'edit_item'     => __( 'Edit Category', 'case-study-manager' ),
					'update_item'   => __( 'Update Category', 'case-study-manager' ),
					'add_new_item'  => __( 'Add New Category', 'case-study-manager' ),
					'new_item_name' => __( 'New Category Name', 'case-study-manager' ),
					'menu_name'     => __( 'Categories', 'case-study-manager' ),
				),
				'public'            => true,
				'hierarchical'      => true,
				'show_ui'           => true,
				'show_admin_column' => true,
				'show_in_rest'      => true,
				'rewrite'           => array( 'slug' => 'case-study-category' ),
			)
		);
	}

	public function add_elementor_support(): void {
		add_post_type_support( self::POST_TYPE, 'elementor' );
	}

	public function register_meta_box(): void {
		add_meta_box(
			'case_study_details',
			__( 'Case Study Details', 'case-study-manager' ),
			array( $this, 'render_meta_box' ),
			self::POST_TYPE,
			'normal',
			'high'
		);
	}

	/**
	 * @param \WP_Post $post Post object.
	 */
	public function render_meta_box( $post ): void {
		wp_nonce_field( self::NONCE_ACTION, self::NONCE_NAME );

		$location_city  = get_post_meta( $post->ID, self::META_LOCATION_CITY, true );
		$location_state = get_post_meta( $post->ID, self::META_LOCATION_STATE, true );
		$type           = get_post_meta( $post->ID, self::META_CASE_TYPE, true );
		$repair_amount  = get_post_meta( $post->ID, self::META_REPAIR_AMOUNT, true );
		$custom_values  = array();
		foreach ( $this->detail_fields() as $field ) {
			if ( 'custom' === $field['source'] ) {
				$custom_values[ $field['meta_key'] ] = get_post_meta( $post->ID, $field['meta_key'], true );
			}
		}
		?>

		<div class="csm-metabox-grid">
			<?php foreach ( $this->detail_fields() as $field ) : ?>
				<?php if ( 'builtin' === $field['source'] && 'location' === $field['id'] ) : ?>
					<fieldset class="csm-metabox-group">
						<legend><?php echo esc_html( self::translate_known_label( $field['label'] ) ); ?></legend>
						<label for="duppins_ra_location_city"><?php esc_html_e( 'City', 'case-study-manager' ); ?></label>
						<input type="text" id="duppins_ra_location_city" name="ra_location_city" value="<?php echo esc_attr( $location_city ); ?>" placeholder="<?php esc_attr_e( 'e.g., Charlotte', 'case-study-manager' ); ?>" autocomplete="address-level2" />
						<label for="duppins_ra_location_state"><?php esc_html_e( 'State', 'case-study-manager' ); ?></label>
						<input type="text" id="duppins_ra_location_state" name="ra_location_state" value="<?php echo esc_attr( $location_state ); ?>" placeholder="<?php esc_attr_e( 'e.g., NC', 'case-study-manager' ); ?>" autocomplete="address-level1" />
					</fieldset>
				<?php elseif ( 'builtin' === $field['source'] && 'type' === $field['id'] ) : ?>
					<label for="duppins_ra_case_type"><?php echo esc_html( self::translate_known_label( $field['label'] ) ); ?></label>
					<input type="text" id="duppins_ra_case_type" name="ra_case_type" value="<?php echo esc_attr( $type ); ?>" placeholder="<?php esc_attr_e( 'e.g., Remodel, Roofing, Foundation Repair', 'case-study-manager' ); ?>" />
				<?php elseif ( 'builtin' === $field['source'] && 'repair_amount' === $field['id'] ) : ?>
					<label for="duppins_ra_repair_amount"><?php echo esc_html( self::translate_known_label( $field['label'] ) ); ?> ($)</label>
					<input type="number" id="duppins_ra_repair_amount" name="ra_repair_amount" value="<?php echo esc_attr( $repair_amount ); ?>" placeholder="<?php esc_attr_e( 'e.g., 15000', 'case-study-manager' ); ?>" step="0.01" min="0" inputmode="decimal" />
				<?php elseif ( 'custom' === $field['source'] ) : ?>
					<?php
					$input_id = 'duppins_csm_' . $field['id'];
					$value    = isset( $custom_values[ $field['meta_key'] ] ) ? $custom_values[ $field['meta_key'] ] : '';
					?>
					<label for="<?php echo esc_attr( $input_id ); ?>"><?php echo esc_html( $field['label'] ); ?><?php echo 'currency' === $field['type'] ? ' ($)' : ''; ?></label>
					<?php if ( 'text' === $field['type'] ) : ?>
						<input type="text" id="<?php echo esc_attr( $input_id ); ?>" name="csm_custom[<?php echo esc_attr( $field['meta_key'] ); ?>]" value="<?php echo esc_attr( $value ); ?>" />
					<?php else : ?>
						<input type="number" id="<?php echo esc_attr( $input_id ); ?>" name="csm_custom[<?php echo esc_attr( $field['meta_key'] ); ?>]" value="<?php echo esc_attr( $value ); ?>" step="0.01" min="0" inputmode="decimal" />
					<?php endif; ?>
				<?php endif; ?>
			<?php endforeach; ?>
		</div>

		<p class="csm-metabox-help">
			<?php esc_html_e( 'Meta keys:', 'case-study-manager' ); ?>
			<code><?php echo esc_html( self::META_LOCATION_CITY ); ?></code>,
			<code><?php echo esc_html( self::META_LOCATION_STATE ); ?></code>,
			<code><?php echo esc_html( self::META_CASE_TYPE ); ?></code>,
			<code><?php echo esc_html( self::META_REPAIR_AMOUNT ); ?></code>
			<?php foreach ( $this->detail_fields() as $field ) : ?>
				<?php if ( 'custom' === $field['source'] ) : ?>
					, <code><?php echo esc_html( $field['meta_key'] ); ?></code>
				<?php endif; ?>
			<?php endforeach; ?>
		</p>
		<?php
	}

	/**
	 * @param int      $post_id Post ID.
	 * @param \WP_Post $post    Post object.
	 */
	public function save_meta_box( $post_id, $post ): void {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}

		$nonce = filter_input( INPUT_POST, self::NONCE_NAME, FILTER_UNSAFE_RAW );
		$nonce = is_string( $nonce ) ? sanitize_text_field( $nonce ) : '';
		if ( ! $nonce || ! wp_verify_nonce( $nonce, self::NONCE_ACTION ) ) {
			return;
		}

		if ( ! isset( $post->post_type ) || self::POST_TYPE !== $post->post_type ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$location_city  = filter_input( INPUT_POST, 'ra_location_city', FILTER_UNSAFE_RAW );
		$location_state = filter_input( INPUT_POST, 'ra_location_state', FILTER_UNSAFE_RAW );
		$case_type      = filter_input( INPUT_POST, 'ra_case_type', FILTER_UNSAFE_RAW );

		update_post_meta( $post_id, self::META_LOCATION_CITY, sanitize_text_field( (string) $location_city ) );
		update_post_meta( $post_id, self::META_LOCATION_STATE, sanitize_text_field( (string) $location_state ) );
		update_post_meta( $post_id, self::META_CASE_TYPE, sanitize_text_field( (string) $case_type ) );

		$repair_raw = filter_input( INPUT_POST, 'ra_repair_amount', FILTER_UNSAFE_RAW );
		$repair_raw = is_string( $repair_raw ) ? trim( $repair_raw ) : '';
		if ( '' === $repair_raw ) {
			update_post_meta( $post_id, self::META_REPAIR_AMOUNT, '' );
		} else {
			update_post_meta( $post_id, self::META_REPAIR_AMOUNT, floatval( $repair_raw ) );
		}

		$this->save_custom_detail_fields( $post_id );

		update_post_meta(
			$post_id,
			self::META_LOCATION_FULL,
			self::location_line(
				get_post_meta( $post_id, self::META_LOCATION_CITY, true ),
				get_post_meta( $post_id, self::META_LOCATION_STATE, true )
			)
		);
	}

	/**
	 * @param array<string, string> $columns Columns.
	 * @return array<string, string>
	 */
	public function edit_columns( $columns ) {
		$new = array();
		foreach ( $columns as $key => $label ) {
			$new[ $key ] = $label;

			if ( 'title' === $key ) {
				$new['ra_location']      = __( 'Location', 'case-study-manager' );
				$new['ra_type']          = __( 'Type', 'case-study-manager' );
				$new['ra_repair_amount'] = __( 'Repair Amount', 'case-study-manager' );
			}
		}
		return $new;
	}

	/**
	 * @param string $column Column name.
	 * @param int    $post_id Post ID.
	 */
	public function render_column( $column, $post_id ): void {
		if ( 'ra_location' === $column ) {
			$out = self::location_line(
				get_post_meta( $post_id, self::META_LOCATION_CITY, true ),
				get_post_meta( $post_id, self::META_LOCATION_STATE, true )
			);
			echo $out ? esc_html( $out ) : '&mdash;';
			return;
		}

		if ( 'ra_type' === $column ) {
			$type = get_post_meta( $post_id, self::META_CASE_TYPE, true );
			echo $type ? esc_html( $type ) : '&mdash;';
			return;
		}

		if ( 'ra_repair_amount' === $column ) {
			$display = self::repair_amount_display( get_post_meta( $post_id, self::META_REPAIR_AMOUNT, true ) );
			echo '' === $display ? '&mdash;' : esc_html( $display );
		}
	}

	/**
	 * @param array<string, string> $columns Sortable columns.
	 * @return array<string, string>
	 */
	public function sortable_columns( $columns ) {
		$columns['ra_repair_amount'] = 'ra_repair_amount';
		return $columns;
	}

	/**
	 * @param \WP_Query $query Query.
	 */
	public function sort_by_repair_amount( $query ): void {
		if ( ! is_admin() || ! $query->is_main_query() ) {
			return;
		}

		if ( ! current_user_can( 'edit_posts' ) ) {
			return;
		}

		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen || self::POST_TYPE !== $screen->post_type ) {
			return;
		}

		if ( 'ra_repair_amount' === $query->get( 'orderby' ) ) {
			$query->set( 'meta_key', self::META_REPAIR_AMOUNT );
			$query->set( 'orderby', 'meta_value_num' );
		}
	}

	/**
	 * Write custom detail values posted with the metabox. Missing inputs are left unchanged.
	 *
	 * @param int $post_id Post ID.
	 */
	private function save_custom_detail_fields( $post_id ): void {
		// Nonce, post type, and edit_post were checked by save_meta_box().
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$posted = isset( $_POST['csm_custom'] ) ? wp_unslash( $_POST['csm_custom'] ) : array();
		if ( ! is_array( $posted ) ) {
			return;
		}
		foreach ( $this->detail_fields() as $field ) {
			if ( 'custom' !== $field['source'] || ! array_key_exists( $field['meta_key'], $posted ) ) {
				continue;
			}
			$raw = $posted[ $field['meta_key'] ];
			if ( 'text' === $field['type'] ) {
				update_post_meta( $post_id, $field['meta_key'], sanitize_text_field( is_string( $raw ) ? $raw : '' ) );
				continue;
			}
			$raw = is_string( $raw ) ? trim( $raw ) : '';
			if ( '' === $raw ) {
				update_post_meta( $post_id, $field['meta_key'], '' );
			} else {
				update_post_meta( $post_id, $field['meta_key'], floatval( $raw ) );
			}
		}
	}

	/**
	 * Case Studies → Detail fields. Administrators manage the list. Editors fill values on the case study.
	 */
	public function register_detail_fields_page(): void {
		add_submenu_page(
			'edit.php?post_type=' . self::POST_TYPE,
			__( 'Detail fields', 'case-study-manager' ),
			__( 'Detail fields', 'case-study-manager' ),
			'manage_options',
			DetailFields::PAGE_SLUG,
			array( $this, 'render_detail_fields_page' )
		);
	}

	/**
	 * Settings screen for the ordered detail list.
	 */
	public function render_detail_fields_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die(
				esc_html__( 'Sorry, you are not allowed to manage these fields.', 'case-study-manager' ),
				'',
				array( 'response' => 403 )
			);
		}

		$this->handle_detail_fields_post();

		$notice = filter_input( INPUT_GET, 'csm_notice', FILTER_UNSAFE_RAW );
		$error  = filter_input( INPUT_GET, 'csm_error', FILTER_UNSAFE_RAW );
		$notice = is_string( $notice ) ? sanitize_text_field( $notice ) : '';
		$error  = is_string( $error ) ? sanitize_text_field( $error ) : '';
		$fields = $this->detail_fields();
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Detail fields', 'case-study-manager' ); ?></h1>
			<?php if ( 'saved' === $notice ) : ?>
				<div class="notice notice-success"><p><?php esc_html_e( 'Detail fields saved.', 'case-study-manager' ); ?></p></div>
			<?php endif; ?>
			<?php echo wp_kses_post( self::detail_fields_error_notice( $error ) ); ?>
			<p><?php esc_html_e( 'These fields appear on the published case study page and in the REST API, the same as city, type, and repair amount. There is no private setting.', 'case-study-manager' ); ?></p>
			<p><?php esc_html_e( 'Removing a custom field hides it. Values already saved on case studies stay in the database.', 'case-study-manager' ); ?></p>
			<form method="post" action="<?php echo esc_url( admin_url( 'edit.php?post_type=' . self::POST_TYPE . '&page=' . DetailFields::PAGE_SLUG ) ); ?>">
				<?php wp_nonce_field( DetailFields::NONCE_ACTION, DetailFields::NONCE_NAME ); ?>
				<table class="widefat striped">
					<thead>
						<tr>
							<th scope="col"><?php esc_html_e( 'Order', 'case-study-manager' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Label', 'case-study-manager' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Type', 'case-study-manager' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Key', 'case-study-manager' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Remove', 'case-study-manager' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $fields as $index => $field ) : ?>
							<?php $input_id = 'csm-field-' . $field['id']; ?>
							<tr>
								<td>
									<label class="screen-reader-text" for="<?php echo esc_attr( $input_id ); ?>-order">
										<?php
										echo esc_html(
											sprintf(
												/* translators: %s: field label */
												__( 'Order for %s', 'case-study-manager' ),
												$field['label']
											)
										);
										?>
									</label>
									<input type="number" class="small-text" id="<?php echo esc_attr( $input_id ); ?>-order" name="csm_fields[<?php echo esc_attr( $field['id'] ); ?>][position]" value="<?php echo esc_attr( (string) ( ( $index + 1 ) * 10 ) ); ?>" step="1" />
								</td>
								<td>
									<label class="screen-reader-text" for="<?php echo esc_attr( $input_id ); ?>-label">
										<?php
										echo esc_html(
											sprintf(
												/* translators: %s: field label */
												__( 'Label for %s', 'case-study-manager' ),
												$field['label']
											)
										);
										?>
									</label>
									<input type="text" class="regular-text" id="<?php echo esc_attr( $input_id ); ?>-label" name="csm_fields[<?php echo esc_attr( $field['id'] ); ?>][label]" value="<?php echo esc_attr( $field['label'] ); ?>" maxlength="120" />
								</td>
								<td><?php echo esc_html( self::detail_type_label( $field['type'] ) ); ?></td>
								<td><?php echo wp_kses_post( self::detail_key_html( $field ) ); ?></td>
								<td>
									<?php if ( 'custom' === $field['source'] ) : ?>
										<label for="<?php echo esc_attr( $input_id ); ?>-remove">
											<input type="checkbox" id="<?php echo esc_attr( $input_id ); ?>-remove" name="csm_fields[<?php echo esc_attr( $field['id'] ); ?>][remove]" value="1" />
											<?php
											echo esc_html(
												sprintf(
													/* translators: %s: field label */
													__( 'Remove %s', 'case-study-manager' ),
													$field['label']
												)
											);
											?>
										</label>
									<?php else : ?>
										<span aria-hidden="true">&mdash;</span>
										<span class="screen-reader-text"><?php esc_html_e( 'Built-in fields stay on the case study.', 'case-study-manager' ); ?></span>
									<?php endif; ?>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
				<h2><?php esc_html_e( 'Add a detail field', 'case-study-manager' ); ?></h2>
				<p class="description">
					<?php esc_html_e( 'The key is created from the label, starts with _csm_, and cannot be changed. Example: Insurance carrier is stored as', 'case-study-manager' ); ?>
					<code>_csm_insurance_carrier</code>.
					<?php esc_html_e( 'A published case study page and the REST API will show the value.', 'case-study-manager' ); ?>
				</p>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><label for="csm-new-label"><?php esc_html_e( 'Label', 'case-study-manager' ); ?></label></th>
						<td><input type="text" class="regular-text" id="csm-new-label" name="csm_new_label" maxlength="120" /></td>
					</tr>
					<tr>
						<th scope="row"><label for="csm-new-type"><?php esc_html_e( 'Type', 'case-study-manager' ); ?></label></th>
						<td>
							<select id="csm-new-type" name="csm_new_type">
								<option value="text"><?php esc_html_e( 'Text', 'case-study-manager' ); ?></option>
								<option value="number"><?php esc_html_e( 'Number', 'case-study-manager' ); ?></option>
								<option value="currency"><?php esc_html_e( 'Currency', 'case-study-manager' ); ?></option>
							</select>
						</td>
					</tr>
				</table>
				<?php submit_button( __( 'Save detail fields', 'case-study-manager' ) ); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * Save the field list from the settings screen, then redirect back to it.
	 */
	private function handle_detail_fields_post(): void {
		$nonce = filter_input( INPUT_POST, DetailFields::NONCE_NAME, FILTER_UNSAFE_RAW );
		if ( ! is_string( $nonce ) ) {
			return;
		}
		$nonce = sanitize_text_field( $nonce );
		if ( ! wp_verify_nonce( $nonce, DetailFields::NONCE_ACTION ) || ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified above.
		$posted = isset( $_POST['csm_fields'] ) ? wp_unslash( $_POST['csm_fields'] ) : array();
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified above.
		$new_label = isset( $_POST['csm_new_label'] ) ? wp_unslash( $_POST['csm_new_label'] ) : '';
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified above.
		$new_type = isset( $_POST['csm_new_type'] ) ? wp_unslash( $_POST['csm_new_type'] ) : '';
		if ( ! is_array( $posted ) ) {
			$posted = array();
		}
		if ( ! is_string( $new_label ) ) {
			$new_label = '';
		}
		if ( ! is_string( $new_type ) ) {
			$new_type = '';
		}

		$result = DetailFields::apply_form( $this->detail_fields(), $posted, $new_label, $new_type );
		update_option( DetailFields::OPTION, $result['fields'] );

		$args = array(
			'post_type'  => self::POST_TYPE,
			'page'       => DetailFields::PAGE_SLUG,
			'csm_notice' => 'saved',
		);
		if ( '' !== $result['error'] ) {
			$args['csm_error'] = $result['error'];
		}
		wp_safe_redirect( add_query_arg( $args, admin_url( 'edit.php' ) ) );
		exit;
	}

	/**
	 * @param string $error Error code from the field form.
	 * @return string
	 */
	private static function detail_fields_error_notice( $error ): string {
		$messages = array(
			'empty_key' => __( 'That label needs a letter or number.', 'case-study-manager' ),
			'reserved'  => __( 'That label uses a reserved field.', 'case-study-manager' ),
			'duplicate' => __( 'That key already exists.', 'case-study-manager' ),
			'bad_type'  => __( 'Choose text, number, or currency.', 'case-study-manager' ),
		);
		if ( ! isset( $messages[ $error ] ) ) {
			return '';
		}
		return '<div class="notice notice-error"><p>' . esc_html( $messages[ $error ] ) . '</p></div>';
	}

	/**
	 * @param string $type Field type.
	 * @return string
	 */
	private static function detail_type_label( $type ): string {
		if ( 'location' === $type ) {
			return __( 'City and state', 'case-study-manager' );
		}
		if ( 'number' === $type ) {
			return __( 'Number', 'case-study-manager' );
		}
		if ( 'currency' === $type ) {
			return __( 'Currency', 'case-study-manager' );
		}
		return __( 'Text', 'case-study-manager' );
	}

	/**
	 * @param array<string, string> $field Field definition.
	 * @return string
	 */
	private static function detail_key_html( array $field ): string {
		if ( 'builtin' === $field['source'] && 'location' === $field['id'] ) {
			return '<code>' . esc_html( self::META_LOCATION_CITY ) . '</code>, <code>' . esc_html( self::META_LOCATION_STATE ) . '</code>';
		}
		if ( 'builtin' === $field['source'] && 'type' === $field['id'] ) {
			return '<code>' . esc_html( self::META_CASE_TYPE ) . '</code>';
		}
		if ( 'builtin' === $field['source'] && 'repair_amount' === $field['id'] ) {
			return '<code>' . esc_html( self::META_REPAIR_AMOUNT ) . '</code>';
		}
		if ( 'custom' === $field['source'] && isset( $field['meta_key'] ) ) {
			return '<code>' . esc_html( $field['meta_key'] ) . '</code>';
		}
		return '';
	}
}
