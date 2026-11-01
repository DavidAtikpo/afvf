<?php
/**
 * Fallback template.
 *
 * @package AFVF
 */

get_header();
?>
<section class="wrap prose">
	<?php
	if ( have_posts() ) {
		while ( have_posts() ) {
			the_post();
			the_title( '<h1>', '</h1>' );
			the_content();
		}
	} else {
		echo '<p>' . esc_html__( 'Aucun contenu.', 'afvf' ) . '</p>';
	}
	?>
</section>
<?php
get_footer();
