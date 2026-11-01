<?php
/**
 * Zones widget (réseaux sociaux via extension).
 *
 * @package AFVF
 */

defined( 'ABSPATH' ) || exit;

function afvf_register_sidebars() {
	register_sidebar(
		array(
			'name'          => __( 'Pied de page — icônes sociales (extension)', 'afvf' ),
			'id'            => 'afvf-footer-social',
			'description'   => __( 'Ajoutez ici le bloc « Liens sociaux » (+) : choisissez les réseaux, collez les URLs, Mettre à jour. (Pas dans « Widgets désactivés ».) Remplace les icônes intégrées au thème.', 'afvf' ),
			'before_widget' => '<div id="%1$s" class="footer-social-widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<span class="screen-reader-text">',
			'after_title'   => '</span>',
		)
	);
}
add_action( 'widgets_init', 'afvf_register_sidebars' );

/**
 * Widget réseaux sociaux actif dans le pied de page ?
 */
function afvf_footer_social_uses_plugin() {
	return is_active_sidebar( 'afvf-footer-social' );
}
