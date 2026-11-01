<?php
/**
 * AFVF theme bootstrap.
 *
 * @package AFVF
 */

defined( 'ABSPATH' ) || exit;

require get_template_directory() . '/inc/setup.php';
require get_template_directory() . '/inc/widgets.php';
require get_template_directory() . '/inc/customizer.php';
require get_template_directory() . '/inc/ker-aicha.php';
require get_template_directory() . '/inc/block-patterns.php';
require get_template_directory() . '/inc/block-patterns-ker-aicha.php';
require get_template_directory() . '/inc/block-styles.php';
require get_template_directory() . '/inc/banner-canvas.php';
require get_template_directory() . '/inc/nav-fallback.php';
require get_template_directory() . '/inc/newsletter.php';
require get_template_directory() . '/inc/contact-form.php';
require get_template_directory() . '/inc/events.php';
require get_template_directory() . '/inc/news.php';
require get_template_directory() . '/inc/services.php';
require get_template_directory() . '/inc/cover-video.php';
require get_template_directory() . '/inc/chat.php';
if ( is_admin() ) {
	require get_template_directory() . '/inc/newsletter-admin.php';
	require get_template_directory() . '/inc/contact-admin.php';
	require get_template_directory() . '/inc/newsletter-send-admin.php';
	require get_template_directory() . '/inc/chat-admin.php';
}

/**
 * Excerpt length for cards.
 */
function afvf_excerpt_length( $length ) {
	return is_admin() ? $length : 28;
}
add_filter( 'excerpt_length', 'afvf_excerpt_length' );

/**
 * Fond de page blanc : réglage Personnaliser, modèle « fond blanc » ou motifs Wix (Vision, cartes…).
 *
 * @return bool
 */
function afvf_page_wants_white_bg() {
	if ( get_theme_mod( 'afvf_bg_white', false ) ) {
		return true;
	}
	if ( ! is_singular() ) {
		return false;
	}
	if ( is_page_template( 'page-templates/template-white-page.php' ) ) {
		return true;
	}
	if ( is_singular( 'post' ) || is_singular( 'afvf_service' ) ) {
		return true;
	}
	$post = get_queried_object();
	if ( ! $post instanceof WP_Post ) {
		return false;
	}
	if ( ! is_front_page() && ( str_contains( $post->post_content, '[afvf_actualites' ) || str_contains( $post->post_content, '[afvf_services' ) ) ) {
		return true;
	}
	foreach ( array( 'afvf-vision-offset', 'afvf-photo-text-list', 'afvf-hover-split-grid', 'afvf-lift-grid', 'afvf-zigzag', 'afvf-former-hero', 'afvf-course-rise-grid' ) as $needle ) {
		if ( str_contains( $post->post_content, $needle ) ) {
			return true;
		}
	}
	return false;
}

/**
 * Add body class on front page.
 *
 * @param string[] $classes Body classes.
 * @return string[]
 */
function afvf_body_classes( $classes ) {
	if ( is_front_page() ) {
		$classes[] = 'afvf-front';
	}

	$editor = function_exists( 'afvf_use_editor_layout' ) && afvf_use_editor_layout();

	if ( $editor && ( is_front_page() || is_page() || is_singular() ) ) {
		$classes[] = 'afvf-editor-layout';
		$classes[] = 'afvf-hero-under-header';
		$classes[] = 'has-hero-wix';
	} elseif ( ( is_page() || is_singular() ) && function_exists( 'afvf_use_editor_layout' ) && afvf_use_editor_layout() ) {
		// Même recette hero / bannière pleine largeur que dans l’éditeur (pages Kër Aïcha, etc.).
		$classes[] = 'afvf-hero-under-header';
		$classes[] = 'has-hero-wix';
	} elseif ( is_front_page() && ! $editor && (bool) get_theme_mod( 'afvf_show_hero', true ) ) {
		$classes[] = 'afvf-hero-under-header';
		$classes[] = 'has-hero-wix';
	}

	if ( ! is_admin() && afvf_page_wants_white_bg() ) {
		$classes[] = 'afvf-bg-white';
	}

	if ( ( is_page() || is_singular() ) && ! is_admin() ) {
		$post = get_queried_object();
		if ( $post instanceof WP_Post && str_contains( $post->post_content, 'afvf-ker-aicha' ) ) {
			$classes[] = 'afvf-ker-aicha-layout-page';
		}
	}

	return $classes;
}
add_filter( 'body_class', 'afvf_body_classes' );

/**
 * Barre sociale fixe (accueil) + chat (tout le site), hors admin.
 */
function afvf_render_site_overlays() {
	if ( is_admin() ) {
		return;
	}

	if ( is_front_page() ) {
		get_template_part( 'template-parts/social', 'floating-rail' );
	}

	if ( function_exists( 'afvf_site_chat_enabled' ) && afvf_site_chat_enabled() ) {
		get_template_part( 'template-parts/site', 'chat' );
		return;
	}

	$whatsapp = afvf_mod( 'afvf_whatsapp', 'https://wa.me/33626368994' );
	if ( $whatsapp ) {
		printf(
			'<a class="footer-chat-fab" href="%1$s" target="_blank" rel="noopener" aria-label="%2$s"></a>',
			esc_url( $whatsapp ),
			esc_attr__( 'Contact WhatsApp', 'afvf' )
		);
	}
}
add_action( 'wp_footer', 'afvf_render_site_overlays', 5 );
