<?php
/**
 * Pied de page (mise en page Wix).
 *
 * @package AFVF
 */

defined( 'ABSPATH' ) || exit;

$phone   = afvf_mod( 'afvf_phone', '06 26 36 89 94' );
$email   = afvf_mod( 'afvf_email', 'assofvf@gmail.com' );
$donate   = afvf_mod( 'afvf_donate_url', 'https://www.helloasso.com/associations/afvf/formulaires/1' );
$member   = afvf_mod( 'afvf_member_url', 'https://pay.qonto.com/payment-links/019e2fee-f9e0-7add-b36a-47b06719677e?resource_id=019e2fee-f9e1-7413-b624-514697f490e8' );
$terms    = afvf_terms_url();
$privacy  = afvf_privacy_url();
?>
</main>

<footer class="footer footer--wix">
	<div class="footer-shell">
	<div class="footer-main">
		<div class="footer-col footer-col--contact">
			<h3 class="footer-brand">AFVF</h3>
			<p class="footer-intro">
				<?php
				echo wp_kses(
					__( 'Nous sommes à votre écoute via mail, téléphone,<br>tchat et sur les réseaux sociaux.<br>Pour nous suivre:', 'afvf' ),
					array( 'br' => array() )
				);
				?>
			</p>
			<?php if ( afvf_footer_social_uses_plugin() ) : ?>
				<div class="footer-social footer-social--plugin">
					<?php dynamic_sidebar( 'afvf-footer-social' ); ?>
				</div>
			<?php else : ?>
				<?php get_template_part( 'template-parts/footer', 'social' ); ?>
			<?php endif; ?>
			<p class="footer-contact-line">
				<strong><?php esc_html_e( 'Email', 'afvf' ); ?> :</strong>
				<a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a>
			</p>
			<p class="footer-contact-line footer-contact-line--phone">
				<strong><?php esc_html_e( 'Téléphone', 'afvf' ); ?> :</strong>
				<a href="<?php echo esc_url( afvf_tel_href( $phone ) ); ?>"><?php echo esc_html( $phone ); ?></a>
			</p>
		</div>

		<div class="footer-col footer-col--newsletter">
			<h3 class="footer-newsletter-title"><?php esc_html_e( 'Recevez nos actualités mensuellement', 'afvf' ); ?></h3>
			<?php afvf_newsletter_render_notice(); ?>
			<form class="footer-newsletter-form" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
				<input type="hidden" name="action" value="afvf_newsletter">
				<?php wp_nonce_field( 'afvf_newsletter', 'afvf_newsletter_nonce' ); ?>
				<div class="afvf-honeypot" aria-hidden="true">
					<label for="afvf-company"><?php esc_html_e( 'Ne pas remplir', 'afvf' ); ?></label>
					<input id="afvf-company" type="text" name="afvf_company" tabindex="-1" autocomplete="off">
				</div>
				<label class="screen-reader-text" for="footer-email"><?php esc_html_e( 'Adresse e-mail', 'afvf' ); ?></label>
				<input id="footer-email" type="email" name="email" required placeholder="<?php esc_attr_e( 'Entrez votre adresse mail ici', 'afvf' ); ?>" value="<?php echo isset( $_GET['newsletter'] ) && 'invalid' === sanitize_key( wp_unslash( $_GET['newsletter'] ) ) && isset( $_GET['email'] ) ? esc_attr( sanitize_email( wp_unslash( $_GET['email'] ) ) ) : ''; ?>">
				<button type="submit" class="btn btn-footer-pink"><?php esc_html_e( "S'abonner !", 'afvf' ); ?></button>
			</form>
		</div>

		<div class="footer-col footer-col--links">
			<h3 class="footer-links-title"><?php esc_html_e( 'LIENS RAPIDES', 'afvf' ); ?></h3>
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'footer',
					'container'      => false,
					'menu_class'     => 'footer-quicklinks',
					'fallback_cb'    => 'afvf_footer_menu_fallback',
					'depth'          => 1,
				)
			);
			?>
		</div>
	</div>

	<div class="footer-cta">
		<a class="btn btn-footer-pink btn-footer-donate" href="<?php echo esc_url( $donate ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'DON-nez nous de la force', 'afvf' ); ?></a>
		<a class="footer-adhere" href="<?php echo esc_url( $member ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Adhérer', 'afvf' ); ?></a>
	</div>
	</div>

	<div class="footer-legal-bar">
		<p>
			&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?>
			<?php esc_html_e( 'Association des Familles de Victimes de Féminicides', 'afvf' ); ?>
			| <a href="<?php echo esc_url( $terms ); ?>"><?php esc_html_e( "Conditions d'utilisations", 'afvf' ); ?></a>
			| <a href="<?php echo esc_url( $privacy ); ?>"><?php esc_html_e( 'Politique de confidentialité', 'afvf' ); ?></a>
		</p>
	</div>

</footer>
<?php wp_footer(); ?>
</body>
</html>
