<?php
/**
 * Icônes réseaux sociaux (pied de page).
 *
 * @package AFVF
 */

defined( 'ABSPATH' ) || exit;

$networks = array(
	'whatsapp'  => afvf_social_url( 'afvf_whatsapp', 'https://wa.me/33626368994' ),
	'x'         => afvf_social_url( 'afvf_x', 'https://x.com/assofvf' ),
	'instagram' => afvf_social_url( 'afvf_instagram', 'https://www.instagram.com/assofvf' ),
	'facebook'  => afvf_social_url( 'afvf_facebook', 'https://www.facebook.com/assofvf/' ),
	'linkedin'  => afvf_social_url( 'afvf_linkedin', 'https://www.linkedin.com/in/association-des-familles-de-victimes-de-feminicides-107893246' ),
	'youtube'   => afvf_social_url( 'afvf_youtube', 'https://www.youtube.com/@afvfmedia' ),
);

$labels = array(
	'whatsapp'  => 'WhatsApp',
	'x'         => 'X',
	'instagram' => 'Instagram',
	'facebook'  => 'Facebook',
	'linkedin'  => 'LinkedIn',
	'youtube'   => 'YouTube',
);
?>
<ul class="footer-social" aria-label="<?php esc_attr_e( 'Réseaux sociaux', 'afvf' ); ?>">
	<?php foreach ( $networks as $key => $url ) : ?>
		<?php if ( ! $url ) { continue; } ?>
		<li>
			<a class="footer-social__link footer-social__link--<?php echo esc_attr( $key ); ?>" href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener noreferrer">
				<?php afvf_social_icon_svg( $key ); ?>
				<span class="screen-reader-text"><?php echo esc_html( $labels[ $key ] ); ?></span>
			</a>
		</li>
	<?php endforeach; ?>
</ul>
