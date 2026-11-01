<?php
/**
 * Icônes sociales centrées (pages type Collectivités — fond gris).
 *
 * @package AFVF
 */

defined( 'ABSPATH' ) || exit;

$networks = array(
	'facebook'  => afvf_social_url( 'afvf_facebook', 'https://www.facebook.com/assofvf/' ),
	'instagram' => afvf_social_url( 'afvf_instagram', 'https://www.instagram.com/assofvf' ),
	'linkedin'  => afvf_social_url( 'afvf_linkedin', 'https://www.linkedin.com/in/association-des-familles-de-victimes-de-feminicides-107893246' ),
);

$labels = array(
	'facebook'  => 'Facebook',
	'instagram' => 'Instagram',
	'linkedin'  => 'LinkedIn',
);

$visible = array_filter( $networks );
if ( empty( $visible ) ) {
	return;
}
?>
<nav class="afvf-page-social" aria-label="<?php esc_attr_e( 'Réseaux sociaux', 'afvf' ); ?>">
	<?php foreach ( $networks as $key => $url ) : ?>
		<?php if ( ! $url ) { continue; } ?>
		<a class="afvf-page-social__link afvf-page-social__link--<?php echo esc_attr( $key ); ?>" href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener noreferrer">
			<?php afvf_social_inline_icon_svg( $key ); ?>
			<span class="screen-reader-text"><?php echo esc_html( $labels[ $key ] ); ?></span>
		</a>
	<?php endforeach; ?>
</nav>
