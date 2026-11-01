<?php
/**
 * 404.
 *
 * @package AFVF
 */

get_header();
?>
<header class="page-hero">
	<div class="wrap">
		<h1><?php esc_html_e( 'Page introuvable', 'afvf' ); ?></h1>
		<p><?php esc_html_e( 'Le contenu a peut-être été déplacé. Utilisez le menu ou revenez à l’accueil.', 'afvf' ); ?></p>
		<p><a class="btn light" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Accueil', 'afvf' ); ?></a></p>
	</div>
</header>
<?php
get_footer();
