<?php
/**
 * Header.
 *
 * @package AFVF
 */

defined( 'ABSPATH' ) || exit;

$phone  = afvf_mod( 'afvf_phone', '06 26 36 89 94' );
$extra  = afvf_mod( 'afvf_topbar_extra', '3919' );
$exit   = afvf_mod( 'afvf_exit_url', 'https://www.service-public.fr/' );
$help   = afvf_help_url();
$member = afvf_mod( 'afvf_member_url' );
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(
	array_filter(
		array(
			get_theme_mod( 'afvf_show_topbar', false ) ? 'has-topbar' : '',
		)
	)
); ?>>
<?php wp_body_open(); ?>
<a class="skip" href="#contenu"><?php esc_html_e( 'Aller au contenu', 'afvf' ); ?></a>

<?php if ( get_theme_mod( 'afvf_show_topbar', false ) ) : ?>
<div class="topbar">
	<span>
		<a href="<?php echo esc_url( $help ); ?>"><?php esc_html_e( "Besoin d'aide ?", 'afvf' ); ?></a>
		· <a href="<?php echo esc_url( afvf_tel_href( $phone ) ); ?>"><?php echo esc_html( $phone ); ?></a>
		<?php if ( $extra ) : ?>
			· <?php echo esc_html( $extra ); ?>
		<?php endif; ?>
	</span>
	<a class="exit" href="<?php echo esc_url( $exit ); ?>" rel="noopener"><?php esc_html_e( 'Quitter rapidement', 'afvf' ); ?></a>
</div>
<?php endif; ?>

<header class="header">
	<div class="header-inner">
		<?php get_template_part( 'template-parts/site', 'nav' ); ?>
		<?php get_template_part( 'template-parts/burger', 'button' ); ?>
	</div>
</header>

<main id="contenu"<?php echo is_front_page() ? ' class="main-front"' : ''; ?>>
