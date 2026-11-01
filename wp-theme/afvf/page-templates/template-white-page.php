<?php
/**
 * Template Name: Page — fond blanc (type Wix)
 * Description: Fond de page blanc (au lieu de la teinte crème du thème), contenu pleine largeur comme l’éditeur de blocs.
 *
 * @package AFVF
 */

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<section class="<?php echo esc_attr( afvf_use_editor_layout() ? 'afvf-block-content' : 'wrap prose' ); ?>">
		<?php the_content(); ?>
	</section>
	<?php
endwhile;

get_footer();
