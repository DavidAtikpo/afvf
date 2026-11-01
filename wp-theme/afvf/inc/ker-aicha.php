<?php
/**
 * Page Kër Aïcha — barre contact (téléphone, e-mail, Je participe).
 *
 * @package AFVF
 */

defined( 'ABSPATH' ) || exit;

/**
 * HTML barre contact (front + shortcode legacy).
 *
 * @return string
 */
function afvf_ker_aicha_contact_bar_html() {
	$phone = afvf_mod( 'afvf_phone', '06 26 36 89 94' );
	$email = afvf_mod( 'afvf_email', 'assofvf@gmail.com' );
	$cta   = afvf_mod( 'afvf_donate_url', 'https://www.helloasso.com/associations/afvf/formulaires/1' );

	ob_start();
	?>
	<div class="afvf-ker-aicha-bar-wrap">
	<div class="afvf-ker-aicha-bar" role="region" aria-label="<?php esc_attr_e( 'Contact et participation', 'afvf' ); ?>">
		<div class="afvf-ker-aicha-bar__cell">
			<span class="afvf-ker-aicha-bar__label"><?php esc_html_e( 'Nous appeler', 'afvf' ); ?></span>
			<a class="afvf-ker-aicha-bar__value" href="<?php echo esc_url( afvf_tel_href( $phone ) ); ?>"><?php echo esc_html( $phone ); ?></a>
		</div>
		<div class="afvf-ker-aicha-bar__cell">
			<span class="afvf-ker-aicha-bar__label"><?php esc_html_e( 'E-mail', 'afvf' ); ?></span>
			<a class="afvf-ker-aicha-bar__value" href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a>
		</div>
		<a class="afvf-ker-aicha-bar__cell afvf-ker-aicha-bar__cell--cta" href="<?php echo esc_url( $cta ); ?>" target="_blank" rel="noopener">
			<?php esc_html_e( 'Je participe', 'afvf' ); ?>
		</a>
	</div>
	</div>
	<?php
	return ob_get_clean();
}

/**
 * Motif de blocs : barre contact (sans bloc Code court).
 *
 * @return string
 */
function afvf_ker_aicha_contact_bar_block_pattern() {
	$phone      = afvf_mod( 'afvf_phone', '06 26 36 89 94' );
	$email      = afvf_mod( 'afvf_email', 'assofvf@gmail.com' );
	$cta        = afvf_mod( 'afvf_donate_url', 'https://www.helloasso.com/associations/afvf/formulaires/1' );
	$phone_href = esc_url( afvf_tel_href( $phone ) );
	$email_attr = esc_attr( $email );
	$email_text = esc_html( $email );
	$phone_text = esc_html( $phone );
	$cta_url    = esc_url( $cta );
	$cta_label  = esc_html__( 'Je participe', 'afvf' );
	$label_call = esc_html__( 'Nous appeler', 'afvf' );
	$label_mail = esc_html__( 'E-mail', 'afvf' );

	return '<!-- wp:group {"className":"afvf-ker-aicha-bar-wrap","layout":{"type":"default"}} -->
<div class="wp-block-group afvf-ker-aicha-bar-wrap"><!-- wp:group {"className":"afvf-ker-aicha-bar","layout":{"type":"default"}} -->
<div class="wp-block-group afvf-ker-aicha-bar"><!-- wp:group {"className":"afvf-ker-aicha-bar__cell","layout":{"type":"default"}} -->
<div class="wp-block-group afvf-ker-aicha-bar__cell"><!-- wp:paragraph {"className":"afvf-ker-aicha-bar__label"} -->
<p class="afvf-ker-aicha-bar__label">' . $label_call . '</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p><a class="afvf-ker-aicha-bar__value" href="' . $phone_href . '">' . $phone_text . '</a></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:group {"className":"afvf-ker-aicha-bar__cell","layout":{"type":"default"}} -->
<div class="wp-block-group afvf-ker-aicha-bar__cell"><!-- wp:paragraph {"className":"afvf-ker-aicha-bar__label"} -->
<p class="afvf-ker-aicha-bar__label">' . $label_mail . '</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p><a class="afvf-ker-aicha-bar__value" href="mailto:' . $email_attr . '">' . $email_text . '</a></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:group {"className":"afvf-ker-aicha-bar__cell afvf-ker-aicha-bar__cell--cta","layout":{"type":"default"}} -->
<div class="wp-block-group afvf-ker-aicha-bar__cell afvf-ker-aicha-bar__cell--cta"><!-- wp:paragraph {"align":"center"} -->
<p class="has-text-align-center"><a href="' . $cta_url . '" target="_blank" rel="noopener">' . $cta_label . '</a></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:group --></div>
<!-- /wp:group -->';
}

/**
 * @return string
 */
function afvf_ker_aicha_contact_bar_shortcode() {
	return afvf_ker_aicha_contact_bar_html();
}
add_shortcode( 'afvf_ker_aicha_contact_bar', 'afvf_ker_aicha_contact_bar_shortcode' );

/**
 * Motif de blocs : lien Adhérer (coin haut droit, sans Code court).
 *
 * @return string
 */
function afvf_ker_aicha_adhere_block_pattern() {
	$member = esc_url(
		afvf_mod(
			'afvf_member_url',
			'https://pay.qonto.com/payment-links/019e2fee-f9e0-7add-b36a-47b06719677e?resource_id=019e2fee-f9e1-7413-b624-514697f490e8'
		)
	);
	$label  = esc_html__( 'Adhérer', 'afvf' );

	return '<!-- wp:group {"className":"afvf-ker-aicha-adhere-wrap","layout":{"type":"default"}} -->
<div class="wp-block-group afvf-ker-aicha-adhere-wrap"><!-- wp:paragraph -->
<p><a class="afvf-ker-aicha-adhere" href="' . $member . '" target="_blank" rel="noopener">' . $label . '</a></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->';
}

/**
 * Lien « Adhérer » (coin haut droit, type maquette Kër Aïcha).
 *
 * @return string
 */
function afvf_ker_aicha_adhere_shortcode() {
	$member = afvf_mod(
		'afvf_member_url',
		'https://pay.qonto.com/payment-links/019e2fee-f9e0-7add-b36a-47b06719677e?resource_id=019e2fee-f9e1-7413-b624-514697f490e8'
	);
	return sprintf(
		'<a class="afvf-ker-aicha-adhere" href="%1$s" target="_blank" rel="noopener">%2$s</a>',
		esc_url( $member ),
		esc_html__( 'Adhérer', 'afvf' )
	);
}
add_shortcode( 'afvf_ker_aicha_adhere', 'afvf_ker_aicha_adhere_shortcode' );

/**
 * Ancien calque galerie hors Couverture (pages déjà montées).
 *
 * @param string $inner Markup bannière / grille images.
 * @param string $hint  Aide éditeur.
 * @return string
 */
function afvf_ker_aicha_gallery_float_pattern( $inner, $hint = '' ) {
	$hint_block = '';
	if ( $hint ) {
		$hint_block = '<!-- wp:paragraph {"align":"center","className":"afvf-pattern-hint"} -->
<p class="has-text-align-center afvf-pattern-hint">' . esc_html( $hint ) . '</p>
<!-- /wp:paragraph -->

';
	}

	return '<!-- wp:group {"align":"full","className":"afvf-ker-aicha-gallery-float","layout":{"type":"default"},"style":{"spacing":{"padding":{"top":"0","bottom":"0","left":"1.25rem","right":"1.25rem"},"margin":{"top":"0","bottom":"0"}}}} -->
<div class="wp-block-group alignfull afvf-ker-aicha-gallery-float" style="margin-top:0;margin-bottom:0;padding-top:0;padding-bottom:0;padding-right:1.25rem;padding-left:1.25rem">' . $hint_block . $inner . '</div>
<!-- /wp:group -->';
}

/**
 * Motif Kër Aïcha : une Couverture (fond) + contenu dedans.
 *
 * @param string $section        gallery|card|bar
 * @param int    $min_height_px  Hauteur min.
 * @param string $inner          Blocs à l’intérieur de la Couverture.
 * @param string $hint           Aide éditeur.
 * @return string
 */
function afvf_ker_aicha_section_cover_pattern( $section, $min_height_px, $inner, $hint = '' ) {
	$section = preg_replace( '/[^a-z0-9-]/', '', (string) $section );
	$classes = 'afvf-ker-aicha-band afvf-ker-aicha-band--section afvf-ker-aicha-band--section-' . $section;
	if ( 'gallery' === $section ) {
		$classes .= ' afvf-ker-aicha-page';
	}

	$pad_top    = '2rem';
	$pad_bottom = 'gallery' === $section ? '0' : ( 'bar' === $section ? '2.5rem' : '2rem' );
	$pad_left   = '1.25rem';
	if ( 'card' === $section ) {
		$classes .= ' afvf-ker-aicha-cover-grow';
		$pad_top    = '2.75rem';
		$pad_bottom = '3.25rem';
	}
	if ( 'bar' === $section ) {
		$classes .= ' afvf-ker-aicha-cover-grow';
	}

	$hint_block = '';
	if ( $hint ) {
		$hint_block = '<!-- wp:paragraph {"align":"center","className":"afvf-pattern-hint"} -->
<p class="has-text-align-center afvf-pattern-hint">' . esc_html( $hint ) . '</p>
<!-- /wp:paragraph -->

';
	}

	$min_height_px = max( 0, (int) $min_height_px );
	$class_attr    = esc_attr( $classes );
	$grow          = in_array( $section, array( 'card', 'bar' ), true );

	if ( $grow ) {
		return '<!-- wp:cover {"dimRatio":0,"align":"full","className":"' . $class_attr . '","style":{"spacing":{"padding":{"top":"' . $pad_top . '","bottom":"' . $pad_bottom . '","left":"' . $pad_left . '","right":"' . $pad_left . '"}}}} -->
<div class="wp-block-cover alignfull ' . $class_attr . '" style="padding-top:' . esc_attr( $pad_top ) . ';padding-right:' . esc_attr( $pad_left ) . ';padding-bottom:' . esc_attr( $pad_bottom ) . ';padding-left:' . esc_attr( $pad_left ) . '"><span aria-hidden="true" class="wp-block-cover__background has-background-dim-0 has-background-dim"></span><div class="wp-block-cover__inner-container">' . $hint_block . $inner . '</div></div>
<!-- /wp:cover -->';
	}

	return '<!-- wp:cover {"dimRatio":0,"minHeight":' . $min_height_px . ',"minHeightUnit":"px","align":"full","className":"' . $class_attr . '","style":{"spacing":{"padding":{"top":"' . $pad_top . '","bottom":"' . $pad_bottom . '","left":"' . $pad_left . '","right":"' . $pad_left . '"}}}} -->
<div class="wp-block-cover alignfull ' . $class_attr . '" style="padding-top:' . esc_attr( $pad_top ) . ';padding-right:' . esc_attr( $pad_left ) . ';padding-bottom:' . esc_attr( $pad_bottom ) . ';padding-left:' . esc_attr( $pad_left ) . ';min-height:' . $min_height_px . 'px"><span aria-hidden="true" class="wp-block-cover__background has-background-dim-0 has-background-dim"></span><div class="wp-block-cover__inner-container">' . $hint_block . $inner . '</div></div>
<!-- /wp:cover -->';
}

/**
 * @deprecated Utiliser afvf_ker_aicha_section_cover_pattern().
 */
function afvf_ker_aicha_band_cover_pattern( $modifier, $min_height_px, $inner, $hint = '' ) {
	$modifier = preg_replace( '/[^a-z0-9-]/', '', (string) $modifier );
	$classes  = 'afvf-ker-aicha-band afvf-ker-aicha-band--' . $modifier;
	if ( 'fond' === $modifier ) {
		$classes .= ' afvf-ker-aicha-page';
	}
	if ( 'gallery' === $modifier ) {
		$classes .= ' afvf-ker-aicha-gallery-float';
		$min_height_px = 0;
	}

	$pad_top    = 'fond' === $modifier ? '2rem' : ( 'gallery' === $modifier ? '0' : '1.25rem' );
	$pad_bottom = 'bar' === $modifier ? '3rem' : ( 'fond' === $modifier ? '5rem' : '1.25rem' );
	if ( 'gallery' === $modifier ) {
		$pad_bottom = '0.75rem';
	}

	$hint_block = '';
	if ( $hint ) {
		$hint_block = '<!-- wp:paragraph {"align":"center","className":"afvf-pattern-hint"} -->
<p class="has-text-align-center afvf-pattern-hint">' . esc_html( $hint ) . '</p>
<!-- /wp:paragraph -->

';
	}

	$min_height_px = max( 0, (int) $min_height_px );
	$class_attr    = esc_attr( $classes );

	return '<!-- wp:cover {"dimRatio":0,"minHeight":' . $min_height_px . ',"minHeightUnit":"px","align":"full","className":"' . $class_attr . '","style":{"spacing":{"padding":{"top":"' . $pad_top . '","bottom":"' . $pad_bottom . '","left":"1.25rem","right":"1.25rem"}}}} -->
<div class="wp-block-cover alignfull ' . $class_attr . '" style="padding-top:' . esc_attr( $pad_top ) . ';padding-right:1.25rem;padding-bottom:' . esc_attr( $pad_bottom ) . ';padding-left:1.25rem;min-height:' . $min_height_px . 'px"><span aria-hidden="true" class="wp-block-cover__background has-background-dim-0 has-background-dim"></span><div class="wp-block-cover__inner-container">' . $hint_block . $inner . '</div></div>
<!-- /wp:cover -->';
}

/**
 * Couvertures Kër Aïcha (Motifs 1–3) : pas le mode « calques » bannière Assises.
 *
 * @param array $attrs Attributs bloc core/cover.
 * @return bool
 */
function afvf_cover_is_ker_aicha_section( $attrs ) {
	$class = isset( $attrs['className'] ) ? (string) $attrs['className'] : '';
	return str_contains( $class, 'afvf-ker-aicha-band--section' )
		|| str_contains( $class, 'afvf-ker-aicha-cover-grow' );
}
