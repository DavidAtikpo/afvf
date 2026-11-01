<?php
/**
 * Événements (type de contenu « Événements ») + Actualités (articles) affichés sur le site / l’accueil.
 *
 * - Menu admin « Événements » : créer / modifier / supprimer des événements.
 * - Menu admin « Actualités » : les articles WordPress.
 * - Shortcodes : [afvf_events count="4"] et [afvf_actualites count="3"].
 * - Motifs : « Événements à venir (automatique) » et « Actualités — 3 dernières (automatique) ».
 *
 * @package AFVF
 */

defined( 'ABSPATH' ) || exit;

/* -------------------------------------------------------------------------
 * Type de contenu « Événement »
 * ---------------------------------------------------------------------- */

function afvf_register_event_post_type() {
	register_post_type(
		'afvf_event',
		array(
			'labels'              => array(
				'name'               => __( 'Événements', 'afvf' ),
				'singular_name'      => __( 'Événement', 'afvf' ),
				'menu_name'          => __( 'Événements', 'afvf' ),
				'all_items'          => __( 'Tous les événements', 'afvf' ),
				'add_new'            => __( 'Ajouter', 'afvf' ),
				'add_new_item'       => __( 'Ajouter un événement', 'afvf' ),
				'edit_item'          => __( 'Modifier l’événement', 'afvf' ),
				'new_item'           => __( 'Nouvel événement', 'afvf' ),
				'view_item'          => __( 'Voir l’événement', 'afvf' ),
				'search_items'       => __( 'Rechercher des événements', 'afvf' ),
				'not_found'          => __( 'Aucun événement trouvé.', 'afvf' ),
				'not_found_in_trash' => __( 'Aucun événement dans la corbeille.', 'afvf' ),
				'featured_image'     => __( 'Affiche / photo de l’événement', 'afvf' ),
				'set_featured_image' => __( 'Choisir l’affiche', 'afvf' ),
			),
			'public'              => true,
			'show_in_rest'        => true,
			'menu_icon'           => 'dashicons-calendar-alt',
			'menu_position'       => 5,
			'supports'            => array( 'title', 'editor', 'excerpt', 'thumbnail' ),
			'has_archive'         => false,
			'rewrite'             => array(
				'slug'       => 'evenement',
				'with_front' => false,
			),
			'exclude_from_search' => false,
		)
	);

	// Une seule fois : prendre en compte le nouveau type dans les permaliens.
	if ( '1' !== get_option( 'afvf_events_rewrite_done' ) ) {
		flush_rewrite_rules( false );
		update_option( 'afvf_events_rewrite_done', '1' );
	}
}
add_action( 'init', 'afvf_register_event_post_type', 5 );

/* -------------------------------------------------------------------------
 * « Articles » = « Actualités » dans l’administration
 * ---------------------------------------------------------------------- */

function afvf_relabel_post_object() {
	global $wp_post_types;
	if ( ! isset( $wp_post_types['post'] ) ) {
		return;
	}
	$l                     = $wp_post_types['post']->labels;
	$l->name               = __( 'Actualités', 'afvf' );
	$l->singular_name      = __( 'Actualité', 'afvf' );
	$l->menu_name          = __( 'Actualités', 'afvf' );
	$l->all_items          = __( 'Toutes les actualités', 'afvf' );
	$l->add_new            = __( 'Ajouter', 'afvf' );
	$l->add_new_item       = __( 'Ajouter une actualité', 'afvf' );
	$l->edit_item          = __( 'Modifier l’actualité', 'afvf' );
	$l->new_item           = __( 'Nouvelle actualité', 'afvf' );
	$l->view_item          = __( 'Voir l’actualité', 'afvf' );
	$l->search_items       = __( 'Rechercher des actualités', 'afvf' );
	$l->not_found          = __( 'Aucune actualité trouvée.', 'afvf' );
	$l->not_found_in_trash = __( 'Aucune actualité dans la corbeille.', 'afvf' );
	$l->name_admin_bar     = __( 'Actualité', 'afvf' );
}
add_action( 'init', 'afvf_relabel_post_object', 20 );

function afvf_relabel_posts_menu() {
	global $menu, $submenu;
	if ( isset( $menu[5][0] ) ) {
		$menu[5][0] = __( 'Actualités', 'afvf' ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
	}
	if ( isset( $submenu['edit.php'][5][0] ) ) {
		$submenu['edit.php'][5][0] = __( 'Toutes les actualités', 'afvf' ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
	}
	if ( isset( $submenu['edit.php'][10][0] ) ) {
		$submenu['edit.php'][10][0] = __( 'Ajouter une actualité', 'afvf' ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
	}
}
add_action( 'admin_menu', 'afvf_relabel_posts_menu', 999 );

/* -------------------------------------------------------------------------
 * Données d’un événement (champs personnalisés)
 * ---------------------------------------------------------------------- */

/**
 * @return string[]
 */
function afvf_event_fields() {
	return array( 'date', 'end_date', 'start', 'end', 'place', 'subtitle', 'ticket_url', 'ticket_label', 'more_url', 'tickets', 'address', 'repeat_cover' );
}

/**
 * @param int $post_id Événement.
 * @return array<string, string>
 */
function afvf_event_data( $post_id ) {
	$data = array();
	foreach ( afvf_event_fields() as $key ) {
		$data[ $key ] = (string) get_post_meta( $post_id, '_afvf_event_' . $key, true );
	}
	if ( '' === $data['ticket_label'] ) {
		$data['ticket_label'] = __( 'Réserver des places', 'afvf' );
	}
	return $data;
}

/**
 * Formate une date AAAA-MM-JJ avec le format PHP donné (langue du site).
 *
 * @param string $ymd    Date.
 * @param string $format Format.
 * @return string
 */
function afvf_event_date_i18n( $ymd, $format ) {
	if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', (string) $ymd ) ) {
		return '';
	}
	$ts = strtotime( $ymd . ' 12:00:00 UTC' );
	return $ts ? wp_date( $format, $ts ) : '';
}

/**
 * Ajoute un point final aux abréviations (« sept » → « sept. »).
 *
 * @param string $s Texte.
 * @return string
 */
function afvf_event_dot( $s ) {
	$s = function_exists( 'mb_strtolower' ) ? mb_strtolower( $s ) : strtolower( $s );
	return ( '' !== $s && '.' !== substr( $s, -1 ) ) ? $s . '.' : $s;
}

/* -------------------------------------------------------------------------
 * Page de création : encadré « Détails de l’événement »
 * ---------------------------------------------------------------------- */

function afvf_event_add_meta_box() {
	add_meta_box(
		'afvf_event_details',
		__( 'Détails de l’événement', 'afvf' ),
		'afvf_event_render_meta_box',
		'afvf_event',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'afvf_event_add_meta_box' );

function afvf_event_render_meta_box( $post ) {
	$d = afvf_event_data( $post->ID );
	wp_nonce_field( 'afvf_event_save', 'afvf_event_nonce' );
	?>
	<style>
		.afvf-event-box { display:grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px 18px; }
		.afvf-event-box label { display:block; font-weight:600; margin-bottom:3px; }
		.afvf-event-box input { width:100%; }
		.afvf-event-box .afvf-wide { grid-column: 1 / -1; }
		.afvf-event-box small { color:#666; }
	</style>
	<div class="afvf-event-box">
		<p>
			<label for="afvf_event_date"><?php esc_html_e( 'Date de début *', 'afvf' ); ?></label>
			<input type="date" id="afvf_event_date" name="afvf_event[date]" value="<?php echo esc_attr( $d['date'] ); ?>" required>
		</p>
		<p>
			<label for="afvf_event_end_date"><?php esc_html_e( 'Date de fin (si plusieurs jours)', 'afvf' ); ?></label>
			<input type="date" id="afvf_event_end_date" name="afvf_event[end_date]" value="<?php echo esc_attr( $d['end_date'] ); ?>">
		</p>
		<p>
			<label for="afvf_event_start"><?php esc_html_e( 'Heure de début', 'afvf' ); ?></label>
			<input type="time" id="afvf_event_start" name="afvf_event[start]" value="<?php echo esc_attr( $d['start'] ); ?>">
		</p>
		<p>
			<label for="afvf_event_end"><?php esc_html_e( 'Heure de fin', 'afvf' ); ?></label>
			<input type="time" id="afvf_event_end" name="afvf_event[end]" value="<?php echo esc_attr( $d['end'] ); ?>">
		</p>
		<p class="afvf-wide">
			<label for="afvf_event_place"><?php esc_html_e( 'Lieu', 'afvf' ); ?></label>
			<input type="text" id="afvf_event_place" name="afvf_event[place]" value="<?php echo esc_attr( $d['place'] ); ?>" placeholder="Parc départemental de la Plage Bleue, Valenton (94)">
		</p>
		<p class="afvf-wide">
			<label for="afvf_event_subtitle"><?php esc_html_e( 'Sous-titre (affiché après la date)', 'afvf' ); ?></label>
			<input type="text" id="afvf_event_subtitle" name="afvf_event[subtitle]" value="<?php echo esc_attr( $d['subtitle'] ); ?>" placeholder="Cagnotte pour la Maison des Familles">
		</p>
		<p>
			<label for="afvf_event_ticket_url"><?php esc_html_e( 'Lien du bouton (réservation / billetterie)', 'afvf' ); ?></label>
			<input type="url" id="afvf_event_ticket_url" name="afvf_event[ticket_url]" value="<?php echo esc_attr( $d['ticket_url'] ); ?>" placeholder="https://…">
			<small><?php esc_html_e( 'Laissez vide : pas de bouton.', 'afvf' ); ?></small>
		</p>
		<p>
			<label for="afvf_event_ticket_label"><?php esc_html_e( 'Texte du bouton', 'afvf' ); ?></label>
			<input type="text" id="afvf_event_ticket_label" name="afvf_event[ticket_label]" value="<?php echo esc_attr( get_post_meta( $post->ID, '_afvf_event_ticket_label', true ) ); ?>" placeholder="<?php esc_attr_e( 'Réserver des places', 'afvf' ); ?>">
		</p>
		<p class="afvf-wide">
			<label for="afvf_event_more_url"><?php esc_html_e( 'Lien « Plus d’infos » (facultatif)', 'afvf' ); ?></label>
			<input type="url" id="afvf_event_more_url" name="afvf_event[more_url]" value="<?php echo esc_attr( $d['more_url'] ); ?>" placeholder="<?php esc_attr_e( 'Par défaut : la page de l’événement', 'afvf' ); ?>">
		</p>
		<p class="afvf-wide">
			<label for="afvf_event_address"><?php esc_html_e( 'Adresse complète pour la carte (facultatif)', 'afvf' ); ?></label>
			<input type="text" id="afvf_event_address" name="afvf_event[address]" value="<?php echo esc_attr( $d['address'] ); ?>" placeholder="Parc départemental de la Plage Bleue, 94460 Valenton">
			<small><?php esc_html_e( 'Si renseignée, une carte Google Maps s’affiche en bas de la page de l’événement.', 'afvf' ); ?></small>
		</p>
		<p class="afvf-wide">
			<label><input type="checkbox" name="afvf_event[repeat_cover]" value="1" <?php checked( '1', $d['repeat_cover'] ); ?> style="width:auto"> <?php esc_html_e( 'Afficher aussi l’affiche en entier dans « À propos de l’événement »', 'afvf' ); ?></label>
			<small><?php esc_html_e( 'Par défaut l’image mise en avant n’apparaît qu’une fois, en haut. Cochez pour la répéter sous le texte (comme sur Wix).', 'afvf' ); ?></small>
		</p>
		<p class="afvf-wide">
			<label for="afvf_event_tickets"><?php esc_html_e( 'Billets / places (page « Réserver des places »)', 'afvf' ); ?></label>
			<textarea id="afvf_event_tickets" name="afvf_event[tickets]" rows="4" style="width:100%" placeholder="Place adulte | 10 | Entrée plein tarif&#10;Place enfant | 5&#10;Don libre | 0 | Merci pour votre soutien"><?php echo esc_textarea( $d['tickets'] ); ?></textarea>
			<small><?php esc_html_e( 'Un billet par ligne : Nom | Prix en € | Description (facultative). Prix 0 = gratuit. Laissez vide pour un simple bouton vers le lien de réservation ci-dessus.', 'afvf' ); ?></small>
		</p>
		<p class="afvf-wide"><small><?php esc_html_e( 'Astuce : avec une « Image mise en avant » (affiche), la carte s’affiche avec la photo ; sans image, elle affiche le détail (date, lieu, description, partage). Le texte de l’éditeur ou « Extrait » sert de description.', 'afvf' ); ?></small></p>
	</div>
	<?php
}

function afvf_event_save_meta( $post_id ) {
	if ( ! isset( $_POST['afvf_event_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['afvf_event_nonce'] ) ), 'afvf_event_save' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	$in = isset( $_POST['afvf_event'] ) && is_array( $_POST['afvf_event'] ) ? wp_unslash( $_POST['afvf_event'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitized below.

	$clean = array();
	foreach ( array( 'date', 'end_date' ) as $k ) {
		$v           = isset( $in[ $k ] ) ? sanitize_text_field( $in[ $k ] ) : '';
		$clean[ $k ] = preg_match( '/^\d{4}-\d{2}-\d{2}$/', $v ) ? $v : '';
	}
	foreach ( array( 'start', 'end' ) as $k ) {
		$v           = isset( $in[ $k ] ) ? sanitize_text_field( $in[ $k ] ) : '';
		$clean[ $k ] = preg_match( '/^\d{2}:\d{2}$/', $v ) ? $v : '';
	}
	foreach ( array( 'place', 'subtitle', 'ticket_label' ) as $k ) {
		$clean[ $k ] = isset( $in[ $k ] ) ? sanitize_text_field( $in[ $k ] ) : '';
	}
	$clean['tickets']      = isset( $in['tickets'] ) ? sanitize_textarea_field( $in['tickets'] ) : '';
	$clean['address']      = isset( $in['address'] ) ? sanitize_text_field( $in['address'] ) : '';
	$clean['repeat_cover'] = ( isset( $in['repeat_cover'] ) && '1' === $in['repeat_cover'] ) ? '1' : '';
	foreach ( array( 'ticket_url', 'more_url' ) as $k ) {
		$clean[ $k ] = isset( $in[ $k ] ) ? esc_url_raw( trim( $in[ $k ] ) ) : '';
	}

	foreach ( $clean as $k => $v ) {
		if ( '' === $v ) {
			delete_post_meta( $post_id, '_afvf_event_' . $k );
		} else {
			update_post_meta( $post_id, '_afvf_event_' . $k, $v );
		}
	}

	// Dernier jour (sert à masquer l’événement une fois passé).
	$last = $clean['end_date'] ? $clean['end_date'] : $clean['date'];
	if ( $last && $clean['end_date'] && $clean['date'] && $clean['end_date'] < $clean['date'] ) {
		$last = $clean['date'];
	}
	if ( $last ) {
		update_post_meta( $post_id, '_afvf_event_last', $last );
	} else {
		delete_post_meta( $post_id, '_afvf_event_last' );
	}
}
add_action( 'save_post_afvf_event', 'afvf_event_save_meta' );

/* Pas d’affiche choisie : on prend la première image du texte. */
add_action( 'save_post_afvf_event', 'afvf_news_auto_featured_image', 20 );

/* Colonnes de la liste admin */
function afvf_event_admin_columns( $cols ) {
	$out = array();
	foreach ( $cols as $key => $label ) {
		$out[ $key ] = $label;
		if ( 'title' === $key ) {
			$out['afvf_date']  = __( 'Date', 'afvf' );
			$out['afvf_place'] = __( 'Lieu', 'afvf' );
		}
	}
	return $out;
}
add_filter( 'manage_afvf_event_posts_columns', 'afvf_event_admin_columns' );

function afvf_event_admin_column_content( $col, $post_id ) {
	$d = afvf_event_data( $post_id );
	if ( 'afvf_date' === $col ) {
		$txt = $d['date'] ? afvf_event_date_i18n( $d['date'], 'j F Y' ) : '—';
		if ( $d['date'] && $d['start'] ) {
			$txt .= ' · ' . $d['start'];
		}
		echo esc_html( $txt );
		$last = (string) get_post_meta( $post_id, '_afvf_event_last', true );
		if ( $last && $last < wp_date( 'Y-m-d' ) ) {
			echo ' <em style="color:#a00">(' . esc_html__( 'passé', 'afvf' ) . ')</em>';
		}
	}
	if ( 'afvf_place' === $col ) {
		echo esc_html( $d['place'] ? $d['place'] : '—' );
	}
}
add_action( 'manage_afvf_event_posts_custom_column', 'afvf_event_admin_column_content', 10, 2 );

function afvf_event_sortable_columns( $cols ) {
	$cols['afvf_date'] = 'afvf_date';
	return $cols;
}
add_filter( 'manage_edit-afvf_event_sortable_columns', 'afvf_event_sortable_columns' );

function afvf_event_admin_order( $query ) {
	if ( ! is_admin() || ! $query->is_main_query() || 'afvf_event' !== $query->get( 'post_type' ) ) {
		return;
	}
	// Tri par date d’événement uniquement si demandé (clic sur la colonne « Date »).
	// Les événements sans date restent listés (jointure « EXISTS » ou « NOT EXISTS »).
	if ( 'afvf_date' === $query->get( 'orderby' ) ) {
		$query->set(
			'meta_query', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			array(
				'relation'    => 'OR',
				'afvf_has'    => array(
					'key'     => '_afvf_event_date',
					'compare' => 'EXISTS',
				),
				'afvf_no_has' => array(
					'key'     => '_afvf_event_date',
					'compare' => 'NOT EXISTS',
				),
			)
		);
		$query->set( 'orderby', 'afvf_has' );
	}
}
add_action( 'pre_get_posts', 'afvf_event_admin_order' );

/* -------------------------------------------------------------------------
 * Requêtes
 * ---------------------------------------------------------------------- */

/**
 * Événements à venir ou en cours, du plus proche au plus lointain.
 * Un événement sans date reste affiché (en dernier) tant qu’il est publié.
 *
 * @param int $count Nombre max.
 * @return WP_Post[]
 */
function afvf_events_upcoming( $count = 4 ) {
	$today = wp_date( 'Y-m-d' );
	$all   = get_posts(
		array(
			'post_type'        => 'afvf_event',
			'post_status'      => 'publish',
			'posts_per_page'   => 100,
			'orderby'          => 'date',
			'order'            => 'DESC',
			'suppress_filters' => false,
		)
	);
	$keep  = array();
	foreach ( $all as $post ) {
		$d    = afvf_event_data( $post->ID );
		$last = $d['end_date'] && $d['end_date'] >= $d['date'] ? $d['end_date'] : $d['date'];
		if ( $last && $last < $today ) {
			continue; // Passé.
		}
		$keep[] = array( $d['date'] ? $d['date'] : '9999-12-31', $post );
	}
	usort(
		$keep,
		static function ( $x, $y ) {
			return strcmp( $x[0], $y[0] );
		}
	);
	$posts = array();
	foreach ( array_slice( $keep, 0, max( 1, (int) $count ) ) as $row ) {
		$posts[] = $row[1];
	}
	return $posts;
}

/* -------------------------------------------------------------------------
 * Affichage d’une carte événement
 * ---------------------------------------------------------------------- */

function afvf_event_icon_clock() {
	return '<svg viewBox="0 0 24 24" width="12" height="12" aria-hidden="true"><circle cx="12" cy="12" r="9" fill="none" stroke="currentColor" stroke-width="2"/><path d="M12 7v5l3 2" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>';
}

/**
 * Icônes de partage (Facebook, X, LinkedIn).
 *
 * @param int $post_id Événement.
 * @return string
 */
function afvf_event_share_html( $post_id ) {
	$url   = rawurlencode( get_permalink( $post_id ) );
	$title = rawurlencode( get_the_title( $post_id ) );
	$links = array(
		'facebook' => array( 'https://www.facebook.com/sharer/sharer.php?u=' . $url, 'Facebook', '<svg viewBox="0 0 24 24" width="12" height="12" aria-hidden="true"><path fill="currentColor" d="M16.75 8.25h-2.55V6.6c0-.78.63-1.02 1.08-1.02h1.42V3h-2.35c-2.17 0-3.65 1.65-3.65 3.75v1.5H7.5v2.75h2.35V21h3.65v-9.75h2.55l.35-2.75z"/></svg>' ),
		'x'        => array( 'https://twitter.com/intent/tweet?url=' . $url . '&text=' . $title, 'X', '<svg viewBox="0 0 24 24" width="12" height="12" aria-hidden="true"><path fill="currentColor" d="M17.8 3h3.1l-6.8 7.7L22 21h-6.2l-4.9-6.4L5.3 21H2.2l7.3-8.3L2 3h6.4l4.4 5.8L17.8 3zm-1.1 16.2h1.7L7.4 4.7H5.6l11.1 14.5z"/></svg>' ),
		'linkedin' => array( 'https://www.linkedin.com/sharing/share-offsite/?url=' . $url, 'LinkedIn', '<svg viewBox="0 0 24 24" width="12" height="12" aria-hidden="true"><path fill="currentColor" d="M20.4 20.45h-3.55v-5.57c0-1.33-.03-3.04-1.85-3.04-1.85 0-2.14 1.45-2.14 2.94v5.67H9.35V9h3.41v1.56h.05c.48-.9 1.64-1.85 3.37-1.85 3.6 0 4.27 2.37 4.27 5.46v6.28zM5.34 7.43a2.06 2.06 0 110-4.13 2.06 2.06 0 010 4.13zM7.12 20.45H3.56V9h3.56v11.45z"/></svg>' ),
	);
	$out = '<p class="afvf-event-card__share-label">' . esc_html__( 'Partager', 'afvf' ) . '</p><p class="afvf-event-card__share">';
	foreach ( $links as $l ) {
		$out .= '<a href="' . esc_url( $l[0] ) . '" target="_blank" rel="noopener noreferrer" aria-label="' . esc_attr( $l[1] ) . '">' . $l[2] . '</a>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG.
	}
	return $out . '</p>';
}

/**
 * @param WP_Post $post Événement.
 * @return string
 */
function afvf_event_card_html( $post ) {
	$id   = $post->ID;
	$d    = afvf_event_data( $id );
	$link = get_permalink( $id );
	$more = $d['more_url'] ? $d['more_url'] : $link;
	$thumb = afvf_news_thumb_html( $post, 'large' ); // Image mise en avant, sinon 1re image du texte.
	$img   = '' !== $thumb;

	$wd     = afvf_event_dot( afvf_event_date_i18n( $d['date'], 'D' ) );
	$mo     = afvf_event_dot( afvf_event_date_i18n( $d['date'], 'M' ) );
	$day    = afvf_event_date_i18n( $d['date'], 'j' );
	$short  = trim( $wd . ' ' . $day . ' ' . $mo );

	// Pastille « N jours avant l’événement » (négatif = déjà commencé : pas de pastille).
	$days  = -1;
	$badge = '';
	if ( $d['date'] ) {
		$tz    = wp_timezone();
		$today = new DateTimeImmutable( 'today', $tz );
		$start = DateTimeImmutable::createFromFormat( '!Y-m-d', $d['date'], $tz );
		if ( $start ) {
			$days = (int) $today->diff( $start )->format( '%R%a' );
			if ( 0 === $days ) {
				$badge = __( 'Aujourd’hui', 'afvf' );
			} elseif ( 1 === $days ) {
				$badge = __( 'Demain', 'afvf' );
			} elseif ( $days > 1 ) {
				/* translators: %d: nombre de jours */
				$badge = sprintf( __( '%d jours avant l’événement', 'afvf' ), $days );
			}
		}
	}
	$button = '';
	// Comme sur Wix : le bouton mène toujours à la page de l’événement (section « Inscription »).
	if ( afvf_event_tickets( $id ) || $d['ticket_url'] ) {
		$button = '<div class="afvf-event-card__foot"><a class="afvf-event-card__btn" href="' . esc_url( $link . '#inscription' ) . '">' . esc_html( $d['ticket_label'] ) . '</a></div>';
	}

	if ( $img ) {
		$meta = esc_html( $short );
		$where = $d['place'] ? $d['place'] : $d['subtitle'];
		if ( $where ) {
			$meta .= ( $short ? '&nbsp;&nbsp;|&nbsp;&nbsp;' : '' ) . esc_html( $where );
		}
		// Comme Wix : pastille « N jours avant l’événement » seulement avant le début.
		$pill = ( $d['date'] && $days >= 0 )
			? '<p class="afvf-event-card__badge">' . afvf_event_icon_clock() . ' ' . esc_html( $badge ) . '</p>' // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG statique.
			: '';
		return '<article class="afvf-event-card afvf-event-card--poster">'
			. '<a class="afvf-event-card__img" href="' . esc_url( $link ) . '">' . $thumb . '</a>' // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- vignette WP.
			. '<div class="afvf-event-card__body">'
			. $pill
			. '<h3 class="afvf-event-card__title">' . esc_html( get_the_title( $id ) ) . '</h3>'
			. '<p class="afvf-event-card__meta">' . $meta . '</p>'
			. '<p class="afvf-event-card__more"><a href="' . esc_url( $more ) . '">' . esc_html__( 'Plus d’infos', 'afvf' ) . '</a></p>'
			. '</div>'
			. $button
			. '</article>';
	}

	// Carte « détail » (sans affiche).
	$when = afvf_event_date_i18n( $d['date'], 'j' ) . ' ' . afvf_event_dot( afvf_event_date_i18n( $d['date'], 'M' ) ) . ' ' . afvf_event_date_i18n( $d['date'], 'Y' );
	if ( $d['end_date'] && $d['end_date'] !== $d['date'] ) {
		$when .= ' – ' . afvf_event_date_i18n( $d['end_date'], 'j' ) . ' ' . afvf_event_dot( afvf_event_date_i18n( $d['end_date'], 'M' ) ) . ' ' . afvf_event_date_i18n( $d['end_date'], 'Y' );
	}
	if ( $d['start'] ) {
		$when .= ', ' . $d['start'] . ( $d['end'] ? ' – ' . $d['end'] : '' );
	}

	$desc = $post->post_excerpt ? $post->post_excerpt : wp_strip_all_tags( strip_shortcodes( $post->post_content ) );
	$desc = wp_trim_words( $desc, 60, '…' );

	$out  = '<article class="afvf-event-card afvf-event-card--detail">';
	$out .= '<div class="afvf-event-card__body">';
	if ( $d['date'] && '' !== $badge ) {
		$out .= '<p class="afvf-event-card__badge">' . afvf_event_icon_clock() . ' ' . esc_html( $badge ) . '</p>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG.
	}
	$out .= '<h3 class="afvf-event-card__title"><a href="' . esc_url( $link ) . '">' . esc_html( get_the_title( $id ) ) . '</a></h3>';
	if ( $d['date'] ) {
		$out .= '<p class="afvf-event-card__when">' . esc_html( $when ) . '</p>';
	}
	if ( $d['place'] ) {
		$out .= '<p class="afvf-event-card__place">' . esc_html( $d['place'] ) . '</p>';
	}
	if ( $desc ) {
		$out .= '<p class="afvf-event-card__desc">' . esc_html( $desc ) . '</p>';
	}
	$out .= afvf_event_share_html( $id );
	$out .= '</div>' . $button . '</article>';
	return $out;
}

/**
 * [afvf_events count="4"]
 *
 * @param array<string, string> $atts Attributs.
 * @return string
 */
function afvf_events_shortcode( $atts ) {
	$atts  = shortcode_atts( array( 'count' => 4 ), $atts, 'afvf_events' );
	$posts = afvf_events_upcoming( (int) $atts['count'] );
	if ( ! $posts ) {
		return '<p class="afvf-events-empty">' . esc_html__( 'Aucun événement à venir pour le moment.', 'afvf' ) . '</p>';
	}
	$html = '<div class="afvf-events-list">';
	foreach ( $posts as $post ) {
		$html .= afvf_event_card_html( $post ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- échappé dans la fonction.
	}
	return $html . '</div>';
}
add_shortcode( 'afvf_events', 'afvf_events_shortcode' );

/* -------------------------------------------------------------------------
 * Page de l’événement (style Wix) : billets + inscription
 * ---------------------------------------------------------------------- */

/**
 * Billets : une ligne « Nom | Prix | Description ».
 *
 * @param int $post_id Événement.
 * @return array<int, array{name:string, price:float, desc:string}>
 */
function afvf_event_tickets( $post_id ) {
	$raw   = (string) get_post_meta( $post_id, '_afvf_event_tickets', true );
	$out   = array();
	foreach ( preg_split( '/\r\n|\r|\n/', $raw ) as $line ) {
		$line = trim( $line );
		if ( '' === $line ) {
			continue;
		}
		$parts = array_map( 'trim', explode( '|', $line ) );
		if ( '' === $parts[0] ) {
			continue;
		}
		$price = isset( $parts[1] ) ? (float) str_replace( ',', '.', preg_replace( '/[^0-9,.]/', '', $parts[1] ) ) : 0.0;
		$out[] = array(
			'name'  => $parts[0],
			'price' => max( 0.0, $price ),
			'desc'  => isset( $parts[2] ) ? $parts[2] : '',
		);
	}
	return $out;
}

/**
 * @param float $price Prix.
 * @return string
 */
function afvf_event_price_label( $price ) {
	return $price > 0 ? number_format_i18n( $price, 2 ) . ' €' : __( 'Gratuit', 'afvf' );
}

/**
 * « 20 sept. 2026, 19:00 – 30 nov. 2026, 23:00 »
 *
 * @param array<string, string> $d Données.
 * @return string
 */
function afvf_event_when_text( $d ) {
	if ( ! $d['date'] ) {
		return '';
	}
	$fmt = static function ( $ymd ) {
		return afvf_event_date_i18n( $ymd, 'j' ) . ' ' . afvf_event_dot( afvf_event_date_i18n( $ymd, 'M' ) ) . ' ' . afvf_event_date_i18n( $ymd, 'Y' );
	};
	$start = $fmt( $d['date'] ) . ( $d['start'] ? ', ' . $d['start'] : '' );
	if ( $d['end_date'] && $d['end_date'] !== $d['date'] ) {
		return $start . ' – ' . $fmt( $d['end_date'] ) . ( $d['end'] ? ', ' . $d['end'] : '' );
	}
	return $start . ( $d['end'] ? ' – ' . $d['end'] : '' );
}

/**
 * « dim. 20 sept. | Lieu ».
 *
 * @param array<string, string> $d Données.
 * @return string
 */
function afvf_event_meta_line( $d ) {
	$short = trim( afvf_event_dot( afvf_event_date_i18n( $d['date'], 'D' ) ) . ' ' . afvf_event_date_i18n( $d['date'], 'j' ) . ' ' . afvf_event_dot( afvf_event_date_i18n( $d['date'], 'M' ) ) );
	$where = $d['place'] ? $d['place'] : $d['subtitle'];
	return $short && $where ? $short . '  |  ' . $where : $short . $where;
}

/**
 * Formulaire d’inscription (billets + quantités + total).
 *
 * @param int $post_id Événement.
 * @return string
 */
function afvf_event_registration_html( $post_id ) {
	$tickets = afvf_event_tickets( $post_id );
	$d       = afvf_event_data( $post_id );
	if ( ! $tickets && ! $d['ticket_url'] ) {
		return '';
	}

	$notice = '';
	if ( isset( $_GET['reservation'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$code = sanitize_key( wp_unslash( $_GET['reservation'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$msgs = array(
			'ok'      => __( 'Merci ! Votre réservation a bien été envoyée. Nous vous confirmons votre inscription rapidement.', 'afvf' ),
			'empty'   => __( 'Choisissez au moins une place.', 'afvf' ),
			'invalid' => __( 'Merci de renseigner votre prénom, votre nom et une adresse e-mail valide.', 'afvf' ),
			'expired' => __( 'Votre commande a expiré. Merci de choisir à nouveau vos places.', 'afvf' ),
			'error'   => __( 'Une erreur est survenue. Merci de réessayer.', 'afvf' ),
		);
		if ( isset( $msgs[ $code ] ) ) {
			$notice = '<p class="afvf-ev-notice afvf-ev-notice--' . ( 'ok' === $code ? 'ok' : 'error' ) . '" role="status">' . esc_html( $msgs[ $code ] ) . '</p>';
		}
	}

	$out  = '<section id="inscription" class="afvf-ev-block afvf-ev-register"><h2>' . esc_html__( 'Inscription', 'afvf' ) . '</h2>' . $notice; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- notice échappée.

	if ( ! $tickets ) {
		return $out . '<p><a class="afvf-ev-btn" href="' . esc_url( $d['ticket_url'] ) . '" target="_blank" rel="noopener noreferrer">' . esc_html( $d['ticket_label'] ) . '</a></p></section>';
	}

	$prices = wp_list_pluck( $tickets, 'price' );
	$range  = min( $prices ) === max( $prices )
		? afvf_event_price_label( min( $prices ) )
		/* translators: 1: prix min, 2: prix max. */
		: sprintf( __( 'De %1$s à %2$s', 'afvf' ), afvf_event_price_label( min( $prices ) ), afvf_event_price_label( max( $prices ) ) );

	$out .= '<form class="afvf-ev-form" method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
	$out .= '<input type="hidden" name="action" value="afvf_event_checkout"><input type="hidden" name="event" value="' . (int) $post_id . '">';
	$out .= wp_nonce_field( 'afvf_event_reserve', 'afvf_ev_nonce', true, false );
	$out .= '<div style="position:absolute;left:-9999px" aria-hidden="true"><label>Société <input type="text" name="afvf_company" tabindex="-1" autocomplete="off"></label></div>';
	$out .= '<div class="afvf-ev-tickets"><div class="afvf-ev-tickets__head"><span>' . esc_html__( 'Billets', 'afvf' ) . '</span><span>' . esc_html( $range ) . '</span></div>';
	foreach ( $tickets as $i => $t ) {
		$sel = '<select class="afvf-ev-qty" name="qty[' . (int) $i . ']" data-price="' . esc_attr( $t['price'] ) . '" aria-label="' . esc_attr( sprintf( /* translators: %s: billet. */ __( 'Quantité — %s', 'afvf' ), $t['name'] ) ) . '">';
		for ( $q = 0; $q <= 10; $q++ ) {
			$sel .= '<option value="' . $q . '">' . $q . '</option>';
		}
		$sel .= '</select>';
		$out .= '<div class="afvf-ev-ticket"><div class="afvf-ev-ticket__info"><strong>' . esc_html( $t['name'] ) . '</strong>'
			. ( $t['desc'] ? '<span>' . esc_html( $t['desc'] ) . '</span>' : '' )
			. '<span class="afvf-ev-ticket__price">' . esc_html( afvf_event_price_label( $t['price'] ) ) . '</span></div>'
			. '<label class="afvf-ev-ticket__qty"><span>' . esc_html__( 'Quantité', 'afvf' ) . '</span>' . $sel . '</label></div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- champs échappés.
	}
	$out .= '</div>';
	$out .= '<div class="afvf-ev-total"><span>' . esc_html__( 'Total', 'afvf' ) . '</span><strong><span class="afvf-ev-total__n">0,00</span> €</strong></div>';
	$out .= '<p class="afvf-ev-submit"><button type="submit" class="afvf-ev-btn afvf-ev-btn--pink">' . esc_html__( 'Passer la commande', 'afvf' ) . '</button></p></form></section>';
	return $out;
}

/**
 * Carte Google Maps (sans clé API) pour l’adresse de l’événement.
 *
 * @param string $address Adresse.
 * @return string
 */
function afvf_event_map_html( $address ) {
	if ( '' === trim( $address ) ) {
		return '';
	}
	$src = 'https://maps.google.com/maps?q=' . rawurlencode( $address ) . '&z=15&output=embed';
	return '<div class="afvf-ev-map"><iframe title="' . esc_attr__( 'Plan d’accès', 'afvf' ) . '" src="' . esc_url( $src ) . '" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe></div>';
}

/** Durée de la commande en cours (minutes), comme le « Temps restant » de Wix. */
const AFVF_EVENT_ORDER_MINUTES = 25;

/**
 * @param string $token    Jeton de commande.
 * @param int    $event_id Événement.
 * @return array|null
 */
function afvf_event_get_order( $token, $event_id ) {
	$token = preg_replace( '/[^A-Za-z0-9]/', '', (string) $token );
	if ( '' === $token ) {
		return null;
	}
	$order = get_transient( 'afvf_order_' . $token );
	if ( ! is_array( $order ) || (int) $order['event'] !== (int) $event_id || (int) $order['expires'] < time() ) {
		return null;
	}
	return $order;
}

/**
 * Étape 1 : « Passer la commande » → mémorise les places choisies puis ouvre la page de commande.
 */
function afvf_event_handle_checkout() {
	$event_id = isset( $_POST['event'] ) ? absint( $_POST['event'] ) : 0;
	$back     = $event_id && 'afvf_event' === get_post_type( $event_id ) ? get_permalink( $event_id ) : home_url( '/' );
	$fail     = static function ( $code ) use ( $back ) {
		wp_safe_redirect( add_query_arg( 'reservation', $code, $back ) . '#inscription' );
		exit;
	};

	if ( ! isset( $_POST['afvf_ev_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['afvf_ev_nonce'] ) ), 'afvf_event_reserve' ) || ! $event_id ) {
		$fail( 'error' );
	}

	$tickets = afvf_event_tickets( $event_id );
	$qty_in  = isset( $_POST['qty'] ) && is_array( $_POST['qty'] ) ? array_map( 'absint', wp_unslash( $_POST['qty'] ) ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	$lines   = array();
	$total   = 0.0;
	foreach ( $tickets as $i => $t ) {
		$q = isset( $qty_in[ $i ] ) ? min( 10, (int) $qty_in[ $i ] ) : 0;
		if ( $q > 0 ) {
			$lines[] = array(
				'name'  => $t['name'],
				'qty'   => $q,
				'price' => $t['price'],
			);
			$total  += $q * $t['price'];
		}
	}
	if ( ! $lines ) {
		$fail( 'empty' );
	}

	$token = wp_generate_password( 20, false );
	set_transient(
		'afvf_order_' . $token,
		array(
			'event'   => $event_id,
			'lines'   => $lines,
			'total'   => $total,
			'expires' => time() + AFVF_EVENT_ORDER_MINUTES * MINUTE_IN_SECONDS,
		),
		( AFVF_EVENT_ORDER_MINUTES + 5 ) * MINUTE_IN_SECONDS
	);
	wp_safe_redirect( add_query_arg( 'commande', $token, $back ) );
	exit;
}
add_action( 'admin_post_nopriv_afvf_event_checkout', 'afvf_event_handle_checkout' );
add_action( 'admin_post_afvf_event_checkout', 'afvf_event_handle_checkout' );

/**
 * Page « commande » : 1. Ajoutez vos détails · 2. Paiement + récapitulatif.
 *
 * @param int    $post_id Événement.
 * @param array  $order   Commande.
 * @param string $token   Jeton.
 * @return string
 */
function afvf_event_checkout_html( $post_id, $order, $token ) {
	$d    = afvf_event_data( $post_id );
	$when = afvf_event_when_text( $d );

	$items = '';
	foreach ( $order['lines'] as $l ) {
		$items .= '<div class="afvf-co__item"><div><span>' . esc_html( $l['name'] ) . '</span><small>' . esc_html( sprintf( /* translators: %d: quantité. */ __( 'Qté : %d', 'afvf' ), $l['qty'] ) ) . '</small></div><strong>' . esc_html( afvf_event_price_label( $l['qty'] * $l['price'] ) ) . '</strong></div>';
	}

	$notice = '';
	if ( isset( $_GET['reservation'] ) && 'invalid' === sanitize_key( wp_unslash( $_GET['reservation'] ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$notice = '<p class="afvf-ev-notice afvf-ev-notice--error" role="status">' . esc_html__( 'Merci de renseigner votre prénom, votre nom et une adresse e-mail valide.', 'afvf' ) . '</p>';
	}

	$expires = (int) $order['expires'];
	$left    = max( 0, $expires - time() );

	$out  = '<div class="afvf-co" data-left="' . (int) $left . '" data-back="' . esc_url( get_permalink( $post_id ) . '?reservation=expired#inscription' ) . '">';
	$out .= '<div class="afvf-co__main"><h1 class="afvf-co__step">1.&nbsp; ' . esc_html__( 'Ajoutez vos détails', 'afvf' ) . '</h1>' . $notice;
	$out .= '<form class="afvf-co__form" method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
	$out .= '<input type="hidden" name="action" value="afvf_event_reserve"><input type="hidden" name="event" value="' . (int) $post_id . '"><input type="hidden" name="token" value="' . esc_attr( $token ) . '">';
	$out .= wp_nonce_field( 'afvf_event_reserve', 'afvf_ev_nonce', true, false );
	$out .= '<div style="position:absolute;left:-9999px" aria-hidden="true"><label>Société <input type="text" name="afvf_company" tabindex="-1" autocomplete="off"></label></div>';
	$out .= '<div class="afvf-co__row"><label>' . esc_html__( '*Prénom', 'afvf' ) . '<input type="text" name="first_name" required autocomplete="given-name"></label>'
		. '<label>' . esc_html__( '*Nom de famille', 'afvf' ) . '<input type="text" name="last_name" required autocomplete="family-name"></label></div>';
	$out .= '<label class="afvf-co__full">' . esc_html__( '*E-mail', 'afvf' ) . '<input type="email" name="email" required autocomplete="email"></label>';
	$out .= '<button type="submit" class="afvf-co__btn">' . esc_html__( 'Continuer', 'afvf' ) . '</button></form>';
	$out .= '<h2 class="afvf-co__step afvf-co__step--next">2.&nbsp; ' . esc_html__( 'Paiement', 'afvf' ) . '</h2></div>';

	$out .= '<aside class="afvf-co__side"><p class="afvf-co__timer">' . esc_html__( 'Temps restant :', 'afvf' ) . ' <span class="afvf-co__time">' . esc_html( sprintf( '%02d:%02d', floor( $left / 60 ), $left % 60 ) ) . '</span></p>';
	$out .= '<div class="afvf-co__sum"><div class="afvf-co__sum-head"><h2>' . esc_html( get_the_title( $post_id ) ) . '</h2>'
		. ( $when ? '<p>' . esc_html( $when ) . '</p>' : '' )
		. ( $d['place'] ? '<p>' . esc_html( $d['place'] ) . '</p>' : '' ) . '</div>';
	$out .= '<div class="afvf-co__items">' . $items . '</div>';
	$out .= '<div class="afvf-co__total"><span>' . esc_html__( 'Total', 'afvf' ) . '</span><strong>' . esc_html( afvf_event_price_label( $order['total'] ) ) . '</strong></div></div></aside></div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- tout est échappé ci-dessus.
	return $out;
}

/**
 * Étape 2 : « Continuer » → enregistre la réservation, prévient l’association, puis paiement ou confirmation.
 */
function afvf_event_handle_reserve() {
	$event_id = isset( $_POST['event'] ) ? absint( $_POST['event'] ) : 0;
	$back     = $event_id && 'afvf_event' === get_post_type( $event_id ) ? get_permalink( $event_id ) : home_url( '/' );
	$token    = isset( $_POST['token'] ) ? preg_replace( '/[^A-Za-z0-9]/', '', sanitize_text_field( wp_unslash( $_POST['token'] ) ) ) : '';
	$fail     = static function ( $code, $keep_token = false ) use ( $back, $token ) {
		$args = array( 'reservation' => $code );
		if ( $keep_token ) {
			$args['commande'] = $token;
		}
		wp_safe_redirect( add_query_arg( $args, $back ) . ( $keep_token ? '' : '#inscription' ) );
		exit;
	};

	if ( ! isset( $_POST['afvf_ev_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['afvf_ev_nonce'] ) ), 'afvf_event_reserve' ) || ! $event_id ) {
		$fail( 'error' );
	}
	if ( ! empty( $_POST['afvf_company'] ) ) {
		$fail( 'ok' );
	}

	$order = afvf_event_get_order( $token, $event_id );
	if ( ! $order ) {
		$fail( 'expired' );
	}

	$first = isset( $_POST['first_name'] ) ? sanitize_text_field( wp_unslash( $_POST['first_name'] ) ) : '';
	$last  = isset( $_POST['last_name'] ) ? sanitize_text_field( wp_unslash( $_POST['last_name'] ) ) : '';
	$email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	$name  = trim( $first . ' ' . $last );
	if ( ! $first || ! $last || ! $email || ! is_email( $email ) ) {
		$fail( 'invalid', true );
	}

	$parts = array();
	foreach ( $order['lines'] as $l ) {
		$parts[] = sprintf( '%d × %s (%s)', $l['qty'], $l['name'], afvf_event_price_label( $l['price'] ) );
	}
	$total   = (float) $order['total'];
	$title   = get_the_title( $event_id );
	$summary = implode( ', ', $parts ) . sprintf( ' — Total : %s', number_format_i18n( $total, 2 ) . ' €' );

	global $wpdb;
	$wpdb->insert(
		afvf_contact_table(),
		array(
			'name'         => $name,
			'organization' => wp_html_excerpt( 'Événement « ' . $title . ' » — ' . $summary, 190, '…' ),
			'email'        => $email,
			'submitted_at' => current_time( 'mysql', true ),
			'ip_address'   => isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '',
			'status'       => 'new',
		),
		array( '%s', '%s', '%s', '%s', '%s', '%s' )
	);

	$dest = function_exists( 'afvf_mod' ) ? afvf_mod( 'afvf_email', 'assofvf@gmail.com' ) : get_option( 'admin_email' );
	wp_mail(
		$dest,
		'[AFVF] Réservation — ' . $title,
		sprintf( "Nouvelle réservation\n\nÉvénement : %s\nNom : %s\nE-mail : %s\n\nPlaces : %s\n", $title, $name, $email, $summary ),
		array( 'Reply-To: ' . $name . ' <' . $email . '>' )
	);
	delete_transient( 'afvf_order_' . $token );

	$pay = (string) get_post_meta( $event_id, '_afvf_event_ticket_url', true );
	if ( $pay && $total > 0 ) {
		wp_redirect( esc_url_raw( $pay ) ); // phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect -- lien de paiement externe saisi par l’administrateur.
		exit;
	}
	$fail( 'ok' );
}
add_action( 'admin_post_nopriv_afvf_event_reserve', 'afvf_event_handle_reserve' );
add_action( 'admin_post_afvf_event_reserve', 'afvf_event_handle_reserve' );

/* -------------------------------------------------------------------------
 * Motifs de blocs
 * ---------------------------------------------------------------------- */

function afvf_register_events_news_patterns() {
	register_block_pattern(
		'afvf/events-upcoming',
		array(
			'title'       => __( 'Événements à venir (automatique)', 'afvf' ),
			'description' => _x( 'Titre « EVENEMENTS A VENIR » + cartes générées depuis le menu « Événements » de l’administration (les événements passés disparaissent seuls). À placer sur la page d’accueil.', 'Block pattern description', 'afvf' ),
			'categories'  => array( 'afvf', 'columns' ),
			'content'     => '<!-- wp:group {"align":"full","className":"afvf-events-section","layout":{"type":"default"}} -->
<div class="wp-block-group alignfull afvf-events-section"><!-- wp:heading {"textAlign":"center","level":2,"className":"afvf-section-title"} -->
<h2 class="wp-block-heading has-text-align-center afvf-section-title">' . esc_html__( 'EVENEMENTS A VENIR', 'afvf' ) . '</h2>
<!-- /wp:heading -->

<!-- wp:shortcode -->
[afvf_events count="4"]
<!-- /wp:shortcode --></div>
<!-- /wp:group -->',
		)
	);

	register_block_pattern(
		'afvf/news-latest',
		array(
			'title'       => __( 'Actualités — 3 dernières (automatique)', 'afvf' ),
			'description' => _x( 'Titre « ACTUALITÉS » + les 3 dernières actualités publiées dans le menu « Actualités » de l’administration. À placer sur la page d’accueil.', 'Block pattern description', 'afvf' ),
			'categories'  => array( 'afvf', 'columns' ),
			'content'     => '<!-- wp:group {"align":"full","className":"afvf-events-section afvf-news-section","layout":{"type":"default"}} -->
<div class="wp-block-group alignfull afvf-events-section afvf-news-section"><!-- wp:heading {"textAlign":"center","level":2,"className":"afvf-section-title"} -->
<h2 class="wp-block-heading has-text-align-center afvf-section-title">' . esc_html__( 'ACTUALITÉS', 'afvf' ) . '</h2>
<!-- /wp:heading -->

<!-- wp:shortcode -->
[afvf_actualites count="3"]
<!-- /wp:shortcode --></div>
<!-- /wp:group -->',
		)
	);
}
add_action( 'init', 'afvf_register_events_news_patterns', 30 );
