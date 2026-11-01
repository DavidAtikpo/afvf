<?php
/**
 * Article (actualité) — mise en page « blog Wix » : détail + posts récents.
 *
 * @package AFVF
 */

get_header();

while ( have_posts() ) :
	the_post();
	$post_id = get_the_ID();
	?>
	<div class="afvf-post-single">
		<article <?php post_class( 'afvf-post-single__inner' ); ?>>
			<header class="afvf-post-single__head">
				<?php echo afvf_news_author_html( $post_id, true ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<h1 class="afvf-post-single__title"><?php the_title(); ?></h1>
			</header>

			<div class="afvf-post-single__content">
				<?php the_content(); ?>
				<?php if ( has_post_thumbnail() && false === strpos( get_post_field( 'post_content', $post_id ), '<img' ) ) : ?>
					<figure class="afvf-post-single__cover"><?php the_post_thumbnail( 'large' ); ?></figure>
				<?php endif; ?>
			</div>

			<footer class="afvf-post-single__foot">
				<?php echo afvf_news_share_html( $post_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<?php echo afvf_news_stats_html( $post_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</footer>

		</article>

		<?php echo afvf_news_recent_html( $post_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</div>
	<?php
endwhile;

get_footer();
