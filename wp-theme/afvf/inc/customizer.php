<?php
/**
 * Customizer — coordonnées et liens externes.
 *
 * @package AFVF
 */

defined( 'ABSPATH' ) || exit;

function afvf_customize_register( WP_Customize_Manager $wp_customize ) {
	$wp_customize->add_section(
		'afvf_settings',
		array(
			'title'    => __( 'AFVF — Réglages du site', 'afvf' ),
			'priority' => 30,
		)
	);

	$fields = array(
		'afvf_phone'        => array( 'label' => __( 'Téléphone', 'afvf' ), 'default' => '06 26 36 89 94' ),
		'afvf_email'        => array( 'label' => __( 'Email', 'afvf' ), 'default' => 'assofvf@gmail.com' ),
		'afvf_help_page'    => array( 'label' => __( 'Page « Besoin d\'aide ? » (slug ou chemin, ex. aide)', 'afvf' ), 'default' => 'aide' ),
		'afvf_topbar_extra' => array( 'label' => __( 'Texte barre rouge (ex. 3919)', 'afvf' ), 'default' => '3919' ),
		'afvf_exit_url'     => array( 'label' => __( 'Lien « Quitter rapidement »', 'afvf' ), 'default' => 'https://www.service-public.fr/' ),
		'afvf_donate_url'   => array(
			'label'   => __( 'Lien don (HelloAsso)', 'afvf' ),
			'default' => 'https://www.helloasso.com/associations/afvf/formulaires/1',
		),
		'afvf_member_url'   => array(
			'label'   => __( 'Lien adhésion', 'afvf' ),
			'default' => 'https://pay.qonto.com/payment-links/019e2fee-f9e0-7add-b36a-47b06719677e?resource_id=019e2fee-f9e1-7413-b624-514697f490e8',
		),
		'afvf_ticker'       => array(
			'label'   => __( 'Bandeau défilant (accueil)', 'afvf' ),
			'default' => '#ARRETONSLESFEMINICIDES',
		),
		'afvf_terms_url'    => array(
			'label'   => __( 'Page conditions d\'utilisation (URL ou chemin)', 'afvf' ),
			'default' => '/mentions-legales/',
		),
	);

	foreach ( $fields as $id => $field ) {
		$wp_customize->add_setting(
			$id,
			array(
				'default'           => $field['default'],
				'sanitize_callback' => 'sanitize_text_field',
			)
		);
		$wp_customize->add_control(
			$id,
			array(
				'label'   => $field['label'],
				'section' => 'afvf_settings',
				'type'    => 'text',
			)
		);
	}

	$wp_customize->add_section(
		'afvf_social_footer',
		array(
			'title'       => __( 'Réseaux sociaux — pied de page', 'afvf' ),
			'description' => __( 'Recommandé : installez l’extension « Simple Social Icons » (Extensions → Ajouter), puis placez son widget dans Apparence → Widgets → « Pied de page — icônes sociales (extension) ». Les champs ci-dessous ne servent que si vous n’utilisez pas l’extension.', 'afvf' ),
			'priority'    => 31,
		)
	);

	$social_fields = array(
		'afvf_whatsapp'  => array(
			'label'       => 'WhatsApp',
			'default'     => 'https://wa.me/33626368994',
			'description' => __( 'Ex. https://wa.me/33626368994 (numéro sans espaces, indicatif 33)', 'afvf' ),
		),
		'afvf_x'         => array(
			'label'       => 'X (Twitter)',
			'default'     => 'https://x.com/assofvf',
			'description' => __( 'Ex. https://x.com/votre_compte', 'afvf' ),
		),
		'afvf_instagram' => array(
			'label'       => 'Instagram',
			'default'     => 'https://www.instagram.com/assofvf',
			'description' => __( 'Ex. https://www.instagram.com/assofvf', 'afvf' ),
		),
		'afvf_facebook'  => array(
			'label'       => 'Facebook',
			'default'     => 'https://www.facebook.com/assofvf/',
			'description' => __( 'Ex. https://www.facebook.com/assofvf/', 'afvf' ),
		),
		'afvf_linkedin'  => array(
			'label'       => 'LinkedIn',
			'default'     => 'https://www.linkedin.com/in/association-des-familles-de-victimes-de-feminicides-107893246',
			'description' => __( 'Page ou profil LinkedIn de l’association', 'afvf' ),
		),
		'afvf_youtube'   => array(
			'label'       => 'YouTube',
			'default'     => 'https://www.youtube.com/@afvfmedia',
			'description' => __( 'Ex. https://www.youtube.com/@afvfmedia', 'afvf' ),
		),
	);

	foreach ( $social_fields as $id => $field ) {
		$wp_customize->add_setting(
			$id,
			array(
				'default'           => $field['default'],
				'sanitize_callback' => 'esc_url_raw',
			)
		);
		$wp_customize->add_control(
			$id,
			array(
				'label'       => $field['label'],
				'description' => $field['description'],
				'section'     => 'afvf_social_footer',
				'type'        => 'url',
				'input_attrs' => array(
					'placeholder' => $field['default'],
				),
			)
		);
	}

	$wp_customize->add_setting(
		'afvf_show_floating_social',
		array(
			'default'           => true,
			'sanitize_callback' => 'rest_sanitize_boolean',
		)
	);
	$wp_customize->add_control(
		'afvf_show_floating_social',
		array(
			'label'       => __( 'Barre Facebook / Instagram à droite de l’écran', 'afvf' ),
			'description' => __( 'Uniquement sur la page d’accueil. Utilise les liens Facebook et Instagram ci-dessous.', 'afvf' ),
			'section'     => 'afvf_social_footer',
			'type'        => 'checkbox',
		)
	);

	$wp_customize->add_section(
		'afvf_newsletter',
		array(
			'title'       => __( 'Newsletter (pied de page)', 'afvf' ),
			'description' => __( 'Formulaire « Recevez nos actualités mensuellement ». Les inscrits sont listés dans le menu admin Newsletter.', 'afvf' ),
			'priority'    => 32,
		)
	);

	$wp_customize->add_setting(
		'afvf_newsletter_double_optin',
		array(
			'default'           => false,
			'sanitize_callback' => 'rest_sanitize_boolean',
		)
	);
	$wp_customize->add_control(
		'afvf_newsletter_double_optin',
		array(
			'label'       => __( 'Double confirmation par e-mail (recommandé RGPD)', 'afvf' ),
			'description' => __( 'Si coché, la personne doit cliquer un lien reçu par mail avant d’être inscrite.', 'afvf' ),
			'section'     => 'afvf_newsletter',
			'type'        => 'checkbox',
		)
	);

	$wp_customize->add_setting(
		'afvf_show_topbar',
		array(
			'default'           => false,
			'sanitize_callback' => 'rest_sanitize_boolean',
		)
	);
	$wp_customize->add_control(
		'afvf_show_topbar',
		array(
			'label'       => __( 'Barre rouge au-dessus du menu (aide / 3919)', 'afvf' ),
			'description' => __( 'Désactivée par défaut pour correspondre au site Wix.', 'afvf' ),
			'section'     => 'afvf_settings',
			'type'        => 'checkbox',
		)
	);

	$wp_customize->add_setting(
		'afvf_site_chat',
		array(
			'default'           => true,
			'sanitize_callback' => 'rest_sanitize_boolean',
		)
	);
	$wp_customize->add_control(
		'afvf_site_chat',
		array(
			'label'       => __( 'Chat site (visiteur ↔ admin WordPress)', 'afvf' ),
			'description' => __( 'Bouton rose : le visiteur écrit d’abord, puis laisse nom/e-mail (type Wix). Réponses dans Boîte de réception.', 'afvf' ),
			'section'     => 'afvf_settings',
			'type'        => 'checkbox',
		)
	);

	$wp_customize->add_setting(
		'afvf_show_hero',
		array(
			'default'           => true,
			'sanitize_callback' => 'rest_sanitize_boolean',
		)
	);
	$wp_customize->add_control(
		'afvf_show_hero',
		array(
			'label'       => __( 'Afficher le bandeau héro sur la page d\'accueil', 'afvf' ),
			'description' => __( 'Hero intégré au thème (Personnaliser). Ignoré si « Pages construites dans l’éditeur » est coché.', 'afvf' ),
			'section'     => 'afvf_settings',
			'type'        => 'checkbox',
		)
	);

	$wp_customize->add_setting(
		'afvf_editor_layout',
		array(
			'default'           => true,
			'sanitize_callback' => 'rest_sanitize_boolean',
		)
	);
	$wp_customize->add_control(
		'afvf_editor_layout',
		array(
			'label'       => __( 'Pages construites dans l’éditeur (toutes les pages + accueil)', 'afvf' ),
			'description' => __( 'Coché (recommandé) : toutes les pages et l’accueil — le menu reste transparent sur le 1er bloc Couverture (hero). Décoché : bandeau titre classique du thème.', 'afvf' ),
			'section'     => 'afvf_settings',
			'type'        => 'checkbox',
		)
	);

	$wp_customize->add_setting(
		'afvf_bg_white',
		array(
			'default'           => false,
			'sanitize_callback' => 'rest_sanitize_boolean',
		)
	);
	$wp_customize->add_control(
		'afvf_bg_white',
		array(
			'label'       => __( 'Fond de page blanc (toutes les pages)', 'afvf' ),
			'description' => __( 'Coché : le fond crème du thème devient blanc partout (comme le site Wix). Les pages avec les motifs « Vision », « Cartes photo + texte » ou « Grille 2×3 » sont déjà blanches automatiquement.', 'afvf' ),
			'section'     => 'afvf_settings',
			'type'        => 'checkbox',
		)
	);

	$wp_customize->add_setting(
		'afvf_hero_quote',
		array(
			'default'           => "Elles s'appelaient Sandra, Julie, Aïssatou... Pour vous c'est un fait divers, pour nous un drame à jamais... (Extrait Appel du 06 Juillet 2019)",
			'sanitize_callback' => 'sanitize_textarea_field',
		)
	);
	$wp_customize->add_control(
		'afvf_hero_quote',
		array(
			'label'   => __( 'Texte sous le logo (accueil)', 'afvf' ),
			'section' => 'afvf_settings',
			'type'    => 'textarea',
		)
	);

	$tiles = array(
		array(
			'afvf_tile1_title' => __( 'Mission', 'afvf' ),
			'afvf_tile1_text'  => __( 'Principes, Vision & Engagements', 'afvf' ),
			'afvf_tile1_url'   => '/vision/',
			'afvf_tile1_class' => 'hero-tile--pink',
		),
		array(
			'afvf_tile2_title' => __( 'Evènements', 'afvf' ),
			'afvf_tile2_text'  => __( 'S\'inscrire et Contribuer au changement', 'afvf' ),
			'afvf_tile2_url'   => '/assises/',
			'afvf_tile2_class' => 'hero-tile--sky',
		),
		array(
			'afvf_tile3_title' => __( 'S\'engager', 'afvf' ),
			'afvf_tile3_text'  => __( 'Faire du bénévolat, participer ou faire un don', 'afvf' ),
			'afvf_tile3_url'   => '/rejoignez-nous/',
			'afvf_tile3_class' => 'hero-tile--navy',
		),
	);

	foreach ( $tiles as $i => $tile ) {
		foreach ( $tile as $key => $default ) {
			if ( str_ends_with( $key, '_class' ) ) {
				continue;
			}
			$wp_customize->add_setting(
				$key,
				array(
					'default'           => $default,
					'sanitize_callback' => 'sanitize_text_field',
				)
			);
			$wp_customize->add_control(
				$key,
				array(
					'label'   => sprintf(
						/* translators: %d: tile number 1-3 */
						__( 'Bloc accueil %d — %s', 'afvf' ),
						(int) floor( $i ) + 1,
						str_contains( $key, 'title' ) ? 'titre' : ( str_contains( $key, 'text' ) ? 'sous-titre' : 'lien' )
					),
					'section' => 'afvf_settings',
					'type'    => 'text',
				)
			);
		}
	}
}
add_action( 'customize_register', 'afvf_customize_register' );

/**
 * Logo dans le bandeau d'accueil ou l'en-tête.
 */
function afvf_the_brand_logo( $class = 'brand-logo' ) {
	if ( has_custom_logo() ) {
		echo wp_get_attachment_image(
			(int) get_theme_mod( 'custom_logo' ),
			'medium',
			false,
			array(
				'class' => $class,
				'alt'   => get_bloginfo( 'name' ),
			)
		);
		return;
	}
	$path = get_template_directory() . '/assets/images/logo-afvf.png';
	if ( file_exists( $path ) ) {
		printf(
			'<img class="%s" src="%s" alt="%s" width="280" height="267" loading="eager">',
			esc_attr( $class ),
			esc_url( get_template_directory_uri() . '/assets/images/logo-afvf.png' ),
			esc_attr( get_bloginfo( 'name' ) )
		);
		return;
	}
	echo '<img class="' . esc_attr( $class ) . '" src="' . esc_url( get_template_directory_uri() . '/assets/images/logo-placeholder.svg' ) . '" alt="" width="58" height="58">';
}

/**
 * URL de fond du hero d'accueil.
 */
/**
 * SVG minimal pour les icônes sociales du pied de page.
 *
 * @param string $network Network key.
 */
function afvf_social_icon_svg( $network ) {
	// Logos officiels (glyphes blancs sur fond de marque géré en CSS).
	$svg = array(
		'whatsapp'  => '<svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"><path fill="#25D366" d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347M12 0C5.373 0 0 5.373 0 12c0 2.127.555 4.126 1.528 5.867L0 24l6.335-1.662c1.68.917 3.582 1.438 5.665 1.438 6.627 0 12-5.373 12-12S18.627 0 12 0"/></svg>',
		'x'         => '<svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"><path fill="#000000" d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>',
		'instagram' => '<svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"><rect x="3" y="3" width="18" height="18" rx="5" fill="none" stroke="#ffffff" stroke-width="2"/><circle cx="12" cy="12" r="4.2" fill="none" stroke="#ffffff" stroke-width="2"/><circle cx="17.35" cy="6.65" r="1.15" fill="#ffffff"/></svg>',
		'facebook'  => '<svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"><path fill="#ffffff" d="M16.75 8.25h-2.55V6.6c0-.78.63-1.02 1.08-1.02h1.42V3h-2.35c-2.17 0-3.65 1.65-3.65 3.75v1.5H7.5v2.75h2.35V21h3.65v-9.75h2.55l.35-2.75z"/></svg>',
		'linkedin'  => '<svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"><path fill="#ffffff" d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 114.127 0 2.063 2.063 0 01-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>',
		'youtube'   => '<svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"><path fill="#ffffff" d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>',
	);
	if ( isset( $svg[ $network ] ) ) {
		echo $svg[ $network ]; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG markup.
	}
}

/**
 * Icônes sombres pour bandeaux de page (Facebook, Instagram, LinkedIn).
 *
 * @param string $network facebook|instagram|linkedin.
 */
function afvf_social_inline_icon_svg( $network ) {
	$fill = '#2a2a2a';
	$svg  = array(
		'facebook'  => '<svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"><path fill="' . $fill . '" d="M16.75 8.25h-2.55V6.6c0-.78.63-1.02 1.08-1.02h1.42V3h-2.35c-2.17 0-3.65 1.65-3.65 3.75v1.5H7.5v2.75h2.35V21h3.65v-9.75h2.55l.35-2.75z"/></svg>',
		'instagram' => '<svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"><rect x="3" y="3" width="18" height="18" rx="5" fill="none" stroke="' . $fill . '" stroke-width="2"/><circle cx="12" cy="12" r="4.2" fill="none" stroke="' . $fill . '" stroke-width="2"/><circle cx="17.35" cy="6.65" r="1.15" fill="' . $fill . '"/></svg>',
		'linkedin'  => '<svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"><path fill="' . $fill . '" d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 114.127 0 2.063 2.063 0 01-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>',
	);
	if ( isset( $svg[ $network ] ) ) {
		echo $svg[ $network ]; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG markup.
	}
}

/**
 * Shortcode : Facebook, Instagram, LinkedIn (URLs = Customizer AFVF).
 *
 * @return string
 */
function afvf_social_inline_shortcode() {
	ob_start();
	get_template_part( 'template-parts/page', 'social-inline' );
	return ob_get_clean();
}
add_shortcode( 'afvf_social_inline', 'afvf_social_inline_shortcode' );

/**
 * Icônes barre fixe droite (fond bleu Wix, pas de carrés Facebook/Instagram séparés).
 *
 * @param string $network facebook|instagram.
 */
function afvf_social_rail_icon_svg( $network ) {
	$icons = array(
		'facebook'  => '<svg viewBox="0 0 24 24" width="26" height="26" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"><path fill="#ffffff" d="M14 8.5V6.8c0-.6.2-1 .9-1H17V3h-2.4C11.8 3 11 4.5 11 7v1.5H9v2.7h2V21h3v-9.8h2.6l.4-2.7H14z"/></svg>',
		'instagram' => '<svg viewBox="0 0 24 24" width="26" height="26" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"><defs><linearGradient id="afvf-ig-rail" x1="0" y1="24" x2="24" y2="0" gradientUnits="userSpaceOnUse"><stop stop-color="#feda75"/><stop offset="0.3" stop-color="#fa7e1e"/><stop offset="0.55" stop-color="#d62976"/><stop offset="1" stop-color="#962fbf"/></linearGradient></defs><rect x="3" y="3" width="18" height="18" rx="5" fill="none" stroke="url(#afvf-ig-rail)" stroke-width="2"/><circle cx="12" cy="12" r="4.2" fill="none" stroke="url(#afvf-ig-rail)" stroke-width="2"/><circle cx="17.35" cy="6.65" r="1.15" fill="url(#afvf-ig-rail)"/></svg>',
	);
	if ( isset( $icons[ $network ] ) ) {
		echo $icons[ $network ]; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG markup.
	}
}

function afvf_hero_background_url( $post_id = null ) {
	$post_id = $post_id ?: get_queried_object_id();
	$url     = get_the_post_thumbnail_url( $post_id, 'full' );
	if ( $url ) {
		return $url;
	}
	$fallback = get_template_directory() . '/assets/images/hero-home.jpg';
	if ( file_exists( $fallback ) ) {
		return get_template_directory_uri() . '/assets/images/hero-home.jpg';
	}
	return '';
}

/**
 * @param string $key Setting key.
 * @param string $default Fallback.
 */
function afvf_mod( $key, $default = '' ) {
	$value = get_theme_mod( $key, $default );
	return is_string( $value ) ? $value : $default;
}

/**
 * Contenu des pages entièrement géré par l’éditeur de blocs (sans bandeau / hero imposés par le thème).
 */
function afvf_use_editor_layout() {
	if ( null !== get_theme_mod( 'afvf_editor_layout', null ) ) {
		return (bool) get_theme_mod( 'afvf_editor_layout', true );
	}
	return (bool) get_theme_mod( 'afvf_home_editor_layout', true );
}

/**
 * Classes du conteneur principal du contenu de page.
 *
 * @return string
 */
function afvf_page_content_class() {
	return afvf_use_editor_layout() ? 'afvf-block-content' : 'wrap prose';
}

/**
 * URL réseau social (pied de page). Chaîne vide = ne pas afficher l’icône.
 *
 * @param string $key     Setting key.
 * @param string $default Default URL.
 */
function afvf_social_url( $key, $default = '' ) {
	$value = trim( (string) get_theme_mod( $key, $default ) );
	if ( '' === $value ) {
		return '';
	}
	return $value;
}

function afvf_help_url() {
	$val = trim( afvf_mod( 'afvf_help_page', 'aide' ) );
	if ( str_starts_with( $val, 'http://' ) || str_starts_with( $val, 'https://' ) ) {
		return esc_url( $val );
	}
	return home_url( '/' . trim( $val, '/' ) . '/' );
}

function afvf_terms_url() {
	$val = trim( afvf_mod( 'afvf_terms_url', '/mentions-legales/' ) );
	if ( str_starts_with( $val, 'http://' ) || str_starts_with( $val, 'https://' ) ) {
		return esc_url( $val );
	}
	return home_url( '/' . trim( $val, '/' ) . '/' );
}

/**
 * Lien politique de confidentialité (page WP ou page statique du site).
 */
function afvf_privacy_url() {
	$policy = get_privacy_policy_url();
	if ( $policy ) {
		return $policy;
	}
	return home_url( '/confidentialite/' );
}

function afvf_tel_href( $phone = null ) {
	$phone = $phone ?? afvf_mod( 'afvf_phone', '06 26 36 89 94' );
	$digits = preg_replace( '/\D+/', '', $phone );
	if ( str_starts_with( $digits, '0' ) ) {
		$digits = '33' . substr( $digits, 1 );
	}
	return 'tel:+' . $digits;
}

function afvf_page_hero_style( $post_id = null ) {
	$post_id = $post_id ?: get_the_ID();
	$url     = get_the_post_thumbnail_url( $post_id, 'large' );
	if ( ! $url ) {
		return '';
	}
	return sprintf(
		'background-image: linear-gradient(120deg, rgba(17,17,17,0.82), rgba(141,11,30,0.55)), url(%s);',
		esc_url( $url )
	);
}
