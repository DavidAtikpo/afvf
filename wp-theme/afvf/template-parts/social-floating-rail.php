<?php
/**
 * Barre fixe Facebook / Instagram (côté droit), type Wix.
 *
 * @package AFVF
 */

defined( 'ABSPATH' ) || exit;

if ( ! is_front_page() || ! get_theme_mod( 'afvf_show_floating_social', true ) ) {
	return;
}

$links = array(
	'facebook'  => afvf_social_url( 'afvf_facebook', 'https://www.facebook.com/assofvf/' ),
	'instagram' => afvf_social_url( 'afvf_instagram', 'https://www.instagram.com/assofvf' ),
);

$labels = array(
	'facebook'  => 'Facebook',
	'instagram' => 'Instagram',
);

$links = array_filter( $links );
if ( empty( $links ) ) {
	return;
}
?>
<aside class="afvf-social-rail" aria-label="<?php esc_attr_e( 'Réseaux sociaux', 'afvf' ); ?>">
	<?php foreach ( $links as $key => $url ) : ?>
		<a
			class="afvf-social-rail__link afvf-social-rail__link--<?php echo esc_attr( $key ); ?>"
			href="<?php echo esc_url( $url ); ?>"
			target="_blank"
			rel="noopener noreferrer"
		>
			<?php afvf_social_rail_icon_svg( $key ); ?>
			<span class="screen-reader-text"><?php echo esc_html( $labels[ $key ] ); ?></span>
		</a>
	<?php endforeach; ?>
</aside>
