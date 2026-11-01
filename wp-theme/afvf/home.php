<?php
/**
 * Liste des actualités (page des articles) — cartes « blog Wix ».
 *
 * @package AFVF
 */

get_header();

$page_for_posts = (int) get_option( 'page_for_posts' );
$title          = $page_for_posts ? get_the_title( $page_for_posts ) : __( 'Actualités', 'afvf' );
?>
<header class="page-hero">
	<div class="wrap">
		<p class="eyebrow">AFVF</p>
		<h1><?php echo esc_html( $title ); ?></h1>
	</div>
</header>
<section class="afvf-events-section afvf-news-section">
	<?php if ( have_posts() ) : ?>
		<div class="afvf-news-grid">
			<?php
			while ( have_posts() ) {
				the_post();
				echo afvf_news_card_html( get_post() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
			?>
		</div>
		<nav class="afvf-news-pagination"><?php the_posts_pagination( array( 'prev_text' => '‹', 'next_text' => '›' ) ); ?></nav>
	<?php else : ?>
		<p class="afvf-events-empty"><?php esc_html_e( 'Aucune actualité pour le moment.', 'afvf' ); ?></p>
	<?php endif; ?>
</section>
<?php
get_footer();
