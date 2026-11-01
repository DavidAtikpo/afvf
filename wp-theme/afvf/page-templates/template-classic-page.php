<?php
/**
 * Template Name: Page — bandeau titre (classique)
 * Description: Bandeau titre automatique du thème + contenu en colonne étroite (sans mode éditeur pleine largeur).
 *
 * @package AFVF
 */

get_header();

while ( have_posts() ) :
	the_post();
	$style = afvf_page_hero_style();
	?>
	<header class="page-hero"<?php echo $style ? ' style="' . esc_attr( $style ) . '"' : ''; ?>>
		<div class="wrap">
			<h1><?php the_title(); ?></h1>
			<?php if ( has_excerpt() ) : ?>
				<p><?php echo esc_html( get_the_excerpt() ); ?></p>
			<?php endif; ?>
		</div>
	</header>
	<section class="wrap prose">
		<?php the_content(); ?>
	</section>
	<?php
endwhile;

get_footer();
