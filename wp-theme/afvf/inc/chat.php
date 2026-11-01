<?php
/**
 * Chat site ↔ administration WordPress.
 *
 * @package AFVF
 */

defined( 'ABSPATH' ) || exit;

define( 'AFVF_CHAT_DB_VERSION', 3 );

function afvf_chat_threads_table() {
	global $wpdb;
	return $wpdb->prefix . 'afvf_chat_threads';
}

function afvf_chat_messages_table() {
	global $wpdb;
	return $wpdb->prefix . 'afvf_chat_messages';
}

function afvf_chat_install_tables() {
	$installed = (int) get_option( 'afvf_chat_db_version', 0 );
	global $wpdb;
	$threads = afvf_chat_threads_table();

	// Colonne contact_captured : vérif à chaque chargement (migration v1 → v2).
	// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$col = $wpdb->get_results( "SHOW COLUMNS FROM {$threads} LIKE 'contact_captured'" );
	if ( empty( $col ) ) {
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$wpdb->query( "ALTER TABLE {$threads} ADD contact_captured tinyint(1) NOT NULL DEFAULT 0 AFTER visitor_email" );
	}

	$msgs_table = afvf_chat_messages_table();
	// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$attach_col = $wpdb->get_results( "SHOW COLUMNS FROM {$msgs_table} LIKE 'attachment_id'" );
	if ( empty( $attach_col ) ) {
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$wpdb->query( "ALTER TABLE {$msgs_table} ADD attachment_id bigint(20) unsigned DEFAULT NULL AFTER body" );
	}

	$installed = (int) get_option( 'afvf_chat_db_version', 0 );
	if ( $installed >= AFVF_CHAT_DB_VERSION ) {
		return;
	}

	$charset = $wpdb->get_charset_collate();
	$msgs    = afvf_chat_messages_table();

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';

	dbDelta(
		"CREATE TABLE {$threads} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			thread_token varchar(64) NOT NULL,
			visitor_name varchar(120) DEFAULT NULL,
			visitor_email varchar(190) DEFAULT NULL,
			contact_captured tinyint(1) NOT NULL DEFAULT 0,
			status varchar(20) NOT NULL DEFAULT 'open',
			admin_unread int(11) NOT NULL DEFAULT 0,
			created_at datetime NOT NULL,
			updated_at datetime NOT NULL,
			PRIMARY KEY (id),
			UNIQUE KEY thread_token (thread_token),
			KEY status (status),
			KEY updated_at (updated_at)
		) {$charset};"
	);

	if ( $installed > 0 && $installed < 2 ) {
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$col = $wpdb->get_results( "SHOW COLUMNS FROM {$threads} LIKE 'contact_captured'" );
		if ( empty( $col ) ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$wpdb->query( "ALTER TABLE {$threads} ADD contact_captured tinyint(1) NOT NULL DEFAULT 0 AFTER visitor_email" );
		}
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$wpdb->query( "UPDATE {$threads} SET contact_captured = 1 WHERE visitor_email IS NOT NULL AND visitor_email != ''" );
	}

	dbDelta(
		"CREATE TABLE {$msgs} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			thread_id bigint(20) unsigned NOT NULL,
			sender varchar(20) NOT NULL,
			body text NOT NULL,
			attachment_id bigint(20) unsigned DEFAULT NULL,
			created_at datetime NOT NULL,
			PRIMARY KEY (id),
			KEY thread_id (thread_id)
		) {$charset};"
	);

	if ( $installed > 0 && $installed < 3 ) {
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$attach_col = $wpdb->get_results( "SHOW COLUMNS FROM {$msgs} LIKE 'attachment_id'" );
		if ( empty( $attach_col ) ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$wpdb->query( "ALTER TABLE {$msgs} ADD attachment_id bigint(20) unsigned DEFAULT NULL AFTER body" );
		}
	}

	update_option( 'afvf_chat_db_version', AFVF_CHAT_DB_VERSION );
}
add_action( 'after_setup_theme', 'afvf_chat_install_tables' );

function afvf_site_chat_enabled() {
	return (bool) get_theme_mod( 'afvf_site_chat', true );
}

/**
 * URL REST fiable (/?rest_route=…) — fonctionne même si /wp-json/ renvoie 404.
 *
 * @param string               $route   ex. chat/start.
 * @param array<string, mixed> $query   Paramètres GET additionnels.
 * @return string
 */
function afvf_chat_rest_url( $route, $query = array() ) {
	$route = ltrim( $route, '/' );
	$args  = array_merge(
		array( 'rest_route' => '/afvf/v1/' . $route ),
		$query
	);
	return add_query_arg( $args, home_url( '/' ) );
}

/**
 * @param string $token Thread token.
 * @return object|null
 */
function afvf_chat_get_thread_by_token( $token ) {
	global $wpdb;
	if ( ! preg_match( '/^[a-zA-Z0-9]{32,64}$/', $token ) ) {
		return null;
	}
	return $wpdb->get_row(
		$wpdb->prepare(
			'SELECT * FROM ' . afvf_chat_threads_table() . ' WHERE thread_token = %s',
			$token
		)
	);
}

/**
 * @param int $thread_id Thread ID.
 * @return array<int, object>
 */
function afvf_chat_get_messages( $thread_id ) {
	global $wpdb;
	return $wpdb->get_results(
		$wpdb->prepare(
			'SELECT * FROM ' . afvf_chat_messages_table() . ' WHERE thread_id = %d ORDER BY created_at ASC, id ASC',
			$thread_id
		)
	);
}

/**
 * Types MIME autorisés pour le chat.
 *
 * @return array<string, string>
 */
function afvf_chat_allowed_mimes() {
	return apply_filters(
		'afvf_chat_upload_mimes',
		array(
			'jpg|jpeg|jpe' => 'image/jpeg',
			'png'          => 'image/png',
			'gif'          => 'image/gif',
			'webp'         => 'image/webp',
			'pdf'          => 'application/pdf',
		)
	);
}

/**
 * @return int
 */
function afvf_chat_max_upload_bytes() {
	return (int) apply_filters( 'afvf_chat_max_upload_bytes', 5 * MB_IN_BYTES );
}

/**
 * @param int $attachment_id Attachment ID.
 * @return array<string, string>|null
 */
function afvf_chat_attachment_payload( $attachment_id ) {
	$attachment_id = (int) $attachment_id;
	if ( $attachment_id < 1 ) {
		return null;
	}
	$url = wp_get_attachment_url( $attachment_id );
	if ( ! $url ) {
		return null;
	}
	$file = get_attached_file( $attachment_id );
	return array(
		'id'   => (string) $attachment_id,
		'url'  => $url,
		'name' => $file ? basename( $file ) : __( 'Fichier', 'afvf' ),
		'mime' => (string) get_post_mime_type( $attachment_id ),
	);
}

/**
 * @param int   $thread_id Thread ID.
 * @param array $file      Entrée $_FILES.
 * @return int|WP_Error
 */
function afvf_chat_store_upload( $thread_id, $file ) {
	if ( empty( $file['tmp_name'] ) || ! is_uploaded_file( $file['tmp_name'] ) ) {
		return new WP_Error( 'afvf_chat_file', __( 'Fichier invalide.', 'afvf' ), array( 'status' => 400 ) );
	}
	if ( ! empty( $file['error'] ) && UPLOAD_ERR_OK !== (int) $file['error'] ) {
		return new WP_Error( 'afvf_chat_file', __( 'Échec de l’envoi du fichier.', 'afvf' ), array( 'status' => 400 ) );
	}
	if ( (int) $file['size'] > afvf_chat_max_upload_bytes() ) {
		return new WP_Error( 'afvf_chat_file', __( 'Fichier trop volumineux (max. 5 Mo).', 'afvf' ), array( 'status' => 400 ) );
	}

	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';

	$mimes     = afvf_chat_allowed_mimes();
	$overrides = array(
		'test_form' => false,
		'mimes'     => $mimes,
	);
	$upload    = wp_handle_upload( $file, $overrides );
	if ( isset( $upload['error'] ) ) {
		return new WP_Error( 'afvf_chat_file', $upload['error'], array( 'status' => 400 ) );
	}

	$attachment = array(
		'post_mime_type' => $upload['type'],
		'post_title'     => sanitize_file_name( pathinfo( $upload['file'], PATHINFO_FILENAME ) ),
		'post_content'   => '',
		'post_status'    => 'inherit',
	);
	$attach_id  = wp_insert_attachment( $attachment, $upload['file'] );
	if ( is_wp_error( $attach_id ) || ! $attach_id ) {
		return new WP_Error( 'afvf_chat_file', __( 'Impossible d’enregistrer le fichier.', 'afvf' ), array( 'status' => 500 ) );
	}
	wp_generate_attachment_metadata( $attach_id, $upload['file'] );
	update_post_meta( $attach_id, '_afvf_chat_thread_id', (int) $thread_id );

	return (int) $attach_id;
}

/**
 * @param int    $thread_id Thread.
 * @param string $sender    visitor|admin|form.
 * @param string $body      Message.
 * @param int    $attachment_id Attachment WP optionnel.
 */
function afvf_chat_add_message( $thread_id, $sender, $body, $attachment_id = 0 ) {
	global $wpdb;
	$body          = wp_strip_all_tags( $body );
	$body          = trim( $body );
	$attachment_id = (int) $attachment_id;
	if ( '' === $body && ! $attachment_id ) {
		return false;
	}

	$now  = current_time( 'mysql', true );
	$row  = array(
		'thread_id'  => $thread_id,
		'sender'     => in_array( $sender, array( 'admin', 'form' ), true ) ? $sender : 'visitor',
		'body'       => $body,
		'created_at' => $now,
	);
	$fmt  = array( '%d', '%s', '%s', '%s' );
	if ( $attachment_id ) {
		$row['attachment_id'] = $attachment_id;
		$fmt[]                = '%d';
	}
	$wpdb->insert( afvf_chat_messages_table(), $row, $fmt );

	$update  = array( 'updated_at' => $now );
	$formats = array( '%s' );
	if ( in_array( $sender, array( 'visitor', 'form' ), true ) ) {
		$thread = $wpdb->get_row( $wpdb->prepare( 'SELECT admin_unread FROM ' . afvf_chat_threads_table() . ' WHERE id = %d', $thread_id ) );
		$update['admin_unread'] = $thread ? ( (int) $thread->admin_unread + 1 ) : 1;
		$formats[]              = '%d';
	}

	$wpdb->update(
		afvf_chat_threads_table(),
		$update,
		array( 'id' => $thread_id ),
		$formats,
		array( '%d' )
	);

	return (int) $wpdb->insert_id;
}

function afvf_chat_notify_admin( $thread, $preview ) {
	$dest = afvf_mod( 'afvf_email', get_option( 'admin_email' ) );
	$link = admin_url( 'admin.php?page=afvf-inbox&thread_id=' . (int) $thread->id );
	wp_mail(
		$dest,
		'[AFVF] Nouveau message chat',
		sprintf(
			"De : %s <%s>\n\n%s\n\nRépondre dans l'admin :\n%s\n",
			$thread->visitor_name ?: __( 'Visiteur', 'afvf' ),
			$thread->visitor_email ?: '—',
			$preview,
			$link
		)
	);
}

function afvf_chat_notify_visitor( $thread, $body ) {
	if ( empty( $thread->visitor_email ) || ! is_email( $thread->visitor_email ) ) {
		return;
	}
	$site = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
	wp_mail(
		$thread->visitor_email,
		sprintf( __( '[%s] Réponse à votre message', 'afvf' ), $site ),
		sprintf(
			__( "Bonjour %1\$s,\n\nL'équipe AFVF vous répond :\n\n%2\$s\n\nVous pouvez aussi rouvrir le chat sur le site pour continuer la conversation.\n\n— %3\$s\n", 'afvf' ),
			$thread->visitor_name ?: '',
			$body,
			$site
		)
	);
}

function afvf_chat_rest_start( WP_REST_Request $request ) {
	$name    = sanitize_text_field( (string) $request->get_param( 'name' ) );
	$email   = sanitize_email( (string) $request->get_param( 'email' ) );
	$message = (string) $request->get_param( 'message' );
	$message = trim( wp_strip_all_tags( $message ) );

	if ( strlen( $message ) < 2 ) {
		return new WP_Error( 'afvf_chat_empty', __( 'Message trop court.', 'afvf' ), array( 'status' => 400 ) );
	}

	global $wpdb;
	$token = bin2hex( random_bytes( 16 ) );
	$now   = current_time( 'mysql', true );

	$wpdb->insert(
		afvf_chat_threads_table(),
		array(
			'thread_token'  => $token,
			'visitor_name'  => $name ?: null,
			'visitor_email'    => $email ?: null,
			'contact_captured' => $email ? 1 : 0,
			'status'           => 'open',
			'admin_unread'     => 0,
			'created_at'       => $now,
			'updated_at'       => $now,
		),
		array( '%s', '%s', '%s', '%d', '%s', '%d', '%s', '%s' )
	);

	$thread_id = (int) $wpdb->insert_id;
	if ( ! $thread_id ) {
		return new WP_Error(
			'afvf_chat_db',
			__( 'Impossible d’enregistrer le message. Rechargez la page ou contactez l’administrateur.', 'afvf' ),
			array( 'status' => 500 )
		);
	}

	afvf_chat_add_message( $thread_id, 'visitor', $message );

	$thread = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . afvf_chat_threads_table() . ' WHERE id = %d', $thread_id ) );
	if ( $thread ) {
		afvf_chat_notify_admin( $thread, $message );
	}

	return rest_ensure_response(
		array(
			'token'           => $token,
			'needs_contact'   => ! (int) $thread->contact_captured,
			'messages'        => afvf_chat_format_messages( afvf_chat_get_messages( $thread_id ) ),
		)
	);
}

function afvf_chat_rest_contact( WP_REST_Request $request ) {
	$token   = sanitize_text_field( (string) $request->get_param( 'token' ) );
	$name    = sanitize_text_field( (string) $request->get_param( 'name' ) );
	$email   = sanitize_email( (string) $request->get_param( 'email' ) );
	$message = trim( wp_strip_all_tags( (string) $request->get_param( 'message' ) ) );
	$thread  = afvf_chat_get_thread_by_token( $token );

	if ( ! $thread || 'open' !== $thread->status ) {
		return new WP_Error( 'afvf_chat_thread', __( 'Conversation introuvable.', 'afvf' ), array( 'status' => 404 ) );
	}
	if ( ! $name || ! is_email( $email ) ) {
		return new WP_Error( 'afvf_chat_contact', __( 'Nom et e-mail valides requis.', 'afvf' ), array( 'status' => 400 ) );
	}

	global $wpdb;
	$now  = current_time( 'mysql', true );
	$form = sprintf(
		"FORMULAIRE ENVOYÉ PAR CHAT\nNom : %s\nE-mail : %s\nMessage : %s",
		$name,
		$email,
		$message ?: '—'
	);

	$wpdb->update(
		afvf_chat_threads_table(),
		array(
			'visitor_name'     => $name,
			'visitor_email'    => $email,
			'contact_captured' => 1,
			'updated_at'       => $now,
		),
		array( 'id' => (int) $thread->id ),
		array( '%s', '%s', '%d', '%s' ),
		array( '%d' )
	);

	afvf_chat_add_message( (int) $thread->id, 'form', $form );

	$thread = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . afvf_chat_threads_table() . ' WHERE id = %d', (int) $thread->id ) );
	afvf_chat_notify_admin( $thread, $form );

	return rest_ensure_response(
		array(
			'needs_contact' => false,
			'messages'      => afvf_chat_format_messages( afvf_chat_get_messages( (int) $thread->id ) ),
		)
	);
}

function afvf_chat_rest_send( WP_REST_Request $request ) {
	$token   = sanitize_text_field( (string) $request->get_param( 'token' ) );
	$message = trim( wp_strip_all_tags( (string) $request->get_param( 'message' ) ) );
	$thread  = afvf_chat_get_thread_by_token( $token );

	if ( ! $thread || 'open' !== $thread->status ) {
		return new WP_Error( 'afvf_chat_thread', __( 'Conversation introuvable.', 'afvf' ), array( 'status' => 404 ) );
	}
	if ( strlen( $message ) < 1 ) {
		return new WP_Error( 'afvf_chat_empty', __( 'Message vide.', 'afvf' ), array( 'status' => 400 ) );
	}

	afvf_chat_add_message( (int) $thread->id, 'visitor', $message );
	afvf_chat_notify_admin( $thread, $message );

	return rest_ensure_response(
		array(
			'messages' => afvf_chat_format_messages( afvf_chat_get_messages( (int) $thread->id ) ),
		)
	);
}

function afvf_chat_rest_fetch( WP_REST_Request $request ) {
	$token  = sanitize_text_field( (string) $request->get_param( 'token' ) );
	$thread = afvf_chat_get_thread_by_token( $token );
	if ( ! $thread ) {
		return new WP_Error( 'afvf_chat_thread', __( 'Conversation introuvable.', 'afvf' ), array( 'status' => 404 ) );
	}

	return rest_ensure_response(
		array(
			'status'         => $thread->status,
			'needs_contact'  => ! (int) $thread->contact_captured,
			'messages'       => afvf_chat_format_messages( afvf_chat_get_messages( (int) $thread->id ) ),
		)
	);
}

/**
 * Libellé visiteur type Wix (Visitor #123).
 *
 * @param object $thread Thread row.
 */
function afvf_chat_visitor_label( $thread ) {
	if ( ! empty( $thread->visitor_name ) ) {
		return $thread->visitor_name;
	}
	if ( ! empty( $thread->visitor_email ) ) {
		return $thread->visitor_email;
	}
	return sprintf(
		/* translators: %d: visitor thread id */
		__( 'Visiteur #%d', 'afvf' ),
		(int) $thread->id
	);
}

/**
 * Initiales pour avatar (nom, e-mail ou « Visiteur #n »).
 *
 * @param string $display_name Label affiché.
 */
function afvf_contact_avatar_initials( $display_name ) {
	$display_name = trim( (string) $display_name );
	if ( '' === $display_name ) {
		return '?';
	}
	if ( preg_match( '/^visiteur\s*#\d+/iu', $display_name ) ) {
		return 'V';
	}
	$local = $display_name;
	if ( str_contains( $local, '@' ) ) {
		$local = (string) strstr( $local, '@', true );
		$local = str_replace( array( '.', '_', '-', '+' ), ' ', $local );
	}
	$parts = preg_split( '/\s+/u', $local, -1, PREG_SPLIT_NO_EMPTY );
	if ( is_array( $parts ) && count( $parts ) >= 2 ) {
		return mb_strtoupper( mb_substr( $parts[0], 0, 1 ) . mb_substr( $parts[1], 0, 1 ) );
	}
	$word = is_array( $parts ) && isset( $parts[0] ) ? $parts[0] : $local;
	$word = preg_replace( '/[^a-zA-Z0-9\x{00C0}-\x{024F}]/u', '', $word );
	if ( '' === $word ) {
		return '?';
	}
	if ( mb_strlen( $word ) >= 2 ) {
		return mb_strtoupper( mb_substr( $word, 0, 2 ) );
	}
	return mb_strtoupper( mb_substr( $word, 0, 1 ) );
}

/**
 * Couleur d’avatar stable (palette type Wix).
 *
 * @param string $seed Chaîne (e-mail, nom, id).
 */
function afvf_contact_avatar_color_index( $seed ) {
	return abs( crc32( strtolower( trim( (string) $seed ) ) ) ) % 6;
}

/**
 * Markup avatar circulaire (initiales + badges optionnels).
 *
 * @param string               $display_name Nom affiché.
 * @param array<string, mixed> $args         size, show_online, show_chat_badge, extra_class.
 */
function afvf_contact_avatar_markup( $display_name, $args = array() ) {
	$args = wp_parse_args(
		$args,
		array(
			'size'             => 'md',
			'show_online'      => false,
			'show_chat_badge'  => false,
			'extra_class'      => '',
		)
	);
	$initials = afvf_contact_avatar_initials( $display_name );
	$classes  = array(
		'afvf-inbox-avatar',
		'afvf-inbox-avatar--' . sanitize_html_class( (string) $args['size'] ),
		'afvf-inbox-avatar--hue-' . afvf_contact_avatar_color_index( $display_name ),
	);
	if ( $args['extra_class'] ) {
		$classes[] = sanitize_html_class( (string) $args['extra_class'] );
	}
	$html  = '<span class="' . esc_attr( implode( ' ', $classes ) ) . '">';
	$html .= '<span class="afvf-inbox-avatar__initials">' . esc_html( $initials ) . '</span>';
	if ( $args['show_online'] ) {
		$html .= '<span class="afvf-inbox-avatar__status" title="' . esc_attr__( 'Conversation active', 'afvf' ) . '"></span>';
	}
	if ( $args['show_chat_badge'] ) {
		$html .= '<span class="afvf-inbox-avatar__channel" title="' . esc_attr__( 'Chat', 'afvf' ) . '"><span class="afvf-inbox-avatar__channel-icon" aria-hidden="true"></span></span>';
	}
	$html .= '</span>';
	return $html;
}

/**
 * @param array<int, object> $rows Messages.
 * @return array<int, array<string, string>>
 */
function afvf_chat_format_messages( $rows ) {
	$out = array();
	foreach ( $rows as $row ) {
		$item = array(
			'id'      => (string) $row->id,
			'sender'  => $row->sender,
			'body'    => $row->body,
			'created' => $row->created_at,
		);
		$attach = afvf_chat_attachment_payload( isset( $row->attachment_id ) ? (int) $row->attachment_id : 0 );
		if ( $attach ) {
			$item['attachment'] = $attach;
		}
		$out[] = $item;
	}
	return $out;
}

/**
 * Aperçu texte pour notifications e-mail.
 *
 * @param string $body Message.
 * @param int    $attachment_id Attachment.
 */
function afvf_chat_message_preview( $body, $attachment_id = 0 ) {
	$attach = afvf_chat_attachment_payload( $attachment_id );
	if ( $attach ) {
		$label = sprintf(
			/* translators: %s: file name */
			__( '[Pièce jointe] %s', 'afvf' ),
			$attach['name']
		);
		return $body ? $body . "\n" . $label : $label;
	}
	return $body;
}

/**
 * Affiche le contenu d’un message (texte + pièce jointe).
 *
 * @param object $message Row message.
 */
function afvf_chat_echo_message_content( $message ) {
	$attachment_id = isset( $message->attachment_id ) ? (int) $message->attachment_id : 0;
	$attach        = afvf_chat_attachment_payload( $attachment_id );
	if ( $attach ) {
		if ( str_starts_with( $attach['mime'], 'image/' ) ) {
			echo '<img class="afvf-chat-attach-img" src="' . esc_url( $attach['url'] ) . '" alt="' . esc_attr( $attach['name'] ) . '" loading="lazy">';
		} else {
			echo '<a class="afvf-chat-attach-file" href="' . esc_url( $attach['url'] ) . '" target="_blank" rel="noopener noreferrer">' . esc_html( $attach['name'] ) . '</a>';
		}
	}
	if ( ! empty( $message->body ) ) {
		echo '<span class="afvf-chat-attach-caption">' . esc_html( $message->body ) . '</span>';
	}
}

function afvf_chat_rest_upload( WP_REST_Request $request ) {
	$token   = sanitize_text_field( (string) $request->get_param( 'token' ) );
	$caption = trim( wp_strip_all_tags( (string) $request->get_param( 'message' ) ) );
	$files   = $request->get_file_params();
	$file    = isset( $files['file'] ) ? $files['file'] : null;

	if ( empty( $file ) ) {
		return new WP_Error( 'afvf_chat_file', __( 'Aucun fichier.', 'afvf' ), array( 'status' => 400 ) );
	}

	global $wpdb;
	$thread = $token ? afvf_chat_get_thread_by_token( $token ) : null;

	if ( $thread && 'open' !== $thread->status ) {
		return new WP_Error( 'afvf_chat_thread', __( 'Conversation fermée.', 'afvf' ), array( 'status' => 403 ) );
	}

	if ( ! $thread ) {
		$token = bin2hex( random_bytes( 16 ) );
		$now   = current_time( 'mysql', true );
		$wpdb->insert(
			afvf_chat_threads_table(),
			array(
				'thread_token'     => $token,
				'visitor_name'     => null,
				'visitor_email'    => null,
				'contact_captured' => 0,
				'status'           => 'open',
				'admin_unread'     => 0,
				'created_at'       => $now,
				'updated_at'       => $now,
			),
			array( '%s', '%s', '%s', '%d', '%s', '%d', '%s', '%s' )
		);
		$thread_id = (int) $wpdb->insert_id;
		if ( ! $thread_id ) {
			return new WP_Error( 'afvf_chat_db', __( 'Impossible de démarrer la conversation.', 'afvf' ), array( 'status' => 500 ) );
		}
		$thread = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . afvf_chat_threads_table() . ' WHERE id = %d', $thread_id ) );
	}

	$stored = afvf_chat_store_upload( (int) $thread->id, $file );
	if ( is_wp_error( $stored ) ) {
		return $stored;
	}

	afvf_chat_add_message( (int) $thread->id, 'visitor', $caption, (int) $stored );
	$preview = afvf_chat_message_preview( $caption, (int) $stored );
	afvf_chat_notify_admin( $thread, $preview );

	return rest_ensure_response(
		array(
			'token'           => $thread->thread_token,
			'needs_contact'   => ! (int) $thread->contact_captured,
			'messages'        => afvf_chat_format_messages( afvf_chat_get_messages( (int) $thread->id ) ),
		)
	);
}

function afvf_chat_register_rest_routes() {
	register_rest_route(
		'afvf/v1',
		'/chat/start',
		array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => 'afvf_chat_rest_start',
			'permission_callback' => '__return_true',
		)
	);
	register_rest_route(
		'afvf/v1',
		'/chat/send',
		array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => 'afvf_chat_rest_send',
			'permission_callback' => '__return_true',
		)
	);
	register_rest_route(
		'afvf/v1',
		'/chat/contact',
		array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => 'afvf_chat_rest_contact',
			'permission_callback' => '__return_true',
		)
	);
	register_rest_route(
		'afvf/v1',
		'/chat/messages',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'afvf_chat_rest_fetch',
			'permission_callback' => '__return_true',
			'args'                => array(
				'token' => array(
					'required' => true,
					'type'     => 'string',
				),
			),
		)
	);

	register_rest_route(
		'afvf/v1',
		'/chat/upload',
		array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => 'afvf_chat_rest_upload',
			'permission_callback' => '__return_true',
		)
	);

	register_rest_route(
		'afvf/v1',
		'/chat/admin/thread/(?P<id>\d+)',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'afvf_chat_rest_admin_thread',
			'permission_callback' => static function () {
				return current_user_can( 'manage_options' );
			},
		)
	);

	register_rest_route(
		'afvf/v1',
		'/chat/admin/reply',
		array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => 'afvf_chat_rest_admin_reply',
			'permission_callback' => static function () {
				return current_user_can( 'manage_options' );
			},
		)
	);
}
add_action( 'rest_api_init', 'afvf_chat_register_rest_routes' );

function afvf_chat_rest_admin_thread( WP_REST_Request $request ) {
	$thread_id = (int) $request['id'];
	global $wpdb;
	$thread = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . afvf_chat_threads_table() . ' WHERE id = %d', $thread_id ) );
	if ( ! $thread ) {
		return new WP_Error( 'afvf_chat_thread', __( 'Conversation introuvable.', 'afvf' ), array( 'status' => 404 ) );
	}

	$wpdb->update(
		afvf_chat_threads_table(),
		array( 'admin_unread' => 0 ),
		array( 'id' => $thread_id ),
		array( '%d' ),
		array( '%d' )
	);

	return rest_ensure_response(
		array(
			'thread'   => array(
				'id'    => (int) $thread->id,
				'label' => afvf_chat_visitor_label( $thread ),
				'email' => $thread->visitor_email,
			),
			'messages' => afvf_chat_format_messages( afvf_chat_get_messages( $thread_id ) ),
		)
	);
}

function afvf_chat_rest_admin_reply( WP_REST_Request $request ) {
	$thread_id = (int) $request->get_param( 'thread_id' );
	$body      = trim( wp_strip_all_tags( (string) $request->get_param( 'body' ) ) );
	$notify    = (bool) $request->get_param( 'notify_email' );

	global $wpdb;
	$thread = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . afvf_chat_threads_table() . ' WHERE id = %d', $thread_id ) );
	if ( ! $thread || '' === $body ) {
		return new WP_Error( 'afvf_chat_empty', __( 'Message vide.', 'afvf' ), array( 'status' => 400 ) );
	}

	afvf_chat_add_message( $thread_id, 'admin', $body );
	$wpdb->update(
		afvf_chat_threads_table(),
		array( 'admin_unread' => 0 ),
		array( 'id' => $thread_id ),
		array( '%d' ),
		array( '%d' )
	);

	if ( $notify ) {
		afvf_chat_notify_visitor( $thread, $body );
	}

	return rest_ensure_response(
		array(
			'messages' => afvf_chat_format_messages( afvf_chat_get_messages( $thread_id ) ),
		)
	);
}

function afvf_enqueue_chat_script() {
	if ( ! afvf_site_chat_enabled() ) {
		return;
	}
	wp_enqueue_script(
		'afvf-chat',
		get_template_directory_uri() . '/assets/js/chat.js',
		array(),
		'1.7.0',
		true
	);
	wp_localize_script(
		'afvf-chat',
		'afvfChat',
		array(
			'restHome' => esc_url_raw( home_url( '/' ) ),
			'nonce'    => wp_create_nonce( 'wp_rest' ),
			'pollMs'   => 8000,
			'strings' => array(
				'title'          => __( 'AFVF — Association des Familles…', 'afvf' ),
				'online'         => __( 'En ligne', 'afvf' ),
				'contactPrompt'  => __( 'Bonjour, veuillez laisser vos coordonnées pour être contacté même si vous n’êtes plus sur le site.', 'afvf' ),
				'name'           => __( 'Nom', 'afvf' ),
				'email'          => __( 'E-mail', 'afvf' ),
				'message'        => __( 'Message', 'afvf' ),
				'ok'             => __( 'OK', 'afvf' ),
				'placeholder'    => __( 'Rédigez votre message…', 'afvf' ),
				'send'           => __( 'Envoyer', 'afvf' ),
				'close'          => __( 'Fermer', 'afvf' ),
				'you'            => __( 'Vous', 'afvf' ),
				'team'           => __( 'AFVF', 'afvf' ),
				'error'          => __( 'Envoi impossible. Réessayez.', 'afvf' ),
				'closed'         => __( 'Cette conversation est fermée.', 'afvf' ),
				'attachError'       => __( 'Impossible d’envoyer ce fichier.', 'afvf' ),
				'attachUploading'   => __( 'Envoi en cours…', 'afvf' ),
			),
			'maxUploadBytes' => afvf_chat_max_upload_bytes(),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'afvf_enqueue_chat_script', 20 );
