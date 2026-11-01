<?php
/**
 * Motifs de blocs (accueil et pages).
 *
 * @package AFVF
 */

defined( 'ABSPATH' ) || exit;

/**
 * Indication dans les motifs : positions par classe CSS (pas d’attributs custom dans le JSON).
 * Les classes afvf-pos-* sont lues par le thème à l’enregistrement et sur le site public.
 *
 * @param string $base_classes Classes sémantiques.
 * @param string $top          ex. 10%.
 * @param string $left         ex. 3% (vide si ancrage droite).
 * @param string $right        ex. 6%.
 * @return string
 */
function afvf_pattern_layer_classes( $base_classes, $top, $left = '', $right = '' ) {
	$top_n   = str_replace( '.', '_', number_format( (float) str_replace( '%', '', $top ), 1, '.', '' ) );
	$side    = $right
		? 'r' . str_replace( '.', '_', number_format( (float) str_replace( '%', '', $right ), 1, '.', '' ) )
		: 'l' . str_replace( '.', '_', number_format( (float) str_replace( '%', '', $left ), 1, '.', '' ) );
	$pos_cls = 'afvf-pos-t' . $top_n . '-' . $side;
	return trim( $base_classes . ' ' . $pos_cls );
}

/**
 * Une diapositive du hero défilant (Couverture photo + titre + texte + bouton).
 *
 * @param string $img    URL de l’image par défaut.
 * @param string $title  Titre.
 * @param string $text   Texte.
 * @param string $label  Libellé du bouton.
 * @param string $href   Lien du bouton.
 * @return string
 */
function afvf_pattern_hero_slide( $img, $title, $text, $label, $href ) {
	$img_esc = esc_url( $img );
	return '<!-- wp:cover {"url":"' . $img_esc . '","dimRatio":40,"minHeight":620,"minHeightUnit":"px","contentPosition":"center center","align":"full","className":"afvf-hero-slide"} -->
<div class="wp-block-cover alignfull afvf-hero-slide" style="min-height:620px"><img class="wp-block-cover__image-background" alt="" src="' . $img_esc . '" data-object-fit="cover"/><span aria-hidden="true" class="wp-block-cover__background has-background-dim-40 has-background-dim"></span><div class="wp-block-cover__inner-container"><!-- wp:heading {"textAlign":"center","level":2,"className":"afvf-hero-slide__title"} -->
<h2 class="wp-block-heading has-text-align-center afvf-hero-slide__title">' . esc_html( $title ) . '</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center","className":"afvf-hero-slide__text"} -->
<p class="has-text-align-center afvf-hero-slide__text">' . esc_html( $text ) . '</p>
<!-- /wp:paragraph -->

<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"},"className":"afvf-hero-slide__cta"} -->
<div class="wp-block-buttons afvf-hero-slide__cta"><!-- wp:button {"className":"afvf-hero-slide__btn is-style-outline"} -->
<div class="wp-block-button afvf-hero-slide__btn is-style-outline"><a class="wp-block-button__link wp-element-button" href="' . esc_url( $href ) . '">' . esc_html( $label ) . '</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div></div>
<!-- /wp:cover -->';
}

/**
 * Carte « photo portrait + étiquette + titre + texte » (se soulève au survol).
 *
 * @param string $title Titre (aussi libellé de l’étiquette).
 * @param string $text  Texte court.
 * @param string $href  Lien de l’étiquette.
 * @return string
 */
function afvf_pattern_lift_card( $title, $text, $href = '#' ) {
	$title_esc = esc_html( $title );
	return '<!-- wp:group {"className":"afvf-lift-card","layout":{"type":"default"}} -->
<div class="wp-block-group afvf-lift-card"><!-- wp:image {"sizeSlug":"large","linkDestination":"none","className":"afvf-lift-card__media"} -->
<figure class="wp-block-image size-large afvf-lift-card__media"><img alt=""/></figure>
<!-- /wp:image -->

<!-- wp:buttons {"className":"afvf-lift-card__label-wrap","layout":{"type":"flex","justifyContent":"center"}} -->
<div class="wp-block-buttons afvf-lift-card__label-wrap"><!-- wp:button {"className":"afvf-lift-card__label is-style-outline"} -->
<div class="wp-block-button afvf-lift-card__label is-style-outline"><a class="wp-block-button__link wp-element-button" href="' . esc_url( $href ) . '">' . $title_esc . '</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons -->

<!-- wp:heading {"textAlign":"center","level":3,"className":"afvf-lift-card__title"} -->
<h3 class="wp-block-heading has-text-align-center afvf-lift-card__title">' . $title_esc . '</h3>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center","className":"afvf-lift-card__text"} -->
<p class="has-text-align-center afvf-lift-card__text">' . esc_html( $text ) . '</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->';
}

/**
 * Rangée « texte + photo » (zigzag) : texte à gauche ou à droite.
 *
 * @param bool     $image_first true = photo à gauche, texte à droite.
 * @param string   $title       Grand titre (animation « fumée »).
 * @param string   $subtitle    Phrase d’accroche en gras (vide = aucune).
 * @param string[] $paragraphs  Paragraphes.
 * @param string   $more_href   Lien « en savoir plus » (vide = pas de bouton, ex. 1re rangée).
 * @param string[] $bullets     Puces optionnelles (après les paragraphes).
 * @return string
 */
function afvf_pattern_zigzag_row( $image_first, $title, $subtitle, $paragraphs, $more_href = '', $bullets = array() ) {
	$media = '<!-- wp:image {"sizeSlug":"large","linkDestination":"none","className":"afvf-zigzag__media"} -->
<figure class="wp-block-image size-large afvf-zigzag__media"><img alt=""/></figure>
<!-- /wp:image -->';

	$body  = '<!-- wp:heading {"level":2,"className":"afvf-zigzag__title afvf-smoke-title"} -->
<h2 class="wp-block-heading afvf-zigzag__title afvf-smoke-title">' . esc_html( $title ) . '</h2>
<!-- /wp:heading -->';
	if ( '' !== $subtitle ) {
		$body .= '

<!-- wp:paragraph {"className":"afvf-zigzag__lead"} -->
<p class="afvf-zigzag__lead"><strong>' . esc_html( $subtitle ) . '</strong></p>
<!-- /wp:paragraph -->';
	}
	foreach ( $paragraphs as $para ) {
		$body .= '

<!-- wp:paragraph {"className":"afvf-zigzag__text"} -->
<p class="afvf-zigzag__text">' . esc_html( $para ) . '</p>
<!-- /wp:paragraph -->';
	}
	if ( ! empty( $bullets ) ) {
		$list = '';
		foreach ( $bullets as $item ) {
			$list .= '<li>' . esc_html( $item ) . '</li>';
		}
		$body .= '

<!-- wp:list {"className":"afvf-zigzag__list"} -->
<ul class="afvf-zigzag__list">' . $list . '</ul>
<!-- /wp:list -->';
	}
	if ( '' !== $more_href ) {
		$body .= '

<!-- wp:buttons {"className":"afvf-zigzag__cta","layout":{"type":"flex","justifyContent":"right"}} -->
<div class="wp-block-buttons afvf-zigzag__cta"><!-- wp:button {"className":"afvf-zigzag__more"} -->
<div class="wp-block-button afvf-zigzag__more"><a class="wp-block-button__link wp-element-button" href="' . esc_url( $more_href ) . '">' . esc_html__( 'en savoir plus', 'afvf' ) . '</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons -->';
	}

	$text = '<!-- wp:group {"className":"afvf-zigzag__panel","layout":{"type":"default"}} -->
<div class="wp-block-group afvf-zigzag__panel">' . $body . '</div>
<!-- /wp:group -->';

	$mod = $image_first ? ' afvf-zigzag__row--reverse' : '';
	return '<!-- wp:group {"className":"afvf-zigzag__row' . $mod . '","layout":{"type":"default"}} -->
<div class="wp-block-group afvf-zigzag__row' . $mod . '">' . ( $image_first ? $media . "\n\n" . $text : $text . "\n\n" . $media ) . '</div>
<!-- /wp:group -->';
}

/**
 * Carte « photo à gauche + titre et texte » sur fond bleu-gris.
 *
 * @param string $title Titre.
 * @param string $text  Texte.
 * @return string
 */
function afvf_pattern_photo_text_card( $title, $text ) {
	return '<!-- wp:group {"className":"afvf-photo-text-card","layout":{"type":"default"}} -->
<div class="wp-block-group afvf-photo-text-card"><!-- wp:image {"sizeSlug":"large","linkDestination":"none","className":"afvf-photo-text-card__media"} -->
<figure class="wp-block-image size-large afvf-photo-text-card__media"><img alt=""/></figure>
<!-- /wp:image -->

<!-- wp:group {"className":"afvf-photo-text-card__body","layout":{"type":"default"}} -->
<div class="wp-block-group afvf-photo-text-card__body"><!-- wp:heading {"level":3,"className":"afvf-photo-text-card__title"} -->
<h3 class="wp-block-heading afvf-photo-text-card__title">' . esc_html( $title ) . '</h3>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"afvf-photo-text-card__text"} -->
<p class="afvf-photo-text-card__text">' . esc_html( $text ) . '</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:group -->';
}

/**
 * Carte moitié texte (fond couleur) / moitié photo — bouton « Découvrir » au survol.
 *
 * @param string $tone  Couleur : plaidoyer | socio | event | finance | formation | admin.
 * @param string $title Titre (souligné).
 * @param string $lead  Sous-titre.
 * @param string $href  Lien du bouton.
 * @param string $img   URL de l’image (vide = à choisir dans l’éditeur).
 * @return string
 */
function afvf_pattern_hover_split_card( $tone, $title, $lead, $href, $img = '' ) {
	$tone_esc  = preg_replace( '/[^a-z0-9-]/', '', strtolower( $tone ) );
	$title_esc = esc_html( $title );
	$lead_esc  = esc_html( $lead );
	$href_esc  = esc_url( $href );
	$btn       = esc_html__( 'Découvrir', 'afvf' );
	$img_esc   = $img ? esc_url( $img ) : '';
	$img_attr  = $img_esc ? ' src="' . $img_esc . '"' : '';
	$img_json  = $img_esc ? ',"url":"' . $img_esc . '"' : '';

	return '<!-- wp:group {"className":"afvf-hover-split-card afvf-hover-split-card--' . $tone_esc . '","layout":{"type":"default"}} -->
<div class="wp-block-group afvf-hover-split-card afvf-hover-split-card--' . $tone_esc . '"><!-- wp:group {"className":"afvf-hover-split-card__panel","layout":{"type":"default"}} -->
<div class="wp-block-group afvf-hover-split-card__panel"><!-- wp:heading {"level":3,"className":"afvf-hover-split-card__title"} -->
<h3 class="wp-block-heading afvf-hover-split-card__title">' . $title_esc . '</h3>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"afvf-hover-split-card__lead"} -->
<p class="afvf-hover-split-card__lead">' . $lead_esc . '</p>
<!-- /wp:paragraph -->

<!-- wp:buttons {"className":"afvf-hover-split-card__cta","layout":{"type":"flex","justifyContent":"left"}} -->
<div class="wp-block-buttons afvf-hover-split-card__cta"><!-- wp:button {"className":"afvf-hover-split-card__btn is-style-outline","style":{"border":{"radius":"0px","width":"1px"},"color":{"text":"#ffffff"}}} -->
<div class="wp-block-button afvf-hover-split-card__btn is-style-outline"><a class="wp-block-button__link has-text-color wp-element-button" href="' . $href_esc . '" style="border-radius:0px;border-width:1px;color:#ffffff">' . $btn . '</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group -->

<!-- wp:image {"sizeSlug":"large","linkDestination":"none","className":"afvf-hover-split-card__media"' . $img_json . '} -->
<figure class="wp-block-image size-large afvf-hover-split-card__media"><img alt=""' . $img_attr . '/></figure>
<!-- /wp:image --></div>
<!-- /wp:group -->';
}

/**
 * Carte formation (maquette Wix) : photo + bandeau bleu + objectif.
 * Au survol le bandeau bleu se dilate vers le haut (le bas reste fixe) ; sur la carte beige l’objectif monte dans le bleu.
 *
 * @param string $title      Titre du cours (bandeau bleu).
 * @param string $trainer    Ligne « Formateur : … ».
 * @param string $objective  Texte objectif (sans « Objectif : »).
 * @param string $duration   Ex. « 2 jours — 14 heures ».
 * @param string $price      Ex. « 290 € ».
 * @param string $content    Contenu (HTML simple autorisé).
 * @param string $more_href  Lien « En savoir + ».
 * @param string $footer     Variante : sand (beige, bleu sur la photo) | peach (pêche, bleu en haut de la photo).
 * @return string
 */
function afvf_pattern_course_rise_card( $title, $trainer, $objective, $duration, $price, $content, $more_href, $footer = 'sand' ) {
	$footer_esc = in_array( $footer, array( 'sand', 'peach' ), true ) ? $footer : 'sand';
	$obj        = preg_replace( '/^\s*Objectif\s*:\s*/iu', '', (string) $objective );
	$card_cls   = 'afvf-course-rise-card afvf-course-rise-card--' . $footer_esc;

	return '<!-- wp:group {"className":"' . esc_attr( $card_cls ) . '","layout":{"type":"default"}} -->
<div class="wp-block-group ' . esc_attr( $card_cls ) . '"><!-- wp:group {"className":"afvf-course-rise-card__head","layout":{"type":"default"}} -->
<div class="wp-block-group afvf-course-rise-card__head"><!-- wp:image {"sizeSlug":"large","linkDestination":"none","className":"afvf-course-rise-card__photo"} -->
<figure class="wp-block-image size-large afvf-course-rise-card__photo"><img alt=""/></figure>
<!-- /wp:image -->

<!-- wp:group {"className":"afvf-course-rise-card__blue","layout":{"type":"default"}} -->
<div class="wp-block-group afvf-course-rise-card__blue"><!-- wp:heading {"level":3,"className":"afvf-course-rise-card__blue-title"} -->
<h3 class="wp-block-heading afvf-course-rise-card__blue-title">' . esc_html( $title ) . '</h3>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"afvf-course-rise-card__trainer"} -->
<p class="afvf-course-rise-card__trainer">' . esc_html( $trainer ) . '</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:paragraph {"className":"afvf-course-rise-card__objective"} -->
<p class="afvf-course-rise-card__objective"><span class="afvf-course-rise-card__obj-label">' . esc_html__( 'Objectif :', 'afvf' ) . '</span> ' . esc_html( $obj ) . '</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:group {"className":"afvf-course-rise-card__foot","layout":{"type":"default"}} -->
<div class="wp-block-group afvf-course-rise-card__foot"><!-- wp:paragraph {"className":"afvf-course-rise-card__meta"} -->
<p class="afvf-course-rise-card__meta">' . esc_html__( 'Durée', 'afvf' ) . ' : ' . esc_html( $duration ) . ' ' . esc_html__( 'Tarif', 'afvf' ) . ' : ' . esc_html( $price ) . '</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"className":"afvf-course-rise-card__meta afvf-course-rise-card__meta--content"} -->
<p class="afvf-course-rise-card__meta afvf-course-rise-card__meta--content">' . esc_html__( 'Contenu', 'afvf' ) . ' : ' . wp_kses_post( $content ) . '</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"className":"afvf-course-rise-card__more-wrap"} -->
<p class="afvf-course-rise-card__more-wrap"><a class="afvf-course-rise-card__more" href="' . esc_url( $more_href ) . '">' . esc_html__( 'En savoir +', 'afvf' ) . '</a></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:group -->';
}

/**
 * Carte lecteur podcast : vignette + titre + audio (lecteur personnalisé sur le site public).
 *
 * @param string $name Titre de l’épisode.
 * @param string $by   Intervenant(e) (affiché après un tiret).
 * @return string
 */
function afvf_pattern_podcast_card( $name, $by ) {
	return '<!-- wp:group {"className":"afvf-podcast-card","layout":{"type":"default"}} -->
<div class="wp-block-group afvf-podcast-card"><!-- wp:image {"sizeSlug":"thumbnail","linkDestination":"none","className":"afvf-podcast-card__thumb"} -->
<figure class="wp-block-image size-thumbnail afvf-podcast-card__thumb"><img alt=""/></figure>
<!-- /wp:image -->

<!-- wp:group {"className":"afvf-podcast-card__body","layout":{"type":"default"}} -->
<div class="wp-block-group afvf-podcast-card__body"><!-- wp:paragraph {"className":"afvf-podcast-card__name"} -->
<p class="afvf-podcast-card__name">' . esc_html( $name ) . ' <span class="afvf-podcast-card__by">- ' . esc_html( $by ) . '</span></p>
<!-- /wp:paragraph -->

<!-- wp:audio {"className":"afvf-podcast-card__audio"} /--></div>
<!-- /wp:group --></div>
<!-- /wp:group -->';
}

/**
 * Colonne « Mobiliser » : photo ronde, libellé, bouton navy « entrez ».
 *
 * @param string $label Libellé (déjà traduit).
 * @param string $href  URL du bouton.
 * @return string
 */
function afvf_pattern_mobiliser_card( $label, $href ) {
	$label_esc = esc_html( $label );
	$href_esc  = esc_url( $href );
	return '<!-- wp:column -->
<div class="wp-block-column"><!-- wp:group {"className":"afvf-mobiliser-card","layout":{"type":"default"}} -->
<div class="wp-block-group afvf-mobiliser-card"><!-- wp:image {"sizeSlug":"large","linkDestination":"none","className":"afvf-mobiliser-card__photo"} -->
<figure class="wp-block-image size-large afvf-mobiliser-card__photo"><img alt=""/></figure>
<!-- /wp:image -->

<!-- wp:paragraph {"align":"center","className":"afvf-mobiliser-card__label"} -->
<p class="has-text-align-center afvf-mobiliser-card__label">' . $label_esc . '</p>
<!-- /wp:paragraph -->

<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
<div class="wp-block-buttons"><!-- wp:button {"className":"afvf-btn-navy-pill"} -->
<div class="wp-block-button afvf-btn-navy-pill"><a class="wp-block-button__link wp-element-button" href="' . $href_esc . '">' . esc_html__( 'entrez', 'afvf' ) . '</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group --></div>
<!-- /wp:column -->';
}

function afvf_register_block_patterns() {
	if ( ! function_exists( 'register_block_pattern' ) ) {
		return;
	}

	register_block_pattern_category(
		'afvf',
		array(
			'label' => __( 'AFVF', 'afvf' ),
		)
	);

	// Positions « Wix » : logo, phrase puis hashtag alignés à gauche, comme sur le site d’origine.
	$hh_logo  = esc_attr( afvf_pattern_layer_classes( 'afvf-wix-hero-logo', '3%', '22%' ) );
	$hh_quote = esc_attr( afvf_pattern_layer_classes( 'afvf-wix-hero-quote', '41%', '22%' ) );
	$hh_tag   = esc_attr( afvf_pattern_layer_classes( 'afvf-wix-hero-hashtag', '53%', '22%' ) );

	register_block_pattern(
		'afvf/home-hero-fixed-bg',
		array(
			'title'       => __( 'Accueil — Hero fond fixe (GIF / vidéo, type Wix)', 'afvf' ),
			'description' => _x( 'Logo, phrase et hashtag par-dessus un fond fixe. Pour la vidéo : sélectionnez la Couverture puis cliquez sur « Vidéo » dans la barre du bloc. Chaque élément se déplace avec « Déplacer ».', 'Block pattern description', 'afvf' ),
			'categories'  => array( 'afvf', 'banner' ),
			'content'     => '<!-- wp:cover {"dimRatio":0,"minHeight":720,"minHeightUnit":"px","align":"full","className":"afvf-wix-fixed-media afvf-banner-canvas is-style-afvf-banner-canvas","style":{"spacing":{"padding":{"top":"1rem","bottom":"2rem","left":"1.25rem","right":"1.25rem"}}}} -->
<div class="wp-block-cover alignfull afvf-wix-fixed-media afvf-banner-canvas is-style-afvf-banner-canvas" style="padding-top:1rem;padding-right:1.25rem;padding-bottom:2rem;padding-left:1.25rem;min-height:720px"><span aria-hidden="true" class="wp-block-cover__background has-background-dim-0 has-background-dim"></span><div class="wp-block-cover__inner-container"><!-- wp:image {"sizeSlug":"large","linkDestination":"none","className":"' . $hh_logo . '"} -->
<figure class="wp-block-image size-large ' . $hh_logo . '"><img alt="' . esc_attr__( 'AFVF', 'afvf' ) . '"/></figure>
<!-- /wp:image -->

<!-- wp:paragraph {"className":"' . $hh_quote . '"} -->
<p class="' . $hh_quote . '">' . esc_html__( "Elles s'appelaient Sandra, Julie, Aïssatou… Texte d’accroche sous le logo.", 'afvf' ) . '</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"className":"' . $hh_tag . '"} -->
<p class="' . $hh_tag . '">#ARRETONSLESFEMINICIDES</p>
<!-- /wp:paragraph --></div></div>
<!-- /wp:cover -->',
		)
	);

	$entreprises_hero_bg = esc_url( get_template_directory_uri() . '/assets/images/hero-entreprises-institutions.png' );

	register_block_pattern(
		'afvf/hero-entreprises-institutions',
		array(
			'title'       => __( 'Entreprises & Institutions — hero (maquette Wix)', 'afvf' ),
			'description' => _x( 'Photo de réunion + dégradé blanc à gauche + carte blanche centrée. Même rendu éditeur / public. Ne pas utiliser « placement libre ». Si WordPress affiche « Composition » : cliquez le hero → « Sortir de la composition », puis Enregistrer.', 'Block pattern description', 'afvf' ),
			'categories'  => array( 'afvf', 'banner' ),
			'content'     => '<!-- wp:cover {"url":"' . $entreprises_hero_bg . '","dimRatio":0,"minHeight":560,"minHeightUnit":"px","align":"full","className":"afvf-wix-photo-card afvf-hero-entreprises","style":{"spacing":{"padding":{"top":"2.5rem","bottom":"2.5rem","left":"1.25rem","right":"1.25rem"}}}} -->
<div class="wp-block-cover alignfull afvf-wix-photo-card afvf-hero-entreprises" style="padding-top:2.5rem;padding-right:1.25rem;padding-bottom:2.5rem;padding-left:1.25rem;min-height:560px"><img class="wp-block-cover__image-background" alt="" src="' . $entreprises_hero_bg . '" data-object-fit="cover"/><span aria-hidden="true" class="wp-block-cover__background has-background-dim-0 has-background-dim"></span><div class="wp-block-cover__inner-container"><!-- wp:group {"className":"afvf-wix-overlay-card","backgroundColor":"white","style":{"spacing":{"padding":{"top":"2.25rem","bottom":"2rem","left":"2rem","right":"2rem"}}},"layout":{"type":"constrained","contentSize":"920px"}} -->
<div class="wp-block-group afvf-wix-overlay-card is-style-afvf-hero-card has-white-background-color has-background"><!-- wp:heading {"textAlign":"center","level":1,"className":"afvf-wix-overlay-card__title afvf-hero-entreprises-title"} -->
<h1 class="wp-block-heading has-text-align-center afvf-wix-overlay-card__title afvf-hero-entreprises-title">' . esc_html__( 'ENTREPRISES & INSTITUTIONS', 'afvf' ) . '<br>' . esc_html__( 'ENGAGEZ-VOUS !', 'afvf' ) . '</h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center"} -->
<p class="has-text-align-center">' . esc_html__( 'Menons les choses ensemble.', 'afvf' ) . '</p>
<!-- /wp:paragraph -->

<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center","flexWrap":"wrap"},"style":{"spacing":{"blockGap":"0.75rem","margin":{"top":"1.5rem"}}},"className":"afvf-wix-overlay-card__buttons"} -->
<div class="wp-block-buttons afvf-wix-overlay-card__buttons" style="margin-top:1.5rem"><!-- wp:button {"className":"afvf-wix-btn-pink","style":{"border":{"radius":"0px"},"color":{"background":"#ec5e87","text":"#ffffff"}}} -->
<div class="wp-block-button afvf-wix-btn-pink"><a class="wp-block-button__link wp-element-button has-text-color has-background" style="border-radius:0px;background-color:#ec5e87;color:#ffffff" href="#">' . esc_html__( 'Mobiliser…', 'afvf' ) . '</a></div>
<!-- /wp:button -->

<!-- wp:button {"className":"afvf-wix-btn-pink","style":{"border":{"radius":"0px"},"color":{"background":"#ec5e87","text":"#ffffff"}}} -->
<div class="wp-block-button afvf-wix-btn-pink"><a class="wp-block-button__link wp-element-button has-text-color has-background" style="border-radius:0px;background-color:#ec5e87;color:#ffffff" href="#">' . esc_html__( 'Kër Aïcha', 'afvf' ) . '</a></div>
<!-- /wp:button -->

<!-- wp:button {"className":"afvf-wix-btn-pink","style":{"border":{"radius":"0px"},"color":{"background":"#ec5e87","text":"#ffffff"}}} -->
<div class="wp-block-button afvf-wix-btn-pink"><a class="wp-block-button__link wp-element-button has-text-color has-background" style="border-radius:0px;background-color:#ec5e87;color:#ffffff" href="#">' . esc_html__( 'Locaux AFVF', 'afvf' ) . '</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group --></div></div>
<!-- /wp:cover -->',
		)
	);

	register_block_pattern(
		'afvf/photo-with-white-card',
		array(
			'title'       => __( 'Photo + carte blanche au centre (type Wix)', 'afvf' ),
			'description' => _x( 'Image de fond visible (sans voile). Carte blanche avec titre, texte et 3 boutons roses. Cliquez la Couverture → image, puis modifiez le texte.', 'Block pattern description', 'afvf' ),
			'categories'  => array( 'afvf', 'banner' ),
			'content'     => '<!-- wp:cover {"dimRatio":0,"minHeight":520,"minHeightUnit":"px","align":"full","className":"afvf-wix-photo-card","style":{"spacing":{"padding":{"top":"2.5rem","bottom":"2.5rem","left":"1.25rem","right":"1.25rem"}}}} -->
<div class="wp-block-cover alignfull afvf-wix-photo-card" style="padding-top:2.5rem;padding-right:1.25rem;padding-bottom:2.5rem;padding-left:1.25rem;min-height:520px"><span aria-hidden="true" class="wp-block-cover__background has-background-dim-0 has-background-dim"></span><div class="wp-block-cover__inner-container"><!-- wp:group {"className":"afvf-wix-overlay-card","backgroundColor":"white","style":{"spacing":{"padding":{"top":"2.25rem","bottom":"2rem","left":"2rem","right":"2rem"}}},"layout":{"type":"constrained","contentSize":"920px"}} -->
<div class="wp-block-group afvf-wix-overlay-card is-style-afvf-hero-card has-white-background-color has-background"><!-- wp:heading {"textAlign":"center","level":1,"className":"afvf-wix-overlay-card__title"} -->
<h1 class="wp-block-heading has-text-align-center afvf-wix-overlay-card__title">' . esc_html__( 'ORGANISATION', 'afvf' ) . '</h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center"} -->
<p class="has-text-align-center">' . esc_html__( 'Agir avec force, unir nos talents, accueillir vos engagements.', 'afvf' ) . '</p>
<!-- /wp:paragraph -->

<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center","flexWrap":"wrap"},"style":{"spacing":{"blockGap":"0.75rem","margin":{"top":"1.5rem"}}},"className":"afvf-wix-overlay-card__buttons"} -->
<div class="wp-block-buttons afvf-wix-overlay-card__buttons" style="margin-top:1.5rem"><!-- wp:button {"className":"afvf-wix-btn-pink","style":{"border":{"radius":"0px"},"color":{"background":"#ec5e87","text":"#ffffff"}}} -->
<div class="wp-block-button afvf-wix-btn-pink"><a class="wp-block-button__link wp-element-button has-text-color has-background" style="border-radius:0px;background-color:#ec5e87;color:#ffffff">' . esc_html__( 'Nos pôles d’actions', 'afvf' ) . '</a></div>
<!-- /wp:button -->

<!-- wp:button {"className":"afvf-wix-btn-pink","style":{"border":{"radius":"0px"},"color":{"background":"#ec5e87","text":"#ffffff"}}} -->
<div class="wp-block-button afvf-wix-btn-pink"><a class="wp-block-button__link wp-element-button has-text-color has-background" style="border-radius:0px;background-color:#ec5e87;color:#ffffff">' . esc_html__( 'Équipes', 'afvf' ) . '</a></div>
<!-- /wp:button -->

<!-- wp:button {"className":"afvf-wix-btn-pink","style":{"border":{"radius":"0px"},"color":{"background":"#ec5e87","text":"#ffffff"}}} -->
<div class="wp-block-button afvf-wix-btn-pink"><a class="wp-block-button__link wp-element-button has-text-color has-background" style="border-radius:0px;background-color:#ec5e87;color:#ffffff">' . esc_html__( 'Rejoignez-nous', 'afvf' ) . '</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group --></div></div>
<!-- /wp:cover -->',
		)
	);

	register_block_pattern(
		'afvf/banner-maquette-accueil',
		array(
			'title'       => __( 'Bannière — maquette accueil (logo, titre, ENTREZ, QR)', 'afvf' ),
			'description' => _x( 'Photo de fond, logo, texte, ENTREZ, image QR. Insérez le motif puis évitez la composition synchronisée (Sortir de la composition) pour déplacer et enregistrer.', 'Block pattern description', 'afvf' ),
			'categories'  => array( 'afvf', 'banner' ),
			'content'     => '<!-- wp:cover {"dimRatio":0,"minHeight":680,"minHeightUnit":"px","align":"full","className":"afvf-banner-canvas is-style-afvf-banner-canvas","style":{"spacing":{"padding":{"top":"0","bottom":"0","left":"0","right":"0"}}}} -->
<div class="wp-block-cover alignfull afvf-banner-canvas is-style-afvf-banner-canvas" style="padding-top:0;padding-right:0;padding-bottom:0;padding-left:0;min-height:680px"><span aria-hidden="true" class="wp-block-cover__background has-background-dim-0 has-background-dim"></span><div class="wp-block-cover__inner-container"><!-- wp:image {"sizeSlug":"large","linkDestination":"none","className":"' . esc_attr( afvf_pattern_layer_classes( 'afvf-wix-hero-logo', '10%', '3%' ) ) . '"} -->
<figure class="wp-block-image size-large ' . esc_attr( afvf_pattern_layer_classes( 'afvf-wix-hero-logo', '10%', '3%' ) ) . '"><img alt="' . esc_attr__( 'Logo AFVF', 'afvf' ) . '"/></figure>
<!-- /wp:image -->

<!-- wp:group {"className":"' . esc_attr( afvf_pattern_layer_classes( 'afvf-banner-wix-lines', '10%', '48%' ) ) . '","layout":{"type":"default"}} -->
<div class="wp-block-group ' . esc_attr( afvf_pattern_layer_classes( 'afvf-banner-wix-lines', '10%', '48%' ) ) . '"><!-- wp:paragraph {"align":"right","className":"afvf-wl-1"} -->
<p class="has-text-align-right afvf-wl-1">' . esc_html__( 'ASSOCIATION', 'afvf' ) . '</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"align":"right","className":"afvf-wl-2"} -->
<p class="has-text-align-right afvf-wl-2"><mark style="background-color:rgba(0, 0, 0, 0);color:#f6a5b7" class="has-inline-color">' . esc_html__( 'Des', 'afvf' ) . '</mark> ' . esc_html__( 'FAMILLES', 'afvf' ) . '</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"align":"right","className":"afvf-wl-3"} -->
<p class="has-text-align-right afvf-wl-3"><mark style="background-color:rgba(0, 0, 0, 0);color:#f6a5b7" class="has-inline-color">' . esc_html__( 'De', 'afvf' ) . '</mark> ' . esc_html__( 'VICTIMES', 'afvf' ) . '</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"align":"right","className":"afvf-wl-4"} -->
<p class="has-text-align-right afvf-wl-4"><mark style="background-color:rgba(0, 0, 0, 0);color:#f6a5b7" class="has-inline-color">' . esc_html__( 'De', 'afvf' ) . '</mark> ' . esc_html__( 'FEMINICIDES', 'afvf' ) . '</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:buttons {"className":"' . esc_attr( afvf_pattern_layer_classes( 'afvf-banner-entrez', '52%', '', '6%' ) ) . '","layout":{"type":"flex","justifyContent":"right"}} -->
<div class="wp-block-buttons ' . esc_attr( afvf_pattern_layer_classes( 'afvf-banner-entrez', '52%', '', '6%' ) ) . '"><!-- wp:button {"className":"afvf-wix-btn-pink","style":{"border":{"radius":"0px"},"color":{"background":"#ec5e87","text":"#ffffff"}}} -->
<div class="wp-block-button afvf-wix-btn-pink"><a class="wp-block-button__link wp-element-button has-text-color has-background" style="border-radius:0px;background-color:#ec5e87;color:#ffffff">' . esc_html__( 'ENTREZ', 'afvf' ) . '</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons -->

<!-- wp:image {"sizeSlug":"full","linkDestination":"none","className":"' . esc_attr( afvf_pattern_layer_classes( 'afvf-banner-qr-image', '64%', '', '6%' ) ) . '"} -->
<figure class="wp-block-image size-full ' . esc_attr( afvf_pattern_layer_classes( 'afvf-banner-qr-image', '64%', '', '6%' ) ) . '"><img alt="' . esc_attr__( 'QR code', 'afvf' ) . '"/></figure>
<!-- /wp:image --></div></div>
<!-- /wp:cover -->',
		)
	);

	register_block_pattern(
		'afvf/banner-free-layers',
		array(
			'title'       => __( 'Bannière — placement libre (glisser texte, image, boutons)', 'afvf' ),
			'description' => _x( 'Calques déplaçables (poignée Déplacer). Après insertion : ne pas laisser en composition synchronisée — cliquez la bannière puis « Sortir de la composition » avant d’enregistrer.', 'Block pattern description', 'afvf' ),
			'categories'  => array( 'afvf', 'banner' ),
			'content'     => '<!-- wp:cover {"dimRatio":0,"minHeight":560,"minHeightUnit":"px","align":"full","className":"afvf-banner-canvas is-style-afvf-banner-canvas","style":{"spacing":{"padding":{"top":"0","bottom":"0","left":"0","right":"0"}}}} -->
<div class="wp-block-cover alignfull afvf-banner-canvas is-style-afvf-banner-canvas" style="padding-top:0;padding-right:0;padding-bottom:0;padding-left:0;min-height:560px"><span aria-hidden="true" class="wp-block-cover__background has-background-dim-0 has-background-dim"></span><div class="wp-block-cover__inner-container"><!-- wp:heading {"level":1,"className":"' . esc_attr( afvf_pattern_layer_classes( 'afvf-wix-overlay-card__title', '18%', '8%' ) ) . '"} -->
<h1 class="wp-block-heading ' . esc_attr( afvf_pattern_layer_classes( 'afvf-wix-overlay-card__title', '18%', '8%' ) ) . '">' . esc_html__( 'ORGANISATION', 'afvf' ) . '</h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"' . esc_attr( afvf_pattern_layer_classes( 'afvf-banner-lead', '32%', '8%' ) ) . '"} -->
<p class="' . esc_attr( afvf_pattern_layer_classes( 'afvf-banner-lead', '32%', '8%' ) ) . '">' . esc_html__( 'Agir avec force, unir nos talents, accueillir vos engagements.', 'afvf' ) . '</p>
<!-- /wp:paragraph -->

<!-- wp:buttons {"layout":{"type":"flex","flexWrap":"wrap"},"style":{"spacing":{"blockGap":"0.75rem"}},"className":"' . esc_attr( afvf_pattern_layer_classes( 'afvf-wix-overlay-card__buttons', '72%', '8%' ) ) . '"} -->
<div class="wp-block-buttons ' . esc_attr( afvf_pattern_layer_classes( 'afvf-wix-overlay-card__buttons', '72%', '8%' ) ) . '"><!-- wp:button {"className":"afvf-wix-btn-pink","style":{"border":{"radius":"0px"},"color":{"background":"#ec5e87","text":"#ffffff"}}} -->
<div class="wp-block-button afvf-wix-btn-pink"><a class="wp-block-button__link wp-element-button has-text-color has-background" style="border-radius:0px;background-color:#ec5e87;color:#ffffff">' . esc_html__( 'Nos pôles d’actions', 'afvf' ) . '</a></div>
<!-- /wp:button -->

<!-- wp:button {"className":"afvf-wix-btn-pink","style":{"border":{"radius":"0px"},"color":{"background":"#ec5e87","text":"#ffffff"}}} -->
<div class="wp-block-button afvf-wix-btn-pink"><a class="wp-block-button__link wp-element-button has-text-color has-background" style="border-radius:0px;background-color:#ec5e87;color:#ffffff">' . esc_html__( 'Équipes', 'afvf' ) . '</a></div>
<!-- /wp:button -->

<!-- wp:button {"className":"afvf-wix-btn-pink","style":{"border":{"radius":"0px"},"color":{"background":"#ec5e87","text":"#ffffff"}}} -->
<div class="wp-block-button afvf-wix-btn-pink"><a class="wp-block-button__link wp-element-button has-text-color has-background" style="border-radius:0px;background-color:#ec5e87;color:#ffffff">' . esc_html__( 'Rejoignez-nous', 'afvf' ) . '</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons -->

<!-- wp:image {"sizeSlug":"large","linkDestination":"none","className":"' . esc_attr( afvf_pattern_layer_classes( 'afvf-banner-side-image', '20%', '62%' ) ) . '"} -->
<figure class="wp-block-image size-large ' . esc_attr( afvf_pattern_layer_classes( 'afvf-banner-side-image', '20%', '62%' ) ) . '"><img alt=""/></figure>
<!-- /wp:image --></div></div>
<!-- /wp:cover -->',
		)
	);

	$assises_cta     = afvf_pattern_layer_classes( 'afvf-assises-cta', '58%', '5%' );
	$assises_poster  = afvf_pattern_layer_classes( 'afvf-assises-poster', '6%', '', '4%' );
	$assises_cta_txt = esc_html__( 'Téléchargez le', 'afvf' );
	$assises_prog    = esc_html__( 'Programme', 'afvf' );

	register_block_pattern(
		'afvf/banner-assises-maquette',
		array(
			'title'       => __( 'Assises — bannière maquette (CTA + affiche)', 'afvf' ),
			'description' => _x( 'Comme la maquette : photo de fond (mains), « Téléchargez le Programme » à gauche, affiche carrée à droite. Ajoutez votre photo de fond, l’image affiche et le lien sur le texte. Pas de composition synchronisée.', 'Block pattern description', 'afvf' ),
			'categories'  => array( 'afvf', 'banner' ),
			'content'     => '<!-- wp:cover {"dimRatio":0,"minHeight":620,"minHeightUnit":"px","align":"full","className":"afvf-banner-canvas is-style-afvf-banner-canvas afvf-banner-assises","style":{"spacing":{"padding":{"top":"0","bottom":"0","left":"0","right":"0"}}}} -->
<div class="wp-block-cover alignfull afvf-banner-canvas is-style-afvf-banner-canvas afvf-banner-assises" style="padding-top:0;padding-right:0;padding-bottom:0;padding-left:0;min-height:620px"><span aria-hidden="true" class="wp-block-cover__background has-background-dim-0 has-background-dim"></span><div class="wp-block-cover__inner-container"><!-- wp:paragraph {"className":"' . esc_attr( $assises_cta ) . '"} -->
<p class="' . esc_attr( $assises_cta ) . '"><a href="#">' . $assises_cta_txt . ' <mark style="background-color:rgba(0, 0, 0, 0);color:#ec5e87" class="has-inline-color afvf-assises-programme">' . $assises_prog . '</mark></a></p>
<!-- /wp:paragraph -->

<!-- wp:image {"sizeSlug":"full","linkDestination":"none","className":"' . esc_attr( $assises_poster ) . '"} -->
<figure class="wp-block-image size-full ' . esc_attr( $assises_poster ) . '"><img alt="' . esc_attr__( 'Affiche Assises nationales', 'afvf' ) . '"/></figure>
<!-- /wp:image --></div></div>
<!-- /wp:cover -->',
		)
	);

	register_block_pattern(
		'afvf/banner-page-poster',
		array(
			'title'       => __( 'Bannière page — fond + affiche seule', 'afvf' ),
			'description' => _x( 'Version simple : uniquement l’affiche à droite. Pour la maquette complète avec « Téléchargez le Programme », utilisez « Assises — bannière maquette ».', 'Block pattern description', 'afvf' ),
			'categories'  => array( 'afvf', 'banner' ),
			'content'     => '<!-- wp:cover {"dimRatio":0,"minHeight":520,"minHeightUnit":"px","align":"full","className":"afvf-banner-canvas is-style-afvf-banner-canvas afvf-banner-assises","style":{"spacing":{"padding":{"top":"0","bottom":"0","left":"0","right":"0"}}}} -->
<div class="wp-block-cover alignfull afvf-banner-canvas is-style-afvf-banner-canvas afvf-banner-assises" style="padding-top:0;padding-right:0;padding-bottom:0;padding-left:0;min-height:520px"><span aria-hidden="true" class="wp-block-cover__background has-background-dim-0 has-background-dim"></span><div class="wp-block-cover__inner-container"><!-- wp:image {"sizeSlug":"full","linkDestination":"none","className":"' . esc_attr( afvf_pattern_layer_classes( 'afvf-assises-poster', '6%', '', '4%' ) ) . '"} -->
<figure class="wp-block-image size-full ' . esc_attr( afvf_pattern_layer_classes( 'afvf-assises-poster', '6%', '', '4%' ) ) . '"><img alt="' . esc_attr__( 'Affiche', 'afvf' ) . '"/></figure>
<!-- /wp:image --></div></div>
<!-- /wp:cover -->',
		)
	);

	$dons_title   = afvf_pattern_layer_classes( 'afvf-dons-title', '13%', '50%' );
	$dons_video   = afvf_pattern_layer_classes( 'afvf-dons-video', '27%', '50%' );
	$dons_tagline = afvf_pattern_layer_classes( 'afvf-dons-tagline', '82%', '20%' );

	register_block_pattern(
		'afvf/banner-dons-legs',
		array(
			'title'       => __( 'Dons et Legs — bannière (photo + vidéo + titre dégradé)', 'afvf' ),
			'description' => _x( 'Photo noir & blanc en fond (à ajouter dans la Couverture), titre « DONS et LEGS » en dégradé rose/violet, vidéo à droite, bandeau bleu « Donnez-Léguez-Agissez ». Chaque élément se déplace avec « Déplacer ». Pas de composition synchronisée.', 'Block pattern description', 'afvf' ),
			'categories'  => array( 'afvf', 'banner' ),
			'content'     => '<!-- wp:cover {"dimRatio":0,"minHeight":520,"minHeightUnit":"px","align":"full","className":"afvf-banner-canvas is-style-afvf-banner-canvas afvf-banner-dons","style":{"spacing":{"padding":{"top":"0","bottom":"0","left":"0","right":"0"}}}} -->
<div class="wp-block-cover alignfull afvf-banner-canvas is-style-afvf-banner-canvas afvf-banner-dons" style="padding-top:0;padding-right:0;padding-bottom:0;padding-left:0;min-height:520px"><span aria-hidden="true" class="wp-block-cover__background has-background-dim-0 has-background-dim"></span><div class="wp-block-cover__inner-container"><!-- wp:heading {"level":1,"className":"' . esc_attr( $dons_title ) . '"} -->
<h1 class="wp-block-heading ' . esc_attr( $dons_title ) . '">' . esc_html__( 'DONS et LEGS', 'afvf' ) . '</h1>
<!-- /wp:heading -->

<!-- wp:video {"className":"' . esc_attr( $dons_video ) . '"} -->
<figure class="wp-block-video ' . esc_attr( $dons_video ) . '"></figure>
<!-- /wp:video -->

<!-- wp:paragraph {"className":"' . esc_attr( $dons_tagline ) . '"} -->
<p class="' . esc_attr( $dons_tagline ) . '">' . esc_html__( 'Donnez-Léguez-Agissez', 'afvf' ) . '</p>
<!-- /wp:paragraph --></div></div>
<!-- /wp:cover -->',
		)
	);

	register_block_pattern(
		'afvf/home-hero-cover',
		array(
			'title'       => __( 'Accueil — Hero (image de couverture)', 'afvf' ),
			'description' => _x( 'Grande bannière avec image, logo ou titre et texte.', 'Block pattern description', 'afvf' ),
			'categories'  => array( 'afvf', 'banner' ),
			'content'     => '<!-- wp:cover {"url":"","dimRatio":50,"minHeight":480,"minHeightUnit":"px","align":"full","style":{"spacing":{"padding":{"top":"3rem","bottom":"3rem","left":"1.25rem","right":"1.25rem"}}}} -->
<div class="wp-block-cover alignfull" style="padding-top:3rem;padding-right:1.25rem;padding-bottom:3rem;padding-left:1.25rem;min-height:480px"><span aria-hidden="true" class="wp-block-cover__background has-background-dim"></span><div class="wp-block-cover__inner-container"><!-- wp:heading {"level":1,"textColor":"white","fontSize":"x-large"} -->
<h1 class="wp-block-heading has-white-color has-text-color has-x-large-font-size">AFVF</h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"textColor":"white","fontSize":"medium"} -->
<p class="has-white-color has-text-color has-medium-font-size">' . esc_html__( 'Familles de victimes de féminicides — texte d’accroche de votre page d’accueil.', 'afvf' ) . '</p>
<!-- /wp:paragraph --></div></div>
<!-- /wp:cover -->',
		)
	);

	register_block_pattern(
		'afvf/home-three-columns',
		array(
			'title'       => __( 'Accueil — 3 colonnes (accès rapide)', 'afvf' ),
			'description' => _x( 'Trois blocs côte à côte, comme les tuiles sous le hero.', 'Block pattern description', 'afvf' ),
			'categories'  => array( 'afvf', 'columns' ),
			'content'     => '<!-- wp:columns {"align":"full","style":{"spacing":{"padding":{"top":"0","bottom":"0","left":"0","right":"0"}}}} -->
<div class="wp-block-columns alignfull"><!-- wp:column {"style":{"color":{"background":"#e95c87"},"spacing":{"padding":{"top":"2rem","bottom":"2rem","left":"1.25rem","right":"1.25rem"}}}} -->
<div class="wp-block-column has-background" style="background-color:#e95c87;padding-top:2rem;padding-right:1.25rem;padding-bottom:2rem;padding-left:1.25rem"><!-- wp:heading {"level":3,"textColor":"white"} -->
<h3 class="wp-block-heading has-white-color has-text-color">' . esc_html__( 'Mission', 'afvf' ) . '</h3>
<!-- /wp:heading -->

<!-- wp:paragraph {"textColor":"white"} -->
<p class="has-white-color has-text-color">' . esc_html__( 'Principes, vision et engagements', 'afvf' ) . '</p>
<!-- /wp:paragraph --></div>
<!-- /wp:column -->

<!-- wp:column {"style":{"color":{"background":"#7eb8e8"},"spacing":{"padding":{"top":"2rem","bottom":"2rem","left":"1.25rem","right":"1.25rem"}}}} -->
<div class="wp-block-column has-background" style="background-color:#7eb8e8;padding-top:2rem;padding-right:1.25rem;padding-bottom:2rem;padding-left:1.25rem"><!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">' . esc_html__( 'Événements', 'afvf' ) . '</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>' . esc_html__( 'S’inscrire et contribuer', 'afvf' ) . '</p>
<!-- /wp:paragraph --></div>
<!-- /wp:column -->

<!-- wp:column {"style":{"color":{"background":"#1a3d8f"},"spacing":{"padding":{"top":"2rem","bottom":"2rem","left":"1.25rem","right":"1.25rem"}}}} -->
<div class="wp-block-column has-background" style="background-color:#1a3d8f;padding-top:2rem;padding-right:1.25rem;padding-bottom:2rem;padding-left:1.25rem"><!-- wp:heading {"level":3,"textColor":"white"} -->
<h3 class="wp-block-heading has-white-color has-text-color">' . esc_html__( 'S’engager', 'afvf' ) . '</h3>
<!-- /wp:heading -->

<!-- wp:paragraph {"textColor":"white"} -->
<p class="has-white-color has-text-color">' . esc_html__( 'Bénévolat, adhésion, don', 'afvf' ) . '</p>
<!-- /wp:paragraph --></div>
<!-- /wp:column --></div>
<!-- /wp:columns -->',
		)
	);

	$s1 = esc_html__( 'Rendez-vous d’information en ligne', 'afvf' );
	$s2 = esc_html__( 'Sensibilisation aux VSS et féminicides', 'afvf' );
	$s3 = esc_html__( 'Cours individuel « Écouter est un art »', 'afvf' );

	register_block_pattern(
		'afvf/services-book-online',
		array(
			'title'       => __( 'Services en ligne — catalogue + Réserver (3 cartes)', 'afvf' ),
			'description' => _x( 'Page type « Book Online » Wix : 3 services avec bouton Réserver. Les liens Réserver pointent vers vos pages de réservation (plugin Amelia, Simply Schedule Appointments, etc.). Voir SERVICES-RESERVATION.txt.', 'Block pattern description', 'afvf' ),
			'categories'  => array( 'afvf', 'columns' ),
			'content'     => '<!-- wp:group {"className":"afvf-services-catalog","layout":{"type":"constrained","contentSize":"1100px"}} -->
<div class="wp-block-group afvf-services-catalog"><!-- wp:group {"className":"afvf-services-catalog__title","layout":{"type":"constrained"}} -->
<div class="wp-block-group afvf-services-catalog__title"><!-- wp:paragraph {"align":"center","className":"afvf-services-catalog__kicker"} -->
<p class="has-text-align-center afvf-services-catalog__kicker">' . esc_html__( 'Nos services', 'afvf' ) . '</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"textAlign":"center","level":2,"className":"afvf-services-catalog__headline"} -->
<h2 class="wp-block-heading has-text-align-center afvf-services-catalog__headline">' . esc_html__( 'POUR LES PROFESSIONNELS', 'afvf' ) . '</h2>
<!-- /wp:heading --></div>
<!-- /wp:group -->

<!-- wp:columns {"style":{"spacing":{"blockGap":{"left":"1.25rem"}}}} -->
<div class="wp-block-columns"><!-- wp:column -->
<div class="wp-block-column"><!-- wp:group {"className":"afvf-service-card","layout":{"type":"default"}} -->
<div class="wp-block-group afvf-service-card"><!-- wp:image {"sizeSlug":"large","linkDestination":"none"} -->
<figure class="wp-block-image size-large"><img alt=""/></figure>
<!-- /wp:image -->

<!-- wp:group {"className":"afvf-service-card__body","layout":{"type":"default"}} -->
<div class="wp-block-group afvf-service-card__body"><!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">' . $s1 . '</h3>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"afvf-service-meta"} -->
<p class="afvf-service-meta">' . esc_html__( '15 min', 'afvf' ) . '<br>' . esc_html__( 'Gratuit', 'afvf' ) . '</p>
<!-- /wp:paragraph -->

<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button {"className":"afvf-wix-btn-pink","style":{"border":{"radius":"0px"},"color":{"background":"#ec5e87","text":"#ffffff"}}} -->
<div class="wp-block-button afvf-wix-btn-pink"><a class="wp-block-button__link has-text-color has-background wp-element-button" href="/book-online/rendez-vous-information/" style="border-radius:0px;background-color:#ec5e87;color:#ffffff">' . esc_html__( 'Réserver', 'afvf' ) . '</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group --></div>
<!-- /wp:group --></div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column"><!-- wp:group {"className":"afvf-service-card","layout":{"type":"default"}} -->
<div class="wp-block-group afvf-service-card"><!-- wp:image {"sizeSlug":"large","linkDestination":"none"} -->
<figure class="wp-block-image size-large"><img alt=""/></figure>
<!-- /wp:image -->

<!-- wp:group {"className":"afvf-service-card__body","layout":{"type":"default"}} -->
<div class="wp-block-group afvf-service-card__body"><!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">' . $s2 . '</h3>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"afvf-service-meta"} -->
<p class="afvf-service-meta">' . esc_html__( '14 h', 'afvf' ) . '<br>290 €</p>
<!-- /wp:paragraph -->

<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button {"className":"afvf-wix-btn-pink","style":{"border":{"radius":"0px"},"color":{"background":"#ec5e87","text":"#ffffff"}}} -->
<div class="wp-block-button afvf-wix-btn-pink"><a class="wp-block-button__link has-text-color has-background wp-element-button" href="/book-online/sensibilisation/" style="border-radius:0px;background-color:#ec5e87;color:#ffffff">' . esc_html__( 'Réserver', 'afvf' ) . '</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group --></div>
<!-- /wp:group --></div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column"><!-- wp:group {"className":"afvf-service-card","layout":{"type":"default"}} -->
<div class="wp-block-group afvf-service-card"><!-- wp:image {"sizeSlug":"large","linkDestination":"none"} -->
<figure class="wp-block-image size-large"><img alt=""/></figure>
<!-- /wp:image -->

<!-- wp:group {"className":"afvf-service-card__body","layout":{"type":"default"}} -->
<div class="wp-block-group afvf-service-card__body"><!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">' . $s3 . '</h3>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"afvf-service-meta"} -->
<p class="afvf-service-meta">' . esc_html__( '1 h', 'afvf' ) . '<br>35 €</p>
<!-- /wp:paragraph -->

<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button {"className":"afvf-wix-btn-pink","style":{"border":{"radius":"0px"},"color":{"background":"#ec5e87","text":"#ffffff"}}} -->
<div class="wp-block-button afvf-wix-btn-pink"><a class="wp-block-button__link has-text-color has-background wp-element-button" href="/book-online/cours-ecouter/" style="border-radius:0px;background-color:#ec5e87;color:#ffffff">' . esc_html__( 'Réserver', 'afvf' ) . '</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group --></div>
<!-- /wp:group --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->',
		)
	);

	$mobiliser_intro = esc_html__(
		'Les collectivités locales, les bailleurs sociaux, les fondations et l’État pour créer, rénover et flécher des places réellement accessibles aux femmes en danger et à leurs enfants. Des réponses concrètes pour accueillir, protéger et reconstruire.',
		'afvf'
	);
	$mobiliser_url   = home_url( '/mobiliser/' );
	$entreprises_url = home_url( '/entreprise-institution-engagez-vous/' );

	$mobiliser_grid = '<!-- wp:group {"align":"full","className":"afvf-mobiliser","style":{"spacing":{"padding":{"top":"2.5rem","bottom":"3rem","left":"1.25rem","right":"1.25rem"}}},"layout":{"type":"constrained","contentSize":"1100px"}} -->
<div class="wp-block-group alignfull afvf-mobiliser" style="padding-top:2.5rem;padding-right:1.25rem;padding-bottom:3rem;padding-left:1.25rem"><!-- wp:heading {"textAlign":"center","level":2,"className":"afvf-mobiliser__title"} -->
<h2 class="wp-block-heading has-text-align-center afvf-mobiliser__title">' . esc_html__( 'MOBILISER', 'afvf' ) . '</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center","className":"afvf-mobiliser__intro"} -->
<p class="has-text-align-center afvf-mobiliser__intro">' . $mobiliser_intro . '</p>
<!-- /wp:paragraph -->

<!-- wp:columns {"style":{"spacing":{"blockGap":{"left":"1.5rem"},"margin":{"top":"2.25rem"}}}} -->
<div class="wp-block-columns" style="margin-top:2.25rem">' . afvf_pattern_mobiliser_card( __( 'Bailleurs sociaux', 'afvf' ), $mobiliser_url ) . afvf_pattern_mobiliser_card( __( 'Entreprises', 'afvf' ), $entreprises_url ) . afvf_pattern_mobiliser_card( __( 'Collectivités locales', 'afvf' ), $mobiliser_url ) . afvf_pattern_mobiliser_card( __( 'Fondations', 'afvf' ), $mobiliser_url ) . '</div>
<!-- /wp:columns --></div>
<!-- /wp:group -->';

	$contact_ecoute = '<!-- wp:group {"align":"full","className":"afvf-contact-ecoute-band","style":{"spacing":{"padding":{"top":"2.5rem","bottom":"3rem","left":"1.25rem","right":"1.25rem"}},"color":{"background":"#ececec"}},"layout":{"type":"constrained","contentSize":"1100px"}} -->
<div class="wp-block-group alignfull afvf-contact-ecoute-band has-background" style="background-color:#ececec;padding-top:2.5rem;padding-right:1.25rem;padding-bottom:3rem;padding-left:1.25rem"><!-- wp:shortcode -->
[afvf_contact_form]
<!-- /wp:shortcode --></div>
<!-- /wp:group -->';

	register_block_pattern(
		'afvf/mobiliser-grid-wix',
		array(
			'title'       => __( 'Mobiliser — 4 colonnes (Wix)', 'afvf' ),
			'description' => _x( 'Titre rose, texte d’intro, 4 photos rondes et boutons « entrez ». Remplacez les images et les liens des boutons.', 'Block pattern description', 'afvf' ),
			'categories'  => array( 'afvf', 'columns' ),
			'content'     => $mobiliser_grid,
		)
	);

	register_block_pattern(
		'afvf/contact-ecoute-wix',
		array(
			'title'       => __( 'Contact — Nous sommes @ votre écoute (Wix)', 'afvf' ),
			'description' => _x( 'Formulaire Nom, Organisme, E-mail et bouton Envoyer. Messages dans Contact (admin WordPress).', 'Block pattern description', 'afvf' ),
			'categories'  => array( 'afvf', 'text' ),
			'content'     => $contact_ecoute,
		)
	);

	$collectivites_intro = esc_html__(
		'Les territoires peuvent devenir des lieux de refuge et de reconstruction pour les femmes victimes de violence. L’AFVF accompagne les élus et les services pour des réponses concrètes et durables.',
		'afvf'
	);
	$collectivites_band  = '<!-- wp:group {"align":"full","className":"afvf-collectivites-intro","style":{"spacing":{"padding":{"top":"3rem","bottom":"3rem","left":"1.25rem","right":"1.25rem"}},"color":{"background":"#d4dde8"}},"layout":{"type":"constrained","contentSize":"820px"}} -->
<div class="wp-block-group alignfull afvf-collectivites-intro has-background" style="background-color:#d4dde8;padding-top:3rem;padding-right:1.25rem;padding-bottom:3rem;padding-left:1.25rem"><!-- wp:heading {"textAlign":"center","level":2,"className":"afvf-collectivites-intro__title"} -->
<h2 class="wp-block-heading has-text-align-center afvf-collectivites-intro__title">' . esc_html__( 'COLLECTIVITES LOCAUX', 'afvf' ) . '</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center","className":"afvf-collectivites-intro__lead"} -->
<p class="has-text-align-center afvf-collectivites-intro__lead">' . $collectivites_intro . '</p>
<!-- /wp:paragraph -->

<!-- wp:list {"className":"afvf-collectivites-intro__actions"} -->
<ul class="afvf-collectivites-intro__actions"><li>' . esc_html__( 'Attribution de logements sociaux', 'afvf' ) . '</li><li>' . esc_html__( 'Mise à disposition de logements d’urgence', 'afvf' ) . '</li><li>' . esc_html__( 'Soutien financier', 'afvf' ) . '</li></ul>
<!-- /wp:list --></div>
<!-- /wp:group -->';

	register_block_pattern(
		'afvf/collectivites-locales-intro-wix',
		array(
			'title'       => __( 'Collectivités — bandeau intro (Wix)', 'afvf' ),
			'description' => _x( 'Fond lavande, titre COLLECTIVITES LOCAUX et liste d’actions. Texte modifiable dans l’éditeur.', 'Block pattern description', 'afvf' ),
			'categories'  => array( 'afvf', 'text' ),
			'content'     => $collectivites_band,
		)
	);

	register_block_pattern(
		'afvf/collectivites-newsletter-wix',
		array(
			'title'       => __( 'Collectivités — Newsletter (Rejoignez notre liste)', 'afvf' ),
			'description' => _x( 'Bandeau gris + formulaire newsletter seul. À placer sous le bandeau intro.', 'Block pattern description', 'afvf' ),
			'categories'  => array( 'afvf', 'text' ),
			'content'     => '<!-- wp:group {"align":"full","className":"afvf-collectivites-newsletter","style":{"spacing":{"padding":{"top":"2.5rem","bottom":"2.5rem","left":"1.25rem","right":"1.25rem"}},"color":{"background":"#ececec"}},"layout":{"type":"constrained","contentSize":"720px"}} -->
<div class="wp-block-group alignfull afvf-collectivites-newsletter has-background" style="background-color:#ececec;padding-top:2.5rem;padding-right:1.25rem;padding-bottom:2.5rem;padding-left:1.25rem"><!-- wp:shortcode -->
[afvf_newsletter_inscription]
<!-- /wp:shortcode --></div>
<!-- /wp:group -->',
		)
	);

	register_block_pattern(
		'afvf/social-inline-wix',
		array(
			'title'       => __( 'Réseaux sociaux — Facebook, Instagram, LinkedIn (centrés)', 'afvf' ),
			'description' => _x( 'Icônes seules (URLs = Customizer AFVF). Souvent sous la newsletter.', 'Block pattern description', 'afvf' ),
			'categories'  => array( 'afvf', 'text' ),
			'content'     => '<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"1rem","bottom":"2rem","left":"1.25rem","right":"1.25rem"}},"color":{"background":"#ececec"}},"layout":{"type":"constrained","contentSize":"720px"}} -->
<div class="wp-block-group alignfull has-background" style="background-color:#ececec;padding-top:1rem;padding-right:1.25rem;padding-bottom:2rem;padding-left:1.25rem"><!-- wp:shortcode -->
[afvf_social_inline]
<!-- /wp:shortcode --></div>
<!-- /wp:group -->',
		)
	);

	register_block_pattern(
		'afvf/newsletter-rejoignez-liste',
		array(
			'title'       => __( 'Newsletter — Rejoignez notre liste (Wix)', 'afvf' ),
			'description' => _x( 'Formulaire prénom, nom, e-mail et bouton rose. Les inscriptions vont dans Newsletter (admin WordPress).', 'Block pattern description', 'afvf' ),
			'categories'  => array( 'afvf', 'text' ),
			'content'     => '<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"2.5rem","bottom":"3.5rem","left":"1.25rem","right":"1.25rem"}},"color":{"background":"#ececec"}},"layout":{"type":"constrained","contentSize":"720px"}} -->
<div class="wp-block-group alignfull has-background" style="background-color:#ececec;padding-top:2.5rem;padding-right:1.25rem;padding-bottom:3.5rem;padding-left:1.25rem"><!-- wp:shortcode -->
[afvf_newsletter_inscription]
<!-- /wp:shortcode --></div>
<!-- /wp:group -->',
		)
	);

	register_block_pattern(
		'afvf/page-title-banner',
		array(
			'title'       => __( 'Page — Bandeau titre + intro', 'afvf' ),
			'description' => _x( 'Titre de page et chapô (remplace l’ancien bandeau du thème).', 'Block pattern description', 'afvf' ),
			'categories'  => array( 'afvf', 'banner' ),
			'content'     => '<!-- wp:cover {"dimRatio":60,"minHeight":220,"minHeightUnit":"px","align":"full"} -->
<div class="wp-block-cover alignfull" style="min-height:220px"><span aria-hidden="true" class="wp-block-cover__background has-background-dim-60 has-background-dim"></span><div class="wp-block-cover__inner-container"><!-- wp:heading {"level":1,"textColor":"white"} -->
<h1 class="wp-block-heading has-white-color has-text-color">' . esc_html__( 'Titre de la page', 'afvf' ) . '</h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"textColor":"white"} -->
<p class="has-white-color has-text-color">' . esc_html__( 'Courte introduction ou chapô.', 'afvf' ) . '</p>
<!-- /wp:paragraph --></div></div>
<!-- /wp:cover -->',
		)
	);

	$local_title = esc_html__( 'UN LOCAL pour l’AFVF', 'afvf' );
	$local_lead  = esc_html__(
		'Un lieu stratégique pour mieux accueillir, accompagner et coordonner',
		'afvf'
	);
	$local_hint  = esc_html__(
		'Remplacez la photo à gauche. La carte blanche chevauche l’image (type maquette Wix).',
		'afvf'
	);

	register_block_pattern(
		'afvf/local-afvf-offset',
		array(
			'title'       => __( 'Un local pour l’AFVF — photo + carte (Wix)', 'afvf' ),
			'description' => _x( 'Fond bleu, photo à gauche, carte blanche en bas à droite qui chevauche l’image. Texte modifiable.', 'Block pattern description', 'afvf' ),
			'categories'  => array( 'afvf', 'banner' ),
			'content'     => '<!-- wp:group {"align":"full","className":"afvf-local-offset","style":{"color":{"background":"#8eb5dc"},"spacing":{"padding":{"top":"2.75rem","bottom":"3.5rem","left":"1.25rem","right":"1.25rem"}}},"layout":{"type":"constrained","contentSize":"980px"}} -->
<div class="wp-block-group alignfull afvf-local-offset has-background" style="background-color:#8eb5dc;padding-top:2.75rem;padding-right:1.25rem;padding-bottom:3.5rem;padding-left:1.25rem"><!-- wp:paragraph {"align":"center","className":"afvf-pattern-hint"} -->
<p class="has-text-align-center afvf-pattern-hint">' . $local_hint . '</p>
<!-- /wp:paragraph -->

<!-- wp:group {"className":"afvf-local-offset__stage","layout":{"type":"default"}} -->
<div class="wp-block-group afvf-local-offset__stage"><!-- wp:image {"sizeSlug":"large","linkDestination":"none","className":"afvf-local-offset__photo"} -->
<figure class="wp-block-image size-large afvf-local-offset__photo"><img alt="' . esc_attr__( 'Équipe au bureau', 'afvf' ) . '"/></figure>
<!-- /wp:image -->

<!-- wp:group {"className":"afvf-local-offset__card","backgroundColor":"white","style":{"spacing":{"padding":{"top":"2rem","bottom":"2.25rem","left":"1.75rem","right":"1.75rem"}}},"layout":{"type":"constrained","contentSize":"420px"}} -->
<div class="wp-block-group afvf-local-offset__card has-white-background-color has-background" style="padding-top:2rem;padding-right:1.75rem;padding-bottom:2.25rem;padding-left:1.75rem"><!-- wp:heading {"textAlign":"center","level":2,"className":"afvf-local-offset__title"} -->
<h2 class="wp-block-heading has-text-align-center afvf-local-offset__title">' . $local_title . '</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center","className":"afvf-local-offset__lead"} -->
<p class="has-text-align-center afvf-local-offset__lead">' . $local_lead . '</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:group --></div>
<!-- /wp:group -->',
		)
	);

	$img_dir     = get_template_directory_uri() . '/assets/images/';
	$hover_cards = afvf_pattern_hover_split_card(
		'plaidoyer',
		__( 'PLAIDOYER & RELATION INSTITUTIONNEL', 'afvf' ),
		__( 'Faire entendre la voix des victimes et influencer le changement', 'afvf' ),
		home_url( '/plaidoyer/' ),
		$img_dir . 'hero-entreprises-institutions.png'
	) . "\n\n" . afvf_pattern_hover_split_card(
		'socio',
		__( 'SOCIO-PSYCHO-JURIDIQUE', 'afvf' ),
		__( 'Accompagner, protéger et reconstruire', 'afvf' ),
		home_url( '/socio-psycho-juridique/' ),
		$img_dir . 'adhere-banner-poster.png'
	) . "\n\n" . afvf_pattern_hover_split_card(
		'event',
		__( 'EVENEMENTIEL', 'afvf' ),
		__( 'Agir ensemble, sur le terrain et dans l’espace public', 'afvf' ),
		home_url( '/evenementiel/' )
	) . "\n\n" . afvf_pattern_hover_split_card(
		'finance',
		__( 'FINANCEMENT', 'afvf' ),
		__( 'Donner les moyens d’agir contre les violences', 'afvf' ),
		home_url( '/financement/' )
	) . "\n\n" . afvf_pattern_hover_split_card(
		'formation',
		__( 'FORMATION', 'afvf' ),
		__( 'Sensibiliser, former, prévenir', 'afvf' ),
		home_url( '/formation/' )
	) . "\n\n" . afvf_pattern_hover_split_card(
		'admin',
		__( 'ADMINISTRATION & LOGISTIQUE', 'afvf' ),
		__( 'Garantir la rigueur, la fluidité et la coordination', 'afvf' ),
		home_url( '/administration-logistique/' )
	);

	register_block_pattern(
		'afvf/hover-split-grid-decouvrir',
		array(
			'title'       => __( 'Grille 2×3 — cartes texte + photo (bouton Découvrir au survol)', 'afvf' ),
			'description' => _x( '6 cartes comme la maquette Wix : fond de couleur + texte à gauche, photo à droite. Le bouton « Découvrir » apparaît au survol de la souris. Ajoutez vos photos et vos liens.', 'Block pattern description', 'afvf' ),
			'categories'  => array( 'afvf', 'columns' ),
			'content'     => '<!-- wp:group {"align":"wide","className":"afvf-hover-split-grid","layout":{"type":"default"}} -->
<div class="wp-block-group alignwide afvf-hover-split-grid">' . $hover_cards . '</div>
<!-- /wp:group -->',
		)
	);

	register_block_pattern(
		'afvf/photo-text-cards',
		array(
			'title'       => __( 'Cartes photo + texte (fond bleu-gris, type Wix)', 'afvf' ),
			'description' => _x( 'Liste de cartes : photo à gauche, titre et texte justifié à droite sur fond bleu-gris. À utiliser avec le modèle de page « Page — fond blanc ». Dupliquez une carte pour en ajouter.', 'Block pattern description', 'afvf' ),
			'categories'  => array( 'afvf', 'columns' ),
			'content'     => '<!-- wp:group {"align":"wide","className":"afvf-photo-text-list","layout":{"type":"default"}} -->
<div class="wp-block-group alignwide afvf-photo-text-list">' . afvf_pattern_photo_text_card(
				__( 'Soutien direct aux familles de victimes et aux combattantes rescapées de violences conjugales', 'afvf' ),
				__( 'Nous offrons une écoute attentive, une orientation personnalisée et un accompagnement individuel, complété par des groupes de parole pour rompre l’isolement.', 'afvf' )
			) . "\n\n" . afvf_pattern_photo_text_card(
				__( 'Actions innovantes', 'afvf' ),
				__( 'Avec la fondation Nous-Rire la Vie, nous lançons des projets de reconstruction pensés pour les femmes rescapées.', 'afvf' )
			) . '</div>
<!-- /wp:group -->',
		)
	);

	$vision_url = esc_url(
		afvf_mod(
			'afvf_member_url',
			'https://pay.qonto.com/payment-links/019e2fee-f9e0-7add-b36a-47b06719677e?resource_id=019e2fee-f9e1-7413-b624-514697f490e8'
		)
	);

	register_block_pattern(
		'afvf/vision-offset-card',
		array(
			'title'       => __( 'Vision — photo + carte beige chevauchante (Wix)', 'afvf' ),
			'description' => _x( 'Grande photo à droite, carte beige à gauche qui déborde sur la photo : titre blanc, texte, liste et bouton « Nous Rejoindre ». À utiliser avec le modèle de page « Page — fond blanc ».', 'Block pattern description', 'afvf' ),
			'categories'  => array( 'afvf', 'columns' ),
			'content'     => '<!-- wp:group {"align":"wide","className":"afvf-vision-offset","layout":{"type":"default"}} -->
<div class="wp-block-group alignwide afvf-vision-offset"><!-- wp:image {"sizeSlug":"large","linkDestination":"none","className":"afvf-vision-offset__photo"} -->
<figure class="wp-block-image size-large afvf-vision-offset__photo"><img alt=""/></figure>
<!-- /wp:image -->

<!-- wp:group {"className":"afvf-vision-offset__card","layout":{"type":"default"}} -->
<div class="wp-block-group afvf-vision-offset__card"><!-- wp:heading {"level":2,"className":"afvf-vision-offset__title"} -->
<h2 class="wp-block-heading afvf-vision-offset__title">' . esc_html__( 'VISION', 'afvf' ) . '</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"afvf-vision-offset__text"} -->
<p class="afvf-vision-offset__text">' . esc_html__( 'Parce que l’humain doit rester au cœur de toute action, notre vision s’ancre dans une exigence de protection, de dignité et de justice.', 'afvf' ) . '<br>' . esc_html__( 'Elle répond à un double impératif :', 'afvf' ) . '</p>
<!-- /wp:paragraph -->

<!-- wp:list {"className":"afvf-vision-offset__list"} -->
<ul class="afvf-vision-offset__list"><li>' . esc_html__( 'celui de protéger chaque femme victime de violences, quel que soit son parcours, sa situation ou son lieu de vie ;', 'afvf' ) . '</li><li>' . esc_html__( 'celui d’accompagner avec respect et constance chaque famille frappée par un féminicide, à toutes les étapes : de l’annonce du décès aux démarches judiciaires, en passant par le deuil, la réparation et la reconstruction.', 'afvf' ) . '</li></ul>
<!-- /wp:list -->

<!-- wp:paragraph {"className":"afvf-vision-offset__text"} -->
<p class="afvf-vision-offset__text">' . esc_html__( 'À l’AFVF, nous refusons que la solitude, l’incompréhension ou l’oubli s’ajoutent à la douleur.', 'afvf' ) . '<br>' . esc_html__( 'Nous faisons le choix de l’engagement, du soin et de l’écoute.', 'afvf' ) . '</p>
<!-- /wp:paragraph -->

<!-- wp:buttons {"className":"afvf-vision-offset__cta","layout":{"type":"flex","justifyContent":"right"}} -->
<div class="wp-block-buttons afvf-vision-offset__cta"><!-- wp:button {"className":"afvf-vision-offset__btn is-style-outline"} -->
<div class="wp-block-button afvf-vision-offset__btn is-style-outline"><a class="wp-block-button__link wp-element-button" href="' . $vision_url . '" target="_blank" rel="noopener">' . esc_html__( 'Nous Rejoindre', 'afvf' ) . '</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group --></div>
<!-- /wp:group -->',
		)
	);

	$slide_dir = get_template_directory_uri() . '/assets/images/';
	register_block_pattern(
		'afvf/hero-slider-3s',
		array(
			'title'       => __( 'Hero défilant — plusieurs images + textes (change toutes les 3 s)', 'afvf' ),
			'description' => _x( 'Diaporama plein écran pour l’accueil : chaque diapositive est une Couverture (photo + titre + texte + bouton). Défilement automatique toutes les 3 secondes, pastilles de navigation, pause au survol. Dupliquez une Couverture pour ajouter une diapositive. Classe afvf-hero-slider--5s sur le groupe = 5 secondes.', 'Block pattern description', 'afvf' ),
			'categories'  => array( 'afvf', 'banner' ),
			'content'     => '<!-- wp:group {"align":"full","className":"afvf-hero-slider","layout":{"type":"default"}} -->
<div class="wp-block-group alignfull afvf-hero-slider">' . afvf_pattern_hero_slide(
				$slide_dir . 'hero-home.jpg',
				__( 'Arrêtons les féminicides', 'afvf' ),
				__( 'Ensemble, protégeons les femmes et leurs enfants.', 'afvf' ),
				__( 'Découvrir', 'afvf' ),
				home_url( '/a-propos/' )
			) . "\n\n" . afvf_pattern_hero_slide(
				$slide_dir . 'hero-entreprises-institutions.png',
				__( 'Accompagner les familles', 'afvf' ),
				__( 'Une écoute attentive, des droits, une reconstruction.', 'afvf' ),
				__( 'Besoin d’aide ?', 'afvf' ),
				home_url( '/besoin-d-aide/' )
			) . "\n\n" . afvf_pattern_hero_slide(
				$slide_dir . 'adhere-banner-office.png',
				__( 'Rejoignez l’AFVF', 'afvf' ),
				__( 'Chaque adhésion, chaque don compte.', 'afvf' ),
				__( 'Adhérer', 'afvf' ),
				esc_url_raw( afvf_mod( 'afvf_member_url', home_url( '/' ) ) )
			) . '</div>
<!-- /wp:group -->',
		)
	);

	register_block_pattern(
		'afvf/lift-cards-grid',
		array(
			'title'       => __( 'Grille 3×2 — photo + étiquette (cartes qui se soulèvent au survol)', 'afvf' ),
			'description' => _x( 'Six cartes : photo portrait, étiquette blanche encadrée qui chevauche la photo, titre gris clair et texte. Sur le site public, chaque carte monte légèrement quand la souris passe dessus. Ajoutez vos photos et vos liens.', 'Block pattern description', 'afvf' ),
			'categories'  => array( 'afvf', 'columns' ),
			'content'     => '<!-- wp:group {"align":"wide","className":"afvf-lift-grid","layout":{"type":"default"}} -->
<div class="wp-block-group alignwide afvf-lift-grid">' . afvf_pattern_lift_card(
				__( 'DEVELOPPER', 'afvf' ),
				__( 'Massivement les lieux d’accueil et les solutions d’hébergement d’urgence et de long terme.', 'afvf' )
			) . "\n\n" . afvf_pattern_lift_card(
				__( 'FAIRE ENTENDRE', 'afvf' ),
				__( 'La voix des familles et des survivantes dans toutes les instances de décision.', 'afvf' )
			) . "\n\n" . afvf_pattern_lift_card(
				__( 'FORMER', 'afvf' ),
				__( 'Toutes les parties prenantes à l’écoute, à la détection et à la prise en charge des violences.', 'afvf' )
			) . "\n\n" . afvf_pattern_lift_card(
				__( 'CREER UN FOND', 'afvf' ),
				__( 'Pour soutenir en urgence les familles de victimes de féminicides et les survivantes de tentatives de féminicides.', 'afvf' )
			) . "\n\n" . afvf_pattern_lift_card(
				__( 'INSTITUER', 'afvf' ),
				__( 'Une Journée nationale de lutte contre les féminicides.', 'afvf' )
			) . "\n\n" . afvf_pattern_lift_card(
				__( 'OBTENIR', 'afvf' ),
				__( 'Des financements publics pérennes pour accompagner les femmes et leurs enfants dans la durée.', 'afvf' )
			) . '</div>
<!-- /wp:group -->',
		)
	);

	register_block_pattern(
		'afvf/pink-button-center',
		array(
			'title'       => __( 'Bouton rose centré — « Nos Projets » (Wix)', 'afvf' ),
			'description' => _x( 'Bouton rectangulaire rose, texte blanc, centré. Changez le texte et le lien dans l’éditeur. Dupliquez pour en mettre plusieurs côte à côte.', 'Block pattern description', 'afvf' ),
			'categories'  => array( 'afvf', 'buttons' ),
			'content'     => '<!-- wp:buttons {"className":"afvf-pink-button-row","layout":{"type":"flex","justifyContent":"center"},"style":{"spacing":{"margin":{"top":"1.5rem","bottom":"1.5rem"}}}} -->
<div class="wp-block-buttons afvf-pink-button-row" style="margin-top:1.5rem;margin-bottom:1.5rem"><!-- wp:button {"className":"afvf-pink-button"} -->
<div class="wp-block-button afvf-pink-button"><a class="wp-block-button__link wp-element-button" href="' . esc_url( home_url( '/projets/' ) ) . '">' . esc_html__( 'Nos Projets', 'afvf' ) . '</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons -->',
		)
	);

	register_block_pattern(
		'afvf/zigzag-projects',
		array(
			'title'       => __( 'Projets — 3 rangées alternées texte / photo (titre animé)', 'afvf' ),
			'description' => _x( 'Rangée 1 : texte à gauche, photo à droite (sans bouton) ; rangées suivantes : alternance + bouton noir « en savoir plus » en bas à droite du texte. Titre animé fumée bleu ciel. Dupliquez une rangée pour continuer.', 'Block pattern description', 'afvf' ),
			'categories'  => array( 'afvf', 'columns' ),
			'content'     => '<!-- wp:group {"align":"full","className":"afvf-zigzag","layout":{"type":"default"}} -->
<div class="wp-block-group alignfull afvf-zigzag">' . afvf_pattern_zigzag_row(
				false,
				__( 'NOS PROJETS', 'afvf' ),
				__( 'L’AFVF ne se contente pas d’alerter : elle agit.', 'afvf' ),
				array(
					__( 'Parmi nos projets phares, la rénovation du lieu de vie “Kër Aïcha”, en Seine-et-Marne, incarne notre engagement concret. Ce futur espace d’accueil, de répit et d’accompagnement pour des femmes survivantes de violences et leurs enfants portera le prénom d’Aïssatou Sow, en hommage à sa mémoire. Ce lieu sera à la fois un refuge, un centre de reconstruction psychosociale et un symbole de résilience.', 'afvf' ),
					__( 'En parallèle, le déploiement d’antennes locales de l’AFVF à travers la France (Hexagone et Outre-mer) vise à renforcer notre présence de proximité. L’objectif : ne laisser aucune famille seule, tisser un maillage solidaire et accompagner au plus près les victimes, partout où elles vivent.', 'afvf' ),
				),
				''
			) . "\n\n" . afvf_pattern_zigzag_row(
				true,
				__( 'UN LIEU DE VIE', 'afvf' ),
				__( 'Kër Aïcha, un refuge pour se reconstruire.', 'afvf' ),
				array(
					__( 'Texte à modifier : présentez ici ce projet, son public, son calendrier et la façon de le soutenir.', 'afvf' ),
				),
				home_url( '/ker-aicha/' )
			) . "\n\n" . afvf_pattern_zigzag_row(
				false,
				__( 'DÉPLOIEMENT NATIONAL', 'afvf' ),
				__( 'Mise en place des antennes locales', 'afvf' ),
				array(
					__( 'L’AFVF déploie des antennes locales pour renforcer la présence de proximité auprès des familles et des survivantes.', 'afvf' ),
					__( 'Chaque antenne vise à tisser un maillage solidaire et à accompagner au plus près les victimes, partout où elles vivent.', 'afvf' ),
				),
				home_url( '/organisation/' ),
				array(
					__( 'Écoute et orientation des familles', 'afvf' ),
					__( 'Lien avec les institutions et les professionnels', 'afvf' ),
					__( 'Actions de sensibilisation sur le territoire', 'afvf' ),
					__( 'Coordination avec le réseau national AFVF', 'afvf' ),
				)
			) . '</div>
<!-- /wp:group -->',
		)
	);

	$former_title_cls = esc_attr(
		afvf_pattern_layer_classes( 'afvf-former-hero__title', '28%', '3%' )
	);
	$former_disc_cls = esc_attr(
		afvf_pattern_layer_classes( 'afvf-former-hero__disc', '38%', '', '4%' )
	);

	register_block_pattern(
		'afvf/former-training-hero',
		array(
			'title'       => __( 'Former — photo + titre + bulle texte (Wix)', 'afvf' ),
			'description' => _x( 'Bannière formation : grand titre à gauche, photo de fond, bulle ovale pêche à droite (texte centré). Titre et bulle déplaçables (poignée « Déplacer »). Pas de composition synchronisée. Remplacez la photo de la Couverture.', 'Block pattern description', 'afvf' ),
			'categories'  => array( 'afvf', 'banner' ),
			'content'     => '<!-- wp:cover {"dimRatio":30,"minHeight":480,"minHeightUnit":"px","align":"full","className":"afvf-former-hero","style":{"spacing":{"padding":{"top":"0","bottom":"0","left":"0","right":"0"}}}} -->
<div class="wp-block-cover alignfull afvf-former-hero" style="padding-top:0;padding-right:0;padding-bottom:0;padding-left:0;min-height:480px"><span aria-hidden="true" class="wp-block-cover__background has-background-dim-30 has-background-dim"></span><div class="wp-block-cover__inner-container"><!-- wp:heading {"level":2,"className":"' . $former_title_cls . '"} -->
<h2 class="wp-block-heading ' . $former_title_cls . '">' . esc_html__( 'FORMER', 'afvf' ) . '</h2>
<!-- /wp:heading -->

<!-- wp:group {"className":"' . $former_disc_cls . '","layout":{"type":"default"}} -->
<div class="wp-block-group ' . $former_disc_cls . '"><!-- wp:paragraph {"className":"afvf-former-hero__disc-text"} -->
<p class="afvf-former-hero__disc-text">' . esc_html__( 'Toutes les parties prenantes à l’écoute, à la détection et à la prise en charge des violences.', 'afvf' ) . '</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"className":"afvf-former-hero__disc-text"} -->
<p class="afvf-former-hero__disc-text">' . esc_html__( 'En rendant ces formations obligatoires, co-construites avec les associations spécialisées, pour la police, la justice, la santé, le travail social, l’éducation.', 'afvf' ) . '</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div></div>
<!-- /wp:cover -->',
		)
	);

	register_block_pattern(
		'afvf/cours-inscription-wix',
		array(
			'title'       => __( 'Nos Cours — formulaire + photo (Wix)', 'afvf' ),
			'description' => _x( 'Fond gris clair, titre blanc « Nos COURS », grande carte blanche (formulaire) devant et photo à droite derrière la carte. Choisissez la photo dans le bloc Image. Le bouton « Procéder au paiement » enregistre la demande puis redirige vers /book-online/cours-ecouter/.', 'Block pattern description', 'afvf' ),
			'categories'  => array( 'afvf', 'columns' ),
			'content'     => '<!-- wp:group {"align":"full","className":"afvf-cours-band","style":{"spacing":{"padding":{"top":"0","bottom":"0","left":"0","right":"0"}}},"layout":{"type":"default"}} -->
<div class="wp-block-group alignfull afvf-cours-band" style="padding-top:0;padding-right:0;padding-bottom:0;padding-left:0"><!-- wp:group {"className":"afvf-cours-band__stage","layout":{"type":"default"}} -->
<div class="wp-block-group afvf-cours-band__stage"><!-- wp:heading {"level":2,"className":"afvf-cours-band__kicker"} -->
<h2 class="wp-block-heading afvf-cours-band__kicker">' . esc_html__( 'Nos COURS', 'afvf' ) . '</h2>
<!-- /wp:heading -->

<!-- wp:group {"className":"afvf-cours-band__card","layout":{"type":"default"}} -->
<div class="wp-block-group afvf-cours-band__card"><!-- wp:heading {"level":3,"className":"afvf-cours-band__card-title"} -->
<h3 class="wp-block-heading afvf-cours-band__card-title">' . esc_html__( 'J’aimerais participer à 1 COURS à 35 euros', 'afvf' ) . '</h3>
<!-- /wp:heading -->

<!-- wp:shortcode -->
[afvf_course_form]
<!-- /wp:shortcode -->

<!-- wp:shortcode -->
[afvf_social_inline]
<!-- /wp:shortcode --></div>
<!-- /wp:group -->

<!-- wp:image {"sizeSlug":"large","linkDestination":"none","className":"afvf-cours-band__photo"} -->
<figure class="wp-block-image size-large afvf-cours-band__photo"><img alt=""/></figure>
<!-- /wp:image --></div>
<!-- /wp:group --></div>
<!-- /wp:group -->',
		)
	);

	register_block_pattern(
		'afvf/webinaire-wix',
		array(
			'title'       => __( 'Webinaire — photo + titre + carte blanche (Wix)', 'afvf' ),
			'description' => _x( 'Fond gris, photo à gauche, titre « webinaire se former pour » qui chevauche, grande carte blanche avec texte et bouton rose « Réserver une place ». Choisissez la photo dans le bloc Image et le lien du bouton.', 'Block pattern description', 'afvf' ),
			'categories'  => array( 'afvf', 'columns' ),
			'content'     => '<!-- wp:group {"align":"full","className":"afvf-webinar-band","style":{"spacing":{"padding":{"top":"0","bottom":"0","left":"0","right":"0"}}},"layout":{"type":"default"}} -->
<div class="wp-block-group alignfull afvf-webinar-band" style="padding-top:0;padding-right:0;padding-bottom:0;padding-left:0"><!-- wp:group {"className":"afvf-webinar-band__stage","layout":{"type":"default"}} -->
<div class="wp-block-group afvf-webinar-band__stage"><!-- wp:image {"sizeSlug":"large","linkDestination":"none","className":"afvf-webinar-band__photo"} -->
<figure class="wp-block-image size-large afvf-webinar-band__photo"><img alt=""/></figure>
<!-- /wp:image -->

<!-- wp:heading {"level":2,"className":"afvf-webinar-band__title"} -->
<h2 class="wp-block-heading afvf-webinar-band__title">' . esc_html__( 'webinaire', 'afvf' ) . ' <em>' . esc_html__( 'se former pour', 'afvf' ) . '</em></h2>
<!-- /wp:heading -->

<!-- wp:group {"className":"afvf-webinar-band__card","layout":{"type":"default"}} -->
<div class="wp-block-group afvf-webinar-band__card"><!-- wp:heading {"level":3,"className":"afvf-webinar-band__lead"} -->
<h3 class="wp-block-heading afvf-webinar-band__lead">' . esc_html__( 'Comprendre, prévenir, agir contre les féminicides et toutes les violences faites conjugales et intrafamiliales.', 'afvf' ) . '</h3>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"afvf-webinar-band__text"} -->
<p class="afvf-webinar-band__text">' . esc_html__( 'L’AFVF propose une série de webinaires interactifs pour mieux comprendre les mécanismes des violences conjugales, le contrôle coercitif, les féminicides et leurs impacts sur les victimes, les enfants et les familles.', 'afvf' ) . '</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"className":"afvf-webinar-band__text"} -->
<p class="afvf-webinar-band__text">' . esc_html__( 'Ces rendez-vous en ligne s’adressent aux professionnels du social, du soin, de la justice, de l’éducation, mais aussi au grand public désireux d’agir. À travers des témoignages de terrain, des apports d’experts et des outils concrets, nos webinaires sont une invitation à la réflexion, à la mobilisation et à la co-construction de solutions durables.', 'afvf' ) . '</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"className":"afvf-webinar-band__text"} -->
<p class="afvf-webinar-band__text">' . esc_html__( 'Dès Avril 2026, nos formations en présentiels seront possibles.', 'afvf' ) . '</p>
<!-- /wp:paragraph -->

<!-- wp:buttons {"className":"afvf-webinar-band__cta","layout":{"type":"flex","justifyContent":"center"}} -->
<div class="wp-block-buttons afvf-webinar-band__cta"><!-- wp:button {"className":"afvf-webinar-band__btn"} -->
<div class="wp-block-button afvf-webinar-band__btn"><a class="wp-block-button__link wp-element-button" href="' . esc_url( home_url( '/book-online/' ) ) . '">' . esc_html__( 'Réserver une place', 'afvf' ) . '</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group --></div>
<!-- /wp:group --></div>
<!-- /wp:group -->',
		)
	);

	register_block_pattern(
		'afvf/a-la-une-3-colonnes',
		array(
			'title'       => __( 'À la UNE — vidéo + photos qui défilent + texte et affiche', 'afvf' ),
			'description' => _x( 'Trois colonnes : vidéo qui se joue toute seule (choisissez le fichier), photos qui défilent avec flèches (ajoutez/supprimez des images dans le bloc), texte « A la UNE » avec lien et affiche.', 'Block pattern description', 'afvf' ),
			'categories'  => array( 'afvf', 'columns' ),
			'content'     => '<!-- wp:group {"align":"wide","className":"afvf-une","layout":{"type":"default"}} -->
<div class="wp-block-group alignwide afvf-une"><!-- wp:video {"autoplay":true,"loop":true,"muted":true,"playsInline":true,"className":"afvf-une__video"} -->
<figure class="wp-block-video afvf-une__video"></figure>
<!-- /wp:video -->

<!-- wp:group {"className":"afvf-une-carousel","layout":{"type":"default"}} -->
<div class="wp-block-group afvf-une-carousel"><!-- wp:group {"className":"afvf-une-carousel__track","layout":{"type":"default"}} -->
<div class="wp-block-group afvf-une-carousel__track"><!-- wp:image {"sizeSlug":"large","linkDestination":"none","className":"afvf-une-carousel__slide"} -->
<figure class="wp-block-image size-large afvf-une-carousel__slide"><img alt=""/></figure>
<!-- /wp:image -->

<!-- wp:image {"sizeSlug":"large","linkDestination":"none","className":"afvf-une-carousel__slide"} -->
<figure class="wp-block-image size-large afvf-une-carousel__slide"><img alt=""/></figure>
<!-- /wp:image -->

<!-- wp:image {"sizeSlug":"large","linkDestination":"none","className":"afvf-une-carousel__slide"} -->
<figure class="wp-block-image size-large afvf-une-carousel__slide"><img alt=""/></figure>
<!-- /wp:image -->

<!-- wp:image {"sizeSlug":"large","linkDestination":"none","className":"afvf-une-carousel__slide"} -->
<figure class="wp-block-image size-large afvf-une-carousel__slide"><img alt=""/></figure>
<!-- /wp:image --></div>
<!-- /wp:group --></div>
<!-- /wp:group -->

<!-- wp:group {"className":"afvf-une__news","layout":{"type":"default"}} -->
<div class="wp-block-group afvf-une__news"><!-- wp:group {"className":"afvf-une__text","layout":{"type":"default"}} -->
<div class="wp-block-group afvf-une__text"><!-- wp:heading {"level":2,"className":"afvf-une__title"} -->
<h2 class="wp-block-heading afvf-une__title">' . esc_html__( 'A la UNE', 'afvf' ) . '</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"afvf-une__lead"} -->
<p class="afvf-une__lead">' . esc_html__( '5è édition des Assises Nationales Contre les Féminicides. Samedi 26 Septembre 2026 - Paris', 'afvf' ) . '</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"className":"afvf-une__link"} -->
<p class="afvf-une__link"><a href="#">' . esc_html__( 'Plaquette AFVF', 'afvf' ) . '</a></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:image {"sizeSlug":"large","linkDestination":"none","className":"afvf-une__poster"} -->
<figure class="wp-block-image size-large afvf-une__poster"><img alt=""/></figure>
<!-- /wp:image --></div>
<!-- /wp:group --></div>
<!-- /wp:group -->',
		)
	);

	register_block_pattern(
		'afvf/podcasts-wix',
		array(
			'title'       => __( 'Podcasts — témoignages, bouton + lecteurs audio (Wix)', 'afvf' ),
			'description' => _x( 'Fond bleu : titre « PODCAST Témoignages », bouton rose « Tous les PODCASTS », texte d’intro, citation, et 5 cartes lecteur audio (vignette + titre + lecteur). Pour chaque carte : choisissez la vignette et le fichier audio.', 'Block pattern description', 'afvf' ),
			'categories'  => array( 'afvf', 'columns' ),
			'content'     => '<!-- wp:group {"align":"full","className":"afvf-podcast","layout":{"type":"default"}} -->
<div class="wp-block-group alignfull afvf-podcast"><!-- wp:group {"className":"afvf-podcast__grid","layout":{"type":"default"}} -->
<div class="wp-block-group afvf-podcast__grid"><!-- wp:heading {"level":2,"className":"afvf-podcast__title"} -->
<h2 class="wp-block-heading afvf-podcast__title">' . esc_html__( 'PODCAST', 'afvf' ) . '<br>' . esc_html__( 'Témoignages', 'afvf' ) . '</h2>
<!-- /wp:heading -->

<!-- wp:buttons {"className":"afvf-podcast__all"} -->
<div class="wp-block-buttons afvf-podcast__all"><!-- wp:button {"className":"afvf-podcast__all-btn"} -->
<div class="wp-block-button afvf-podcast__all-btn"><a class="wp-block-button__link wp-element-button" href="' . esc_url( home_url( '/podcasts/' ) ) . '">' . esc_html__( 'Tous les PODCASTS', 'afvf' ) . '</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons -->

' . afvf_pattern_podcast_card( __( 'Introduction', 'afvf' ), __( 'Savy', 'afvf' ) ) . '

<!-- wp:paragraph {"className":"afvf-podcast__intro"} -->
<p class="afvf-podcast__intro">' . esc_html__( 'Chaque lundi, retrouvez les Podcast de Savy ou d’Angèle qui vous parleront de sujets choisis en thèmes aussi variés, sur la problématique des violences faites aux femmes.', 'afvf' ) . '</p>
<!-- /wp:paragraph -->

' . afvf_pattern_podcast_card( __( 'Sur le trottoir des sourires', 'afvf' ), __( 'Savy', 'afvf' ) ) . '

' . afvf_pattern_podcast_card( __( 'Parlez aux hommes', 'afvf' ), __( 'Angèle', 'afvf' ) ) . '

<!-- wp:group {"className":"afvf-podcast__quote","layout":{"type":"default"}} -->
<div class="wp-block-group afvf-podcast__quote"><!-- wp:paragraph {"className":"afvf-podcast__quote-text"} -->
<p class="afvf-podcast__quote-text">' . esc_html__( 'Je ne suis pas un oiseau et aucune cage ne peut m’empêcher d’être libre et indépendante.', 'afvf' ) . '</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"className":"afvf-podcast__quote-by"} -->
<p class="afvf-podcast__quote-by">' . esc_html__( 'Charlotte Bronte, Jane Eyre', 'afvf' ) . '</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

' . afvf_pattern_podcast_card( __( 'Si tu étais…', 'afvf' ), __( 'Noël Agossa', 'afvf' ) ) . '

' . afvf_pattern_podcast_card( __( 'toi, femme…', 'afvf' ), __( 'Angèle', 'afvf' ) ) . '</div>
<!-- /wp:group --></div>
<!-- /wp:group -->',
		)
	);

	$nr_img    = esc_url( get_template_directory_uri() . '/assets/images/hero-nous-rire.jpg' );
	$nr_title  = esc_attr( afvf_pattern_layer_classes( 'afvf-nous-hero__title', '49%', '17%' ) );
	$nr_sub    = esc_attr( afvf_pattern_layer_classes( 'afvf-nous-hero__sub', '63%', '', '17.8%' ) );
	$nr_btn1   = esc_attr( afvf_pattern_layer_classes( 'afvf-nous-hero__btn1', '19%', '', '17.4%' ) );
	$nr_btn2   = esc_attr( afvf_pattern_layer_classes( 'afvf-nous-hero__btn2', '27%', '', '17.4%' ) );

	register_block_pattern(
		'afvf/nous-rire-la-vie',
		array(
			'title'       => __( 'Nous-rire la vie — bannière (photo + titre + 2 boutons)', 'afvf' ),
			'description' => _x( 'Grande photo en fond (à remplacer dans la Couverture), titre « NOUS-RIRE LA VIE », sous-titre souligné et deux boutons en haut à droite (jaune et vert). Chaque élément se déplace avec « Déplacer ».', 'Block pattern description', 'afvf' ),
			'categories'  => array( 'afvf', 'banner' ),
			'content'     => '<!-- wp:cover {"url":"' . $nr_img . '","dimRatio":0,"minHeight":620,"minHeightUnit":"px","align":"full","className":"afvf-banner-canvas is-style-afvf-banner-canvas afvf-banner-nous","style":{"spacing":{"padding":{"top":"0","bottom":"0","left":"0","right":"0"}}}} -->
<div class="wp-block-cover alignfull afvf-banner-canvas is-style-afvf-banner-canvas afvf-banner-nous" style="padding-top:0;padding-right:0;padding-bottom:0;padding-left:0;min-height:620px"><img class="wp-block-cover__image-background" alt="" src="' . $nr_img . '" data-object-fit="cover"/><span aria-hidden="true" class="wp-block-cover__background has-background-dim-0 has-background-dim"></span><div class="wp-block-cover__inner-container"><!-- wp:heading {"level":1,"className":"' . $nr_title . '"} -->
<h1 class="wp-block-heading ' . $nr_title . '">' . esc_html__( 'NOUS-RIRE LA VIE', 'afvf' ) . '</h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"' . $nr_sub . '"} -->
<p class="' . $nr_sub . '">' . esc_html__( 'Fondation De l’AFVF', 'afvf' ) . '</p>
<!-- /wp:paragraph -->

<!-- wp:buttons {"className":"' . $nr_btn1 . '"} -->
<div class="wp-block-buttons ' . $nr_btn1 . '"><!-- wp:button {"className":"afvf-nous-hero__yellow"} -->
<div class="wp-block-button afvf-nous-hero__yellow"><a class="wp-block-button__link wp-element-button" href="#">' . esc_html__( 'Lieu de Vie - Kër Aïcha', 'afvf' ) . '</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons -->

<!-- wp:buttons {"className":"' . $nr_btn2 . '"} -->
<div class="wp-block-buttons ' . $nr_btn2 . '"><!-- wp:button {"className":"afvf-nous-hero__green"} -->
<div class="wp-block-button afvf-nous-hero__green"><a class="wp-block-button__link wp-element-button" href="#">' . esc_html__( 'Le Jardin Partagé', 'afvf' ) . '</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div></div>
<!-- /wp:cover -->',
		)
	);

	$ey_kicker = esc_attr( afvf_pattern_layer_classes( 'afvf-eye-hero__kicker', '34.2%', '30.5%' ) );
	$ey_title  = esc_attr( afvf_pattern_layer_classes( 'afvf-eye-hero__title', '38%', '30.5%' ) );
	$ey_tag    = esc_attr( afvf_pattern_layer_classes( 'afvf-eye-hero__tag', '47.5%', '', '31%' ) );

	register_block_pattern(
		'afvf/nous-rire-video',
		array(
			'title'       => __( 'Nous-rire la vie — bannière vidéo (Fondation AFVF)', 'afvf' ),
			'description' => _x( 'Bannière pleine largeur avec vidéo en fond : « FONDATION », titre « NOUS-RIRE LA VIE » et « AFVF ». Sélectionnez la Couverture puis cliquez sur « Vidéo » dans la barre du bloc pour choisir la vidéo. Les textes se déplacent avec « Déplacer ».', 'Block pattern description', 'afvf' ),
			'categories'  => array( 'afvf', 'banner' ),
			'content'     => '<!-- wp:cover {"dimRatio":0,"minHeight":520,"minHeightUnit":"px","align":"full","className":"afvf-banner-canvas is-style-afvf-banner-canvas afvf-eye-hero"} -->
<div class="wp-block-cover alignfull afvf-banner-canvas is-style-afvf-banner-canvas afvf-eye-hero" style="min-height:520px"><span aria-hidden="true" class="wp-block-cover__background has-background-dim-0 has-background-dim"></span><div class="wp-block-cover__inner-container"><!-- wp:paragraph {"className":"' . $ey_kicker . '"} -->
<p class="' . $ey_kicker . '">' . esc_html__( 'FONDATION', 'afvf' ) . '</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":1,"className":"' . $ey_title . '"} -->
<h1 class="wp-block-heading ' . $ey_title . '">' . esc_html__( 'NOUS-RIRE LA VIE', 'afvf' ) . '</h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"' . $ey_tag . '"} -->
<p class="' . $ey_tag . '">' . esc_html__( 'AFVF', 'afvf' ) . '</p>
<!-- /wp:paragraph --></div></div>
<!-- /wp:cover -->',
		)
	);

	$pm_dir  = get_template_directory_uri() . '/assets/images/';
	$pm_bg   = esc_url( $pm_dir . 'promesse-bg.jpg' );
	$pm_img  = esc_url( $pm_dir . 'promesse-photo.jpg' );
	$pm_p    = static function ( $t ) {
		return '<!-- wp:paragraph {"className":"afvf-promesse__text"} -->
<p class="afvf-promesse__text">' . $t . '</p>
<!-- /wp:paragraph -->';
	};
	$pm_text = $pm_p( esc_html__( 'La Fondation "Nous-Rire La Vie" est née d’un constat douloureux et d’une volonté inébranlable. Face aux milliers d’enfants orphelins de féminicides et aux familles brisées par les violences conjugales, l’AFVF a créé cette fondation pour transformer la douleur en espoir et redonner de la joie là où la violence a semé la tristesse.', 'afvf' ) )
		. "\n\n" . $pm_p( esc_html__( 'Chaque année en France, des centaines d’enfants perdent leur mère dans des circonstances dramatiques, tandis que des milliers d’autres grandissent dans l’ombre des violences conjugales. Ces enfants co-victimes méritent que la société leur offre des moments de bonheur, de reconstruction et d’épanouissement.', 'afvf' ) )
		. "\n\n" . $pm_p( esc_html__( '"Nous-Rire La Vie" : ce nom résonne comme une promesse, celle de faire renaître la lumière dans les yeux d’enfants meurtris, de leur rappeler qu’ils ont le droit au bonheur malgré les épreuves traversées.', 'afvf' ) )
		. "\n\n" . $pm_p( esc_html__( 'Merci à Catherine pour ce nom.', 'afvf' ) );

	register_block_pattern(
		'afvf/une-promesse-wix',
		array(
			'title'       => __( 'Une promesse… — photo + carte blanche sur fond flou (Wix)', 'afvf' ),
			'description' => _x( 'Grande bannière à fond de photo (ou vidéo), avec une photo carrée à gauche accolée à une carte blanche : titre, texte justifié et bouton rose. Remplacez le fond et la photo avec « Remplacer ».', 'Block pattern description', 'afvf' ),
			'categories'  => array( 'afvf', 'columns' ),
			'content'     => '<!-- wp:cover {"url":"' . $pm_bg . '","dimRatio":0,"minHeight":410,"minHeightUnit":"px","align":"full","className":"afvf-promesse"} -->
<div class="wp-block-cover alignfull afvf-promesse" style="min-height:410px"><span aria-hidden="true" class="wp-block-cover__background has-background-dim-0 has-background-dim"></span><img class="wp-block-cover__image-background" alt="" src="' . $pm_bg . '" data-object-fit="cover"/><div class="wp-block-cover__inner-container"><!-- wp:group {"className":"afvf-promesse__stage","layout":{"type":"default"}} -->
<div class="wp-block-group afvf-promesse__stage"><!-- wp:image {"sizeSlug":"large","linkDestination":"none","className":"afvf-promesse__photo"} -->
<figure class="wp-block-image size-large afvf-promesse__photo"><img src="' . $pm_img . '" alt=""/></figure>
<!-- /wp:image -->

<!-- wp:group {"className":"afvf-promesse__card","layout":{"type":"default"}} -->
<div class="wp-block-group afvf-promesse__card"><!-- wp:heading {"level":2,"className":"afvf-promesse__title"} -->
<h2 class="wp-block-heading afvf-promesse__title">' . esc_html__( 'UNE PROMESSE…', 'afvf' ) . '</h2>
<!-- /wp:heading -->

' . $pm_text . '

<!-- wp:buttons {"className":"afvf-promesse__btns"} -->
<div class="wp-block-buttons afvf-promesse__btns"><!-- wp:button {"className":"afvf-promesse__btn"} -->
<div class="wp-block-button afvf-promesse__btn"><a class="wp-block-button__link wp-element-button" href="#">' . esc_html__( 'DON-nez nous de la force', 'afvf' ) . '</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group --></div>
<!-- /wp:group --></div></div>
<!-- /wp:cover -->',
		)
	);

	register_block_pattern(
		'afvf/contact-wix',
		array(
			'title'       => __( 'Contact — formulaire violet (Wix)', 'afvf' ),
			'description' => _x( 'Titre « Contact » violet et formulaire : prénom, nom, e-mail, téléphone, message et bouton « Envoyer ». Les messages arrivent par e-mail à l’association et dans le menu Messages.', 'Block pattern description', 'afvf' ),
			'categories'  => array( 'afvf', 'columns' ),
			'content'     => '<!-- wp:group {"className":"afvf-cwix-wrap","layout":{"type":"default"}} -->
<div class="wp-block-group afvf-cwix-wrap"><!-- wp:heading {"level":2,"className":"afvf-cwix-wrap__title"} -->
<h2 class="wp-block-heading afvf-cwix-wrap__title">' . esc_html__( 'Contact', 'afvf' ) . '</h2>
<!-- /wp:heading -->

<!-- wp:shortcode -->
[afvf_contact_wix]
<!-- /wp:shortcode --></div>
<!-- /wp:group -->',
		)
	);

	$dn_dir  = get_template_directory_uri() . '/assets/images/';
	$dn_icon = static function ( $file, $alt ) use ( $dn_dir ) {
		return '<!-- wp:image {"align":"center","sizeSlug":"full","linkDestination":"none","className":"afvf-don3__icon"} -->
<figure class="wp-block-image aligncenter size-full afvf-don3__icon"><img src="' . esc_url( $dn_dir . $file ) . '" alt="' . esc_attr( $alt ) . '"/></figure>
<!-- /wp:image -->';
	};
	$dn_title = static function ( $t ) {
		return '<!-- wp:heading {"textAlign":"center","level":3,"className":"afvf-don3__title"} -->
<h3 class="wp-block-heading has-text-align-center afvf-don3__title">' . $t . '</h3>
<!-- /wp:heading -->';
	};
	$dn_p = static function ( $t, $cls = '' ) {
		return '<!-- wp:paragraph {"align":"center","className":"afvf-don3__text' . ( $cls ? ' ' . $cls : '' ) . '"} -->
<p class="has-text-align-center afvf-don3__text' . ( $cls ? ' ' . $cls : '' ) . '">' . $t . '</p>
<!-- /wp:paragraph -->';
	};
	$dn_col = static function ( $mod, $inner ) {
		return '<!-- wp:column {"className":"afvf-don3__col afvf-don3__col--' . $mod . '"} -->
<div class="wp-block-column afvf-don3__col afvf-don3__col--' . $mod . '">' . $inner . '</div>
<!-- /wp:column -->';
	};
	$dn_c1 = $dn_icon( 'don-icon-user.svg', '' ) . "\n\n" . $dn_title( esc_html__( 'Chèque', 'afvf' ) ) . "\n\n"
		. $dn_p( esc_html__( 'à l’ordre de : Association des familles de victimes de féminicides.', 'afvf' ) . '<br>' . esc_html__( 'Merci de nous envoyez un mail pour l’adresse d’envoi: assofvf@gmail.com', 'afvf' ) );
	$dn_c2 = $dn_icon( 'don-icon-globe.svg', '' ) . "\n\n" . $dn_title( esc_html__( 'EN LIGNE', 'afvf' ) ) . "\n\n"
		. $dn_p( esc_html__( 'Vous pouvez déduire de vos impôts', 'afvf' ) ) . "\n\n"
		. '<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"},"className":"afvf-don3__btns"} -->
<div class="wp-block-buttons afvf-don3__btns"><!-- wp:button {"className":"afvf-don3__btn"} -->
<div class="wp-block-button afvf-don3__btn"><a class="wp-block-button__link wp-element-button" href="' . esc_url( function_exists( 'afvf_mod' ) ? afvf_mod( 'afvf_donate_url', 'https://www.helloasso.com/associations/afvf/formulaires/1' ) : '#' ) . '">' . esc_html__( 'Faire un DON', 'afvf' ) . '</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons -->';
	$dn_c3 = $dn_icon( 'don-icon-phone.svg', '' ) . "\n\n" . $dn_title( esc_html__( 'Par Téléphone', 'afvf' ) ) . "\n\n"
		. $dn_p( esc_html__( 'Contactez-nous pour des informations précises.', 'afvf' ) . '<br>' . esc_html__( 'Il est possible de faire un don aussi en hors ligne', 'afvf' ) ) . "\n\n"
		. $dn_p( '06 26 36 89 94', 'afvf-don3__phone' );

	register_block_pattern(
		'afvf/don-trois-colonnes',
		array(
			'title'       => __( 'Faire un don — Chèque / En ligne / Téléphone (3 colonnes)', 'afvf' ),
			'description' => _x( 'Trois colonnes pleine largeur colorées (rose, bleu clair, bleu foncé) avec icône, titre et texte blanc. Modifiez le lien du bouton « Faire un DON » et le numéro.', 'Block pattern description', 'afvf' ),
			'categories'  => array( 'afvf', 'columns' ),
			'content'     => '<!-- wp:columns {"align":"full","className":"afvf-don3","style":{"spacing":{"blockGap":{"top":"0","left":"0"}}}} -->
<div class="wp-block-columns alignfull afvf-don3">' . $dn_col( 'pink', $dn_c1 ) . '

' . $dn_col( 'sky', $dn_c2 ) . '

' . $dn_col( 'navy', $dn_c3 ) . '</div>
<!-- /wp:columns -->',
		)
	);

	$ga_dir = get_template_directory_uri() . '/assets/images/';
	$ga_ico = static function ( $n, $alt ) use ( $ga_dir ) {
		$u = esc_url( $ga_dir . 'gala-icon-' . $n . '.png' );
		return '<!-- wp:image {"align":"center","sizeSlug":"full","linkDestination":"none","className":"afvf-gala__icon"} -->
<figure class="wp-block-image aligncenter size-full afvf-gala__icon"><img src="' . $u . '" alt="' . esc_attr( $alt ) . '"/></figure>
<!-- /wp:image -->';
	};
	$ga_h   = static function ( $text, $level, $cls ) {
		return '<!-- wp:heading {"level":' . $level . ',"className":"' . $cls . '"} -->
<h' . $level . ' class="wp-block-heading ' . $cls . '">' . $text . '</h' . $level . '>
<!-- /wp:heading -->';
	};
	$ga_p   = static function ( $text, $cls = '' ) {
		$attr = $cls ? ' {"className":"' . $cls . '"}' : '';
		$c    = $cls ? ' class="' . $cls . '"' : '';
		return '<!-- wp:paragraph' . $attr . ' -->
<p' . $c . '>' . $text . '</p>
<!-- /wp:paragraph -->';
	};
	$ga_ul  = static function ( $items ) {
		$out = '<!-- wp:list {"className":"afvf-gala__list"} -->
<ul class="wp-block-list afvf-gala__list">';
		foreach ( $items as $it ) {
			$out .= '<!-- wp:list-item -->
<li>' . $it . '</li>
<!-- /wp:list-item -->';
		}
		return $out . '</ul>
<!-- /wp:list -->';
	};

	$ga_col1 = $ga_ico( 1, __( 'Enfants co-victimes', 'afvf' ) ) . "\n\n"
		. $ga_h( esc_html__( 'Pour les enfants co-victimes', 'afvf' ), 3, 'afvf-gala__title' ) . "\n\n"
		. $ga_p( esc_html__( 'Notre fondation s’engage à accompagner chaque enfant dans les moments clés de sa reconstruction, en créant des souvenirs positifs qui peuvent transformer son rapport à la vie :', 'afvf' ) ) . "\n\n"
		. $ga_h( esc_html__( 'Moments de célébration', 'afvf' ), 4, 'afvf-gala__sub' ) . "\n\n"
		. $ga_ul(
			array(
				esc_html__( 'Anniversaires personnalisés : cadeaux adaptés à l’âge et aux goûts de chaque enfant', 'afvf' ),
				esc_html__( 'Fêtes de Noël magiques : distribution de cadeaux et organisation d’événements féeriques', 'afvf' ),
				esc_html__( 'Réussites scolaires : récompenses et encouragements pour valoriser leurs efforts', 'afvf' ),
				esc_html__( 'Étapes importantes : accompagnement lors des premières fois (rentrée scolaire, examens, etc.)', 'afvf' ),
			)
		);

	$ga_col2 = $ga_ico( 2, __( 'Femmes victimes de violences', 'afvf' ) ) . "\n\n"
		. $ga_h( esc_html__( 'Pour les femmes victimes de violences et leurs enfants', 'afvf' ), 3, 'afvf-gala__title' ) . "\n\n"
		. $ga_p( esc_html__( 'Notre action s’étend également aux mères courageuses qui fuient les violences avec leurs enfants, car leur reconstruction passe aussi par des moments de répit et de bonheur partagé :', 'afvf' ) ) . "\n\n"
		. $ga_h( esc_html__( 'Accompagnement familial', 'afvf' ), 4, 'afvf-gala__sub' ) . "\n\n"
		. $ga_ul(
			array(
				esc_html__( 'Sorties mère-enfant : activités permettant de recréer des liens sereins', 'afvf' ),
				esc_html__( 'Séjours de décompression : moments de pause loin des tensions et des procédures', 'afvf' ),
				esc_html__( 'Ateliers parent-enfant : pour réapprendre à jouer, créer et rire ensemble', 'afvf' ),
				esc_html__( 'Soutien lors des fêtes : pour que les enfants n’associent pas les célébrations à la violence', 'afvf' ),
			)
		) . "\n\n"
		. $ga_h( esc_html__( 'Reconstruction positive', 'afvf' ), 4, 'afvf-gala__sub' ) . "\n\n"
		. $ga_ul(
			array(
				esc_html__( 'Activités de bien-être : spa, relaxation, activités ressourçantes pour les mères', 'afvf' ),
				esc_html__( 'Développement personnel : ateliers confiance en soi, gestion du stress, créativité', 'afvf' ),
				esc_html__( 'Réseautage bienveillant : rencontres entre femmes qui se reconstruisent', 'afvf' ),
				esc_html__( 'Célébration des victoires : reconnaissance de chaque étape de reconstruction', 'afvf' ),
			)
		);

	$ga_col3 = $ga_ico( 3, __( 'Gala annuel', 'afvf' ) ) . "\n\n"
		. $ga_h( esc_html__( 'NOTRE ÉVÉNEMENT PHARE :', 'afvf' ), 3, 'afvf-gala__title afvf-gala__title--big' ) . "\n\n"
		. $ga_h( esc_html__( 'LE GALA ANNUEL', 'afvf' ), 3, 'afvf-gala__title afvf-gala__title--big afvf-gala__title--blue' ) . "\n\n"
		. $ga_p( esc_html__( 'Chaque année, nous organiserons un Gala pour constituer le temps fort de notre action :', 'afvf' ) ) . "\n\n"
		. $ga_h( esc_html__( 'Objectifs du Gala', 'afvf' ), 4, 'afvf-gala__sub afvf-gala__sub--dark' ) . "\n\n"
		. $ga_ul(
			array(
				esc_html__( 'Collecte de fonds pour financer nos actions toute l’année', 'afvf' ),
				esc_html__( 'Sensibilisation du grand public à la cause des enfants co-victimes', 'afvf' ),
				esc_html__( 'Mise en lumière des témoignages et des réussites de reconstruction', 'afvf' ),
				esc_html__( 'Fédération des soutiens et partenaires autour de notre mission', 'afvf' ),
			)
		) . "\n\n"
		. $ga_h( esc_html__( 'Programme', 'afvf' ), 4, 'afvf-gala__sub afvf-gala__sub--dark' ) . "\n\n"
		. $ga_ul(
			array(
				esc_html__( 'Témoignages émouvants d’enfants et familles accompagnés', 'afvf' ),
				esc_html__( 'Spectacles et animations par des artistes engagés', 'afvf' ),
				esc_html__( 'Vente aux enchères solidaires d’œuvres d’art et expériences uniques', 'afvf' ),
				esc_html__( 'Remise de prix aux partenaires et bénévoles exceptionnels', 'afvf' ),
				esc_html__( 'Moments de convivialité pour créer du lien social', 'afvf' ),
			)
		) . "\n\n"
		. $ga_p( esc_html__( 'DATE Prévisionnelle : Samedi 08 Mars 2026 (sous réserve)', 'afvf' ), 'afvf-gala__date' );

	register_block_pattern(
		'afvf/enfants-femmes-gala',
		array(
			'title'       => __( 'Enfants · Femmes · Gala (3 colonnes séparées)', 'afvf' ),
			'description' => _x( 'Trois colonnes pleine largeur séparées par un trait noir : icône, titre, texte et listes à puces (type Wix).', 'Block pattern description', 'afvf' ),
			'categories'  => array( 'afvf', 'columns' ),
			'content'     => '<!-- wp:columns {"align":"full","className":"afvf-gala","style":{"spacing":{"blockGap":{"top":"0","left":"0"}}}} -->
<div class="wp-block-columns alignfull afvf-gala"><!-- wp:column {"className":"afvf-gala__col"} -->
<div class="wp-block-column afvf-gala__col">' . $ga_col1 . '</div>
<!-- /wp:column -->

<!-- wp:column {"className":"afvf-gala__col"} -->
<div class="wp-block-column afvf-gala__col">' . $ga_col2 . '</div>
<!-- /wp:column -->

<!-- wp:column {"className":"afvf-gala__col"} -->
<div class="wp-block-column afvf-gala__col">' . $ga_col3 . '</div>
<!-- /wp:column --></div>
<!-- /wp:columns -->',
		)
	);

	$cm_dir   = get_template_directory_uri() . '/assets/images/';
	$cm_img1  = esc_url( $cm_dir . 'comment-1.jpg' );
	$cm_img3  = esc_url( $cm_dir . 'comment-3.jpg' );
	$cm_user  = esc_url( $cm_dir . 'comment-icon-user.svg' );
	$cm_cup   = esc_url( $cm_dir . 'comment-icon-cup.svg' );
	$cm_pin   = esc_url( $cm_dir . 'comment-icon-pin.svg' );
	$cm_title = esc_html__( 'Comment cela se passe?', 'afvf' );
	$cm_btn1  = esc_html__( 'EXPERTS-Contributions', 'afvf' );
	$cm_btn2  = esc_html__( 'Programme Complet', 'afvf' );
	$cm_cap1  = esc_html__( 'Inscrivez-vous en cliquant sur l’icone.', 'afvf' );
	$cm_cap2  = esc_html__( 'Réservez le cocktail-déjeunatoire', 'afvf' );
	$cm_cap3  = esc_html__( 'Préparez votre pièce d’identité et nous vous accueillons au colloque.', 'afvf' );
	$cm_text2 = esc_html__( 'Un déjeuner en mode cocktail au palais ? Oui c’est possible et nous gagnerons toutes et tous du temps pour reprendre à l’heure, les tables rondes de notre colloque. Nous comptons sur votre participation afin que ce temps d’échange soit fructueux.', 'afvf' );
	$cm_menu  = esc_html__( 'Le menu est à 20 euros.', 'afvf' );
	$cm_text2b = esc_html__( ' Il est assuré par les Tables de YEMMA, traiteur officiel de l’AFVF.', 'afvf' );
	$cm_addr  = esc_html__( '15 Ter, rue de Vaugirard, 75006 PARIS', 'afvf' );

	$cm_icon = static function ( $url, $mod, $alt ) {
		return '<!-- wp:image {"align":"center","sizeSlug":"full","linkDestination":"none","className":"afvf-comment-step__icon afvf-comment-step__icon--' . $mod . '"} -->
<figure class="wp-block-image aligncenter size-full afvf-comment-step__icon afvf-comment-step__icon--' . $mod . '"><img src="' . $url . '" alt="' . esc_attr( $alt ) . '"/></figure>
<!-- /wp:image -->';
	};
	$cm_photo = static function ( $url ) {
		return '<!-- wp:image {"align":"center","sizeSlug":"full","linkDestination":"none","className":"afvf-comment-step__photo"} -->
<figure class="wp-block-image aligncenter size-full afvf-comment-step__photo"><img src="' . $url . '" alt=""/></figure>
<!-- /wp:image -->';
	};
	$cm_num = static function ( $n ) {
		return '<!-- wp:heading {"textAlign":"center","level":3,"className":"afvf-comment-step__num"} -->
<h3 class="wp-block-heading has-text-align-center afvf-comment-step__num">' . $n . '</h3>
<!-- /wp:heading -->';
	};
	$cm_cap = static function ( $t ) {
		return '<!-- wp:paragraph {"align":"center","className":"afvf-comment-step__caption"} -->
<p class="has-text-align-center afvf-comment-step__caption"><strong>' . $t . '</strong></p>
<!-- /wp:paragraph -->';
	};

	register_block_pattern(
		'afvf/comment-ca-se-passe',
		array(
			'title'       => __( 'Comment cela se passe ? (3 étapes, fond cuivré)', 'afvf' ),
			'description' => _x( 'Titre + bouton, trois cartes numérotées 01 / 02 / 03 avec icône ronde, texte et image, et bouton « Programme Complet » (type Wix).', 'Block pattern description', 'afvf' ),
			'categories'  => array( 'afvf', 'columns' ),
			'content'     => '<!-- wp:group {"align":"full","className":"afvf-comment","layout":{"type":"default"}} -->
<div class="wp-block-group alignfull afvf-comment"><!-- wp:group {"className":"afvf-comment__head","layout":{"type":"flex","flexWrap":"wrap","justifyContent":"center","verticalAlignment":"center"}} -->
<div class="wp-block-group afvf-comment__head"><!-- wp:heading {"level":2,"className":"afvf-comment__title"} -->
<h2 class="wp-block-heading afvf-comment__title">' . $cm_title . '</h2>
<!-- /wp:heading -->

<!-- wp:buttons {"className":"afvf-comment__btn"} -->
<div class="wp-block-buttons afvf-comment__btn"><!-- wp:button {"style":{"border":{"radius":"0px"},"color":{"background":"#ffffff","text":"#26408b"}}} -->
<div class="wp-block-button"><a class="wp-block-button__link has-text-color has-background wp-element-button" href="#" style="border-radius:0px;color:#26408b;background-color:#ffffff">' . $cm_btn1 . '</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group -->

<!-- wp:columns {"className":"afvf-comment__cards","style":{"spacing":{"blockGap":{"top":"0.6rem","left":"0.6rem"}}}} -->
<div class="wp-block-columns afvf-comment__cards"><!-- wp:column {"className":"afvf-comment-step"} -->
<div class="wp-block-column afvf-comment-step">' . $cm_num( '01.' ) . '

' . $cm_cap( $cm_cap1 ) . '

' . $cm_icon( $cm_user, 'user', __( 'Inscription', 'afvf' ) ) . '

' . $cm_photo( $cm_img1 ) . '</div>
<!-- /wp:column -->

<!-- wp:column {"className":"afvf-comment-step"} -->
<div class="wp-block-column afvf-comment-step">' . $cm_num( '02.' ) . '

' . $cm_cap( $cm_cap2 ) . '

' . $cm_icon( $cm_cup, 'cup', __( 'Cocktail-déjeunatoire', 'afvf' ) ) . '

<!-- wp:paragraph {"className":"afvf-comment-step__text"} -->
<p class="afvf-comment-step__text">' . $cm_text2 . '</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"className":"afvf-comment-step__text"} -->
<p class="afvf-comment-step__text"><strong>' . $cm_menu . '</strong>' . $cm_text2b . '</p>
<!-- /wp:paragraph --></div>
<!-- /wp:column -->

<!-- wp:column {"className":"afvf-comment-step"} -->
<div class="wp-block-column afvf-comment-step">' . $cm_num( '03.' ) . '

' . $cm_cap( $cm_cap3 ) . '

' . $cm_icon( $cm_pin, 'pin', __( 'Lieu', 'afvf' ) ) . '

<!-- wp:paragraph {"align":"center","className":"afvf-comment-step__address"} -->
<p class="has-text-align-center afvf-comment-step__address">' . $cm_addr . '</p>
<!-- /wp:paragraph -->

' . $cm_photo( $cm_img3 ) . '</div>
<!-- /wp:column --></div>
<!-- /wp:columns -->

<!-- wp:buttons {"className":"afvf-comment__more"} -->
<div class="wp-block-buttons afvf-comment__more"><!-- wp:button {"style":{"border":{"radius":"0px"},"color":{"background":"#ffffff","text":"#26408b"}}} -->
<div class="wp-block-button"><a class="wp-block-button__link has-text-color has-background wp-element-button" href="#" style="border-radius:0px;color:#26408b;background-color:#ffffff">' . $cm_btn2 . '</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group -->',
		)
	);

	$support_qr    = esc_url( get_template_directory_uri() . '/assets/images/soutenez-qr.png' );
	$support_img   = esc_url( get_template_directory_uri() . '/assets/images/soutenez-image.jpg' );
	$support_don   = esc_url( afvf_mod( 'afvf_donate_url', 'https://www.helloasso.com/associations/afvf/formulaires/1' ) );
	$support_title = esc_html__( 'SOUTENEZ-NOUS', 'afvf' );
	$support_text  = esc_html__( 'Encouragez notre équipe d’engagé.e.s', 'afvf' );
	$support_btn   = esc_html__( 'Faire un DON', 'afvf' );

	register_block_pattern(
		'afvf/soutenez-nous-wix',
		array(
			'title'       => __( 'Soutenez-nous (colonne bleue + image)', 'afvf' ),
			'description' => _x( 'Deux colonnes : à gauche une colonne bleue (QR code, titre, texte, bouton rose « Faire un DON »), à droite une image seule (les textes sont dans l’image).', 'Block pattern description', 'afvf' ),
			'categories'  => array( 'afvf', 'columns' ),
			'content'     => '<!-- wp:columns {"verticalAlignment":"stretch","align":"full","className":"afvf-support-band","style":{"spacing":{"blockGap":{"top":"0","left":"0"}}}} -->
<div class="wp-block-columns alignfull are-vertically-aligned-stretch afvf-support-band"><!-- wp:column {"verticalAlignment":"center","width":"40%","className":"afvf-support-band__blue","style":{"color":{"background":"#26408b"},"spacing":{"padding":{"top":"1.5rem","bottom":"1.5rem","left":"1.5rem","right":"1.5rem"}}}} -->
<div class="wp-block-column is-vertically-aligned-center afvf-support-band__blue has-background" style="background-color:#26408b;padding-top:1.5rem;padding-right:1.5rem;padding-bottom:1.5rem;padding-left:1.5rem;flex-basis:40%"><!-- wp:image {"align":"center","width":"84px","sizeSlug":"full","linkDestination":"none","className":"afvf-support-band__qr"} -->
<figure class="wp-block-image aligncenter size-full is-resized afvf-support-band__qr"><img src="' . $support_qr . '" alt="' . esc_attr__( 'QR code — faire un don', 'afvf' ) . '" style="width:84px"/></figure>
<!-- /wp:image -->

<!-- wp:heading {"textAlign":"center","level":3,"className":"afvf-support-band__title","style":{"color":{"text":"#ffffff"}}} -->
<h3 class="wp-block-heading has-text-align-center afvf-support-band__title has-text-color" style="color:#ffffff">' . $support_title . '</h3>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center","className":"afvf-support-band__text","style":{"color":{"text":"#ffffff"}}} -->
<p class="has-text-align-center afvf-support-band__text has-text-color" style="color:#ffffff">' . $support_text . '</p>
<!-- /wp:paragraph -->

<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"},"className":"afvf-support-band__cta"} -->
<div class="wp-block-buttons afvf-support-band__cta"><!-- wp:button {"className":"afvf-support-band__btn","style":{"border":{"radius":"0px"},"color":{"background":"#ec5e87","text":"#ffffff"}}} -->
<div class="wp-block-button afvf-support-band__btn"><a class="wp-block-button__link has-text-color has-background wp-element-button" href="' . $support_don . '" target="_blank" rel="noopener" style="border-radius:0px;color:#ffffff;background-color:#ec5e87">' . $support_btn . '</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:column -->

<!-- wp:column {"verticalAlignment":"stretch","width":"60%","className":"afvf-support-band__media","style":{"spacing":{"padding":{"top":"0","bottom":"0","left":"0","right":"0"}}}} -->
<div class="wp-block-column is-vertically-aligned-stretch afvf-support-band__media" style="padding-top:0;padding-right:0;padding-bottom:0;padding-left:0;flex-basis:60%"><!-- wp:image {"sizeSlug":"full","linkDestination":"none","className":"afvf-support-band__img"} -->
<figure class="wp-block-image size-full afvf-support-band__img"><img src="' . $support_img . '" alt="' . esc_attr__( 'Proches, amis, militants : ensemble nous irons plus loin dans le combat contre les féminicides — AFVF', 'afvf' ) . '"/></figure>
<!-- /wp:image --></div>
<!-- /wp:column --></div>
<!-- /wp:columns -->',
		)
	);

	$course_rise_cards = afvf_pattern_course_rise_card(
		__( 'Comprendre les violences faites aux femmes', 'afvf' ),
		__( 'Formateur : Noël AGOSSA', 'afvf' ),
		__( 'Acquérir une compréhension globale des mécanismes de violences', 'afvf' ),
		__( '2 jours — 14 heures', 'afvf' ),
		__( '290 €', 'afvf' ),
		'…',
		home_url( '/formations/' ),
		'sand'
	) . afvf_pattern_course_rise_card(
		__( 'Accompagnement de première ligne', 'afvf' ),
		__( 'Formateur : Noël AGOSSA', 'afvf' ),
		__( 'Savoir accueillir et orienter une victime de manière sécurisée.', 'afvf' ),
		__( '5 jours — 35 heures', 'afvf' ),
		__( '850 €', 'afvf' ),
		'• ' . esc_html__( 'Écoute active et accueil sans jugement…', 'afvf' ),
		home_url( '/formations/' ),
		'peach'
	);

	register_block_pattern(
		'afvf/course-rise-grid',
		array(
			'title'       => __( 'Formations — cartes bandeau bleu (survol)', 'afvf' ),
			'description' => _x( 'Deux cartes cours : photo, bandeau bleu + objectif ; au survol le bandeau bleu monte en haut de la photo (2e photo révélée). Deux images par carte : base + survol.', 'Block pattern description', 'afvf' ),
			'categories'  => array( 'afvf', 'columns' ),
			'content'     => '<!-- wp:group {"align":"wide","className":"afvf-course-rise-grid","layout":{"type":"default"}} -->
<div class="wp-block-group alignwide afvf-course-rise-grid">' . $course_rise_cards . '</div>
<!-- /wp:group -->',
		)
	);

	register_block_pattern(
		'afvf/formations-page-wix',
		array(
			'title'       => __( 'Formations — page complète (Wix)', 'afvf' ),
			'description' => _x( 'Fond gris, titre FORMATIONS, bouton rose Catalogue, introduction, « Public concerné » avec puces, puis les deux cartes formation (le bandeau bleu monte au survol). Chaque carte : image de base + image au survol.', 'Block pattern description', 'afvf' ),
			'categories'  => array( 'afvf', 'columns' ),
			'content'     => '<!-- wp:group {"align":"full","className":"afvf-formations-page","layout":{"type":"default"}} -->
<div class="wp-block-group alignfull afvf-formations-page"><!-- wp:group {"className":"afvf-formations-page__inner","layout":{"type":"default"}} -->
<div class="wp-block-group afvf-formations-page__inner"><!-- wp:group {"className":"afvf-formations-page__head","layout":{"type":"flex","flexWrap":"wrap","verticalAlignment":"center"}} -->
<div class="wp-block-group afvf-formations-page__head"><!-- wp:heading {"level":1,"className":"afvf-formations-page__title"} -->
<h1 class="wp-block-heading afvf-formations-page__title">' . esc_html__( 'FORMATIONS', 'afvf' ) . '</h1>
<!-- /wp:heading -->

<!-- wp:buttons {"className":"afvf-formations-page__catalog-wrap"} -->
<div class="wp-block-buttons afvf-formations-page__catalog-wrap"><!-- wp:button {"className":"afvf-formations-page__catalog"} -->
<div class="wp-block-button afvf-formations-page__catalog"><a class="wp-block-button__link wp-element-button" href="' . esc_url( home_url( '/formations/' ) ) . '">' . esc_html__( 'Catalogue de Formations', 'afvf' ) . '</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group -->

<!-- wp:paragraph {"className":"afvf-formations-page__intro"} -->
<p class="afvf-formations-page__intro">' . esc_html__( 'L’AFVF , Association des Familles de Victimes de Féminicides, propose des parcours de formation spécialisés, ancrés dans la pratique de terrain, et pensés pour répondre aux besoins concrets des professionnels et des institutions.', 'afvf' ) . '</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3,"className":"afvf-formations-page__subtitle"} -->
<h3 class="wp-block-heading afvf-formations-page__subtitle">' . esc_html__( 'Public concerné', 'afvf' ) . '</h3>
<!-- /wp:heading -->

<!-- wp:list {"className":"afvf-formations-page__list"} -->
<ul class="wp-block-list afvf-formations-page__list"><!-- wp:list-item -->
<li>' . esc_html__( 'Professionnels du secteur social, médical ou judiciaire', 'afvf' ) . '</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>' . esc_html__( 'Bénévoles associatifs', 'afvf' ) . '</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>' . esc_html__( 'Structures institutionnelles', 'afvf' ) . '</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>' . esc_html__( 'Personnes souhaitant développer une expertise dans l’accompagnement des victimes', 'afvf' ) . '</li>
<!-- /wp:list-item --></ul>
<!-- /wp:list -->

<!-- wp:group {"className":"afvf-course-rise-grid","layout":{"type":"default"}} -->
<div class="wp-block-group afvf-course-rise-grid">' . $course_rise_cards . '</div>
<!-- /wp:group --></div>
<!-- /wp:group --></div>
<!-- /wp:group -->',
		)
	);

	$mission_title_cls = esc_attr(
		afvf_pattern_layer_classes( 'afvf-mission-banner__title', '55%', '47.7%' )
	);

	register_block_pattern(
		'afvf/mission-banner-label',
		array(
			'title'       => __( 'Bannière titre — « Nos Missions » (étiquette rose sur photo)', 'afvf' ),
			'description' => _x( 'Grande photo en Couverture (choisissez votre image : ciel + ballon cœur) avec le titre en blanc sur un rectangle rose, placé à droite en bas. Hauteur modifiable dans la Couverture ; titre déplaçable avec la poignée « Déplacer ».', 'Block pattern description', 'afvf' ),
			'categories'  => array( 'afvf', 'banner' ),
			'content'     => '<!-- wp:cover {"dimRatio":0,"minHeight":320,"minHeightUnit":"px","align":"full","className":"afvf-mission-banner","style":{"spacing":{"padding":{"top":"0","bottom":"0","left":"0","right":"0"}}}} -->
<div class="wp-block-cover alignfull afvf-mission-banner" style="padding-top:0;padding-right:0;padding-bottom:0;padding-left:0;min-height:320px"><span aria-hidden="true" class="wp-block-cover__background has-background-dim-0 has-background-dim"></span><div class="wp-block-cover__inner-container"><!-- wp:heading {"level":1,"className":"' . $mission_title_cls . '"} -->
<h1 class="wp-block-heading ' . $mission_title_cls . '">' . esc_html__( 'Nos Missions', 'afvf' ) . '</h1>
<!-- /wp:heading --></div></div>
<!-- /wp:cover -->',
		)
	);

	$adhere_url   = esc_url(
		afvf_mod(
			'afvf_member_url',
			'https://pay.qonto.com/payment-links/019e2fee-f9e0-7add-b36a-47b06719677e?resource_id=019e2fee-f9e1-7413-b624-514697f490e8'
		)
	);
	$adhere_label   = esc_html__( 'Adhérer', 'afvf' );
	$adhere_office  = esc_url( get_template_directory_uri() . '/assets/images/adhere-banner-office.png' );
	$adhere_poster  = esc_url( get_template_directory_uri() . '/assets/images/adhere-banner-poster.png' );
	$adhere_hint    = esc_html__(
		'Poignée « Déplacer » sur l’affiche et le bouton Adhérer (même position à l’enregistrement sur le site public). Couverture = photo bureau.',
		'afvf'
	);
	$adhere_poster_cls = esc_attr(
		afvf_pattern_layer_classes( 'afvf-adhere-banner__poster afvf-adhere-cover__poster', '18%', '', '11%' )
	);
	$adhere_cta_cls    = esc_attr(
		afvf_pattern_layer_classes( 'afvf-adhere-banner__cta afvf-adhere-cover__cta', '86%', '46%' )
	);

	register_block_pattern(
		'afvf/adhere-duo-photo-poster',
		array(
			'title'       => __( 'Adhérer — bannière Wix (Couverture + affiche)', 'afvf' ),
			'description' => _x( 'Comme afvf.fr : photo de fond en Couverture, affiche posée à droite par-dessus, bouton Adhérer au centre en bas.', 'Block pattern description', 'afvf' ),
			'categories'  => array( 'afvf', 'banner' ),
			'content'     => '<!-- wp:cover {"url":"' . $adhere_office . '","dimRatio":0,"minHeight":640,"minHeightUnit":"px","align":"full","className":"afvf-adhere-banner afvf-adhere-cover","style":{"spacing":{"padding":{"top":"0","bottom":"2rem","left":"0","right":"0"}}}} -->
<div class="wp-block-cover alignfull afvf-adhere-banner afvf-adhere-cover" style="padding-top:0;padding-right:0;padding-bottom:2rem;padding-left:0;min-height:640px"><img class="wp-block-cover__image-background" alt="" src="' . $adhere_office . '" data-object-fit="cover"/><span aria-hidden="true" class="wp-block-cover__background has-background-dim-0 has-background-dim"></span><div class="wp-block-cover__inner-container"><!-- wp:paragraph {"align":"center","className":"afvf-pattern-hint"} -->
<p class="has-text-align-center afvf-pattern-hint">' . $adhere_hint . '</p>
<!-- /wp:paragraph -->

<!-- wp:image {"sizeSlug":"full","linkDestination":"none","url":"' . $adhere_poster . '","className":"' . $adhere_poster_cls . '"} -->
<figure class="wp-block-image size-full ' . $adhere_poster_cls . '"><img src="' . $adhere_poster . '" alt="' . esc_attr__( 'Affiche — Violences conjugales', 'afvf' ) . '"/></figure>
<!-- /wp:image -->

<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"},"className":"' . $adhere_cta_cls . '"} -->
<div class="wp-block-buttons ' . $adhere_cta_cls . '"><!-- wp:button {"className":"afvf-adhere-banner__btn afvf-adhere-cover__btn","style":{"border":{"radius":"0px"},"color":{"background":"#ffffff","text":"#1f4fa3"}}} -->
<div class="wp-block-button afvf-adhere-banner__btn afvf-adhere-cover__btn"><a class="wp-block-button__link has-text-color has-background wp-element-button" href="' . $adhere_url . '" target="_blank" rel="noopener" style="border-radius:0px;color:#1f4fa3;background-color:#ffffff">' . $adhere_label . '</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div></div>
<!-- /wp:cover -->',
		)
	);
}
add_action( 'init', 'afvf_register_block_patterns' );
