<?php
/**
 * Page standard.
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
				<h1><?php the_title(); ?></h1>
				<?php if ( has_excerpt() ) : ?>
					<p><?php echo esc_html( get_the_excerpt() ); ?></p>
				<?php endif; ?>
			</div>
		</header>
		<?php
	}
	?>
	<section class="<?php echo esc_attr( afvf_page_content_class() ); ?>">
		<?php the_content(); ?>
	</section>
	<?php
endwhile;

get_footer();
