<?php
/**
 * Services en ligne + réservation (équivalent « Wix Bookings »).
 *
 * - Menu admin « Services en ligne » : créer les services (durée, prix, jours et horaires).
 * - Shortcode [afvf_services] : catalogue de cartes avec bouton « Réserver ».
 * - Page d’un service : « Programmer votre service » (calendrier, créneaux, formulaire).
 * - Réservations : sous-menu « Réservations », e-mail à l’association et au client.
 *
 * @package AFVF
 */

defined( 'ABSPATH' ) || exit;

define( 'AFVF_BOOKING_DB_VERSION', 1 );

/* -------------------------------------------------------------------------
 * Type de contenu « Service »
 * ---------------------------------------------------------------------- */

function afvf_register_service_post_type() {
	register_post_type(
		'afvf_service',
		array(
			'labels'              => array(
				'name'               => __( 'Services en ligne', 'afvf' ),
				'singular_name'      => __( 'Service', 'afvf' ),
				'menu_name'          => __( 'Services en ligne', 'afvf' ),
				'all_items'          => __( 'Tous les services', 'afvf' ),
				'add_new'            => __( 'Ajouter', 'afvf' ),
				'add_new_item'       => __( 'Ajouter un service', 'afvf' ),
				'edit_item'          => __( 'Modifier le service', 'afvf' ),
				'new_item'           => __( 'Nouveau service', 'afvf' ),
				'view_item'          => __( 'Voir le service', 'afvf' ),
				'not_found'          => __( 'Aucun service trouvé.', 'afvf' ),
				'featured_image'     => __( 'Photo du service', 'afvf' ),
				'set_featured_image' => __( 'Choisir la photo', 'afvf' ),
			),
			'public'              => true,
			'show_in_rest'        => true,
			'menu_icon'           => 'dashicons-calendar',
			'menu_position'       => 6,
			'supports'            => array( 'title', 'editor', 'thumbnail', 'page-attributes' ),
			'has_archive'         => false,
			'exclude_from_search' => true,
			'rewrite'             => array(
				'slug'       => 'book-online',
				'with_front' => false,
			),
		)
	);

	if ( '1' !== get_option( 'afvf_services_rewrite_done' ) ) {
		flush_rewrite_rules( false );
		update_option( 'afvf_services_rewrite_done', '1' );
	}
}
add_action( 'init', 'afvf_register_service_post_type', 5 );

/* -------------------------------------------------------------------------
 * Table des réservations
 * ---------------------------------------------------------------------- */

function afvf_booking_table() {
	global $wpdb;
	return $wpdb->prefix . 'afvf_booking';
}

function afvf_booking_install_table() {
	if ( (int) get_option( 'afvf_booking_db_version', 0 ) >= AFVF_BOOKING_DB_VERSION ) {
		return;
	}
	global $wpdb;
	$table   = afvf_booking_table();
	$charset = $wpdb->get_charset_collate();
	$sql     = "CREATE TABLE {$table} (
		id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		service_id bigint(20) unsigned NOT NULL,
		slot_start datetime NOT NULL,
		name varchar(190) NOT NULL DEFAULT '',
		email varchar(190) NOT NULL DEFAULT '',
		phone varchar(60) DEFAULT NULL,
		message text,
		created_at datetime NOT NULL,
		PRIMARY KEY  (id),
		UNIQUE KEY slot (service_id, slot_start),
		KEY slot_start (slot_start)
	) {$charset};";
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	dbDelta( $sql );
	update_option( 'afvf_booking_db_version', AFVF_BOOKING_DB_VERSION );
}
add_action( 'after_setup_theme', 'afvf_booking_install_table' );

/* -------------------------------------------------------------------------
 * Données d’un service
 * ---------------------------------------------------------------------- */

/**
 * @param int $post_id Service.
 * @return array
 */
function afvf_service_data( $post_id ) {
	$days = get_post_meta( $post_id, '_afvf_svc_days', true );
	if ( ! is_array( $days ) ) {
		$days = array( 1, 2, 3, 4, 5 );
	}
	$start = (string) get_post_meta( $post_id, '_afvf_svc_start', true );
	$end   = (string) get_post_meta( $post_id, '_afvf_svc_end', true );
	$dur   = (int) get_post_meta( $post_id, '_afvf_svc_duration', true );
	$hor   = (int) get_post_meta( $post_id, '_afvf_svc_horizon', true );
	$not   = get_post_meta( $post_id, '_afvf_svc_notice', true );

	return array(
		'duration' => $dur > 0 ? $dur : 60,
		'price'    => (string) get_post_meta( $post_id, '_afvf_svc_price', true ),
		'days'     => array_values( array_unique( array_map( 'intval', $days ) ) ),
		'start'    => preg_match( '/^\d{2}:\d{2}$/', $start ) ? $start : '09:00',
		'end'      => preg_match( '/^\d{2}:\d{2}$/', $end ) ? $end : '17:00',
		'horizon'  => $hor > 0 ? min( 365, $hor ) : 60,
		'notice'   => '' === $not ? 24 : max( 0, (int) $not ),
	);
}

function afvf_service_duration_label( $minutes ) {
	$minutes = (int) $minutes;
	if ( $minutes < 60 ) {
		return $minutes . ' min';
	}
	$h = intdiv( $minutes, 60 );
	$m = $minutes % 60;
	return $m ? sprintf( '%d h %02d', $h, $m ) : $h . ' h';
}

function afvf_service_price_label( $price ) {
	$price = trim( (string) $price );
	if ( '' === $price ) {
		return '';
	}
	$num = str_replace( array( ' ', '€', ',' ), array( '', '', '.' ), $price );
	if ( is_numeric( $num ) ) {
		return (float) $num <= 0 ? __( 'Gratuit', 'afvf' ) : rtrim( rtrim( number_format( (float) $num, 2, ',', ' ' ), '0' ), ',' ) . ' €';
	}
	return $price;
}

function afvf_service_day_names() {
	return array(
		1 => array( 'Lun', 'lundi' ),
		2 => array( 'Mar', 'mardi' ),
		3 => array( 'Mer', 'mercredi' ),
		4 => array( 'Jeu', 'jeudi' ),
		5 => array( 'Ven', 'vendredi' ),
		6 => array( 'Sam', 'samedi' ),
		7 => array( 'Dim', 'dimanche' ),
	);
}

/**
 * Heures de début possibles pour une journée.
 *
 * @param array $d afvf_service_data().
 * @return string[] « HH:MM ».
 */
function afvf_service_day_times( $d ) {
	$to_min = static function ( $t ) {
		$p = explode( ':', $t );
		return (int) $p[0] * 60 + (int) $p[1];
	};
	$start = $to_min( $d['start'] );
	$end   = $to_min( $d['end'] );
	$dur   = max( 5, (int) $d['duration'] );
	$step  = max( 30, $dur );
	$out   = array();
	if ( $start + $dur > $end ) {
		// Service plus long que la plage horaire : un seul créneau, à l’heure de début.
		return array( $d['start'] );
	}
	for ( $t = $start; $t + $dur <= $end; $t += $step ) {
		$out[] = sprintf( '%02d:%02d', intdiv( $t, 60 ), $t % 60 );
	}
	return $out;
}

/**
 * Créneaux libres : « Y-m-d » => [ « HH:MM », … ].
 *
 * @param int $post_id Service.
 * @return array
 */
function afvf_service_availability( $post_id ) {
	$d    = afvf_service_data( $post_id );
	$tz   = wp_timezone();
	$now  = new DateTimeImmutable( 'now', $tz );
	$min  = $now->modify( '+' . (int) $d['notice'] . ' hours' );
	$day  = $now->setTime( 0, 0 );
	$last = $day->modify( '+' . (int) $d['horizon'] . ' days' );

	global $wpdb;
	$rows  = $wpdb->get_col( // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$wpdb->prepare(
			'SELECT slot_start FROM ' . afvf_booking_table() . ' WHERE service_id = %d AND slot_start >= %s AND slot_start < %s',
			$post_id,
			$day->format( 'Y-m-d H:i:s' ),
			$last->format( 'Y-m-d H:i:s' )
		)
	);
	$taken = array_flip( $rows );
	$times = afvf_service_day_times( $d );
	$out   = array();

	for ( $dt = $day; $dt < $last; $dt = $dt->modify( '+1 day' ) ) {
		if ( ! in_array( (int) $dt->format( 'N' ), $d['days'], true ) ) {
			continue;
		}
		foreach ( $times as $t ) {
			$key = $dt->format( 'Y-m-d' ) . ' ' . $t . ':00';
			if ( isset( $taken[ $key ] ) ) {
				continue;
			}
			if ( new DateTimeImmutable( $key, $tz ) < $min ) {
				continue;
			}
			$out[ $dt->format( 'Y-m-d' ) ][] = $t;
		}
	}
	return $out;
}

/* -------------------------------------------------------------------------
 * Administration : boîte de réglages
 * ---------------------------------------------------------------------- */

function afvf_service_add_meta_box() {
	add_meta_box( 'afvf_service_details', __( 'Réglages de la réservation', 'afvf' ), 'afvf_service_render_meta_box', 'afvf_service', 'normal', 'high', array( '__block_editor_compatible_meta_box' => true ) );
}
add_action( 'add_meta_boxes', 'afvf_service_add_meta_box' );

function afvf_service_render_meta_box( $post ) {
	$d = afvf_service_data( $post->ID );
	wp_nonce_field( 'afvf_service_save', 'afvf_service_nonce' );
	?>
	<style>
		.afvf-svc-box { display:grid; grid-template-columns: repeat(2, minmax(0,1fr)); gap: 12px 18px; }
		.afvf-svc-box label { display:block; font-weight:600; margin-bottom:3px; }
		.afvf-svc-box input[type=text], .afvf-svc-box input[type=number], .afvf-svc-box input[type=time] { width:100%; }
		.afvf-svc-box .full { grid-column: 1 / -1; }
		.afvf-svc-box .days label { display:inline-block; font-weight:400; margin-right:14px; }
		.afvf-svc-box .hint { color:#666; font-size:12px; margin:2px 0 0; }
	</style>
	<div class="afvf-svc-box">
		<div>
			<label for="afvf_svc_duration"><?php esc_html_e( 'Durée (en minutes)', 'afvf' ); ?></label>
			<input type="number" min="5" step="5" id="afvf_svc_duration" name="afvf_svc_duration" value="<?php echo esc_attr( $d['duration'] ); ?>">
			<p class="hint"><?php esc_html_e( 'Ex. 15, 60, 840 (= 14 h).', 'afvf' ); ?></p>
		</div>
		<div>
			<label for="afvf_svc_price"><?php esc_html_e( 'Prix', 'afvf' ); ?></label>
			<input type="text" id="afvf_svc_price" name="afvf_svc_price" value="<?php echo esc_attr( $d['price'] ); ?>" placeholder="35">
			<p class="hint"><?php esc_html_e( 'Un nombre en euros (35), 0 ou vide = « Gratuit », ou du texte (« Sur devis »).', 'afvf' ); ?></p>
		</div>
		<div class="full days">
			<label><?php esc_html_e( 'Jours de réservation possibles', 'afvf' ); ?></label>
			<?php foreach ( afvf_service_day_names() as $n => $names ) : ?>
				<label><input type="checkbox" name="afvf_svc_days[]" value="<?php echo (int) $n; ?>" <?php checked( in_array( $n, $d['days'], true ) ); ?>> <?php echo esc_html( $names[1] ); ?></label>
			<?php endforeach; ?>
		</div>
		<div>
			<label for="afvf_svc_start"><?php esc_html_e( 'Première heure de début', 'afvf' ); ?></label>
			<input type="time" id="afvf_svc_start" name="afvf_svc_start" value="<?php echo esc_attr( $d['start'] ); ?>">
		</div>
		<div>
			<label for="afvf_svc_end"><?php esc_html_e( 'Heure de fin de journée', 'afvf' ); ?></label>
			<input type="time" id="afvf_svc_end" name="afvf_svc_end" value="<?php echo esc_attr( $d['end'] ); ?>">
			<p class="hint"><?php esc_html_e( 'Les créneaux sont générés entre ces deux heures, selon la durée.', 'afvf' ); ?></p>
		</div>
		<div>
			<label for="afvf_svc_horizon"><?php esc_html_e( 'Réservable jusqu’à (jours à l’avance)', 'afvf' ); ?></label>
			<input type="number" min="1" max="365" id="afvf_svc_horizon" name="afvf_svc_horizon" value="<?php echo esc_attr( $d['horizon'] ); ?>">
		</div>
		<div>
			<label for="afvf_svc_notice"><?php esc_html_e( 'Délai minimum avant le rendez-vous (heures)', 'afvf' ); ?></label>
			<input type="number" min="0" id="afvf_svc_notice" name="afvf_svc_notice" value="<?php echo esc_attr( $d['notice'] ); ?>">
		</div>
		<p class="full hint"><?php esc_html_e( 'Le texte de l’éditeur ci-dessus s’affiche dans « Détails du service ». La photo du service se choisit à droite (« Photo du service »).', 'afvf' ); ?></p>
	</div>
	<?php
}

function afvf_service_save( $post_id ) {
	if ( ! isset( $_POST['afvf_service_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['afvf_service_nonce'] ) ), 'afvf_service_save' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	$time = static function ( $key, $def ) {
		$v = isset( $_POST[ $key ] ) ? sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) : '';
		return preg_match( '/^\d{2}:\d{2}$/', $v ) ? $v : $def;
	};
	$days = isset( $_POST['afvf_svc_days'] ) ? array_filter( array_map( 'absint', (array) wp_unslash( $_POST['afvf_svc_days'] ) ), static fn( $n ) => $n >= 1 && $n <= 7 ) : array();

	update_post_meta( $post_id, '_afvf_svc_duration', max( 5, isset( $_POST['afvf_svc_duration'] ) ? absint( $_POST['afvf_svc_duration'] ) : 60 ) );
	update_post_meta( $post_id, '_afvf_svc_price', isset( $_POST['afvf_svc_price'] ) ? sanitize_text_field( wp_unslash( $_POST['afvf_svc_price'] ) ) : '' );
	update_post_meta( $post_id, '_afvf_svc_days', array_values( $days ) );
	update_post_meta( $post_id, '_afvf_svc_start', $time( 'afvf_svc_start', '09:00' ) );
	update_post_meta( $post_id, '_afvf_svc_end', $time( 'afvf_svc_end', '17:00' ) );
	update_post_meta( $post_id, '_afvf_svc_horizon', max( 1, isset( $_POST['afvf_svc_horizon'] ) ? min( 365, absint( $_POST['afvf_svc_horizon'] ) ) : 60 ) );
	update_post_meta( $post_id, '_afvf_svc_notice', isset( $_POST['afvf_svc_notice'] ) ? absint( $_POST['afvf_svc_notice'] ) : 24 );
}
add_action( 'save_post_afvf_service', 'afvf_service_save' );

/**
 * Trois services de départ (une seule fois, uniquement s’il n’y en a aucun).
 */
function afvf_service_seed() {
	if ( '1' === get_option( 'afvf_services_seeded' ) ) {
		return;
	}
	update_option( 'afvf_services_seeded', '1' );
	if ( get_posts( array( 'post_type' => 'afvf_service', 'post_status' => 'any', 'numberposts' => 1, 'fields' => 'ids' ) ) ) {
		return;
	}
	$items = array(
		array( 'Rendez-vous d’information en ligne', 'rendez-vous-information', 15, '0', array( 1, 2, 3, 4, 5 ), 'Un échange de 15 minutes en visioconférence pour découvrir l’AFVF et ses actions.' ),
		array( 'Sensibilisation aux VSS et féminicides', 'sensibilisation', 840, '290', array( 1, 2, 3, 4, 5 ), 'Formation de sensibilisation aux violences sexistes et sexuelles et aux féminicides.' ),
		array( 'COURS individuel : « Écouter est un Art »', 'cours-ecouter', 60, '35', array( 5 ), 'Un cours individuel d’une heure pour apprendre l’écoute active.' ),
	);
	foreach ( $items as $i => $it ) {
		$id = wp_insert_post(
			array(
				'post_type'    => 'afvf_service',
				'post_status'  => 'publish',
				'post_title'   => $it[0],
				'post_name'    => $it[1],
				'post_content' => '<!-- wp:paragraph --><p>' . esc_html( $it[5] ) . '</p><!-- /wp:paragraph -->',
				'menu_order'   => $i,
			)
		);
		if ( $id && ! is_wp_error( $id ) ) {
			update_post_meta( $id, '_afvf_svc_duration', $it[2] );
			update_post_meta( $id, '_afvf_svc_price', $it[3] );
			update_post_meta( $id, '_afvf_svc_days', $it[4] );
			update_post_meta( $id, '_afvf_svc_start', '09:00' );
			update_post_meta( $id, '_afvf_svc_end', '17:00' );
			update_post_meta( $id, '_afvf_svc_horizon', 60 );
			update_post_meta( $id, '_afvf_svc_notice', 24 );
		}
	}
}
add_action( 'init', 'afvf_service_seed', 20 );

/* -------------------------------------------------------------------------
 * Catalogue [afvf_services]
 * ---------------------------------------------------------------------- */

function afvf_service_card_html( $post ) {
	$id    = $post->ID;
	$d     = afvf_service_data( $id );
	$link  = get_permalink( $id );
	$thumb = get_post_thumbnail_id( $id );
	$meta  = array();
	if ( 1 === count( $d['days'] ) ) {
		$names  = afvf_service_day_names();
		$meta[] = $names[ $d['days'][0] ][0];
	}
	$meta[] = afvf_service_duration_label( $d['duration'] );
	$price  = afvf_service_price_label( $d['price'] );
	if ( $price ) {
		$meta[] = $price;
	}

	$img = $thumb
		? wp_get_attachment_image( $thumb, 'large', false, array( 'alt' => '' ) )
		: '<span class="afvf-svc-card__ph" aria-hidden="true"></span>';

	$out  = '<article class="afvf-svc-card">';
	$out .= '<a class="afvf-svc-card__img" href="' . esc_url( $link ) . '" tabindex="-1" aria-hidden="true">' . $img . '</a>';
	$out .= '<div class="afvf-svc-card__body"><h3 class="afvf-svc-card__title"><a href="' . esc_url( $link ) . '">' . esc_html( get_the_title( $id ) ) . '</a></h3>';
	$out .= '<ul class="afvf-svc-card__meta">';
	foreach ( $meta as $m ) {
		$out .= '<li>' . esc_html( $m ) . '</li>';
	}
	$out .= '</ul><p class="afvf-svc-card__cta"><a class="afvf-svc-btn" href="' . esc_url( $link ) . '">' . esc_html__( 'Réserver', 'afvf' ) . '</a></p></div></article>';
	return $out;
}

function afvf_services_shortcode( $atts ) {
	$atts  = shortcode_atts( array( 'count' => 12 ), $atts, 'afvf_services' );
	$posts = get_posts(
		array(
			'post_type'   => 'afvf_service',
			'post_status' => 'publish',
			'numberposts' => max( 1, (int) $atts['count'] ),
			'orderby'     => array( 'menu_order' => 'ASC', 'date' => 'ASC' ),
		)
	);
	// Un service sans titre (brouillon oublié) n’est pas affiché.
	$posts = array_values( array_filter( $posts, static fn( $p ) => '' !== trim( $p->post_title ) ) );
	if ( ! $posts ) {
		return '<p class="afvf-svc-empty">' . esc_html__( 'Aucun service pour le moment.', 'afvf' ) . '</p>';
	}
	$out = '<div class="afvf-svc-grid">';
	foreach ( $posts as $p ) {
		$out .= afvf_service_card_html( $p );
	}
	return $out . '</div>';
}
add_shortcode( 'afvf_services', 'afvf_services_shortcode' );

/* -------------------------------------------------------------------------
 * Page d’un service : « Programmer votre service »
 * ---------------------------------------------------------------------- */

function afvf_service_enqueue() {
	if ( ! is_singular( 'afvf_service' ) ) {
		return;
	}
	wp_enqueue_script( 'afvf-booking', get_template_directory_uri() . '/assets/js/booking.js', array(), wp_get_theme()->get( 'Version' ), true );
}
add_action( 'wp_enqueue_scripts', 'afvf_service_enqueue' );

function afvf_service_catalog_url() {
	$page = get_page_by_path( 'book-online' );
	return $page ? get_permalink( $page ) : home_url( '/' );
}

/**
 * @param int $post_id Service.
 * @return string
 */
function afvf_service_booking_html( $post_id ) {
	$d     = afvf_service_data( $post_id );
	$title = get_the_title( $post_id );
	$back  = afvf_service_catalog_url();

	$state = isset( $_GET['reservation'] ) ? sanitize_key( wp_unslash( $_GET['reservation'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	if ( 'ok' === $state ) {
		$date = isset( $_GET['d'] ) ? sanitize_text_field( wp_unslash( $_GET['d'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$time = isset( $_GET['t'] ) ? sanitize_text_field( wp_unslash( $_GET['t'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$when = '';
		if ( preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date ) && preg_match( '/^\d{2}:\d{2}$/', $time ) ) {
			$when = wp_date( 'l j F Y', ( new DateTimeImmutable( $date . ' ' . $time, wp_timezone() ) )->getTimestamp() ) . ' · ' . $time;
		}
		ob_start();
		?>
		<div class="afvf-bk afvf-bk--done">
			<h1 class="afvf-bk__title"><?php esc_html_e( 'Réservation confirmée', 'afvf' ); ?></h1>
			<p class="afvf-bk__intro"><?php esc_html_e( 'Merci ! Un e-mail de confirmation vient de vous être envoyé.', 'afvf' ); ?></p>
			<div class="afvf-bk__recap">
				<strong><?php echo esc_html( $title ); ?></strong>
				<?php if ( $when ) : ?>
					<span><?php echo esc_html( ucfirst( $when ) ); ?></span>
				<?php endif; ?>
				<span><?php echo esc_html( afvf_service_duration_label( $d['duration'] ) ); ?></span>
			</div>
			<p><a class="afvf-svc-btn" href="<?php echo esc_url( $back ); ?>"><?php esc_html_e( 'Retour aux services', 'afvf' ); ?></a></p>
		</div>
		<?php
		return ob_get_clean();
	}

	$errors = array(
		'taken'   => __( 'Ce créneau vient d’être réservé par quelqu’un d’autre. Merci d’en choisir un autre.', 'afvf' ),
		'invalid' => __( 'Merci de renseigner votre nom et une adresse e-mail valide.', 'afvf' ),
		'error'   => __( 'Une erreur est survenue. Merci de réessayer.', 'afvf' ),
	);
	$avail  = afvf_service_availability( $post_id );
	$now    = new DateTimeImmutable( 'now', wp_timezone() );
	$conf   = array(
		'avail'   => (object) $avail,
		'today'   => $now->format( 'Y-m-d' ),
		'horizon' => $now->modify( '+' . (int) $d['horizon'] . ' days' )->format( 'Y-m-d' ),
		'duration' => afvf_service_duration_label( $d['duration'] ),
		'tz'      => wp_timezone_string(),
		'utc'     => 'UTC' . wp_date( 'P' ),
		'i18n'    => array(
			'none'   => __( 'Aucune disponibilité', 'afvf' ),
			'avail'  => __( 'Disponibilité pour le', 'afvf' ),
			'at'     => __( 'à', 'afvf' ),
		),
	);
	$price   = afvf_service_price_label( $d['price'] );
	$content = apply_filters( 'the_content', get_post_field( 'post_content', $post_id ) );

	ob_start();
	?>
	<div class="afvf-bk" data-afvf-booking="<?php echo esc_attr( wp_json_encode( $conf ) ); ?>">
		<a class="afvf-bk__back" href="<?php echo esc_url( $back ); ?>" data-afvf-back><span aria-hidden="true">‹</span> <?php esc_html_e( 'Retour', 'afvf' ); ?></a>

		<h1 class="afvf-bk__title" data-title data-t1="<?php echo esc_attr__( 'Programmer votre service', 'afvf' ); ?>" data-t2="<?php echo esc_attr__( 'Formulaire de réservation', 'afvf' ); ?>"><?php esc_html_e( 'Programmer votre service', 'afvf' ); ?></h1>
		<p class="afvf-bk__intro" data-intro><?php esc_html_e( 'Consultez nos disponibilités et réservez la date et l’heure qui vous conviennent.', 'afvf' ); ?></p>

		<?php if ( isset( $errors[ $state ] ) ) : ?>
			<p class="afvf-bk__notice" role="alert"><?php echo esc_html( $errors[ $state ] ); ?></p>
		<?php endif; ?>

		<div class="afvf-bk__step afvf-bk__step--pick" data-step="1">
			<div class="afvf-bk__head">
				<h2><?php esc_html_e( 'Sélectionnez une date et une heure', 'afvf' ); ?></h2>
				<span class="afvf-bk__tz" data-tz><?php echo esc_html( sprintf( /* translators: %s: timezone */ __( 'Fuseau horaire : %s', 'afvf' ), str_replace( '_', ' ', wp_timezone_string() ) ) ); ?></span>
			</div>
			<div class="afvf-bk__pick">
				<div class="afvf-cal" data-cal></div>
				<div class="afvf-bk__slots" data-slots aria-live="polite"></div>
			</div>
		</div>

		<div class="afvf-bk__step afvf-bk__step--form" data-step="2" hidden>
			<form class="afvf-bk__form afvf-bk__cols" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
				<input type="hidden" name="action" value="afvf_service_book">
				<input type="hidden" name="service" value="<?php echo (int) $post_id; ?>">
				<input type="hidden" name="date" value="" data-f-date>
				<input type="hidden" name="time" value="" data-f-time>
				<?php wp_nonce_field( 'afvf_service_book', 'afvf_book_nonce' ); ?>
				<div class="afvf-honeypot" aria-hidden="true">
					<label for="afvf-bk-company"><?php esc_html_e( 'Ne pas remplir', 'afvf' ); ?></label>
					<input id="afvf-bk-company" type="text" name="afvf_company" tabindex="-1" autocomplete="off">
				</div>

				<div class="afvf-bk__col afvf-bk__col--client">
					<h2 class="afvf-bk__h2"><?php esc_html_e( 'Détails du client', 'afvf' ); ?></h2>
					<div class="afvf-bk__row">
						<p><label for="afvf-bk-name"><?php esc_html_e( 'Nom', 'afvf' ); ?> *</label><input id="afvf-bk-name" name="name" type="text" required maxlength="190" autocomplete="name"></p>
						<p><label for="afvf-bk-email"><?php esc_html_e( 'E-mail', 'afvf' ); ?> *</label><input id="afvf-bk-email" name="email" type="email" required autocomplete="email"></p>
					</div>
					<p>
						<label for="afvf-bk-phone"><?php esc_html_e( 'Numéro de téléphone', 'afvf' ); ?></label>
						<span class="afvf-bk__phone">
							<select name="phone_cc" aria-label="<?php esc_attr_e( 'Indicatif', 'afvf' ); ?>">
								<option value="+33">🇫🇷 +33</option>
								<option value="+221">🇸🇳 +221</option>
								<option value="+32">🇧🇪 +32</option>
								<option value="+41">🇨🇭 +41</option>
								<option value="+352">🇱🇺 +352</option>
								<option value="+1">🇨🇦 +1</option>
								<option value="+212">🇲🇦 +212</option>
								<option value="+213">🇩🇿 +213</option>
								<option value="+216">🇹🇳 +216</option>
								<option value="+225">🇨🇮 +225</option>
								<option value="">🌍 <?php esc_html_e( 'Autre', 'afvf' ); ?></option>
							</select>
							<input id="afvf-bk-phone" name="phone" type="tel" maxlength="30" autocomplete="tel-national">
						</span>
					</p>
					<p><label for="afvf-bk-msg"><?php esc_html_e( 'Ajoutez votre message', 'afvf' ); ?></label><textarea id="afvf-bk-msg" name="message" rows="4" maxlength="2000"></textarea></p>
				</div>

				<aside class="afvf-bk__col afvf-bk__col--sum">
					<h2 class="afvf-bk__h2"><?php esc_html_e( 'Détails de la réservation', 'afvf' ); ?></h2>
					<p class="afvf-bk__sumtitle"><?php echo esc_html( $title ); ?></p>
					<p class="afvf-bk__sumdate" data-recap></p>
					<button type="button" class="afvf-bk__toggle afvf-bk__toggle--sum" data-sumtoggle aria-expanded="false"><span><?php esc_html_e( 'Afficher les détails', 'afvf' ); ?></span> <i aria-hidden="true">⌄</i></button>
					<p class="afvf-bk__sumdetail" hidden><?php echo esc_html( afvf_service_duration_label( $d['duration'] ) ); ?></p>
					<h3 class="afvf-bk__h3"><?php esc_html_e( 'Détails du paiement', 'afvf' ); ?></h3>
					<p class="afvf-bk__sumprice"><?php echo esc_html( $price ? $price : __( 'Gratuit', 'afvf' ) ); ?></p>
					<p class="afvf-bk__legal"><?php esc_html_e( 'En confirmant votre réservation, vous acceptez de recevoir des notifications par e-mail en rapport avec cette réservation.', 'afvf' ); ?></p>
					<button type="submit" class="afvf-bk__next afvf-bk__next--on"><?php esc_html_e( 'Réserver', 'afvf' ); ?></button>
				</aside>
			</form>
		</div>

		<div class="afvf-bk__details">
			<h2><?php esc_html_e( 'Détails du service', 'afvf' ); ?></h2>
			<p class="afvf-bk__dtitle"><?php echo esc_html( $title ); ?></p>
			<p class="afvf-bk__dmeta"><?php echo esc_html( afvf_service_duration_label( $d['duration'] ) . ( $price ? ' · ' . $price : '' ) ); ?></p>
			<?php if ( trim( wp_strip_all_tags( $content ) ) ) : ?>
				<div class="afvf-bk__more" id="afvf-bk-more" hidden><?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
				<button type="button" class="afvf-bk__toggle" data-more aria-expanded="false" aria-controls="afvf-bk-more"><span><?php esc_html_e( 'Afficher plus', 'afvf' ); ?></span> <i aria-hidden="true">⌄</i></button>
			<?php endif; ?>
		</div>

		<button type="button" class="afvf-bk__next" data-next disabled><?php esc_html_e( 'Suivant', 'afvf' ); ?></button>
	</div>
	<?php
	return ob_get_clean();
}

/* -------------------------------------------------------------------------
 * Enregistrement d’une réservation
 * ---------------------------------------------------------------------- */

function afvf_service_handle_book() {
	$service = isset( $_POST['service'] ) ? absint( $_POST['service'] ) : 0;
	$back    = $service && 'afvf_service' === get_post_type( $service ) ? get_permalink( $service ) : home_url( '/' );
	$fail    = static function ( $code ) use ( $back ) {
		wp_safe_redirect( add_query_arg( 'reservation', $code, $back ) );
		exit;
	};

	if ( ! $service || ! isset( $_POST['afvf_book_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['afvf_book_nonce'] ) ), 'afvf_service_book' ) ) {
		$fail( 'error' );
	}
	if ( ! empty( $_POST['afvf_company'] ) ) {
		$fail( 'error' );
	}

	$date  = isset( $_POST['date'] ) ? sanitize_text_field( wp_unslash( $_POST['date'] ) ) : '';
	$time  = isset( $_POST['time'] ) ? sanitize_text_field( wp_unslash( $_POST['time'] ) ) : '';
	$name  = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
	$email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	$phone = isset( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : '';
	$cc    = isset( $_POST['phone_cc'] ) ? preg_replace( '/[^0-9+]/', '', sanitize_text_field( wp_unslash( $_POST['phone_cc'] ) ) ) : '';
	$msg   = isset( $_POST['message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) : '';
	if ( '' !== $phone && $cc ) {
		$phone = $cc . ' ' . $phone;
	}
	$first = strtok( $name, ' ' ) ?: $name;

	if ( ! $name || ! $email || ! is_email( $email ) ) {
		$fail( 'invalid' );
	}

	$avail = afvf_service_availability( $service );
	if ( empty( $avail[ $date ] ) || ! in_array( $time, $avail[ $date ], true ) ) {
		$fail( 'taken' );
	}

	global $wpdb;
	$ok = $wpdb->insert(
		afvf_booking_table(),
		array(
			'service_id' => $service,
			'slot_start' => $date . ' ' . $time . ':00',
			'name'       => $name,
			'email'      => $email,
			'phone'      => $phone ?: null,
			'message'    => $msg ?: null,
			'created_at' => current_time( 'mysql', true ),
		),
		array( '%d', '%s', '%s', '%s', '%s', '%s', '%s' )
	);
	if ( ! $ok ) {
		$fail( 'taken' ); // Clé unique : créneau pris entre-temps.
	}

	$title = get_the_title( $service );
	$d     = afvf_service_data( $service );
	$when  = wp_date( 'l j F Y', ( new DateTimeImmutable( $date . ' ' . $time, wp_timezone() ) )->getTimestamp() ) . ' à ' . $time;

	// Visible aussi dans le menu « Messages ».
	$wpdb->insert(
		afvf_contact_table(),
		array(
			'name'         => $name,
			'organization' => wp_html_excerpt( 'RDV « ' . $title . ' » — ' . $when, 190, '…' ),
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
		sprintf(
			"Nouvelle réservation\n\nService : %s\nDate : %s\nDurée : %s\n\nNom : %s\nE-mail : %s\nTéléphone : %s\n\nMessage :\n%s\n\nToutes les réservations : %s\n",
			$title,
			$when,
			afvf_service_duration_label( $d['duration'] ),
			$name,
			$email,
			$phone ?: '—',
			$msg ?: '—',
			admin_url( 'edit.php?post_type=afvf_service&page=afvf-bookings' )
		),
		array( 'Reply-To: ' . $name . ' <' . $email . '>' )
	);
	wp_mail(
		$email,
		'Votre réservation — ' . $title,
		sprintf(
			"Bonjour %s,\n\nVotre réservation est bien enregistrée :\n\n%s\n%s (%s)\n\nPour toute question, répondez à ce message ou écrivez-nous : %s\n\nL’équipe AFVF\n",
			$first,
			$title,
			$when,
			afvf_service_duration_label( $d['duration'] ),
			$dest
		)
	);

	wp_safe_redirect(
		add_query_arg(
			array(
				'reservation' => 'ok',
				'd'           => $date,
				't'           => $time,
			),
			$back
		)
	);
	exit;
}
add_action( 'admin_post_nopriv_afvf_service_book', 'afvf_service_handle_book' );
add_action( 'admin_post_afvf_service_book', 'afvf_service_handle_book' );

/* -------------------------------------------------------------------------
 * Administration : liste des réservations
 * ---------------------------------------------------------------------- */

function afvf_booking_admin_menu() {
	add_submenu_page(
		'edit.php?post_type=afvf_service',
		__( 'Réservations', 'afvf' ),
		__( 'Réservations', 'afvf' ),
		'manage_options',
		'afvf-bookings',
		'afvf_booking_admin_page'
	);
}
add_action( 'admin_menu', 'afvf_booking_admin_menu' );

function afvf_booking_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	global $wpdb;
	$now  = current_time( 'mysql' );
	$rows = $wpdb->get_results( 'SELECT * FROM ' . afvf_booking_table() . ' ORDER BY slot_start DESC LIMIT 500' ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Réservations', 'afvf' ); ?></h1>
		<?php if ( isset( $_GET['deleted'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
			<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Réservation annulée : le créneau est de nouveau libre.', 'afvf' ); ?></p></div>
		<?php endif; ?>
		<table class="widefat striped">
			<thead><tr>
				<th><?php esc_html_e( 'Date du rendez-vous', 'afvf' ); ?></th>
				<th><?php esc_html_e( 'Service', 'afvf' ); ?></th>
				<th><?php esc_html_e( 'Nom', 'afvf' ); ?></th>
				<th><?php esc_html_e( 'E-mail', 'afvf' ); ?></th>
				<th><?php esc_html_e( 'Téléphone', 'afvf' ); ?></th>
				<th><?php esc_html_e( 'Message', 'afvf' ); ?></th>
				<th></th>
			</tr></thead>
			<tbody>
			<?php if ( ! $rows ) : ?>
				<tr><td colspan="7"><?php esc_html_e( 'Aucune réservation pour le moment.', 'afvf' ); ?></td></tr>
			<?php endif; ?>
			<?php foreach ( $rows as $r ) : ?>
				<?php
				$ts   = ( new DateTimeImmutable( $r->slot_start, wp_timezone() ) )->getTimestamp();
				$past = $r->slot_start < $now;
				$del  = wp_nonce_url( admin_url( 'admin-post.php?action=afvf_booking_delete&id=' . (int) $r->id ), 'afvf_booking_delete_' . (int) $r->id );
				?>
				<tr<?php echo $past ? ' style="opacity:.55"' : ''; ?>>
					<td><strong><?php echo esc_html( wp_date( 'D j M Y · H:i', $ts ) ); ?></strong></td>
					<td><?php echo esc_html( get_the_title( (int) $r->service_id ) ); ?></td>
					<td><?php echo esc_html( $r->name ); ?></td>
					<td><a href="mailto:<?php echo esc_attr( $r->email ); ?>"><?php echo esc_html( $r->email ); ?></a></td>
					<td><?php echo esc_html( (string) $r->phone ); ?></td>
					<td><?php echo esc_html( wp_html_excerpt( (string) $r->message, 140, '…' ) ); ?></td>
					<td><a href="<?php echo esc_url( $del ); ?>" onclick="return confirm('<?php echo esc_js( __( 'Annuler cette réservation ?', 'afvf' ) ); ?>');"><?php esc_html_e( 'Annuler', 'afvf' ); ?></a></td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	<?php
}

function afvf_booking_handle_delete() {
	$id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
	if ( ! current_user_can( 'manage_options' ) || ! $id || ! check_admin_referer( 'afvf_booking_delete_' . $id ) ) {
		wp_die( esc_html__( 'Action non autorisée.', 'afvf' ) );
	}
	global $wpdb;
	$wpdb->delete( afvf_booking_table(), array( 'id' => $id ), array( '%d' ) );
	wp_safe_redirect( admin_url( 'edit.php?post_type=afvf_service&page=afvf-bookings&deleted=1' ) );
	exit;
}
add_action( 'admin_post_afvf_booking_delete', 'afvf_booking_handle_delete' );

/* -------------------------------------------------------------------------
 * Motif de blocs
 * ---------------------------------------------------------------------- */

function afvf_register_services_pattern() {
	register_block_pattern(
		'afvf/services-auto',
		array(
			'title'       => __( 'Services en ligne — catalogue automatique + réservation', 'afvf' ),
			'description' => _x( 'Titre « Nos services / POUR LES PROFESSIONNELS » et cartes générées depuis le menu « Services en ligne ». Le bouton « Réserver » ouvre le calendrier de réservation du service.', 'Block pattern description', 'afvf' ),
			'categories'  => array( 'afvf', 'columns' ),
			'content'     => '<!-- wp:group {"className":"afvf-services-auto","layout":{"type":"default"}} -->
<div class="wp-block-group afvf-services-auto"><!-- wp:paragraph {"align":"center","className":"afvf-services-auto__kicker"} -->
<p class="has-text-align-center afvf-services-auto__kicker">' . esc_html__( 'Nos services', 'afvf' ) . '</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"textAlign":"center","level":2,"className":"afvf-services-auto__title"} -->
<h2 class="wp-block-heading has-text-align-center afvf-services-auto__title">' . esc_html__( 'POUR LES PROFESSIONNELS', 'afvf' ) . '</h2>
<!-- /wp:heading -->

<!-- wp:shortcode -->
[afvf_services]
<!-- /wp:shortcode --></div>
<!-- /wp:group -->',
		)
	);
}
add_action( 'init', 'afvf_register_services_pattern', 30 );
