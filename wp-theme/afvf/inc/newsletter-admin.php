<?php
/**
 * Administration — liste des inscrits newsletter.
 *
 * @package AFVF
 */

defined( 'ABSPATH' ) || exit;

function afvf_newsletter_admin_menu() {
	add_menu_page(
		__( 'Newsletter AFVF', 'afvf' ),
		__( 'Newsletter', 'afvf' ),
		'manage_options',
		'afvf-newsletter',
		'afvf_newsletter_admin_page',
		'dashicons-email-alt',
		58
	);
}
add_action( 'admin_menu', 'afvf_newsletter_admin_menu' );

function afvf_newsletter_admin_export_csv() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Accès refusé.', 'afvf' ) );
	}
	check_admin_referer( 'afvf_newsletter_export' );

	global $wpdb;
	$rows = $wpdb->get_results(
		'SELECT email, first_name, last_name, status, subscribed_at, confirmed_at FROM ' . afvf_newsletter_table() . ' ORDER BY subscribed_at DESC',
		ARRAY_A
	);

	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename=afvf-newsletter-' . gmdate( 'Y-m-d' ) . '.csv' );

	$out = fopen( 'php://output', 'w' );
	fputcsv( $out, array( 'email', 'first_name', 'last_name', 'status', 'subscribed_at', 'confirmed_at' ) );
	foreach ( $rows as $row ) {
		fputcsv( $out, $row );
	}
	fclose( $out );
	exit;
}

function afvf_newsletter_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	if ( isset( $_GET['export'] ) && 'csv' === $_GET['export'] ) {
		afvf_newsletter_admin_export_csv();
	}

	global $wpdb;
	$table = afvf_newsletter_table();
	$total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table} WHERE status = 'active'" );
	$pending = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table} WHERE status = 'pending'" );

	$per_page = 50;
	$page     = isset( $_GET['paged'] ) ? max( 1, (int) $_GET['paged'] ) : 1;
	$offset   = ( $page - 1 ) * $per_page;

	$items = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT * FROM {$table} ORDER BY subscribed_at DESC LIMIT %d OFFSET %d",
			$per_page,
			$offset
		)
	);

	$total_items = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" );
	$export_url  = wp_nonce_url(
		admin_url( 'admin.php?page=afvf-newsletter&export=csv' ),
		'afvf_newsletter_export'
	);
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Newsletter — inscrits', 'afvf' ); ?></h1>
		<p>
			<?php
			printf(
				/* translators: 1: active count, 2: pending count */
				esc_html__( 'Inscriptions actives : %1$d — en attente de confirmation : %2$d', 'afvf' ),
				$total,
				$pending
			);
			?>
		</p>
		<p>
			<a class="button button-primary" href="<?php echo esc_url( $export_url ); ?>">
				<?php esc_html_e( 'Exporter CSV', 'afvf' ); ?>
			</a>
		</p>
		<p>
			<a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=afvf-newsletter-broadcast' ) ); ?>">
				<?php esc_html_e( 'Envoyer une campagne à tous', 'afvf' ); ?>
			</a>
		</p>
		<p class="description">
			<?php esc_html_e( 'Inscriptions via le pied de page ou la page « Rejoignez notre liste » (motif ou shortcode [afvf_newsletter_inscription]). Campagnes et messages individuels depuis ce menu ; installez WP Mail SMTP pour l’envoi des e-mails.', 'afvf' ); ?>
		</p>
		<table class="widefat striped">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Prénom', 'afvf' ); ?></th>
					<th><?php esc_html_e( 'Nom', 'afvf' ); ?></th>
					<th><?php esc_html_e( 'E-mail', 'afvf' ); ?></th>
					<th><?php esc_html_e( 'Statut', 'afvf' ); ?></th>
					<th><?php esc_html_e( 'Inscription', 'afvf' ); ?></th>
					<th><?php esc_html_e( 'Confirmée', 'afvf' ); ?></th>
					<th><?php esc_html_e( 'Actions', 'afvf' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php if ( empty( $items ) ) : ?>
					<tr><td colspan="7"><?php esc_html_e( 'Aucune inscription pour le moment.', 'afvf' ); ?></td></tr>
				<?php else : ?>
					<?php foreach ( $items as $item ) : ?>
						<tr>
							<td><?php echo $item->first_name ? esc_html( $item->first_name ) : '—'; ?></td>
							<td><?php echo $item->last_name ? esc_html( $item->last_name ) : '—'; ?></td>
							<td><?php echo esc_html( $item->email ); ?></td>
							<td><?php echo esc_html( $item->status ); ?></td>
							<td><?php echo esc_html( $item->subscribed_at ); ?></td>
							<td><?php echo $item->confirmed_at ? esc_html( $item->confirmed_at ) : '—'; ?></td>
							<td>
								<a href="<?php echo esc_url( admin_url( 'admin.php?page=afvf-newsletter-write&email=' . rawurlencode( $item->email ) ) ); ?>">
									<?php esc_html_e( 'Écrire', 'afvf' ); ?>
								</a>
							</td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
			</tbody>
		</table>
		<?php if ( $total_items > $per_page ) : ?>
			<p>
				<?php
				printf(
					esc_html__( 'Page %1$d — %2$d inscriptions au total.', 'afvf' ),
					$page,
					$total_items
				);
				?>
			</p>
		<?php endif; ?>
	</div>
	<?php
}
