<?php
/**
 * Actualités (articles) — style « blog Wix » : cartes, page de détail, posts récents.
 *
 * Shortcode : [afvf_actualites count="3" paginate="0" more="1" columns="3"]
 *
 * @package AFVF
 */

defined( 'ABSPATH' ) || exit;

/* -------------------------------------------------------------------------
 * Utilitaires
 * ---------------------------------------------------------------------- */

/**
 * URL de la page « Actualités ».
 *
 * @return string
 */
function afvf_news_archive_url() {
	$posts_page = (int) get_option( 'page_for_posts' );
	if ( $posts_page ) {
		return get_permalink( $posts_page );
	}
	$page = get_page_by_path( 'actualites' );
	return $page ? get_permalink( $page ) : home_url( '/actualites/' );
}

/**
 * @param int $post_id Article.
 * @return int Minutes.
 */
function afvf_news_reading_minutes( $post_id ) {
	$text  = wp_strip_all_tags( strip_shortcodes( (string) get_post_field( 'post_content', $post_id ) ) );
	$words = preg_split( '/\s+/u', trim( $text ), -1, PREG_SPLIT_NO_EMPTY );
	return max( 1, (int) ceil( count( $words ) / 200 ) );
}

/**
 * « 27 sept. · 2 min de lecture »
 *
 * @param int  $post_id   Article.
 * @param bool $with_year Afficher l’année.
 * @return string
 */
function afvf_news_meta_line( $post_id, $with_year = false ) {
	$ts    = get_post_timestamp( $post_id );
	$day   = wp_date( 'j', $ts );
	$month = afvf_event_dot( wp_date( 'M', $ts ) );
	$date  = $day . ' ' . $month . ( $with_year ? ' ' . wp_date( 'Y', $ts ) : '' );
	/* translators: %d: minutes de lecture. */
	$read = sprintf( __( '%d min de lecture', 'afvf' ), afvf_news_reading_minutes( $post_id ) );
	return $date . ' · ' . $read;
}

function afvf_news_author_html( $post_id, $with_year = false ) {
	$logo = esc_url( get_template_directory_uri() . '/assets/images/logo-afvf.png' );

	$bookmark = '<button type="button" class="afvf-news-bookmark" data-post="' . (int) $post_id . '" aria-pressed="false" aria-label="' . esc_attr__( 'Enregistrer', 'afvf' ) . '"><svg viewBox="0 0 24 24" width="14" height="14" aria-hidden="true"><path d="M6 3h12v18l-6-4.5L6 21V3z" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/></svg></button>';

	$menu = '<div class="afvf-news-menu"><button type="button" class="afvf-news-menu__btn" aria-haspopup="true" aria-expanded="false" aria-label="' . esc_attr__( 'Partager', 'afvf' ) . '"><svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true"><circle cx="12" cy="5" r="1.8" fill="currentColor"/><circle cx="12" cy="12" r="1.8" fill="currentColor"/><circle cx="12" cy="19" r="1.8" fill="currentColor"/></svg></button><div class="afvf-news-menu__list" role="menu" hidden>';
	foreach ( afvf_news_share_items( $post_id ) as $item ) {
		/* translators: %s: réseau. */
		$menu .= '<a role="menuitem" href="' . esc_url( $item[0] ) . '" target="_blank" rel="noopener noreferrer">' . esc_html( sprintf( __( 'Partager sur %s', 'afvf' ), $item[1] ) ) . '</a>';
	}
	$menu .= '<button type="button" role="menuitem" class="afvf-post-share__copy" data-url="' . esc_url( get_permalink( $post_id ) ) . '">' . esc_html__( 'Copier le lien', 'afvf' ) . '</button></div></div>';

	return '<div class="afvf-news-author"><img class="afvf-news-author__avatar" src="' . $logo . '" alt="" width="24" height="24" loading="lazy">'
		. '<div class="afvf-news-author__text"><span class="afvf-news-author__name">AFVF</span>'
		. '<span class="afvf-news-author__meta">' . esc_html( afvf_news_meta_line( $post_id, $with_year ) ) . '</span></div>'
		. '<div class="afvf-news-author__actions">' . $bookmark . $menu . '</div></div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG statiques, valeurs échappées.
}

/**
 * Vues · commentaires · cœur.
 *
 * @param int $post_id Article.
 * @return string
 */
function afvf_news_stats_html( $post_id ) {
	$views    = (int) get_post_meta( $post_id, '_afvf_views', true );
	$comments = (int) get_comments_number( $post_id );
	$likes    = (int) get_post_meta( $post_id, '_afvf_likes', true );

	$eye     = '<svg viewBox="0 0 24 24" width="14" height="14" aria-hidden="true"><path d="M1.5 12S5.5 5 12 5s10.5 7 10.5 7-4 7-10.5 7S1.5 12 1.5 12z" fill="none" stroke="currentColor" stroke-width="1.6"/><circle cx="12" cy="12" r="3" fill="none" stroke="currentColor" stroke-width="1.6"/></svg>';
	$comment = '<svg viewBox="0 0 24 24" width="14" height="14" aria-hidden="true"><path d="M4 5h16v11H9l-5 4V5z" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/></svg>';
	$heart   = '<svg viewBox="0 0 24 24" width="15" height="15" aria-hidden="true"><path d="M12 20.5s-8-4.9-8-10.6A4.4 4.4 0 0 1 12 7.6a4.4 4.4 0 0 1 8 2.3c0 5.7-8 10.6-8 10.6z" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/></svg>';

	return '<div class="afvf-news-stats">'
		. '<span class="afvf-news-stats__item" title="' . esc_attr__( 'Vues', 'afvf' ) . '">' . $eye . ' <span>' . $views . '</span></span>'
		. '<span class="afvf-news-stats__item" title="' . esc_attr__( 'Commentaires', 'afvf' ) . '">' . $comment . ' <span>' . $comments . '</span></span>'
		. '<button type="button" class="afvf-news-like" data-post="' . (int) $post_id . '" aria-pressed="false" aria-label="' . esc_attr__( 'J’aime', 'afvf' ) . '">' . $heart . ' <span class="afvf-news-like__n">' . $likes . '</span></button>'
		. '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG statiques, nombres castés.
}

/**
 * ID de la première image du contenu (bloc Image / classe wp-image-N).
 *
 * @param string $content Contenu.
 * @return int
 */
function afvf_news_first_content_image_id( $content ) {
	if ( preg_match( '/<!--\s*wp:(?:image|cover)\s+\{[^}]*"id":(\d+)/', $content, $m ) ) {
		return (int) $m[1];
	}
	if ( preg_match( '/wp-image-(\d+)/', $content, $m ) ) {
		return (int) $m[1];
	}
	return 0;
}

/**
 * Vignette : image mise en avant, sinon première image du texte de l’article.
 *
 * @param WP_Post $post Article.
 * @param string  $size Taille.
 * @return string HTML (vide si aucune image).
 */
function afvf_news_thumb_html( $post, $size = 'medium_large' ) {
	if ( has_post_thumbnail( $post->ID ) ) {
		return get_the_post_thumbnail( $post->ID, $size, array( 'alt' => '' ) );
	}
	$att = afvf_news_first_content_image_id( $post->post_content );
	if ( $att && wp_attachment_is_image( $att ) ) {
		return wp_get_attachment_image( $att, $size, false, array( 'alt' => '' ) );
	}
	if ( preg_match( '/<img[^>]+src=["\']([^"\']+)["\']/i', $post->post_content, $m ) ) {
		return '<img src="' . esc_url( $m[1] ) . '" alt="" loading="lazy">';
	}
	return '';
}

/**
 * À l’enregistrement : si aucune image mise en avant, utiliser la première image du texte.
 *
 * @param int $post_id Article.
 */
function afvf_news_auto_featured_image( $post_id ) {
	if ( wp_is_post_revision( $post_id ) || has_post_thumbnail( $post_id ) ) {
		return;
	}
	$att = afvf_news_first_content_image_id( (string) get_post_field( 'post_content', $post_id ) );
	if ( $att && wp_attachment_is_image( $att ) ) {
		set_post_thumbnail( $post_id, $att );
	}
}
add_action( 'save_post_post', 'afvf_news_auto_featured_image' );

/**
 * Carte d’actualité.
 *
 * @param WP_Post $post    Article.
 * @param bool    $compact Version « Posts récents » (sans auteur ni extrait).
 * @return string
 */
function afvf_news_card_html( $post, $compact = false ) {
	$id    = $post->ID;
	$link  = get_permalink( $id );
	$thumb = afvf_news_thumb_html( $post, 'medium_large' );
	if ( '' === $thumb ) {
		$thumb = '<span class="afvf-news-card__ph" aria-hidden="true"></span>';
	}

	$out  = '<article class="afvf-news-card' . ( $compact ? ' afvf-news-card--compact' : '' ) . '">';
	$out .= '<a class="afvf-news-card__img" href="' . esc_url( $link ) . '">' . $thumb . '</a>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- vignette WP.
	$out .= '<div class="afvf-news-card__body">';
	if ( ! $compact ) {
		$out .= afvf_news_author_html( $id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	$out .= '<h3 class="afvf-news-card__title"><a href="' . esc_url( $link ) . '">' . esc_html( get_the_title( $id ) ) . '</a></h3>';
	if ( ! $compact ) {
		$text = $post->post_excerpt ? $post->post_excerpt : wp_strip_all_tags( strip_shortcodes( $post->post_content ) );
		$out .= '<p class="afvf-news-card__text">' . esc_html( wp_trim_words( $text, 22, '…' ) ) . '</p>';
	}
	$out .= '</div>' . afvf_news_stats_html( $id ) . '</article>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	return $out;
}

/**
 * Grille de cartes pour une requête.
 *
 * @param WP_Query $query   Requête.
 * @param bool     $compact Version compacte.
 * @return string
 */
function afvf_news_grid_html( $query, $compact = false ) {
	$html = '<div class="afvf-news-grid' . ( $compact ? ' afvf-news-grid--compact' : '' ) . '">';
	foreach ( $query->posts as $post ) {
		$html .= afvf_news_card_html( $post, $compact );
	}
	return $html . '</div>';
}

/* -------------------------------------------------------------------------
 * Shortcode
 * ---------------------------------------------------------------------- */

function afvf_actualites_shortcode( $atts ) {
	$atts = shortcode_atts(
		array(
			'count'    => 3,
			'more'     => '1',
			'paginate' => '0',
		),
		$atts,
		'afvf_actualites'
	);

	$per   = max( 1, (int) $atts['count'] );
	$paged = '1' === (string) $atts['paginate'] ? max( 1, isset( $_GET['pg'] ) ? absint( $_GET['pg'] ) : 1 ) : 1; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	$query = new WP_Query(
		array(
			'post_type'           => 'post',
			'post_status'         => 'publish',
			'posts_per_page'      => $per,
			'paged'               => $paged,
			'ignore_sticky_posts' => true,
			'no_found_rows'       => '1' !== (string) $atts['paginate'],
		)
	);
	if ( ! $query->have_posts() ) {
		return '<p class="afvf-events-empty">' . esc_html__( 'Aucune actualité pour le moment.', 'afvf' ) . '</p>';
	}

	$html = afvf_news_grid_html( $query ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

	if ( '1' === (string) $atts['paginate'] && $query->max_num_pages > 1 ) {
		$links = paginate_links(
			array(
				'base'      => esc_url_raw( add_query_arg( 'pg', '%#%', get_permalink() ) ),
				'format'    => '',
				'current'   => $paged,
				'total'     => $query->max_num_pages,
				'prev_text' => '‹',
				'next_text' => '›',
			)
		);
		$html .= '<nav class="afvf-news-pagination" aria-label="' . esc_attr__( 'Pagination', 'afvf' ) . '">' . $links . '</nav>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	wp_reset_postdata();

	if ( '1' === (string) $atts['more'] ) {
		$html .= '<p class="afvf-news-all"><a href="' . esc_url( afvf_news_archive_url() ) . '">' . esc_html__( 'Toutes les actualités', 'afvf' ) . '</a></p>';
	}
	return $html;
}
add_shortcode( 'afvf_actualites', 'afvf_actualites_shortcode' );

/* -------------------------------------------------------------------------
 * Page de détail : partage + posts récents
 * ---------------------------------------------------------------------- */

/**
 * @param int $post_id Article.
 * @return array<int, array{0:string,1:string,2:string}> URL, nom, tracé SVG.
 */
function afvf_news_share_items( $post_id ) {
	$url   = rawurlencode( get_permalink( $post_id ) );
	$title = rawurlencode( get_the_title( $post_id ) );
	return array(
		array( 'https://www.facebook.com/sharer/sharer.php?u=' . $url, 'Facebook', '<path fill="currentColor" d="M16.75 8.25h-2.55V6.6c0-.78.63-1.02 1.08-1.02h1.42V3h-2.35c-2.17 0-3.65 1.65-3.65 3.75v1.5H7.5v2.75h2.35V21h3.65v-9.75h2.55l.35-2.75z"/>' ),
		array( 'https://twitter.com/intent/tweet?url=' . $url . '&text=' . $title, 'X', '<path fill="currentColor" d="M17.8 3h3.1l-6.8 7.7L22 21h-6.2l-4.9-6.4L5.3 21H2.2l7.3-8.3L2 3h6.4l4.4 5.8L17.8 3zm-1.1 16.2h1.7L7.4 4.7H5.6l11.1 14.5z"/>' ),
		array( 'https://www.linkedin.com/sharing/share-offsite/?url=' . $url, 'LinkedIn', '<path fill="currentColor" d="M20.4 20.45h-3.55v-5.57c0-1.33-.03-3.04-1.85-3.04-1.85 0-2.14 1.45-2.14 2.94v5.67H9.35V9h3.41v1.56h.05c.48-.9 1.64-1.85 3.37-1.85 3.6 0 4.27 2.37 4.27 5.46v6.28zM5.34 7.43a2.06 2.06 0 110-4.13 2.06 2.06 0 010 4.13zM7.12 20.45H3.56V9h3.56v11.45z"/>' ),
	);
}

function afvf_news_share_html( $post_id ) {
	$out = '<div class="afvf-post-share">';
	foreach ( afvf_news_share_items( $post_id ) as $i ) {
		$out .= '<a href="' . esc_url( $i[0] ) . '" target="_blank" rel="noopener noreferrer" aria-label="' . esc_attr( $i[1] ) . '"><svg viewBox="0 0 24 24" width="14" height="14" aria-hidden="true">' . $i[2] . '</svg></a>';
	}
	$out .= '<button type="button" class="afvf-post-share__copy" data-url="' . esc_url( get_permalink( $post_id ) ) . '" aria-label="' . esc_attr__( 'Copier le lien', 'afvf' ) . '"><svg viewBox="0 0 24 24" width="14" height="14" aria-hidden="true"><path d="M10 14a4 4 0 0 0 5.7 0l3-3a4 4 0 0 0-5.7-5.7l-1 1M14 10a4 4 0 0 0-5.7 0l-3 3a4 4 0 0 0 5.7 5.7l1-1" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg></button>';
	return $out . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG statiques.
}

/**
 * @param int $exclude_id Article courant.
 * @return string
 */
function afvf_news_recent_html( $exclude_id ) {
	$query = new WP_Query(
		array(
			'post_type'           => 'post',
			'post_status'         => 'publish',
			'posts_per_page'      => 3,
			'post__not_in'        => array( (int) $exclude_id ),
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
		)
	);
	if ( ! $query->have_posts() ) {
		return '';
	}
	$html = '<section class="afvf-post-recent"><div class="afvf-post-recent__head"><h2>' . esc_html__( 'Posts récents', 'afvf' ) . '</h2><a href="' . esc_url( afvf_news_archive_url() ) . '">' . esc_html__( 'Voir tout', 'afvf' ) . '</a></div>'
		. afvf_news_grid_html( $query, true ) . '</section>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	wp_reset_postdata();
	return $html;
}

/* -------------------------------------------------------------------------
 * Compteurs : vues + « j’aime »
 * ---------------------------------------------------------------------- */

function afvf_news_count_view() {
	if ( ! is_singular( 'post' ) || is_preview() || is_user_logged_in() ) {
		return;
	}
	$id = get_queried_object_id();
	update_post_meta( $id, '_afvf_views', (int) get_post_meta( $id, '_afvf_views', true ) + 1 );
}
add_action( 'template_redirect', 'afvf_news_count_view' );

function afvf_news_ajax_like() {
	$id  = isset( $_POST['post'] ) ? absint( $_POST['post'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing -- action publique non sensible.
	$dir = ( isset( $_POST['dir'] ) && 'down' === $_POST['dir'] ) ? -1 : 1; // phpcs:ignore WordPress.Security.NonceVerification.Missing
	if ( ! $id || 'post' !== get_post_type( $id ) || 'publish' !== get_post_status( $id ) ) {
		wp_send_json_error();
	}
	$n = max( 0, (int) get_post_meta( $id, '_afvf_likes', true ) + $dir );
	update_post_meta( $id, '_afvf_likes', $n );
	wp_send_json_success( array( 'likes' => $n ) );
}
add_action( 'wp_ajax_afvf_like', 'afvf_news_ajax_like' );
add_action( 'wp_ajax_nopriv_afvf_like', 'afvf_news_ajax_like' );
