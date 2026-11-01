<?php
/**
 * Formulaire « Nous sommes @ votre écoute » (page Mobiliser / Contact).
 *
 * @package AFVF
 */

defined( 'ABSPATH' ) || exit;

define( 'AFVF_CONTACT_DB_VERSION', 1 );

/**
 * @return string
 */
function afvf_contact_table() {
	global $wpdb;
	return $wpdb->prefix . 'afvf_contact';
}

function afvf_contact_install_table() {
	$installed = (int) get_option( 'afvf_contact_db_version', 0 );
	if ( $installed >= AFVF_CONTACT_DB_VERSION ) {
		return;
	}

	global $wpdb;
	$table   = afvf_contact_table();
	$charset = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE {$table} (
		id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		name varchar(190) NOT NULL DEFAULT '',
		organization varchar(190) DEFAULT NULL,
		email varchar(190) NOT NULL,
		submitted_at datetime NOT NULL,
		ip_address varchar(45) DEFAULT NULL,
		status varchar(20) NOT NULL DEFAULT 'new',
		PRIMARY KEY  (id),
		KEY status (status),
		KEY submitted_at (submitted_at)
	) {$charset};";

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	dbDelta( $sql );
	update_option( 'afvf_contact_db_version', AFVF_CONTACT_DB_VERSION );
}
add_action( 'after_setup_theme', 'afvf_contact_install_table' );

/**
 * @param string $wrap_class Notice container class base.
 */
function afvf_contact_render_notice( $wrap_class = 'afvf-contact-ecoute__notice' ) {
	if ( ! isset( $_GET['contact'] ) ) {
		return;
	}

	$code = sanitize_key( wp_unslash( $_GET['contact'] ) );
	$msgs = array(
		'ok'      => __( 'Merci, votre message a bien été envoyé. Nous vous recontacterons rapidement.', 'afvf' ),
		'invalid' => __( 'Merci de renseigner un nom et une adresse e-mail valide.', 'afvf' ),
		'error'   => __( 'Une erreur est survenue. Merci de réessayer.', 'afvf' ),
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

/**
 * @param array $args title, subtitle.
 */
function afvf_contact_render_form( $args = array() ) {
	$args = wp_parse_args(
		$args,
		array(
			'title'    => __( 'NOUS SOMMES @ VOTRE ECOUTE', 'afvf' ),
			'subtitle' => __( 'Merci de nous contacter via ce formulaire.', 'afvf' ),
		)
	);

	$email_val = '';
	$name_val  = '';
	if ( isset( $_GET['contact'] ) && 'invalid' === sanitize_key( wp_unslash( $_GET['contact'] ) ) ) {
		if ( isset( $_GET['email'] ) ) {
			$email_val = sanitize_email( wp_unslash( $_GET['email'] ) );
		}
		if ( isset( $_GET['name'] ) ) {
			$name_val = sanitize_text_field( wp_unslash( $_GET['name'] ) );
		}
	}
	?>
	<section class="afvf-contact-ecoute">
		<header class="afvf-contact-ecoute__header">
			<h2 class="afvf-contact-ecoute__title"><?php echo esc_html( $args['title'] ); ?></h2>
			<p class="afvf-contact-ecoute__subtitle"><?php echo esc_html( $args['subtitle'] ); ?></p>
		</header>
		<?php afvf_contact_render_notice(); ?>
		<form class="afvf-contact-ecoute__form" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
			<input type="hidden" name="action" value="afvf_contact">
			<?php wp_nonce_field( 'afvf_contact', 'afvf_contact_nonce' ); ?>
			<div class="afvf-honeypot" aria-hidden="true">
				<label for="afvf-contact-company"><?php esc_html_e( 'Ne pas remplir', 'afvf' ); ?></label>
				<input id="afvf-contact-company" type="text" name="afvf_company" tabindex="-1" autocomplete="off">
			</div>
			<div class="afvf-contact-ecoute__fields">
				<p class="afvf-contact-ecoute__field">
					<label for="afvf-contact-name"><?php esc_html_e( 'Nom', 'afvf' ); ?></label>
					<input id="afvf-contact-name" type="text" name="name" required autocomplete="name" maxlength="190" value="<?php echo esc_attr( $name_val ); ?>">
				</p>
				<p class="afvf-contact-ecoute__field">
					<label for="afvf-contact-org"><?php esc_html_e( 'Organisme', 'afvf' ); ?></label>
					<input id="afvf-contact-org" type="text" name="organization" autocomplete="organization" maxlength="190">
				</p>
				<p class="afvf-contact-ecoute__field">
					<label for="afvf-contact-email"><?php esc_html_e( 'E-mail', 'afvf' ); ?> <span class="afvf-required" aria-hidden="true">*</span></label>
					<input id="afvf-contact-email" type="email" name="email" required autocomplete="email" value="<?php echo esc_attr( $email_val ); ?>">
				</p>
				<p class="afvf-contact-ecoute__submit-wrap">
					<button type="submit" class="afvf-contact-ecoute__submit"><?php esc_html_e( 'Envoyer', 'afvf' ); ?></button>
				</p>
			</div>
		</form>
	</section>
	<?php
}

/**
 * @return string
 */
function afvf_contact_form_shortcode() {
	ob_start();
	afvf_contact_render_form();
	return ob_get_clean();
}
add_shortcode( 'afvf_contact_form', 'afvf_contact_form_shortcode' );

/**
 * Formulaire « Nos Cours » (motif Wix) — prénom, nom, e-mail, message, paiement.
 *
 * @return string
 */
function afvf_course_form_shortcode() {
	$payment_url = apply_filters(
		'afvf_course_payment_url',
		home_url( '/book-online/cours-ecouter/' )
	);
	ob_start();
	?>
	<div class="afvf-cours-form">
		<?php afvf_contact_render_notice( 'afvf-cours-form__notice' ); ?>
		<form class="afvf-cours-form__fields" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
			<input type="hidden" name="action" value="afvf_course">
			<?php wp_nonce_field( 'afvf_course', 'afvf_course_nonce' ); ?>
			<div class="afvf-honeypot" aria-hidden="true">
				<label for="afvf-course-company"><?php esc_html_e( 'Ne pas remplir', 'afvf' ); ?></label>
				<input id="afvf-course-company" type="text" name="afvf_company" tabindex="-1" autocomplete="off">
			</div>
			<div class="afvf-cours-form__row">
				<p class="afvf-cours-form__field">
					<label for="afvf-course-first"><?php esc_html_e( 'Prénom', 'afvf' ); ?></label>
					<input id="afvf-course-first" type="text" name="first_name" required autocomplete="given-name" maxlength="120">
				</p>
				<p class="afvf-cours-form__field">
					<label for="afvf-course-last"><?php esc_html_e( 'Nom', 'afvf' ); ?></label>
					<input id="afvf-course-last" type="text" name="last_name" required autocomplete="family-name" maxlength="120">
				</p>
			</div>
			<p class="afvf-cours-form__field">
				<label for="afvf-course-email"><?php esc_html_e( 'E-mail', 'afvf' ); ?> <span class="afvf-required" aria-hidden="true">*</span></label>
				<input id="afvf-course-email" type="email" name="email" required autocomplete="email">
			</p>
			<p class="afvf-cours-form__field">
				<label for="afvf-course-message"><?php esc_html_e( 'Message', 'afvf' ); ?></label>
				<textarea id="afvf-course-message" name="message" rows="4" maxlength="2000"></textarea>
			</p>
			<p class="afvf-cours-form__submit-wrap">
				<button type="submit" class="afvf-cours-form__submit"><?php esc_html_e( 'Procéder au paiement', 'afvf' ); ?></button>
			</p>
			<input type="hidden" name="afvf_payment_url" value="<?php echo esc_url( $payment_url ); ?>">
		</form>
	</div>
	<?php
	return ob_get_clean();
}
add_shortcode( 'afvf_course_form', 'afvf_course_form_shortcode' );

/**
 * @param string $name Name.
 * @param string $organization Organization.
 * @param string $email Email.
 */
function afvf_contact_notify_admin( $name, $organization, $email ) {
	$dest = function_exists( 'afvf_mod' ) ? afvf_mod( 'afvf_email', 'assofvf@gmail.com' ) : get_option( 'admin_email' );
	$body = sprintf(
		"Nouveau message via le formulaire de contact :\n\nNom : %s\nOrganisme : %s\nE-mail : %s\n\nDate : %s\n\nMessages : %s\n",
		$name,
		$organization ? $organization : '—',
		$email,
		gmdate( 'Y-m-d H:i:s' ),
		admin_url( 'admin.php?page=afvf-contact' )
	);
	wp_mail( $dest, '[AFVF] Formulaire de contact', $body );
}

function afvf_handle_contact_form() {
	$redirect = wp_get_referer() ?: home_url( '/' );
	$redirect = remove_query_arg( array( 'contact', 'email', 'name' ), $redirect );

	if ( ! isset( $_POST['afvf_contact_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['afvf_contact_nonce'] ) ), 'afvf_contact' ) ) {
		wp_safe_redirect( add_query_arg( 'contact', 'error', $redirect ) );
		exit;
	}

	if ( ! empty( $_POST['afvf_company'] ) ) {
		wp_safe_redirect( add_query_arg( 'contact', 'ok', $redirect ) );
		exit;
	}

	$name         = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
	$organization = isset( $_POST['organization'] ) ? sanitize_text_field( wp_unslash( $_POST['organization'] ) ) : '';
	$email        = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';

	if ( ! $name || ! $email || ! is_email( $email ) ) {
		wp_safe_redirect(
			add_query_arg(
				array(
					'contact' => 'invalid',
					'email'   => rawurlencode( $email ),
					'name'    => rawurlencode( $name ),
				),
				$redirect
			)
		);
		exit;
	}

	global $wpdb;
	$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	$wpdb->insert(
		afvf_contact_table(),
		array(
			'name'         => $name,
			'organization' => $organization ?: null,
			'email'        => $email,
			'submitted_at' => current_time( 'mysql', true ),
			'ip_address'   => $ip,
			'status'       => 'new',
		),
		array( '%s', '%s', '%s', '%s', '%s', '%s' )
	);

	if ( false === $wpdb->insert_id ) {
		wp_safe_redirect( add_query_arg( 'contact', 'error', $redirect ) );
		exit;
	}

	afvf_contact_notify_admin( $name, $organization, $email );
	wp_safe_redirect( add_query_arg( 'contact', 'ok', $redirect ) );
	exit;
}
add_action( 'admin_post_nopriv_afvf_contact', 'afvf_handle_contact_form' );
add_action( 'admin_post_afvf_contact', 'afvf_handle_contact_form' );

/**
 * Inscription cours : enregistrement + redirection vers la page de paiement.
 */
function afvf_handle_course_form() {
	$redirect = wp_get_referer() ?: home_url( '/' );
	$redirect = remove_query_arg( array( 'contact', 'email', 'name' ), $redirect );
	$pay      = isset( $_POST['afvf_payment_url'] ) ? esc_url_raw( wp_unslash( $_POST['afvf_payment_url'] ) ) : home_url( '/' );

	if ( ! isset( $_POST['afvf_course_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['afvf_course_nonce'] ) ), 'afvf_course' ) ) {
		wp_safe_redirect( add_query_arg( 'contact', 'error', $redirect ) );
		exit;
	}
	if ( ! empty( $_POST['afvf_company'] ) ) {
		wp_safe_redirect( $pay );
		exit;
	}

	$first   = isset( $_POST['first_name'] ) ? sanitize_text_field( wp_unslash( $_POST['first_name'] ) ) : '';
	$last    = isset( $_POST['last_name'] ) ? sanitize_text_field( wp_unslash( $_POST['last_name'] ) ) : '';
	$email   = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	$message = isset( $_POST['message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) : '';
	$name    = trim( $first . ' ' . $last );

	if ( ! $name || ! $email || ! is_email( $email ) ) {
		wp_safe_redirect( add_query_arg( 'contact', 'invalid', $redirect ) );
		exit;
	}

	global $wpdb;
	$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	$org = 'Cours 35 € — ' . ( $message ? wp_html_excerpt( $message, 500, '…' ) : '—' );
	$wpdb->insert(
		afvf_contact_table(),
		array(
			'name'         => $name,
			'organization' => $org,
			'email'        => $email,
			'submitted_at' => current_time( 'mysql', true ),
			'ip_address'   => $ip,
			'status'       => 'new',
		),
		array( '%s', '%s', '%s', '%s', '%s', '%s' )
	);

	if ( false !== $wpdb->insert_id ) {
		$dest = function_exists( 'afvf_mod' ) ? afvf_mod( 'afvf_email', 'assofvf@gmail.com' ) : get_option( 'admin_email' );
		wp_mail(
			$dest,
			'[AFVF] Inscription cours',
			sprintf(
				"Inscription cours (35 €)\n\nNom : %s\nE-mail : %s\n\nMessage :\n%s\n",
				$name,
				$email,
				$message ? $message : '—'
			)
		);
	}

	wp_safe_redirect( $pay );
	exit;
}
add_action( 'admin_post_nopriv_afvf_course', 'afvf_handle_course_form' );
add_action( 'admin_post_afvf_course', 'afvf_handle_course_form' );

/**
 * Formulaire « Contact » (motif Wix violet) — prénom, nom, e-mail, téléphone, message.
 *
 * @return string
 */
function afvf_contact_wix_shortcode() {
	ob_start();
	?>
	<div class="afvf-cwix" id="contact-formulaire">
		<?php afvf_contact_render_notice( 'afvf-cwix__notice' ); ?>
		<form class="afvf-cwix__form" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
			<input type="hidden" name="action" value="afvf_contact_wix">
			<?php wp_nonce_field( 'afvf_contact_wix', 'afvf_contact_wix_nonce' ); ?>
			<div class="afvf-honeypot" aria-hidden="true">
				<label for="afvf-cwix-company"><?php esc_html_e( 'Ne pas remplir', 'afvf' ); ?></label>
				<input id="afvf-cwix-company" type="text" name="afvf_company" tabindex="-1" autocomplete="off">
			</div>
			<div class="afvf-cwix__row">
				<p class="afvf-cwix__field">
					<label for="afvf-cwix-first"><?php esc_html_e( 'Prénom', 'afvf' ); ?></label>
					<input id="afvf-cwix-first" type="text" name="first_name" autocomplete="given-name" maxlength="120" placeholder="<?php esc_attr_e( 'Ex. Kat', 'afvf' ); ?>">
				</p>
				<p class="afvf-cwix__field">
					<label for="afvf-cwix-last"><?php esc_html_e( 'Nom de famille', 'afvf' ); ?></label>
					<input id="afvf-cwix-last" type="text" name="last_name" autocomplete="family-name" maxlength="120" placeholder="<?php esc_attr_e( 'Ex. Jones', 'afvf' ); ?>">
				</p>
			</div>
			<div class="afvf-cwix__row">
				<p class="afvf-cwix__field">
					<label for="afvf-cwix-email"><?php esc_html_e( 'E-mail', 'afvf' ); ?> *</label>
					<input id="afvf-cwix-email" type="email" name="email" required autocomplete="email" placeholder="<?php esc_attr_e( 'Ex. e-mail@exemple.com', 'afvf' ); ?>">
				</p>
				<p class="afvf-cwix__field">
					<label for="afvf-cwix-phone"><?php esc_html_e( 'Téléphone', 'afvf' ); ?></label>
					<input id="afvf-cwix-phone" type="tel" name="phone" autocomplete="tel" maxlength="40" placeholder="<?php esc_attr_e( 'Ex. 01 23 45 67 89', 'afvf' ); ?>">
				</p>
			</div>
			<p class="afvf-cwix__field afvf-cwix__field--full">
				<label for="afvf-cwix-message"><?php esc_html_e( 'Message', 'afvf' ); ?></label>
				<textarea id="afvf-cwix-message" name="message" rows="2" maxlength="3000" placeholder="<?php esc_attr_e( 'Rédigez votre message ici…', 'afvf' ); ?>"></textarea>
			</p>
			<p class="afvf-cwix__submit-wrap">
				<button type="submit" class="afvf-cwix__submit"><?php esc_html_e( 'Envoyer', 'afvf' ); ?></button>
			</p>
		</form>
	</div>
	<?php
	return ob_get_clean();
}
add_shortcode( 'afvf_contact_wix', 'afvf_contact_wix_shortcode' );

function afvf_handle_contact_wix() {
	$redirect = wp_get_referer() ?: home_url( '/' );
	$redirect = remove_query_arg( array( 'contact', 'email', 'name' ), $redirect );
	$anchor   = '#contact-formulaire';

	if ( ! isset( $_POST['afvf_contact_wix_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['afvf_contact_wix_nonce'] ) ), 'afvf_contact_wix' ) ) {
		wp_safe_redirect( add_query_arg( 'contact', 'error', $redirect ) . $anchor );
		exit;
	}
	if ( ! empty( $_POST['afvf_company'] ) ) {
		wp_safe_redirect( add_query_arg( 'contact', 'ok', $redirect ) . $anchor );
		exit;
	}

	$first   = isset( $_POST['first_name'] ) ? sanitize_text_field( wp_unslash( $_POST['first_name'] ) ) : '';
	$last    = isset( $_POST['last_name'] ) ? sanitize_text_field( wp_unslash( $_POST['last_name'] ) ) : '';
	$email   = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	$phone   = isset( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : '';
	$message = isset( $_POST['message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) : '';
	$name    = trim( $first . ' ' . $last );

	if ( ! $email || ! is_email( $email ) ) {
		wp_safe_redirect( add_query_arg( 'contact', 'invalid', $redirect ) . $anchor );
		exit;
	}
	if ( '' === $name ) {
		$name = $email;
	}

	global $wpdb;
	$ip   = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	$info = trim( ( $phone ? 'Tél ' . $phone . ' — ' : '' ) . wp_html_excerpt( $message, 120, '…' ) );
	$wpdb->insert(
		afvf_contact_table(),
		array(
			'name'         => $name,
			'organization' => wp_html_excerpt( $info, 185, '…' ) ?: null,
			'email'        => $email,
			'submitted_at' => current_time( 'mysql', true ),
			'ip_address'   => $ip,
			'status'       => 'new',
		),
		array( '%s', '%s', '%s', '%s', '%s', '%s' )
	);

	if ( $wpdb->insert_id ) {
		$dest = function_exists( 'afvf_mod' ) ? afvf_mod( 'afvf_email', 'assofvf@gmail.com' ) : get_option( 'admin_email' );
		wp_mail(
			$dest,
			'[AFVF] Formulaire Contact',
			sprintf(
				"Nouveau message (page Contact) :\n\nNom : %s\nE-mail : %s\nTéléphone : %s\n\nMessage :\n%s\n\nMessages : %s\n",
				$name,
				$email,
				$phone ? $phone : '—',
				$message ? $message : '—',
				admin_url( 'admin.php?page=afvf-contact' )
			),
			array( 'Reply-To: ' . $email )
		);
	} else {
		wp_safe_redirect( add_query_arg( 'contact', 'error', $redirect ) . $anchor );
		exit;
	}

	wp_safe_redirect( add_query_arg( 'contact', 'ok', $redirect ) . $anchor );
	exit;
}
add_action( 'admin_post_nopriv_afvf_contact_wix', 'afvf_handle_contact_wix' );
add_action( 'admin_post_afvf_contact_wix', 'afvf_handle_contact_wix' );
