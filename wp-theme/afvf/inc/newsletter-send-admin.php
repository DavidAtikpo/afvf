<?php
/**
 * Admin — envoi newsletter (diffusion + individuel).
 *
 * @package AFVF
 */

defined( 'ABSPATH' ) || exit;

function afvf_newsletter_send_admin_menus() {
	add_submenu_page(
		'afvf-newsletter',
		__( 'Envoyer une campagne', 'afvf' ),
		__( 'Envoyer une campagne', 'afvf' ),
		'manage_options',
		'afvf-newsletter-broadcast',
		'afvf_newsletter_broadcast_page'
	);
	add_submenu_page(
		null,
		__( 'Écrire à un abonné', 'afvf' ),
		__( 'Écrire à un abonné', 'afvf' ),
		'manage_options',
		'afvf-newsletter-write',
		'afvf_newsletter_write_page'
	);
}
add_action( 'admin_menu', 'afvf_newsletter_send_admin_menus', 11 );

function afvf_newsletter_send_admin_post() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	if ( isset( $_POST['afvf_broadcast_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['afvf_broadcast_nonce'] ) ), 'afvf_newsletter_broadcast' ) ) {
		$subject = isset( $_POST['subject'] ) ? sanitize_text_field( wp_unslash( $_POST['subject'] ) ) : '';
		$body    = isset( $_POST['body'] ) ? wp_kses_post( wp_unslash( $_POST['body'] ) ) : '';
		if ( $subject && $body ) {
			$sent = 0;
			foreach ( afvf_newsletter_sendable_emails() as $email ) {
				if ( afvf_newsletter_send_message( $email, $subject, $body ) ) {
					++$sent;
				}
			}
			wp_safe_redirect( admin_url( 'admin.php?page=afvf-newsletter-broadcast&sent=' . $sent ) );
			exit;
		}
	}

	if ( isset( $_POST['afvf_write_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['afvf_write_nonce'] ) ), 'afvf_newsletter_write' ) ) {
		$email   = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
		$subject = isset( $_POST['subject'] ) ? sanitize_text_field( wp_unslash( $_POST['subject'] ) ) : '';
		$body    = isset( $_POST['body'] ) ? wp_kses_post( wp_unslash( $_POST['body'] ) ) : '';
		if ( is_email( $email ) && $subject && $body ) {
			$ok = afvf_newsletter_send_message( $email, $subject, $body );
			wp_safe_redirect(
				admin_url(
					'admin.php?page=afvf-newsletter-write&email=' . rawurlencode( $email ) . ( $ok ? '&ok=1' : '&ok=0' )
				)
			);
			exit;
		}
	}
}
add_action( 'admin_init', 'afvf_newsletter_send_admin_post' );

function afvf_newsletter_broadcast_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$count = count( afvf_newsletter_sendable_emails() );
	$sent  = isset( $_GET['sent'] ) ? (int) $_GET['sent'] : null;
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Envoyer une campagne newsletter', 'afvf' ); ?></h1>
		<p class="description">
			<?php
			printf(
				esc_html__( 'Envoi à %d abonné(s) actifs (double opt-in respecté). Configurez WP Mail SMTP pour la délivrabilité.', 'afvf' ),
				$count
			);
			?>
		</p>
		<?php if ( null !== $sent ) : ?>
			<div class="notice notice-success"><p><?php printf( esc_html__( '%d e-mail(s) envoyé(s).', 'afvf' ), $sent ); ?></p></div>
		<?php endif; ?>
		<form method="post">
			<?php wp_nonce_field( 'afvf_newsletter_broadcast', 'afvf_broadcast_nonce' ); ?>
			<table class="form-table">
				<tr>
					<th><label for="subject"><?php esc_html_e( 'Objet', 'afvf' ); ?></label></th>
					<td><input type="text" name="subject" id="subject" class="large-text" required></td>
				</tr>
				<tr>
					<th><label for="body"><?php esc_html_e( 'Message', 'afvf' ); ?></label></th>
					<td><textarea name="body" id="body" rows="12" class="large-text" required></textarea>
					<p class="description"><?php esc_html_e( 'HTML simple autorisé (paragraphes, liens).', 'afvf' ); ?></p></td>
				</tr>
			</table>
			<?php submit_button( __( 'Envoyer à tous les abonnés', 'afvf' ) ); ?>
		</form>
	</div>
	<?php
}

function afvf_newsletter_write_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$email = isset( $_GET['email'] ) ? sanitize_email( wp_unslash( $_GET['email'] ) ) : '';
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Écrire à un abonné', 'afvf' ); ?></h1>
		<p><a href="<?php echo esc_url( admin_url( 'admin.php?page=afvf-newsletter' ) ); ?>">&larr; <?php esc_html_e( 'Liste des inscrits', 'afvf' ); ?></a></p>
		<?php if ( ! empty( $_GET['ok'] ) ) : ?>
			<div class="notice notice-success"><p><?php esc_html_e( 'Message envoyé.', 'afvf' ); ?></p></div>
		<?php elseif ( isset( $_GET['ok'] ) && '0' === $_GET['ok'] ) : ?>
			<div class="notice notice-error"><p><?php esc_html_e( 'Échec de l’envoi.', 'afvf' ); ?></p></div>
		<?php endif; ?>
		<form method="post">
			<?php wp_nonce_field( 'afvf_newsletter_write', 'afvf_write_nonce' ); ?>
			<table class="form-table">
				<tr>
					<th><label for="email"><?php esc_html_e( 'E-mail', 'afvf' ); ?></label></th>
					<td><input type="email" name="email" id="email" value="<?php echo esc_attr( $email ); ?>" class="regular-text" required></td>
				</tr>
				<tr>
					<th><label for="subject"><?php esc_html_e( 'Objet', 'afvf' ); ?></label></th>
					<td><input type="text" name="subject" id="subject" class="large-text" required></td>
				</tr>
				<tr>
					<th><label for="body"><?php esc_html_e( 'Message', 'afvf' ); ?></label></th>
					<td><textarea name="body" id="body" rows="10" class="large-text" required></textarea></td>
				</tr>
			</table>
			<?php submit_button( __( 'Envoyer', 'afvf' ) ); ?>
		</form>
	</div>
	<?php
}
