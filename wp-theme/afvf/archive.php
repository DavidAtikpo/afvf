<?php
/**
 * Archives (catégories, dates…).
 *
 * @package AFVF
 */

get_header();

$title = get_the_archive_title();
?>
<header class="page-hero">
	<div class="wrap">
		<h1><?php echo wp_kses_post( $title ); ?></h1>
	</div>
</header>
<section class="wrap">
	<?php if ( have_posts() ) : ?>
		<div class="grid-3">
			<?php
			while ( have_posts() ) :
				the_post();
				?>
				<article <?php post_class( 'card' ); ?>>
					<a href="<?php the_permalink(); ?>">
						<?php if ( has_post_thumbnail() ) : ?>
							<?php the_post_thumbnail( 'medium_large' ); ?>
						<?php endif; ?>
						<h3><?php the_title(); ?></h3>
						<p><?php echo esc_html( get_the_excerpt() ); ?></p>
					</a>
				</article>
				<?php
			endwhile;
			?>
		</div>
		<?php the_posts_pagination(); ?>
	<?php else : ?>
		<p><?php esc_html_e( 'Aucun contenu.', 'afvf' ); ?></p>
	<?php endif; ?>
</section>
<?php
get_footer();
