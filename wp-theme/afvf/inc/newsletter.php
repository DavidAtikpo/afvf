<?php
/**
 * Inscription newsletter (pied de page).
 *
 * @package AFVF
 */

defined( 'ABSPATH' ) || exit;

define( 'AFVF_NEWSLETTER_DB_VERSION', 2 );

/**
 * @return string
 */
function afvf_newsletter_table() {
	global $wpdb;
	return $wpdb->prefix . 'afvf_newsletter';
}

function afvf_newsletter_install_table() {
	$installed = (int) get_option( 'afvf_newsletter_db_version', 0 );
	if ( $installed >= AFVF_NEWSLETTER_DB_VERSION ) {
		return;
	}

	global $wpdb;
	$table   = afvf_newsletter_table();
	$charset = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE {$table} (
		id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		email varchar(190) NOT NULL,
		first_name varchar(100) DEFAULT NULL,
		last_name varchar(100) DEFAULT NULL,
		status varchar(20) NOT NULL DEFAULT 'active',
		subscribed_at datetime NOT NULL,
		confirmed_at datetime DEFAULT NULL,
		confirm_token varchar(64) DEFAULT NULL,
		ip_address varchar(45) DEFAULT NULL,
		PRIMARY KEY  (id),
		UNIQUE KEY email (email),
		KEY status (status)
	) {$charset};";

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	dbDelta( $sql );
	update_option( 'afvf_newsletter_db_version', AFVF_NEWSLETTER_DB_VERSION );
}
add_action( 'after_setup_theme', 'afvf_newsletter_install_table' );

/**
 * @param string $email Email.
 * @return object|null
 */
function afvf_newsletter_get_by_email( $email ) {
	global $wpdb;
	return $wpdb->get_row(
		$wpdb->prepare(
			'SELECT * FROM ' . afvf_newsletter_table() . ' WHERE email = %s',
			$email
		)
	);
}

/**
 * @param string $token Token.
 * @return object|null
 */
function afvf_newsletter_get_by_token( $token ) {
	global $wpdb;
	return $wpdb->get_row(
		$wpdb->prepare(
			'SELECT * FROM ' . afvf_newsletter_table() . ' WHERE confirm_token = %s',
			$token
		)
	);
}

/**
 * @return bool
 */
function afvf_newsletter_double_optin() {
	return (bool) get_theme_mod( 'afvf_newsletter_double_optin', false );
}

/**
 * @param string $email Subscriber email.
 * @param string $token  Confirmation token (optional).
 */
function afvf_newsletter_email_subscriber( $email, $token = '' ) {
	$site = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );

	if ( $token ) {
		$link = add_query_arg(
			array(
				'action' => 'afvf_newsletter_confirm',
				'token'  => $token,
			),
			admin_url( 'admin-post.php' )
		);
		$subject = sprintf( __( '[%s] Confirmez votre inscription à la newsletter', 'afvf' ), $site );
		$body    = sprintf(
			__( "Bonjour,\n\nMerci pour votre intérêt pour %s.\n\nPour recevoir nos actualités mensuelles, confirmez votre adresse en cliquant sur ce lien :\n%s\n\nSi vous n'êtes pas à l'origine de cette demande, ignorez ce message.\n", 'afvf' ),
			$site,
			$link
		);
	} else {
		$subject = sprintf( __( '[%s] Inscription à la newsletter confirmée', 'afvf' ), $site );
		$body    = sprintf(
			__( "Bonjour,\n\nVous êtes inscrit(e) aux actualités mensuelles de %s.\n\nÀ bientôt,\nL'équipe AFVF\n", 'afvf' ),
			$site
		);
	}

	wp_mail( $email, $subject, $body );
}

/**
 * E-mails éligibles à une campagne (actifs ; double opt-in = confirmés).
 *
 * @return string[]
 */
function afvf_newsletter_sendable_emails() {
	global $wpdb;
	$table  = afvf_newsletter_table();
	$double = afvf_newsletter_double_optin();
	if ( $double ) {
		$sql = "SELECT email FROM {$table} WHERE status = 'active' AND confirmed_at IS NOT NULL ORDER BY email ASC";
	} else {
		$sql = "SELECT email FROM {$table} WHERE status = 'active' ORDER BY email ASC";
	}
	$rows = $wpdb->get_col( $sql );
	return array_filter( array_map( 'sanitize_email', $rows ) );
}

/**
 * @param string $to      Recipient.
 * @param string $subject Subject.
 * @param string $body    Plain or HTML body.
 * @return bool
 */
function afvf_newsletter_send_message( $to, $subject, $body ) {
	if ( ! is_email( $to ) ) {
		return false;
	}
	$site = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
	$headers = array( 'Content-Type: text/html; charset=UTF-8' );
	$html = wpautop( wp_kses_post( $body ) );
	$html = '<div style="font-family:Helvetica,Arial,sans-serif;font-size:15px;line-height:1.5;color:#222;">' . $html . '<p style="margin-top:2rem;font-size:13px;color:#666;">— ' . esc_html( $site ) . '</p></div>';
	return wp_mail( $to, $subject, $html, $headers );
}

/**
 * Formulaire « Rejoignez notre liste » (page dédiée, type Wix).
 *
 * @param array $args title, subtitle.
 */
function afvf_newsletter_render_list_form( $args = array() ) {
	$args = wp_parse_args(
		$args,
		array(
			'title'    => __( 'Rejoignez notre liste', 'afvf' ),
			'subtitle' => __( 'Inscrivez-vous pour rester informé', 'afvf' ),
		)
	);

	$email_val = '';
	if ( isset( $_GET['newsletter'] ) && 'invalid' === sanitize_key( wp_unslash( $_GET['newsletter'] ) ) && isset( $_GET['email'] ) ) {
		$email_val = sanitize_email( wp_unslash( $_GET['email'] ) );
	}
	?>
	<section class="afvf-newsletter-signup wrap">
		<header class="afvf-newsletter-signup__header">
			<h1 class="afvf-newsletter-signup__title"><?php echo esc_html( $args['title'] ); ?></h1>
			<p class="afvf-newsletter-signup__subtitle"><?php echo esc_html( $args['subtitle'] ); ?></p>
		</header>
		<?php afvf_newsletter_render_notice( 'afvf-newsletter-signup__notice' ); ?>
		<form class="afvf-newsletter-signup__form" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
			<input type="hidden" name="action" value="afvf_newsletter">
			<?php wp_nonce_field( 'afvf_newsletter', 'afvf_newsletter_nonce' ); ?>
			<div class="afvf-honeypot" aria-hidden="true">
				<label for="afvf-company-signup"><?php esc_html_e( 'Ne pas remplir', 'afvf' ); ?></label>
				<input id="afvf-company-signup" type="text" name="afvf_company" tabindex="-1" autocomplete="off">
			</div>
			<div class="afvf-newsletter-signup__row afvf-newsletter-signup__row--names">
				<p class="afvf-newsletter-signup__field">
					<label for="afvf-first-name"><?php esc_html_e( 'Prénom', 'afvf' ); ?></label>
					<input id="afvf-first-name" type="text" name="first_name" autocomplete="given-name" maxlength="100">
				</p>
				<p class="afvf-newsletter-signup__field">
					<label for="afvf-last-name"><?php esc_html_e( 'Nom', 'afvf' ); ?></label>
					<input id="afvf-last-name" type="text" name="last_name" autocomplete="family-name" maxlength="100">
				</p>
			</div>
			<p class="afvf-newsletter-signup__field afvf-newsletter-signup__field--email">
				<label for="afvf-signup-email"><?php esc_html_e( 'E-mail', 'afvf' ); ?> <span class="afvf-required" aria-hidden="true">*</span></label>
				<input id="afvf-signup-email" type="email" name="email" required autocomplete="email" value="<?php echo esc_attr( $email_val ); ?>">
			</p>
			<button type="submit" class="afvf-newsletter-signup__submit"><?php esc_html_e( 'S’inscrire', 'afvf' ); ?></button>
		</form>
	</section>
	<?php
}

/**
 * @return string
 */
function afvf_newsletter_inscription_shortcode() {
	ob_start();
	afvf_newsletter_render_list_form();
	return ob_get_clean();
}
add_shortcode( 'afvf_newsletter_inscription', 'afvf_newsletter_inscription_shortcode' );

function afvf_newsletter_notify_admin( $email, $first_name = '', $last_name = '' ) {
	$dest = afvf_mod( 'afvf_email', 'assofvf@gmail.com' );
	$name   = trim( $first_name . ' ' . $last_name );
	$lines  = "Nouvelle inscription à la newsletter :\n\n";
	if ( $name ) {
		$lines .= sprintf( "Nom : %s\n", $name );
	}
	$lines .= sprintf( "E-mail : %s\n\nDate : %s\n\nListe : %s\n", $email, gmdate( 'Y-m-d H:i:s' ), admin_url( 'admin.php?page=afvf-newsletter' ) );
	wp_mail( $dest, '[AFVF] Nouvelle inscription newsletter', $lines );
}

function afvf_handle_newsletter() {
	$redirect = wp_get_referer() ?: home_url( '/' );
	$redirect = remove_query_arg( array( 'newsletter', 'token' ), $redirect );

	if ( ! isset( $_POST['afvf_newsletter_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['afvf_newsletter_nonce'] ) ), 'afvf_newsletter' ) ) {
		wp_safe_redirect( add_query_arg( 'newsletter', 'error', $redirect ) );
		exit;
	}

	// Honeypot anti-spam.
	if ( ! empty( $_POST['afvf_company'] ) ) {
		wp_safe_redirect( add_query_arg( 'newsletter', 'ok', $redirect ) );
		exit;
	}

	$email      = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	$first_name = isset( $_POST['first_name'] ) ? sanitize_text_field( wp_unslash( $_POST['first_name'] ) ) : '';
	$last_name  = isset( $_POST['last_name'] ) ? sanitize_text_field( wp_unslash( $_POST['last_name'] ) ) : '';
	if ( ! $email || ! is_email( $email ) ) {
		wp_safe_redirect(
			add_query_arg(
				array(
					'newsletter' => 'invalid',
					'email'      => rawurlencode( $email ),
				),
				$redirect
			)
		);
		exit;
	}

	$existing = afvf_newsletter_get_by_email( $email );
	if ( $existing && 'active' === $existing->status ) {
		wp_safe_redirect( add_query_arg( 'newsletter', 'exists', $redirect ) );
		exit;
	}

	global $wpdb;
	$table   = afvf_newsletter_table();
	$now     = current_time( 'mysql', true );
	$ip      = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	$double  = afvf_newsletter_double_optin();
	$token   = $double ? wp_generate_password( 32, false, false ) : null;
	$status  = $double ? 'pending' : 'active';
	$confirmed = $double ? null : $now;

	$row_data = array(
		'email'         => $email,
		'first_name'    => $first_name ?: null,
		'last_name'     => $last_name ?: null,
		'status'        => $status,
		'subscribed_at' => $now,
		'confirmed_at'  => $confirmed,
		'confirm_token' => $token,
		'ip_address'    => $ip,
	);

	if ( $existing ) {
		$wpdb->update(
			$table,
			$row_data,
			array( 'id' => $existing->id ),
			array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' ),
			array( '%d' )
		);
	} else {
		$wpdb->insert(
			$table,
			$row_data,
			array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
		);
	}

	if ( $double && $token ) {
		afvf_newsletter_email_subscriber( $email, $token );
		wp_safe_redirect( add_query_arg( 'newsletter', 'confirm_sent', $redirect ) );
	} else {
		afvf_newsletter_email_subscriber( $email );
		afvf_newsletter_notify_admin( $email, $first_name, $last_name );
		wp_safe_redirect( add_query_arg( 'newsletter', 'ok', $redirect ) );
	}
	exit;
}
add_action( 'admin_post_nopriv_afvf_newsletter', 'afvf_handle_newsletter' );
add_action( 'admin_post_afvf_newsletter', 'afvf_handle_newsletter' );

function afvf_handle_newsletter_confirm() {
	$redirect = home_url( '/' );
	$token    = isset( $_GET['token'] ) ? sanitize_text_field( wp_unslash( $_GET['token'] ) ) : '';

	if ( ! $token ) {
		wp_safe_redirect( add_query_arg( 'newsletter', 'error', $redirect ) );
		exit;
	}

	$row = afvf_newsletter_get_by_token( $token );
	if ( ! $row ) {
		wp_safe_redirect( add_query_arg( 'newsletter', 'error', $redirect ) );
		exit;
	}

	if ( 'active' === $row->status ) {
		wp_safe_redirect( add_query_arg( 'newsletter', 'ok', $redirect ) );
		exit;
	}

	global $wpdb;
	$now = current_time( 'mysql', true );
	$wpdb->update(
		afvf_newsletter_table(),
		array(
			'status'        => 'active',
			'confirmed_at'  => $now,
			'confirm_token' => null,
		),
		array( 'id' => $row->id ),
		array( '%s', '%s', '%s' ),
		array( '%d' )
	);

	afvf_newsletter_email_subscriber( $row->email );
	afvf_newsletter_notify_admin( $row->email );

	wp_safe_redirect( add_query_arg( 'newsletter', 'confirmed', $redirect ) );
	exit;
}
add_action( 'admin_post_nopriv_afvf_newsletter_confirm', 'afvf_handle_newsletter_confirm' );
add_action( 'admin_post_afvf_newsletter_confirm', 'afvf_handle_newsletter_confirm' );

/**
 * Affiche le message après inscription (pied de page).
 */
/**
 * @param string $wrap_class Classes du conteneur (footer ou page inscription).
 */
function afvf_newsletter_render_notice( $wrap_class = 'footer-newsletter-notice' ) {
	if ( ! isset( $_GET['newsletter'] ) ) {
		return;
	}

	$code = sanitize_key( wp_unslash( $_GET['newsletter'] ) );
	$msgs = array(
		'ok'           => __( 'Merci ! Vous êtes inscrit(e) à nos actualités mensuelles.', 'afvf' ),
		'exists'       => __( 'Cette adresse est déjà inscrite à la newsletter.', 'afvf' ),
		'invalid'      => __( 'Adresse e-mail invalide. Merci de réessayer.', 'afvf' ),
		'confirm_sent' => __( 'Un e-mail de confirmation vient de vous être envoyé. Cliquez sur le lien pour finaliser l’inscription.', 'afvf' ),
		'confirmed'    => __( 'Votre inscription à la newsletter est confirmée. Merci !', 'afvf' ),
		'error'        => __( 'Une erreur est survenue. Merci de réessayer.', 'afvf' ),
	);

	if ( ! isset( $msgs[ $code ] ) ) {
		return;
	}

	$state = in_array( $code, array( 'invalid', 'error' ), true ) ? 'error' : 'ok';
	printf(
		'<p class="%1$s %1$s--%2$s" role="status">%3$s</p>',
		esc_attr( $wrap_class ),
		esc_attr( $state ),
		esc_html( $msgs[ $code ] )
	);
}
