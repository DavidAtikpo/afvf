<?php
/**
 * Menu par défaut tant que le menu WP n'est pas configuré.
 *
 * @package AFVF
 */

defined( 'ABSPATH' ) || exit;

function afvf_menu_fallback() {
	$items = array(
		array( __( 'Bienvenue', 'afvf' ), home_url( '/bienvenue/' ) ),
		array( __( 'Accueil', 'afvf' ), home_url( '/' ) ),
		array( __( "Besoin d'aide ?", 'afvf' ), afvf_help_url() ),
		array( __( 'A propos', 'afvf' ), home_url( '/a-propos/' ) ),
		array( __( 'Organisation', 'afvf' ), home_url( '/organisation/' ) ),
		array( __( 'Actus', 'afvf' ), get_permalink( get_option( 'page_for_posts' ) ) ?: home_url( '/actualites/' ) ),
		array( __( 'Assises Nationales', 'afvf' ), home_url( '/assises/' ) ),
		array( __( 'Entreprises & Institutions', 'afvf' ), home_url( '/mobiliser/' ) ),
		array( __( 'Reconstruction', 'afvf' ), home_url( '/reconstruction/' ) ),
		array( __( 'Notre Fondation', 'afvf' ), home_url( '/fondation/' ) ),
		array( __( 'Legs', 'afvf' ), home_url( '/legs/' ) ),
		array( __( 'Services en ligne', 'afvf' ), home_url( '/services/' ) ),
	);
	echo '<ul class="menu">';
	foreach ( $items as $item ) {
		printf(
			'<li class="menu-item"><a href="%s">%s</a></li>',
			esc_url( $item[1] ),
			esc_html( $item[0] )
		);
	}
	echo '</ul>';
}

function afvf_footer_menu_fallback() {
	$items = array(
		array( __( 'Missions', 'afvf' ), home_url( '/missions/' ) ),
		array( __( 'Soutenez-nous', 'afvf' ), home_url( '/legs/' ) ),
		array( __( 'Actualités', 'afvf' ), get_permalink( get_option( 'page_for_posts' ) ) ?: home_url( '/actualites/' ) ),
		array( __( 'Evénements', 'afvf' ), home_url( '/evenements/' ) ),
		array( __( 'Contact', 'afvf' ), home_url( '/contact/' ) ),
	);
	echo '<ul class="footer-quicklinks">';
	foreach ( $items as $item ) {
		printf(
			'<li><a href="%s">%s</a></li>',
			esc_url( $item[1] ),
			esc_html( $item[0] )
		);
	}
	echo '</ul>';
}
