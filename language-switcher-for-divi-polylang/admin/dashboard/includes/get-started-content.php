<?php
/**
 * Get Started tab content.
 *
 * @package Language_Switcher_For_Elementor_Polylang
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$assets_url        = LSDP_URL . 'assets/images/';
$builder_video_urls = array(
	'elementor' => 'https://www.youtube.com/embed/oRp8vmrU8yM?start=110',
	'gutenberg' => 'https://www.youtube.com/embed/oRp8vmrU8yM?start=365',
	'divi'      => 'https://www.youtube.com/embed/oRp8vmrU8yM?start=447',
);

$dashboard     = class_exists( 'LSDP_Admin_Dashboard' ) ? LSDP_Admin_Dashboard::init() : null;
$has_elementor = class_exists( 'LSDP_Common_Helpers' ) && LSDP_Common_Helpers::lsdp_is_plugin_active( 'elementor/elementor.php' );
$has_divi      = $dashboard ? $dashboard->is_divi_available() : (
	( class_exists( 'LSDP_Common_Helpers' ) && LSDP_Common_Helpers::lsdp_is_plugin_active( 'divi-builder/divi-builder.php' ) )
	|| ( function_exists( 'wp_get_theme' ) && 'Divi' === wp_get_theme()->get_template() )
	|| defined( 'ET_BUILDER_THEME' )
	|| defined( 'ET_BUILDER_PLUGIN_ACTIVE' )
);

$show_builder_picker = $has_elementor || $has_divi;
$saved_builder       = $dashboard ? $dashboard->get_preferred_builder() : '';

if ( $saved_builder ) {
	$default_builder = $saved_builder;
} elseif ( $has_elementor ) {
	$default_builder = 'elementor';
} elseif ( $has_divi ) {
	$default_builder = 'divi';
} else {
	$default_builder = 'gutenberg';
}

$video_url = isset( $builder_video_urls[ $default_builder ] ) ? $builder_video_urls[ $default_builder ] : $builder_video_urls['gutenberg'];

$builder_cards = array(
	'divi'      => array(
		'available'   => $has_divi,
		'title'       => __( 'Divi', 'language-switcher-for-divi-polylang' ),
		'description' => __( 'Use the Divi module to display the switcher.', 'language-switcher-for-divi-polylang' ),
		'icon'        => 'divi-icon.png',
	),
	'elementor' => array(
		'available'   => $has_elementor,
		'title'       => __( 'Elementor', 'language-switcher-for-divi-polylang' ),
		'description' => __( 'Use the widget to add the language switcher.', 'language-switcher-for-divi-polylang' ),
		'icon'        => 'elementor-icon.png',
	),
	'gutenberg' => array(
		'available'   => true,
		'title'       => __( 'Gutenberg', 'language-switcher-for-divi-polylang' ),
		'description' => __( 'Add the language switcher using a block.', 'language-switcher-for-divi-polylang' ),
		'icon'        => 'gutenberg-icon.png',
	),
);

// Put the selected builder first while preserving the normal order of the others.
$builder_order = array_unique( array( $default_builder, 'divi', 'elementor', 'gutenberg' ) );
?>
<div class="<?php echo esc_attr( 'lsdp-get-started-content' . ( $show_builder_picker ? '' : ' lsdp-gs-no-picker' ) ); ?>" id="lsdp-gs-wrap" data-default-builder="<?php echo esc_attr( $default_builder ); ?>">
	<?php if ( $show_builder_picker ) : ?>
	<div class="lsdp-gs-box">
		<div class="lsdp-gs-choose-heading">
		<h2><?php esc_html_e( 'Select the builder you use to add and manage the language switcher.', 'language-switcher-for-divi-polylang' ); ?></h2>
		<p><?php esc_html_e( 'Place the switcher inside your pages, headers, footers, templates, or navigation menus.', 'language-switcher-for-divi-polylang' ); ?></p>
		</div>

		<div class="lsdp-gs-builder-cards" role="radiogroup" aria-label="<?php echo esc_attr__( 'Choose your builder', 'language-switcher-for-divi-polylang' ); ?>">
			<?php foreach ( $builder_order as $builder_key ) : ?>
				<?php
				$builder_card = $builder_cards[ $builder_key ];
				if ( ! $builder_card['available'] ) {
					continue;
				}
				?>
			<button type="button" class="lsdp-gs-builder-card<?php echo $builder_key === $default_builder ? ' is-selected' : ''; ?>" data-builder="<?php echo esc_attr( $builder_key ); ?>" role="radio" aria-checked="<?php echo $builder_key === $default_builder ? 'true' : 'false'; ?>">
				<span class="lsdp-gs-builder-top">
					<span class="lsdp-gs-builder-icon-wrap" aria-hidden="true">
						<img class="lsdp-gs-builder-icon" src="<?php echo esc_url( $assets_url . $builder_card['icon'] ); ?>" alt="" />
					</span>
				</span>
				<span class="lsdp-gs-builder-title"><?php echo esc_html( $builder_card['title'] ); ?></span>
				<span class="lsdp-gs-builder-desc"><?php echo esc_html( $builder_card['description'] ); ?></span>
			</button>
			<?php endforeach; ?>
		</div>
	</div>

	<?php endif; ?>

	<div class="lsdp-gs-box lsdp-gs-content-panel">
		<div class="lsdp-gs-content-header">
			<h2 id="lsdp-gs-guide-title"></h2>
			<p class="lsdp-gs-sub" id="lsdp-gs-guide-sub"></p>
			<?php
			if ( class_exists( 'LSDP_Elementor_Template_Language' ) ) {
				LSDP_Elementor_Template_Language::render_get_started_notice();
			}
			?>
		</div>

		<div class="lsdp-gs-content-grid">
			<div class="lsdp-gs-video">
				<h3><?php esc_html_e( 'Watch how it works', 'language-switcher-for-divi-polylang' ); ?></h3>
				<div class="lsdp-video-container">
					<iframe
						id="lsdp-gs-video-iframe"
						width="100%"
						height="380"
						src="<?php echo esc_url( $video_url ); ?>"
						title="<?php echo esc_attr__( 'Language Switcher tutorial', 'language-switcher-for-divi-polylang' ); ?>"
						allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
						allowfullscreen>
					</iframe>
				</div>
			</div>
			<div class="lsdp-gs-guide" id="lsdp-gs-steps">
			</div>
		</div>
	</div>

	<footer class="lsdp-gs-footer">

		<div class="lsdp-gs-footer-card">
			<div class="lsdp-gs-footer-icon" aria-hidden="true">
				<span class="dashicons dashicons-editor-help"></span>
			</div>
			<h3><?php esc_html_e( 'Support', 'language-switcher-for-divi-polylang' ); ?></h3>
			<p><?php esc_html_e( 'Need help? Our team can assist with setup and troubleshooting.', 'language-switcher-for-divi-polylang' ); ?></p>
			<div class="lsdp-gs-footer-links">
				<a
					class="lsdp-gs-footer-btn"
					href="<?php echo esc_url( 'https://wordpress.org/support/plugin/language-switcher-for-divi-polylang/#new-topic-0' ); ?>"
					target="_blank"
					rel="noopener noreferrer"
				>
					<?php esc_html_e( 'Get Support', 'language-switcher-for-divi-polylang' ); ?>
					<span class="dashicons dashicons-arrow-right-alt2" aria-hidden="true"></span>
				</a>
			</div>
		</div>

		<?php lsdp_render_autopoly_promo( 'get_started' ); ?>

		<div class="lsdp-gs-footer-card">
			<div class="lsdp-gs-footer-icon" aria-hidden="true">
				<span class="dashicons dashicons-star-filled"></span>
			</div>
			<h3><?php esc_html_e( 'Your Feedback Matters', 'language-switcher-for-divi-polylang' ); ?></h3>
			<p><?php esc_html_e( 'If you\'re happy with the plugin, we\'d greatly appreciate a quick review. Your support helps us continue improving it.', 'language-switcher-for-divi-polylang' ); ?></p>
			<div class="lsdp-gs-footer-links">
				<a
					class="lsdp-gs-footer-btn"
					href="<?php echo esc_url( 'https://wordpress.org/support/plugin/language-switcher-for-divi-polylang/reviews/#new-post' ); ?>"
					target="_blank"
					rel="noopener noreferrer"
				>
					<?php esc_html_e( 'Leave a Review', 'language-switcher-for-divi-polylang' ); ?>
					<span class="dashicons dashicons-arrow-right-alt2" aria-hidden="true"></span>
				</a>
			</div>
		</div>
	</footer>
</div>
