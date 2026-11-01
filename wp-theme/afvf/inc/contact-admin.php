<?php
/**
 * Administration — messages formulaire de contact.
 *
 * @package AFVF
 */

defined( 'ABSPATH' ) || exit;

function afvf_contact_admin_menu() {
	add_menu_page(
		__( 'Contact AFVF', 'afvf' ),
		__( 'Contact', 'afvf' ),
		'manage_options',
		'afvf-contact',
		'afvf_contact_admin_page',
		'dashicons-email',
		59
	);
}
add_action( 'admin_menu', 'afvf_contact_admin_menu' );

function afvf_contact_admin_export_csv() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Accès refusé.', 'afvf' ) );
	}
	check_admin_referer( 'afvf_contact_export' );

	global $wpdb;
	$rows = $wpdb->get_results(
		'SELECT name, organization, email, submitted_at, status FROM ' . afvf_contact_table() . ' ORDER BY submitted_at DESC',
		ARRAY_A
	);

	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename=afvf-contact-' . gmdate( 'Y-m-d' ) . '.csv' );

	$out = fopen( 'php://output', 'w' );
	fputcsv( $out, array( 'name', 'organization', 'email', 'submitted_at', 'status' ) );
	foreach ( $rows as $row ) {
		fputcsv( $out, $row );
	}
	fclose( $out );
	exit;
}

function afvf_contact_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	if ( isset( $_GET['export'] ) && 'csv' === $_GET['export'] ) {
		afvf_contact_admin_export_csv();
	}

	global $wpdb;
	$table = afvf_contact_table();
	$new   = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table} WHERE status = 'new'" );

	$per_page = 50;
	$page     = isset( $_GET['paged'] ) ? max( 1, (int) $_GET['paged'] ) : 1;
	$offset   = ( $page - 1 ) * $per_page;

	$items = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT * FROM {$table} ORDER BY submitted_at DESC LIMIT %d OFFSET %d",
			$per_page,
			$offset
		)
	);

	$total_items = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" );
	$export_url  = wp_nonce_url(
		admin_url( 'admin.php?page=afvf-contact&export=csv' ),
		'afvf_contact_export'
	);
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Formulaire de contact', 'afvf' ); ?></h1>
		<p>
			<?php
			printf(
				/* translators: %d: count of unread messages */
				esc_html__( 'Messages non lus : %d', 'afvf' ),
				$new
			);
			?>
		</p>
		<p>
			<a class="button button-primary" href="<?php echo esc_url( $export_url ); ?>">
				<?php esc_html_e( 'Exporter CSV', 'afvf' ); ?>
			</a>
		</p>
		<p class="description">
			<?php esc_html_e( 'Envoi depuis le bloc « Nous sommes @ votre écoute » (motif Mobiliser ou shortcode [afvf_contact_form]).', 'afvf' ); ?>
		</p>
		<table class="widefat striped">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Nom', 'afvf' ); ?></th>
					<th><?php esc_html_e( 'Organisme', 'afvf' ); ?></th>
					<th><?php esc_html_e( 'E-mail', 'afvf' ); ?></th>
					<th><?php esc_html_e( 'Date', 'afvf' ); ?></th>
					<th><?php esc_html_e( 'Statut', 'afvf' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php if ( empty( $items ) ) : ?>
					<tr><td colspan="5"><?php esc_html_e( 'Aucun message pour le moment.', 'afvf' ); ?></td></tr>
				<?php else : ?>
					<?php foreach ( $items as $item ) : ?>
						<tr>
							<td><?php echo esc_html( $item->name ); ?></td>
							<td><?php echo $item->organization ? esc_html( $item->organization ) : '—'; ?></td>
							<td><a href="mailto:<?php echo esc_attr( $item->email ); ?>"><?php echo esc_html( $item->email ); ?></a></td>
							<td><?php echo esc_html( $item->submitted_at ); ?></td>
							<td><?php echo esc_html( $item->status ); ?></td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
			</tbody>
		</table>
		<?php if ( $total_items > $per_page ) : ?>
			<p>
				<?php
				printf(
					/* translators: 1: current page, 2: total pages */
					esc_html__( 'Page %1$d — %2$d messages au total.', 'afvf' ),
					$page,
					$total_items
				);
				?>
			</p>
		<?php endif; ?>
	</div>
	<?php
}
