<?php
/**
 * Template Name: Besoin d'aide
 * Description: Mise en page pour la page d'urgence et d'écoute.
 *
 * @package AFVF
 */

get_header();

while ( have_posts() ) :
	the_post();
	if ( ! afvf_use_editor_layout() ) {
		$style = afvf_page_hero_style();
		?>
		<header class="page-hero"<?php echo $style ? ' style="' . esc_attr( $style ) . '"' : ''; ?>>
			<div class="wrap">
				<p class="eyebrow"><?php esc_html_e( 'Urgence et écoute', 'afvf' ); ?></p>
				<h1><?php the_title(); ?></h1>
				<?php if ( has_excerpt() ) : ?>
					<p><?php echo esc_html( get_the_excerpt() ); ?></p>
				<?php endif; ?>
			</div>
		</header>
		<section class="wrap prose">
			<div class="alert">
				<strong><?php esc_html_e( 'En danger immédiat : 17', 'afvf' ); ?></strong>
				<?php esc_html_e( '(police secours), 112 (urgence européenne), 114 (SMS pour les personnes sourdes et malentendantes).', 'afvf' ); ?>
			</div>
			<?php the_content(); ?>
		</section>
		<?php
	} else {
		?>
		<section class="afvf-block-content">
			<?php the_content(); ?>
		</section>
		<?php
	}
endwhile;

get_footer();
