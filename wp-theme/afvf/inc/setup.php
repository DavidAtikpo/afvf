<?php
/**
 * Theme setup.
 *
 * @package AFVF
 */

defined( 'ABSPATH' ) || exit;

function afvf_setup() {
	load_theme_textdomain( 'afvf', get_template_directory() . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support(
		'html5',
		array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' )
	);
	add_theme_support( 'custom-logo', array( 'height' => 320, 'width' => 320, 'flex-height' => true, 'flex-width' => true ) );
	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'editor-styles' );

	register_nav_menus(
		array(
			'primary' => __( 'Menu principal', 'afvf' ),
			'footer'  => __( 'Liens rapides (pied de page)', 'afvf' ),
		)
	);
}
add_action( 'after_setup_theme', 'afvf_setup' );

function afvf_flush_rewrite_on_switch() {
	flush_rewrite_rules();
}
add_action( 'after_switch_theme', 'afvf_flush_rewrite_on_switch' );

function afvf_content_width() {
	$GLOBALS['content_width'] = 1120;
}
add_action( 'after_setup_theme', 'afvf_content_width', 0 );


function afvf_enqueue_assets() {
	wp_enqueue_style(
		'afvf-fonts',
		'https://fonts.googleapis.com/css2?family=Anton&family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&family=Poppins:wght@400;500;600;700&family=Montserrat:wght@400;500;600;700&family=Oswald:wght@500;600;700&display=swap',
		array(),
		null
	);
	wp_enqueue_style(
		'afvf-main',
		get_template_directory_uri() . '/assets/css/main.css',
		array( 'afvf-fonts', 'wp-block-library', 'global-styles' ),
		'1.9.154'
	);
	wp_enqueue_script( 'afvf-main', get_template_directory_uri() . '/assets/js/main.js', array(), '1.9.154', true );
	wp_localize_script( 'afvf-main', 'afvfAjax', array( 'url' => admin_url( 'admin-ajax.php' ) ) );
}
add_action( 'wp_enqueue_scripts', 'afvf_enqueue_assets' );

function afvf_editor_styles() {
	add_editor_style(
		array(
			'assets/css/main.css',
			'assets/css/editor-hero-card.css',
			'assets/css/banner-canvas.css',
		)
	);
}
add_action( 'after_setup_theme', 'afvf_editor_styles' );

/**
 * URL de l’icône du site (favicon) — défaut thème si rien dans Personnaliser.
 */
function afvf_default_site_icon_url() {
	$files = array('logo-afvf.png' );
	foreach ( $files as $file ) {
		$path = get_template_directory() . '/assets/images/' . $file;
		if ( file_exists( $path ) ) {
			return get_template_directory_uri() . '/assets/images/' . $file;
		}
	}
	return '';
}

/**
 * Favicon dans l’onglet (site public + admin + page de connexion).
 */
function afvf_output_site_icon_fallback() {
	if ( has_site_icon() ) {
		return;
	}
	$icon = afvf_default_site_icon_url();
	if ( ! $icon ) {
		return;
	}
	$icon = add_query_arg( 'ver', '1.9.154', $icon );
	echo '<link rel="icon" href="' . esc_url( $icon ) . '" sizes="32x32">' . "\n";
	echo '<link rel="icon" href="' . esc_url( $icon ) . '" sizes="192x192">' . "\n";
	echo '<link rel="apple-touch-icon" href="' . esc_url( $icon ) . '">' . "\n";
}

add_action( 'wp_head', 'afvf_output_site_icon_fallback', 100 );
add_action( 'admin_head', 'afvf_output_site_icon_fallback', 99 );
add_action( 'login_head', 'afvf_output_site_icon_fallback', 99 );
