<?php
/**
 * Vidéo de fond pour le bloc « Couverture » (hero d’accueil, bannières…).
 *
 * Dans l’éditeur : sélectionner la Couverture → bouton « Vidéo de fond » dans la barre
 * du bloc, ou panneau « Vidéo de fond » dans la colonne de droite. Fonctionne même si la
 * Couverture n’a aucune image (cas du motif « Hero fond fixe »).
 *
 * @package AFVF
 */

defined( 'ABSPATH' ) || exit;

function afvf_cover_video_editor_script() {
	wp_enqueue_script(
		'afvf-cover-video-editor',
		get_template_directory_uri() . '/assets/js/cover-video-editor.js',
		array( 'wp-hooks', 'wp-element', 'wp-compose', 'wp-block-editor', 'wp-components', 'wp-i18n' ),
		wp_get_theme()->get( 'Version' ),
		true
	);
}
add_action( 'enqueue_block_editor_assets', 'afvf_cover_video_editor_script' );

/**
 * Ajoute la balise <video> dans la Couverture sur le site public.
 *
 * @param string $content Rendu HTML du bloc.
 * @param array  $block   Bloc analysé.
 * @return string
 */
function afvf_cover_video_render( $content, $block ) {
	$url = isset( $block['attrs']['afvfVideoUrl'] ) ? (string) $block['attrs']['afvfVideoUrl'] : '';
	if ( '' === $url || false !== strpos( $content, 'wp-block-cover__video-background' ) ) {
		return $content;
	}

	$video = sprintf(
		'<video class="wp-block-cover__video-background intrinsic-ignore afvf-bg-video" autoplay muted loop playsinline preload="auto" aria-hidden="true" tabindex="-1" src="%s" data-object-fit="cover"></video>',
		esc_url( $url )
	);

	$marker = '<div class="wp-block-cover__inner-container';
	$pos    = strpos( $content, $marker );
	if ( false === $pos ) {
		return $content;
	}
	return substr_replace( $content, $video, $pos, 0 );
}
add_filter( 'render_block_core/cover', 'afvf_cover_video_render', 10, 2 );
