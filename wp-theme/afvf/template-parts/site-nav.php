<?php
/**
 * Navigation principale (barre du haut).
 *
 * @package AFVF
 */

defined( 'ABSPATH' ) || exit;

$donate  = afvf_mod( 'afvf_donate_url', 'https://www.helloasso.com/associations/afvf/formulaires/1' );
$overlay = ! empty( $args['overlay'] );
$classes = 'nav' . ( $overlay ? ' nav--overlay' : '' );
?>
<nav class="<?php echo esc_attr( $classes ); ?>" id="menu" aria-label="<?php esc_attr_e( 'Navigation principale', 'afvf' ); ?>">
	<?php
	wp_nav_menu(
		array(
			'theme_location' => 'primary',
			'container'      => false,
			'menu_class'     => 'menu',
			'fallback_cb'    => 'afvf_menu_fallback',
			'depth'          => 2,
		)
	);
	?>
	<?php if ( $donate ) : ?>
		<a class="nav-donate-badge" href="<?php echo esc_url( $donate ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'FAIRE UN DON', 'afvf' ); ?></a>
	<?php endif; ?>
</nav>
