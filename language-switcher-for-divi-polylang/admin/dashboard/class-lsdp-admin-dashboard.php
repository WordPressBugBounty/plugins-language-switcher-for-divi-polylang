<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin dashboard for Language Switcher settings.
 */
class LSDP_Admin_Dashboard {
	/**
	 * Singleton instance.
	 *
	 * @var LSDP_Admin_Dashboard|null
	*/
	private static $instance = null;

	/**
	 * Dashboard includes directory.
	 *
	 * @var string
	 */
	private $addon_dir = __DIR__;

	/**
	 * Get singleton instance.
	 *
	 * @return LSDP_Admin_Dashboard
	 */
	public static function init() {
		if ( empty( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Register dashboard hooks.
	 */
	public function register_dashboard() {
		add_action( 'admin_menu', array( $this, 'register_dashboard_page' ), 10 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_required_scripts' ) );
		add_action( 'in_admin_header', array( $this, 'suppress_foreign_admin_notices' ), 1000 );
		add_action( 'wp_ajax_lsdp_save_preferred_builder', array( $this, 'ajax_save_preferred_builder' ) );

		if ( class_exists( 'LSDP_Common_Helpers' ) && LSDP_Common_Helpers::is_elementor_available() ) {
			require_once $this->addon_dir . '/includes/class-lsdp-elementor-template-language.php';
			LSDP_Elementor_Template_Language::init();
		}
	}

	/**
	 * Whether the current request is rendering this plugin dashboard.
	 *
	 * @return bool
	 */
	private function is_dashboard_page() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- GET used only for page detection.
		$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';

		return 'lsdp-get-started' === $page;
	}

	/**
	 * Remove third-party plugin and theme admin notices on this dashboard.
	 */
	public function suppress_foreign_admin_notices() {
		if ( ! $this->is_dashboard_page() ) {
			return;
		}

		remove_all_actions( 'admin_notices' );
		remove_all_actions( 'all_admin_notices' );
		remove_all_actions( 'user_admin_notices' );
	}

	/**
	 * Register dashboard submenu page.
	 */
	public function register_dashboard_page() {
				add_submenu_page(
					'mlang',
					__( 'Language Switcher', 'language-switcher-for-divi-polylang' ),
					__( 'Language Switcher', 'language-switcher-for-divi-polylang' ),
					'manage_options',
					'lsdp-get-started',
					array( $this, 'display_plugin_admin_dashboard' )
				);
	}

			/**
	 * Render dashboard page.
	 */
	public function display_plugin_admin_dashboard() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- GET used only for tab display.
		$current_tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'add-to-your-pages';
		$page_url    = admin_url( 'admin.php?page=lsdp-get-started' );
		$logo_url    = plugin_dir_url( __FILE__ ) . 'assets/images/language-switcher-for-elementor-polylang.svg';

		echo '<div class="wrap lsdp-dashboard-wrap">';

		echo '<div class="lsdp-dashboard-header">';
		echo '<div class="lsdp-header-content">';
		echo '<div class="lsdp-header-logo">';
		echo '<img src="' . esc_url( $logo_url ) . '" alt="" />';
		echo '<h1 class="lsdp-header-title">' . esc_html__( 'Language Switcher for Polylang', 'language-switcher-for-divi-polylang' ) . '</h1>';
		echo '</div>';
		echo '<div class="lsdp-header-actions">';
		echo '<a href="' . esc_url( 'https://wordpress.org/support/plugin/language-switcher-for-divi-polylang/#new-topic-0' ) . '" class="button button-secondary lsdp-header-btn lsdp-header-btn-support" target="_blank" rel="noopener noreferrer" title="' . esc_attr__( 'Get Support', 'language-switcher-for-divi-polylang' ) . '"><span class="dashicons dashicons-editor-help lsdp-header-btn-question-icon" aria-hidden="true"></span><span class="lsdp-header-btn-label">' . esc_html__( 'Get Support', 'language-switcher-for-divi-polylang' ) . '</span></a>';
		echo '<a href="' . esc_url( 'https://docs.coolplugins.net/doc/language-switcher-for-elementor-polylang/?utm_source=lsdp_plugin&utm_medium=inside&utm_campaign=docs&utm_content=dashboard_header' ) . '" class="button button-secondary lsdp-header-btn" target="_blank" rel="noopener noreferrer" title="' . esc_attr__( 'Documentation', 'language-switcher-for-divi-polylang' ) . '"><span class="dashicons dashicons-book" aria-hidden="true"></span><span class="lsdp-header-btn-label">' . esc_html__( 'Documentation', 'language-switcher-for-divi-polylang' ) . '</span></a>';
				echo '</div>';
				echo '</div>';
				echo '</div>';

		echo '<h2 class="nav-tab-wrapper">';
		echo '<a href="' . esc_url( add_query_arg( 'tab', 'add-to-your-pages', $page_url ) ) . '" class="nav-tab' . ( 'add-to-your-pages' === $current_tab ? ' nav-tab-active' : '' ) . '">' . esc_html__( 'Add to Your Pages', 'language-switcher-for-divi-polylang' ) . '</a>';
		echo '<a href="' . esc_url( add_query_arg( 'tab', 'floating-switcher', $page_url ) ) . '" class="nav-tab' . ( 'floating-switcher' === $current_tab ? ' nav-tab-active' : '' ) . '">' . esc_html__( 'Floating Switcher', 'language-switcher-for-divi-polylang' ) . '</a>';
		echo '</h2>';

		echo '<div class="lsdp-tab-content-wrapper">';
		if ( 'floating-switcher' === $current_tab ) {
			$this->floating_switcher_content();
		} else {
			$this->get_started_content();
		}
		echo '</div>';

		echo '</div>';
	}

	/**
	 * Render floating switcher tab content.
	 */
	public function floating_switcher_content() {
		$lsdp_languages = function_exists( 'pll_languages_list' ) ? pll_languages_list() : array();

		if ( empty( $lsdp_languages ) ) {
			echo '<div class="notice notice-warning"><p>';
			echo '<strong>' . esc_html__( 'No languages configured!', 'language-switcher-for-divi-polylang' ) . '</strong><br>';
			echo esc_html__( 'Please configure at least two languages in Polylang settings.', 'language-switcher-for-divi-polylang' );
			echo '</p></div>';
		}

		echo '<div id="lsdp-floater-app-root"></div>';
	}

	/**
	 * Render get started tab content.
	 */
	public function get_started_content() {
		require_once $this->addon_dir . '/includes/autopoly-promo.php';
		require $this->addon_dir . '/includes/get-started-content.php';
	}

	/**
	 * Builder guides for the Get Started tab.
	 *
	 * @return array
	 */
	private function get_started_builder_data() {
		$embed_urls = array(
			'elementor'         => 'https://www.youtube.com/embed/oRp8vmrU8yM?start=110',
			'elementor_syncing' => 'https://www.youtube.com/embed/oRp8vmrU8yM?start=253',
			'gutenberg'         => 'https://www.youtube.com/embed/oRp8vmrU8yM?start=365',
			'divi'              => 'https://www.youtube.com/embed/oRp8vmrU8yM?start=447',
		);

		return array(
			'elementor' => array(
				'guideTitle' => __( 'Elementor Language Switcher', 'language-switcher-for-divi-polylang' ),
				'guideSub'   => __( 'Add a customizable Language Switcher widget to your Elementor website.', 'language-switcher-for-divi-polylang' ),
				'tabs'       => array(
					array(
						'id'       => 'language-switcher',
						'label'    => __( 'Language Switcher', 'language-switcher-for-divi-polylang' ),
						'icon'     => 'dashicons-translation',
						'embedUrl' => $embed_urls['elementor'],
						'items'    => array(
							__( 'Edit the header template with Elementor or create a new one.', 'language-switcher-for-divi-polylang' ),
							__( 'Drag and drop the Language Switcher widget into your header.', 'language-switcher-for-divi-polylang' ),
							__( 'Configure language display (names, flags, or both) and customize the switcher\'s appearance (color, style, typography, and more).', 'language-switcher-for-divi-polylang' ),
							__( 'Save your changes and preview the language switcher on the frontend.', 'language-switcher-for-divi-polylang' ),
						),
					),
					array(
						'id'       => 'template-syncing',
						'label'    => __( 'Translate & Sync Elementor Templates', 'language-switcher-for-divi-polylang' ),
						'icon'     => 'dashicons-update',
						'embedUrl' => $embed_urls['elementor_syncing'],
						'items'    => array(
							__( 'Automatically assign languages to your existing Elementor templates by clicking the Apply Now button from the plugin dashboard.', 'language-switcher-for-divi-polylang' ),
							__( 'Next, create translated versions of your templates using the Polylang language options.', 'language-switcher-for-divi-polylang' ),
							__( 'Translate and save each template. The translated templates are automatically linked to their originals.', 'language-switcher-for-divi-polylang' ),
							__( 'Display conditions are synced automatically, ensuring the correct translated template is shown on each language version of your website.', 'language-switcher-for-divi-polylang' ),
						),
					),
				),
				'embedUrl'   => $embed_urls['elementor'],
			),
			'gutenberg' => array(
				'guideTitle'    => __( 'Gutenberg Language Switcher', 'language-switcher-for-divi-polylang' ),
				'guideSub'      => __( 'Add a language switcher anywhere on your website using the Gutenberg block editor.', 'language-switcher-for-divi-polylang' ),
				'overviewTitle' => __( 'Quick Overview', 'language-switcher-for-divi-polylang' ),
				'overviewItems' => array(
					__( 'Insert the Language Switcher block into any page or post.', 'language-switcher-for-divi-polylang' ),
					__( 'Choose how languages are displayed (flags, names, or both).', 'language-switcher-for-divi-polylang' ),
					__( 'Customize alignment, styling, and switcher behavior.', 'language-switcher-for-divi-polylang' ),
					__( 'Publish the page to make the language switcher available on your site.', 'language-switcher-for-divi-polylang' ),
				),
				'embedUrl'      => $embed_urls['gutenberg'],
			),
			'divi'      => array(
				'guideTitle'    => __( 'Divi Language Switcher', 'language-switcher-for-divi-polylang' ),
				'guideSub'      => __( 'Add a customizable Language Switcher module to your multilingual Divi website.', 'language-switcher-for-divi-polylang' ),
				'overviewTitle' => __( 'Quick Overview', 'language-switcher-for-divi-polylang' ),
				'overviewItems' => array(
					__( 'Navigate to Divi Theme Builder, add a new or edit an existing header template.', 'language-switcher-for-divi-polylang' ),
					__( 'Add the Language Switcher module where you want it to appear.', 'language-switcher-for-divi-polylang' ),
					__( 'Configure switcher display settings (names, flags, or both) and customize the switcher\'s colors, typography, spacing, and layout.', 'language-switcher-for-divi-polylang' ),
					__( 'Save your changes and preview the header on the frontend.', 'language-switcher-for-divi-polylang' ),
				),
				'embedUrl'      => $embed_urls['divi'],
			),
		);
	}

	/**
	 * Enqueue dashboard styles and Get Started assets.
	 *
	 * @param string $hook Current admin page hook suffix.
	 */
	public function enqueue_required_scripts( $hook ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- GET used only for conditional asset loading.
		$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- GET used only for conditional asset loading.
		$tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'add-to-your-pages';

		if ( 'lsdp-get-started' !== $page && false === strpos( $hook, 'lsdp-get-started' ) ) {
				return;
		}

		wp_enqueue_style(
			'cool-lsdp-plugins-polylang-addon',
			plugin_dir_url( __FILE__ ) . 'assets/css/styles.css',
			array( 'dashicons' ),
			LSDP,
			'all'
		);

		require_once $this->addon_dir . '/includes/autopoly-promo.php';
		lsdp_enqueue_autopoly_promo_script();

		if ( 'floating-switcher' === $tab ) {
			return;
		}

		wp_enqueue_script(
			'lsdp-get-started',
			plugin_dir_url( __FILE__ ) . 'assets/js/get-started.js',
			array( 'lsdp-autopoly-promo' ),
			LSDP,
			true
		);

		$preferred_builder = $this->get_preferred_builder();

		wp_localize_script(
			'lsdp-get-started',
			'lsdpGetStarted',
			array(
				'builders'         => $this->get_started_builder_data(),
				'ajaxUrl'          => admin_url( 'admin-ajax.php' ),
				'nonce'            => wp_create_nonce( 'lsdp_save_preferred_builder' ),
				'preferredBuilder' => $preferred_builder,
				'restoreContent'   => (bool) $preferred_builder,
			)
		);
	}

	/**
	 * Available builders based on installed Elementor / Divi.
	 *
	 * @return string[]
	 */
	public function get_available_builders() {
		$builders = array( 'gutenberg' );

		if ( class_exists( 'LSDP_Common_Helpers' ) && LSDP_Common_Helpers::lsdp_is_plugin_active( 'elementor/elementor.php' ) ) {
			$builders[] = 'elementor';
		}

		if ( $this->is_divi_available() ) {
			$builders[] = 'divi';
		}

		return $builders;
	}

	/**
	 * Whether Divi theme or Divi Builder is available.
	 *
	 * @return bool
	 */
	public function is_divi_available() {
		return ( class_exists( 'LSDP_Common_Helpers' ) && LSDP_Common_Helpers::lsdp_is_plugin_active( 'divi-builder/divi-builder.php' ) )
			|| ( function_exists( 'wp_get_theme' ) && 'Divi' === wp_get_theme()->get_template() )
			|| defined( 'ET_BUILDER_THEME' )
			|| defined( 'ET_BUILDER_PLUGIN_ACTIVE' );
	}

	/**
	 * Saved preferred builder if still available, otherwise empty string.
	 *
	 * @return string
	 */
	public function get_preferred_builder() {
		$saved     = get_option( 'lsdp_preferred_builder', '' );
		$saved     = is_string( $saved ) ? sanitize_key( $saved ) : '';
		$available = $this->get_available_builders();

		if ( $saved && in_array( $saved, $available, true ) ) {
			return $saved;
		}

		return '';
	}

	/**
	 * AJAX: persist preferred Get Started builder and usage counts.
	 */
	public function ajax_save_preferred_builder() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => 'forbidden' ), 403 );
		}

		check_ajax_referer( 'lsdp_save_preferred_builder', 'nonce' );

		$builder   = isset( $_POST['builder'] ) ? sanitize_key( wp_unslash( $_POST['builder'] ) ) : '';
		$available = $this->get_available_builders();

		if ( ! in_array( $builder, $available, true ) ) {
			wp_send_json_error( array( 'message' => 'invalid_builder' ), 400 );
		}

		update_option( 'lsdp_preferred_builder', $builder, false );

		$counts = get_option( 'lsdp_builder_usage_counts', array() );
		if ( ! is_array( $counts ) ) {
			$counts = array();
		}
		$counts[ $builder ] = isset( $counts[ $builder ] ) ? absint( $counts[ $builder ] ) + 1 : 1;
		update_option( 'lsdp_builder_usage_counts', $counts, false );

		wp_send_json_success(
			array(
				'builder' => $builder,
				'counts'  => $counts,
			)
		);
	}
}

	/**
 * Initialize the dashboard.
 */
function lsdp_register_admin_dashboard() {

	$page = LSDP_Admin_Dashboard::init();
	$page->register_dashboard();
}
