<?php
/**
 * Motifs Kër Aïcha — 3 Couvertures (fond + contenu à l’intérieur).
 *
 * @package AFVF
 */

defined( 'ABSPATH' ) || exit;

/**
 * Enregistre les motifs Kër Aïcha.
 */
function afvf_register_ker_aicha_block_patterns() {
	if ( ! function_exists( 'register_block_pattern' ) || ! function_exists( 'afvf_ker_aicha_section_cover_pattern' ) ) {
		return;
	}

	register_block_pattern_category(
		'afvf-ker-aicha',
		array(
			'label' => __( 'Kër Aïcha', 'afvf' ),
		)
	);

	$fondation_url = home_url( '/fondation/' );

	$ker_aicha_contexte = esc_html__(
		'Un lieu d’accueil et de reconstruction pour les femmes victimes de violences et leurs enfants, porté par l’AFVF et ses partenaires.',
		'afvf'
	);
	$ker_aicha_objectif = esc_html__(
		'Offrir un cadre sécurisé, des accompagnements spécialisés et des activités favorisant la reprise en main et la dignité.',
		'afvf'
	);
	$ker_aicha_lieu = esc_html__(
		'Espaces de vie, d’écoute, de soin et d’activités (ateliers, culture, sport, retour à l’emploi) dans un environnement bienveillant.',
		'afvf'
	);
	$ker_aicha_appel = esc_html__(
		'Vous souhaitez soutenir le projet (mécénat, don, compétences, partenariat) ? Contactez-nous ou participez via la Fondation.',
		'afvf'
	);

	$ker_aicha_gallery_grid = '<!-- wp:group {"className":"afvf-ker-aicha-gallery-grid","layout":{"type":"default"}} -->
<div class="wp-block-group afvf-ker-aicha-gallery-grid"><!-- wp:image {"sizeSlug":"large","linkDestination":"none","className":"afvf-ker-aicha-gallery__photo"} -->
<figure class="wp-block-image size-large afvf-ker-aicha-gallery__photo"><img alt=""/></figure>
<!-- /wp:image -->

<!-- wp:image {"sizeSlug":"large","linkDestination":"none","className":"afvf-ker-aicha-gallery__photo"} -->
<figure class="wp-block-image size-large afvf-ker-aicha-gallery__photo"><img alt=""/></figure>
<!-- /wp:image -->

<!-- wp:image {"sizeSlug":"large","linkDestination":"none","className":"afvf-ker-aicha-gallery__photo"} -->
<figure class="wp-block-image size-large afvf-ker-aicha-gallery__photo"><img alt=""/></figure>
<!-- /wp:image --></div>
<!-- /wp:group -->';

	$ker_aicha_banner_triple = '<!-- wp:group {"className":"afvf-ker-aicha-banner afvf-ker-aicha-banner--triple afvf-ker-aicha-gallery-in-cover","layout":{"type":"default"}} -->
<div class="wp-block-group afvf-ker-aicha-banner afvf-ker-aicha-banner--triple afvf-ker-aicha-gallery-in-cover">' . $ker_aicha_gallery_grid . '</div>
<!-- /wp:group -->';

	$ker_aicha_banner_single = '<!-- wp:group {"className":"afvf-ker-aicha-banner afvf-ker-aicha-banner--single afvf-ker-aicha-gallery-in-cover","layout":{"type":"default"}} -->
<div class="wp-block-group afvf-ker-aicha-banner afvf-ker-aicha-banner--single afvf-ker-aicha-gallery-in-cover"><!-- wp:image {"sizeSlug":"large","linkDestination":"none","className":"afvf-ker-aicha-banner__image"} -->
<figure class="wp-block-image size-large afvf-ker-aicha-banner__image"><img alt=""/></figure>
<!-- /wp:image --></div>
<!-- /wp:group -->';

	$ker_aicha_card = '<!-- wp:group {"className":"afvf-ker-aicha-card","backgroundColor":"white","style":{"spacing":{"padding":{"top":"2.25rem","bottom":"1.75rem","left":"2rem","right":"2rem"}}}} -->
<div class="wp-block-group afvf-ker-aicha-card has-white-background-color has-background"><!-- wp:heading {"textAlign":"center","level":1,"className":"afvf-ker-aicha-card__title"} -->
<h1 class="wp-block-heading has-text-align-center afvf-ker-aicha-card__title">' . esc_html__( 'KËR AÏCHA', 'afvf' ) . '</h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center","className":"afvf-ker-aicha-card__subtitle"} -->
<p class="has-text-align-center afvf-ker-aicha-card__subtitle">' . esc_html__( 'Projet de Rénovation et d’Accompagnement « Kër Aïcha »', 'afvf' ) . '</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3,"className":"afvf-ker-aicha-card__section"} -->
<h3 class="wp-block-heading afvf-ker-aicha-card__section">' . esc_html__( 'Contexte et justification', 'afvf' ) . '</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>' . $ker_aicha_contexte . '</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3,"className":"afvf-ker-aicha-card__section"} -->
<h3 class="wp-block-heading afvf-ker-aicha-card__section">' . esc_html__( 'Objectif du projet', 'afvf' ) . '</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>' . $ker_aicha_objectif . '</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"className":"afvf-ker-aicha-highlight"} -->
<p class="afvf-ker-aicha-highlight">' . esc_html__( 'Ouverture prévue : Janvier 2021', 'afvf' ) . '</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3,"className":"afvf-ker-aicha-card__section"} -->
<h3 class="wp-block-heading afvf-ker-aicha-card__section">' . esc_html__( 'Description du lieu et des activités', 'afvf' ) . '</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>' . $ker_aicha_lieu . '</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3,"className":"afvf-ker-aicha-card__section"} -->
<h3 class="wp-block-heading afvf-ker-aicha-card__section">' . esc_html__( 'Appel à contribution', 'afvf' ) . '</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>' . $ker_aicha_appel . '</p>
<!-- /wp:paragraph -->

<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"},"style":{"spacing":{"margin":{"top":"1.75rem"}}},"className":"afvf-ker-aicha-card__buttons"} -->
<div class="wp-block-buttons afvf-ker-aicha-card__buttons" style="margin-top:1.75rem"><!-- wp:button {"className":"afvf-wix-btn-pink","style":{"border":{"radius":"0px"},"color":{"background":"#ec5e87","text":"#ffffff"}}} -->
<div class="wp-block-button afvf-wix-btn-pink"><a class="wp-block-button__link wp-element-button has-text-color has-background" style="border-radius:0px;background-color:#ec5e87;color:#ffffff" href="' . esc_url( $fondation_url ) . '">' . esc_html__( 'Fondation Maison Kër Aïcha', 'afvf' ) . '</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group -->';

	$ker_aicha_adhere = afvf_ker_aicha_adhere_block_pattern();
	$ker_aicha_bar    = afvf_ker_aicha_contact_bar_block_pattern();

	$hint_1 = __( 'Couverture → Remplacer : image de fond. Voile 0 %. Galerie en bas de la bannière + Adhérer.', 'afvf' );
	$hint_2 = __( 'Couverture → Remplacer : fond (ex. marbre). Carte blanche à l’intérieur.', 'afvf' );
	$hint_3 = __( 'Couverture → fond optionnel. Barre Nous appeler / E-mail / Je participe à l’intérieur.', 'afvf' );

	$motif_1_inner = $ker_aicha_adhere . $ker_aicha_banner_triple;
	$motif_2_inner = $ker_aicha_card;
	$motif_3_inner = $ker_aicha_bar;

	$motif_1 = afvf_ker_aicha_section_cover_pattern( 'gallery', 520, $motif_1_inner, $hint_1 );
	$motif_1b_inner = $ker_aicha_adhere . $ker_aicha_banner_single;
	$motif_1b      = afvf_ker_aicha_section_cover_pattern( 'gallery', 520, $motif_1b_inner, $hint_1 );
	$motif_2       = afvf_ker_aicha_section_cover_pattern( 'card', 0, $motif_2_inner, $hint_2 );
	$motif_3       = afvf_ker_aicha_section_cover_pattern( 'bar', 0, $motif_3_inner, $hint_3 );

	$cat_ker    = array( 'afvf', 'afvf-ker-aicha' );
	$kw_ker     = array( 'ker', 'aicha', 'ker aicha', 'afvf' );
	$kw_gallery = array_merge( $kw_ker, array( 'galerie', 'gallery', 'photos', 'motif 1' ) );
	$kw_card    = array_merge( $kw_ker, array( 'carte', 'texte', 'motif 2' ) );
	$kw_contact = array_merge( $kw_ker, array( 'nous appeler', 'telephone', 'email', 'contact', 'motif 3', '0626368994', 'assofvf' ) );

	register_block_pattern(
		'afvf/ker-aicha-motif-1-galerie',
		array(
			'title'       => __( 'Kër Aïcha — Motif 1 : Couverture fond + galerie', 'afvf' ),
			'description' => _x( 'Une Couverture : image de fond + 3 photos + Adhérer (tout dedans).', 'Block pattern description', 'afvf' ),
			'categories'  => array_merge( $cat_ker, array( 'gallery', 'banner' ) ),
			'keywords'    => $kw_gallery,
			'content'     => $motif_1,
		)
	);

	register_block_pattern(
		'afvf/ker-aicha-motif-1-galerie-1photo',
		array(
			'title'       => __( 'Kër Aïcha — Motif 1b : Couverture fond + 1 photo', 'afvf' ),
			'description' => _x( 'Comme le motif 1, avec une seule image en bandeau.', 'Block pattern description', 'afvf' ),
			'categories'  => array_merge( $cat_ker, array( 'gallery', 'banner' ) ),
			'keywords'    => $kw_gallery,
			'content'     => $motif_1b,
		)
	);

	register_block_pattern(
		'afvf/ker-aicha-motif-2-carte',
		array(
			'title'       => __( 'Kër Aïcha — Motif 2 : Couverture fond + carte', 'afvf' ),
			'description' => _x( 'Une Couverture : fond + texte KËR AÏCHA et bouton rose (dedans).', 'Block pattern description', 'afvf' ),
			'categories'  => array_merge( $cat_ker, array( 'text', 'banner' ) ),
			'keywords'    => $kw_card,
			'content'     => $motif_2,
		)
	);

	register_block_pattern(
		'afvf/ker-aicha-motif-3-barre',
		array(
			'title'       => __( 'Kër Aïcha — Motif 3 : Couverture fond + barre contact', 'afvf' ),
			'description' => _x( 'Une Couverture : fond + Nous appeler, E-mail, Je participe (dedans).', 'Block pattern description', 'afvf' ),
			'categories'  => array_merge( $cat_ker, array( 'text', 'banner' ) ),
			'keywords'    => $kw_contact,
			'content'     => $motif_3,
		)
	);

	// Slugs historiques (redirigent vers les 3 motifs).
	register_block_pattern(
		'afvf/ker-aicha-banniere-3-photos',
		array(
			'title'       => __( 'Kër Aïcha — Motif 1 : Couverture fond + galerie', 'afvf' ),
			'description' => _x( 'Alias du motif 1 (fond + galerie dans la Couverture).', 'Block pattern description', 'afvf' ),
			'categories'  => array_merge( $cat_ker, array( 'gallery' ) ),
			'keywords'    => $kw_gallery,
			'content'     => $motif_1,
		)
	);

	register_block_pattern(
		'afvf/ker-aicha-carte-contenu',
		array(
			'title'       => __( 'Kër Aïcha — Motif 2 : Couverture fond + carte', 'afvf' ),
			'description' => _x( 'Alias du motif 2.', 'Block pattern description', 'afvf' ),
			'categories'  => array_merge( $cat_ker, array( 'text' ) ),
			'keywords'    => $kw_card,
			'content'     => $motif_2,
		)
	);

	register_block_pattern(
		'afvf/ker-aicha-barre-contact',
		array(
			'title'       => __( 'Kër Aïcha — Motif 3 : Couverture fond + barre contact', 'afvf' ),
			'description' => _x( 'Alias du motif 3.', 'Block pattern description', 'afvf' ),
			'categories'  => array_merge( $cat_ker, array( 'text' ) ),
			'keywords'    => $kw_contact,
			'content'     => $motif_3,
		)
	);
}
add_action( 'init', 'afvf_register_ker_aicha_block_patterns', 20 );
