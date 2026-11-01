<?php
/**
 * Styles de blocs Gutenberg (carte hero, position).
 *
 * @package AFVF
 */

defined( 'ABSPATH' ) || exit;

function afvf_register_block_styles() {
	if ( ! function_exists( 'register_block_style' ) ) {
		return;
	}

	$group_styles = array(
		'afvf-hero-card'        => __( 'Carte hero — centrée (défaut)', 'afvf' ),
		'afvf-hero-card-left'   => __( 'Carte hero — à gauche', 'afvf' ),
		'afvf-hero-card-right'  => __( 'Carte hero — à droite', 'afvf' ),
		'afvf-hero-card-narrow' => __( 'Carte hero — étroite', 'afvf' ),
		'afvf-hero-card-wide'   => __( 'Carte hero — large', 'afvf' ),
		'afvf-hero-card-custom' => __( 'Carte hero — personnaliser', 'afvf' ),
		'afvf-hero-card-square' => __( 'Carte hero — carrée', 'afvf' ),
		'afvf-hero-card-rounded' => __( 'Carte hero — coins arrondis', 'afvf' ),
	);

	foreach ( $group_styles as $name => $label ) {
		register_block_style(
			'core/group',
			array(
				'name'  => $name,
				'label' => $label,
			)
		);
	}

	$cover_styles = array(
		'afvf-content-top'    => __( 'Hero — contenu en haut', 'afvf' ),
		'afvf-content-bottom' => __( 'Hero — contenu en bas', 'afvf' ),
	);

	foreach ( $cover_styles as $name => $label ) {
		register_block_style(
			'core/cover',
			array(
				'name'  => $name,
				'label' => $label,
			)
		);
	}
}
add_action( 'init', 'afvf_register_block_styles' );
