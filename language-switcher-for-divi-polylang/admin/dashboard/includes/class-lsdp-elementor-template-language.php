<?php
/**
 * Assign Polylang default language to Elementor library templates.
 *
 * @package Language_Switcher_For_Elementor_Polylang
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles the Get Started notice and bulk language assignment for elementor_library posts.
 */
class LSDP_Elementor_Template_Language {

	/**
	 * Singleton instance.
	 *
	 * @var self|null
	 */
	private static $instance = null;

	/**
	 * Admin-post action name.
	 */
	const ACTION = 'lsdp_set_default_language_elementor_library';

	/**
	 * Nonce action name.
	 */
	const NONCE_ACTION = 'lsdp_set_default_language_elementor_library';

	/**
	 * Bootstrap hooks.
	 *
	 * @return self
	 */
	public static function init() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Register admin handlers.
	 */
	private function __construct() {
		add_action( 'admin_post_' . self::ACTION, array( $this, 'handle_assign_default_language' ) );
	}

	/**
	 * Whether Elementor + Polylang integration is available.
	 *
	 * @return bool
	 */
	public static function is_available() {
		return class_exists( 'LSDP_Common_Helpers' )
			&& LSDP_Common_Helpers::is_dependencies_active()
			&& LSDP_Common_Helpers::is_elementor_available()
			&& function_exists( 'pll_default_language' )
			&& function_exists( 'pll_set_post_language' )
			&& function_exists( 'pll_get_post_language' );
	}

	/**
	 * Whether unassigned Elementor templates exist.
	 *
	 * @return bool
	 */
	public static function should_show_notice() {
		return self::is_available() && self::count_unassigned_templates() > 0;
	}

	/**
	 * Count elementor_library posts without a Polylang language.
	 *
	 * @return int
	 */
	public static function count_unassigned_templates() {
		$count = 0;

		foreach ( self::get_elementor_library_post_ids() as $post_id ) {
			if ( ! pll_get_post_language( $post_id ) ) {
				++$count;
			}
		}

		return $count;
	}

	/**
	 * Render the Get Started notice markup.
	 *
	 * @return void
	 */
	public static function render_get_started_notice() {
		if ( ! self::should_show_notice() ) {
			return;
		}
		?>
		<div class="lsdp-gs-notice" id="lsdp-gs-elementor-notice" hidden>
			<div class="lsdp-gs-notice-main">
				<span class="lsdp-gs-notice-icon dashicons dashicons-info-outline" aria-hidden="true"></span>
				<p class="lsdp-gs-notice-text">
					<?php esc_html_e( 'Notice: Assign default language to pre-published Elementor Templates.', 'language-switcher-for-divi-polylang' ); ?>
				</p>
			</div>
			<div class="lsdp-gs-notice-actions">
				<form class="lsdp-gs-notice-apply-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="<?php echo esc_attr( self::ACTION ); ?>" />
					<?php wp_nonce_field( self::NONCE_ACTION, '_wpnonce_' . self::NONCE_ACTION ); ?>
					<button type="submit" class="button button-primary lsdp-gs-notice-apply">
						<?php esc_html_e( 'Apply Now', 'language-switcher-for-divi-polylang' ); ?>
					</button>
				</form>
			</div>
		</div>
		<?php
	}

	/**
	 * Assign default Polylang language to unassigned Elementor templates.
	 *
	 * @return void
	 */
	public function handle_assign_default_language() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to perform this action.', 'language-switcher-for-divi-polylang' ) );
		}

		check_admin_referer( self::NONCE_ACTION, '_wpnonce_' . self::NONCE_ACTION );

		$default_lang = pll_default_language( 'slug' );
		if ( empty( $default_lang ) ) {
			wp_safe_redirect( self::get_redirect_url() );
			exit;
		}

		foreach ( self::get_elementor_library_post_ids() as $post_id ) {
			if ( pll_get_post_language( $post_id ) ) {
				continue;
			}

			pll_set_post_language( $post_id, $default_lang );
		}

		wp_safe_redirect( self::get_redirect_url() );
		exit;
	}

	/**
	 * Get Elementor template post IDs.
	 *
	 * @return int[]
	 */
	private static function get_elementor_library_post_ids() {
		$query_args = array(
			'post_type'              => 'elementor_library',
			'post_status'            => array( 'publish', 'draft', 'pending', 'private', 'future' ),
			'posts_per_page'         => -1,
			'fields'                 => 'ids',
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'orderby'                => 'ID',
			'order'                  => 'ASC',
		);

		if ( function_exists( 'PLL' ) ) {
			$query_args['lang'] = '';
		}

		$post_ids = get_posts( $query_args );

		return is_array( $post_ids ) ? array_map( 'absint', $post_ids ) : array();
	}

	/**
	 * Redirect URL back to Get Started after assignment.
	 *
	 * @return string
	 */
	private static function get_redirect_url() {
		return add_query_arg(
			array(
				'page' => 'lsdp-get-started',
				'tab'  => 'add-to-your-pages',
			),
			admin_url( 'admin.php' )
		);
	}
}
