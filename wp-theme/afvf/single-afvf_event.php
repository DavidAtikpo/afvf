<?php
/**
 * Page d’un événement — mise en page « Wix » : bandeau bleu, image, heure et lieu, à propos, inscription.
 *
 * @package AFVF
 */

get_header();

while ( have_posts() ) :
	the_post();
	$post_id = get_the_ID();
	$d       = afvf_event_data( $post_id );
	$tickets = afvf_event_tickets( $post_id );
	$meta    = afvf_event_meta_line( $d );
	$when    = afvf_event_when_text( $d );
	$thumb   = get_post_thumbnail_id( $post_id );

	// Page « commande » (après « Passer la commande »).
	$token = isset( $_GET['commande'] ) ? preg_replace( '/[^A-Za-z0-9]/', '', sanitize_text_field( wp_unslash( $_GET['commande'] ) ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$order = $token ? afvf_event_get_order( $token, $post_id ) : null;
	if ( $order ) {
		echo '<div class="afvf-event-single afvf-event-single--checkout">' . afvf_event_checkout_html( $post_id, $order, $token ) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		continue;
	}

	// Bouton du bandeau : mène à la section « Inscription » de cette page.
	$cta_href = ( $tickets || $d['ticket_url'] ) ? '#inscription' : '';
	$cta_ext  = false;

	// Le contenu : par défaut la 1re image n’est pas répétée si c’est déjà l’image de couverture.
	$content = apply_filters( 'the_content', get_the_content() );
	$in_body = $thumb && afvf_news_first_content_image_id( get_post_field( 'post_content', $post_id ) ) === (int) $thumb;
	if ( '1' === $d['repeat_cover'] ) {
		if ( $thumb && ! $in_body ) {
			$content = '<figure class="wp-block-image">' . wp_get_attachment_image( $thumb, 'large' ) . '</figure>' . $content;
		}
	} elseif ( $in_body ) {
		$content = preg_replace( '#<figure[^>]*class="[^"]*wp-block-image[^"]*"[^>]*>.*?</figure>#is', '', $content, 1 );
	}
	?>
	<div class="afvf-event-single">
		<header class="afvf-ev-hero">
			<div class="afvf-ev-hero__inner">
				<h1 class="afvf-ev-hero__title"><?php the_title(); ?></h1>
				<?php if ( $meta ) : ?>
					<p class="afvf-ev-hero__meta"><?php echo esc_html( $meta ); ?></p>
				<?php endif; ?>
				<?php if ( $cta_href ) : ?>
					<p><a class="afvf-ev-btn" href="<?php echo esc_url( $cta_href ); ?>"<?php echo $cta_ext ? ' target="_blank" rel="noopener noreferrer"' : ''; ?>><?php echo esc_html( $d['ticket_label'] ); ?></a></p>
				<?php endif; ?>
			</div>
		</header>

		<div class="afvf-ev-body">
			<?php if ( $thumb ) : ?>
				<figure class="afvf-ev-cover"><?php echo wp_get_attachment_image( $thumb, 'large' ); ?></figure>
			<?php endif; ?>

			<?php if ( $when || $d['place'] ) : ?>
				<section class="afvf-ev-block">
					<h2><?php esc_html_e( 'Heure et lieu', 'afvf' ); ?></h2>
					<?php if ( $when ) : ?>
						<p><?php echo esc_html( $when ); ?></p>
					<?php endif; ?>
					<?php if ( $d['place'] ) : ?>
						<p><?php echo esc_html( $d['place'] ); ?></p>
					<?php endif; ?>
				</section>
			<?php endif; ?>

			<?php if ( trim( wp_strip_all_tags( $content ) ) || false !== strpos( $content, '<img' ) ) : ?>
				<section class="afvf-ev-block afvf-ev-about">
					<h2><?php esc_html_e( 'À propos de l’événement', 'afvf' ); ?></h2>
					<div class="afvf-ev-about__content"><?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- the_content filtré. ?></div>
				</section>
			<?php endif; ?>

			<?php echo afvf_event_registration_html( $post_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

			<?php echo afvf_event_map_html( $d['address'] ? $d['address'] : '' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</div>
	</div>
	<?php
endwhile;

get_footer();
