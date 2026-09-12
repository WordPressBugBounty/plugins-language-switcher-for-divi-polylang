<?php
namespace LSDP\feedback;
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Class for feedback from user before deactivate plugin.
 */
class LSDP_Feedback {

	/** Class for feedback.
		* Get file path.
		*
		* @var plugin_url
		*/
	private $plugin_url = __FILE__;
	/**
	 *
	 * Define plugin version.
	 *
	 * @var plugin_version
	 */
	private $plugin_version = LSDP;
	/**
	 *
	 * Define plugin slug.
	 *
	 * @var plugin_slug
	 */
	private $plugin_slug = 'language-switcher-for-divi-polylang';
	/**
	 *
	 * Define plugin name.
	 *
	 * @var plugin_name
	 */
	private $plugin_name = 'Language Switcher for Polylang';
	/**
	 *
	 * Define text domain for translation.
	 *
	 * @var text_domain
	 */
	private $text_domain = 'LSDP';
	/**
		*
		* Define feedback url for redirection.
		*
		* @var feedback_url
		*/
	private $feedback_url = 'https://feedback.coolplugins.net/wp-json/coolplugins-feedback/v1/feedback';
	/**
	 * Use this constructor to fire all actions and filters.
	 */
	public function __construct() {
		$this->plugin_url = plugin_dir_url( $this->plugin_url );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_feedback_scripts' ) );
		add_action( 'admin_head', array( $this, 'show_deactivate_feedback_popup' ) );
		add_action( 'wp_ajax_' . $this->text_domain . '_submit_deactivation_response', array( $this, 'submit_deactivation_response' ) );
	}

	/**
	 * Enqueue all scripts and styles to required page only.
	 */
	public function enqueue_feedback_scripts() {
		$screen = get_current_screen();
		if ( isset( $screen ) && 'plugins' === $screen->id ) {
			wp_enqueue_script( __NAMESPACE__ . 'feedback-script', $this->plugin_url . '/js/admin-feedback.js', array( 'jquery' ), $this->plugin_version, true );
			wp_enqueue_style( 'cool-plugins-feedback-style', $this->plugin_url . '/css/admin-feedback.css', null, $this->plugin_version );
		}
	}

	/**
	 * HTML for creating feedback popup form.
	 */
	public function show_deactivate_feedback_popup() {
		$screen = get_current_screen();
		if ( ! isset( $screen ) || 'plugins' !== $screen->id ) {
			return;
		}
		$deactivate_reasons = array(
			'didnt_work_as_expected'         => array(
				'title'             => esc_html__( 'The plugin didn\'t work as expected', 'language-switcher-for-divi-polylang' ),
				'input_placeholder' => esc_html__( 'What did you expect?', 'language-switcher-for-divi-polylang' ),
			),
			'found_a_better_plugin'          => array(
				'title'             => esc_html__( 'I found a better plugin', 'language-switcher-for-divi-polylang' ),
				'input_placeholder' => esc_html__( 'Please share which plugin', 'language-switcher-for-divi-polylang' ),
			),
			'couldnt_get_the_plugin_to_work' => array(
				'title'             => esc_html__( 'The plugin is not working', 'language-switcher-for-divi-polylang' ),
				'input_placeholder' => esc_html__( 'Please share your issue. So we can fix that for other users.', 'language-switcher-for-divi-polylang' ),
			),
			'temporary_deactivation'         => array(
				'title'             => esc_html__( 'It\'s a temporary deactivation', 'language-switcher-for-divi-polylang' ),
				'input_placeholder' => '',
			),
			'other'                          => array(
				'title'             => esc_html__( 'Other', 'language-switcher-for-divi-polylang' ),
				'input_placeholder' => esc_html__( 'Please share the reason', 'language-switcher-for-divi-polylang' ),
			),
		);

		?>
		<div id="cool-plugins-deactivate-feedback-dialog-wrapper" class="<?php echo esc_attr( $this->plugin_slug ); ?> hide-feedback-popup">

			<div class="cool-plugins-deactivation-response">
			<div id="cool-plugins-deactivate-feedback-dialog-header">
				<span id="cool-plugins-feedback-form-title"><?php echo esc_html__( 'Quick Feedback', 'language-switcher-for-divi-polylang' ); ?></span>
			</div>
			<div id="cool-plugins-loader-wrapper">
				<div class="cool-plugins-loader-container">
					<img class="cool-plugins-preloader" src="<?php echo esc_url( $this->plugin_url ); ?>images/cool-plugins-preloader.gif">
				</div>
			</div>
			<div id="cool-plugins-form-wrapper" class="cool-plugins-form-wrapper-cls">
			<form id="cool-plugins-deactivate-feedback-dialog-form" method="post">
				<?php
				wp_nonce_field( '_cool-plugins_deactivate_feedback_nonce' );
				?>
				<input type="hidden" name="action" value="cool-plugins_deactivate_feedback" />
				<div id="cool-plugins-deactivate-feedback-dialog-form-caption"><?php echo esc_html__( 'If you have a moment, please share why you are deactivating this plugin.', 'language-switcher-for-divi-polylang' ); ?></div>
				<div id="cool-plugins-deactivate-feedback-dialog-form-body">
					<?php foreach ( $deactivate_reasons as $reason_key => $reason ) : ?>
						<div class="cool-plugins-deactivate-feedback-dialog-input-wrapper">
							<input id="cool-plugins-deactivate-feedback-<?php echo esc_attr( $reason_key ); ?>" class="cool-plugins-deactivate-feedback-dialog-input" type="radio" name="reason_key" value="<?php echo esc_attr( $reason_key ); ?>" />
							<label for="cool-plugins-deactivate-feedback-<?php echo esc_attr( $reason_key ); ?>" class="cool-plugins-deactivate-feedback-dialog-label"><?php echo esc_html( $reason['title'] ); ?></label>
							<?php if ( ! empty( $reason['input_placeholder'] ) ) : ?>
								<textarea class="cool-plugins-feedback-text" type="textarea" name="reason_<?php echo esc_attr( $reason_key ); ?>" placeholder="<?php echo esc_attr( $reason['input_placeholder'] ); ?>"></textarea>
							<?php endif; ?>
							<?php if ( ! empty( $reason['alert'] ) ) : ?>
								<div class="cool-plugins-feedback-text"><?php echo esc_html( $reason['alert'] ); ?></div>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
					<input class="cool-plugins-GDPR-data-notice" id="cool-plugins-GDPR-data-notice" type="checkbox"><label for="cool-plugins-GDPR-data-notice"><?php echo esc_html__( 'I agree to share anonymous usage data and basic site details (such as server, PHP, and WordPress versions) to support Language Switcher for Polylang improvement efforts. Additionally, I allow Cool Plugins to store all information provided through this form and to respond to my inquiry.', 'language-switcher-for-divi-polylang' ); ?></label>
				</div>
				<div class="cool-plugin-popup-button-wrapper">
					<a class="cool-plugins-button button-deactivate" id="cool-plugin-submitNdeactivate">Submit and Deactivate</a>
					<a class="cool-plugins-button" id="cool-plugin-skipNdeactivate">Skip and Deactivate</a>
				</div>
			</form>
			</div>
			</div>
		</div>
		<?php
	}

	public function lsdp_get_user_info() {
		global $wpdb;
		$server_info = array(
			'server_software'        => isset( $_SERVER['SERVER_SOFTWARE'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) ) : 'N/A',
			'mysql_version'          => $wpdb ? sanitize_text_field( $wpdb->db_version() ) : 'N/A',
			'php_version'            => sanitize_text_field( phpversion() ),
			'wp_version'             => sanitize_text_field( get_bloginfo( 'version' ) ),
			'wp_debug'               => sanitize_text_field( defined( 'WP_DEBUG' ) && WP_DEBUG ? 'Enabled' : 'Disabled' ),
			'wp_memory_limit'        => sanitize_text_field( ini_get( 'memory_limit' ) ),
			'wp_max_upload_size'     => sanitize_text_field( ini_get( 'upload_max_filesize' ) ),
			'wp_permalink_structure' => sanitize_text_field( get_option( 'permalink_structure', 'Default' ) ),
			'wp_multisite'           => sanitize_text_field( is_multisite() ? 'Enabled' : 'Disabled' ),
			'wp_language'            => sanitize_text_field( get_option( 'WPLANG', get_locale() ) ? get_option( 'WPLANG', get_locale() ) : get_locale() ),
			'wp_prefix'              => sanitize_key( $wpdb->prefix ), // Sanitizing database prefix
		);
		$theme_data  = array(
			'name'      => sanitize_text_field( wp_get_theme()->get( 'Name' ) ),
			'version'   => sanitize_text_field( wp_get_theme()->get( 'Version' ) ),
			'theme_uri' => esc_url( wp_get_theme()->get( 'ThemeURI' ) ),
		);
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		$plugin_data = array_map(
			function ( $plugin ) {
				$plugin_info = get_plugin_data( WP_PLUGIN_DIR . '/' . sanitize_text_field( $plugin ) );
				return array(
					'name'       => sanitize_text_field( $plugin_info['Name'] ),
					'version'    => sanitize_text_field( $plugin_info['Version'] ),
					'plugin_uri' => esc_url( $plugin_info['PluginURI'] ),
				);
			},
			get_option( 'active_plugins', array() )
		);
		return array(
			'server_info'   => $server_info,
			'extra_details' => array(
				'wp_theme'       => $theme_data,
				'active_plugins' => $plugin_data,
			),
		);
	}

	/**
	 * Function to submit feedback rom user.
	 */

	public function submit_deactivation_response() {
		if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), '_cool-plugins_deactivate_feedback_nonce' ) ) {
			wp_send_json_error();
		} else {
			$reason             = isset( $_POST['reason'] ) ? sanitize_key( $_POST['reason'] ) : '';
			$deactivate_reasons = array(
				'didnt_work_as_expected'         => array(
					'title'             => esc_html__( 'The plugin didn\'t work as expected', 'language-switcher-for-divi-polylang' ),
					'input_placeholder' => esc_html__( 'What did you expect?', 'language-switcher-for-divi-polylang' ),
				),
				'found_a_better_plugin'          => array(
					'title'             => esc_html__( 'I found a better plugin', 'language-switcher-for-divi-polylang' ),
					'input_placeholder' => esc_html__( 'Please share which plugin', 'language-switcher-for-divi-polylang' ),
				),
				'couldnt_get_the_plugin_to_work' => array(
					'title'             => esc_html__( 'The plugin is not working', 'language-switcher-for-divi-polylang' ),
					'input_placeholder' => esc_html__( 'Please share your issue. So we can fix that for other users.', 'language-switcher-for-divi-polylang' ),
				),
				'temporary_deactivation'         => array(
					'title'             => esc_html__( 'It\'s a temporary deactivation', 'language-switcher-for-divi-polylang' ),
					'input_placeholder' => '',
				),
				'other'                          => array(
					'title'             => esc_html__( 'Other', 'language-switcher-for-divi-polylang' ),
					'input_placeholder' => esc_html__( 'Please share the reason', 'language-switcher-for-divi-polylang' ),
				),
			);

			$deativation_reason = array_key_exists( $reason, $deactivate_reasons ) ? $reason : 'other';

			$plugin_initial    = get_option( 'lsdp_initial_save_version' );
			$sanitized_message = isset( $_POST['message'] ) && '' !== sanitize_text_field( wp_unslash( $_POST['message'] ) ) ? sanitize_text_field( wp_unslash( $_POST['message'] ) ) : 'N/A';
			$admin_email       = sanitize_email( get_option( 'admin_email' ) );
			$site_url          = get_site_url();
			$install_date      = get_option( 'lsdp_install_date' );
			$uni_id            = '40';
			$site_id           = $site_url . '-' . $install_date . '-' . $uni_id;
			$response          = wp_remote_post(
				$this->feedback_url,
				array(
					'timeout' => 30,
					'body'    => array(
						'server_info'    => serialize( $this->lsdp_get_user_info()['server_info'] ),
						'extra_details'  => serialize( $this->lsdp_get_user_info()['extra_details'] ),
						'plugin_version' => $this->plugin_version,
						'plugin_name'    => $this->plugin_name,
						'plugin_initial' => isset( $plugin_initial ) ? sanitize_text_field( $plugin_initial ) : 'N/A',
						'reason'         => $deativation_reason,
						'review'         => $sanitized_message,
						'email'          => $admin_email,
						'domain'         => $site_url,
						'site_id'        => md5( $site_id ),
					),
				)
			);
			die( json_encode( array( 'response' => $response ) ) );
		}
	}
}
new LSDP_Feedback();
