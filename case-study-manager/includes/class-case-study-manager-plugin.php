<?php
/**
 * Core plugin class.
 *
 * @package DuppinsTech\CaseStudies
 */

namespace DuppinsTech\CaseStudies;

defined( 'ABSPATH' ) || exit;

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
	public const META_LOCATION_CITY   = '_ra_location_city';
	public const META_LOCATION_STATE  = '_ra_location_state';
	public const META_CASE_TYPE       = '_ra_case_type';
	public const META_REPAIR_AMOUNT   = '_ra_repair_amount';
	public const META_LOCATION_FULL   = '_ra_location_full';

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

		add_action( 'add_meta_boxes', array( $this, 'register_meta_box' ) );
		add_action( 'save_post_' . self::POST_TYPE, array( $this, 'save_meta_box' ), 10, 2 );

		add_filter( 'manage_edit-' . self::POST_TYPE . '_columns', array( $this, 'edit_columns' ) );
		add_action( 'manage_' . self::POST_TYPE . '_posts_custom_column', array( $this, 'render_column' ), 10, 2 );
		add_filter( 'manage_edit-' . self::POST_TYPE . '_sortable_columns', array( $this, 'sortable_columns' ) );
		add_action( 'pre_get_posts', array( $this, 'sort_by_repair_amount' ) );

		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
	}

	public function load_textdomain(): void {
		load_plugin_textdomain(
			'case-study-manager',
			false,
			dirname( plugin_basename( CSM_PLUGIN_FILE ) ) . '/languages'
		);
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
				'supports'          => array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions' ),
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
		?>

		<div class="csm-metabox-grid">
			<label for="duppins_ra_location_city"><?php esc_html_e( 'Location (City)', 'case-study-manager' ); ?></label>
			<input type="text" id="duppins_ra_location_city" name="ra_location_city" value="<?php echo esc_attr( $location_city ); ?>" placeholder="<?php esc_attr_e( 'e.g., Charlotte', 'case-study-manager' ); ?>" autocomplete="address-level2" />

			<label for="duppins_ra_location_state"><?php esc_html_e( 'Location (State)', 'case-study-manager' ); ?></label>
			<input type="text" id="duppins_ra_location_state" name="ra_location_state" value="<?php echo esc_attr( $location_state ); ?>" placeholder="<?php esc_attr_e( 'e.g., NC', 'case-study-manager' ); ?>" autocomplete="address-level1" />

			<label for="duppins_ra_case_type"><?php esc_html_e( 'Type', 'case-study-manager' ); ?></label>
			<input type="text" id="duppins_ra_case_type" name="ra_case_type" value="<?php echo esc_attr( $type ); ?>" placeholder="<?php esc_attr_e( 'e.g., Remodel, Roofing, Foundation Repair', 'case-study-manager' ); ?>" />

			<label for="duppins_ra_repair_amount"><?php esc_html_e( 'Total Repair Amount ($)', 'case-study-manager' ); ?></label>
			<input type="number" id="duppins_ra_repair_amount" name="ra_repair_amount" value="<?php echo esc_attr( $repair_amount ); ?>" placeholder="<?php esc_attr_e( 'e.g., 15000', 'case-study-manager' ); ?>" step="0.01" min="0" inputmode="decimal" />
		</div>

		<p class="csm-metabox-help">
			<?php esc_html_e( 'Meta keys:', 'case-study-manager' ); ?>
			<code><?php echo esc_html( self::META_LOCATION_CITY ); ?></code>,
			<code><?php echo esc_html( self::META_LOCATION_STATE ); ?></code>,
			<code><?php echo esc_html( self::META_CASE_TYPE ); ?></code>,
			<code><?php echo esc_html( self::META_REPAIR_AMOUNT ); ?></code>
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

		if ( ! isset( $_POST[ self::NONCE_NAME ] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ self::NONCE_NAME ] ) ), self::NONCE_ACTION ) ) {
			return;
		}

		if ( ! isset( $post->post_type ) || self::POST_TYPE !== $post->post_type ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$clean_text = static function ( string $key ): string {
			return isset( $_POST[ $key ] ) ? sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) : '';
		};

		update_post_meta( $post_id, self::META_LOCATION_CITY, $clean_text( 'ra_location_city' ) );
		update_post_meta( $post_id, self::META_LOCATION_STATE, $clean_text( 'ra_location_state' ) );
		update_post_meta( $post_id, self::META_CASE_TYPE, $clean_text( 'ra_case_type' ) );

		$repair_raw = isset( $_POST['ra_repair_amount'] ) ? wp_unslash( $_POST['ra_repair_amount'] ) : '';
		$repair_raw = is_string( $repair_raw ) ? trim( $repair_raw ) : '';
		if ( '' === $repair_raw ) {
			update_post_meta( $post_id, self::META_REPAIR_AMOUNT, '' );
		} else {
			update_post_meta( $post_id, self::META_REPAIR_AMOUNT, floatval( $repair_raw ) );
		}

		$city  = trim( (string) get_post_meta( $post_id, self::META_LOCATION_CITY, true ) );
		$state = trim( (string) get_post_meta( $post_id, self::META_LOCATION_STATE, true ) );

		$location_full = $city;
		if ( $city && $state ) {
			$location_full .= ', ';
		}
		$location_full .= $state;

		update_post_meta( $post_id, self::META_LOCATION_FULL, trim( $location_full ) );
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
			$city  = get_post_meta( $post_id, self::META_LOCATION_CITY, true );
			$state = get_post_meta( $post_id, self::META_LOCATION_STATE, true );

			$out = trim( $city . ( ( $city && $state ) ? ', ' : '' ) . $state );
			echo $out ? esc_html( $out ) : '&mdash;';
			return;
		}

		if ( 'ra_type' === $column ) {
			$type = get_post_meta( $post_id, self::META_CASE_TYPE, true );
			echo $type ? esc_html( $type ) : '&mdash;';
			return;
		}

		if ( 'ra_repair_amount' === $column ) {
			$amount = get_post_meta( $post_id, self::META_REPAIR_AMOUNT, true );
			if ( '' === $amount || null === $amount ) {
				echo '&mdash;';
				return;
			}
			echo esc_html( '$' . number_format_i18n( (float) $amount, 2 ) );
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
}
